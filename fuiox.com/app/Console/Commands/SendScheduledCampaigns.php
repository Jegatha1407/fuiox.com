<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;

class SendScheduledCampaigns extends Command
{
    protected $signature   = 'campaigns:send-scheduled';
    protected $description = 'Send scheduled campaigns that are due';

    public function handle()
    {
        $now = Carbon::now();
        $campaigns = DB::table('campaigns')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();

        foreach ($campaigns as $camp) {
            $user = User::find($camp->user_id);
            if (!$user || !$user->access_token) continue;

            $phones = DB::table('campaign_recipients')
                ->where('campaign_id', $camp->id)
                ->pluck('phone')->toArray();

            if (empty($phones)) {
                DB::table('campaigns')->where('id', $camp->id)
                    ->update(['status' => 'failed', 'updated_at' => now()]);
                continue;
            }

            $sent = 0; $failed = 0; $failedNumbers = [];
            $tpl = [
                'name'     => $camp->template_name,
                'language' => ['code' => $camp->language_code ?? 'en_US'],
            ];

            foreach ($phones as $phone) {
                $resp = Http::withToken($user->access_token)
                    ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to'                => $phone,
                        'type'              => 'template',
                        'template'          => $tpl,
                    ]);
                if ($resp->successful()) {
                    $sent++;
                } else {
                    $failed++;
                    $failedNumbers[] = $phone . ' (' . ($resp->json('error.message') ?? 'failed') . ')';
                }
            }

            DB::table('campaigns')->where('id', $camp->id)->update([
                'sent'           => $sent,
                'failed'         => $failed,
                'failed_numbers' => implode("\n", $failedNumbers) ?: null,
                'status'         => $sent > 0 ? 'completed' : 'failed',
                'completed_at'   => now(),
                'updated_at'     => now(),
            ]);

            Log::info("Scheduled campaign {$camp->id} sent: {$sent}, failed: {$failed}");
        }

        $this->info('Done: ' . count($campaigns) . ' campaigns processed.');
    }
}
