<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;

class BillingController extends Controller
{
    private function userId(): int { return session('auth_user'); }

    // Billing page
    public function billing()
    {
        $userId  = $this->userId();
        $user    = User::findOrFail($userId);
        $plans   = DB::table('plans')->where('is_active',1)->orderBy('sort_order')->orderBy('price')->get();
        $invoices = DB::table('subscriptions')->where('user_id',$userId)->orderByDesc('created_at')->get();
        $subscription = DB::table('subscriptions')->where('user_id',$userId)->where('status','active')->where('expires_at','>',now())->orderByDesc('created_at')->first();
        $inTrial  = $user->free_trial_enabled && $user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isFuture();
        $trialDays = $inTrial ? Carbon::now()->diffInDays($user->trial_ends_at) : 0;
        return view('user.billing', compact('user','plans','invoices','subscription','inTrial','trialDays'));
    }

    // Plans JSON
    public function plans()
    {
        $plans = DB::table('plans')->where('is_active',1)->orderBy('sort_order')->orderBy('price')->get();
        return response()->json(['plans'=>$plans]);
    }

    // Current subscription JSON
    public function current()
    {
        $userId = $this->userId();
        $sub    = DB::table('subscriptions')->where('user_id',$userId)->where('status','active')->where('expires_at','>',now())->orderByDesc('created_at')->first();
        if (!$sub) return response()->json(['subscription'=>null,'plan'=>null,'status'=>'none']);
        $plan = DB::table('plans')->find($sub->plan_id);
        return response()->json(['subscription'=>$sub,'plan'=>$plan,'status'=>$sub->status,'expires_at'=>$sub->expires_at,'days_left'=>max(0,Carbon::now()->diffInDays($sub->expires_at,false))]);
    }

    // Invoices JSON
    public function invoices()
    {
        $invoices = DB::table('subscriptions')->where('user_id',$this->userId())->orderByDesc('created_at')->get();
        return response()->json(['invoices'=>$invoices]);
    }

