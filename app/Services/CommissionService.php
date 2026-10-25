<?php

namespace App\Services;

use App\Domain\Commission\EmployeeCommissionResolver;
use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PpfInvoice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    public function __construct(
        private readonly EmployeeCommissionResolver $employeeResolver,
    ) {}

    /**
     * Membuat atau memperbarui komisi fixed untuk teknisi dan kenek.
     *
     * Dipakai untuk komisi pemasangan berdasarkan tipe install.
     */
    public function createFixedForInvoice(
        Invoice $invoice,
        string $installType,
        ?int $teknisiId = null,
        ?int $kenekId = null
    ): void {
        $installType = strtolower($installType);

        Log::info('[CommissionService] Create fixed commission', [
            'invoice_id'  => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'installType' => $installType,
            'teknisiId'   => $teknisiId,
            'kenekId'     => $kenekId,
        ]);

        $row = config("commission.fixed.$installType");

        if (!$row) {
            Log::warning('[CommissionService] Unknown install type', [
                'invoice_id'  => $invoice->id,
                'installType' => $installType,
            ]);

            return;
        }

        $amountTeknisi = (int) ($row['teknisi'] ?? 0);
        $amountKenek   = (int) ($row['kenek'] ?? 0);

        DB::transaction(function () use (
            $invoice,
            $teknisiId,
            $kenekId,
            $amountTeknisi,
            $amountKenek,
            $installType
        ) {
            if ($teknisiId && $amountTeknisi > 0) {
                $this->upsertCommission([
                    'employees_id'        => $teknisiId,
                    'customer_id'         => $invoice->customer_id,
                    'invoice_id'          => $invoice->id,
                    'amount'              => $amountTeknisi,
                    'role'                => 'teknisi',
                    'install_type'        => $installType,
                    'category_product_id' => null,
                ]);
            }

            if ($kenekId && $amountKenek > 0) {
                $this->upsertCommission([
                    'employees_id'        => $kenekId,
                    'customer_id'         => $invoice->customer_id,
                    'invoice_id'          => $invoice->id,
                    'amount'              => $amountKenek,
                    'role'                => 'kenek',
                    'install_type'        => $installType,
                    'category_product_id' => null,
                ]);
            }
        });

        Log::info('[CommissionService] Fixed commission done', [
            'invoice_id' => $invoice->id,
        ]);
    }


    /**
     * Mengubah final install type menjadi base type untuk komisi marketing.
     *
     * Marketing hanya mengenal:
     * - fullset
     * - dsp
     * - skkb
     */
    private function baseFromFinal(string $finalType): string
    {
        $type = strtolower($finalType);

        if (str_starts_with($type, 'dsp'))  return str_contains($type, 'panoramic') ? 'dsp_panoramic' : 'dsp';
        if (str_starts_with($type, 'skkb')) return 'skkb';

        return 'fullset'; // fullset panoramic → same rate as fullset in marketing config
    }


    /**
     * Membuat atau memperbarui komisi marketing berdasarkan kategori produk.
     */
    public function createMarketingForCustomer(
        ?int $marketingEmployeeId,
        int $customerId,
        ?int $invoiceId,
        int $categoryProductId,
        string $finalType,
        int $invoicePrice = 0,
    ): void {
        if (!$marketingEmployeeId) {
            return;
        }

        $baseType = $this->baseFromFinal($finalType);

        $marketingMap = config('commission.marketing', []);
        $categoryMap = $marketingMap[$categoryProductId] ?? ($marketingMap['default'] ?? []);

        $defaultAmount = (int) ($categoryMap[$baseType] ?? 0);

        $employee = Employee::find($marketingEmployeeId);
        $amount   = $this->employeeResolver->resolve($employee, $defaultAmount, $invoicePrice)['amount'];

        if ($amount <= 0) {
            Log::warning('[CommissionService] Marketing amount nol — kategori belum dikonfigurasi, tetap simpan row', [
                'marketing_employee_id' => $marketingEmployeeId,
                'customer_id'           => $customerId,
                'invoice_id'            => $invoiceId,
                'category_product_id'   => $categoryProductId,
                'final_type'            => $finalType,
                'base_type'             => $baseType,
            ]);
            // Tetap simpan row dengan amount=0 agar marketing selalu tercatat assignment-nya
        }

        $this->upsertCommission([
            'employees_id'        => $marketingEmployeeId,
            'customer_id'         => $customerId,
            'invoice_id'          => $invoiceId,
            'amount'              => $amount,
            'role'                => 'marketing',
            'install_type'        => $baseType,
            'category_product_id' => $categoryProductId,
        ]);

        Log::info('[CommissionService] Marketing commission saved', [
            'marketing_employee_id' => $marketingEmployeeId,
            'customer_id'           => $customerId,
            'invoice_id'            => $invoiceId,
            'category_product_id'   => $categoryProductId,
            'amount'                => $amount,
        ]);
    }


    /**
     * Menentukan kategori produk yang dipakai untuk komisi marketing.
     */
    public function resolveMarketingCategoryId(int $customerId): ?int
    {
        $categoryIds = DB::table('customer_product')
            ->where('customer_id', $customerId)
            ->pluck('category_product_id')
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            return null;
        }

        $priority = config('commission.marketing_category_priority', []);

        foreach ($priority as $preferredCategoryId) {
            if ($categoryIds->contains((int) $preferredCategoryId)) {
                return (int) $preferredCategoryId;
            }
        }

        $candidate = DB::table('category_products')
            ->whereIn('id', $categoryIds)
            ->select('id', 'duration')
            ->orderBy('duration', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($candidate) {
            return (int) $candidate->id;
        }

        return (int) $categoryIds->first();
    }


    /**
     * Membuat komisi marketing otomatis berdasarkan kategori customer.
     */
    public function createMarketingForCustomerAutoCategory(
        ?int $marketingEmployeeId,
        int $customerId,
        ?int $invoiceId,
        string $finalType,
        int $invoicePrice = 0,
    ): void {
        $categoryProductId = $this->resolveMarketingCategoryId($customerId);

        if (!$categoryProductId) {
            Log::warning('[CommissionService] Marketing category not found — no customer_product rows for this customer', [
                'marketing_employee_id' => $marketingEmployeeId,
                'customer_id'           => $customerId,
                'invoice_id'            => $invoiceId,
                'final_type'            => $finalType,
            ]);

            return;
        }

        $this->createMarketingForCustomer(
            $marketingEmployeeId,
            $customerId,
            $invoiceId,
            $categoryProductId,
            $finalType,
            $invoicePrice,
        );
    }


    /**
     * Membuat komisi PPF untuk teknisi, detailer, dan marketing.
     *
     * Rates dari config('commission.ppf'):
     *   - teknisi_rate  : % dari total_price (tukang_id pada instalasi)
     *   - detailer_rate : % dari total_price (kenek_id pada instalasi)
     *   - marketing_fixed: nominal tetap Rp (marketing_id pada customer PPF)
     */
    public function createPpfCommissions(PpfInvoice $invoice): void
    {
        // Installations are created via CustomerPpfController which uses the
        // technicians() pivot, not tukang_id/kenek_id. Load pivot, not legacy cols.
        $invoice->loadMissing([
            'installation.technicians',
            'customer.marketing',
        ]);

        $installation = $invoice->installation;
        $customer     = $invoice->customer; // CustomerPpf

        if (!$installation) {
            Log::warning('[CommissionService] PPF: installation not found', ['ppf_invoice_id' => $invoice->id]);
            return;
        }

        $technicians = $installation->technicians ?? collect();
        $totalPrice  = (int) $invoice->total_price;
        $ppfCfg      = config('commission.ppf', []);

        $teknisiRate    = (float) ($ppfCfg['teknisi_rate']    ?? 0.03);
        $detailerRate   = (float) ($ppfCfg['detailer_rate']   ?? 0.02);
        $adminRate      = (float) ($ppfCfg['admin_rate']      ?? 0.02);
        $marketingFixed = (int)   ($ppfCfg['marketing_fixed'] ?? 500000);

        $adminEmployees = \App\Models\Employee::where('ppf_admin_commission', true)->get();

        // Use invoice_date as the installation date so commissions fall into the
        // correct period, not the date the release button was clicked.
        $installedAt = \Carbon\Carbon::parse($invoice->invoice_date ?? $invoice->created_at);
        $period      = CommissionPeriod::resolveForDate($installedAt);
        $now         = now();

        Log::info('[CommissionService] PPF: releasing commissions', [
            'ppf_invoice_id'  => $invoice->id,
            'total_price'     => $totalPrice,
            'technician_count'=> $technicians->count(),
            'marketing_id'    => $customer?->marketing_id,
            'installed_at'    => $installedAt->toDateString(),
        ]);

        DB::transaction(function () use (
            $invoice, $installation, $customer, $technicians,
            $totalPrice, $teknisiRate, $detailerRate, $adminRate, $marketingFixed,
            $adminEmployees, $period, $installedAt, $now
        ) {
            // First technician = Teknisi (3%), remaining = Detailer (2% each).
            // Based on pivot order; applies to 1–4 technicians per installation.
            // Per-employee commission setting (none/percentage) overrides the default rate.
            foreach ($technicians as $index => $tech) {
                $isFirst = ($index === 0);
                $defaultAmount = (int) round($totalPrice * ($isFirst ? $teknisiRate : $detailerRate));
                $resolved = $this->employeeResolver->resolve($tech, $defaultAmount, $totalPrice);
                $this->upsertPpfCommission([
                    'employees_id'  => (int) $tech->id,
                    'ppf_invoice_id'=> $invoice->id,
                    'branch_id'     => $invoice->branch_id,
                    'amount'        => $resolved['amount'],
                    'role'          => $isFirst ? 'teknisi' : 'detailer',
                    'install_type'  => $installation->install_type ?? 'ppf',
                    'installed_at'  => $installedAt,
                ]);
            }

            if ($technicians->isEmpty()) {
                Log::warning('[CommissionService] PPF: no technicians assigned to installation', [
                    'ppf_invoice_id'  => $invoice->id,
                    'installation_id' => $installation->id,
                ]);
            }

            // Admin — 2% for every employee flagged ppf_admin_commission = true
            foreach ($adminEmployees as $admin) {
                $this->upsertPpfCommission([
                    'employees_id'  => (int) $admin->id,
                    'ppf_invoice_id'=> $invoice->id,
                    'branch_id'     => $invoice->branch_id,
                    'amount'        => (int) round($totalPrice * $adminRate),
                    'role'          => 'admin',
                    'install_type'  => $installation->install_type ?? 'ppf',
                    'installed_at'  => $installedAt,
                ]);
            }

            // Marketing — fixed Rp500.000, kecuali ada setting komisi kustom per karyawan
            if ($customer && $customer->marketing_id) {
                $marketingResolved = $this->employeeResolver->resolve($customer->marketing, $marketingFixed, $totalPrice);
                $this->upsertPpfCommission([
                    'employees_id'  => (int) $customer->marketing_id,
                    'ppf_invoice_id'=> $invoice->id,
                    'branch_id'     => $invoice->branch_id,
                    'amount'        => $marketingResolved['amount'],
                    'role'          => 'marketing',
                    'install_type'  => $installation->install_type ?? 'ppf',
                    'installed_at'  => $installedAt,
                ]);
            }

            // Stamp period on all PPF commission rows for this invoice
            DB::table('commissions')
                ->where('ppf_invoice_id', $invoice->id)
                ->whereNull('commission_period_id')
                ->update(['commission_period_id' => $period->id, 'updated_at' => $now]);

            // Correct created_at to the PPF invoice date for all rows of this PPF invoice.
            DB::table('commissions')
                ->where('ppf_invoice_id', $invoice->id)
                ->update(['created_at' => $installedAt]);
        });

        // Bust leaderboard cache for this branch
        $bid = $invoice->branch_id ?? 0;
        Cache::forget("commission.top_marketing.{$bid}");
        Cache::forget("commission.top_installer.{$bid}");

        Log::info('[CommissionService] PPF: commissions created', [
            'ppf_invoice_id'  => $invoice->id,
            'technician_count'=> $technicians->count(),
        ]);
    }


    /**
     * Menghapus semua komisi berdasarkan PPF invoice.
     */
    public function deleteByPpfInvoice(int $ppfInvoiceId): void
    {
        DB::table('commissions')->where('ppf_invoice_id', $ppfInvoiceId)->delete();
    }


    /**
     * Menghapus semua komisi berdasarkan invoice.
     *
     * Berguna ketika invoice dihapus.
     */
    public function deleteByInvoice(int $invoiceId): void
    {
        DB::table('commissions')
            ->where('invoice_id', $invoiceId)
            ->delete();
    }


    /**
     * Helper untuk insert/update komisi agar tidak dobel.
     *
     * Tidak menggunakan updateOrInsert() karena MySQL `column = NULL` tidak pernah
     * match — query WHERE invoice_id = NULL selalu kosong. Kita handle NULL secara
     * eksplisit dengan whereNull().
     *
     * Pada INSERT: set status='eligible'. Pada UPDATE: jangan sentuh status
     * supaya komisi yang sudah 'paid' tidak ter-reset.
     */
    private function upsertCommission(array $data): void
    {
        $invoiceId = $data['invoice_id'];

        $query = DB::table('commissions')
            ->where('employees_id', $data['employees_id'])
            ->where('role', $data['role']);

        if (is_null($invoiceId)) {
            $query->whereNull('invoice_id');
        } else {
            $query->where('invoice_id', $invoiceId);
        }

        $existing = $query->first();

        $payload = [
            'customer_id'         => $data['customer_id'],
            'amount'              => $data['amount'],
            'install_type'        => $data['install_type'] ?? null,
            'category_product_id' => $data['category_product_id'] ?? null,
            'updated_at'          => now(),
        ];

        if ($existing) {
            DB::table('commissions')->where('id', $existing->id)->update($payload);
        } else {
            DB::table('commissions')->insert(array_merge(
                [
                    'employees_id' => $data['employees_id'],
                    'invoice_id'   => $invoiceId,
                    'role'         => $data['role'],
                    'status'       => 'eligible',
                    'is_active'    => true,
                    'created_at'   => now(),
                ],
                $payload
            ));
        }
    }


    /**
     * Insert-or-update for PPF commissions.
     * Deduplication key: (employees_id, role, ppf_invoice_id).
     */
    private function upsertPpfCommission(array $data): void
    {
        $existing = DB::table('commissions')
            ->where('employees_id',   $data['employees_id'])
            ->where('role',           $data['role'])
            ->where('ppf_invoice_id', $data['ppf_invoice_id'])
            ->first();

        $installedAt = $data['installed_at'] ?? now();

        $payload = [
            'amount'       => $data['amount'],
            'install_type' => $data['install_type'] ?? null,
            'branch_id'    => $data['branch_id'] ?? null,
            'updated_at'   => now(),
        ];

        if ($existing) {
            DB::table('commissions')->where('id', $existing->id)->update($payload);
        } else {
            DB::table('commissions')->insert(array_merge([
                'employees_id'   => $data['employees_id'],
                'customer_id'    => null,
                'invoice_id'     => null,
                'ppf_invoice_id' => $data['ppf_invoice_id'],
                'branch_id'      => $data['branch_id'] ?? null,
                'role'           => $data['role'],
                'status'         => 'eligible',
                'is_active'      => true,
                'created_at'     => $installedAt,
            ], $payload));
        }
    }
}
