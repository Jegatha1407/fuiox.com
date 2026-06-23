<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AppEmployeeController extends Controller
{
    private function employee()
    {
        $user = \App\Models\User::find(session('auth_user'));
        if (!$user || !$user->is_app_employee) abort(403);
        return $user;
    }

    private function assignment($employee, $appType)
    {
        $assignment = DB::table('app_assignments')
            ->where('staff_user_id', $employee->id)
            ->where('app_type', $appType)
            ->first();
        if (!$assignment) abort(403);
        return $assignment;
    }

    public function dashboard($appType)
    {
        $employee = $this->employee();
        $assignment = $this->assignment($employee, $appType);
        $appInfo = AppsController::catalog()[$appType] ?? null;
        if (!$appInfo) abort(404);
        $config = AppsController::appConfig($appType);
        return view('employee.dashboard', compact('employee', 'assignment', 'appType', 'appInfo', 'config'));
    }

    public function flowBuilder($appType)
    {
        $employee = $this->employee();
        $assignment = $this->assignment($employee, $appType);
        if (!$assignment->can_flow_builder) abort(403);

        $ownerId = $employee->parent_user_id;
        $user = \App\Models\User::find($ownerId);
        $appInfo = AppsController::catalog()[$appType];
        $config = AppsController::appConfig($appType);
        $flow = DB::table('app_flows')->where('user_id', $ownerId)->where('app_type', $appType)->first();
        $resourceLabel = $config['resource_label'];
        $resourcesList = DB::table('app_resources')->where('user_id', $ownerId)->where('app_type', $appType)->where('is_active', 1)->orderBy('name')->get();
        return view('employee.flow_builder', compact('user', 'appType', 'flow', 'appInfo', 'resourceLabel', 'resourcesList', 'config'));
    }

    public function resources($appType)
    {
        $employee = $this->employee();
        $assignment = $this->assignment($employee, $appType);
        if (!$assignment->can_resources) abort(403);

        $ownerId = $employee->parent_user_id;
        $user = \App\Models\User::find($ownerId);
        $appInfo = AppsController::catalog()[$appType];
        $config = AppsController::appConfig($appType);
        $items = DB::table('app_resources')->where('user_id', $ownerId)->where('app_type', $appType)->orderBy('name')->get();
        $label = $config['resource_label'];
        return view('employee.resources', compact('user', 'appType', 'items', 'appInfo', 'label', 'config'));
    }

    public function records($appType)
    {
        $employee = $this->employee();
        $assignment = $this->assignment($employee, $appType);
        if (!$assignment->can_records) abort(403);

        $ownerId = $employee->parent_user_id;
        $user = \App\Models\User::find($ownerId);
        $appInfo = AppsController::catalog()[$appType];
        $config = AppsController::appConfig($appType);

        if ($config['is_time_based']) {
            $records = DB::table('app_appointments')->where('user_id', $ownerId)->where('app_type', $appType)->orderByDesc('appointment_date')->get();
        } else {
            $records = DB::table('app_orders')->where('user_id', $ownerId)->where('app_type', $appType)->orderByDesc('created_at')->get();
        }
        return view('employee.records', compact('user', 'appType', 'records', 'appInfo', 'config'));
    }

    public function changePassword()
    {
        $employee = $this->employee();
        return view('employee.change_password', compact('employee'));
    }

    public function updatePassword(Request $request)
    {
        $employee = $this->employee();
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $employee->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $employee->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password updated successfully.');
    }

    public function noAccess()
    {
        return view('employee.no_access');
    }
}
