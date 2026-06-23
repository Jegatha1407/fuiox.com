<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TeamController extends Controller
{
    private function userId() { return session('auth_user'); }

    
   public function team()
{
    $userId  = $this->userId();
    $user    = \App\Models\User::findOrFail($userId);
    $members = DB::table('users')
        ->where('parent_user_id', $userId)
        ->where('role', 'user')
        ->select('id','name','email','team_role','is_active','is_online','last_seen','created_at')
        ->orderBy('name')
        ->get();

    $stats = [
        'total'  => $members->count(),
        'online' => $members->where('is_online', 1)->count(),
        'admins' => $members->where('team_role', 'admin')->count(),
        'agents' => $members->where('team_role', 'agent')->count(),
    ];

    return view('user.team', compact('user', 'members', 'stats'));
}
    public function list()
    {
        $userId  = $this->userId();
        $members = DB::table("users")
            ->where("parent_user_id", $userId)
            ->where("role", "user")
            ->where("is_app_employee", 0)
            ->select("id","name","email","team_role","is_active","is_online","last_seen","created_at")
            ->orderBy("name")->get();
        return response()->json(["members" => $members]);
    }

    // ── INVITE / ADD TEAM MEMBER ──────────────────
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'      => 'required|string|max:100',
                'email'     => 'required|email|unique:users,email',
                'team_role' => 'required|in:admin,manager,agent',
                'password'  => 'required|string|min:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->flatten()->first();
            return response()->json(['error' => $errors], 422);
        }

        try {
            $userId = $this->userId();
            $owner  = User::findOrFail($userId);

            // Check team member limit from active plan
            $sub = DB::table('subscriptions')->where('user_id',$userId)->where('status','active')->where('expires_at','>',now())->orderByDesc('created_at')->first();
            if ($sub) {
                $plan = DB::table('plans')->find($sub->plan_id);
                if ($plan) {
                    $currentCount = DB::table('users')->where('parent_user_id',$userId)->count();
                    if ($currentCount >= $plan->team_limit) {
                        return response()->json(['error' => "Your {$plan->name} plan allows only {$plan->team_limit} team members. Upgrade to add more."], 403);
                    }
                }
            }

            $member = User::create([
                'name'                => $request->name,
                'email'               => $request->email,
                'password'            => Hash::make($request->password),
                'role'                => 'user',
                'team_role'           => $request->team_role,
                'parent_user_id'      => $userId,
                'organisation'        => $owner->organisation,
                'phone_number_id'     => $owner->phone_number_id,
                'access_token'        => $owner->access_token,
                'business_account_id' => $owner->business_account_id,
                'mobile'              => $owner->mobile,
                'is_verified'         => true,
                'is_active'           => true,
                'bot_status'          => 'off',
            ]);

            return response()->json(['success' => true, 'id' => $member->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── UPDATE TEAM MEMBER ────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'team_role' => 'required|in:admin,manager,agent',
            'is_active' => 'required|boolean',
        ]);

        DB::table('users')
            ->where('id', $id)
            ->where('parent_user_id', $this->userId())
            ->update([
                'team_role' => $request->team_role,
                'is_active' => $request->is_active,
                'updated_at'=> now(),
            ]);

        return response()->json(['success' => true]);
    }

    // ── REMOVE TEAM MEMBER ────────────────────────
    public function destroy($id)
    {
        DB::table('users')
            ->where('id', $id)
            ->where('parent_user_id', $this->userId())
            ->delete();

        return response()->json(['success' => true]);
    }

    // ── STATS ─────────────────────────────────────
    public function stats()
    {
        $userId  = $this->userId();
        $total   = DB::table('users')->where('parent_user_id', $userId)->count();
        $online  = DB::table('users')->where('parent_user_id', $userId)->where('is_online', true)->count();
        $agents  = DB::table('users')->where('parent_user_id', $userId)->where('team_role', 'agent')->count();
        $admins  = DB::table('users')->where('parent_user_id', $userId)->where('team_role', 'admin')->count();

        return response()->json(compact('total', 'online', 'agents', 'admins'));
    }

    public function teamDashboard()
    {
        $userId  = $this->userId();
        $user    = \App\Models\User::findOrFail($userId);
        $ownerId = $user->parent_user_id ?? $userId;
        $role    = $user->team_role;

        $phones = [];
        if ($role === 'agent') {
            $phones = DB::table('conversations')
                ->where('user_id', $ownerId)->where('assigned_to', $userId)
                ->pluck('wa_id')->toArray();
        }

        $totalMsgs     = DB::table('messages')->where('user_id', $ownerId)->when($role==='agent', fn($q)=>$q->whereIn('wa_id',$phones))->count();
        $sentToday     = DB::table('messages')->where('user_id', $ownerId)->where('type','outgoing')->where('created_at','>=',\Carbon\Carbon::today())->when($role==='agent', fn($q)=>$q->whereIn('wa_id',$phones))->count();
        $receivedToday = DB::table('messages')->where('user_id', $ownerId)->where('type','incoming')->where('created_at','>=',\Carbon\Carbon::today())->when($role==='agent', fn($q)=>$q->whereIn('wa_id',$phones))->count();
        $unread        = DB::table('messages')->where('user_id', $ownerId)->where('type','incoming')->where('read',0)->when($role==='agent', fn($q)=>$q->whereIn('wa_id',$phones))->count();
        $totalChats    = DB::table('conversations')->where('user_id', $ownerId)->when($role==='agent', fn($q)=>$q->where('assigned_to',$userId))->count();

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $chartData[] = [
                'date'     => $date->format('D'),
                'sent'     => DB::table('messages')->where('user_id',$ownerId)->where('type','outgoing')->whereDate('created_at',$date)->when($role==='agent',fn($q)=>$q->whereIn('wa_id',$phones))->count(),
                'received' => DB::table('messages')->where('user_id',$ownerId)->where('type','incoming')->whereDate('created_at',$date)->when($role==='agent',fn($q)=>$q->whereIn('wa_id',$phones))->count(),
            ];
        }

        $recentConvs = DB::table('conversations')
            ->where('user_id', $ownerId)
            ->when($role==='agent', fn($q)=>$q->where('assigned_to',$userId))
            ->orderByDesc('updated_at')->limit(5)->get();

        $stats = compact('totalMsgs','sentToday','receivedToday','unread','totalChats');
        return view('user.team_dashboard', compact('user','stats','chartData','recentConvs','role'));
    }

    public function changePasswordPage()
    {
        $user = \App\Models\User::findOrFail($this->userId());
        return view('user.team_change_password', compact('user'));
    }

    public function changePassword(\Illuminate\Http\Request $request)
    {
        $request->validate(['current_password'=>'required','password'=>'required|min:6|confirmed']);
        $user = \App\Models\User::findOrFail($this->userId());
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password'=>'Current password is incorrect.']);
        }
        $user->update(['password'=>\Illuminate\Support\Facades\Hash::make($request->password)]);
        return back()->with('success','Password changed successfully!');
    }
}
