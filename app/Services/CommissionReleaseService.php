<?php

namespace App\Services;

use App\Domain\Commission\CommissionCalculator;
use App\Domain\Commission\CommissionSplit;
use App\Models\CommissionPeriod;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommissionReleaseService
{
    public function __construct(
        private CommissionService $commissionService,
        private AditCommissionService $aditCommission,
        private CommissionCalculator $calculator,
    ) {}

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Preview komisi yang akan dibuat — tanpa insert.
     */
    public function preview(Invoice $invoice, bool $lebaran = false): array
    {
        $invoice->loadMissing(
            'customer.tukang',
            'customer.kenek',
            'customer.marketing',
            'customer.customerProducts.part',
        );
        $customer = $invoice->customer;

        if (!$customer) {
            return ['rows' => [], 'is_second_car' => false, 'install_type' => null, 'lebaran' => $lebaran];
        }

        $split = $this->calculator->resolveInstallerSplit($customer, $invoice, $lebaran);
        $rows  = [];

        $samePerson = $customer->tukang_id && $customer->kenek_id
            && (int) $customer->tukang_id === (int) $customer->kenek_id;

        if ($samePerson) {
            $combined = $split->teknisiAmount + $split->kenekAmount;
            if ($combined > 0) {
                $rows[] = [
                    'role'          => 'installer',
                    'employee_name' => $customer->tukang?->name ?? '-',
                    'job_position'  => $customer->tukang?->job_position ?? '-',
                    'amount'        => $combined,
                    'note'          => $this->noteFor($split, 'tukang') . ' + Installer 2 (gabung)',
                    'is_second_car' => $split->isSecondCar,
                    'config_key'    => $split->configKey,
                ];
            }
        } else {
            if ($customer->tukang_id && $split->teknisiAmount > 0) {
                $rows[] = [
                    'role'          => 'installer',
                    'employee_name' => $customer->tukang?->name ?? '-',
                    'job_position'  => $customer->tukang?->job_position ?? '-',
                    'amount'        => $split->teknisiAmount,
                    'note'          => $this->noteFor($split, 'tukang'),
                    'is_second_car' => $split->isSecondCar,
                    'config_key'    => $split->configKey,
                ];
            }

            if ($customer->kenek_id && $split->kenekAmount > 0) {
                $rows[] = [
                    'role'          => 'installer',
                    'employee_name' => $customer->kenek?->name ?? '-',
                    'job_position'  => $customer->kenek?->job_position ?? '-',
                    'amount'        => $split->kenekAmount,
                    'note'          => $this->noteFor($split, 'kenek'),
                    'is_second_car' => $split->isSecondCar,
                    'config_key'    => $split->configKey,
                ];
            }
        }

        // Marketing
        if ($customer->marketing_id) {
            $categoryId = $this->commissionService->resolveMarketingCategoryId($customer->id);
            if ($categoryId) {
                $mMap = config('commission.marketing', []);
                $mCat = $mMap[$categoryId] ?? ($mMap['default'] ?? []);
                $base = $this->calculator->resolveMarketingBase($customer);
                $mAmt = (int) ($mCat[$base] ?? 0);

                if ($mAmt > 0) {
                    $rows[] = [
                        'role'          => 'marketing',
                        'employee_name' => $customer->marketing?->name ?? '-',
                        'job_position'  => $customer->marketing?->job_position ?? '-',
                        'amount'        => $mAmt,
                        'note'          => 'Marketing',
                        'is_second_car' => false,
                        'config_key'    => $base,
                    ];
                }
            }
        }

        // Override commissions (Adit / lainnya dari DB)
        $overrideRows = $this->aditCommission->previewFor($invoice);
        foreach ($overrideRows as $row) {
            $rows[] = $row;
        }

        return [
            'rows'          => $rows,
            'is_second_car' => $split->isSecondCar,
            'is_third_car'  => $split->isThirdCar,
            'install_type'  => $split->configKey,
            'lebaran'       => $lebaran,
        ];
    }

    /**
     * Buat komisi dan tandai invoice sebagai released.
     */
    public function release(Invoice $invoice, int $releasedBy, bool $lebaran = false): void
    {
        $invoice->loadMissing(
            'customer.tukang',
            'customer.kenek',
            'customer.marketing',
            'customer.customerProducts.part',
        );
        $customer = $invoice->customer;

        if (!$customer) {
            return;
        }

        // Hapus komisi lama yang tidak terhubung invoice (dari flow lama)
        DB::table('commissions')
            ->where('customer_id', $customer->id)
            ->whereNull('invoice_id')
            ->whereIn('role', ['installer', 'marketing', 'adit_override', 'teknisi', 'kenek'])
            ->delete();

        // Hapus komisi prematur yang dibuat saat assignment (role teknisi/kenek dari WorkOrderController)
        // sebelum insert komisi yang benar via release flow
        DB::table('commissions')
            ->where('invoice_id', $invoice->id)
            ->whereIn('role', ['teknisi', 'kenek'])
            ->delete();

        // Use the explicit installation date if set; fall back to customer creation date.
        $installedAt = $customer->install_date
            ? Carbon::parse($customer->install_date)
            : Carbon::parse($customer->created_at);
        $period      = CommissionPeriod::resolveForDate($installedAt);
        $split       = $this->calculator->resolveInstallerSplit($customer, $invoice, $lebaran);
        $now         = now();

        $samePerson = $customer->tukang_id && $customer->kenek_id
            && (int) $customer->tukang_id === (int) $customer->kenek_id;

        if ($samePerson) {
            // Same employee in both installer slots — one record with combined amount
            // to avoid violating uniq_commission_invoice_emp_role.
            $combined = $split->teknisiAmount + $split->kenekAmount;
            if ($combined > 0) {
                DB::table('commissions')->insert([
                    'employees_id'         => (int) $customer->tukang_id,
                    'customer_id'          => (int) $customer->id,
                    'invoice_id'           => $invoice->id,
                    'commission_period_id' => $period->id,
                    'amount'               => $combined,
                    'role'                 => 'installer',
                    'status'               => 'eligible',
                    'install_type'         => $split->configKey,
                    'created_at'           => $installedAt,
                    'updated_at'           => $now,
                ]);
            }
        } else {
            if ($customer->tukang_id && $split->teknisiAmount > 0) {
                DB::table('commissions')->insert([
                    'employees_id'         => (int) $customer->tukang_id,
                    'customer_id'          => (int) $customer->id,
                    'invoice_id'           => $invoice->id,
                    'commission_period_id' => $period->id,
                    'amount'               => $split->teknisiAmount,
                    'role'                 => 'installer',
                    'status'               => 'eligible',
                    'install_type'         => $split->configKey,
                    'created_at'           => $installedAt,
                    'updated_at'           => $now,
                ]);
            }

            if ($customer->kenek_id && $split->kenekAmount > 0) {
                DB::table('commissions')->insert([
                    'employees_id'         => (int) $customer->kenek_id,
                    'customer_id'          => (int) $customer->id,
                    'invoice_id'           => $invoice->id,
                    'commission_period_id' => $period->id,
                    'amount'               => $split->kenekAmount,
                    'role'                 => 'installer',
                    'status'               => 'eligible',
                    'install_type'         => $split->configKey,
                    'created_at'           => $installedAt,
                    'updated_at'           => $now,
                ]);
            }
        }

        // Marketing — always use customer's own install_type, never the installer's
        // car-count-upgraded configKey (e.g. a DSP customer whose tukang also did
        // a fullset that day would otherwise get 'ekstra_panoramic' → 'fullset' → 120k).
        if ($customer->marketing_id) {
            $this->commissionService->createMarketingForCustomerAutoCategory(
                (int) $customer->marketing_id,
                $customer->id,
                $invoice->id,
                $this->calculator->resolveMarketingBase($customer),
            );
        }

        // Adit override
        $this->aditCommission->sync($invoice, $invoice->price);

        // Stamp period on any commission rows for this invoice that weren't set above
        // (covers marketing from upsertCommission and adit_override from updateOrInsert)
        DB::table('commissions')
            ->where('invoice_id', $invoice->id)
            ->whereNull('commission_period_id')
            ->update(['commission_period_id' => $period->id, 'updated_at' => $now]);

        // Correct created_at for ALL rows of this invoice to the installation date.
        // Marketing (upsertCommission) and adit override (updateOrInsert) both write
        // now() for created_at — this single UPDATE normalises them all.
        DB::table('commissions')
            ->where('invoice_id', $invoice->id)
            ->update(['created_at' => $installedAt]);

        $invoice->update([
            'commission_released_at' => $now,
            'commission_released_by' => $releasedBy,
        ]);

        // Bust leaderboard cache for this branch
        $bid = $customer->branch_id ?? 0;
        Cache::forget("commission.top_marketing.{$bid}");
        Cache::forget("commission.top_installer.{$bid}");
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function noteFor(CommissionSplit $split, string $slot): string
    {
        $parts = [];

        if ($split->isThirdCar)       $parts[] = 'Mobil ke-3';
        elseif ($split->isSecondCar)  $parts[] = 'Mobil ke-2';
        if ($split->isLebaran)        $parts[] = 'Lebaran';
        if ($split->isSamePairType()) $parts[] = 'Pasangan Sejenis (50:50)';
        if (str_contains($split->configKey, '_solo')) $parts[] = 'Solo';

        $label = $slot === 'tukang' ? 'Installer 1' : 'Installer 2';
        return $label . ($parts ? ' — ' . implode(', ', $parts) : '');
    }

    /**
     * Strip installer config-key suffixes to resolve the marketing lookup base.
     * Preserves _panoramic for dsp so config['marketing'][n]['dsp_panoramic'] (140k) is used.
     */
    private function marketingBase(string $configKey): string
    {
        $hasPanorama = str_contains($configKey, 'panoramic');
        $clean = preg_replace('/_(lebaran|50|solo|panoramic).*$/', '', $configKey);
        if (str_starts_with($clean, 'dsp'))  return $hasPanorama ? 'dsp_panoramic' : 'dsp';
        if (str_starts_with($clean, 'skkb')) return 'skkb';
        return 'fullset'; // fullset panoramic → same rate as fullset in marketing config
    }
}
