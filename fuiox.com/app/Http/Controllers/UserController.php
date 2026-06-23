<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    private function userId(): int { return session('auth_user'); }
    private function user(): User  { return User::findOrFail($this->userId()); }

    
    public function dashboard()
    {
        $user = $this->user();
        $totalMessages   = DB::table('messages')->where('user_id',$user->id)->distinct('wa_id')->count('wa_id');
        $pendingMessages = DB::table('messages')->where('user_id',$user->id)->where('type','incoming')->where('read',false)->count();
        $stats = ['total_messages'=>$totalMessages,'pending_messages'=>$pendingMessages];
        return view('user.dashboard', compact('user','stats'));
    }

    
    public function setup()
    {
        $user = $this->user();
        // If already connected, go to dashboard
        if ($user->phone_number_id && $user->access_token) {
            return redirect()->route('dashboard');
        }
        return view('setup', compact('user'));
    }
    public function updateSetup(Request $request)
    {
        $token   = $request->access_token;
        $phoneId = $request->phone_number_id;
        $wabaId  = $request->business_account_id;

        if (!$token || !$phoneId) {
            return back()->withErrors(['token' => 'Please provide Access Token and Phone Number ID.']);
        }

        // Check if another user already uses these credentials
        $existing = DB::table('users')
            ->where('phone_number_id', $phoneId)
            ->where('id', '!=', $this->userId())
            ->first();
        if ($existing) {
            return back()->withErrors(['token' => 'These credentials are already connected to another account.']);
        }

        // Verify with Meta API
        $verify = \Illuminate\Support\Facades\Http::withToken($token)
            ->get("https://graph.facebook.com/v19.0/{$phoneId}?fields=display_phone_number,verified_name");

        if ($verify->failed() || isset($verify->json()['error'])) {
            $errMsg = $verify->json()['error']['message'] ?? 'Invalid credentials. Please check your Access Token and Phone Number ID.';
            return back()->withErrors(['token' => $errMsg]);
        }

        $this->user()->update([
            'phone_number_id'     => $phoneId,
            'access_token'        => $token,
            'business_account_id' => $wabaId,
            'organisation'        => $request->organisation ?? $this->user()->organisation,
            'mobile'              => $request->mobile ?? $this->user()->mobile,
        ]);

        // Logout and redirect to login
        $userId = session('auth_user');
        session()->flush();
        return redirect()->route('login')->with('success', '✅ WhatsApp connected! Please login to continue.');
    }

    
    public function settings()
    {
        $user = $this->user();
        return view('user.settings', compact('user'));
    }
    public function updateSettings(Request $request)
    {
        $user = $this->user();
        $data = [];
        if ($request->name)         $data['name']         = $request->name;
        if ($request->organisation) $data['organisation']  = $request->organisation;
        if ($request->filled('mobile')) $data['mobile']   = $request->mobile;
        if ($request->filled('address')) $data['address'] = $request->address;
        if ($request->filled('city'))    $data['city']    = $request->city;
        if ($request->filled('state'))   $data['state']   = $request->state;
        if ($request->filled('country')) $data['country'] = $request->country;
        if ($request->filled('pincode')) $data['pincode'] = $request->pincode;
        if ($request->password) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }
        if (!empty($data)) $user->update($data);
        return redirect()->route('settings')->with('success','✅ Settings updated!');
    }

    
    public function apiDocs()
    {
        $user = $this->user();
        return view('user.api_docs', compact('user'));
    }

    
    public function generateApiKey()
    {
        $key = 'fxk_'.bin2hex(random_bytes(20));
        DB::table('users')->where('id',$this->userId())->update(['api_key'=>$key]);
        return response()->json(['key'=>$key]);
    }

    
    public function credentialRequest()
    {
        $user = $this->user();
        return view('user.credential_request', compact('user'));
    }
    public function submitCredentialRequest(Request $request)
    {
        $request->validate(['reason'=>'required|string|max:500']);
        DB::table('credential_requests')->insert(['user_id'=>$this->userId(),'reason'=>$request->reason,'status'=>'pending','created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('dashboard')->with('success','✅ Request submitted!');
    }
    public function fetchFromToken(Request $request)
{
    $token  = $request->access_token;
    $wabaId = $request->waba_id;
    \Illuminate\Support\Facades\Log::info('fetchFromToken called', ['token_len' => strlen($token??''), 'waba' => $wabaId]);

    $resp = \Illuminate\Support\Facades\Http::withToken($token)
        ->get("https://graph.facebook.com/v19.0/{$wabaId}/phone_numbers");

    if ($resp->failed()) {
        return response()->json(['error' => 'Invalid token or WABA ID. Please check and try again.']);
    }

    $phones = $resp->json('data');
    if (empty($phones)) {
        return response()->json(['error' => 'No phone numbers found for this account.']);
    }

    $phone = $phones[0];

    return response()->json([
        'phone_number_id'    => $phone['id'],
        'display_phone'      => $phone['display_phone_number'],
        'verified_name'      => $phone['verified_name'],
        'business_account_id'=> $wabaId,
        'mobile'             => preg_replace('/^91/', '', preg_replace('/[^0-9]/', '', $phone['display_phone_number'])),
    ]);
}
}