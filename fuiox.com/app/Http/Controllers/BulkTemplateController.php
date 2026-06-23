<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\Message;
use App\Models\Contact;
use App\Imports\BulkContactImport;

class BulkTemplateController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(session('auth_user'));
        return view('user.bulk-template', compact('user'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $rows = Excel::toArray(new BulkContactImport, $request->file('excel_file'));
        $data = $rows[0] ?? [];

        // Get headers from first row
        $headers = array_keys((array)($data[0] ?? []));
        // Get headers from first row
        $headers = array_keys((array)($data[0] ?? []));
        // Return ALL rows for recipient selection + 3 for preview table
        return response()->json([
            'headers' => $headers,
            'preview' => array_slice($data, 0, 3),
            'rows'    => $data,
            'total'   => count($data),
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'excel_file'      => 'required|file|mimes:xlsx,xls,csv',
            'template_name'   => 'required|string',
            'language_code'   => 'required|string',
            'phone_number_id' => 'required|string',
            'phone_column'    => 'required|string',
            'param_columns'   => 'nullable|array',
            'save_contacts'   => 'nullable|boolean',
            'name_column'     => 'nullable|string',
        ]);

        $user = User::findOrFail(session('auth_user'));

        if (!$user->access_token) {
            return response()->json(['error' => 'API not configured'], 400);
        }

        $rows    = Excel::toArray(new BulkContactImport, $request->file('excel_file'));
        $data    = $rows[0] ?? [];
        $results = [];
        $sent    = 0;
        $failed  = 0;

        foreach ($data as $row) {
            $row = (array) $row;

            // Get phone
            $phone = preg_replace('/[^0-9]/', '', $row[$request->phone_column] ?? '');
            if (!$phone || strlen($phone) < 10) {
                $results[] = ['phone' => $row[$request->phone_column] ?? 'unknown', 'status' => 'failed', 'error' => 'Invalid phone'];
                $failed++;
                continue;
            }

            // Build parameters from mapped columns
            $parameters = [];
            if (!empty($request->param_columns)) {
                foreach ($request->param_columns as $col) {
                    if ($col && isset($row[$col])) {
                        $parameters[] = (string) $row[$col];
                    }
                }
            }

            // Build payload
            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $phone,
                'type'              => 'template',
                'template'          => [
                    'name'     => $request->template_name,
                    'language' => ['code' => $request->language_code],
                ],
            ];

            $components = [];
            if ($request->header_image) {
                $components[] = [
                    'type'       => 'header',
                    'parameters' => [['type' => 'image', 'image' => ['link' => $request->header_image]]],
                ];
            }
            if (!empty($parameters)) {
                $components[] = [
                    'type'       => 'body',
                    'parameters' => collect($parameters)->map(fn($p) => ['type' => 'text', 'text' => $p])->toArray(),
                ];
            }
            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $response = Http::withToken($user->access_token)
                ->post("https://graph.facebook.com/v19.0/{$request->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                // Save message
                Message::create([
                    'user_id' => $user->id,
                    'wa_id'   => $phone,
                    'message' => "Template: {$request->template_name}",
                    'type'    => 'outgoing',
                    'status'  => 'sent',
                ]);

                // Save contact if requested
                if ($request->save_contacts && $request->name_column && isset($row[$request->name_column])) {
                    $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
Contact::updateOrCreate(
    ['user_id' => $user->id, 'phone' => $normalizedPhone],
    ['name'    => (string) $row[$request->name_column]]
);
                Contact::updateOrCreate(
                        ['user_id' => $user->id, 'phone' => $phone],
                        ['name'    => (string) $row[$request->name_column]]
                    );
                }

                $results[] = ['phone' => $phone, 'status' => 'sent'];
                $sent++;
            } else {
                $err = $response->json()['error']['message'] ?? 'Failed';
                $results[] = ['phone' => $phone, 'status' => 'failed', 'error' => $err];
                $failed++;
            }

            // Small delay to avoid rate limiting
            usleep(200000); // 200ms
        }

        return response()->json([
            'sent'    => $sent,
            'failed'  => $failed,
            'total'   => count($data),
            'results' => $results,
        ]);
    }
}