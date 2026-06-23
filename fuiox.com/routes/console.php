<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process delayed flow steps every minute
Schedule::call(function () {
    $pending = DB::table('flow_contacts')
        ->where('status', 'active')
        ->whereNotNull('next_execution_at')
        ->where('next_execution_at', '<=', now())
        ->get();

    foreach ($pending as $fc) {
        if ($fc->current_node_id) {
            // Clear next_execution_at first
            DB::table('flow_contacts')
                ->where('id', $fc->id)
                ->update(['next_execution_at' => null]);
            App\Http\Controllers\FlowController::executeFlowStep(
                $fc->user_id, $fc->wa_id, $fc->flow_id, $fc->current_node_id
            );
        }
    }
})->everyMinute();

// Check expired subscriptions daily at midnight
Schedule::call(function () {
    $expired = DB::table('subscriptions')
        ->where('status', 'active')
        ->where('expires_at', '<', now())
        ->get();
    foreach ($expired as $sub) {
        DB::table('subscriptions')->where('id', $sub->id)->update(['status' => 'expired', 'updated_at' => now()]);
        Log::info('Subscription expired', ['user_id' => $sub->user_id, 'plan_id' => $sub->plan_id]);
    }
    Log::info('Subscription check done', ['expired' => count($expired)]);
})->dailyAt('00:00');

// Send expiry warning 1 day before plan/trial ends
Schedule::call(function () {
    $tomorrow = now()->addDay();
    $expiring = DB::table('subscriptions')
        ->where('status', 'active')
        ->whereDate('expires_at', $tomorrow->toDateString())
        ->get();

    foreach ($expiring as $sub) {
        $user = DB::table('users')->where('id', $sub->user_id)->first();
        if (!$user || !$user->access_token || !$user->phone_number_id || !$user->mobile) continue;

        $planName = $sub->plan_name ?? 'your plan';
        $message  = "⚠️ *Fuiox Alert*\n\nHi {$user->name},\n\nYour *{$planName}* expires tomorrow ({$tomorrow->format('d M Y')}).\n\nRenew now to avoid interruption: " . url('/billing') . "\n\nThank you,\nFuiox Technologies";

        try {
            \Illuminate\Support\Facades\Http::withToken($user->access_token)
                ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'   => preg_replace('/[^0-9]/', '', $user->mobile),
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);
            Log::info('Expiry warning sent', ['user_id' => $sub->user_id, 'plan' => $planName]);
        } catch (\Exception $e) {
            Log::error('Expiry warning failed', ['user_id' => $sub->user_id, 'error' => $e->getMessage()]);
        }

        // Also add in-app notification
        DB::table('notifications')->insert([
            'user_id'    => $sub->user_id,
            'type'       => 'billing',
            'title'      => 'Plan Expiring Tomorrow',
            'message'    => "Your {$planName} expires tomorrow. Renew to keep access.",
            'is_read'    => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    Log::info('Expiry warnings sent', ['count' => count($expiring)]);
})->dailyAt('09:00');


Illuminate\Support\Facades\Schedule::command('campaigns:send-scheduled')->everyMinute();
