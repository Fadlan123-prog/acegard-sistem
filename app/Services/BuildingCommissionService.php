<?php

namespace App\Services;

use App\Domain\Commission\EmployeeCommissionResolver;
use App\Models\CommissionPeriod;
use App\Models\Employee;
use App\Models\InvoiceBuilding;
use Illuminate\Support\Facades\DB;

/**
 * Komisi teknisi gedung: meter yang dicatat per teknisi per kategori film × tarif per meter
 * (config commission.building_per_meter). Tidak ada kenek dan tidak ada pembagian rata —
 * hanya meter yang dialokasikan admin ke seorang teknisi yang dibayar.
 *
 * Dipanggil dari InvoiceBuildingController::recordPelunasan di dalam transaksi pelunasan
 * (sudah dikunci); idempoten lewat unique (invoice_building_id, employees_id, role).
 */
class BuildingCommissionService
{
    public function __construct(private EmployeeCommissionResolver $employeeResolver) {}

    /** @return array<int,int> employee_id => komisi */
    public function release(InvoiceBuilding $invoice): array
    {
        $invoice->loadMissing('customer.technicianMeters', 'customer.products', 'customer.marketing');
        $customer = $invoice->customer;

        if (! $customer) {
            return [];
        }

        $this->releaseMarketing($invoice);

        if ($customer->technicianMeters->isEmpty()) {
            return [];
        }

        $rates = (array) config('commission.building_per_meter', []);

        $perEmployee = [];
        foreach ($customer->technicianMeters as $row) {
            $amount = (int) round((float) $row->meters * (int) ($rates[$row->category_product_building_id] ?? 0));
            $perEmployee[$row->employee_id] = ($perEmployee[$row->employee_id] ?? 0) + $amount;
        }

        $employees = Employee::whereIn('id', array_keys($perEmployee))->get()->keyBy('id');
        $installedAt = $invoice->pelunasan_at ?? now();
        $period = CommissionPeriod::resolveForDate($installedAt);
        $created = [];

        foreach ($perEmployee as $employeeId => $default) {
            $resolved = $this->employeeResolver->resolve($employees[$employeeId] ?? null, $default, (int) $invoice->price);

            if ($resolved['amount'] <= 0 && ! $resolved['forced']) {
                continue;
            }

            DB::table('commissions')->updateOrInsert(
                [
                    'invoice_building_id' => $invoice->id,
                    'employees_id'        => $employeeId,
                    'role'                => 'installer',
                ],
                [
                    'branch_id'            => $customer->branch_id,
                    'amount'               => $resolved['amount'],
                    'status'               => 'eligible',
                    'is_active'            => true,
                    'install_type'         => 'building_per_meter',
                    'commission_period_id' => $period->id,
                    'created_at'           => $installedAt,
                    'updated_at'           => now(),
                ]
            );

            $created[(int) $employeeId] = $resolved['amount'];
        }

        return $created;
    }

    /** Marketing gedung: seluruh meter produk × tarif marketing per kategori. */
    private function releaseMarketing(InvoiceBuilding $invoice): void
    {
        $customer = $invoice->customer;

        if (! $customer->marketing_id || ! $customer->marketing) {
            return;
        }

        $rates = (array) config('commission.building_marketing_per_meter', []);

        $default = 0;
        foreach ($customer->products as $row) {
            $default += (int) round((float) $row->meters * (int) ($rates[$row->category_product_building_id] ?? 0));
        }

        $resolved = $this->employeeResolver->resolve($customer->marketing, $default, (int) $invoice->price);

        if ($resolved['amount'] <= 0 && ! $resolved['forced']) {
            return;
        }

        $installedAt = $invoice->pelunasan_at ?? now();

        DB::table('commissions')->updateOrInsert(
            [
                'invoice_building_id' => $invoice->id,
                'employees_id'        => $customer->marketing_id,
                'role'                => 'marketing',
            ],
            [
                'branch_id'            => $customer->branch_id,
                'amount'               => $resolved['amount'],
                'status'               => 'eligible',
                'is_active'            => true,
                'install_type'         => 'building_per_meter',
                'commission_period_id' => CommissionPeriod::resolveForDate($installedAt)->id,
                'created_at'           => $installedAt,
                'updated_at'           => now(),
            ]
        );
    }
}
