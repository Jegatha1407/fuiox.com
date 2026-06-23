<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    // Dashboard
    public function dashboard()
    {
        $users = User::where('role','user')->orderBy('name')->get();
        return view('admin.dashboard', compact('users'));
    }

    // Credential Requests
    public function credentialRequests()
    {
        $requests = DB::table('credential_requests')->join('users','credential_requests.user_id','=','users.id')->select('credential_requests.*','users.name as user_name','users.email as user_email','users.organisation as user_org')->orderByDesc('credential_requests.created_at')->get()->map(function($r) {
            $r->user = (object)['name'=>$r->user_name,'email'=>$r->user_email,'organisation'=>$r->user_org];
            return $r;
        });
        return view('admin.credential_requests', compact('requests'));
    }

    public function acceptRequest($id)
    {
        DB::table('credential_requests')->where('id',$id)->update(['status'=>'accepted','updated_at'=>now()]);
        return redirect()->route('admin.credential.requests')->with('success','Request accepted.');
    }

    public function rejectRequest($id)
    {
        DB::table('credential_requests')->where('id',$id)->update(['status'=>'rejected','updated_at'=>now()]);
        return redirect()->route('admin.credential.requests')->with('success','Request rejected.');
    }

    // Edit User
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.edit_user', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $data = [
            'name'               => $request->name,
            'email'              => $request->email,
            'organisation'       => $request->organisation,
            'mobile'             => $request->mobile,
            'is_active'          => $request->has('is_active') ? 1 : 0,
            'is_blocked'         => $request->has('is_blocked') ? 1 : 0,
            'free_trial_enabled' => $request->has('free_trial_enabled') ? 1 : 0,
            'trial_ends_at'      => $request->trial_ends_at ?: null,
            'updated_at'         => now(),
        ];
        if ($request->password) $data['password'] = Hash::make($request->password);
        DB::table('users')->where('id',$id)->update($data);
        return redirect()->route('admin.users.edit',$id)->with('success','✅ User updated successfully!');
    }

    public function toggleTrial(Request $request, $id)
    {
        $user = \App\Models\User::find($id);
        if (!$user) return response()->json(['error'=>'User not found'],404);

        $enabled = $request->enabled ? 1 : 0;

        if ($enabled) {
            // Check if currently in active free trial
            // Check if user has active free trial subscription
            $inTrial = DB::table('subscriptions')
                ->where('user_id', $id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->where(function($q){
                    $q->where('plan_name', 'like', '%trial%')
                      ->orWhere('amount', 0);
                })->exists();

            if ($inTrial) {
                return response()->json(['error'=>'User is already on an active free trial.','already_trial'=>true], 400);
            }

            // Check if on paid plan
            $hasPaidPlan = DB::table('subscriptions')
                ->where('user_id', $id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->where('amount', '>', 0)
                ->exists();

            if ($hasPaidPlan && !$request->confirmed) {
                return response()->json(['warning'=>'This user has an active paid plan. Are you sure you want to give them a free trial?','needs_confirm'=>true],200);
            }

            // Grant free trial
            $trialEnd = \Carbon\Carbon::now()->addDays(30);
            DB::table('users')->where('id',$id)->update([
                'free_trial_enabled' => 1,
                'trial_ends_at'      => $trialEnd,
                'free_trial_used'    => 1,
                'updated_at'         => now(),
            ]);
            // Add subscription record
            DB::table('subscriptions')->insert([
                'user_id'    => $id,
                'plan_id'    => DB::table('plans')->where('is_free_trial',1)->value('id') ?? 11,
                'status'     => 'active',
                'amount'     => 0,
                'plan_name'  => 'Free Trial',
                'expires_at' => $trialEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['success'=>true,'message'=>'Free trial granted for 30 days.']);
        } else {
            DB::table('users')->where('id',$id)->update(['free_trial_enabled'=>0,'updated_at'=>now()]);
            return response()->json(['success'=>true,'message'=>'Free trial disabled.']);
        }
    }

    public function toggleActive(Request $request, $id)
    {
        DB::table('users')->where('id',$id)->update(['is_active'=>$request->active,'updated_at'=>now()]);
        return response()->json(['success'=>true]);
    }

    public function toggleBlock(Request $request, $id)
    {
        DB::table('users')->where('id',$id)->update(['is_blocked'=>$request->blocked,'updated_at'=>now()]);
        return response()->json(['success'=>true]);
    }

    // Packages
    public function packages()
    {
        $plans = DB::table('plans')->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.packages', compact('plans'));
    }

    public function storePackage(Request $request)
    {
        $data = [
            'name'           => $request->name,
            'price'          => $request->price,
            'yearly_price'   => $request->yearly_price ?: null,
            'currency'       => $request->currency ?? 'INR',
            'messages_limit' => $request->messages_limit,
            'contacts_limit' => $request->contacts_limit,
            'team_limit'     => $request->team_limit,
            'sort_order'     => $request->sort_order ?? 0,
            'description'    => $request->description,
            'features'       => $request->features,
            'is_free_trial'  => $request->is_free_trial ? 1 : 0,
            'is_active'      => 1,
            'updated_at'     => now(),
        ];
        if ($request->id) {
            DB::table('plans')->where('id',$request->id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('plans')->insert($data);
        }
        return redirect()->route('admin.packages')->with('success','✅ Package saved!');
    }

    public function togglePackage($id)
    {
        $plan = DB::table('plans')->find($id);
        DB::table('plans')->where('id',$id)->update(['is_active'=>!$plan->is_active,'updated_at'=>now()]);
        return response()->json(['success'=>true]);
    }

    public function deletePackage($id)
    {
        DB::table('plans')->where('id',$id)->delete();
        return response()->json(['success'=>true]);
    }
}