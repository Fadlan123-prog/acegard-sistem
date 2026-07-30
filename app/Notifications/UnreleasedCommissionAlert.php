<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UnreleasedCommissionAlert extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $customer = $this->invoice->customer;

        return [
            'type'           => 'unreleased_commission',
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'customer_name'  => $customer?->name ?? '-',
            'vehicle'        => trim(($customer?->vehicle_brand ?? '') . ' ' . ($customer?->vehicle_model ?? '')),
            'plat'           => $customer?->plat_number ?? '-',
            'branch_id'      => $customer?->branch_id,
            'url'            => route('invoice.show', $this->invoice->id, false),
            'message'        => 'Invoice ' . $this->invoice->invoice_number . ' sudah lunas — komisi belum dirilis.',
        ];
    }
}
