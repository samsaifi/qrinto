<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteLock
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('app.site_lock_enabled', false)) {
            return $next($request);
        }

        if ($request->session()->get('site_unlocked', false)) {
            return $next($request);
        }

        // Cookie fallback (7-day unlock set on /site-lock/verify) so the site
        // stays unlocked across sessions / private-window closes.
        if ($request->cookie('site_unlocked') === '1') {
            $request->session()->put('site_unlocked', true);
            return $next($request);
        }

        if ($request->is('site-lock') || $request->is('site-lock/*')) {
            return $next($request);
        }

        return redirect()->route('site-lock');
    }
}