    // Create Razorpay order
    public function createOrder(Request $request)
    {
        $request->validate(['plan_id'=>'required|integer']);
        $userId = $this->userId();
        $plan   = DB::table('plans')->find($request->plan_id);
        if (!$plan) return response()->json(['error'=>'Plan not found'],404);

        // Free trial
        if ($plan->is_free_trial || $plan->price == 0) {
            // Check if already used free trial
            $usedTrial = DB::table('subscriptions')
                ->where('user_id', $userId)
                ->where('plan_id', $plan->id)
                ->exists();
            if ($usedTrial) return response()->json(['error'=>'You have already used the free trial.'],400);

            // Activate free trial
            DB::table('subscriptions')->insert([
                'user_id'    => $userId,
                'plan_id'    => $plan->id,
                'status'     => 'active',
                'amount'     => 0,
                'plan_name'  => $plan->name,
                'expires_at' => \Carbon\Carbon::now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Update user trial fields
            DB::table('users')->where('id',$userId)->update([
                'free_trial_enabled' => 1,
                'trial_ends_at'      => \Carbon\Carbon::now()->addDays(30),
                'free_trial_used'    => 1,
            ]);
            return response()->json(['success'=>true,'free_trial'=>true,'message'=>'Free trial activated! Valid for 30 days.']);
        }
        $plan = DB::table('plans')->find($request->plan_id);
        if (!$plan) return response()->json(['error'=>'Plan not found'],404);

        $razorpayKey    = env('RAZORPAY_KEY_ID');
        $razorpaySecret = env('RAZORPAY_KEY_SECRET');
        if (!$razorpayKey || !$razorpaySecret) return response()->json(['error'=>'Payment gateway not configured.'],400);

        $billingCycle = $request->billing_cycle ?? 'monthly';
        $price        = $billingCycle === 'yearly' ? ($plan->yearly_price ?? $plan->price * 12) : $plan->price;
        $amountPaise  = $price * 100;

        $response = Http::withBasicAuth($razorpayKey,$razorpaySecret)->post('https://api.razorpay.com/v1/orders',[
            'amount'   => $amountPaise,
            'currency' => 'INR',
            'receipt'  => 'order_'.$this->userId().'_'.time(),
            'notes'    => ['plan'=>$plan->name,'user_id'=>$this->userId()],
        ]);

        if ($response->failed()) { Log::error('Razorpay order failed',['body'=>$response->json()]); return response()->json(['error'=>'Failed to create payment order.'],500); }

        return response()->json(['order_id'=>$response->json('id'),'amount'=>$amountPaise,'currency'=>'INR','plan'=>$plan,'key_id'=>$razorpayKey]);
    }

    // Verify payment
    public function verifyPayment(Request $request)
    {
        $request->validate(['plan_id'=>'required|integer','razorpay_order_id'=>'required','razorpay_payment_id'=>'required','razorpay_signature'=>'required']);
        $userId = $this->userId();
        $plan   = DB::table('plans')->find($request->plan_id);
        if (!$plan) return response()->json(['error'=>'Plan not found'],404);

        // Handle free trial
        if ($plan->is_free_trial) {
            $user = User::findOrFail($userId);
            if ($user->free_trial_used) return response()->json(['error'=>'Free trial already used. Please choose a paid plan.'],403);
            DB::table('subscriptions')->where('user_id',$userId)->where('status','active')->update(['status'=>'cancelled','updated_at'=>now()]);
            DB::table('subscriptions')->insert(['user_id'=>$userId,'plan_id'=>$plan->id,'plan_name'=>$plan->name,'billing_cycle'=>'monthly','status'=>'active','started_at'=>now(),'expires_at'=>now()->addMonth(),'amount'=>0,'razorpay_order_id'=>'free','razorpay_payment_id'=>'free_trial','created_at'=>now(),'updated_at'=>now()]);
            DB::table('users')->where('id',$userId)->update(['free_trial_used'=>1,'free_trial_enabled'=>1,'trial_ends_at'=>now()->addMonth()]);
            return response()->json(['success'=>true,'plan'=>$plan->name,'free_trial'=>true]);
        }

        // Verify signature
        if ($request->razorpay_order_id !== 'free') {
            $secret       = env('RAZORPAY_KEY_SECRET');
            $generatedSig = hash_hmac('sha256',$request->razorpay_order_id.'|'.$request->razorpay_payment_id,$secret);
            if ($generatedSig !== $request->razorpay_signature) { Log::error('Razorpay signature mismatch'); return response()->json(['error'=>'Payment verification failed.'],400); }
        }

        $billingCycle = $request->billing_cycle ?? 'monthly';
        $expiresAt    = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();
        $amount       = $billingCycle === 'yearly' ? ($plan->yearly_price ?? $plan->price * 12) : $plan->price;

        DB::table('subscriptions')->where('user_id',$userId)->where('status','active')->update(['status'=>'cancelled','updated_at'=>now()]);
        DB::table('subscriptions')->insert(['user_id'=>$userId,'plan_id'=>$plan->id,'plan_name'=>$plan->name,'billing_cycle'=>$billingCycle,'status'=>'active','started_at'=>now(),'expires_at'=>$expiresAt,'razorpay_order_id'=>$request->razorpay_order_id,'razorpay_payment_id'=>$request->razorpay_payment_id,'amount'=>$amount,'created_at'=>now(),'updated_at'=>now()]);
        Log::info('Subscription activated',['user_id'=>$userId,'plan'=>$plan->name]);
        return response()->json(['success'=>true,'plan'=>$plan->name]);
    }

    // Cancel
    public function cancel()
    {
        DB::table('subscriptions')->where('user_id',$this->userId())->where('status','active')->update(['status'=>'cancelled','updated_at'=>now()]);
        return redirect()->route('billing')->with('success','Subscription cancelled.');
    }
}