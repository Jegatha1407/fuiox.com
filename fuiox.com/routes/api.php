<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// ── API AUTH MIDDLEWARE ──────────────────────────────────────────────────────
function apiAuth(Request $req): ?User
{
    $key = $req->header('X-API-Key') ?? $req->query('api_key');
    if (!$key) return null;
    $userData = DB::table('users')->where('api_key', $key)->first();
    return $userData ? User::find($userData->id) : null;
}

// ── SEND TEXT MESSAGE ────────────────────────────────────────────────────────
Route::post('/messages/send', function (Request $req) {
    $user = apiAuth($req);
    if (!$user) return response()->json(['error' => 'Invalid API key'], 401);
    if (!$req->phone || !$req->message) return response()->json(['error' => 'phone and message are required'], 400);

    $phone = preg_replace('/[^0-9]/', '', $req->phone);
    $resp  = Http::withToken($user->access_token)
        ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
            'messaging_product' => 'whatsapp',
            'to'   => $phone,
            'type' => 'text',
            'text' => ['body' => $req->message],
        ]);

    DB::table('messages')->insert([
        'user_id'    => $user->id,
        'wa_id'      => $phone,
        'message'    => $req->message,
        'type'       => 'outgoing',
        'status'     => 'sent',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['success' => $resp->successful(), 'status' => $resp->status()]);
});

// ── SEND IMAGE MESSAGE ────────────────────────────────────────────────────────
Route::post('/messages/send-image', function (Request $req) {
    $user = apiAuth($req);
    if (!$user) return response()->json(['error' => 'Invalid API key'], 401);

    // Parse phones
    $phonesRaw = $req->phones ?? $req->phone ?? null;
    $phones = [];

    if ($req->hasFile('phones_file')) {
        $file    = $req->file('phones_file');
        $ext     = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->path());

        if (in_array($ext, ['csv', 'txt'])) {
            foreach (preg_split('/\r\n|\r|\n/', $content) as $line) {
                foreach (str_getcsv($line) as $col) {
                    $num = preg_replace('/[^0-9]/', '', trim($col));
                    if (strlen($num) >= 9) $phones[] = $num;
                }
            }
        } elseif ($ext === 'xlsx') {
            $zip     = new ZipArchive();
            $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
            file_put_contents($tmpFile, $content);
            if ($zip->open($tmpFile) === true) {
                $sharedStrings = '';
                $sheetXml      = '';
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (str_contains($name, 'sharedStrings')) $sharedStrings = $zip->getFromIndex($i);
                    if (str_contains($name, 'sheet1') && str_ends_with($name, '.xml')) $sheetXml = $zip->getFromIndex($i);
                }
                $zip->close();
                $strings = [];
                preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $sharedStrings, $sm);
                if (!empty($sm[1])) $strings = $sm[1];
                preg_match_all('/<v>(\d+)<\/v>/', $sheetXml, $allVals);
                foreach ($allVals[1] as $val) {
                    $num = preg_replace('/[^0-9]/', '', $val);
                    if (strlen($num) >= 9 && strlen($num) <= 15) {
                        $phones[] = $num;
                    } elseif (isset($strings[(int)$val])) {
                        $str = preg_replace('/[^0-9]/', '', $strings[(int)$val]);
                        if (strlen($str) >= 9 && strlen($str) <= 15) $phones[] = $str;
                    }
                }
                foreach ($strings as $s) {
                    $num = preg_replace('/[^0-9]/', '', trim($s));
                    if (strlen($num) >= 9 && strlen($num) <= 15) $phones[] = $num;
                }
                unlink($tmpFile);
            }
        }
    } elseif ($phonesRaw) {
        if (is_string($phonesRaw)) {
            $decoded = json_decode($phonesRaw, true);
            $phones  = is_array($decoded) ? $decoded : array_map('trim', explode(',', $phonesRaw));
        } else {
            $phones = (array) $phonesRaw;
        }
    }

    // Auto-add country code for 10-digit Indian numbers
    $phones = array_map(function ($p) {
        $p = preg_replace('/[^0-9]/', '', $p);
        if (strlen($p) === 10) $p = '91' . $p;
        return $p;
    }, $phones);
    $phones = array_unique(array_filter($phones, fn($p) => strlen($p) >= 10));

    if (empty($phones)) return response()->json(['error' => 'No valid phone numbers found'], 400);

    // Handle image
    $imageUrl = null;
    $mediaId  = null;

    if ($req->hasFile('image_url') || $req->hasFile('image')) {
        $file      = $req->file('image_url') ?? $req->file('image');
        $uploadResp = Http::withToken($user->access_token)
            ->attach('file', file_get_contents($file->path()), $file->getClientOriginalName(), ['Content-Type' => $file->getMimeType()])
            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/media", [
                'messaging_product' => 'whatsapp',
            ]);
        if (!$uploadResp->successful()) {
            return response()->json(['error' => 'Image upload failed', 'details' => $uploadResp->json()], 400);
        }
        $mediaId = $uploadResp->json('id');
    } elseif ($req->image_url) {
        $imageUrl = $req->image_url;
    } else {
        return response()->json(['error' => 'image or image_url required'], 400);
    }

    $sent = 0; $failed = 0; $results = [];

    foreach ($phones as $phone) {
        $imageData = $mediaId ? ['id' => $mediaId] : ['link' => $imageUrl];
        if ($req->caption) $imageData['caption'] = $req->caption;

        $body = ['messaging_product' => 'whatsapp', 'to' => $phone, 'type' => 'image', 'image' => $imageData];
        $resp = Http::withToken($user->access_token)->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", $body);

        if ($resp->successful()) {
            $sent++;
            DB::table('messages')->insert(['user_id'=>$user->id,'wa_id'=>$phone,'message'=>'[Image] '.($req->caption??''),'media_type'=>'image','type'=>'outgoing','status'=>'sent','created_at'=>now(),'updated_at'=>now()]);
        } else {
            $failed++;
        }
        $results[] = ['phone' => $phone, 'status' => $resp->successful() ? 'sent' : 'failed', 'response' => $resp->json()];
    }

    return response()->json(['success' => true, 'sent' => $sent, 'failed' => $failed, 'total' => count($phones), 'results' => $results]);
});

