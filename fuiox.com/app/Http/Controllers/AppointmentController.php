<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    public function form()
    {
        return view('appointment.form');
    }

    public function submit(Request $request)
    {
        Log::info('=== STEP 1: FORM SUBMITTED ===', $request->all());

        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'required|string|max:15',
            'email'        => 'required|email',
            'requirements' => 'required|string|max:1000',
        ]);

        Log::info('=== STEP 2: VALIDATION PASSED ===', $validated);

        $whatsappSent = $this->sendWhatsApp($validated['phone'], $validated['name']);
        Log::info('=== STEP 3: WHATSAPP RESULT ===', ['sent' => $whatsappSent]);

        if ($whatsappSent) {
            return redirect()->route('appointment.form')
                ->with('success', 'Thank you ' . $validated['name'] . '! We received your details and will contact you shortly.');
        }

        return redirect()->route('appointment.form')
            ->with('error', 'Form submitted but WhatsApp message failed. We will still contact you.');
    }

 private function sendWhatsApp($phone, $name)
{
    try {
        $token   = env('WHATSAPP_TOKEN');
        $phoneId = env('WHATSAPP_PHONE_ID');

        // ✅ format phone (India)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (!str_starts_with($phone, '91')) {
            $phone = '91' . $phone;
        }

        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->withoutVerifying()
            ->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                "messaging_product" => "whatsapp",
                "to" => $phone,
                "type" => "template",
                "template" => [
                    "name" => "lead_app", // ✅ your template name
                    "language" => [
                        "code" => "en" // ✅ important
                    ],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $name // replaces {{1}}
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        \Log::info('WHATSAPP RESPONSE', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        return $response->successful();

    } catch (\Exception $e) {
        \Log::error('WHATSAPP ERROR', [
            'message' => $e->getMessage()
        ]);
        return false;
    }
}
}