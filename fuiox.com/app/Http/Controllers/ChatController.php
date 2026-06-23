<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Message;
use App\Models\Contact;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(session('auth_user'));
        $ownerId = $user->parent_user_id ?? $user->id;
        $owner = User::findOrFail($ownerId);

        
        $hasActiveSub = \Illuminate\Support\Facades\DB::table('subscriptions')
            ->where('user_id', $ownerId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
        $inTrial = $owner->free_trial_enabled && $owner->trial_ends_at && \Carbon\Carbon::parse($owner->trial_ends_at)->isFuture();
        $planExpired = !$hasActiveSub && !$inTrial && $owner->role === 'user';

        
        $plans = $planExpired ? \Illuminate\Support\Facades\DB::table('plans')->where('is_active',1)->orderBy('price')->get() : collect();

        Log::info("CHAT DEBUG",["user_id"=>$user->id,"owner_id"=>$ownerId,"planExpired"=>$planExpired,"inTrial"=>$inTrial,"hasActiveSub"=>$hasActiveSub]);
        return view("chat", compact("user","planExpired","plans"));
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    public function assignConversation(Request $request)
    {
        $request->validate(['wa_id' => 'required', 'agent_id' => 'nullable|integer']);
        $userId  = session('auth_user');
        $user    = User::findOrFail($userId);
        $ownerId = $user->parent_user_id ?? $userId;

        $exists = DB::table('conversations')->where('user_id', $ownerId)->where('wa_id', $request->wa_id)->exists();
        if ($exists) {
            DB::table('conversations')->where('user_id', $ownerId)->where('wa_id', $request->wa_id)->update([
                'assigned_to' => $request->agent_id ?: null,
                'assigned_at' => $request->agent_id ? now() : null,
                'updated_at'  => now(),
            ]);
        } else {
            DB::table('conversations')->insert([
                'user_id'     => $ownerId,
                'wa_id'       => $request->wa_id,
                'assigned_to' => $request->agent_id ?: null,
                'assigned_at' => $request->agent_id ? now() : null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $agentName = $request->agent_id ? optional(User::find($request->agent_id))->name : null;
        return response()->json(['success' => true, 'agent' => $agentName]);
    }

   
    public function getTeamMembers()
    {
        $userId  = session('auth_user');
        $user    = User::findOrFail($userId);
        $ownerId = $user->parent_user_id ?? $userId;

        $members = User::where('parent_user_id', $ownerId)
            ->where('is_active', 1)
            ->select('id', 'name', 'email', 'team_role')
            ->get();

        return response()->json(['members' => $members]);
    }

    
    public function getUsers()
    {
        $userId = session('auth_user');
        if (!$userId) return response()->json([], 401);

        $user = User::findOrFail($userId);

        $ownerId = $user->parent_user_id ?? $userId;

        $savedContacts = Contact::where('user_id', $ownerId)
            ->get()
            ->mapWithKeys(function ($contact) {
                $phoneDigits = $this->normalizePhone($contact->phone);
                return [$phoneDigits => $contact];
            });

        $convQuery = DB::table('conversations')->where('user_id', $ownerId);
        if ($user->team_role === 'agent') {
            $convQuery->where('assigned_to', $userId);
        }
        $assignedWaIds = $convQuery->pluck('wa_id')->toArray();

        $chatQuery = Message::where('user_id', $ownerId)
            ->selectRaw('wa_id, MAX(created_at) as last_time')
            ->groupBy('wa_id')
            ->orderByDesc('last_time');

        if ($user->team_role === 'agent' && !empty($assignedWaIds)) {
            $chatQuery->whereIn('wa_id', $assignedWaIds);
        } elseif ($user->team_role === 'agent' && empty($assignedWaIds)) {
            return response()->json([]);
        }

        $chatContacts = $chatQuery->get();

        $users = [];
        $seen = [];

        foreach ($chatContacts as $row) {
            $phoneDigits = $this->normalizePhone($row->wa_id);
            $seen[$phoneDigits] = true;

            $last = Message::where('user_id', $ownerId)
                ->where('wa_id', $row->wa_id)
                ->latest('created_at')
                ->first();

            $contact = $savedContacts[$phoneDigits] ?? null;
            $displayName = $contact ? $contact->name : '';
            $displayPhone = $contact ? $contact->phone : $row->wa_id;

            $lastMessageText = '';
            if ($last) {
                if ($last->media_type) {
                    $lastMessageText = $this->getMediaPreviewText($last->media_type, $last->media_filename);
                } else {
                    $clean = mb_convert_encoding($last->message ?? '', 'UTF-8', 'UTF-8');
                    $lastMessageText = mb_strlen($clean) > 35
                        ? mb_substr($clean, 0, 35) . '...'
                        : $clean;
                }
            }

            $conv = DB::table('conversations')
                ->where('user_id', $ownerId)
                ->where('wa_id', $row->wa_id)
                ->first();

            // Get assigned agent name
            $assignedAgent = null;
            if ($conv && $conv->assigned_to) {
                $agent = User::find($conv->assigned_to);
                $assignedAgent = $agent ? ['id' => $agent->id, 'name' => $agent->name] : null;
            }

            $users[] = [
                'phone'           => $row->wa_id,
                'name'            => $displayName,
                'display_phone'   => $displayPhone,
                'tags'            => $contact ? $contact->tags : null,
                'last_message'    => $lastMessageText,
                'last_time'       => Carbon::parse($row->last_time)
                    ->setTimezone('Asia/Kolkata')
                    ->format('h:i A'),
                'last_type'       => $last ? $last->type : '',
                'unread'          => Message::where('user_id', $ownerId)
                    ->where('wa_id', $row->wa_id)
                    ->where('type', 'incoming')
                    ->where('read', false)
                    ->count(),
                'conversation_id' => $conv ? $conv->id : null,
                'assigned_to'     => $assignedAgent,
            ];
        }

        return response()->json($users);
    }

  
    public function getMessages($phone)
    {
        $userId = session('auth_user');
        if (!$userId) return response()->json([]);

        $user    = User::find($userId);
        $ownerId = $user ? ($user->parent_user_id ?? $userId) : $userId;
        if ($user) $user->update(['last_seen' => Carbon::now()]);

        $clean = preg_replace('/[^0-9]/', '', $phone);
        $messages = Message::where('user_id', $ownerId)
            ->where(function($q) use ($phone, $clean) {
                $q->where('wa_id', $phone)
                  ->orWhere('wa_id', $clean)
                  ->orWhereRaw("REPLACE(REPLACE(wa_id, '+', ''), ' ', '') = ?", [$clean]);
            })
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->reverse()->values()
            ->map(function ($m) {
                return [
                    'id'              => $m->id,
                    'message'         => $m->message,
                    'type'            => $m->type,
                    'created_at'      => Carbon::parse($m->created_at)->setTimezone('Asia/Kolkata')->format('h:i A'),
                    'date'            => Carbon::parse($m->created_at)->setTimezone('Asia/Kolkata')->format('d M Y'),
                    'media_type'      => $m->media_type,
                    'media_id'        => $m->media_id,
                    'media_url'       => $m->media_id,
                    'media_caption'   => $m->media_caption,
                    'media_filename'  => $m->media_filename,
                    'media_mime_type' => $m->media_mime_type,
                    'media_size'      => $m->media_size,
                    'reaction'        => $m->reaction ?? null,
                    'reply_to'        => $m->reply_to ?? null,
                    'reply_to_id'     => $m->reply_to_id ?? null,
                ];
            });

        Message::where('user_id', $ownerId)->where('wa_id', $phone)->where('type', 'incoming')->update(['read' => true]);

        return response()->json($messages);
    }

 public function sendMessage(Request $request)
{
    $userId = session('auth_user');

    if (!$userId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = User::find($userId);
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
$ownerId = $user->parent_user_id ?? $userId;
    if (!$user->phone_number_id || !$user->access_token) {
        return response()->json(['error' => 'WhatsApp API not configured. Go to Setup.'], 400);
    }

    $phone   = preg_replace('/[^0-9]/', '', $request->phone ?? '');
    $message = trim($request->message ?? '');
    $hasFile = $request->hasFile('media');

    if (!$phone) {
        return response()->json(['error' => 'Phone number required'], 422);
    }

    if (!$message && !$hasFile) {
        return response()->json(['error' => 'Message or media required'], 422);
    }

  
    if ($hasFile) {
        $file     = $request->file('media');
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $fileSize = $file->getSize();

        // ── STEP 1: Determine WhatsApp type ──────────────
        // If JS sent force_audio=1 it means this is a voice recording
        // regardless of what MIME the browser assigned (iOS sends video/mp4)
        if ($request->input('force_audio') === '1' || $request->input('media_type') === 'audio') {
            $waType = 'audio';
        } else {
            // VIDEO before AUDIO — video/webm must never become audio
            $waType = 'document';
            if (str_starts_with($mimeType, 'image/'))     $waType = 'image';
            elseif (str_starts_with($mimeType, 'video/')) $waType = 'video';
            elseif (str_starts_with($mimeType, 'audio/')) $waType = 'audio';
        }

        // ── WhatsApp file size limits ─────────────────────
        $limits = ['image' => 5, 'video' => 16, 'audio' => 16, 'document' => 100];
        $limitMB = $limits[$waType] ?? 100;
        if ($fileSize > $limitMB * 1024 * 1024) {
            return response()->json([
                'error' => "File too large. WhatsApp allows max {$limitMB}MB for {$waType} files. Your file is " . round($fileSize / 1024 / 1024, 1) . "MB."
            ], 422);
        }

        $uploadMime   = $mimeType;
        $uploadName   = $file->getClientOriginalName();
        $tmpConverted = null;

        // Find ffmpeg — check snap first, then system
        $ff = '';
        foreach (['/usr/local/bin/ffmpeg', '/usr/bin/ffmpeg', '/snap/bin/ffmpeg'] as $path) {
            if (file_exists($path)) { $ff = $path; break; }
        }
        if (!$ff) $ff = trim(shell_exec('which ffmpeg 2>/dev/null') ?: '');
        if ($waType === 'image') {
            if (!in_array($mimeType, ['image/jpeg','image/png','image/webp','image/gif'])) {
                $uploadMime = 'image/jpeg';
                $uploadName = pathinfo($uploadName, PATHINFO_FILENAME) . '.jpg';
            }

        } elseif ($waType === 'video') {
            // Must convert webm → real mp4 binary
            if (!in_array($mimeType, ['video/mp4','video/3gpp'])) {
                if ($ff && file_exists($ff)) {
                    $out = tempnam(sys_get_temp_dir(), 'wv') . '.mp4';
                    shell_exec(escapeshellcmd($ff) . ' -y -i ' . escapeshellarg($file->getRealPath())
                        . ' -c:v libx264 -preset fast -crf 28 -c:a aac -movflags +faststart '
                        . escapeshellarg($out) . ' 2>&1');
                    if (file_exists($out) && filesize($out) > 100) {
                        $tmpConverted = $out;
                    } else {
                        if (isset($out) && file_exists($out)) @unlink($out);
                    }
                }
                $uploadMime = 'video/mp4';
                $uploadName = pathinfo($uploadName, PATHINFO_FILENAME) . '.mp4';
            }

        } elseif ($waType === 'audio') {
            // Convert webm → real ogg binary using ffmpeg
            $uploadMime = 'audio/ogg';
            $uploadName = 'voice.ogg';
            if ($ff && file_exists($ff)) {
                $out = tempnam(sys_get_temp_dir(), 'wa') . '.ogg';
                shell_exec(escapeshellcmd($ff) . ' -y -i ' . escapeshellarg($file->getRealPath())
                    . ' -c:a libopus -b:a 32k -ar 16000 -ac 1 ' . escapeshellarg($out) . ' 2>&1');
                if (!file_exists($out) || filesize($out) < 100) {
                    if (file_exists($out)) @unlink($out);
                    $out = tempnam(sys_get_temp_dir(), 'wa') . '.ogg';
                    shell_exec(escapeshellcmd($ff) . ' -y -i ' . escapeshellarg($file->getRealPath())
                        . ' -c:a libvorbis -q:a 4 ' . escapeshellarg($out) . ' 2>&1');
                }
                if (file_exists($out) && filesize($out) > 100) {
                    $tmpConverted = $out;
                } else {
                    if (isset($out) && file_exists($out)) @unlink($out);
                }
            }

        } else {
            // document — zip/rar not supported
            $allowed = ['application/pdf','application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain'];
            if (!in_array($mimeType, $allowed)) {
                return response()->json(['error' => 'WhatsApp does not support this file type. Supported: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT.'], 422);
            }
        }

        Log::info('WA Upload', ['waType' => $waType, 'mime' => $uploadMime, 'name' => $uploadName, 'converted' => $tmpConverted ? 'yes' : 'no']);

        // ── STEP 3: Upload using fopen stream ─────────────
        $uploadSrc = $tmpConverted ?: $file->getRealPath();
        $fh = fopen($uploadSrc, 'rb');
        if (!$fh) {
            if ($tmpConverted) @unlink($tmpConverted);
            return response()->json(['error' => 'Cannot read file'], 500);
        }

        $uploadResponse = Http::withHeaders(['Authorization' => 'Bearer ' . $user->access_token])
            ->attach('file', $fh, $uploadName, ['Content-Type' => $uploadMime])
            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/media", [
                'messaging_product' => 'whatsapp',
                'type'              => $waType,
            ]);

        fclose($fh);
        if ($tmpConverted) @unlink($tmpConverted);

        Log::info('WA Upload response', ['status' => $uploadResponse->status(), 'body' => $uploadResponse->json()]);

        if ($uploadResponse->failed()) {
            $err = $uploadResponse->json()['error']['message'] ?? 'Upload failed';
            return response()->json(['error' => 'Upload failed: ' . $err], 400);
        }

        $mediaId = $uploadResponse->json('id');
        if (!$mediaId) {
            return response()->json(['error' => 'No media ID from WhatsApp'], 500);
        }

        // Build send payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => $waType,
            $waType             => ['id' => $mediaId],
        ];

        if (in_array($waType, ['image', 'video']) && $message) {
            $payload[$waType]['caption'] = $message;
        }
        if ($waType === 'document') {
            $payload[$waType]['filename'] = $file->getClientOriginalName();
            if ($message) $payload[$waType]['caption'] = $message;
        }

        $sendResponse = Http::withToken($user->access_token)
            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", $payload);

        Log::info('Send media response', [
            'status' => $sendResponse->status(),
            'body'   => $sendResponse->json(),
        ]);

        if ($sendResponse->status() === 401) {
            return response()->json(['error' => 'Token expired. Update in /setup'], 401);
        }

        if ($sendResponse->failed()) {
            $err = $sendResponse->json()['error']['message'] ?? 'Send failed';
            return response()->json(['error' => $err], 400);
        }

        // Cache file locally
        $cacheFolder = storage_path('app/public/media');
        if (!file_exists($cacheFolder)) mkdir($cacheFolder, 0755, true);
        $ext    = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)
                  ?: explode('/', $mimeType)[1] ?? 'bin';
        copy($file->getRealPath(), $cacheFolder . '/' . $mediaId . '.' . $ext);

        Message::create([
            'user_id'         => $ownerId,
            'wa_id'           => $phone,
            'message'         => $message ?: $file->getClientOriginalName(),
            'type'            => 'outgoing',
            'status'          => 'sent',
            'meta_message_id' => $sendResponse->json('messages.0.id'),
            'media_type'      => $waType,
            'media_id'        => $mediaId,
            'media_caption'   => $message ?: null,
            'media_filename'  => $file->getClientOriginalName(),
            'media_mime_type' => $uploadMime,
            'media_size'      => $fileSize,
            'reply_to'        => $request->reply_to    ?? null,
            'reply_to_id'     => $request->reply_to_id ?? null,
        ]);

        return response()->json(['status' => 'sent']);
    }

    // ── SEND TEXT ────────────────────────────────
   // ── SEND TEXT ────────────────────────────────

$replyToId = $request->reply_to_id ?? null;

$payload = [
    'messaging_product' => 'whatsapp',
    'recipient_type'    => 'individual',
    'to'                => $phone,
    'type'              => 'text',
    'text'              => [
        'body' => $message
    ],
];

if ($replyToId) {
    $payload['context'] = [
        'message_id' => $replyToId
    ];
}

$response = Http::withToken($user->access_token)
    ->post(
        "https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages",
        $payload
    );

Log::info('Send text response', [
    'status' => $response->status(),
    'body'   => $response->json(),
]);

if ($response->status() === 401) {
    return response()->json([
        'error'    => 'Token expired. Please update in /setup',
        'redirect' => '/setup'
    ], 401);
}

if ($response->failed()) {
    $err = $response->json()['error']['message'] ?? 'Send failed';

  return response()->json([
    'error' => $err
], 400);
}

$metaMessageId = $response->json()['messages'][0]['id'] ?? null;

Message::create([
    'user_id'         => $ownerId,
    'wa_id'           => $phone,
    'message'         => $message,
    'type'            => 'outgoing',
    'status'          => 'sent',
    'meta_message_id' => $metaMessageId,
    'reply_to'        => $request->reply_to ?? null,
    'reply_to_id'     => $replyToId,
]);

return response()->json([
    'status' => 'sent'
]);
}

 // ── GET MEDIA URL ─────────────────────────────
   public function getMedia($mediaId)
{
    $userId = session('auth_user');

    if (!$userId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = User::find($userId);
    if (!$user || !$user->access_token) {
        return response()->json(['error' => 'Not configured'], 400);
    }

    // Check local cache
    $cacheFolder = storage_path('app/public/media');
    if (!file_exists($cacheFolder)) {
        mkdir($cacheFolder, 0755, true);
    }

    $cachedFiles = glob($cacheFolder . '/' . $mediaId . '.*');
    if (!empty($cachedFiles)) {
        $mimeType = mime_content_type($cachedFiles[0]);
        return response()->file($cachedFiles[0], [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline',
            'Cache-Control'       => 'public, max-age=86400',
            'Accept-Ranges'       => 'bytes',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    // Get media info from Meta
    $infoResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $user->access_token,
        'User-Agent'    => 'curl/7.64.1',
    ])->get("https://graph.facebook.com/v19.0/{$mediaId}");

    Log::info('Media info response', [
        'media_id' => $mediaId,
        'status'   => $infoResponse->status(),
        'body'     => $infoResponse->json(),
    ]);

    if ($infoResponse->failed()) {
        return response()->json([
            'error' => 'Failed to get media info',
            'debug' => $infoResponse->json(),
        ], 404);
    }

    $mediaInfo = $infoResponse->json();
    $mediaUrl  = $mediaInfo['url']       ?? null;
    $mimeType  = $mediaInfo['mime_type'] ?? 'application/octet-stream';

    if (!$mediaUrl) {
        return response()->json(['error' => 'No URL from Meta'], 404);
    }

    // Download media
    $downloadResponse = Http::withHeaders([
        'Authorization' => 'Bearer ' . $user->access_token,
        'User-Agent'    => 'curl/7.64.1',
    ])->get($mediaUrl);

    if ($downloadResponse->failed()) {
        return response()->json(['error' => 'Failed to download'], 500);
    }

    // Save locally
    $ext      = explode('/', $mimeType)[1] ?? 'bin';
    $ext      = str_replace(['jpeg', 'quicktime', 'ogg; codecs=opus'], ['jpg', 'mov', 'ogg'], $ext);
    $filename = $mediaId . '.' . $ext;
    file_put_contents($cacheFolder . '/' . $filename, $downloadResponse->body());

    return response($downloadResponse->body())
        ->header('Content-Type', $mimeType)
        ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
        ->header('Cache-Control', 'public, max-age=86400')
        ->header('Accept-Ranges', 'bytes')
        ->header('Access-Control-Allow-Origin', '*');
}
    // ── GET WHATSAPP MEDIA TYPE ───────────────────
    private function getWhatsAppMediaType(string $mimeType): string
    {
        $types = [
            'image/' => 'image',
            'audio/' => 'audio',
            'video/' => 'video',
            'application/pdf' => 'document',
            'application/msword' => 'document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'document',
            'text/plain' => 'document',
            'application/zip' => 'document',
            'application/x-rar-compressed' => 'document',
        ];

        foreach ($types as $prefix => $type) {
            if (str_starts_with($mimeType, $prefix)) {
                return $type;
            }
        }

        return 'document'; // Default fallback
    }

    // ── PROCESS INCOMING MEDIA ────────────────────
    private function processIncomingMedia(array $media, string $type, string $accessToken): array
    {
        $mediaId = $media['id'] ?? null;
        if (!$mediaId) {
            return [];
        }

        // Get media URL
        $response = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/v19.0/{$mediaId}");

        if ($response->failed()) {
            return [];
        }

        $mediaInfo = $response->json();
        $url = $mediaInfo['url'] ?? null;

        return [
            'type' => $type,
            'id' => $mediaId,
            'url' => $url,
            'filename' => $media['filename'] ?? null,
            'caption' => $media['caption'] ?? null,
            'mime_type' => $media['mime_type'] ?? null,
            'size' => $mediaInfo['file_size'] ?? null,
        ];
    }

    // ── GET MEDIA PREVIEW TEXT ───────────────────
    private function getMediaPreviewText(string $mediaType, ?string $filename): string
    {
        switch ($mediaType) {
            case 'image':
                return '📷 Image';
            case 'audio':
                return '🎵 Audio';
            case 'video':
                return '🎥 Video';
            case 'document':
                return '📄 ' . ($filename ?: 'Document');
            default:
                return '📎 Media';
        }
    }

    // ── GET META TEMPLATES ─────────────────────────
    // public function getMetaTemplates()
    // {
    //     $userId = session('auth_user');
    //     if (!$userId) {
    //         return response()->json(['templates' => [], 'business_link' => null], 401);
    //     }

    //     $user = User::find($userId);
    //     if (!$user || !$user->phone_number_id || !$user->access_token) {
    //         return response()->json(['templates' => [], 'business_link' => null]);
    //     }

    //     $templates = $this->fetchMetaTemplates($user);
    //     return response()->json($templates);
    // }

    // ── SEND TEMPLATE ──────────────────────────────
    // public function sendTemplate(Request $request)
    // {
    //     $userId = session('auth_user');
    //     if (!$userId) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $user = User::find($userId);
    //     if (!$user) {
    //         return response()->json(['error' => 'User not found'], 404);
    //     }

    //     if (!$user->phone_number_id || !$user->access_token) {
    //         return response()->json(['error' => 'WhatsApp API not configured. Go to Dashboard → Setup'], 400);
    //     }

    //     $validated = $request->validate([
    //         'phone' => 'required|string',
    //         'template_name' => 'required|string',
    //         'language_code' => 'required|string',
    //         'parameters' => 'nullable|array',
    //         'parameters.*' => 'required|string',
    //     ]);

    //     $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
    //     $templateName = trim($validated['template_name']);
    //     $languageCode = trim($validated['language_code']);
    //     $parameters = $validated['parameters'] ?? [];

    //     if (!$phone || !$templateName) {
    //         return response()->json(['error' => 'Phone and template are required'], 422);
    //     }

    //     $payload = [
    //         'messaging_product' => 'whatsapp',
    //         'to' => $phone,
    //         'type' => 'template',
    //         'template' => [
    //             'name' => $templateName,
    //             'language' => ['code' => $languageCode],
    //         ],
    //     ];

    //     if (!empty($parameters)) {
    //         $payload['template']['components'] = [
    //             [
    //                 'type' => 'body',
    //                 'parameters' => array_map(fn ($value) => ['type' => 'text', 'text' => $value], $parameters),
    //             ],
    //         ];
    //     }

    //     $response = Http::withToken($user->access_token)
    //         ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", $payload);

    //     if ($response->status() === 401) {
    //         return response()->json([
    //             'error' => '❌ Token Invalid or Expired. 
    // 
    // 
    // 
    // Go to Dashboard → Reconnect WhatsApp'
    //         ], 401);
    //     }

    //     if ($response->failed()) {
    //         $errorBody = $response->json();
    //         $errorMsg = $errorBody['error']['message'] ?? 'Unknown error';
    //         return response()->json(['error' => '❌ Failed to send template: ' . $errorMsg], $response->status());
    //     }

    //     Message::create([
    //         'user_id' => $userId,
    //         'wa_id' => $phone,
    //         'message' => "Template: {$templateName}",
    //         'type' => 'outgoing',
    //         'status' => 'sent',
    //     ]);

    //     return response()->json(['status' => 'sent']);
    // }

    // protected function fetchMetaTemplates(User $user): array
    // {
    //     if (!$user->phone_number_id || !$user->access_token) {
    //         return ['templates' => [], 'business_link' => null];
    //     }

    //     $accountResponse = Http::withToken($user->access_token)
    //         ->get("https://graph.facebook.com/v19.0/{$user->phone_number_id}?fields=whatsapp_business_account{id}");

    //     $businessId = $accountResponse->json('whatsapp_business_account.id');
    //     $businessLink = $businessId
    //         ? "https://business.facebook.com/wa/manage_templates?business_id={$businessId}"
    //         : null;

    //     if (!$businessId) {
    //         return ['templates' => [], 'business_link' => $businessLink];
    //     }

    //     $templateResponse = Http::withToken($user->access_token)
    //         ->get("https://graph.facebook.com/v19.0/{$businessId}/message_templates?fields=name,language,category,status,components");

    //     if ($templateResponse->failed()) {
    //         return ['templates' => [], 'business_link' => $businessLink];
    //     }

    //     $templates = collect($templateResponse->json('data', []))->map(function ($item) {
    //         $components = $item['components'] ?? [];
    //         $bodyText = '';
    //         foreach ($components as $component) {
    //             if (strtoupper($component['type'] ?? '') === 'BODY') {
    //                 $bodyText = $component['text'] ?? '';
    //                 break;
    //             }
    //         }

    //         preg_match_all('/\{\{\s*\d+\s*\}\}/', $bodyText, $matches);
    //         $parameterCount = count($matches[0] ?? []);

    //         return [
    //             'name' => $item['name'] ?? '',
    //             'language' => $item['language']['code'] ?? 'en_US',
    //             'status' => $item['status'] ?? 'unknown',
    //             'category' => $item['category'] ?? 'unknown',
    //             'preview' => $bodyText,
    //             'parameter_count' => $parameterCount,
    //         ];
    //     })->toArray();

    //     return ['templates' => $templates, 'business_link' => $businessLink];
    // }
  // ── RECEIVE WEBHOOK ────────────────────────────
    public function receiveWebhook(Request $request)
    {
        if ($request->isMethod('get')) {
            if ($request->hub_verify_token === env('VERIFY_TOKEN', 'test123')) {
                return response($request->hub_challenge, 200);
            }
            return response('Forbidden', 403);
        }
        $body = $request->all();
        Log::info('=== WEBHOOK HIT ===', $body);
        try {
            $entry   = $body['entry'][0] ?? null;
            $changes = $entry['changes'][0] ?? null;
            $value   = $changes['value'] ?? null;
            if (!$value) return response()->json(['status' => 'empty']);
            $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
            $user = User::where('phone_number_id', $phoneNumberId)->first();
            if (!$user) { Log::error('User not found: ' . $phoneNumberId); return response()->json(['status' => 'user_not_found']); }
            if (!isset($value['messages'])) { Log::info('Status update only'); return response()->json(['status' => 'status_only']); }
            foreach ($value['messages'] as $msg) {
                $messageId = $msg['id'] ?? null;
                $fromPhone = $msg['from'];
                $type      = $msg['type'] ?? 'text';
                $replyToId = $msg['context']['id'] ?? null;
                if (!$messageId || !$fromPhone) continue;
                if (Message::where('meta_message_id', $messageId)->exists()) { Log::info('Duplicate: ' . $messageId); continue; }
                $hasAccess = \Illuminate\Support\Facades\DB::table('subscriptions')->where('user_id',$user->id)->where('status','active')->where('expires_at','>',now())->exists();
                $inTrial = $user->free_trial_enabled && $user->trial_ends_at && \Carbon\Carbon::parse($user->trial_ends_at)->isFuture();
                if (!$hasAccess && !$inTrial) { Log::info('Message blocked - no plan',['user_id'=>$user->id]); continue; }
                $text = ''; $mediaData = null;
                if ($type === 'reaction') {
                    $emoji = $msg['reaction']['emoji'] ?? '';
                    $reactedMsgId = $msg['reaction']['message_id'] ?? null;
                    if ($reactedMsgId && $emoji) {
                        Message::where('user_id',$user->id)->where(function($q) use ($reactedMsgId){ $q->where('meta_message_id',$reactedMsgId)->orWhere('message_id',$reactedMsgId)->orWhere('whatsapp_message_id',$reactedMsgId); })->update(['reaction'=>$emoji]);
                        Log::info('Reaction saved',['emoji'=>$emoji]);
                    }
                    continue;
                }
                switch ($type) {
                    case 'interactive': $text = $msg['interactive']['list_reply']['title'] ?? ($msg['interactive']['button_reply']['title'] ?? ''); break;
                    case 'text': $text = $msg['text']['body'] ?? ''; break;
                    case 'image': $text = '📷 Image'; $mediaData = $this->processIncomingMedia($msg['image'], $type, $user->access_token); break;
                    case 'audio': $isVoice = $msg['audio']['voice'] ?? false; $text = $isVoice ? '🎵 Voice message' : '🎵 Audio'; $mediaData = $this->processIncomingMedia($msg['audio'], $type, $user->access_token); break;
                    case 'video': $text = '🎥 Video'; $mediaData = $this->processIncomingMedia($msg['video'], $type, $user->access_token); break;
                    case 'document': $text = '📄 ' . ($msg['document']['filename'] ?? 'Document'); $mediaData = $this->processIncomingMedia($msg['document'], $type, $user->access_token); break;
                    case 'location': $text = '📍 Location'; break;
                    case 'contacts': $text = '👤 Contact'; break;
                    case 'sticker': $text = '🎭 Sticker'; break;
                    case 'button': $text = $msg['button']['text'] ?? '🔘 Button reply'; break;
                    case 'order': $text = '🛒 Order'; break;
                    case 'poll': $text = '📊 Poll: ' . ($msg['poll']['title'] ?? 'Poll'); break;
                    case 'poll_update': $text = '📊 Poll update'; break;
                    case 'system': $text = '⚙️ System message'; break;
                    case 'unsupported': $text = '❓ Unsupported message'; break;
                    default: $text = '💬 ' . ucfirst($type) . ' message'; break;
                }
                if (!$text) continue;
                $saved = Message::create([
                    'user_id'=>$user->id,'wa_id'=>$fromPhone,'message'=>$text,'type'=>'incoming','status'=>'received',
                    'meta_message_id'=>$messageId,'reply_to_id'=>$replyToId,
                    'media_type'=>$mediaData['type']??null,'media_url'=>$mediaData['url']??null,'media_id'=>$mediaData['id']??null,
                    'media_caption'=>$mediaData['caption']??null,'media_filename'=>$mediaData['filename']??null,
                    'media_mime_type'=>$mediaData['mime_type']??null,'media_size'=>$mediaData['size']??null,
                ]);
                Log::info('✅ MESSAGE SAVED',['id'=>$saved->id,'user_id'=>$user->id,'from'=>$fromPhone,'message'=>$text]);
                $abtHandled = false;
                $abtAiSettings = \DB::table('ai_settings')->where('user_id', $user->id)->first();
                $abtKey = ($abtAiSettings && $abtAiSettings->claude_api_key) ? \App\Http\Controllers\AiChatController::decryptKeyPublic($abtAiSettings->claude_api_key) : null;
                $abtHandled = \App\Http\Controllers\AppFlowEngine::tryHandle($user->id, $fromPhone, $text ?? '', 'whatsapp', $abtKey);
                if ($abtHandled === false && $user->bot_status !== 'off') {
                    \App\Http\Controllers\AutomationController::processIncoming($user->id, $fromPhone, $text ?? '');
                    \App\Http\Controllers\AiChatController::processMessage($user->id, $fromPhone, $text ?? '', 'whatsapp');
                }
            }
        } catch (\Exception $e) {
            Log::error('Webhook exception: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
        return response()->json(['status' => 'ok']);
    }
    public function toggleBot(Request $request)
    {
        $user = User::find(session('auth_user'));
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $status = $request->status === 'on' ? 'on' : 'off';
        $user->update(['bot_status' => $status]);

        return response()->json(['status' => $status]);
    }

    // ── WALLET BALANCE ─────────────────────────────────
    public function walletBalance()
    {
        $userId = session('auth_user');
        if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);
        $user = User::find($userId);
        if (!$user || !$user->access_token || !$user->business_account_id) {
            return response()->json(['balance' => null, 'error' => 'Not configured']);
        }
        try {
            $resp = Http::timeout(8)->withToken($user->access_token)
                ->get("https://graph.facebook.com/v19.0/{$user->business_account_id}", [
                    'fields' => 'funding_source_details,balance',
                ]);
            if (!$resp->successful()) {
                return response()->json(['balance' => null, 'error' => 'Permission denied. Token needs business_management permission.']);
            }
            $data     = $resp->json();
            $balance  = $data['balance']['amount_due'] ?? $data['funding_source_details']['amount'] ?? null;
            $currency = $data['balance']['currency']   ?? $data['funding_source_details']['currency'] ?? 'USD';
            return response()->json(['balance' => $balance, 'currency' => $currency]);
        } catch (\Exception $e) {
            return response()->json(['balance' => null, 'error' => 'Could not fetch wallet balance.']);
        }
    }

    // ── LAST UPDATE (auto refresh) ─────────────────
   public function lastUpdate()
{
    try {
        $userId = session('auth_user');
        if (!$userId) {
            return response()->json(['last' => null]);
        }
        $user    = User::find($userId);
        $ownerId = $user ? ($user->parent_user_id ?? $userId) : $userId;
        $last = Message::where('user_id', $ownerId)
            ->orderBy('created_at', 'desc')
            ->first();
        return response()->json([
            'last' => $last ? $last->created_at : null
        ]);
    } catch (\Exception $e) {
        return response()->json(['last' => null]);
    }
}
// public function metaTemplates()
// {
//     $user = User::findOrFail(session('auth_user'));

//     if (!$user->access_token || !$user->business_account_id) {
//         return response()->json([
//             'templates' => []
//         ]);
//     }

//     $response = Http::withToken($user->access_token)
//         ->get("https://graph.facebook.com/v22.0/{$user->business_account_id}/message_templates");

//     if (!$response->successful()) {
//         return response()->json([
//             'templates' => []
//         ]);
//     }

//     $templates = collect($response->json('data', []))->map(function ($tpl) {

//         $preview = '';

//         if (!empty($tpl['components'])) {
//             foreach ($tpl['components'] as $component) {

//                 if (($component['type'] ?? '') === 'BODY') {
//                     $preview = $component['text'] ?? '';
//                 }
//             }
//         }

//         preg_match_all('/\{\{\d+\}\}/', $preview, $matches);

//         return [
//             'name' => $tpl['name'] ?? '',
//             'language' => $tpl['language'] ?? 'en',
//             'status' => $tpl['status'] ?? '',
//             'category' => $tpl['category'] ?? '',
//             'preview' => $preview,
//             'parameter_count' => count($matches[0]),
//         ];
//     });

//     return response()->json([
//         'templates' => $templates
//     ]);
// }
public function businessNumbers()
{
    $user = User::findOrFail(session('auth_user'));

    if (!$user->access_token || !$user->business_account_id) {

        return response()->json([
            'error' => 'Business account ID or token missing'
        ], 400);
    }

    $response = Http::withToken($user->access_token)
        ->get(
            "https://graph.facebook.com/v22.0/{$user->business_account_id}/phone_numbers"
        );

    if (!$response->successful()) {

        return response()->json([
            'error' => $response->json()
        ], 500);
    }

    $numbers = collect($response->json('data', []))->map(function ($num) {

        return [
            'id' => $num['id'] ?? '',
            'number' => $num['display_phone_number'] ?? '',
        ];
    });

    return response()->json([
        'numbers' => $numbers
    ]);
}
public function sendTemplate(Request $request)
{
    $user = User::findOrFail(session('auth_user'));
    $ownerId = $user->parent_user_id ?? $user->id;
    $owner = ($ownerId !== $user->id) ? User::find($ownerId) : $user;
    if (!$owner) $owner = $user;

    if (!$request->template_name || !$request->language_code || !$request->recipient_phone) {
        return response()->json(['error' => 'template_name, language_code and recipient_phone are required'], 422);
    }

    $parameters = [];
    if (!empty($request->parameters)) {
        foreach ($request->parameters as $value) {
            $parameters[] = ['type' => 'text', 'text' => $value];
        }
    }

    $payload = [
        'messaging_product' => 'whatsapp',
        'to'                => preg_replace('/[^0-9]/', '', $request->recipient_phone),
        'type'              => 'template',
        'template'          => [
            'name'     => $request->template_name,
            'language' => ['code' => $request->language_code],
        ],
    ];

    $components = [];
    
    // Add header component if image URL provided
    $headerImage = $request->header_image;
    // If no image provided but template has image header, use the stored handle
    if (!$headerImage && $request->header_handle) {
        $headerImage = $request->header_handle;
    }
    if ($headerImage) {
        $components[] = [
            'type' => 'header',
            'parameters' => [
                ['type' => 'image', 'image' => ['link' => $headerImage]]
            ]
        ];
    }
    
    // Add body component if parameters exist
    if (count($parameters) > 0) {
        $components[] = ['type' => 'body', 'parameters' => $parameters];
    }
    
    if (count($components) > 0) {
        $payload['template']['components'] = $components;
    }

    Log::info('TEMPLATE PAYLOAD', $payload);

    $response = Http::withToken($owner->access_token)
        ->post("https://graph.facebook.com/v19.0/{$owner->phone_number_id}/messages", $payload);

    Log::info('TEMPLATE RESPONSE', ['status' => $response->status(), 'body' => $response->json()]);

    if ($response->failed()) {
        $error = $response->json('error.message') ?? 'Failed to send template';
        return response()->json(['error' => $error], 400);
    }

    $msgId = $response->json('messages.0.id');

    Message::create([
        'user_id'         => $user->id,
        'wa_id'           => preg_replace('/[^0-9]/', '', $request->recipient_phone),
        'message'         => 'Template: ' . $request->template_name,
        'type'            => 'outgoing',
        'status'          => 'sent',
        'meta_message_id' => $msgId,
    ]);

    return response()->json(['success' => true, 'message_id' => $msgId]);
}

public function metaTemplates()
{
    $user = User::findOrFail(session('auth_user'));

    if (!$user->access_token || !$user->business_account_id) {

        return response()->json([
            'templates' => []
        ]);
    }

    $response = Http::withToken($user->access_token)
        ->get(
            "https://graph.facebook.com/v22.0/{$user->business_account_id}/message_templates"
        );

    if (!$response->successful()) {

        return response()->json([
            'templates' => [],
            'error' => $response->json()
        ]);
    }

    $templates = collect($response->json('data', []))
        ->map(function ($tpl) {

            $bodyText = '';

            if (!empty($tpl['components'])) {

                foreach ($tpl['components'] as $component) {

                    if (($component['type'] ?? '') === 'BODY') {

                        $bodyText = $component['text'] ?? '';
                    }
                }
            }

            preg_match_all('/\{\{\d+\}\}/', $bodyText, $matches);

            return [

                'name' => $tpl['name'] ?? '',

                'language' =>
                    $tpl['language'] ?? 'en_US',

                'status' =>
                    $tpl['status'] ?? '',

                'category' =>
                    $tpl['category'] ?? '',

                'preview' => $bodyText,

                'parameter_count' =>
                    count($matches[0]),

                'components' =>
                    $tpl['components'] ?? []
            ];
        });

    return response()->json([
        'templates' => $templates->values()
    ]);
}
// ── REACT TO MESSAGE ───────────────────────────────
public function reactMessage(Request $request)
{
    $userId = session('auth_user');
    if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

    $message = Message::where('id', $request->message_id)
        ->where('user_id', $userId)
        ->first();

    if (!$message) return response()->json(['error' => 'Message not found'], 404);

    $message->update(['reaction' => $request->emoji]);

    return response()->json(['status' => 'reacted', 'emoji' => $request->emoji]);
}

// ── FORWARD MESSAGE ────────────────────────────────
public function forwardMessage(Request $request)
{
    $userId = session('auth_user');
    if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

    $user  = User::findOrFail($userId);
    $phones = $request->phones ?? [];
    $text  = $request->message ?? '';

    if (empty($phones) || !$text) {
        return response()->json(['error' => 'Phones and message required'], 422);
    }

    $sent = 0;
    foreach ($phones as $phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $response = Http::withToken($user->access_token)
            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $phone,
                'type'              => 'text',
                'text'              => ['body' => $text],
            ]);

        if ($response->successful()) {
            Message::create([
                'user_id' => $userId,
                'wa_id'   => $phone,
                'message' => $text,
                'type'    => 'outgoing',
                'status'  => 'sent',
            ]);
            $sent++;
        }
    }

    return response()->json(['status' => 'forwarded', 'sent' => $sent]);
}

// ── GET PROFILE ────────────────────────────────────
public function getProfile($phone)
{
    $userId = session('auth_user');
    if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

    $phoneClean = preg_replace('/[^0-9]/', '', $phone);

    $contact = Contact::where('user_id', $userId)
        ->whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') = ?", [$phoneClean])
        ->first();

    $messageCount = Message::where('user_id', $userId)
        ->where('wa_id', $phone)
        ->count();

    $sharedMedia = Message::where('user_id', $userId)
        ->where('wa_id', $phone)
        ->whereNotNull('media_id')
        ->orderByDesc('created_at')
        ->take(6)
        ->get()
        ->map(fn($m) => [
            'media_id'   => $m->media_id,
            'media_type' => $m->media_type,
            'created_at' => Carbon::parse($m->created_at)->setTimezone('Asia/Kolkata')->format('d M Y'),
        ]);

    return response()->json([
        'phone'         => $phone,
        'name'          => $contact?->name,
        'is_saved'      => $contact !== null,
        'message_count' => $messageCount,
        'shared_media'  => $sharedMedia,
    ]);
}

// ── SAVE CONTACT ───────────────────────────────────
public function saveContact(Request $request)
{
    $userId = session('auth_user');
    if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

    $request->validate([
        'name'  => 'required|string|max:100',
        'phone' => 'required|string',
    ]);

    $contact = Contact::updateOrCreate(
        ['user_id' => $userId, 'phone' => $request->phone],
        ['name'    => $request->name]
    );

    return response()->json(['status' => 'saved', 'contact' => $contact]);
}
// ── DELETE MESSAGE ─────────────────────────────────
public function deleteMessage(Request $request)
{
    $userId = session('auth_user');
    if (!$userId) return response()->json(['error' => 'Unauthorized'], 401);

    $message = Message::where('id', $request->message_id)
        ->where('user_id', $userId)
        ->first();

    if (!$message) return response()->json(['error' => 'Not found'], 404);

    $message->delete();

    return response()->json(['status' => 'deleted']);
}

    // Aliases for new route structure
    public function chat() { return $this->index(); }
    public function webhook(\Illuminate\Http\Request $request) { return $this->receiveWebhook($request); }
    public function checkWindow($phone) {
        $userId  = session('auth_user');
        $user    = \App\Models\User::find($userId);
        $ownerId = $user ? ($user->parent_user_id ?? $userId) : $userId;
        $clean   = preg_replace('/[^0-9]/', '', $phone);
        $lastIncoming = \Illuminate\Support\Facades\DB::table('messages')->where('user_id',$ownerId)->whereIn('wa_id',[$phone,$clean])->where('type','incoming')->latest('created_at')->value('created_at');
        if (!$lastIncoming) return response()->json(['locked'=>true,'message'=>'This contact has never messaged you. Send a template to start the conversation.']);
        $hoursSince = \Carbon\Carbon::parse($lastIncoming)->diffInHours(now());
        if ($hoursSince >= 24) return response()->json(['locked'=>true,'message'=>"Last message was {$hoursSince} hours ago. Send a template to reopen the chat window."]);
        return response()->json(['locked'=>false]);
    }
    public function react(\Illuminate\Http\Request $request) { return $this->reactMessage($request); }
    public function changePassword(\Illuminate\Http\Request $request) {
        $userId = session('auth_user');
        $user   = \App\Models\User::findOrFail($userId);
        if (!\Illuminate\Support\Facades\Hash::check($request->old_password, $user->password)) return response()->json(['error'=>'Current password is incorrect.']);
        $user->update(['password'=>\Illuminate\Support\Facades\Hash::make($request->new_password)]);
        return response()->json(['success'=>true]);
    }
    public function agentDashboard() { $user = \App\Models\User::findOrFail(session('auth_user')); return view('agent.dashboard', compact('user')); }
    public function agentPassword()  { $user = \App\Models\User::findOrFail(session('auth_user')); return view('agent.password', compact('user')); }
    public function teamMembers()    { return $this->getTeamMembers(); }
    public function uploadMedia(\Illuminate\Http\Request $request) { return response()->json(['success'=>false,'error'=>'Use send endpoint']); }
   
    
    private function ownerId() { $u=\App\Models\User::find(session('auth_user')); return $u?($u->parent_user_id??$u->id):session('auth_user'); }
}