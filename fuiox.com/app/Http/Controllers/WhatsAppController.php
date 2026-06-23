<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
class WhatsAppController extends Controller
{
    // ===== VERIFY WEBHOOK =====
    public function verify(Request $request)
    {
        if ($request->hub_verify_token === env('VERIFY_TOKEN')) {
            return response($request->hub_challenge, 200);
        }
        return response('Forbidden', 403);
    }

    // ===== RECEIVE MESSAGE =====
   public function webhook(Request $request)
{
    try {
        $value = $request['entry'][0]['changes'][0]['value'] ?? null;

        if (!$value || !isset($value['messages'][0])) {
            return response('OK', 200);
        }

        $message = $value['messages'][0];

        if (($message['type'] ?? '') !== 'text') {
            return response('OK', 200);
        }

        $messageId = $message['id'] ?? null;
        $from = $message['from'] ?? null;

        if (!$messageId || !$from) {
            return response('OK', 200);
        }

        if (Cache::has("wa_" . $messageId)) {
            return response('OK', 200);
        }

        Cache::put("wa_" . $messageId, true, now()->addMinutes(10));

        $this->sendTemplate($from);

        return response('OK', 200);

    } catch (\Throwable $e) {
        \Log::error($e->getMessage());
        return response('OK', 200);
    }
}
    // ===== SEND TEMPLATE =====
private function sendTemplate($to)
{
    try {

        // 📤 Payload
        $payload = [
            "messaging_product" => "whatsapp",
            "to" => $to,
            "type" => "template",
            "template" => [
                "name" => "finalize",
                "language" => [
                    "code" => "en_US"
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => "Jegatha" // {{1}}
                            ],
                            [
                                "type" => "text",
                                "text" => "your email address" // {{2}}
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // 📝 Log request
        \Log::info("WHATSAPP REQUEST:", $payload);

        // 📡 Send request
        $response = Http::withToken(env('WHATSAPP_TOKEN'))
            ->post(
                "https://graph.facebook.com/v19.0/" . env('PHONE_NUMBER_ID') . "/messages",
                $payload
            );

        // 📝 Log response
        \Log::info("WHATSAPP RESPONSE: " . $response->body());

    } catch (\Throwable $e) {

        // ❌ Log error
        \Log::error("WHATSAPP ERROR: " . $e->getMessage());
    }
}
    // ===== SEND TEXT (optional) =====
    private function sendText($to, $msg)
    {
        Http::withToken(env('WHATSAPP_TOKEN'))
            ->post("https://graph.facebook.com/v19.0/" . env('PHONE_NUMBER_ID') . "/messages", [
                "messaging_product" => "whatsapp",
                "to" => $to,
                "text" => [
                    "body" => $msg
                ]
            ]);
    }
}