<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppsController extends Controller
{
    private function userId(): int {
        $id = session('auth_user');
        $user = \App\Models\User::find($id);
        if ($user && $user->is_app_employee && $user->parent_user_id) {
            return $user->parent_user_id;
        }
        return $id;
    }

    public static function catalog(): array
    {
        return [
            'hospital' => [
                'name' => 'Hospital', 'icon' => '🏥', 'color' => '#e3f2fd',
                'desc' => 'Appointment booking with doctors, departments and time slots.',
            ],
            'restaurant' => [
                'name' => 'Restaurant', 'icon' => '🍽️', 'color' => '#fff3e0',
                'desc' => 'Menu ordering and table reservations.',
            ],
            'ecommerce' => [
                'name' => 'E-commerce', 'icon' => '🛒', 'color' => '#e8f5e9',
                'desc' => 'Product orders and catalog browsing.',
            ],
            'salon' => [
                'name' => 'Salon & Spa', 'icon' => '💇', 'color' => '#fce4ec',
                'desc' => 'Stylist booking and service slot scheduling.',
            ],
        ];
    }

    // ── Per-app config: resource label, icon, whether it's time-based (needs date+slots) or catalog-based ──
    public static function appConfig($appType): array
    {
        return match($appType) {
            'hospital' => [
                'resource_label' => 'Doctor', 'resource_icon' => '👨‍⚕️', 'is_time_based' => true,
                'form_fields' => [
                    ['key' => 'name', 'label' => 'Patient Name', 'type' => 'text', 'required' => true],
                ],
                'record_label' => 'Appointment',
            ],
            'restaurant' => [
                'resource_label' => 'Menu Item', 'resource_icon' => '🍔', 'is_time_based' => false,
                'form_fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['key' => 'address', 'label' => 'Delivery Address', 'type' => 'text', 'required' => true],
                ],
                'record_label' => 'Order',
            ],
            'ecommerce' => [
                'resource_label' => 'Product', 'resource_icon' => '📦', 'is_time_based' => false,
                'form_fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['key' => 'address', 'label' => 'Delivery Address', 'type' => 'text', 'required' => true],
                ],
                'record_label' => 'Order',
            ],
            'salon' => [
                'resource_label' => 'Service', 'resource_icon' => '💆', 'is_time_based' => true,
                'form_fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ],
                'record_label' => 'Booking',
            ],
            default => [
                'resource_label' => 'Resource', 'resource_icon' => '📋', 'is_time_based' => false,
                'form_fields' => [['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true]],
                'record_label' => 'Order',
            ],
        };
    }

    public static function resourceLabel($appType): string
    {
        return self::appConfig($appType)['resource_label'];
    }

    public function index()
    {
        $userId = $this->userId();
        $user = \App\Models\User::findOrFail($userId);
        $installed = DB::table('installed_apps')->where('user_id', $userId)->get()->keyBy('app_type');
        $catalog = self::catalog();
        return view('user.apps', compact('user', 'installed', 'catalog'));
    }

    public function install(Request $request)
    {
        $userId = $this->userId();
        $appType = $request->app_type;
        if (!array_key_exists($appType, self::catalog())) {
            return response()->json(['error' => 'Unknown app'], 400);
        }
        DB::table('installed_apps')->updateOrInsert(
            ['user_id' => $userId, 'app_type' => $appType],
            ['is_active' => 1, 'updated_at' => now(), 'installed_at' => now()]
        );
        DB::table('app_flows')->updateOrInsert(
            ['user_id' => $userId, 'app_type' => $appType],
            ['updated_at' => now()]
        );
        return response()->json(['success' => true]);
    }

    public function deactivate(Request $request)
    {
        $userId = $this->userId();
        $appType = $request->app_type;
        DB::table('installed_apps')
            ->where('user_id', $userId)->where('app_type', $appType)
            ->update(['is_active' => 0, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function builder($appType)
    {
        $userId = $this->userId();
        $user = \App\Models\User::findOrFail($userId);
        if (!array_key_exists($appType, self::catalog())) {
            abort(404);
        }
        $flow = DB::table('app_flows')->where('user_id', $userId)->where('app_type', $appType)->first();
        $appInfo = self::catalog()[$appType];
        $config = self::appConfig($appType);
        $resourceLabel = $config['resource_label'];
        $resourcesList = DB::table('app_resources')->where('user_id', $userId)->where('app_type', $appType)->where('is_active', 1)->orderBy('name')->get();
        return view('user.app_builder', compact('user', 'appType', 'flow', 'appInfo', 'resourceLabel', 'resourcesList', 'config'));
    }

    public function saveFlow(Request $request, $appType)
    {
        $userId = $this->userId();
        if (!array_key_exists($appType, self::catalog())) {
            return response()->json(['error' => 'Unknown app'], 400);
        }
        $flowData = [
            'nodes' => $request->input('nodes', []),
            'connections' => $request->input('connections', []),
        ];
        DB::table('app_flows')->updateOrInsert(
            ['user_id' => $userId, 'app_type' => $appType],
            ['flow_data' => json_encode($flowData), 'is_published' => 1, 'updated_at' => now()]
        );
        return response()->json(['success' => true]);
    }

    public function getFlow($appType)
    {
        $userId = $this->userId();
        $flow = DB::table('app_flows')->where('user_id', $userId)->where('app_type', $appType)->first();
        $data = ['nodes' => [], 'connections' => []];
        if ($flow && $flow->flow_data) {
            $decoded = json_decode($flow->flow_data, true);
            if (is_array($decoded)) $data = $decoded;
        }
        return response()->json($data);
    }

    public function resources($appType)
    {
        $userId = $this->userId();
        $user = \App\Models\User::findOrFail($userId);
        if (!array_key_exists($appType, self::catalog())) abort(404);
        $items = DB::table('app_resources')->where('user_id', $userId)->where('app_type', $appType)->orderBy('name')->get();
        $appInfo = self::catalog()[$appType];
        $config = self::appConfig($appType);
        $label = $config['resource_label'];
        return view('user.app_resources', compact('user', 'appType', 'items', 'appInfo', 'label', 'config'));
    }

    public function addResource(Request $request, $appType)
    {
        $userId = $this->userId();
        $request->validate(['name' => 'required|string|max:255']);
        DB::table('app_resources')->insert([
            'user_id' => $userId, 'app_type' => $appType,
            'name' => $request->name, 'category' => $request->category,
            'slots' => $request->slots, 'available_dates' => $request->available_dates,
            'price' => $request->price, 'description' => $request->description,
            'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['success' => true]);
    }

    public function updateResource(Request $request, $appType, $id)
    {
        $userId = $this->userId();
        DB::table('app_resources')->where('id', $id)->where('user_id', $userId)->update([
            'name' => $request->name, 'category' => $request->category,
            'slots' => $request->slots, 'available_dates' => $request->available_dates,
            'price' => $request->price, 'description' => $request->description,
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true]);
    }

    public function toggleResource($appType, $id)
    {
        $userId = $this->userId();
        $item = DB::table('app_resources')->where('id', $id)->where('user_id', $userId)->first();
        if (!$item) return response()->json(['error' => 'Not found'], 404);
        DB::table('app_resources')->where('id', $id)->update(['is_active' => !$item->is_active, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function deleteResource($appType, $id)
    {
        $userId = $this->userId();
        DB::table('app_resources')->where('id', $id)->where('user_id', $userId)->delete();
        return response()->json(['success' => true]);
    }

    public function listResources($appType)
    {
        $userId = $this->userId();
        $items = DB::table('app_resources')
            ->where('user_id', $userId)->where('app_type', $appType)->where('is_active', 1)
            ->orderBy('name')->get();
        return response()->json(['items' => $items]);
    }

    public function accessPage($appType)
    {
        $userId = $this->userId();
        $owner = \App\Models\User::findOrFail($userId);
        if (!array_key_exists($appType, self::catalog())) abort(404);

        $appInfo = self::catalog()[$appType];
        $config = self::appConfig($appType);

        $teamMembers = \App\Models\User::where('parent_user_id', $userId)->where('is_app_employee', 1)->get();

        $assignments = DB::table('app_assignments')
            ->where('user_id', $userId)->where('app_type', $appType)
            ->get()->keyBy('staff_user_id');

        $user = $owner;
        return view('user.app_access', compact('owner', 'user', 'appType', 'appInfo', 'config', 'teamMembers', 'assignments'));
    }

    public function toggleAccess(Request $request, $appType)
    {
        $userId = $this->userId();
        $staffId = $request->staff_user_id;
        $enabled = $request->enabled;

        if ($enabled) {
            DB::table('app_assignments')->updateOrInsert(
                ['user_id' => $userId, 'app_type' => $appType, 'staff_user_id' => $staffId],
                ['assigned_at' => now(), 'can_flow_builder' => 1, 'can_resources' => 1, 'can_records' => 1]
            );
        } else {
            DB::table('app_assignments')
                ->where('user_id', $userId)->where('app_type', $appType)->where('staff_user_id', $staffId)
                ->delete();
        }

        return response()->json(['success' => true]);
    }

    public function addEmployee(Request $request, $appType)
    {
        try {
            $userId = $this->userId();
            $owner = \App\Models\User::findOrFail($userId);

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $sub = DB::table('subscriptions')->where('user_id', $userId)->where('status', 'active')->where('expires_at', '>', now())->orderByDesc('created_at')->first();
            if ($sub) {
                $plan = DB::table('plans')->find($sub->plan_id);
                if ($plan) {
                    $currentCount = DB::table('users')->where('parent_user_id', $userId)->count();
                    if ($currentCount >= $plan->team_limit) {
                        return response()->json(['error' => "Your {$plan->name} plan allows only {$plan->team_limit} team members. Upgrade to add more."], 403);
                    }
                }
            }

            $member = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => 'user',
                'is_app_employee' => 1,
                'parent_user_id' => $userId,
                'organisation' => $owner->organisation,
                'phone_number_id' => $owner->phone_number_id,
                'access_token' => $owner->access_token,
                'business_account_id' => $owner->business_account_id,
                'mobile' => $owner->mobile,
                'is_verified' => true,
                'is_active' => true,
                'bot_status' => 'off',
            ]);

            // Grant default access to this app immediately
            DB::table('app_assignments')->updateOrInsert(
                ['user_id' => $userId, 'app_type' => $appType, 'staff_user_id' => $member->id],
                ['assigned_at' => now(), 'can_flow_builder' => 1, 'can_resources' => 1, 'can_records' => 1]
            );

            return response()->json(['success' => true, 'id' => $member->id, 'name' => $member->name, 'email' => $member->email]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateEmployee(Request $request, $appType, $id)
    {
        $userId = $this->userId();
        $member = \App\Models\User::where('id', $id)->where('parent_user_id', $userId)->where('is_app_employee', 1)->first();
        if (!$member) return response()->json(['error' => 'Employee not found'], 404);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $updateData = ['name' => $request->name, 'email' => $request->email];
        if (!empty($request->password)) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $member->update($updateData);

        return response()->json(['success' => true]);
    }

    public function deleteEmployee($appType, $id)
    {
        $userId = $this->userId();
        $member = \App\Models\User::where('id', $id)->where('parent_user_id', $userId)->where('is_app_employee', 1)->first();
        if (!$member) return response()->json(['error' => 'Employee not found'], 404);

        DB::table('app_assignments')->where('staff_user_id', $id)->where('user_id', $userId)->delete();
        $member->delete();

        return response()->json(['success' => true]);
    }

    public function updatePagePermission(Request $request, $appType)
    {
        $userId = $this->userId();
        $staffId = $request->staff_user_id;
        $page = $request->page; // 'flow_builder' | 'resources' | 'records'
        $enabled = $request->enabled ? 1 : 0;

        $column = match($page) {
            'flow_builder' => 'can_flow_builder',
            'resources' => 'can_resources',
            'records' => 'can_records',
            default => null,
        };
        if (!$column) return response()->json(['error' => 'Invalid page'], 400);

        DB::table('app_assignments')->updateOrInsert(
            ['user_id' => $userId, 'app_type' => $appType, 'staff_user_id' => $staffId],
            [$column => $enabled, 'assigned_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function toggleBot(Request $request)
    {
        $userId = $this->userId();
        $appType = $request->app_type;
        $enabled = $request->status === 'on' ? 1 : 0;

        DB::table('installed_apps')
            ->where('user_id', $userId)->where('app_type', $appType)
            ->update(['is_bot_active' => $enabled, 'updated_at' => now()]);

        return response()->json(['status' => $enabled ? 'on' : 'off']);
    }

    // ── Records page (Appointments for Hospital, Orders for others) ──
    public function records($appType)
    {
        $userId = $this->userId();
        $user = \App\Models\User::findOrFail($userId);
        if (!array_key_exists($appType, self::catalog())) abort(404);

        $config = self::appConfig($appType);
        $appInfo = self::catalog()[$appType];

        if ($config['is_time_based']) {
            $records = DB::table('app_appointments')->where('user_id', $userId)->where('app_type', $appType)
                ->orderByDesc('appointment_date')->orderBy('appointment_time')->get();
        } else {
            $records = DB::table('app_orders')->where('user_id', $userId)->where('app_type', $appType)
                ->orderByDesc('created_at')->get();
        }

        return view('user.app_records', compact('user', 'appType', 'records', 'appInfo', 'config'));
    }
}
