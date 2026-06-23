<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Template;
use App\Models\Contact;
use App\Models\Message;
use App\Models\MessageLog;

class TemplateController extends Controller
{
    public function index()
    {
        return redirect()->route('templates.meta.manager');
    }

    protected function fetchMetaTemplates(User $user): array
    {
        if (!$user->phone_number_id || !$user->access_token) {
            return ['templates' => [], 'business_link' => null];
        }

        $businessId = $this->resolveWhatsAppBusinessAccountId($user);
        $businessLink = $businessId
            ? "https://business.facebook.com/wa/manage_templates?business_id={$businessId}"
            : null;

        if (!$businessId) {
            Log::error('Unable to resolve business account', [
                'user_id' => $user->id,
                'phone_number_id' => $user->phone_number_id
            ]);
            return ['templates' => [], 'business_link' => $businessLink];
        }

        try {
            $templateResponse = Http::withToken($user->access_token)
                ->get("https://graph.facebook.com/v19.0/{$businessId}/message_templates?fields=name,language,category,status,components");

            if ($templateResponse->failed()) {
                Log::error('Failed to fetch templates from Meta', [
                    'user_id' => $user->id,
                    'business_id' => $businessId,
                    'status' => $templateResponse->status(),
                    'response' => $templateResponse->json()
                ]);
                return ['templates' => [], 'business_link' => $businessLink];
            }

            $templates = collect($templateResponse->json('data', []))
                ->filter(fn ($item) => strtolower($item['status'] ?? '') === 'approved')
                ->map(function ($item) {
                    $components = $item['components'] ?? [];
                    $bodyText = '';
                    foreach ($components as $component) {
                        if (strtoupper($component['type'] ?? '') === 'BODY') {
                            $bodyText = $component['text'] ?? '';
                            break;
                        }
                    }

                    preg_match_all('/\{\{\s*\d+\s*\}\}/', $bodyText, $matches);
                    $parameterCount = count(array_unique($matches[0] ?? []));

                    return [
                        'name' => $item['name'] ?? '',
                        'language' => $item['language']['code'] ?? 'en_US',
                        'status' => $item['status'] ?? 'unknown',
                        'category' => $item['category'] ?? 'unknown',
                        'preview' => $bodyText,
                        'parameter_count' => $parameterCount,
                        'components' => $components,
                    ];
                })->values()->toArray();

            Log::info('Successfully fetched templates from Meta', [
                'user_id' => $user->id,
                'template_count' => count($templates)
            ]);

            return ['templates' => $templates, 'business_link' => $businessLink];
        } catch (\Exception $e) {
            Log::error('Exception fetching meta templates', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return ['templates' => [], 'business_link' => $businessLink];
        }
    }

    protected function resolveWhatsAppBusinessAccountId(User $user): ?string
    {
        if (!$user->phone_number_id || !$user->access_token) {
            return null;
        }

        // Check if we already have it stored
        if ($user->business_account_id) {
            Log::info('Using stored business account ID', ['business_id' => $user->business_account_id]);
            return $user->business_account_id;
        }

        try {
            // Try multiple approaches to get the business account
            Log::info('Attempting to resolve business account', ['phone_id' => $user->phone_number_id]);

            // Approach 1: Get phone number without field filters first
            $accountResponse = Http::withToken($user->access_token)
                ->get("https://graph.facebook.com/v19.0/{$user->phone_number_id}");

            if ($accountResponse->successful()) {
                $responseData = $accountResponse->json();
                Log::info('Phone endpoint response', ['keys' => array_keys($responseData), 'data' => json_encode($responseData)]);

                // Extract business account ID
                if (isset($responseData['whatsapp_business_account']['id'])) {
                    $businessId = $responseData['whatsapp_business_account']['id'];
                    // Store it for next time
                    $user->update(['business_account_id' => $businessId]);
                    Log::info('Resolved and stored business account ID', ['business_id' => $businessId]);
                    return $businessId;
                }
                if (isset($responseData['whatsapp_business_account']) && is_string($responseData['whatsapp_business_account'])) {
                    $businessId = $responseData['whatsapp_business_account'];
                    $user->update(['business_account_id' => $businessId]);
                    return $businessId;
                }
                if (isset($responseData['business_account_id'])) {
                    $businessId = $responseData['business_account_id'];
                    $user->update(['business_account_id' => $businessId]);
                    return $businessId;
                }
            } else {
                Log::error('API request failed to phone endpoint', [
                    'status' => $accountResponse->status(),
                    'response' => $accountResponse->json()
                ]);
            }

            // Approach 2: Try with explicit fields
            $fieldsResponse = Http::withToken($user->access_token)
                ->get("https://graph.facebook.com/v19.0/{$user->phone_number_id}", [
                    'fields' => 'id,whatsapp_business_account,business_account_id'
                ]);

            if ($fieldsResponse->successful()) {
                $fieldData = $fieldsResponse->json();
                Log::info('Phone endpoint with fields response', ['data' => json_encode($fieldData)]);

                if (isset($fieldData['whatsapp_business_account']['id'])) {
                    $businessId = $fieldData['whatsapp_business_account']['id'];
                    $user->update(['business_account_id' => $businessId]);
                    return $businessId;
                }
                if (isset($fieldData['whatsapp_business_account']) && is_string($fieldData['whatsapp_business_account'])) {
                    $businessId = $fieldData['whatsapp_business_account'];
                    $user->update(['business_account_id' => $businessId]);
                    return $businessId;
                }
                if (isset($fieldData['business_account_id'])) {
                    $businessId = $fieldData['business_account_id'];
                    $user->update(['business_account_id' => $businessId]);
                    return $businessId;
                }
            }

            Log::warning('Could not resolve business account - trying to list managed businesses', [
                'user_id' => $user->id,
                'phone_number_id' => $user->phone_number_id
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception resolving business account', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function fetchMetaTemplatesJson()
    {
        $user = User::findOrFail(session('auth_user'));
        $result = $this->fetchMetaTemplates($user);

        return response()->json(['templates' => $result['templates']]);
    }

    public function redirectToMetaManager()
    {
        $user = User::findOrFail(session('auth_user'));

        if (!$user->phone_number_id || !$user->access_token) {
            return redirect()->route('dashboard')->withErrors(['api' => 'WhatsApp API settings are not connected.']);
        }

        $businessId = $this->resolveWhatsAppBusinessAccountId($user);

        if (!$businessId) {
            return redirect()->route('dashboard')->withErrors(['api' => 'Unable to resolve Meta Business account ID.']);
        }

        return redirect()->away("https://business.facebook.com/wa/manage_templates?business_id={$businessId}");
    }

    public function store(Request $request)
    {
        return redirect()->route('templates.meta.manager');
    }

    public function send(int $id)
    {
        $user = User::findOrFail(session('auth_user'));
        $template = Template::where('user_id', $user->id)->findOrFail($id);
        $contacts = Contact::where('user_id', $user->id)->orderBy('name')->get();

        return view('user.templates.send', compact('user', 'template', 'contacts'));
    }

    public function sendBulk(Request $request)
    {
        $user = User::findOrFail(session('auth_user'));

        $request->validate([
            'template_id' => 'required|integer',
            'contact_phones' => 'required|array|min:1',
            'contact_phones.*' => 'required|string',
        ]);

        $template = Template::where('user_id', $user->id)->findOrFail($request->template_id);
        $contacts = Contact::where('user_id', $user->id)
            ->whereIn('phone', $request->contact_phones)
            ->get();

        if ($contacts->isEmpty()) {
            return back()->withErrors(['contact_phones' => 'Please select at least one valid contact.']);
        }

        if (!$user->phone_number_id || !$user->access_token) {
            return back()->withErrors(['api' => 'WhatsApp API is not connected. Please configure it in API Settings.']);
        }

        $messagesSent = 0;
        $results = [];

        foreach ($contacts as $contact) {
            $personalizedBody = $this->replaceTemplateVariables($template->body, $contact, $user);
            $cleanPhone = preg_replace('/[^0-9]/', '', $contact->phone);

            if (!$cleanPhone) {
                $results[] = [
                    'phone' => $contact->phone,
                    'status' => 'failed',
                    'error' => 'Invalid phone number',
                ];
                MessageLog::create([
                    'user_id' => $user->id,
                    'template_id' => $template->id,
                    'contact_phone' => $contact->phone,
                    'status' => 'failed',
                    'response' => 'Invalid phone number',
                ]);
                continue;
            }

            $response = Http::withToken($user->access_token)
                ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $cleanPhone,
                    'type' => 'text',
                    'text' => [
                        'body' => $personalizedBody,
                    ],
                ]);

            $status = $response->successful() ? 'sent' : 'failed';
            $body = $response->json();

            MessageLog::create([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'contact_phone' => $contact->phone,
                'status' => $status,
                'response' => json_encode($body),
            ]);

            if ($status === 'sent') {
                Message::create([
                    'user_id' => $user->id,
                    'wa_id' => $cleanPhone,
                    'message' => $personalizedBody,
                    'type' => 'outgoing',
                    'status' => 'sent',
                ]);
                $messagesSent++;
            }

            $results[] = [
                'phone' => $contact->phone,
                'status' => $status,
                'response' => $body,
            ];
        }

        return redirect()->route('templates.send', ['id' => $template->id])
            ->with('success', "Sent template to {$messagesSent} contact(s).")
            ->with('sendResults', $results);
    }

    private function replaceTemplateVariables(string $body, Contact $contact, User $user): string
    {
        return preg_replace_callback('/\{\{\s*(name|phone)\s*\}\}/i', function ($matches) use ($contact, $user) {
            $key = strtolower($matches[1]);

            return match ($key) {
                'name' => $contact->name ?: $contact->phone,
                'phone' => $contact->phone,
                default => $matches[0],
            };
        }, $body);
    }

    // ── LIST ALL TEMPLATES (all statuses) ─────────────────
    public function listAll()
    {
        $user = User::findOrFail(session('auth_user'));

        if (!$user->business_account_id || !$user->access_token) {
            return response()->json(['error' => 'API not configured'], 400);
        }

        try {
            $resp = Http::withToken($user->access_token)
                ->get("https://graph.facebook.com/v19.0/{$user->business_account_id}/message_templates", [
                    'fields' => 'id,name,status,language,category,components',
                    'limit'  => 100,
                ]);

            if ($resp->failed()) {
                return response()->json(['error' => $resp->json()['error']['message'] ?? 'Failed'], 400);
            }

            $templates = collect($resp->json('data', []))->map(function ($t) {
                $body = '';
                foreach ($t['components'] ?? [] as $c) {
                    if (strtoupper($c['type'] ?? '') === 'BODY') {
                        $body = $c['text'] ?? '';
                        break;
                    }
                }
                return [
                    'id'         => $t['id'],
                    'name'       => $t['name'],
                    'status'     => $t['status'],
                    'language'   => $t['language'] ?? 'en_US',
                    'category'   => $t['category'],
                    'preview'    => $body,
                    'components' => $t['components'] ?? [],
                ];
            })->values();

            return response()->json(['templates' => $templates]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── CREATE TEMPLATE IN META ────────────────────────────
    public function uploadMedia(Request $request)
    {
        $user = User::findOrFail(session('auth_user'));
        if (!$user->access_token) {
            return response()->json(['error' => 'API not configured'], 400);
        }

        $request->validate(['media' => 'required|file|max:20480']);
        $file = $request->file('media');
        $mimeType = $file->getMimeType();

        // Step 1: Start upload session
        $startResp = Http::withToken($user->access_token)
            ->post("https://graph.facebook.com/v19.0/app/uploads", [
                'file_length' => $file->getSize(),
                'file_type'   => $mimeType,
            ]);

        if ($startResp->failed()) {
            return response()->json(['error' => 'Failed to start upload: ' . ($startResp->json('error.message') ?? 'Unknown error')], 400);
        }

        $uploadSessionId = $startResp->json('id');

        // Step 2: Upload file data
        $uploadResp = Http::withToken($user->access_token)
            ->withHeaders([
                'file_offset' => '0',
                'Content-Type' => $mimeType,
            ])
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post("https://graph.facebook.com/v19.0/{$uploadSessionId}");

        if ($uploadResp->failed()) {
            return response()->json(['error' => 'Upload failed: ' . ($uploadResp->json('error.message') ?? 'Unknown error')], 400);
        }

        $responseBody = $uploadResp->body();
        // Extract first handle if multiple returned
        $handle = $uploadResp->json('h');
        if (!$handle) {
            // Try to extract from response body
            preg_match('/4::[A-Za-z0-9+\/=:_-]+/', $responseBody, $matches);
            $handle = $matches[0] ?? null;
        }
        // Take only first handle if multiple
        if ($handle) {
            $parts = preg_split('/\s+/', trim($handle));
            $handle = $parts[0];
        }
        if (!$handle) {
            return response()->json(['error' => 'No handle returned from Meta'], 400);
        }

        return response()->json(['success' => true, 'handle' => $handle]);
    }

    public function createTemplate(Request $request)
    {
        $user = User::findOrFail(session('auth_user'));

        if (!$user->business_account_id || !$user->access_token) {
            return response()->json(['error' => 'API not configured'], 400);
        }

        $request->validate([
            'name'     => 'required|string|regex:/^[a-z0-9_]+$/|max:512',
            'category' => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
            'language' => 'required|string',
            'body'     => 'required|string|max:1024',
        ]);

        try {
            // If components are passed from frontend use them directly
            if ($request->has('components') && is_array($request->components) && count($request->components) > 0) {
                $payload = [
                    'name'       => $request->name,
                    'category'   => $request->category,
                    'language'   => $request->language,
                    'components' => $request->components,
                ];
            } else {
                // Build components manually
                preg_match_all('/\{\{(\d+)\}\}/', $request->body, $matches);
                $varCount = count(array_unique($matches[1]));

                $bodyComponent = ['type' => 'BODY', 'text' => $request->body];
                if ($varCount > 0) {
                    $bodyComponent['example'] = ['body_text' => [array_fill(0, $varCount, 'sample_value')]];
                }

                $components = [];

                // Header
                if ($request->header_text) {
                    $components[] = ['type' => 'HEADER', 'format' => 'TEXT', 'text' => $request->header_text];
                }

                $components[] = $bodyComponent;

                // Footer
                if ($request->footer_text) {
                    $components[] = ['type' => 'FOOTER', 'text' => $request->footer_text];
                }

                $payload = [
                    'name'       => $request->name,
                    'category'   => $request->category,
                    'language'   => $request->language,
                    'components' => $components,
                ];
            }
            Log::info("Create template PAYLOAD", ["payload" => json_encode($payload)]);
        
            $resp = Http::withToken($user->access_token)
                ->post("https://graph.facebook.com/v19.0/{$user->business_account_id}/message_templates", $payload);

            Log::info('Create template response', ['status' => $resp->status(), 'body' => $resp->json()]);

            if ($resp->failed()) {
                return response()->json(['error' => $resp->json()['error']['message'] ?? 'Failed to create'], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template created! It will be reviewed by Meta.',

                'template' => $resp->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── DELETE TEMPLATE FROM META ──────────────────────────
    public function deleteTemplate(Request $request)
    {
        $user = User::findOrFail(session('auth_user'));

        if (!$user->business_account_id || !$user->access_token) {
            return response()->json(['error' => 'API not configured'], 400);
        }


        try {
            $resp = Http::withToken($user->access_token)
                ->delete("https://graph.facebook.com/v19.0/{$user->business_account_id}/message_templates", [
                    'name' => $request->name,
                ]);

            Log::info('Delete template response', ['status' => $resp->status(), 'body' => $resp->json()]);

            if ($resp->failed()) {
                return response()->json(['error' => $resp->json()['error']['message'] ?? 'Failed to delete'], 400);
            }

            return response()->json(['success' => true, 'message' => 'Template deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── TEMPLATE MANAGER PAGE ──────────────────────────────
    public function manager()
    {
        $user = User::findOrFail(session('auth_user'));
        return view('user.templates.manager', compact('user'));
    }


    public function showSendMetaTemplate()
    {
        $user = User::findOrFail(session('auth_user'));

        if (!$user->phone_number_id || !$user->access_token) {
            return redirect()->route('setup')->withErrors(['api' => 'WhatsApp API is not connected.']);
        }

        return view('user.templates.send-meta', compact('user'));
    }

    public function getMetaTemplates()
    {
        try {
            $user = User::findOrFail(session('auth_user'));

            if (!$user->phone_number_id || !$user->access_token) {
                return response()->json(['templates' => [], 'error' => 'WhatsApp API is not connected'], 400);
            }

            $result = $this->fetchMetaTemplates($user);

            if (empty($result['templates'])) {
                Log::warning('No templates found for user', ['user_id' => $user->id]);
            }

            return response()->json(['templates' => $result['templates']]);
        } catch (\Exception $e) {
            Log::error('Error fetching meta templates', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['templates' => [], 'error' => 'Failed to fetch templates: ' . $e->getMessage()], 500);
        }
    }

    public function getBusinessNumbers()
    {
        $user = User::findOrFail(session('auth_user'));

        if (!$user->phone_number_id || !$user->access_token) {
            return response()->json(['numbers' => [], 'error' => 'API not connected'], 400);
        }

        try {
            $businessId = $this->resolveWhatsAppBusinessAccountId($user);
            if (!$businessId) {
                return response()->json(['numbers' => [], 'error' => 'Unable to resolve business account'], 400);
            }

            $response = Http::withToken($user->access_token)
                ->get("https://graph.facebook.com/v19.0/{$businessId}/phone_numbers?fields=id,display_phone_number");

            if ($response->failed()) {
                return response()->json(['numbers' => [], 'error' => 'Failed to fetch numbers'], 400);
            }

            $numbers = collect($response->json('data', []))
                ->map(fn ($item) => [
                    'id' => $item['id'] ?? '',
                    'number' => $item['display_phone_number'] ?? '',
                ])->values()->toArray();

            return response()->json(['numbers' => $numbers]);
        } catch (\Exception $e) {
            Log::error('Failed to get business numbers', ['error' => $e->getMessage()]);
            return response()->json(['numbers' => [], 'error' => 'Server error'], 500);
        }
    }

    public function sendMetaTemplate(Request $request)
    {
        $user = User::findOrFail(session('auth_user'));

        $request->validate([
            'template_name' => 'required|string',
            'template_language' => 'nullable|string',
            'phone_number_id' => 'required|string',
            'recipient_phone' => 'required|string',
            'parameters' => 'nullable|array',
        ]);

        if (!$user->access_token) {
            return response()->json(['error' => 'API not connected'], 400);
        }

        try {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->recipient_phone);
            $languageCode = $request->template_language ?: 'en_US';

            if (!$cleanPhone || strlen($cleanPhone) < 10) {
                return response()->json(['error' => 'Invalid phone number'], 400);
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $cleanPhone,
                'type' => 'template',
                'template' => [
                    'name' => $request->template_name,
                    'language' => [
                        'code' => $languageCode,
                    ],
                ],
            ];

            // Add parameters if provided
            if (!empty($request->parameters)) {
                $payload['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => collect($request->parameters)
                            ->map(fn ($param) => ['type' => 'text', 'text' => $param])
                            ->values()
                            ->toArray(),
                    ],
                ];
            }

            $response = Http::withToken($user->access_token)
                ->post("https://graph.facebook.com/v19.0/{$request->phone_number_id}/messages", $payload);

            if ($response->successful()) {
                $messageId = $response->json('messages.0.id');

                // Log the message
                Message::create([
                    'user_id' => $user->id,
                    'wa_id' => $cleanPhone,
                    'message' => "Template: {$request->template_name}",
                    'type' => 'outgoing',
                    'status' => 'sent',
                    'message_id' => $messageId,
                ]);

                return response()->json(['success' => true, 'message' => 'Template sent successfully!']);
            } else {
                $error = $response->json();
                Log::error('Meta template send failed', ['error' => $error]);
                return response()->json(['error' => $error['error']['message'] ?? 'Failed to send template'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Exception sending meta template', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}