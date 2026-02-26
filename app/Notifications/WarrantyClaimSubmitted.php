<?php

namespace App\Notifications;

use App\Models\WarrantyClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WarrantyClaimSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WarrantyClaim $claim) {}

    public function via($notifiable): array
    {
        return ['database']; // tambah 'mail' kalau mau email
    }

    public function toDatabase($notifiable): array
    {
        $complaint = (string)($this->claim->complaint ?? '');
        $complaintShort = mb_strlen($complaint) > 80 ? mb_substr($complaint, 0, 80).'…' : $complaint;

        return [
            'claim_id'      => $this->claim->id,
            'claim_number'  => $this->claim->claim_number,
            'warranty_type' => $this->claim->warranty_type,
            'status'        => $this->claim->status,
            'branch_id'     => $this->claim->branch_id,
            'complaint'     => $complaintShort,

            // ✅ gunakan route yang bisa diakses branch user + superadmin
            'url' => route('admin.claims.show', $this->claim->id, false),
        ];
    }

    // OPTIONAL: kalau mau email juga
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Warranty Claim Baru: '.$this->claim->claim_number)
            ->line('Ada claim warranty baru masuk.')
            ->line('Tipe: '.$this->claim->warranty_type)
            ->action('Lihat Claim', route('admin.claims.show', $this->claim->id));
    }
}
