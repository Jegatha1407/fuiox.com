<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanHelper
{
    public static function getActivePlan($userId)
    {
        $sub = DB::table('subscriptions')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();
        if (!$sub) return null;
        return DB::table('plans')->find($sub->plan_id);
    }

    public static function isExpired($userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        // Check free trial
        if ($user->free_trial_enabled && $user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isFuture()) {
            return false;
        }
        // Check active subscription
        $sub = DB::table('subscriptions')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
        return !$sub;
    }

    public static function getLimits($userId)
    {
        $plan = self::getActivePlan($userId);
        if ($plan) {
            return [
                'contacts'  => $plan->contacts_limit,
                'messages'  => $plan->messages_limit,
                'team'      => $plan->team_limit,
                'plan_name' => $plan->name,
            ];
        }
        // Free trial limits
        $user = DB::table('users')->where('id', $userId)->first();
        if ($user->free_trial_enabled && $user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isFuture()) {
            return ['contacts'=>500,'messages'=>1000,'team'=>2,'plan_name'=>'Free Trial'];
        }
        return null;
    }
}
