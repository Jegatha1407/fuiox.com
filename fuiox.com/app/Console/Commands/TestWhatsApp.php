<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestWhatsApp extends Command
{
    protected $signature = 'test:whatsapp';
    protected $description = 'Test WhatsApp API';

    public function handle()
    {
        $token    = env('WHATSAPP_TOKEN');
        $phoneId  = env('WHATSAPP_PHONE_ID');
        $template = env('WHATSAPP_TEMPLATE_NAME');

        $this->info('Token: ' . ($token ? 'EXISTS' : 'MISSING'));
        $this->info('Phone ID: ' . $phoneId);
        $this->info('Template: ' . $template);

     $response = Http::withToken($token)
    ->withoutVerifying()
    ->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
        "messaging_product" => "whatsapp",
        "to" => "917358013530",
        "type" => "template",
        "template" => [
            "name" => "hello_world",
            "language" => [
                "code" => "en_US"
            ]
        ]
    ]);

dd($response->body());
$this->info('Status: ' . $response->status());
$this->info('Response: ' . $response->body());
        $this->info('Status: ' . $response->status());
        $this->info('Response: ' . $response->body());
    }
}