<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use App\Services\PasswordSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Lupa password / atur password dari undangan. Publik, tanpa login.
 *
 * Prinsip: jawaban SELALU sama — baik email terdaftar, tidak terdaftar,
 * belum disetujui, atau role-nya tidak diizinkan — supaya halaman ini tidak
 * bisa dipakai menebak siapa yang punya akun.
 */
class PasswordResetController extends Controller
{
    private const GENERIC_SENT = 'Jika email tersebut milik akun sales yang aktif, tautan atur password sudah dikirim. Periksa kotak masuk dan folder spam. Tautan berlaku 60 menit.';
    private const GENERIC_INVALID = 'Tautan tidak valid atau sudah kedaluwarsa. Minta tautan baru.';

    public function __construct(private PasswordSetupService $passwords) {}

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $email = $data['email'];

        // Dikerjakan SETELAH respons terkirim: pengiriman email membuat respons
        // untuk email valid lebih lambat dari yang tidak valid — selisih waktu
        // itu akan membocorkan keberadaan akun.
        app()->terminating(function () use ($email) {
            try {
                $user = User::where('email', $email)->first();

                if ($user && $user->canUseEmailPasswordSetup()) {
                    $this->passwords->sendLink($user, 'reset');
                    AuditService::log('password_link_sent', 'Auth', "Tautan atur password dikirim ke {$email}");
                }
            } catch (\Throwable $e) {
                // Jangan beri tahu pengguna — hanya catat.
                Log::error('Pengiriman tautan atur password gagal', ['error' => $e->getMessage()]);
            }
        });

        return back()->with('status', self::GENERIC_SENT);
    }

    public function showResetForm(Request $request, string $token)
    {
        return response()
            ->view('auth.reset-password', [
                'token' => $token,
                'email' => (string) $request->query('email', ''),
            ])
            // Token ada di URL: jangan di-cache dan jangan bocor lewat Referer.
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => array_merge(PasswordSetupService::policy(), ['confirmed', 'different:email']),
        ], $this->passwordMessages());

        if (! $this->passwords->attemptReset($data['email'], $data['token'], $data['password'])) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => self::GENERIC_INVALID]);
        }

        AuditService::log('password_reset', 'Auth', "Password diatur lewat tautan email: {$data['email']}");

        return redirect()->route('login')
            ->with('success', 'Password berhasil diatur. Silakan login dengan password baru Anda.');
    }

    /** @return array<string,string> */
    private function passwordMessages(): array
    {
        $min = (int) config('password_setup.min_length', 10);

        return [
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => "Password minimal {$min} karakter.",
            'password.max'       => 'Password maksimal 64 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.different' => 'Password tidak boleh sama dengan email.',
            'password.letters'   => 'Password harus mengandung huruf.',
            'password.mixed'     => 'Password harus mengandung huruf besar dan huruf kecil.',
            'password.numbers'   => 'Password harus mengandung angka.',
        ];
    }
}
