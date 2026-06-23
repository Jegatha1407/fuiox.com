<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class EmbeddedSignupController extends Controller
{
    private function userId(): int { return session('auth_user'); }

    // ── STEP 1: Exchange short-lived token for long-lived token ──
    public function exchangeToken(Request $request)
    {
        $code        = $request->code;
        $appId       = config('services.meta.app_id');
        $appSecret   = config('services.meta.app_secret');

        if (!$code) {
            return response()->json(['error' => 'No code provided'], 400);
        }

        // Exchange code for short-lived token
        $tokenResp = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
            'client_id'     => $appId,
            'client_secret' => $appSecret,
            'code'          => $code,
            'redirect_uri'  => config('app.url') . '/embedded-signup/callback',
        ]);

        if ($tokenResp->failed() || isset($tokenResp->json()['error'])) {
            Log::error('Token exchange failed', ['resp' => $tokenResp->json()]);
            return response()->json(['error' => 'Token exchange failed. Please try again.'], 400);
        }

        $shortToken = $tokenResp->json('access_token');

        // Exchange for long-lived token
        $longTokenResp = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $appId,
            'client_secret'     => $appSecret,
            'fb_exchange_token' => $shortToken,
        ]);

        $accessToken = $longTokenResp->json('access_token') ?? $shortToken;

        return response()->json(['access_token' => $accessToken]);
    }

    // ── STEP 2: Fetch WABA and phone details after signup ──
    public function fetchDetails(Request $request)
    {
        $accessToken = $request->access_token;
        $wabaId      = $request->waba_id;

        if (!$accessToken || !$wabaId) {
            return response()->json(['error' => 'Missing access token or WABA ID'], 400);
        }

        // Get phone numbers under this WABA
        $phonesResp = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/v19.0/{$wabaId}/phone_numbers", [
                'fields' => 'display_phone_number,verified_name,id,quality_rating,code_verification_status'
            ]);

        if ($phonesResp->failed()) {
            Log::error('Fetch phones failed', ['resp' => $phonesResp->json()]);
            return response()->json(['error' => 'Failed to fetch phone numbers. Check your permissions.'], 400);
        }

        $phones = $phonesResp->json('data');
        if (empty($phones)) {
            return response()->json(['error' => 'No phone numbers found in this WhatsApp Business Account.'], 400);
        }

        // Get WABA details
        $wabaResp = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/v19.0/{$wabaId}", [
                'fields' => 'name,currency,timezone_id'
            ]);

        $wabaName = $wabaResp->json('name') ?? '';

        $phone = $phones[0];
        $mobile = preg_replace('/^91/', '', preg_replace('/[^0-9]/', '', $phone['display_phone_number']));

        return response()->json([
            'phone_number_id'     => $phone['id'],
            'display_phone'       => $phone['display_phone_number'],
            'verified_name'       => $phone['verified_name'],
            'business_account_id' => $wabaId,
            'waba_name'           => $wabaName,
            'mobile'              => $mobile,
            'quality_rating'      => $phone['quality_rating'] ?? 'UNKNOWN',
            'all_phones'          => $phones, // In case user has multiple numbers
        ]);
    }

    // ── STEP 3: Save credentials and connect ──
    public function connect(Request $request)
    {
        $userId      = $this->userId();
        $token       = $request->access_token;
        $phoneId     = $request->phone_number_id;
        $wabaId      = $request->business_account_id;
        $mobile      = $request->mobile;
        $verifiedName = $request->verified_name;

        if (!$token || !$phoneId) {
            return response()->json(['error' => 'Missing required credentials.'], 400);
        }

        // Check if credentials already used by another user
        $existing = DB::table('users')
            ->where('phone_number_id', $phoneId)
            ->where('id', '!=', $userId)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'This WhatsApp number is already connected to another Fuiox account.'
            ], 400);
        }

        // Verify token works with Meta
        $verify = Http::withToken($token)
            ->get("https://graph.facebook.com/v19.0/{$phoneId}", [
                'fields' => 'display_phone_number,verified_name'
            ]);

        if ($verify->failed() || isset($verify->json()['error'])) {
            return response()->json(['error' => 'Could not verify credentials with Meta. Please try again.'], 400);
        }

        // Save to user
        DB::table('users')->where('id', $userId)->update([
            'access_token'        => $token,
            'phone_number_id'     => $phoneId,
            'business_account_id' => $wabaId,
            'mobile'              => $mobile,
            'organisation'        => $request->organisation ?? $verifiedName,
            'updated_at'          => now(),
        ]);

        Log::info('WhatsApp connected via Embedded Signup', [
            'user_id'  => $userId,
            'phone_id' => $phoneId,
            'waba_id'  => $wabaId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp connected successfully!',
            'redirect' => route('dashboard'),
        ]);
    }

    // ── Handle multiple phone numbers (if user has more than one) ──
    public function selectPhone(Request $request)
    {
        $accessToken = $request->access_token;
        $wabaId      = $request->waba_id;
        $phoneId     = $request->phone_number_id;

        // Fetch specific phone details
        $resp = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/v19.0/{$phoneId}", [
                'fields' => 'display_phone_number,verified_name,id'
            ]);

        if ($resp->failed()) {
            return response()->json(['error' => 'Failed to fetch phone details.'], 400);
        }

        $phone  = $resp->json();
        $mobile = preg_replace('/^91/', '', preg_replace('/[^0-9]/', '', $phone['display_phone_number']));

        return response()->json([
            'phone_number_id'     => $phone['id'],
            'display_phone'       => $phone['display_phone_number'],
            'verified_name'       => $phone['verified_name'],
            'business_account_id' => $wabaId,
            'mobile'              => $mobile,
        ]);
    }
}