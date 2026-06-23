<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('auth_user')) {
            return redirect()->route('login');
        }
        $user = \App\Models\User::find(session('auth_user'));
        if ($user && $user->is_blocked) {
            session()->flush();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been blocked. Please contact support.']);
        }

        $user = User::find(session('auth_user'));
        if ($user) {
            $user->update([
                'is_online' => true,
                'last_seen' => Carbon::now(),
            ]);
        }

        return $next($request);
    }
}