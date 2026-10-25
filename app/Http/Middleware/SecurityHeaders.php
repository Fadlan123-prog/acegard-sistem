<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers (audit finding M-9). The app relies heavily on
 * inline <script>/<style> blocks in server-rendered Blade views and loads
 * assets from several CDNs, so a hard-enforced Content-Security-Policy would
 * break real pages without a much larger per-view audit. CSP therefore ships
 * report-only for now — visible in browser devtools, blocks nothing — while
 * the headers that carry no compatibility risk are enforced immediately.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('Content-Security-Policy-Report-Only', implode('; ', [
            "default-src 'self'",
            "img-src 'self' data: https:",
            "font-src 'self' data: https:",
            "style-src 'self' 'unsafe-inline' https:",
            "script-src 'self' 'unsafe-inline' https:",
            "object-src 'none'",
            "frame-ancestors 'self'",
        ]));

        return $response;
    }
}
