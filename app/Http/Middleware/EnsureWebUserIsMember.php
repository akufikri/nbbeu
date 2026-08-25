<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defense-in-depth alongside the 'web'/'admin' guard split: even if a 'web'
 * session somehow ends up authenticated as a non-member (e.g. a stale
 * session issued before guards were separated), the site must not treat
 * that session as a logged-in member anywhere — including public pages,
 * where a stale admin session would otherwise still show the member nav
 * dropdown. Runs on every 'web' request and silently drops the guard;
 * any already-present 'auth:web' middleware on protected routes then
 * naturally redirects the now-guest request to login on its own.
 */
class EnsureWebUserIsMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user) {
            if (! $user->hasRole('member') || $user->isSuspended()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        return $next($request);
    }
}
