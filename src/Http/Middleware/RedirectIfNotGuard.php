<?php

namespace SkyHackeR\MultiAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Redirect if not authenticated for given guard.
 */
class RedirectIfNotGuard
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (! Auth::guard($guard)->check()) {
            return redirect()->route($guard . '.login');
        }

        return $next($request);
    }
}
