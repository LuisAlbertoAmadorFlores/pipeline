<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserCanManageDeals
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();
        if (!$user->is_admin) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}
