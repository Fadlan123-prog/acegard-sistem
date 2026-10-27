<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\PasswordSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Ganti password wajib untuk akun yang password-nya ditentukan admin.
 * Hanya dijangkau lewat EnsurePasswordIsFresh (flag sesi) atau langsung oleh
 * user yang sudah login.
 */
class PasswordChangeController extends Controller
{
    public function __construct(private PasswordSetupService $passwords) {}

    public function show(Request $request)
    {
        return response()
            ->view('auth.change-password', ['forced' => (bool) $request->session()->get('must_change_password')])
            ->header('Cache-Control', 'no-store, private');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => array_merge(
                PasswordSetupService::policy(),
                ['confirmed', 'different:current_password', 'different:email']
            ),
        ], [
            'current_password.required'         => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'password.confirmed'                => 'Konfirmasi password tidak sama.',
            'password.different'                => 'Password baru harus berbeda dari password saat ini dan dari email.',
            'password.min'                      => 'Password minimal ' . (int) config('password_setup.min_length', 10) . ' karakter.',
            'password.letters'                  => 'Password harus mengandung huruf.',
            'password.mixed'                    => 'Password harus mengandung huruf besar dan huruf kecil.',
            'password.numbers'                  => 'Password harus mengandung angka.',
        ]);

        $this->passwords->applyNewPassword($user, $data['password']);
        AuditService::log('password_changed', 'Auth', 'Password diganti oleh pemilik akun', auditableId: $user->id, auditableType: 'User');

        // Mulai ulang sesi dari nol: login ulang menyiapkan cabang & dashboard
        // sesuai role, dan tidak ada sesi lama (milik siapa pun) yang bertahan.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Password berhasil diganti. Silakan login kembali.');
    }
}
