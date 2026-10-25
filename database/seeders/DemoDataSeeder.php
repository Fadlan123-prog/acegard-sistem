<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Attendance;
use App\Models\BankAccount;
use App\Models\BankStatementEntry;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\CommissionPaymentBatch;
use App\Models\CommissionPeriod;
use App\Models\CreativeTask;
use App\Models\Customer;
use App\Models\CustomerBuilding;
use App\Models\CustomerBuildingProduct;
use App\Models\CustomerPpf;
use App\Models\CustomerPpfInstallation;
use App\Models\CustomerProduct;
use App\Models\Dealer;
use App\Models\DebtPayment;
use App\Models\DevelopmentAnnualPlan;
use App\Models\DevelopmentReport;
use App\Models\Employee;
use App\Models\EmployeeClaim;
use App\Models\EmployeeHealthBalance;
use App\Models\ExportOrder;
use App\Models\ExportOrderItem;
use App\Models\FilmComplaint;
use App\Models\FilmComplaintItem;
use App\Models\FilmRollEntry;
use App\Models\FilmStockPool;
use App\Models\ImportOrder;
use App\Models\ImportOrderItem;
use App\Models\InternationalPartner;
use App\Models\Invoice;
use App\Models\InvoiceBuilding;
use App\Models\LeaveRequest;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\NotificationConfig;
use App\Models\Payable;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PpfInvoice;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Receivable;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\SupportingGood;
use App\Models\SupportingGoodMutation;
use App\Models\User;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimInvoice;
use App\Models\WarrantyClaimInvoiceItem;
use App\Models\WarrantyClaimItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Transactional dummy data for manual QA, covering the modules in the
 * "Inspection Lane" checklist: CRM funnel, customers/installations,
 * invoicing, warranty & complaints, commissions, HR, finance, inventory,
 * import/export and development reporting.
 *
 * Local-only (same guard as DevAccountSeeder/TeknisiSeeder) — never meant to
 * run against production. Safe to re-run: every unique business number gets
 * a random suffix, and master-ish records (dealers, suppliers, shifts,
 * international partners) use firstOrCreate.
 */
class DemoDataSeeder extends Seeder
{
    private Branch $pusat;
    private Branch $bandung;
    private Branch $jambi;
    private Branch $bengkulu;

