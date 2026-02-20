<?php

namespace App\Http\Middleware;

use App\Support\ChurchContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveChurch
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('website.api_token');

        if (! empty($token)) {
            // Single-tenant mode: use configured token
            $context = new ChurchContext(token: $token);
        } else {
            // Multi-tenant mode: use request Host
            $host = strtolower($request->getHost());
            $context = new ChurchContext(domain: $host);
        }

        app()->instance(ChurchContext::class, $context);

        return $next($request);
    }
}
