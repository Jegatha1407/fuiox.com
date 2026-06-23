<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = User::find(session('auth_user'));

        if (!$user || !$user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}