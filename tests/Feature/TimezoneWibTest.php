<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Receivable;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Waktu aplikasi = WIB (Asia/Jakarta, UTC+7); jatuh tempo berjenis tanggal baru "terlambat" setelah harinya lewat. */
class TimezoneWibTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_application_and_database_use_wib(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', date_default_timezone_get());
        $this->assertSame('+07:00', config('database.connections.mysql.timezone'));
        $this->assertSame('+07:00', DB::selectOne('select @@session.time_zone as tz')->tz);
        $this->assertSame('+07:00', now()->format('P'));
    }

    public function test_is_before_today_is_false_on_the_due_day_itself_and_true_the_next_day_in_wib(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 23:30:00', 'Asia/Jakarta'));
        $due = Carbon::parse('2026-10-31');   // kolom date -> 00:00

        $this->assertFalse($due->copy()->isBeforeToday(), 'jatuh tempo hari ini belum terlambat, walau sudah malam');
        $this->assertTrue(Carbon::parse('2026-10-30')->isBeforeToday());
        $this->assertFalse(Carbon::parse('2026-11-01')->isBeforeToday());

        Carbon::setTestNow(Carbon::parse('2026-11-01 00:05:00', 'Asia/Jakarta'));
        $this->assertTrue($due->copy()->isBeforeToday(), 'lewat tengah malam WIB baru terlambat');
        // 17:05 UTC tanggal 31 = 00:05 WIB tanggal 1: harus mengikuti WIB, bukan UTC
        $this->assertSame('2026-11-01', now()->toDateString());
    }

    public function test_sales_order_scheduled_today_is_not_flagged_late(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 04:51:00', 'Asia/Jakarta'));
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $so = SalesOrder::create([
            'branch_id' => $branch->id, 'so_number' => 'SO-T-1', 'date' => '2026-10-31', 'scheduled_date' => '2026-10-31',
            'prospect_name' => 'Budi', 'category' => 'car_film', 'status' => 'confirmed', 'subtotal' => 0, 'discount_amount' => 0, 'total_amount' => 0,
        ]);

        $this->actingAs($admin)->get(route('sales-orders.show', $so))->assertOk()->assertDontSee('Terlambat');

        Carbon::setTestNow(Carbon::parse('2026-11-01 08:00:00', 'Asia/Jakarta'));
        $this->actingAs($admin)->get(route('sales-orders.show', $so))->assertOk()->assertSee('Terlambat');
    }

    public function test_receivable_due_today_is_not_overdue_until_tomorrow(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 10:00:00', 'Asia/Jakarta'));
        $r = new Receivable(['due_date' => '2026-10-31', 'status' => 'unpaid']);
        $this->assertFalse($r->isOverdue());

        Carbon::setTestNow(Carbon::parse('2026-11-01 00:01:00', 'Asia/Jakarta'));
        $this->assertTrue($r->isOverdue());
    }
}