    private User $superadmin;
    private User $adminJambi;
    private User $owner;
    private User $mgrOps;
    private User $marketingUser;
    private User $financeUser;
    private User $purchasingUser;
    private User $csUser;
    private User $exportImportUser;
    private User $teknisiUser;
    private User $kenekUser;

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('DemoDataSeeder: refusing to run outside the local environment.');
            return;
        }

        $this->loadReferenceData();
        $this->backfillEmployeeBranches();

        $this->seedDealersAndArticles();
        $this->seedTrade();

        [$carCustomers, $buildingCustomers, $ppfCustomers, $ppfInstallations] = $this->seedCustomers();

        $this->seedCrmFunnel();
        $this->seedInvoicing($carCustomers, $buildingCustomers, $ppfInstallations);
        $this->seedWarrantyAndComplaints($carCustomers, $buildingCustomers);
        $this->seedCommissions($carCustomers);
        $this->seedHr();
        $this->seedFinance();
        $this->seedInventory();
        $this->seedDevelopment();

        $this->command?->info('DemoDataSeeder: dummy data ready for manual QA.');
    }

    private function loadReferenceData(): void
    {
        $branches = Branch::orderBy('id')->get()->values();
        $this->pusat    = $branches->get(0);
        $this->bandung  = $branches->get(1);
        $this->jambi    = $branches->get(2);
        $this->bengkulu = $branches->get(3);

        $this->superadmin       = User::where('email', 'acegard@acegard.id')->firstOrFail();
        $this->adminJambi       = User::where('email', 'acegard.jambi@acegard.id')->firstOrFail();
        $this->owner            = User::where('email', 'owner@acegard.id')->firstOrFail();
        $this->mgrOps           = User::where('email', 'mgrops@acegard.id')->firstOrFail();
        $this->marketingUser    = User::where('email', 'marketing@acegard.id')->firstOrFail();
        $this->financeUser      = User::where('email', 'finance@acegard.id')->firstOrFail();
        $this->purchasingUser   = User::where('email', 'purchasing@acegard.id')->firstOrFail();
        $this->csUser           = User::where('email', 'cs@acegard.id')->firstOrFail();
        $this->exportImportUser = User::where('email', 'exportimport@acegard.id')->firstOrFail();
        $this->teknisiUser      = User::where('email', 'teknisi@acegard.id')->firstOrFail();
        $this->kenekUser        = User::where('email', 'kenek@acegard.id')->firstOrFail();
    }

    /** EmployeeSeeder leaves branch_id null on most rows — spread them across branches so SO/commission/payroll data means something. */
    private function backfillEmployeeBranches(): void
    {
        $branchIds = [$this->pusat->id, $this->bandung->id, $this->jambi->id, $this->bengkulu->id];

        Employee::whereNull('branch_id')->orderBy('id')->get()->each(function (Employee $employee, int $i) use ($branchIds) {
            $employee->update(['branch_id' => $branchIds[$i % count($branchIds)]]);
        });
    }

    private function num(string $prefix): string
    {
        return $prefix . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
    }

    /**
     * A date within the current month, never later than today and never
     * before the 1st. Jadwal Pemasangan's default view is the current
     * calendar month (JadwalPemasanganController::index()) — a flat
     * `now()->subDays($n)` can silently cross into last month depending on
     * which day the seeder happens to run on, leaving Customer/PPF
     * installations invisible there even though SalesOrder.scheduled_date
     * (which looks forward, not back) still lands inside the month.
     */
    private function dayThisMonth(int $daysAgo = 0): \Illuminate\Support\Carbon
    {
        return now()->subDays(min($daysAgo, now()->day - 1));
    }

    // ───────────────────────────── A & M: public content ─────────────────────

    private function seedDealersAndArticles(): void
    {
        $dealers = [
            ['name' => 'Acegard Tangerang (Pusat)', 'type' => 'cabang_utama', 'location' => 'Tangerang, Banten'],
            ['name' => 'Acegard Bandung',           'type' => 'dealer',       'location' => 'Bandung, Jawa Barat'],
            ['name' => 'Acegard Jambi',              'type' => 'dealer',       'location' => 'Jambi'],
            ['name' => 'Mitra Dealer Bengkulu',      'type' => 'dealer',       'location' => 'Bengkulu'],
        ];

        foreach ($dealers as $i => $d) {
            Dealer::firstOrCreate(['name' => $d['name']], [
                'type'           => $d['type'],
                'location'       => $d['location'],
                'admin_whatsapp' => '628123456' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'maps_url'       => 'https://maps.google.com/?q=' . rawurlencode($d['location']),
                'is_active'      => true,
                'sort_order'     => $i,
            ]);
        }

        $articles = [
            ['title' => 'Tips Merawat Kaca Film Mobil Agar Awet', 'category' => 'craft', 'published' => true],
            ['title' => 'Acegard Buka Cabang Baru di Jambi', 'category' => 'company', 'published' => true],
            ['title' => 'Perbedaan PPF dan Kaca Film: Mana yang Cocok untuk Anda?', 'category' => 'resource', 'published' => true],
            ['title' => '[Draft] Promo Akhir Tahun — Belum Rilis', 'category' => 'news', 'published' => false],
        ];

        foreach ($articles as $a) {
            Article::firstOrCreate(['slug' => Str::slug($a['title'])], [
                'title'        => $a['title'],
                'category'     => $a['category'],
                'excerpt'      => fake()->sentence(15),
                'body'         => fake()->paragraphs(4, true),
                'is_published' => $a['published'],
                'published_at' => $a['published'] ? now()->subDays(fake()->numberBetween(1, 60)) : null,
            ]);
        }
    }

    // ───────────────────────────── L: import/export ───────────────────────────

    private function seedTrade(): void
    {
        foreach (['PT Sumber Film Jaya', 'Toko Variasi Mobil Makmur', 'CV Bahan Kimia Detailing'] as $name) {
            Supplier::firstOrCreate(['name' => $name], [
                'phone_number' => fake()->phoneNumber(),
                'email'        => Str::slug($name) . '@supplier.test',
                'address'      => fake()->address(),
                'city'         => fake()->city(),
            ]);
        }

        $koreaSupplier = InternationalPartner::firstOrCreate(
            ['name' => 'Korea Film Co'],
            ['type' => 'supplier', 'country' => 'South Korea', 'default_currency' => 'USD', 'contact_person' => 'Mr. Kim', 'phone' => '+82-10-0000-0000', 'email' => 'sales@koreafilm.test']
        );
        $aussieBuyer = InternationalPartner::firstOrCreate(
            ['name' => 'Aussie Auto Parts'],
            ['type' => 'buyer', 'country' => 'Australia', 'default_currency' => 'AUD', 'contact_person' => 'Mr. Smith', 'phone' => '+61-2-0000-0000', 'email' => 'buy@aussieauto.test']
        );

        // Import order 1: still in transit (ordered)
        $this->makeImportOrder($koreaSupplier, 'ordered', [
            ['item_name' => 'PPF Roll 1.52m', 'quantity' => 10, 'unit' => 'roll', 'unit_price_foreign' => 200],
            ['item_name' => 'PPF Roll 1.8m', 'quantity' => 5, 'unit' => 'roll', 'unit_price_foreign' => 300],
        ], 15800);

        // Import order 2: already received, for testing the "receive" transition read-side
        $this->makeImportOrder($koreaSupplier, 'received', [
            ['item_name' => 'Kaca Film Roll 1.52m 60%', 'quantity' => 20, 'unit' => 'roll', 'unit_price_foreign' => 80],
        ], 15750, receivedDate: now()->subDays(5));

        $this->makeExportOrder($aussieBuyer, 'confirmed', [
            ['item_name' => 'PPF Finished Kit', 'quantity' => 20, 'unit' => 'kit', 'unit_price_foreign' => 150],
        ], 10500);
    }

    private function makeImportOrder(InternationalPartner $partner, string $status, array $items, float $rate, ?\Illuminate\Support\Carbon $receivedDate = null): void
    {
        $subtotal = array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_price_foreign'], $items));
        $duty = 2000000; $tax = 1500000; $freight = 3000000; $insurance = 500000;
        $totalIdr = (int) round($subtotal * $rate) + $duty + $tax + $freight + $insurance;

        $order = ImportOrder::create([
            'international_partner_id' => $partner->id,
            'order_number'      => $this->num('IMP'),
            'currency'          => 'USD',
            'exchange_rate'     => $rate,
            'subtotal_foreign'  => $subtotal,
            'duty_cost'         => $duty,
            'tax_cost'          => $tax,
            'freight_cost'      => $freight,
            'insurance_cost'    => $insurance,
            'total_cost_idr'    => $totalIdr,
            'status'            => $status,
            'order_date'        => now()->subDays(10),
            'estimated_arrival' => now()->addDays(10),
            'received_date'     => $receivedDate,
            'created_by'        => $this->exportImportUser->id,
        ]);

        foreach ($items as $item) {
            ImportOrderItem::create(array_merge($item, ['import_order_id' => $order->id]));
        }
    }

    private function makeExportOrder(InternationalPartner $partner, string $status, array $items, float $rate): void
    {
        $subtotal = array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_price_foreign'], $items));

        $order = ExportOrder::create([
            'international_partner_id' => $partner->id,
            'order_number'      => $this->num('EXP'),
            'currency'          => 'AUD',
            'exchange_rate'     => $rate,
            'subtotal_foreign'  => $subtotal,
            'total_revenue_idr' => (int) round($subtotal * $rate),
            'status'            => $status,
            'order_date'        => now()->subDays(7),
            'created_by'        => $this->exportImportUser->id,
        ]);

        foreach ($items as $item) {
            ExportOrderItem::create(array_merge($item, ['export_order_id' => $order->id]));
        }
    }

    // ───────────────────────────── E: customers & installations ──────────────

    private function seedCustomers(): array
    {
        // Deterministic order (by id, not random): TeknisiSeeder's named login
        // accounts (Rizki, Dhani, Fajri, Alam, ...) have the lowest employee
        // ids, so they land first here — which matters because seedInvoicing()
        // only attaches an Invoice to the first few customers, and the
        // teknisi/kenek "Semua Orderan" page only lists a Customer when it has
        // both an assigned tukang_id/kenek_id *and* an Invoice. Random order
        // risked leaving every demo login with an empty order list.
        $teknisi = Employee::where('job_position', 'Teknisi')->orderBy('id')->get();
        $kenek   = Employee::where('job_position', 'Kenek')->orderBy('id')->get();
        $marketing = Employee::where('job_position', 'Marketing')->orderBy('id')->get();

        $carCustomers = [];
        $carSpecs = [
            ['branch' => $this->pusat, 'brand' => 'Toyota', 'model' => 'Avanza', 'panoramic' => false],
            ['branch' => $this->pusat, 'brand' => 'Honda', 'model' => 'CR-V', 'panoramic' => true],
            ['branch' => $this->jambi, 'brand' => 'Mitsubishi', 'model' => 'Xpander', 'panoramic' => false],
            ['branch' => $this->bandung, 'brand' => 'Daihatsu', 'model' => 'Terios', 'panoramic' => false],
        ];

        foreach ($carSpecs as $i => $spec) {
            $customer = Customer::create([
                'branch_id'         => $spec['branch']->id,
                'wsn'               => 'WSN' . now()->format('ymd') . Str::upper(Str::random(5)),
                'card_number'       => now()->format('Y') . Str::upper(Str::random(6)),
                'name'              => fake()->name(),
                'email'             => fake()->safeEmail(),
                'phone_number'      => fake()->phoneNumber(),
                'address'           => fake()->address(),
                'dealer_name'       => fake()->company(),
                'city'              => $spec['branch']->city,
                'country'           => 'Indonesia',
                'vehicle_brand'     => $spec['brand'],
                'vehicle_model'     => $spec['model'],
                'plat_number'       => strtoupper(fake()->bothify('B #### ??')),
                'install_type'      => 'fullset',
                'has_panoramic'     => $spec['panoramic'],
                'tukang_id'         => $teknisi->get($i % max($teknisi->count(), 1))?->id,
                'kenek_id'          => $kenek->get($i % max($kenek->count(), 1))?->id,
                'marketing_id'      => $marketing->get($i % max($marketing->count(), 1))?->id,
                'vehicle_year'      => (string) fake()->numberBetween(2018, 2026),
                'warantee_start'    => $this->dayThisMonth($i)->toDateString(),
                'warantee_end'      => $this->dayThisMonth($i)->addYears(5)->toDateString(),
                'warantee_duration' => 5,
                'install_date'      => $this->dayThisMonth($i)->toDateString(),
            ]);

            CustomerProduct::create([
                'customer_id'         => $customer->id,
                'product_id'          => ($i % 12) + 1,
                'category_product_id' => ($i % 3) + 1,
                'part_id'             => 1, // WINDSHIELD
            ]);
            CustomerProduct::create([
                'customer_id'         => $customer->id,
                'product_id'          => ($i % 12) + 1,
                'category_product_id' => ($i % 3) + 1,
                'part_id'             => 2, // FIRST ROW SIDE WINDOW
            ]);

            $carCustomers[] = $customer;
        }

        // Building customers
        $buildingCustomers = [];
        foreach ([$this->pusat, $this->jambi, $this->bandung] as $i => $branch) {
            $customer = CustomerBuilding::create([
                'branch_id'    => $branch->id,
                'wsn'          => 'WSNB' . now()->format('ymd') . Str::upper(Str::random(5)),
                'card_number'  => 'BD' . now()->format('Y') . Str::upper(Str::random(6)),
                'name'         => fake()->company() . ' Office Tower',
                'email'        => fake()->companyEmail(),
                'phone_number' => fake()->phoneNumber(),
                'address'      => fake()->address(),
                'dealer_name'  => fake()->company(),
                'applicator'   => fake()->name(),
                'city'         => $branch->city,
                'country'      => 'Indonesia',
            ]);

            // warantee_start doubles as this product's install date — customer_building
            // itself has no install_date/warantee_start column (only this per-product
            // table does), so Jadwal Pemasangan reads it from here for "Kaca Film
            // Bangunan" entries. Keep it inside the current month like the car/PPF dates.
            CustomerBuildingProduct::create([
                'customer_building_id'          => $customer->id,
                'product_building_id'           => ($i % 20) + 1,
                'category_product_building_id'  => ($i % 4) + 1,
                'meters'                        => fake()->randomFloat(2, 50, 400),
                'warantee_start'                => $this->dayThisMonth($i)->toDateString(),
                'warantee_end'                  => $this->dayThisMonth($i)->addYears(5)->toDateString(),
                'warantee_duration'              => 5,
            ]);

            $buildingCustomers[] = $customer;
        }

        // PPF customers + installations (varied warranty_status)
        $ppfCustomers = [];
        $ppfInstallations = [];
        $ppfSpecs = [
            ['branch' => $this->pusat, 'status' => 'active', 'type' => 'fullbody'],
            ['branch' => $this->pusat, 'status' => 'expired', 'type' => 'fullbody'],
            ['branch' => $this->jambi, 'status' => 'void', 'type' => 'partial'],
        ];

        foreach ($ppfSpecs as $i => $spec) {
            $customer = CustomerPpf::create([
                'branch_id'     => $spec['branch']->id,
                'wsn'           => 'WSNP' . now()->format('ymd') . Str::upper(Str::random(5)),
                'card_number'   => 'PPF-' . now()->format('ymd') . '-' . Str::upper(Str::random(4)),
                'name'          => fake()->name(),
                'phone'         => fake()->phoneNumber(),
                'email'         => fake()->safeEmail(),
                'address'       => fake()->address(),
                'city'          => $spec['branch']->city,
                'country'       => 'Indonesia',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'Civic Type R',
                'vehicle_year'  => (string) fake()->numberBetween(2020, 2026),
                'vehicle_color' => 'Hitam',
                'vehicle_plate' => strtoupper(fake()->bothify('B #### ??')),
                'marketing_id'  => $marketing->get($i % max($marketing->count(), 1))?->id,
            ]);

            // 'expired' genuinely needs to be old (warranty_years=1, so install_date
            // must predate "now - 1 year" to actually be expired) — only the
            // non-expired statuses get pulled into the current month so Jadwal
            // Pemasangan's default view has something to show for PPF too.
            $installDate = $spec['status'] === 'expired' ? now()->subYears(2) : $this->dayThisMonth($i);

            $installation = CustomerPpfInstallation::create([
                'customer_ppf_id'  => $customer->id,
                'ppf_product_id'   => ($i % 4) + 1,
                'branch_id'        => $spec['branch']->id,
                'install_type'     => $spec['type'],
                'install_date'     => $installDate->toDateString(),
                'tukang_id'        => $teknisi->get($i % max($teknisi->count(), 1))?->id,
                'kenek_id'         => $kenek->get($i % max($kenek->count(), 1))?->id,
                'warranty_code'    => 'PPFW-' . now()->format('ymd') . '-' . Str::upper(Str::random(5)),
                'warranty_start'   => $installDate->toDateString(),
                'warranty_years'   => 1,
                'warranty_end'     => $installDate->copy()->addYear()->toDateString(),
                'warranty_status'  => $spec['status'],
                'panels'           => $spec['type'] === 'partial' ? ['hood', 'door_fl', 'door_fr'] : null,
                'notes'            => 'Seeded demo data untuk QA manual.',
            ]);

            $installation->technicians()->syncWithoutDetaching(
                array_filter([$teknisi->get($i)?->id, $kenek->get($i)?->id])
            );

            $ppfCustomers[] = $customer;
            $ppfInstallations[] = $installation;
        }

        return [$carCustomers, $buildingCustomers, $ppfCustomers, $ppfInstallations];
    }

    // ───────────────────────────── D: CRM funnel ──────────────────────────────

    private function seedCrmFunnel(): void
    {
        $marketingEmployees = Employee::where('job_position', 'Marketing')->get();

        $leadSpecs = [
            ['status' => 'new', 'branch' => $this->pusat],
            ['status' => 'contacted', 'branch' => $this->pusat],
            ['status' => 'follow_up', 'branch' => $this->jambi],
            ['status' => 'quoted', 'branch' => $this->jambi],
            ['status' => 'won', 'branch' => $this->bandung],
            ['status' => 'lost', 'branch' => $this->bandung],
        ];

        $leads = [];
        foreach ($leadSpecs as $i => $spec) {
            $lead = Lead::create([
                'branch_id'     => $spec['branch']->id,
                'name'          => fake()->name(),
                'phone'         => fake()->phoneNumber(),
                'email'         => fake()->safeEmail(),
                'address'       => fake()->address(),
                'city'          => $spec['branch']->city,
                'source'        => fake()->randomElement(['walk_in', 'referral', 'whatsapp', 'website']),
                'category'      => fake()->randomElement(['car_film', 'building_film', 'ppf']),
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Fortuner',
                'vehicle_year'  => (string) fake()->numberBetween(2020, 2026),
                'notes'         => fake()->sentence(),
                'status'        => $spec['status'],
                'lost_reason'   => $spec['status'] === 'lost' ? 'Harga dianggap terlalu mahal oleh prospek.' : null,
                'assigned_to'   => $marketingEmployees->get($i % max($marketingEmployees->count(), 1))?->id,
                'estimated_value' => fake()->numberBetween(1_500_000, 15_000_000),
                'follow_up_date'  => $spec['status'] === 'follow_up' ? now()->addDays(2)->toDateString() : null,
                'quoted_at'       => in_array($spec['status'], ['quoted', 'won']) ? now()->subDays(2) : null,
                'converted_at'    => $spec['status'] === 'won' ? now()->subDay() : null,
                'created_by'      => $this->marketingUser->id,
            ]);

            LeadActivity::create([
                'lead_id'    => $lead->id,
                'type'       => 'call',
                'notes'      => 'Follow up telepon pertama, prospek tertarik dengan promo.',
                'created_by' => $this->marketingUser->id,
            ]);
            LeadActivity::create([
                'lead_id'    => $lead->id,
                'type'       => 'status_change',
                'notes'      => "Status diubah menjadi {$spec['status']}.",
                'created_by' => $this->marketingUser->id,
            ]);

            $leads[] = $lead;
        }

        // Quotations — one per status, one linked to the "quoted" lead
        $quotedLead = $leads[3];
        $quotationSpecs = ['draft', 'sent', 'accepted', 'rejected', 'expired'];
        $quotations = [];

        foreach ($quotationSpecs as $i => $status) {
            $items = [
                ['description' => 'Kaca Film 4K Alpha Pro - Full Set', 'unit' => 'set', 'quantity' => 1, 'unit_price' => 3_500_000, 'discount_percent' => 0],
                ['description' => 'Jasa Pemasangan', 'unit' => 'jasa', 'quantity' => 1, 'unit_price' => 500_000, 'discount_percent' => 0],
            ];
            $subtotal = array_sum(array_map(fn ($it) => $it['quantity'] * $it['unit_price'] * (1 - $it['discount_percent'] / 100), $items));

            $quotation = Quotation::create([
                'branch_id'        => $this->jambi->id,
                'lead_id'          => $i === 2 ? $quotedLead->id : null,
                'quotation_number' => $this->num('QUO'),
                'date'             => now()->subDays(5)->toDateString(),
                'valid_until'      => $status === 'expired' ? now()->subDay()->toDateString() : now()->addDays(14)->toDateString(),
                'prospect_name'    => fake()->name(),
                'prospect_phone'   => fake()->phoneNumber(),
                'category'         => 'car_film',
                'vehicle_brand'    => 'Toyota',
                'vehicle_model'    => 'Fortuner',
                'status'           => $status === 'expired' ? 'sent' : $status,
                'subtotal'         => $subtotal,
                'discount_amount'  => 0,
                'total_amount'     => $subtotal,
                'assigned_to'      => $marketingEmployees->first()?->id,
                'created_by'       => $this->marketingUser->id,
                'sent_at'          => in_array($status, ['sent', 'accepted', 'rejected', 'expired']) ? now()->subDays(4) : null,
                'accepted_at'      => $status === 'accepted' ? now()->subDays(2) : null,
                'rejected_at'      => $status === 'rejected' ? now()->subDays(2) : null,
                'rejection_reason' => $status === 'rejected' ? 'Prospek memilih kompetitor dengan harga lebih rendah.' : null,
            ]);

            foreach ($items as $sort => $item) {
                QuotationItem::create(array_merge($item, [
                    'quotation_id' => $quotation->id,
                    'sort_order'   => $sort,
                    'subtotal'     => $item['quantity'] * $item['unit_price'] * (1 - $item['discount_percent'] / 100),
                ]));
            }

            $quotations[$status] = $quotation;
        }

        // Sales orders — pending/confirmed(with SPK)/in_progress/completed/cancelled
        $soSpecs = [
            ['status' => 'pending', 'quotation' => null],
            ['status' => 'confirmed', 'quotation' => $quotations['accepted'], 'spk' => true],
            ['status' => 'in_progress', 'quotation' => null],
            ['status' => 'completed', 'quotation' => null],
            ['status' => 'cancelled', 'quotation' => null],
        ];

        $teknisi = Employee::where('job_position', 'Teknisi')->get();
        $kenek   = Employee::where('job_position', 'Kenek')->get();

        foreach ($soSpecs as $i => $spec) {
            $items = [
                ['description' => 'Kaca Film 4K Alpha Pro - Full Set', 'unit' => 'set', 'quantity' => 1, 'unit_price' => 3_500_000, 'discount_percent' => 0],
            ];
            $subtotal = array_sum(array_map(fn ($it) => (int) round($it['quantity'] * $it['unit_price'] * (1 - $it['discount_percent'] / 100)), $items));

            $so = SalesOrder::create([
                'branch_id'      => $this->jambi->id,
                'quotation_id'   => $spec['quotation']?->id,
                'so_number'      => $this->num('SO'),
                'spk_number'     => ! empty($spec['spk']) ? $this->num('SPK') : null,
                'spk_token'      => ! empty($spec['spk']) ? Str::random(40) : null,
                'spk_issued_at'  => ! empty($spec['spk']) ? now()->subDay() : null,
                'date'           => now()->subDays(3)->toDateString(),
                'scheduled_date' => now()->addDays(2)->toDateString(),
                'prospect_name'  => $spec['quotation']?->prospect_name ?? fake()->name(),
                'prospect_phone' => fake()->phoneNumber(),
                'category'       => 'car_film',
                'vehicle_brand'  => 'Toyota',
                'vehicle_model'  => 'Fortuner',
                // tukang_id/kenek_id SENGAJA tidak diisi di create() — di alur
                // nyata itu cuma bisa lewat SalesOrderController::assign() setelah
                // SO dikonfirmasi. Di-set di bawah, setelah create, meniru urutan
                // yang sama (dan hanya untuk SO yang sudah lewat tahap confirm).
                'marketing_id'   => $marketingEmployees->first()?->id,
                'subtotal'       => $subtotal,
                'discount_amount' => 0,
                'total_amount'   => $subtotal,
                'status'         => $spec['status'],
                'confirmed_at'   => in_array($spec['status'], ['confirmed', 'in_progress', 'completed']) ? now()->subDays(2) : null,
                'in_progress_at' => in_array($spec['status'], ['in_progress', 'completed']) ? now()->subDay() : null,
                'completed_at'   => $spec['status'] === 'completed' ? now() : null,
                'cancelled_at'   => $spec['status'] === 'cancelled' ? now() : null,
                'cancel_reason'  => $spec['status'] === 'cancelled' ? 'Prospek membatalkan pesanan sebelum jadwal instalasi.' : null,
                'created_by'     => $this->marketingUser->id,
            ]);

            // Assign happens only once the SO has passed confirm — matches
            // SalesOrderController::assign()'s own gate. Offset by -1 so the
            // first assignable SO (i=1, 'confirmed') lands on teknisi/kenek
            // index 0 (Rizki/Fajri, the named login accounts) instead of
            // skipping them — same reasoning as the Customer assignment above.
            if (in_array($spec['status'], ['confirmed', 'in_progress', 'completed'])) {
                $so->update([
                    'tukang_id' => $teknisi->get(($i - 1) % max($teknisi->count(), 1))?->id,
                    'kenek_id'  => $kenek->get(($i - 1) % max($kenek->count(), 1))?->id,
                ]);
            }

            foreach ($items as $sort => $item) {
                SalesOrderItem::create(array_merge($item, [
                    'sales_order_id' => $so->id,
                    'sort_order'     => $sort,
                    'subtotal'       => (int) round($item['quantity'] * $item['unit_price'] * (1 - $item['discount_percent'] / 100)),
                ]));
            }
        }

        if ($quotations['accepted']) {
            $quotations['accepted']->update(['converted_at' => now()->subDay()]);
        }
    }

    // ───────────────────────────── F: invoicing ───────────────────────────────

    private function seedInvoicing(array $carCustomers, array $buildingCustomers, array $ppfInstallations): void
    {
        // work_status varies on purpose (not all pre-completed) so the demo
        // teknisi accounts actually have something to click through: one job
        // still 'pending' (Terima), one 'accepted' (Selesaikan), one fully
        // 'completed' with QC passed and commission released. Timestamps are
        // explicit and strictly increasing (created < invoice_date <
        // accepted_at < completed_at < pelunasan_at < commission_released_at)
        // — leaving created_at to Eloquent's default `now()` while backdating
        // completed_at produced a "finished before it was assigned" bug.
        $invoiceSpecs = [
            ['status' => 'unpaid',  'work_status' => 'pending'],
            ['status' => 'partial', 'work_status' => 'accepted'],
            ['status' => 'paid',    'work_status' => 'completed', 'releaseCommission' => true],
        ];

        foreach ($invoiceSpecs as $i => $spec) {
            $customer = $carCustomers[$i % count($carCustomers)];
            $price = 4_000_000;
            $downpayment = $spec['status'] === 'unpaid' ? 0 : ($spec['status'] === 'partial' ? 2_000_000 : $price);
            $isAccepted  = in_array($spec['work_status'], ['accepted', 'completed']);
            $isCompleted = $spec['work_status'] === 'completed';

            // created_at isn't in Customer's $fillable, so create()/mass-assignment
            // silently drops it and Eloquent stamps "now" anyway — force it after
            // the fact so "Ditugaskan oleh Manager" in teknisi/orders/show.blade.php
            // (which reads $customer->created_at) predates accepted_at/completed_at
            // below instead of showing a job "finished" before it was even assigned.
            $customer->created_at = now()->subDays(6);
            $customer->timestamps = false;
            $customer->save();
            $customer->timestamps = true;

            Invoice::create([
                'customer_id'       => $customer->id,
                'payment_method'    => 'transfer',
                'price'             => $price,
                'discount'          => 0,
                'downpayment'       => $downpayment,
                'total_price'       => $price,
                'remaining_payment' => $price - $downpayment,
                'status'            => $spec['status'],
                'work_status'       => $spec['work_status'],
                'qc_status'         => $isCompleted ? 'approved' : null,
                'invoice_number'    => $this->num('INV'),
                'invoice_date'      => now()->subDays(5),
                'accepted_at'       => $isAccepted ? now()->subDays(4) : null,
                'completed_at'      => $isCompleted ? now()->subDays(3) : null,
                'pelunasan_at'      => $spec['status'] === 'paid' ? now()->subDays(2) : null,
                'pelunasan_by'      => $spec['status'] === 'paid' ? $this->financeUser->id : null,
                'commission_released_at' => ! empty($spec['releaseCommission']) ? now()->subDay() : null,
                'commission_released_by' => ! empty($spec['releaseCommission']) ? $this->owner->id : null,
            ]);
        }

        // Invoice Building — unpaid & paid
        foreach (['unpaid', 'paid'] as $i => $status) {
            $customer = $buildingCustomers[$i % count($buildingCustomers)];
            $price = 8_000_000;

            InvoiceBuilding::create([
                'customer_building_id' => $customer->id,
                'branch_id'             => $customer->branch_id,
                'user_id'               => $this->financeUser->id,
                'type'                  => 'transfer',
                'price'                 => $price,
                'discount'              => 0,
                'down_payment'          => $status === 'paid' ? $price : 0,
                'total_price'           => $price,
                'remaining_payment'     => $status === 'paid' ? 0 : $price,
                'status'                => $status,
                'invoice_number'        => $this->num('INVB'),
                'invoice_date'          => now()->subDays(3)->toDateString(),
                'pelunasan_at'          => $status === 'paid' ? now()->subDay() : null,
                'pelunasan_by'          => $status === 'paid' ? $this->financeUser->id : null,
            ]);
        }

        // PPF invoices — unpaid & paid+commission released
        foreach ($ppfInstallations as $i => $installation) {
            if ($i >= 2) {
                break;
            }
            $status = $i === 0 ? 'unpaid' : 'paid';
            $price = 12_000_000;

            PpfInvoice::create([
                'branch_id'                     => $installation->branch_id,
                'customer_ppf_id'                => $installation->customer_ppf_id,
                'customer_ppf_installation_id'   => $installation->id,
                'invoice_number'                 => $this->num('INVPPF'),
                'invoice_date'                   => now()->subDays(3)->toDateString(),
                'payment_method'                 => 'transfer',
                'price'                          => $price,
                'discount'                       => 0,
                'downpayment'                    => $status === 'paid' ? $price : 0,
                'total_price'                    => $price,
                'remaining_payment'              => $status === 'paid' ? 0 : $price,
                'status'                         => $status,
                'commission_released_at'         => $status === 'paid' ? now() : null,
                'commission_released_by'         => $status === 'paid' ? $this->owner->id : null,
            ]);
        }
    }

    // ───────────────────────────── G: warranty & complaints ───────────────────

    private function seedWarrantyAndComplaints(array $carCustomers, array $buildingCustomers): void
    {
        $customer = $carCustomers[0];

        $claimSpecs = [
            ['status' => 'submitted', 'cause' => 'unknown', 'chargeable' => false],
            ['status' => 'inspected', 'cause' => 'user', 'chargeable' => true],
            ['status' => 'inspected', 'cause' => 'workmanship', 'chargeable' => false],
            ['status' => 'approved', 'cause' => 'user', 'chargeable' => true, 'withInvoice' => true],
            ['status' => 'closed', 'cause' => 'product', 'chargeable' => false],
        ];

        foreach ($claimSpecs as $spec) {
            $claim = WarrantyClaim::create([
                'claim_number'     => $this->num('CLM'),
                'claim_date'       => now()->subDays(5)->toDateString(),
                'warranty_type'    => 'car_film',
                'warrantable_type' => Customer::class,
                'warrantable_id'   => $customer->id,
                'branch_id'        => $customer->branch_id,
                'card_number'      => $customer->card_number,
                'cause'            => $spec['cause'],
                'status'           => $spec['status'],
                'is_chargeable'    => $spec['chargeable'],
                'quote_total'      => $spec['chargeable'] ? 350_000 : 0,
                'complaint'        => 'Kaca film mulai menggelembung di bagian pojok kanan.',
                'inspection_notes' => $spec['status'] !== 'submitted' ? 'Sudah diperiksa teknisi, sesuai temuan di lapangan.' : null,
            ]);

            WarrantyClaimItem::create([
                'warranty_claim_id' => $claim->id,
                'item_type'         => 'car_product',
                'item_label'        => 'Kaca Film Depan Kanan',
                'action'            => 'replace',
                'charge_amount'     => $spec['chargeable'] ? 350_000 : 0,
            ]);

            if (! empty($spec['withInvoice'])) {
                $claimInvoice = WarrantyClaimInvoice::create([
                    'warranty_claim_id' => $claim->id,
                    'invoice_number'    => $this->num('INV-CLM'),
                    'invoice_date'      => now(),
                    'payment_method'    => 'transfer',
                    'subtotal'          => 350_000,
                    'discount'          => 0,
                    'downpayment'       => 0,
                    'total_price'       => 350_000,
                    'remaining_payment' => 350_000,
                    'status'            => 'unpaid',
                ]);

                WarrantyClaimInvoiceItem::create([
                    'warranty_claim_invoice_id' => $claimInvoice->id,
                    'description'               => 'Penggantian Kaca Film Depan Kanan (klaim chargeable)',
                    'qty'                       => 1,
                    'unit_price'                => 350_000,
                    'total'                     => 350_000,
                ]);
            }
        }

        // Film complaints — car & building, various statuses
        $complaintSpecs = [
            ['type' => 'car', 'status' => 'open'],
            ['type' => 'car', 'status' => 'process'],
            ['type' => 'building', 'status' => 'completed'],
        ];

        foreach ($complaintSpecs as $spec) {
            $complaint = FilmComplaint::create([
                'complaint_number'     => $this->num('CMP'),
                'branch_id'            => $customer->branch_id,
                'customer_id'          => $spec['type'] === 'car' ? $customer->id : null,
                'customer_building_id' => $spec['type'] === 'building' ? ($buildingCustomers[0]->id ?? null) : null,
                'complaint_type'       => $spec['type'],
                'complaint_date'       => now()->subDays(2)->toDateString(),
                'status'               => $spec['status'],
                'reason'               => 'Film mengelupas di tepi kaca.',
                'notes'                => 'Dicatat untuk keperluan QA manual testing.',
                'created_by'           => $this->csUser->id,
            ]);

            if ($spec['type'] === 'car') {
                FilmComplaintItem::create([
                    'film_complaint_id' => $complaint->id,
                    'category_product_id' => 1,
                    'product_id'        => 1,
                    'part_id'           => 1,
                    'replacement_side'  => 'not_applicable',
                    'replacement_scope' => 'full',
                    'coverage_percent'  => 100,
                    'usage_source'      => 'manual',
                ]);
            } else {
                FilmComplaintItem::create([
                    'film_complaint_id' => $complaint->id,
                    'category_product_building_id' => 1,
                    'product_building_id'          => 1,
                    'area_meter'                    => 15.5,
                    'used_length_meter'             => 16,
                    'usage_source'                  => 'manual',
                ]);
            }
        }
    }

    // ───────────────────────────── H: commissions ─────────────────────────────

    /**
     * Commission for car_film is a flat-rate lookup table (config('commission')
     * fixed by install_type + panoramic + pairing), NOT a percentage of invoice
     * price — see App\Domain\Commission\CommissionCalculator. Earlier revisions
     * of this seeder hardcoded 150_000 for every role (teknisi AND marketing
     * alike), which doesn't correspond to any real config key. Use the actual
     * calculator so the demo numbers match what CommissionReleaseService::release()
     * would really produce for this customer (fullset, non-panoramic, standard
     * teknisi+kenek pair → 70k/30k; marketing category 1 fullset → 120k).
     */
    private function seedCommissions(array $carCustomers): void
    {
        $currentPeriod = CommissionPeriod::resolveForNow();
        $previousPeriod = CommissionPeriod::resolveForDate(now()->subMonth());

        $teknisi = Employee::where('job_position', 'Teknisi')->get();

        $customer = $carCustomers[0]->loadMissing(['tukang', 'kenek', 'marketing', 'customerProducts.part', 'invoice']);
        $calculator = app(\App\Domain\Commission\CommissionCalculator::class);
        $split = $calculator->resolveInstallerSplit($customer, $customer->invoice);
        $marketingAmount = $calculator->resolveMarketingAmount($customer);

        $commissionSpecs = array_filter([
            $customer->tukang_id ? [
                'employee' => $customer->tukang, 'role' => 'installer', 'amount' => $split->teknisiAmount,
                'status' => 'eligible', 'period' => $currentPeriod,
            ] : null,
            $customer->kenek_id ? [
                'employee' => $customer->kenek, 'role' => 'installer', 'amount' => $split->kenekAmount,
                'status' => 'paid', 'period' => $previousPeriod,
            ] : null,
            $customer->marketing_id ? [
                'employee' => $customer->marketing, 'role' => 'marketing', 'amount' => $marketingAmount,
                'status' => 'eligible', 'period' => $currentPeriod, 'category_product_id' => 1,
            ] : null,
            // A second marketing employee, same real rate, just to demo the is_active toggle.
            Employee::where('job_position', 'Marketing')->skip(1)->first() ? [
                'employee' => Employee::where('job_position', 'Marketing')->skip(1)->first(),
                'role' => 'marketing', 'amount' => $marketingAmount, 'category_product_id' => 1,
                'status' => 'eligible', 'period' => $currentPeriod, 'inactive' => true,
            ] : null,
        ]);

        $paidBatch = CommissionPaymentBatch::create([
            'batch_number'  => $this->num('BATCH'),
            'period_from'   => $previousPeriod->period_start,
            'period_to'     => $previousPeriod->period_end,
            'payment_date'  => now()->subDays(10),
            'status'        => 'paid',
            'total_amount'  => $split->kenekAmount,
            'created_by'    => $this->owner->id,
            'paid_by'       => $this->financeUser->id,
            'paid_at'       => now()->subDays(9),
        ]);

        foreach ($commissionSpecs as $spec) {
            if (! $spec['employee'] || $spec['amount'] <= 0) {
                continue;
            }

            $commission = Commission::create([
                'employees_id'          => $spec['employee']->id,
                'customer_id'           => $carCustomers[0]->id,
                'commission_period_id'  => $spec['period']->id,
                'amount'                => $spec['amount'],
                'role'                  => $spec['role'],
                'category_product_id'   => $spec['category_product_id'] ?? null,
                'status'                => $spec['status'],
                'is_active'             => empty($spec['inactive']),
                'eligible_at'           => now()->subDays(5),
                'paid_at'               => $spec['status'] === 'paid' ? now()->subDays(9) : null,
                'paid_by'               => $spec['status'] === 'paid' ? $this->financeUser->id : null,
                'commission_payment_batch_id' => $spec['status'] === 'paid' ? $paidBatch->id : null,
            ]);
            $commission->branch_id = $spec['employee']->branch_id;
            $commission->save();
        }

        // Pending withdrawal request — for manual testing of approve/reject. Not
        // tied to a real eligible row above (this teknisi is a different person,
        // purely to exercise the approval UI), but the amount still matches a
        // real possible fullset-installer payout rather than an arbitrary figure.
        if ($teknisi->get(2)) {
            CommissionPaymentBatch::create([
                'employee_id'     => $teknisi->get(2)->id,
                'batch_number'    => $this->num('BATCH'),
                'period_from'     => $currentPeriod->period_start,
                'period_to'       => $currentPeriod->period_end,
                'payment_date'    => now(),
                'status'          => 'draft',
                'total_amount'    => config('commission.fixed.fullset.teknisi'),
                'created_by'      => $this->teknisiUser->id,
                'requester_notes' => 'Butuh dicairkan untuk keperluan pribadi.',
            ]);
        }
    }

    // ───────────────────────────── I: HR ───────────────────────────────────────

    private function seedHr(): void
    {
        foreach ([$this->pusat, $this->jambi] as $branch) {
            Shift::firstOrCreate(
                ['branch_id' => $branch->id, 'name' => 'Shift Pagi'],
                ['start_time' => '08:00:00', 'end_time' => '17:00:00', 'overtime_after_minutes' => 30, 'is_active' => true]
            );
        }
        Shift::firstOrCreate(
            ['branch_id' => null, 'name' => 'Shift Malam (Global)'],
            ['start_time' => '20:00:00', 'end_time' => '05:00:00', 'overtime_after_minutes' => 30, 'is_active' => true]
        );

        $employees = Employee::whereIn('job_position', ['Teknisi', 'Kenek'])->take(6)->get();

        foreach ($employees as $employee) {
            foreach (range(1, 5) as $d) {
                Attendance::firstOrCreate(
                    ['employee_id' => $employee->id, 'attendance_date' => now()->subDays($d)->toDateString()],
                    [
                        'branch_id'   => $employee->branch_id,
                        'created_by'  => $this->adminJambi->id,
                        'status'      => fake()->randomElement(['present', 'present', 'present', 'sick', 'permission']),
                        'daily_salary' => 100_000,
                    ]
                );
            }
        }

        // Leave requests — pending/approved/rejected/cancelled
        $leaveSpecs = ['pending', 'approved', 'rejected', 'cancelled'];
        foreach ($leaveSpecs as $i => $status) {
            $employee = $employees->get($i % max($employees->count(), 1));
            if (! $employee) {
                continue;
            }

            LeaveRequest::create([
                'employee_id'    => $employee->id,
                'branch_id'      => $employee->branch_id,
                'requested_by'   => $this->teknisiUser->id,
                'approved_by'    => in_array($status, ['approved', 'rejected']) ? $this->mgrOps->id : null,
                'leave_type'     => fake()->randomElement(['annual', 'sick', 'permission']),
                'from_date'      => now()->addDays(5)->toDateString(),
                'to_date'        => now()->addDays(6)->toDateString(),
                'days'           => 2,
                'reason'         => 'Keperluan keluarga.',
                'status'         => $status,
                'approval_notes' => $status === 'rejected' ? 'Bertepatan dengan jadwal pekerjaan padat.' : null,
                'approved_at'    => in_array($status, ['approved', 'rejected']) ? now() : null,
            ]);
        }

        // Health balances
        foreach ($employees->take(3) as $employee) {
            EmployeeHealthBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'year' => now()->year],
                ['allocated_amount' => 2_000_000, 'used_amount' => 0]
            );
        }

        // Employee claims — pending (with real ApprovalRequest), approved, paid
        $claimEmployee = $employees->first();
        if ($claimEmployee) {
            $pendingClaim = EmployeeClaim::create([
                'employee_id' => $claimEmployee->id,
                'branch_id'   => $claimEmployee->branch_id,
                'type'        => 'pembelian',
                'amount'      => 150_000,
                'description' => 'Beli lakban & kain lap untuk instalasi.',
                'status'      => 'pending_approval',
            ]);
            $pendingClaim->submitForApproval('employee_claim', $this->teknisiUser->id);

            EmployeeClaim::create([
                'employee_id' => $claimEmployee->id,
                'branch_id'   => $claimEmployee->branch_id,
                'type'        => 'bensin',
                'amount'      => 50_000,
                'description' => 'Bensin kunjungan ke lokasi customer.',
                'status'      => 'approved',
            ]);
        }

        // Creative tasks — draft/in_progress/review/revision/done
        $designer = User::role('designer')->first();
        $strategist = User::role('strategi_pengembangan')->first();

        if ($designer && $strategist) {
            foreach (['draft', 'in_progress', 'review', 'revision', 'done'] as $status) {
                CreativeTask::create([
                    'assigned_by' => $strategist->id,
                    'assigned_to' => $designer->id,
                    'title'       => 'Desain Materi Promo — status ' . $status,
                    'brief'       => 'Poster A3 untuk promo cabang, warna sesuai brand guideline.',
                    'priority'    => fake()->randomElement(['low', 'normal', 'high']),
                    'due_date'    => now()->addDays(5)->toDateString(),
                    'status'      => $status,
                ]);
            }
        }

        // Payroll — current month, draft, with items per Pusat branch employee
        $payroll = Payroll::firstOrCreate(
            ['branch_id' => $this->pusat->id, 'month' => now()->format('Y-m')],
            ['working_days' => 26, 'status' => 'draft', 'generated_by' => $this->adminJambi->id]
        );

        foreach (Employee::where('branch_id', $this->pusat->id)->take(3)->get() as $employee) {
            PayrollItem::firstOrCreate(
                ['payroll_id' => $payroll->id, 'employee_id' => $employee->id],
                [
                    'employee_name'   => $employee->name,
                    'job_position'    => $employee->job_position,
                    'monthly_salary'  => $employee->monthly_salary ?? 3_000_000,
                    'present_days'    => 22,
                    'paid_days'       => 22,
                    'prorate_salary'  => $employee->monthly_salary ?? 3_000_000,
                    'transport_allowance' => 300_000,
                    'meal_allowance'      => 400_000,
                    'total_salary'        => ($employee->monthly_salary ?? 3_000_000) + 700_000,
                    'rounded_salary'      => ($employee->monthly_salary ?? 3_000_000) + 700_000,
                ]
            );
        }
    }

    // ───────────────────────────── J: finance ─────────────────────────────────

    private function seedFinance(): void
    {
        \App\Models\PettyCashTransaction::create([
            'branch_id'   => $this->jambi->id,
            'type'        => 'in',
            'date'        => now()->subDays(3)->toDateString(),
            'amount'      => 5_000_000,
            'description' => 'Setoran kas awal bulan.',
        ]);
        \App\Models\PettyCashTransaction::create([
            'branch_id'   => $this->jambi->id,
            'type'        => 'out',
            'date'        => now()->subDays(1)->toDateString(),
            'amount'      => 250_000,
            'description' => 'Pembelian bahan consumable instalasi.',
        ]);

        // Payable (hutang) — partial, with one approved payment
        $payable = Payable::create([
            'branch_id'        => $this->jambi->id,
            'vendor_name'      => 'PT Sumber Film Jaya',
            'reference_number' => $this->num('PO'),
            'description'      => 'Pembelian roll kaca film 1.52m x 30 roll.',
            'amount'           => 15_000_000,
            'paid_amount'       => 5_000_000,
            'due_date'          => now()->addDays(14)->toDateString(),
            'status'            => 'partial',
            'created_by'        => $this->purchasingUser->id,
        ]);
        DebtPayment::create([
            'payable_type'  => Payable::class,
            'payable_id'    => $payable->id,
            'amount'        => 5_000_000,
            'payment_date'  => now()->subDays(2)->toDateString(),
            'payment_method' => 'transfer',
            'recorded_by'   => $this->purchasingUser->id,
            'status'        => 'approved',
        ]);

        // Receivable (piutang) — unpaid, fully testable payment flow
        Receivable::create([
            'branch_id'        => $this->jambi->id,
            'customer_name'    => 'Rental Mobil Sejahtera',
            'reference_number' => $this->num('SO'),
            'description'      => 'Pemasangan kaca film 5 unit mobil rental, pembayaran termin.',
            'amount'           => 10_000_000,
            'paid_amount'       => 0,
            'due_date'          => now()->addDays(30)->toDateString(),
            'status'            => 'unpaid',
            'created_by'        => $this->financeUser->id,
        ]);

        // Bank account + reconciled/unreconciled entries
        $bank = BankAccount::firstOrCreate(
            ['branch_id' => $this->jambi->id, 'account_number' => '1234567890'],
            ['bank_name' => 'BCA', 'account_name' => 'Acegard Jambi', 'opening_balance' => 20_000_000, 'current_balance' => 23_500_000, 'is_active' => true]
        );

        BankStatementEntry::firstOrCreate(
            ['bank_account_id' => $bank->id, 'reference_number' => 'TRX-DEMO-001'],
            [
                'entry_date'   => now()->subDays(4)->toDateString(),
                'description'  => 'Transfer masuk pelunasan invoice.',
                'debit'        => 0,
                'credit'       => 4_000_000,
                'balance_after' => 24_000_000,
                'is_reconciled' => true,
                'reconciled_by' => $this->financeUser->id,
                'reconciled_at' => now()->subDays(3),
                'created_by'    => $this->financeUser->id,
            ]
        );
        BankStatementEntry::firstOrCreate(
            ['bank_account_id' => $bank->id, 'reference_number' => 'TRX-DEMO-002'],
            [
                'entry_date'   => now()->subDay()->toDateString(),
                'description'  => 'Pembayaran supplier belum dicocokkan.',
                'debit'        => 500_000,
                'credit'       => 0,
                'balance_after' => 23_500_000,
                'is_reconciled' => false,
                'created_by'    => $this->financeUser->id,
            ]
        );

        // Notification config — default per branch for warranty expiry
        NotificationConfig::firstOrCreate(
            ['branch_id' => $this->jambi->id, 'type' => 'warranty_expiry'],
            ['days_before' => 7, 'channels' => ['in_app', 'email'], 'is_active' => true]
        );
    }

    // ───────────────────────────── K: inventory & purchasing ──────────────────

    private function seedInventory(): void
    {
        $supportingGoods = [
            ['name' => 'Cairan Slip Kaca Film', 'category' => 'chemical', 'unit' => 'botol', 'stock' => 15, 'min' => 5],
            ['name' => 'Cutter Kaca Film', 'category' => 'cutter', 'unit' => 'pcs', 'stock' => 3, 'min' => 5],
            ['name' => 'Squeegee Hard', 'category' => 'squeegee', 'unit' => 'pcs', 'stock' => 10, 'min' => 2],
        ];

        foreach ($supportingGoods as $g) {
            $good = SupportingGood::firstOrCreate(
                ['branch_id' => $this->jambi->id, 'name' => $g['name']],
                ['category' => $g['category'], 'unit' => $g['unit'], 'current_stock' => $g['stock'], 'minimum_stock' => $g['min']]
            );

            SupportingGoodMutation::create([
                'supporting_good_id' => $good->id,
                'branch_id'          => $this->jambi->id,
                'type'               => 'in',
                'quantity'           => $g['stock'],
                'stock_before'       => 0,
                'stock_after'        => $g['stock'],
                'notes'              => 'Stok awal (demo QA).',
                'recorded_by'        => $this->purchasingUser->id,
            ]);
        }

        // Film stock pool + roll entries (for transfer testing between Jambi & Bengkulu)
        $pool = FilmStockPool::firstOrCreate(
            ['branch_id' => $this->jambi->id, 'category_product_id' => 1, 'product_id' => 1],
            [
                'film_type' => '4K ALPHA PRO', 'darkness_code' => 'AP50', 'usage_for' => 'car',
                'width_meter' => 1.5, 'total_received_length_meter' => 60, 'total_used_length_meter' => 0,
                'remaining_length_meter' => 60, 'remaining_area_meter' => 90,
            ]
        );

        foreach (range(1, 2) as $i) {
            FilmRollEntry::firstOrCreate(
                ['roll_code' => 'ROLL-DEMO-' . $this->jambi->id . '-' . $i],
                [
                    'film_stock_pool_id' => $pool->id,
                    'branch_id'          => $this->jambi->id,
                    'current_branch_id'  => $this->jambi->id,
                    'film_type'          => '4K ALPHA PRO',
                    'darkness_code'      => 'AP50',
                    'width_meter'        => 1.5,
                    'length_meter'       => 30,
                    'area_meter'         => 45,
                    'status'             => 'in_branch',
                    'received_date'      => now()->subDays(10)->toDateString(),
                ]
            );
        }

        // Purchase — draft (pending approval), confirmed+received
        $supplier = Supplier::first();

        $draftPurchase = Purchase::create([
            'branch_id'       => $this->jambi->id,
            'supplier_id'     => $supplier?->id,
            'user_id'         => $this->purchasingUser->id,
            'purchase_number' => $this->num('PO'),
            'purchase_date'   => now()->toDateString(),
            'total_amount'    => 3_000_000,
            'status'          => 'draft',
            'notes'           => 'Menunggu approval owner (demo QA).',
        ]);
        PurchaseItem::create([
            'purchase_id' => $draftPurchase->id,
            'item_name'   => 'Cairan Slip Kaca Film 1L',
            'qty'         => 10,
            'unit'        => 'botol',
            'unit_price'  => 300_000,
            'total_price' => 3_000_000,
        ]);
        $draftPurchase->submitForApproval('purchasing_transaction', $this->purchasingUser->id);

        $confirmedPurchase = Purchase::create([
            'branch_id'       => $this->jambi->id,
            'supplier_id'     => $supplier?->id,
            'user_id'         => $this->purchasingUser->id,
            'purchase_number' => $this->num('PO'),
            'purchase_date'   => now()->subDays(5)->toDateString(),
            'total_amount'    => 9_000_000,
            'status'          => 'confirmed',
            'notes'           => 'Sudah disetujui, siap diterima.',
        ]);
        $item = PurchaseItem::create([
            'purchase_id' => $confirmedPurchase->id,
            'item_name'   => 'Kaca Film Roll 1.52m',
            'qty'         => 30,
            'unit'        => 'roll',
            'unit_price'  => 300_000,
            'total_price' => 9_000_000,
        ]);

        $receipt = PurchaseReceipt::create([
            'purchase_id'    => $confirmedPurchase->id,
            'received_by'   => $this->purchasingUser->id,
            'receipt_number' => $this->num('GRN'),
            'receipt_date'   => now()->subDays(3)->toDateString(),
            'status'         => 'complete',
        ]);
        PurchaseReceiptItem::create([
            'receipt_id'       => $receipt->id,
            'purchase_item_id' => $item->id,
            'qty_received'     => 30,
        ]);
    }

    // ───────────────────────────── N: development ─────────────────────────────

    private function seedDevelopment(): void
    {
        $devUser = User::role('development')->first();
        if (! $devUser) {
            return;
        }

        DevelopmentReport::firstOrCreate(
            ['created_by' => $devUser->id, 'title' => 'Laporan Perkembangan Q1 2026'],
            [
                'content'      => 'Progres ekspansi cabang baru berjalan sesuai rencana, omzet PPF naik 15%.',
                'period_start' => now()->startOfYear()->toDateString(),
                'period_end'   => now()->startOfYear()->addMonths(2)->endOfMonth()->toDateString(),
            ]
        );

        $plan = DevelopmentAnnualPlan::firstOrCreate(
            ['year' => now()->year + 1],
            ['created_by' => $devUser->id, 'summary' => 'Fokus ekspansi PPF ke 3 kota baru dan digitalisasi layanan pelanggan.']
        );

        $initiativeSpecs = [
            ['title' => 'Buka cabang baru di Surabaya', 'status' => 'planned'],
            ['title' => 'Digitalisasi layanan klaim garansi', 'status' => 'in_progress'],
            ['title' => 'Pelatihan teknisi PPF batch 2026', 'status' => 'done'],
        ];

        foreach ($initiativeSpecs as $spec) {
            $plan->initiatives()->firstOrCreate(
                ['title' => $spec['title']],
                ['description' => fake()->sentence(12), 'target_date' => now()->addMonths(6)->toDateString(), 'status' => $spec['status']]
            );
        }
    }
}
