<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobAssigned extends Notification
{
    use Queueable;

    public function __construct(public Customer $customer) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $invoice  = $this->customer->invoice;
        $vehicle  = trim($this->customer->vehicle_brand . ' ' . $this->customer->vehicle_model);
        $branch   = $this->customer->branch?->name ?? '-';

        return [
            'type'          => 'job_assigned',
            'customer_id'   => $this->customer->id,
            'customer_name' => $this->customer->name,
            'vehicle'       => $vehicle,
            'plat'          => $this->customer->plat_number,
            'branch'        => $branch,
            'install_type'  => $this->customer->install_type,
            'invoice_number'=> $invoice?->invoice_number,
            'url'           => route('teknisi.dashboard', [], false),
            'message'       => "Pekerjaan baru: {$vehicle} ({$this->customer->plat_number}) a/n {$this->customer->name} di {$branch}.",
        ];
    }
}
