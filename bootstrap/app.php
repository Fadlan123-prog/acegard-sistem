<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'branch.access' => \App\Http\Middleware\EnsureBranchAccess::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Deliberately NOT calling trustProxies() here (audit finding M-14).
        // If Hostinger fronts the app with a reverse proxy/load balancer,
        // REMOTE_ADDR is the proxy's own IP, not the visitor's — which makes
        // every throttle:X,1 limiter on the public warranty/login routes
        // effectively share one bucket across all visitors. But blindly
        // trusting '*' when there is NO proxy in front (common on standard
        // shared/VPS PHP hosting, where the web server IS the origin) would
        // let any client spoof X-Forwarded-For and bypass those same rate
        // limits entirely — worse than the current state. Confirm the real
        // network topology on the live server first, then either set
        // TRUSTED_PROXIES in .env and read it here, or leave this as-is if
        // there is no proxy:
        //   $middleware->trustProxies(at: explode(',', env('TRUSTED_PROXIES', '')));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Halaman 429 ramah untuk publik: /warranty, /warranty/cards, /warranty/ppf-cards
        // (cek garansi, klaim, download kartu) memakai beberapa limiter throttle:X,1
        // yang berbeda-beda; pesannya sengaja generik "coba lagi 2 menit" untuk semua,
        // bukan mengikuti window asli tiap rute.
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->is('warranty*') && ! $request->expectsJson()) {
                return response()->view('errors.warranty-429', [], 429, $e->getHeaders());
            }
        });
    })
    ->create();

// /** Autodetect lokasi public_html */
// $guesses = [
//     $app->basePath('public_html'),                                     // /home/acegardi/laravel/public_html (kalau ada)
//     dirname($app->basePath()) . DIRECTORY_SEPARATOR . 'public_html',   // /home/acegardi/public_html (cPanel umum)
//     isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) : null,
// ];

// foreach ($guesses as $path) {
//     if ($path && is_dir($path)) {
//         $app->usePublicPath($path); // <-- helper public_path() pakai ini
//         $app->instance('path.public', $path); // <-- SEKALIGUS ikat ke container binding
//         break;
//     }
// }

// $path = '/home/acegardi/public_html'; // web-root Mbak yang benar

// // 1) untuk helper public_path()
// $app->usePublicPath($path);

// // 2) untuk container binding yang dicari paket2 (DomPDF, Filesystem, dll)
// $app->instance('path.public', $path);

return $app;