// ── SEND TEMPLATE ─────────────────────────────────────────────────────────────
Route::post('/templates/send', function (Request $req) {
    $user = apiAuth($req);
    if (!$user) return response()->json(['error' => 'Invalid API key'], 401);

    $phone  = preg_replace('/[^0-9]/', '', $req->phone);
    $params = $req->parameters ?? [];
    $components = [];

    if ($req->header_image) {
        $components[] = ['type' => 'header', 'parameters' => [['type' => 'image', 'image' => ['link' => $req->header_image]]]];
    }
    if (!empty($params)) {
        $components[] = ['type' => 'body', 'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $params)];
    }

    $tpl = ['name' => $req->template_name, 'language' => ['code' => $req->language ?? 'en_US']];
    if (!empty($components)) $tpl['components'] = $components;

    $resp = Http::withToken($user->access_token)
        ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
            'messaging_product' => 'whatsapp',
            'to'   => $phone,
            'type' => 'template',
            'template' => $tpl,
        ]);

    return response()->json(['success' => $resp->successful(), 'status' => $resp->status(), 'response' => $resp->json()]);
});

// ── GET CONTACTS ──────────────────────────────────────────────────────────────
Route::get('/contacts', function (Request $req) {
    $user = apiAuth($req);
    if (!$user) return response()->json(['error' => 'Invalid API key'], 401);
    return response()->json(['contacts' => DB::table('contacts')->where('user_id', $user->id)->get()]);
});

// ── ADD CONTACT ───────────────────────────────────────────────────────────────
Route::post('/contacts', function (Request $req) {
    $user = apiAuth($req);
    if (!$user) return response()->json(['error' => 'Invalid API key'], 401);
    $id = DB::table('contacts')->insertGetId([
        'user_id'    => $user->id,
        'name'       => $req->name,
        'phone'      => $req->phone,
        'email'      => $req->email ?? null,
        'tags'       => $req->tags ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    return response()->json(['success' => true, 'contact' => ['id' => $id]]);
});

// ── GET MESSAGE HISTORY ───────────────────────────────────────────────────────
Route::get('/messages/{phone}', function (Request $req, $phone) {
    $user = apiAuth($req);
    if (!$user) return response()->json(['error' => 'Invalid API key'], 401);
    $msgs = DB::table('messages')
        ->where('user_id', $user->id)
        ->where('wa_id', $phone)
        ->orderBy('created_at')
        ->get();
    return response()->json(['messages' => $msgs]);
});

// ── GET TEMPLATES ─────────────────────────────────────────────────────────────
Route::get('/templates', function (Request $req) {
    $user = apiAuth($req);
    if (!$user) return response()->json(['error' => 'Invalid API key'], 401);
    $resp = Http::withToken($user->access_token)
        ->get("https://graph.facebook.com/v19.0/{$user->business_account_id}/message_templates", [
            'limit' => 100,
        ]);
    return response()->json(['templates' => $resp->json('data') ?? []]);
});

// ── LAUNCH CAMPAIGN ───────────────────────────────────────────────────────────
Route::post('/campaigns', function (Request $req) {
    $user   = apiAuth($req);
    if (!$user) return response()->json(['error' => 'Invalid API key'], 401);
    $phones = $req->phones ?? [];
    $sent   = 0;
    $failed = 0;

    foreach ($phones as $phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $resp  = Http::withToken($user->access_token)
            ->post("https://graph.facebook.com/v19.0/{$user->phone_number_id}/messages", [
                'messaging_product' => 'whatsapp',
                'to'       => $phone,
                'type'     => 'template',
                'template' => ['name' => $req->template_name, 'language' => ['code' => $req->language ?? 'en_US']],
            ]);
        $resp->successful() ? $sent++ : $failed++;
    }

    return response()->json(['success' => true, 'sent' => $sent, 'failed' => $failed, 'total' => count($phones)]);
});