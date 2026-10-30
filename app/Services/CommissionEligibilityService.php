<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class CommissionEligibilityService
{
    public function syncByInvoice(Invoice $invoice): void
    {
        $invoice->refresh();

        $isPaid = $invoice->status === 'paid';
        $isCompleted = $invoice->work_status === 'completed';

        /*
        |--------------------------------------------------------------------------
        | Jika invoice sudah lunas dan pekerjaan selesai,
        | maka komisi siap dibayar.
        |--------------------------------------------------------------------------
        */
        if ($isPaid && $isCompleted) {
            $eligibleAt = collect([
                $invoice->paid_at,
                $invoice->completed_at,
            ])->filter()->max() ?? now();

            DB::table('commissions')
                ->where('invoice_id', $invoice->id)
                ->whereIn('status', ['pending', 'eligible'])
                ->update([
                    'status' => 'eligible',
                    'eligible_at' => $eligibleAt,
                    'updated_at' => now(),
                ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Kalau belum lunas / belum selesai,
        | komisi tetap pending selama belum terbayarkan.
        |--------------------------------------------------------------------------
        */
        DB::table('commissions')
            ->where('invoice_id', $invoice->id)
            ->where('status', '!=', 'paid')
            ->whereNull('commission_payment_batch_id')   // komisi yang sudah diajukan tidak diturunkan
            ->update([
                'status' => 'pending',
                'eligible_at' => null,
                'updated_at' => now(),
            ]);
    }
}
