<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email tautan atur-password. Dikirim sinkron (hosting bersama biasanya tidak
 * menjalankan queue worker).
 *
 * URL SENGAJA dibangun dari config('app.url'), bukan dari Host request —
 * kalau tidak, penyerang yang mengirim header "Host: evil.example" ke form
 * lupa-password membuat email resmi berisi tautan ke domain mereka
 * (password-reset poisoning). Pastikan APP_URL di .env produksi benar.
 */
class PasswordSetupLink extends Notification
{
    public function __construct(
        private string $token,
        private string $kind,        // 'invitation' | 'reset'
        private int $expireMinutes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim((string) config('app.url'), '/') . route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        $lifetime = $this->expireMinutes >= 120
            ? round($this->expireMinutes / 60) . ' jam'
            : $this->expireMinutes . ' menit';

        $invite = $this->kind === 'invitation';

        return (new MailMessage)
            ->subject($invite ? 'Akun sales Acegard Anda sudah disetujui' : 'Atur ulang password akun Acegard')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line($invite
                ? 'Pendaftaran Anda sebagai sales Acegard sudah disetujui. Klik tombol di bawah untuk membuat password Anda sendiri.'
                : 'Kami menerima permintaan untuk mengatur ulang password akun Acegard Anda.')
            ->action($invite ? 'Buat Password' : 'Atur Ulang Password', $url)
            ->line("Tautan ini hanya berlaku {$lifetime} dan hanya bisa dipakai satu kali.")
            ->line('Jika Anda tidak merasa meminta ini, abaikan email ini — password Anda tidak berubah. Jangan teruskan email ini kepada siapa pun, termasuk yang mengaku sebagai admin.');
    }
}
