<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Beranda menampilkan halaman "sedang maintenance"; menu navbar publik lain
 * dialihkan ke halaman garansi. Hidupkan/matikan lewat .env: FRONTEND_MAINTENANCE=true|false.
 * Login, panel internal, dan halaman garansi publik TIDAK terpengaruh.
 */
class FrontendMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.frontend_maintenance')) {
            return $next($request);
        }

        // Menu navbar lain (layanan, tentang, kontak, dst.) dialihkan ke halaman garansi.
        if (! $request->routeIs('frontend.home')) {
            return redirect()->route('warranty.form');
        }

        // Beranda menampilkan halaman maintenance.
        return response()
            ->view('frontend.maintenance', [], 503)
            ->header('Retry-After', '3600')
            ->header('X-Robots-Tag', 'noindex');
    }
}
