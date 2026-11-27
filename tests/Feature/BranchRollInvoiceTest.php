<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchRollInvoice;
use App\Models\BranchRollPrice;
use App\Models\FilmRollEntry;
use App\Models\FilmStockPool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BranchRollInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private Branch $seller;
    private Branch $buyer;
    private FilmStockPool $pool;
    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = Branch::factory()->create();
        $this->buyer = Branch::factory()->create(['ownership_type' => 'franchise']);

        $this->categoryId = DB::table('category_products')->insertGetId(['name' => 'ARMOR', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        $productId = DB::table('products')->insertGetId(['name' => 'A40', 'category_id' => $this->categoryId, 'created_at' => now(), 'updated_at' => now()]);

        $this->pool = FilmStockPool::create([
            'branch_id' => $this->seller->id,
            'category_product_id' => $this->categoryId,
            'product_id' => $productId,
            'film_type' => 'ARMOR',
            'darkness_code' => 'A40',
            'usage_for' => 'car',
            'width_meter' => 1.5,
            'total_received_length_meter' => 90,
            'total_used_length_meter' => 0,
            'remaining_length_meter' => 90,
            'remaining_area_meter' => 135,
        ]);

        foreach (['R-001', 'R-002', 'R-003'] as $i => $code) {
            FilmRollEntry::create([
                'film_stock_pool_id' => $this->pool->id, 'branch_id' => $this->seller->id, 'current_branch_id' => $this->seller->id,
                'roll_code' => $code, 'film_type' => 'ARMOR', 'darkness_code' => 'A40',
                'width_meter' => 1.5, 'length_meter' => 30, 'area_meter' => 45, 'status' => 'in_warehouse',
                'received_date' => now()->subDays(3 - $i)->toDateString(),
            ]);
        }

        BranchRollPrice::create(['ownership_type' => 'franchise', 'film_key' => 'ARMOR', 'price' => 2500000, 'unit' => 'roll']);
    }

    private function as(string $role, Branch $branch)
    {
        $user = $this->userWithRole($role, $branch);

        // BranchContext adalah singleton yang menyimpan cabang aktif; reset supaya tiap "login" dalam satu tes memakai cabangnya sendiri.
        app()->forgetInstance(\App\Services\BranchContext::class);

        return $this->actingAs($user)->withSession(['active_branch_id' => $branch->id]);
    }

    private function payload(int $qty = 2): array
    {
        return [
            'to_branch_id' => $this->buyer->id,
            'invoice_date' => now()->toDateString(),
            'items' => [['film_stock_pool_id' => $this->pool->id, 'qty' => $qty]],
        ];
    }

    private function createInvoice(int $qty = 2): BranchRollInvoice
    {
        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.store'), $this->payload($qty))->assertSessionHasNoErrors();

        return BranchRollInvoice::firstOrFail();
    }

    public function test_membuat_invoice_mengurangi_stok_penjual_dan_menghitung_total_dari_harga_server(): void
    {
        $invoice = $this->createInvoice(2);

        $this->assertSame('sent', $invoice->delivery_status);
        $this->assertSame('unpaid', $invoice->payment_status);
        $this->assertEquals(5000000, (float) $invoice->total_amount);
        $this->assertSame($this->seller->id, $invoice->from_branch_id);

        $this->assertSame(2, FilmRollEntry::where('status', 'in_transit')->count());
        // FIFO: dua roll tertua keluar
        $this->assertSame(['R-001', 'R-002'], FilmRollEntry::where('status', 'in_transit')->orderBy('roll_code')->pluck('roll_code')->all());

        $this->pool->refresh();
        $this->assertEquals(30, (float) $this->pool->remaining_length_meter);
        $this->assertEquals(30, (float) $this->pool->total_received_length_meter);
    }

    public function test_stok_kurang_atau_harga_belum_diatur_menolak_invoice(): void
    {
        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.store'), $this->payload(5))->assertSessionHasErrors('items');
        $this->assertSame(0, BranchRollInvoice::count());
        $this->assertSame(0, FilmRollEntry::where('status', 'in_transit')->count());

        BranchRollPrice::query()->delete();
        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.store'), $this->payload(1))->assertSessionHasErrors('items');
        $this->assertSame(0, BranchRollInvoice::count());
    }

    public function test_cabang_penjual_dan_pembeli_tidak_boleh_sama(): void
    {
        $payload = $this->payload(1);
        $payload['to_branch_id'] = $this->seller->id;

        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.store'), $payload)->assertSessionHasErrors('to_branch_id');
        $this->assertSame(0, BranchRollInvoice::count());
    }

    public function test_pembeli_menerima_roll_dan_roll_pindah_ke_stok_pembeli(): void
    {
        $invoice = $this->createInvoice(2);

        // Penjual tidak boleh menerima roll miliknya sendiri
        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.receive', $invoice))->assertForbidden();

        $this->as('admin', $this->buyer)->post(route('branch-roll-invoices.receive', $invoice))->assertSessionHasNoErrors();

        $invoice->refresh();
        $this->assertSame('received', $invoice->delivery_status);

        $entries = FilmRollEntry::whereIn('roll_code', ['R-001', 'R-002'])->get();
        foreach ($entries as $e) {
            $this->assertSame($this->buyer->id, $e->branch_id);
            $this->assertSame($this->buyer->id, $e->current_branch_id);
            $this->assertSame('in_warehouse', $e->status);
        }

        $buyerPool = FilmStockPool::where('branch_id', $this->buyer->id)->firstOrFail();
        $this->assertSame('ARMOR', $buyerPool->film_type);
        $this->assertEquals(60, (float) $buyerPool->remaining_length_meter);
        $this->assertEquals(60, (float) $buyerPool->total_received_length_meter);

        // Diterima dua kali tidak menggandakan stok
        $this->as('admin', $this->buyer)->post(route('branch-roll-invoices.receive', $invoice))->assertSessionHasErrors('invoice');
        $this->assertEquals(60, (float) $buyerPool->fresh()->remaining_length_meter);
    }

    public function test_penjual_bisa_membatalkan_sebelum_diterima_dan_stok_kembali(): void
    {
        $invoice = $this->createInvoice(2);

        $this->as('admin', $this->buyer)->post(route('branch-roll-invoices.cancel', $invoice))->assertForbidden();

        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.cancel', $invoice))->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $invoice->fresh()->delivery_status);
        $this->assertSame(3, FilmRollEntry::where('status', 'in_warehouse')->where('branch_id', $this->seller->id)->count());
        $this->assertEquals(90, (float) $this->pool->fresh()->remaining_length_meter);
        $this->assertEquals(90, (float) $this->pool->fresh()->total_received_length_meter);
    }

    public function test_invoice_yang_sudah_diterima_atau_lunas_tidak_bisa_dibatalkan(): void
    {
        $invoice = $this->createInvoice(1);
        $this->as('admin', $this->seller)->patch(route('branch-roll-invoices.mark-paid', $invoice))->assertSessionHasNoErrors();
        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.cancel', $invoice))->assertSessionHasErrors('invoice');
        $this->assertSame('sent', $invoice->fresh()->delivery_status);

        $this->as('admin', $this->buyer)->post(route('branch-roll-invoices.receive', $invoice));
        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.cancel', $invoice))->assertSessionHasErrors('invoice');
        $this->assertSame('received', $invoice->fresh()->delivery_status);
    }

    public function test_hanya_penjual_yang_menandai_lunas(): void
    {
        $invoice = $this->createInvoice(1);

        $this->as('admin', $this->buyer)->patch(route('branch-roll-invoices.mark-paid', $invoice))->assertForbidden();
        $this->assertSame('unpaid', $invoice->fresh()->payment_status);

        $this->as('admin', $this->seller)->patch(route('branch-roll-invoices.mark-paid', $invoice))->assertSessionHasNoErrors();
        $this->assertSame('paid', $invoice->fresh()->payment_status);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_cabang_lain_tidak_bisa_melihat_invoice_tetapi_owner_bisa(): void
    {
        $invoice = $this->createInvoice(1);
        $other = Branch::factory()->create();

        $this->as('admin', $other)->get(route('branch-roll-invoices.show', $invoice))->assertForbidden();
        $this->as('admin', $other)->get(route('branch-roll-invoices.pdf', $invoice))->assertForbidden();
        $this->as('admin', $other)->get(route('branch-roll-invoices.index'))->assertOk()->assertDontSee($invoice->invoice_number);

        $this->as('admin', $this->buyer)->get(route('branch-roll-invoices.show', $invoice))->assertOk()->assertSee($invoice->invoice_number);
        $this->as('owner', $other)->get(route('branch-roll-invoices.show', $invoice))->assertOk();
        $this->as('owner', $other)->get(route('branch-roll-invoices.index'))->assertOk()->assertSee($invoice->invoice_number);
    }

    public function test_pdf_invoice_dapat_dicetak_oleh_pihak_terkait(): void
    {
        $invoice = $this->createInvoice(1);

        $res = $this->as('admin', $this->seller)->get(route('branch-roll-invoices.pdf', $invoice))->assertOk();
        $this->assertStringContainsString('application/pdf', $res->headers->get('content-type'));
    }

    public function test_pilihan_stok_hanya_milik_cabang_sendiri(): void
    {
        $this->as('admin', $this->seller)->getJson(route('branch-roll-invoices.pools', ['to_branch_id' => $this->buyer->id]))->assertOk()->assertJsonCount(1)->assertJsonPath('0.available', 3)->assertJsonPath('0.price', 2500000);
        $this->as('admin', $this->buyer)->getJson(route('branch-roll-invoices.pools', ['branch_id' => $this->seller->id]))->assertForbidden();
        $this->as('admin', $this->buyer)->getJson(route('branch-roll-invoices.pools'))->assertOk()->assertJsonCount(0);
    }

    public function test_form_dan_halaman_daftar_tampil(): void
    {
        $this->as('admin', $this->seller)->get(route('branch-roll-invoices.index'))->assertOk();
        $this->as('admin', $this->seller)->get(route('branch-roll-invoices.create'))->assertOk()->assertSee('Cabang Pembeli');
    }

    public function test_harga_roll_hanya_bisa_diatur_owner_superadmin_finance(): void
    {
        $this->as('admin', $this->seller)->get(route('branch-roll-prices.index'))->assertForbidden();
        $this->as('admin', $this->seller)->put(route('branch-roll-prices.update'), ['prices' => ['franchise' => ['ARMOR' => 1]]])->assertForbidden();

        $this->as('owner', $this->seller)->get(route('branch-roll-prices.index'))->assertOk()->assertSee('ARMOR');
        $this->as('finance', $this->seller)->put(route('branch-roll-prices.update'), ['prices' => ['franchise' => ['ARMOR' => 3100000]]])->assertSessionHasNoErrors();
        $this->assertEquals(3100000, (float) BranchRollPrice::where('film_key', 'ARMOR')->where('ownership_type', 'franchise')->value('price'));

        // Harga dikosongkan = tidak bisa dijual lagi
        $this->as('owner', $this->seller)->put(route('branch-roll-prices.update'), ['prices' => ['franchise' => ['ARMOR' => '']]]);
        $this->assertSame(0, BranchRollPrice::count());
    }

    public function test_harga_invoice_lama_tidak_berubah_saat_harga_diganti(): void
    {
        $invoice = $this->createInvoice(1);

        $this->as('owner', $this->seller)->put(route('branch-roll-prices.update'), ['prices' => ['franchise' => ['ARMOR' => 9000000]]]);

        $this->assertEquals(2500000, (float) $invoice->fresh()->total_amount);
    }

    public function test_harga_mengikuti_kategori_kepemilikan_cabang_pembeli(): void
    {
        foreach (['non_franchise_own' => 2000000, 'non_franchise_joint' => 2250000] as $type => $price) {
            BranchRollPrice::create(['ownership_type' => $type, 'film_key' => 'ARMOR', 'price' => $price, 'unit' => 'roll']);
        }

        $expected = ['franchise' => 2500000, 'non_franchise_own' => 2000000, 'non_franchise_joint' => 2250000];

        foreach ($expected as $type => $price) {
            BranchRollInvoice::query()->delete();
            FilmRollEntry::query()->update(['status' => 'in_warehouse']);
            $this->buyer->update(['ownership_type' => $type]);

            $invoice = $this->createInvoice(1);

            $this->assertEquals($price, (float) $invoice->total_amount, $type);
            $this->assertSame($type, $invoice->buyer_ownership_type);
        }
    }

    public function test_pembeli_tanpa_kategori_kepemilikan_ditolak(): void
    {
        $this->buyer->update(['ownership_type' => null]);

        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.store'), $this->payload(1))->assertSessionHasErrors('to_branch_id');
        $this->assertSame(0, BranchRollInvoice::count());
        $this->assertSame(0, FilmRollEntry::where('status', 'in_transit')->count());
    }

    public function test_harga_kategori_yang_belum_diatur_menolak_walau_kategori_lain_ada(): void
    {
        $this->buyer->update(['ownership_type' => 'non_franchise_joint']); // hanya franchise yang punya harga

        $this->as('admin', $this->seller)->post(route('branch-roll-invoices.store'), $this->payload(1))->assertSessionHasErrors('items');
        $this->assertSame(0, BranchRollInvoice::count());
    }

    public function test_endpoint_pools_menampilkan_harga_sesuai_pembeli(): void
    {
        BranchRollPrice::create(['ownership_type' => 'non_franchise_own', 'film_key' => 'ARMOR', 'price' => 2000000, 'unit' => 'roll']);
        $own = Branch::factory()->create(['ownership_type' => 'non_franchise_own']);

        $this->as('admin', $this->seller)->getJson(route('branch-roll-invoices.pools', ['to_branch_id' => $this->buyer->id]))
            ->assertOk()->assertJsonPath('0.price', 2500000);
        $this->as('admin', $this->seller)->getJson(route('branch-roll-invoices.pools', ['to_branch_id' => $own->id]))
            ->assertOk()->assertJsonPath('0.price', 2000000);
    }

    public function test_satu_harga_per_nama_film_berlaku_untuk_roll_mobil_dan_gedung(): void
    {
        $buildingCat = DB::table('category_product_building')->insertGetId(['name' => 'Armor', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        $buildingPool = FilmStockPool::create([
            'branch_id' => $this->seller->id, 'category_product_building_id' => $buildingCat,
            'film_type' => 'ARMOR', 'darkness_code' => 'B40', 'usage_for' => 'building', 'width_meter' => 1.5,
            'total_received_length_meter' => 30, 'total_used_length_meter' => 0, 'remaining_length_meter' => 30, 'remaining_area_meter' => 45,
        ]);
        FilmRollEntry::create([
            'film_stock_pool_id' => $buildingPool->id, 'branch_id' => $this->seller->id, 'current_branch_id' => $this->seller->id,
            'roll_code' => 'B-001', 'film_type' => 'ARMOR', 'darkness_code' => 'B40',
            'width_meter' => 1.5, 'length_meter' => 30, 'area_meter' => 45, 'status' => 'in_warehouse', 'received_date' => now()->toDateString(),
        ]);

        $service = app(\App\Services\BranchRollSaleService::class);

        $this->assertSame(2500000.0, (float) $service->priceFor($this->pool, 'franchise')->price);
        $this->assertSame(2500000.0, (float) $service->priceFor($buildingPool, 'franchise')->price);
    }

    public function test_halaman_harga_satu_daftar_dan_menandai_film_hanya_gedung(): void
    {
        DB::table('category_product_building')->insert(['name' => 'FLEX ONE WAY', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);

        $this->as('owner', $this->seller)->get(route('branch-roll-prices.index'))
            ->assertOk()->assertSee('ARMOR')->assertSee('FLEX ONE WAY')->assertSee('Hanya gedung')
            ->assertSee('Non-Franchise (Saham Gabungan)')->assertDontSee('Film Mobil');

        $this->as('owner', $this->seller)->put(route('branch-roll-prices.update'), ['prices' => ['non_franchise_joint' => ['FLEX ONE WAY' => 6000000, 'TIDAK ADA' => 1]]]);
        $this->assertEquals(6000000, (float) BranchRollPrice::where('film_key', 'FLEX ONE WAY')->where('ownership_type', 'non_franchise_joint')->value('price'));
        $this->assertSame(0, BranchRollPrice::where('film_key', 'TIDAK ADA')->count());
    }
}
