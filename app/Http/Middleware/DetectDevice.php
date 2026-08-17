<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        $isMobile = preg_match(
            '/android|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile/',
            $userAgent
        );

        // Desktop → PC flow
        if (!$isMobile && $request->is('/')) {
            return redirect()->route('flow-pc.index');
        }

        return $next($request);
    }
}