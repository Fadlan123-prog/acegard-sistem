<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommissionPeriodDueAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Carbon $periodStart,
        public Carbon $periodEnd,
        public int    $unpaidCount,
        public int    $unreleasedCount,
        public int    $daysUntilDue,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $due = $this->periodEnd->isoFormat('D MMMM Y');

        $parts = [];
        if ($this->unreleasedCount > 0) {
            $parts[] = "{$this->unreleasedCount} invoice belum rilis komisi";
        }
        if ($this->unpaidCount > 0) {
            $parts[] = "{$this->unpaidCount} karyawan belum terbayar";
        }

        return [
            'type'              => 'commission_period_due',
            'period_start'      => $this->periodStart->toDateString(),
            'period_end'        => $this->periodEnd->toDateString(),
            'days_until_due'    => $this->daysUntilDue,
            'unreleased_count'  => $this->unreleasedCount,
            'unpaid_count'      => $this->unpaidCount,
            'url'               => route('commission.periods.show', $this->periodStart->toDateString(), false),
            'message'           => "Komisi jatuh tempo {$due} ({$this->daysUntilDue} hari lagi). " . implode(', ', $parts) . '.',
        ];
    }
}
