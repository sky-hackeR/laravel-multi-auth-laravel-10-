<?php

namespace SkyHackeR\MultiAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Redirect if already authenticated for given guard.
 */
class RedirectIfGuard
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return redirect()->route($guard . '.home');
        }

        return $next($request);
    }
}
