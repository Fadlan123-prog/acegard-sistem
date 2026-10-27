<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang password-nya ditentukan admin (users.must_change_password) hanya
 * boleh membuka halaman ganti password sampai password itu diganti.
 *
 * Flag dibaca dari SESI (di-set LoginController) supaya tidak ada query DB
 * tambahan di setiap request, termasuk halaman publik.
 */
class EnsurePasswordIsFresh
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->hasSession()
            && $request->session()->get('must_change_password')
            && ! $request->routeIs('password.change', 'password.change.update', 'logout')
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda harus mengganti password terlebih dahulu.'], 423);
            }

            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
