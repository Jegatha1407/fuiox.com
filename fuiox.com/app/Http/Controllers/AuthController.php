<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\User;

class AuthController extends Controller
{
    private function sendBrevoEmail(string $toEmail, string $toName, string $subject, string $text): bool
    {
        $apiKey = env('BREVO_API_KEY');
        if (!$apiKey) return false;

        $response = Http::withHeaders([
            'api-key'      => $apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name'  => env('MAIL_FROM_NAME', 'Fuiox Technologies'),
                'email' => env('MAIL_FROM_ADDRESS', 'jegatheeshwari1407@gmail.com'),
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName]
            ],
            'subject'     => $subject,
            'textContent' => $text,
        ]);

        \Log::info('Brevo email', ['status' => $response->status(), 'to' => $toEmail]);
        return $response->successful();
    }
    // ── SHOW REGISTER ──────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    // ── REGISTER ───────────────────────────────────
    public function register(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:6|confirmed',
            'organisation' => 'required|string|max:100',
            // accept_terms validated manually below
            'mobile'       => 'nullable|string|max:20',
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'pincode'      => 'nullable|string|max:20',
        ], [
            'email.unique'          => 'This email is already registered.',
            'password.confirmed'    => 'Passwords do not match.',
            'password.regex'        => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.not_in'       => 'This password is too common. Please choose a stronger password.',

        ]);
        // terms check removed - validated on frontend
        // Generate OTP
        $otp = strval(rand(100000, 999999));

        // Create user as unverified
        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'organisation'   => $request->organisation,
            'role'           => 'user',
            'team_role'      => 'owner',
            'is_verified'    => false,
            'otp'            => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(1),
            'mobile'         => $request->mobile,
            'address'        => $request->address,
            'city'           => $request->city,
            'state'          => $request->state,
            'country'        => $request->country,
            'pincode'        => $request->pincode,
        ]);

        // Send OTP email via Brevo HTTP API
        try {
            $sent = $this->sendBrevoEmail(
                $user->email,
                $user->name,
                'Verify your email — Fuiox Technologies',
                "Hello {$user->name},\n\nYour Fuiox Technologies OTP is: {$otp}\n\nThis code expires in 10 minutes.\n\nDo not share this with anyone."
            );

            if (!$sent) {
                $user->delete();
                return back()->withInput()
                    ->withErrors(['email' => 'Failed to send OTP. Please check your email address.']);
            }
        } catch (\Exception $e) {
            $user->delete();
            return back()->withInput()
                ->withErrors(['email' => 'Failed to send OTP. Error: ' . $e->getMessage()]);
        }

        session(['otp_user_id' => $user->id]);
        session()->save();

        return redirect()->route('otp.show');
    }

    // ── SHOW OTP ───────────────────────────────────
    public function showOtp()
    {
        if (!session('otp_user_id')) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Session expired. Please register again.']);
        }

        $user = User::find(session('otp_user_id'));

        if (!$user) {
            return redirect()->route('register')
                ->withErrors(['email' => 'User not found. Please register again.']);
        }

        return view('auth.otp', ['email' => $user->email]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = User::find(session('otp_user_id'));

        if (!$user) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Session expired. Register again.']);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'OTP expired. Please resend.']);
        }

        if ($user->otp !== $request->otp) {
            return back()->withErrors(['otp' => 'Incorrect OTP. Try again.']);
        }

        $user->update([
            'is_verified'    => true,
            'otp'            => null,
            'otp_expires_at' => null,
        ]);

        session()->forget('otp_user_id');
        session(['post_verify_user' => $user->id]);
        session()->save();
        return redirect('/auto-login');
    }

    // ── RESEND OTP ─────────────────────────────────
    public function resendOtp()
    {
        $user = User::find(session('otp_user_id'));

        if (!$user) {
            return redirect()->route('register');
        }

        $otp = strval(rand(100000, 999999));

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        try {
            $sent = $this->sendBrevoEmail(
                $user->email,
                $user->name,
                'New OTP — Fuiox Technologies',
                "Hello {$user->name},\n\nYour new OTP is: {$otp}\n\nExpires in 10 minutes."
            );
            if (!$sent) {
                return back()->withErrors(['otp' => 'Failed to resend OTP. Please try again.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => 'Failed to resend: ' . $e->getMessage()]);
        }

        return back()->with('resent', '✅ New OTP sent to ' . $user->email);
    }
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withInput()
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        // Always send OTP on login
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update(['otp_code' => $otp, 'otp_expires_at' => Carbon::now()->addMinutes(10)]);
        session(['otp_user_id' => $user->id, 'otp_purpose' => 'login']);
        try {
            $apiKey = env('BREVO_API_KEY');
            \Illuminate\Support\Facades\Http::withHeaders(['api-key' => $apiKey, 'Content-Type' => 'application/json'])
                ->post('https://api.brevo.com/v3/smtp/email', [
                    'sender'      => ['name' => env('MAIL_FROM_NAME','Fuiox Technologies'), 'email' => env('MAIL_FROM_ADDRESS','jegatheeshwari1407@gmail.com')],
                    'to'          => [['email' => $user->email, 'name' => $user->name]],
                    'subject'     => 'Your Fuiox Login OTP',
                    'textContent' => "Hello {$user->name},\n\nYour login OTP is: {$otp}\n\nExpires in 10 minutes.",
                ]);
        } catch (\Exception $e) {}
        return redirect()->route('otp.show');
    }

    // ── SEND LOGIN OTP ─────────────────────────────
     
    public function sendLoginOtp(Request $request)
    {
        $key = 'login_attempts_' . str_replace('.', '_', $request->ip());
        $attempts = cache()->get($key, 0);
        if ($attempts >= 5) {
            return response()->json(['error' => 'Too many attempts. Try again in 30 seconds.']);
        }
        cache()->put($key, $attempts + 1, 30);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid email or password.']);
        }
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update(['otp_code' => $otp, 'otp_expires_at' => Carbon::now()->addMinutes(1)]);
        session(['otp_user_id' => $user->id, 'otp_purpose' => 'login']);
        try {
            $apiKey = env('BREVO_API_KEY');
            \Illuminate\Support\Facades\Http::withHeaders(['api-key' => $apiKey, 'Content-Type' => 'application/json'])
                ->post('https://api.brevo.com/v3/smtp/email', [
                    'sender'      => ['name' => env('MAIL_FROM_NAME','Fuiox Technologies'), 'email' => env('MAIL_FROM_ADDRESS','jegatheeshwari1407@gmail.com')],
                    'to'          => [['email' => $user->email, 'name' => $user->name]],
                    'subject'     => 'Your Fuiox Login OTP',
                    'textContent' => "Hello {$user->name},\n\nYour login OTP is: {$otp}\n\nExpires in 1 minute.\n\nDo not share.",
                ]);
        } catch (\Exception $e) {
            Log::error('OTP send failed', ['error' => $e->getMessage()]);
        }
        return response()->json(['success' => true]);
    }

    // VERIFY LOGIN OTP
    public function verifyLoginOtp(Request $request)
    {
        $user = User::find(session('otp_user_id'));
        if (!$user) return response()->json(['error' => 'Session expired. Please login again.']);
        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['error' => 'OTP expired. Please request a new one.']);
        }
        if ($user->otp_code !== $request->otp) {
            return response()->json(['error' => 'Incorrect OTP. Try again.']);
        }
        $user->update(['otp_code' => null, 'otp_expires_at' => null, 'is_verified' => true, 'is_online' => true, 'last_seen' => Carbon::now()]);
        session()->forget(['otp_user_id', 'otp_purpose']);
        session(['auth_user' => $user->id, 'auth_role' => $user->role]);
        session()->save();
        if ($user->isAdmin()) $redirect = route('admin.dashboard');
        elseif (!$user->phone_number_id || !$user->access_token) $redirect = route('setup');
        elseif ($user->is_app_employee) {
            // App employee — find their app assignment and redirect to app dashboard
            $assignment = \Illuminate\Support\Facades\DB::table('app_assignments')
                ->where('staff_user_id', $user->id)->first();
            $redirect = $assignment ? route('apps.employee.dashboard', $assignment->app_type) : route('apps.employee.no-access');
        }
        elseif ($user->parent_user_id) $redirect = route('chat');
        else $redirect = route('dashboard');
        return response()->json(['redirect' => $redirect, 'success' => true]);
    }

    // ── LOGOUT ─────────────────────────────────────
    public function logout()
    {
        $user = User::find(session('auth_user'));
        if ($user) {
            $user->update([
                'is_online'      => false,
                'last_seen'      => Carbon::now(),
                'last_logout_at' => Carbon::now(),
            ]);
        }
        session()->flush();
        return redirect()->route('login');
    }
}