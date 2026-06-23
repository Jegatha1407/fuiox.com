<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessengerController extends Controller
{
    private function userId(): int { return session('auth_user'); }

    // ── Show connected channels page ──
    public function index()
    {
        $userId = $this->userId();
        $user = \App\Models\User::findOrFail($userId);
        $connections = DB::table('channel_connections')->where('user_id', $userId)->get();
        return view('user.channels', compact('user', 'connections'));
    }

    // ── Connect Facebook Page / Messenger ──
    public function connectMessenger(Request $request)
    {
        $userId = $this->userId();
        $token  = $request->access_token;
        $pageId = $request->page_id;

        if (!$token || !$pageId) {
            return response()->json(['error' => 'Missing token or page ID'], 400);
        }

        // Get page details
        $resp = Http::withToken($token)
            ->get("https://graph.facebook.com/v19.0/{$pageId}", [
                'fields' => 'name,username'
            ]);

        if ($resp->failed()) {
            return response()->json(['error' => 'Invalid page token'], 400);
        }

        $page = $resp->json();

        // Subscribe app to page
        Http::withToken($token)
            ->post("https://graph.facebook.com/v19.0/{$pageId}/subscribed_apps", [
                'subscribed_fields' => 'messages,messaging_postbacks,messaging_optins'
            ]);

        // Save connection
        DB::table('channel_connections')->updateOrInsert(
            ['user_id' => $userId, 'channel' => 'messenger', 'page_id' => $pageId],
            [
                'access_token' => $token,
                'page_name'    => $page['name'] ?? '',
                'username'     => $page['username'] ?? '',
                'is_active'    => 1,
                'connected_at' => now(),
                'updated_at'   => now(),
            ]
        );

        return response()->json(['success' => true, 'page' => $page['name']]);
    }

    // ── Connect Instagram ──
    public function connectInstagram(Request $request)
    {
        $userId = $this->userId();
        $token  = $request->access_token;
        $pageId = $request->page_id;

        if (!$token || !$pageId) {
            return response()->json(['error' => 'Missing token or page ID'], 400);
        }

        // Get Instagram Business Account linked to this page
        $resp = Http::withToken($token)
            ->get("https://graph.facebook.com/v19.0/{$pageId}", [
                'fields' => 'instagram_business_account,name'
            ]);

        if ($resp->failed() || !isset($resp->json()['instagram_business_account'])) {
            return response()->json(['error' => 'No Instagram Business account linked to this Facebook Page.'], 400);
        }

        $instaId = $resp->json()['instagram_business_account']['id'];

        // Get Instagram details
        $instaResp = Http::withToken($token)
            ->get("https://graph.facebook.com/v19.0/{$instaId}", [
                'fields' => 'name,username'
            ]);

        $insta = $instaResp->json();

        // Subscribe app to page for Instagram messaging
        Http::withToken($token)
            ->post("https://graph.facebook.com/v19.0/{$pageId}/subscribed_apps", [
                'subscribed_fields' => 'messages,messaging_postbacks,instagram_manage_messages'
            ]);

        DB::table('channel_connections')->updateOrInsert(
            ['user_id' => $userId, 'channel' => 'instagram', 'page_id' => $instaId],
            [
                'access_token' => $token,
                'page_name'    => $insta['name'] ?? '',
                'username'     => $insta['username'] ?? '',
                'is_active'    => 1,
                'connected_at' => now(),
                'updated_at'   => now(),
            ]
        );

        return response()->json(['success' => true, 'username' => $insta['username'] ?? $instaId]);
    }

    // ── Connect Telegram ──
    public function connectTelegram(Request $request)
    {
        $userId   = $this->userId();
        $botToken = $request->bot_token;

        if (!$botToken) {
            return response()->json(['error' => 'Bot token required'], 400);
        }

        // Verify bot token
        $resp = Http::get("https://api.telegram.org/bot{$botToken}/getMe");

        if ($resp->failed() || !$resp->json('ok')) {
            return response()->json(['error' => 'Invalid Telegram bot token'], 400);
        }

        $bot = $resp->json('result');

        // Set webhook
        $webhookUrl = config('app.url') . '/webhook/telegram/' . $userId;
        Http::post("https://api.telegram.org/bot{$botToken}/setWebhook", [
            'url' => $webhookUrl
        ]);

        DB::table('channel_connections')->updateOrInsert(
            ['user_id' => $userId, 'channel' => 'telegram'],
            [
                'bot_token'    => $botToken,
                'page_name'    => $bot['first_name'] ?? '',
                'username'     => $bot['username'] ?? '',
                'is_active'    => 1,
                'connected_at' => now(),
                'updated_at'   => now(),
            ]
        );

        return response()->json(['success' => true, 'bot' => $bot['username']]);
    }

    // ── Webhook: Messenger & Instagram incoming messages ──
    public function webhook(Request $request)
    {
        // Verify webhook
        if ($request->isMethod('get')) {
            $verifyToken = env('VERIFY_TOKEN', 'test123');
            if ($request->hub_verify_token === $verifyToken) {
                return response($request->hub_challenge, 200);
            }
            return response('Forbidden', 403);
        }

        $body = $request->all();
        Log::info('=== MESSENGER/INSTAGRAM WEBHOOK ===', $body);

        $object = $body['object'] ?? '';

        foreach ($body['entry'] ?? [] as $entry) {
            // Messenger
            if ($object === 'page') {
                foreach ($entry['messaging'] ?? [] as $msg) {
                    $this->processMessengerMessage($entry['id'], $msg);
                }
            }
            // Instagram
            if ($object === 'instagram') {
                foreach ($entry['messaging'] ?? [] as $msg) {
                    $this->processInstagramMessage($entry['id'], $msg);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function processMessengerMessage($pageId, $msg)
    {
        $senderId  = $msg['sender']['id'] ?? null;
        $messageId = $msg['message']['mid'] ?? null;
        $text      = $msg['message']['text'] ?? '';

        if (!$senderId || !$messageId) return;
        if (DB::table('messages')->where('meta_message_id', $messageId)->exists()) return;

        // Find user by page_id
        $conn = DB::table('channel_connections')
            ->where('channel', 'messenger')
            ->where('page_id', $pageId)
            ->where('is_active', 1)
            ->first();

        if (!$conn) return;

        DB::table('messages')->insert([
            'user_id'         => $conn->user_id,
            'channel'         => 'messenger',
            'wa_id'           => $senderId,
            'message'         => $text ?: '[Attachment]',
            'type'            => 'incoming',
            'status'          => 'received',
            'meta_message_id' => $messageId,
            'read'            => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        Log::info('Messenger message saved', ['user_id' => $conn->user_id, 'from' => $senderId]);
    }

    private function processInstagramMessage($instaId, $msg)
    {
        $senderId  = $msg['sender']['id'] ?? null;
        $messageId = $msg['message']['mid'] ?? null;
        $text      = $msg['message']['text'] ?? '';

        if (!$senderId || !$messageId) return;
        if (DB::table('messages')->where('meta_message_id', $messageId)->exists()) return;

        $conn = DB::table('channel_connections')
            ->where('channel', 'instagram')
            ->where('page_id', $instaId)
            ->where('is_active', 1)
            ->first();

        if (!$conn) return;

        DB::table('messages')->insert([
            'user_id'         => $conn->user_id,
            'channel'         => 'instagram',
            'wa_id'           => $senderId,
            'message'         => $text ?: '[Attachment]',
            'type'            => 'incoming',
            'status'          => 'received',
            'meta_message_id' => $messageId,
            'read'            => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        Log::info('Instagram message saved', ['user_id' => $conn->user_id, 'from' => $senderId]);
    }

    // ── Webhook: Telegram ──
    public function telegramWebhook(Request $request, $userId)
    {
        $body = $request->all();
        Log::info('=== TELEGRAM WEBHOOK ===', $body);

        $message   = $body['message'] ?? null;
        if (!$message) return response()->json(['ok' => true]);

        $senderId  = $message['from']['id'] ?? null;
        $messageId = $message['message_id'] ?? null;
        $text      = $message['text'] ?? '';
        $firstName = $message['from']['first_name'] ?? '';
        $username  = $message['from']['username'] ?? '';

        if (!$senderId) return response()->json(['ok' => true]);

        $conn = DB::table('channel_connections')
            ->where('user_id', $userId)
            ->where('channel', 'telegram')
            ->where('is_active', 1)
            ->first();

        if (!$conn) return response()->json(['ok' => true]);

        // Prevent duplicate
        if (DB::table('messages')->where('meta_message_id', 'tg_'.$messageId)->exists()) {
            return response()->json(['ok' => true]);
        }

        DB::table('messages')->insert([
            'user_id'         => $userId,
            'channel'         => 'telegram',
            'wa_id'           => $senderId,
            'message'         => $text ?: '[Attachment]',
            'type'            => 'incoming',
            'status'          => 'received',
            'meta_message_id' => 'tg_' . $messageId,
            'read'            => false,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Save contact name
        DB::table('contacts')->updateOrInsert(
            ['user_id' => $userId, 'phone' => $senderId],
            ['name' => trim($firstName . ' ' . ($username ? '@'.$username : '')), 'updated_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    // ── Send Messenger message ──
    public function sendMessenger(Request $request)
    {
        $userId    = $this->userId();
        $recipient = $request->recipient;
        $message   = $request->message;

        $conn = DB::table('channel_connections')
            ->where('user_id', $userId)
            ->where('channel', 'messenger')
            ->where('is_active', 1)
            ->first();

        if (!$conn) return response()->json(['error' => 'Messenger not connected'], 400);

        $resp = Http::withToken($conn->access_token)
            ->post("https://graph.facebook.com/v19.0/{$conn->page_id}/messages", [
                'recipient' => ['id' => $recipient],
                'message'   => ['text' => $message],
            ]);

        if ($resp->failed()) {
            return response()->json(['error' => $resp->json()['error']['message'] ?? 'Failed to send'], 400);
        }

        DB::table('messages')->insert([
            'user_id'    => $userId,
            'channel'    => 'messenger',
            'wa_id'      => $recipient,
            'message'    => $message,
            'type'       => 'outgoing',
            'status'     => 'sent',
            'read'       => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ── Send Telegram message ──
    public function sendTelegram(Request $request)
    {
        $userId    = $this->userId();
        $recipient = $request->recipient;
        $message   = $request->message;

        $conn = DB::table('channel_connections')
            ->where('user_id', $userId)
            ->where('channel', 'telegram')
            ->where('is_active', 1)
            ->first();

        if (!$conn) return response()->json(['error' => 'Telegram not connected'], 400);

        $resp = Http::post("https://api.telegram.org/bot{$conn->bot_token}/sendMessage", [
            'chat_id' => $recipient,
            'text'    => $message,
        ]);

        if ($resp->failed()) {
            return response()->json(['error' => 'Failed to send Telegram message'], 400);
        }

        DB::table('messages')->insert([
            'user_id'    => $userId,
            'channel'    => 'telegram',
            'wa_id'      => $recipient,
            'message'    => $message,
            'type'       => 'outgoing',
            'status'     => 'sent',
            'read'       => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ── Disconnect channel ──
    public function disconnect(Request $request)
    {
        DB::table('channel_connections')
            ->where('user_id', $this->userId())
            ->where('channel', $request->channel)
            ->update(['is_active' => 0, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ── Get connected pages from Facebook ──
    public function getPages(Request $request)
    {
        $token = $request->access_token;
        if (!$token) return response()->json(['error' => 'Token required'], 400);

        $resp = Http::withToken($token)
            ->get("https://graph.facebook.com/v19.0/me/accounts", [
                'fields' => 'id,name,username,access_token,instagram_business_account'
            ]);

        if ($resp->failed()) {
            return response()->json(['error' => 'Failed to fetch pages'], 400);
        }

        return response()->json(['pages' => $resp->json('data')]);
    }
}
