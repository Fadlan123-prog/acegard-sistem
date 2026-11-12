<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordSetupLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Satu pintu untuk semua yang menyentuh kredensial lewat tautan email:
 * kebijakan password, pengiriman tautan, penukaran token, pencabutan sesi.
 */
class PasswordSetupService
{
    public const BROKER_RESET = 'users';
    public const BROKER_INVITATION = 'sales_invitations';

    /** Aturan validasi password baru (dipakai reset, ganti paksa, dan approve manual). */
    public static function policy(): array
    {
        return [
            'required', 'string',
            // bcrypt hanya membaca 72 byte pertama; batasi agar tidak menyesatkan.
            'max:64',
            PasswordRule::min((int) config('password_setup.min_length', 10))
                ->letters()->mixedCase()->numbers(),
        ];
    }

    /**
     * Kirim tautan. $kind: 'invitation' (48 jam) atau 'reset' (60 menit).
     * Mengembalikan konstanta status Password::* (RESET_LINK_SENT, RESET_THROTTLED, ...).
     */
    public function sendLink(User $user, string $kind): string
    {
        $broker = $kind === 'invitation' ? self::BROKER_INVITATION : self::BROKER_RESET;
        $expire = (int) config("auth.passwords.{$broker}.expire");

        return Password::broker($broker)->sendResetLink(
            ['email' => $user->email],
            function (User $u, string $token) use ($kind, $expire) {
                $u->notify(new PasswordSetupLink($token, $kind, $expire));

                return Password::RESET_LINK_SENT;
            }
        );
    }

    /**
     * Tukar token dengan password baru. Mencoba token reset lalu token undangan
     * (tabel berbeda → tidak ada parameter "jenis" yang bisa dimanipulasi).
     * false = tautan tidak valid / kedaluwarsa / akun tidak berhak — tanpa
     * membedakan alasannya ke pemanggil.
     */
    public function attemptReset(string $email, string $token, string $plainPassword): bool
    {
        $user = User::where('email', $email)->first();

        // Akun yang sudah dinonaktifkan / dicabut setelah tautan terbit tetap ditolak.
        if (! $user || ! $user->canUseEmailPasswordSetup()) {
            return false;
        }

        foreach ([self::BROKER_RESET, self::BROKER_INVITATION] as $broker) {
            $status = Password::broker($broker)->reset(
                ['email' => $email, 'password' => $plainPassword, 'token' => $token],
                fn (User $u, string $password) => $this->applyNewPassword($u, $password)
            );

            if ($status === Password::PASSWORD_RESET) {
                // Token jenis lain untuk email ini tidak boleh tersisa.
                foreach ([self::BROKER_RESET, self::BROKER_INVITATION] as $b) {
                    Password::broker($b)->deleteToken($user);
                }

                return true;
            }
        }

        return false;
    }

    /** Ganti password + putus semua sesi lama + hapus remember-me. */
    public function applyNewPassword(User $user, string $plainPassword, ?string $keepSessionId = null): void
    {
        $user->forceFill([
            // Hash::make eksplisit: cast 'hashed' melewatkan string yang tampak
            // seperti hash bcrypt, jadi jangan bergantung padanya.
            'password'             => Hash::make($plainPassword),
            'remember_token'       => Str::random(60),
            'must_change_password' => false,
            'password_changed_at'  => now(),
        ])->save();

        $this->revokeSessions($user, $keepSessionId);
    }

    /** Hapus sesi server milik user (hanya bisa bila SESSION_DRIVER=database). */
    public function revokeSessions(User $user, ?string $exceptSessionId = null): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $query = DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->id);

        if ($exceptSessionId) {
            $query->where('id', '!=', $exceptSessionId);
        }

        $query->delete();
    }
}
