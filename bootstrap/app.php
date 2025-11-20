<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
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
