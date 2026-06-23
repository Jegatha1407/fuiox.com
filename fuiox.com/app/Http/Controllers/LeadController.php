<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        // 1. Get form data
        $name = $request->name;
        $phone = $request->phone;
        $email = $request->email;
        $requirement = $request->requirement;
        $role = $request->role;

        // 2. Send WhatsApp message
        $this->sendWhatsApp($phone, $name);

        return response()->json([
            "status" => "success",
            "message" => "Lead submitted and WhatsApp sent"
        ]);
    }

    private function sendWhatsApp($to, $name)
    {
        $payload = [
            "messaging_product" => "whatsapp",
            "to" => $to,
            "type" => "template",
            "template" => [
                "name" => "call_button",
                "language" => [
                    "code" => "en"
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => []
                    ]
                ]
            ]
        ];

        Http::withToken(env('WHATSAPP_TOKEN'))
            ->post(
                "https://graph.facebook.com/v19.0/" . env('PHONE_NUMBER_ID') . "/messages",
                $payload
            );
    }
}