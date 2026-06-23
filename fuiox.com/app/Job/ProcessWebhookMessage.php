<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Message;

class ProcessWebhookMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int    $tries   = 3;
    public int    $timeout = 60;

    public function __construct(
        public readonly array $payload
    ) {}

    public function handle(): void
    {
        $p          = $this->payload;
        $user       = User::find($p['user_id']);
        if (!$user) return;

        $messageId  = $p['message_id'];
        $from       = $p['from'];
        $msgType    = $p['type'];
        $replyToId  = $p['reply_to_id'] ?? null;

        // Duplicate guard (job may retry)
        if (Message::where('meta_message_id', $messageId)->exists()) {
            Log::info('Job: duplicate skipped', ['id' => $messageId]);
            return;
        }

        $text      = $p['text'] ?? '';
        $mediaData = [];

        // Fetch media info from Meta (done inside the job, not in the webhook request)
        if (!empty($p['media_id'])) {
            $info = Http::withHeaders([
                'Authorization' => 'Bearer ' . $user->access_token,
                'User-Agent'    => 'curl/7.64.1',
            ])->get("https://graph.facebook.com/v19.0/{$p['media_id']}");

            if ($info->successful()) {
                $mediaData = [
                    'media_type'      => $p['media_type']      ?? null,
                    'media_id'        => $p['media_id'],
                    'media_caption'   => $p['media_caption']   ?? null,
                    'media_filename'  => $p['media_filename']  ?? null,
                    'media_mime_type' => $p['media_mime_type'] ?? null,
                    'media_size'      => $info->json('file_size') ?? null,
                ];
            }
        }

        $saved = Message::create(array_merge([
            'user_id'         => $user->id,
            'wa_id'           => $from,
            'message'         => $text,
            'type'            => 'incoming',
            'status'          => 'received',
            'meta_message_id' => $messageId,
            'reply_to_id'     => $replyToId,
        ], $mediaData));

        Log::info('Job: message saved', ['id' => $saved->id, 'from' => $from]);

        // AI auto-reply (only for text, only if bot is ON)
        if (($user->bot_status ?? 'off') === 'on' && $msgType === 'text' && !empty($text)) {
            $this->sendAiReply($user, $from, $text);
        }
    }

    private function sendAiReply(User $user, string $to, string $text): void
    {
        $openaiKey = config('services.openai.key', env('OPENAI_API_KEY'));
        if (!$openaiKey || str_contains($openaiKey, 'your-openai-key')) return;

        try {
            $aiResp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openaiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model'      => 'gpt-3.5-turbo',
                'messages'   => [
                    ['role' => 'system', 'content' => 'You are a helpful customer support assistant for ' . ($user->organisation ?? 'our company') . '. Keep replies short and professional.'],
                    ['role' => 'user',   'content' => $text],
                ],
                'max_tokens' => 200,
            ]);

            if (!$aiResp->successful()) return;

            $reply = trim($aiResp->json('choices.0.message.content') ?? '');
            if (!$reply) return;

            $sendResp = Http::withToken($user->access_token)
                ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => ['body' => $reply],
                ]);

            if ($sendResp->successful()) {
                Message::create([
                    'user_id'         => $user->id,
                    'wa_id'           => $to,
                    'message'         => $reply,
                    'meta_message_id' => $sendResp->json('messages.0.id'),
                    'type'            => 'outgoing',
                    'status'          => 'sent',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('AI reply failed: ' . $e->getMessage());
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessWebhookMessage job failed', [
            'message_id' => $this->payload['message_id'] ?? null,
            'error'      => $e->getMessage(),
        ]);
    }
}