<?php

namespace App\Notifications;

use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SalesOrderAssigned extends Notification
{
    use Queueable;

    public function __construct(public SalesOrder $salesOrder) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $vehicle = trim($this->salesOrder->vehicle_brand . ' ' . $this->salesOrder->vehicle_model);
        $branch  = $this->salesOrder->branch?->name ?? '-';

        return [
            'type'            => 'sales_order_assigned',
            'sales_order_id'  => $this->salesOrder->id,
            'so_number'       => $this->salesOrder->so_number,
            'prospect_name'   => $this->salesOrder->prospect_name,
            'vehicle'         => $vehicle,
            'branch'          => $branch,
            'scheduled_date'  => $this->salesOrder->scheduled_date?->toDateString(),
            'url'             => route('teknisi.dashboard', [], false),
            'message'         => "SO baru ditugaskan: {$vehicle} a/n {$this->salesOrder->prospect_name} di {$branch}.",
        ];
    }
}
