<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class AiChatController extends Controller
{
    const WINDOW_SIZE = 12;      
    const SUMMARIZE_EVERY = 20;    

    private function userId(): int { return session('auth_user'); }

    // ── Process incoming message with AI (called from webhook) ──
    public static function processMessage($userId, $fromPhone, $incomingText, $channel = 'whatsapp')
    {
        try {
            $user = \App\Models\User::find($userId);
            if (!$user) return;

            $settings = DB::table('ai_settings')->where('user_id', $userId)->first();
            if (!$settings || !$settings->claude_api_key) {
                Log::warning('AI reply skipped — no API key configured', ['user_id' => $userId]);
                return;
            }

            $apiKey = self::decryptKey($settings->claude_api_key);
            if (!$apiKey) return;

            // ── Get or create conversation state — new conversations always default to AI enabled ──
            $state = DB::table('ai_conversation_state')
                ->where('user_id', $userId)->where('wa_id', $fromPhone)->where('channel', $channel)
                ->first();

            $isNewConversation = !$state;

            if (!$state) {
                DB::table('ai_conversation_state')->insert([
                    'user_id' => $userId, 'wa_id' => $fromPhone, 'channel' => $channel,
                    'ai_enabled' => 1, 'summary' => null, 'message_count_since_summary' => 0,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $state = DB::table('ai_conversation_state')
                    ->where('user_id', $userId)->where('wa_id', $fromPhone)->where('channel', $channel)
                    ->first();
            }

            
            if (!$state->ai_enabled) {
                Log::info('AI reply skipped — disabled for this conversation', ['user_id' => $userId, 'wa_id' => $fromPhone]);
                return;
            }

            $systemPrompt = self::buildSystemPrompt($settings, $user);

           
            $recentMsgs = DB::table('messages')
                ->where('user_id', $userId)->where('wa_id', $fromPhone)->where('channel', $channel)
                ->orderByDesc('created_at')
                ->limit(self::WINDOW_SIZE)
                ->get()->reverse()->values();

            $messages = [];
            foreach ($recentMsgs as $msg) {
                if ($msg->message === $incomingText && $msg->type === 'incoming') continue;
                $messages[] = [
                    'role' => $msg->type === 'incoming' ? 'user' : 'assistant',
                    'content' => self::trimContent($msg->message, $msg->media_type ?? null),
                ];
            }
            $messages[] = ['role' => 'user', 'content' => $incomingText];

            
            $finalSystemPrompt = $systemPrompt;
            if (!empty($state->summary)) {
                $finalSystemPrompt .= "\n\n--- Conversation context so far (summary of earlier messages) ---\n" . $state->summary;
            }

         
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 500,
                'system' => $finalSystemPrompt,
                'messages' => $messages,
            ]);

            if ($response->failed()) {
                Log::error('Claude API failed', ['user_id' => $userId, 'resp' => $response->json()]);
                return;
            }

            $aiReply = $response->json('content.0.text');
            if (!$aiReply) return;

          
            self::sendReply($user, $fromPhone, $aiReply, $channel);

            DB::table('messages')->insert([
                'user_id' => $userId, 'wa_id' => $fromPhone, 'message' => $aiReply,
                'type' => 'outgoing', 'status' => 'sent', 'channel' => $channel,
                'read' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);

            $newCount = ($state->message_count_since_summary ?? 0) + 1;
            if ($newCount >= self::SUMMARIZE_EVERY) {
                self::summarizeConversation($apiKey, $userId, $fromPhone, $channel, $state->summary);
                DB::table('ai_conversation_state')
                    ->where('id', $state->id)
                    ->update(['message_count_since_summary' => 0, 'last_summarized_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('ai_conversation_state')
                    ->where('id', $state->id)
                    ->update(['message_count_since_summary' => $newCount, 'updated_at' => now()]);
            }

            Log::info('AI reply sent', ['user_id' => $userId, 'to' => $fromPhone, 'channel' => $channel]);

        } catch (\Exception $e) {
            Log::error('AI chat error: ' . $e->getMessage());
        }
    }

    private static function summarizeConversation($apiKey, $userId, $fromPhone, $channel, $existingSummary)
    {
        try {
            $older = DB::table('messages')
                ->where('user_id', $userId)->where('wa_id', $fromPhone)->where('channel', $channel)
                ->orderByDesc('created_at')
                ->skip(self::WINDOW_SIZE)
                ->limit(self::SUMMARIZE_EVERY)
                ->get()->reverse()->values();

            if ($older->isEmpty()) return;

            $transcript = $older->map(function ($m) {
                $role = $m->type === 'incoming' ? 'Customer' : 'Assistant';
                return "{$role}: " . self::trimContent($m->message, $m->media_type ?? null);
            })->implode("\n");

            $prompt = "Summarize the key facts from this conversation segment in 3-5 short sentences. Include: customer's name if mentioned, what they wanted, any commitments or info given, and current unresolved topic. Be concise — this summary will be reused as context for future replies.";
            if ($existingSummary) {
                $prompt .= "\n\nExisting summary so far:\n{$existingSummary}\n\nUpdate it by merging in the new segment below, keeping it equally short.";
            }
            $prompt .= "\n\nConversation segment:\n{$transcript}";

            $resp = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 250,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

            if ($resp->failed()) {
                Log::error('Summarization failed', ['user_id' => $userId]);
                return;
            }

            $summary = $resp->json('content.0.text');
            if (!$summary) return;

            DB::table('ai_conversation_state')
                ->where('user_id', $userId)->where('wa_id', $fromPhone)->where('channel', $channel)
                ->update(['summary' => $summary, 'updated_at' => now()]);

            Log::info('Conversation summarized', ['user_id' => $userId, 'wa_id' => $fromPhone]);

        } catch (\Exception $e) {
            Log::error('Summarize error: ' . $e->getMessage());
        }
    }

    private static function trimContent($text, $mediaType = null)
    {
        if ($mediaType && $mediaType !== 'text') {
            return "[Customer sent a {$mediaType}]";
        }
        $text = trim(preg_replace('/\s+/', ' ', (string)$text));
        return mb_strlen($text) > 1000 ? mb_substr($text, 0, 1000) . '…' : $text;
    }

    private static function buildSystemPrompt($settings, $user)
    {
        $businessName = $settings->business_name ?? $user->organisation ?? 'Our Business';
        $businessDesc = $settings->business_description ?? 'We are a business that helps customers with their needs.';
        $tone = $settings->tone ?? 'friendly and professional';
        $language = $settings->language ?? 'English';
        $customPrompt = $settings->custom_prompt ?? '';

        return "You are an AI customer support assistant for {$businessName}.
{$businessDesc}

Your tone should be {$tone}.
Always respond in {$language}.
Keep responses concise and helpful — maximum 3-4 sentences.
Do not mention that you are an AI unless directly asked.
If you cannot answer something, politely say you will connect them with a human agent.

{$customPrompt}";
    }

    private static function sendReply($user, $to, $text, $channel)
    {
        if ($channel === 'whatsapp') {
            Http::withToken($user->access_token)
                ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                    'messaging_product' => 'whatsapp', 'to' => $to,
                    'type' => 'text', 'text' => ['body' => $text],
                ]);
        } elseif ($channel === 'messenger') {
            $conn = DB::table('channel_connections')->where('user_id', $user->id)->where('channel', 'messenger')->where('is_active', 1)->first();
            if ($conn) {
                Http::withToken($conn->access_token)
                    ->post("https://graph.facebook.com/v19.0/{$conn->page_id}/messages", [
                        'recipient' => ['id' => $to], 'message' => ['text' => $text],
                    ]);
            }
        } elseif ($channel === 'instagram') {
            $conn = DB::table('channel_connections')->where('user_id', $user->id)->where('channel', 'instagram')->where('is_active', 1)->first();
            if ($conn) {
                Http::withToken($conn->access_token)
                    ->post("https://graph.facebook.com/v19.0/{$conn->page_id}/messages", [
                        'recipient' => ['id' => $to], 'message' => ['text' => $text],
                    ]);
            }
        } elseif ($channel === 'telegram') {
            $conn = DB::table('channel_connections')->where('user_id', $user->id)->where('channel', 'telegram')->where('is_active', 1)->first();
            if ($conn) {
                Http::post("https://api.telegram.org/bot{$conn->bot_token}/sendMessage", [
                    'chat_id' => $to, 'text' => $text,
                ]);
            }
        }
    }

    private static function decryptKey($encrypted)
    {
        try { return Crypt::decryptString($encrypted); }
        catch (\Exception $e) { Log::error('Key decrypt failed: ' . $e->getMessage()); return null; }
    }

    public static function decryptKeyPublic($encrypted)
    {
        return self::decryptKey($encrypted);
    }

    // ── AI Settings page ──
    public function settings()
    {
        $userId = $this->userId();
        $user = \App\Models\User::findOrFail($userId);
        $settings = DB::table('ai_settings')->where('user_id', $userId)->first();
        return view('user.ai_settings', compact('user', 'settings'));
    }

    // ── Save AI Settings (including API key validation) ──
    public function saveSettings(Request $request)
    {
        $userId = $this->userId();
        $languages = $request->input('languages', ['English']);
        if (!is_array($languages)) $languages = [$languages];

        $data = [
            'business_name'        => $request->business_name,
            'business_description' => $request->business_description,
            'purpose_type'          => $request->purpose_type ?? 'service',
            'sales_details'         => $request->sales_details,
            'service_details'       => $request->service_details,
            'phone_number'          => $request->phone_number,
            'facebook_link'         => $request->facebook_link,
            'instagram_link'        => $request->instagram_link,
            'google_business_link'  => $request->google_business_link,
            'tone'                  => $request->tone ?? 'friendly and professional',
            'languages'             => json_encode($languages),
            'custom_prompt'         => $request->custom_prompt,
            'updated_at'            => now(),
        ];

        // Only update key if a new one was actually typed (not the masked placeholder)
        if ($request->filled('claude_api_key') && !str_starts_with($request->claude_api_key, '••••')) {
            $rawKey = trim($request->claude_api_key);
            $valid = $this->testKey($rawKey);
            $data['claude_api_key'] = Crypt::encryptString($rawKey);
            $data['api_key_valid'] = $valid ? 1 : 0;
            $data['api_key_last4'] = substr($rawKey, -4);

            if (!$valid) {
                return redirect()->route('ai.settings')->withErrors(['claude_api_key' => '❌ This API key is invalid. Settings not saved with this key — please check and try again.']);
            }
        }

        DB::table('ai_settings')->updateOrInsert(['user_id' => $userId], $data);
        return redirect()->route('ai.settings')->with('success', '✅ AI settings saved!');
    }

    // ── Validate a Claude API key with a minimal real call ──
    private function testKey($apiKey)
    {
        try {
            $resp = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 10,
                'messages' => [['role' => 'user', 'content' => 'Hi']],
            ]);
            return $resp->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    // ── Test AI (uses saved key) ──
    public function test(Request $request)
    {
        $userId = $this->userId();
        $message = $request->message ?? 'Hello';
        $user = \App\Models\User::findOrFail($userId);
        $settings = DB::table('ai_settings')->where('user_id', $userId)->first();

        if (!$settings || !$settings->claude_api_key) {
            return response()->json(['error' => 'Please save a valid Claude API key first.']);
        }

        $apiKey = self::decryptKey($settings->claude_api_key);
        if (!$apiKey) {
            return response()->json(['error' => 'Could not read saved API key. Please re-enter it.']);
        }

        $systemPrompt = self::buildSystemPrompt($settings, $user);

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-sonnet-4-6',
            'max_tokens' => 300,
            'system' => $systemPrompt,
            'messages' => [['role' => 'user', 'content' => $message]],
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'AI failed: ' . ($response->json()['error']['message'] ?? 'Unknown error')]);
        }

        return response()->json(['reply' => $response->json('content.0.text')]);
    }

    public function toggleGlobal(Request $request)
    {
        $userId = $this->userId();
        $enabled = $request->status === 'on' ? 1 : 0;
        DB::table('ai_conversation_state')
            ->where('user_id', $userId)
            ->update(['ai_enabled' => $enabled, 'updated_at' => now()]);
        return response()->json(['status' => $enabled ? 'on' : 'off', 'applied_to' => 'all_conversations']);
    }

    public function toggleConversation(Request $request)
    {
        $userId = $this->userId();
        $waId = $request->wa_id;
        $channel = $request->channel ?? 'whatsapp';
        $enabled = $request->status === 'on' ? 1 : 0;
        DB::table('ai_conversation_state')->updateOrInsert(
            ['user_id' => $userId, 'wa_id' => $waId, 'channel' => $channel],
            ['ai_enabled' => $enabled, 'updated_at' => now()]
        );
        return response()->json(['status' => $enabled ? 'on' : 'off', 'wa_id' => $waId]);
    }

    public function getConversationState(Request $request)
    {
        $userId = $this->userId();
        $waId = $request->wa_id;
        $channel = $request->channel ?? 'whatsapp';
        $state = DB::table('ai_conversation_state')
            ->where('user_id', $userId)->where('wa_id', $waId)->where('channel', $channel)
            ->first();
        return response()->json(['ai_enabled' => $state ? (bool)$state->ai_enabled : true]);
    }
}
