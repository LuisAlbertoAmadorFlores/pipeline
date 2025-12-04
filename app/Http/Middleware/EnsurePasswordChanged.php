<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Allow access to password change routes and logout
        $allowed = [
            'password.change',
            'password.force_update',
            'password.update',
            'logout',
        ];

        if ($user->must_change_password && !in_array($request->route()?->getName(), $allowed)) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
