<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Models\User;
class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = User::find(session('auth_user'));
        if (!$user) {
            return redirect()->route('login');
        }
        // Allow owners (role=user, team_role=owner) and admins
        if (!$user->parent_user_id) {
            return $next($request);
        }
        // Team members (agent/manager) - allow access to user routes
        if ($user->parent_user_id) {
            if (!$user->is_active) {
                session()->flush();
                return redirect()->route('login')->withErrors(['email' => 'Your account is inactive.']);
            }
            return $next($request);
        }
        return $next($request);
    }
}
