<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        // The consumer flow controller now selects the mobile vs desktop view
        // folder from the request device itself, so no redirect is needed here.
        return $next($request);
    }
}