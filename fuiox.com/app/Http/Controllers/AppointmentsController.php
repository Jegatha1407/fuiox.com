<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentsController extends Controller
{
    private function userId(): int {
        $id = session('auth_user');
        $user = \App\Models\User::find($id);
        if ($user && $user->is_app_employee && $user->parent_user_id) {
            return $user->parent_user_id;
        }
        return $id;
    }

    // ── Appointments ledger page ──
    public function index($appType)
    {
        $userId = $this->userId();
        $user = \App\Models\User::findOrFail($userId);
        $label = \App\Http\Controllers\AppsController::resourceLabel($appType);
        $appInfo = \App\Http\Controllers\AppsController::catalog()[$appType] ?? null;
        if (!$appInfo) abort(404);

        $appointments = DB::table('app_appointments')
            ->where('user_id', $userId)->where('app_type', $appType)
            ->orderByDesc('appointment_date')->orderBy('appointment_time')
            ->get();

        return view('user.app_appointments', compact('user', 'appType', 'appointments', 'label', 'appInfo'));
    }

    // ── Get available (non-booked) slots for one resource on one date ──
    // This is the core conflict-prevention check used by both the manual booking UI and the AI flow engine
    public static function getAvailableSlots($userId, $appType, $resourceId, $date)
    {
        $resource = DB::table('app_resources')->where('id', $resourceId)->where('user_id', $userId)->first();
        if (!$resource) return [];

        $allSlots = array_filter(array_map('trim', explode(',', $resource->slots ?? '')));
        if (empty($allSlots)) return [];

        $bookedSlots = DB::table('app_appointments')
            ->where('user_id', $userId)->where('app_type', $appType)
            ->where('resource_id', $resourceId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('appointment_time')
            ->map(fn($t) => trim($t))
            ->toArray();

        return array_values(array_diff($allSlots, $bookedSlots));
    }

    // ── API endpoint: get available slots (used by Calendar node in chat flow) ──
    public function availableSlots(Request $request, $appType)
    {
        $userId = $this->userId();
        $resourceId = $request->resource_id;
        $date = $request->date;

        if (!$resourceId || !$date) {
            return response()->json(['error' => 'resource_id and date are required'], 400);
        }

        $slots = self::getAvailableSlots($userId, $appType, $resourceId, $date);
        return response()->json(['slots' => $slots]);
    }

    // ── Create a booking — atomically checks for conflict right before inserting ──
    public static function bookSlot($userId, $appType, $resourceId, $date, $time, $patientName, $patientPhone, $department = null, $notes = null)
    {
        // Re-check availability at the exact moment of booking to prevent race conditions
        $conflict = DB::table('app_appointments')
            ->where('user_id', $userId)->where('app_type', $appType)
            ->where('resource_id', $resourceId)
            ->where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($conflict) {
            return ['success' => false, 'error' => 'This slot was just booked by someone else. Please choose a different time.'];
        }

        $resource = DB::table('app_resources')->where('id', $resourceId)->first();
        $bookingNumber = self::nextBookingNumber($userId, $appType);

        $id = DB::table('app_appointments')->insertGetId([
            'user_id' => $userId, 'app_type' => $appType, 'booking_number' => $bookingNumber,
            'resource_id' => $resourceId, 'resource_name' => $resource->name ?? null,
            'patient_name' => $patientName, 'patient_phone' => $patientPhone,
            'department' => $department ?? ($resource->category ?? null),
            'appointment_date' => $date, 'appointment_time' => $time,
            'status' => 'confirmed', 'notes' => $notes,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Log::info('Appointment booked', ['id' => $id, 'booking_number' => $bookingNumber, 'user_id' => $userId, 'resource' => $resource->name ?? '', 'date' => $date, 'time' => $time]);

        return ['success' => true, 'appointment_id' => $id, 'booking_number' => $bookingNumber];
    }

    public static function nextBookingNumber($userId, $appType)
    {
        return DB::transaction(function () use ($userId, $appType) {
            DB::table('app_booking_counters')->updateOrInsert(
                ['user_id' => $userId, 'app_type' => $appType],
                []
            );
            $row = DB::table('app_booking_counters')
                ->where('user_id', $userId)->where('app_type', $appType)
                ->lockForUpdate()->first();
            $next = $row->next_number ?? 1;
            DB::table('app_booking_counters')
                ->where('user_id', $userId)->where('app_type', $appType)
                ->update(['next_number' => $next + 1]);
            return $next;
        });
    }

    // ── API endpoint version of bookSlot, for use by manual UI or external calls ──
    public function book(Request $request, $appType)
    {
        $userId = $this->userId();
        $request->validate([
            'resource_id' => 'required', 'date' => 'required', 'time' => 'required',
            'patient_name' => 'required', 'patient_phone' => 'required',
        ]);

        $result = self::bookSlot(
            $userId, $appType, $request->resource_id, $request->date, $request->time,
            $request->patient_name, $request->patient_phone, $request->department, $request->notes
        );

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 409);
        }

        return response()->json(['success' => true, 'appointment_id' => $result['appointment_id']]);
    }

    // ── Cancel a booking (frees the slot back up) ──
    public function cancel($appType, $id)
    {
        $userId = $this->userId();
        DB::table('app_appointments')->where('id', $id)->where('user_id', $userId)
            ->update(['status' => 'cancelled', 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    // ── Mark as completed ──
    public function complete($appType, $id)
    {
        $userId = $this->userId();
        DB::table('app_appointments')->where('id', $id)->where('user_id', $userId)
            ->update(['status' => 'completed', 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }
}
