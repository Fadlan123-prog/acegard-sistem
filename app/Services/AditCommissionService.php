<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class AditCommissionService
{
    private const OVERRIDE_ROLE = 'adit_override';

    /**
     * Sync komisi override untuk invoice ini.
     * Override employee + rate diambil dari tabel commission_override_triggers.
     */
    public function sync(Invoice $invoice, int $sellingPrice): void
    {
        $invoice->loadMissing('customer.marketing');

        $customer  = $invoice->customer;
        $marketing = $customer?->marketing;

        if (!$customer || !$marketing) {
            return;
        }

        // Cari semua aturan override yang terpicu oleh marketing ini
        $triggers = DB::table('commission_override_triggers as t')
            ->join('employees as oe', 'oe.id', '=', 't.override_employee_id')
            ->where('t.trigger_marketing_id', $marketing->id)
            ->where('t.is_active', true)
            ->select('t.override_employee_id', 't.rate', 'oe.name as override_name')
            ->get();

        if ($triggers->isEmpty()) {
            // Marketing ini tidak memicu override apapun — hapus override lama jika ada
            DB::table('commissions')
                ->where('invoice_id', $invoice->id)
                ->where('role', self::OVERRIDE_ROLE)
                ->delete();
            return;
        }

        $now = now();

        foreach ($triggers as $trigger) {
            $amount = (int) round($sellingPrice * (float) $trigger->rate);

            if ($amount <= 0) {
                continue;
            }

            DB::table('commissions')->updateOrInsert(
                [
                    'employees_id' => $trigger->override_employee_id,
                    'invoice_id'   => $invoice->id,
                    'role'         => self::OVERRIDE_ROLE,
                ],
                [
                    'customer_id' => $invoice->customer_id,
                    'amount'      => $amount,
                    'status'      => 'eligible',
                    'updated_at'  => $now,
                    'created_at'  => $now,
                ]
            );
        }
    }

    /**
     * Preview override commissions — tanpa insert.
     * Dipakai di CommissionReleaseService::preview().
     */
    public function previewFor(Invoice $invoice): array
    {
        $invoice->loadMissing('customer.marketing');

        $customer  = $invoice->customer;
        $marketing = $customer?->marketing;

        if (!$customer || !$marketing) {
            return [];
        }

        $triggers = DB::table('commission_override_triggers as t')
            ->join('employees as oe', 'oe.id', '=', 't.override_employee_id')
            ->where('t.trigger_marketing_id', $marketing->id)
            ->where('t.is_active', true)
            ->select('t.override_employee_id', 't.rate', 'oe.name as override_name', 'oe.job_position')
            ->get();

        $rows = [];
        foreach ($triggers as $trigger) {
            $amount = (int) round($invoice->total_price * (float) $trigger->rate);
            if ($amount <= 0) continue;

            $rows[] = [
                'role'          => self::OVERRIDE_ROLE,
                'employee_name' => $trigger->override_name,
                'job_position'  => $trigger->job_position ?? '-',
                'amount'        => $amount,
                'note'          => round($trigger->rate * 100, 2) . '% dari harga jual (via marketing ' . $marketing->name . ')',
                'is_second_car' => false,
                'config_key'    => null,
            ];
        }

        return $rows;
    }
}
