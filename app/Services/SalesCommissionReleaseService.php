<?php

namespace App\Services;

use App\Domain\Sales\SalesCommissionCalculator;
use App\Models\CommissionPeriod;
use App\Models\SalesTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Merilis komisi untuk satu SalesTransaction — dipanggil saat status transaksi
 * jadi 'paid' (pelunasan), bukan saat closing. Komisi dibekukan (snapshot) di
 * titik ini: perubahan sponsor/role karyawan SETELAHNYA tidak akan mengubah
 * baris komisi yang sudah tersimpan untuk transaksi ini.
 *
 * Idempoten: insert pakai index unik (sales_transaction_id, employees_id,
 * role) di tabel commissions — panggil ulang untuk transaksi yang sama tidak
 * akan menghasilkan baris dobel (insert kedua akan gagal karena duplicate key,
 * ditangani lewat upsert supaya aman dipanggil berkali-kali, mis. retry).
 */
class SalesCommissionReleaseService
{
    public function __construct(
        private readonly SalesCommissionCalculator $calculator,
        private readonly SponsorChainResolver $chainResolver,
    ) {}

    public function release(SalesTransaction $transaction, int $releasedBy): void
    {
        abort_unless($transaction->isPaidOff(), 422, 'Transaksi belum lunas.');

        $chain = $this->chainResolver->resolveFrom($transaction->sales_employee_id);
        $lines = $this->calculator->resolve(
            $transaction->sales_employee_id,
            $transaction->total_price,
            $chain,
        );

        $installedAt = $transaction->pelunasan_at ?? $transaction->paid_at ?? now();
        $period = CommissionPeriod::resolveForDate($installedAt);
        $now = now();

        DB::transaction(function () use ($transaction, $lines, $period, $installedAt, $now) {
            foreach ($lines as $line) {
                DB::table('commissions')->updateOrInsert(
                    [
                        'sales_transaction_id' => $transaction->id,
                        'employees_id'         => $line['employee_id'],
                        'role'                 => $line['role'],
                    ],
                    [
                        'amount'               => $line['amount'],
                        'branch_id'            => $transaction->branch_id,
                        'status'               => 'eligible',
                        'is_active'            => true,
                        'commission_period_id' => $period->id,
                        'created_at'           => $installedAt,
                        'updated_at'           => $now,
                    ]
                );
            }

            $transaction->update([
                'commission_released_at' => $now,
            ]);
        });

        $transaction->update(['commission_released_by' => $releasedBy]);
    }
}
