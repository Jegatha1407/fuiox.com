<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class BrevoService
{
    

    public function sendBackupMail(
        $toEmail,
        $toName,
        $subject,
        $view,
        $attachmentPath,
        $data = []
     ) {
        try {

            $htmlContent = View::make($view, $data)->render();

            $payload = [

                "sender" => [
                    "name"  => env('BREVO_BACKUP_SENDER_NAME'),
                    "email" => env('BREVO_SENDER_EMAIL')
                ],

                "to" => [
                    [
                        "email" => $toEmail,
                        "name"  => $toName
                    ]
                ],

                "subject" => $subject,
                "htmlContent" => $htmlContent
            ];

            if ($attachmentPath && file_exists($attachmentPath)) {
                $payload['attachment'] = [
                    [
                        "name" => basename($attachmentPath),
                        "content" => base64_encode(
                            file_get_contents($attachmentPath)
                        )
                    ]
                ];
            }

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'api-key' => env('BREVO_API_KEY'),
                'content-type' => 'application/json',

            ])->post(
                'https://api.brevo.com/v3/smtp/email',
                $payload
            );


            if ($response->failed()) {
                Log::error('Backup Mail Failed', [
                    'response' => $response->body()
                ]);
                return false;
            }

            Log::info(' Backup Mail Sent Successfully');

            return true;
        } catch (\Exception $e) {
            Log::error('Backup Mail Exception', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
