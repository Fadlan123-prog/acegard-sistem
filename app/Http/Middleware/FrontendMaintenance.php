<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Saat FRONTEND_MAINTENANCE=true, semua halaman publik menampilkan halaman maintenance (503),
 * KECUALI halaman iklan (landing Google Ads) yang tetap tayang. Hidupkan/matikan lewat .env.
 * Login, panel internal, dan halaman garansi publik TIDAK terpengaruh.
 */
class FrontendMaintenance
{
    /** Nama route landing page iklan yang dikecualikan. */
    private const AD_ROUTES = ['frontend.kaca.film.mobil', 'frontend.kaca.film.rumah', 'frontend.ppf.ads', 'frontend.promotion.car'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.frontend_maintenance')) {
            return $next($request);
        }

        // Halaman iklan tidak boleh ikut mati selama maintenance.
        if ($request->routeIs(self::AD_ROUTES)) {
            return $next($request);
        }

        return response()
            ->view('frontend.maintenance', [], 503)
            ->header('Retry-After', '3600')
            ->header('X-Robots-Tag', 'noindex');
    }
}
