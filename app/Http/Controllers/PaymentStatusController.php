<?php

namespace App\Http\Controllers;

use App\Services\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Finance: siapa yang sudah lunas dan siapa yang belum — gabungan invoice mobil,
 * gedung, dan PPF pada cabang aktif. Hanya baca.
 */
class PaymentStatusController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:paid,partial,unpaid,outstanding'],
            'type'   => ['nullable', 'in:car,building,ppf'],
            'from'   => ['nullable', 'date'],
            'to'     => ['nullable', 'date', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $data['status'] ?? 'outstanding'; // default: yang belum lunas
        $branchId = $this->branch->idOrNull();

        $car = DB::table('invoices as i')
            ->join('customers as c', 'c.id', '=', 'i.customer_id')
            ->when($branchId, fn ($q) => $q->where('c.branch_id', $branchId))
            ->selectRaw("'car' as type, i.id, i.invoice_number, DATE(i.invoice_date) as invoice_date, c.name as customer_name,
                c.phone_number as phone, i.total_price, i.downpayment, i.remaining_payment, i.pelunasan_at,
                CASE WHEN i.pelunasan_at IS NOT NULL OR i.status = 'paid' THEN 'paid'
                     WHEN i.downpayment > 0 THEN 'partial' ELSE 'unpaid' END as pay_status");

        $building = DB::table('invoice_building as i')
            ->join('customer_building as c', 'c.id', '=', 'i.customer_building_id')
            ->when($branchId, fn ($q) => $q->where('i.branch_id', $branchId))
            ->selectRaw("'building' as type, i.id, i.invoice_number, DATE(i.invoice_date) as invoice_date, c.name as customer_name,
                c.phone_number as phone, i.total_price, i.down_payment as downpayment, i.remaining_payment, i.pelunasan_at,
                CASE WHEN i.pelunasan_at IS NOT NULL OR i.status = 'paid' THEN 'paid'
                     WHEN i.down_payment > 0 THEN 'partial' ELSE 'unpaid' END as pay_status");

        $ppf = DB::table('ppf_invoices as i')
            ->join('customer_ppf as c', 'c.id', '=', 'i.customer_ppf_id')
            ->where('i.status', '!=', 'void')
            ->when($branchId, fn ($q) => $q->where('i.branch_id', $branchId))
            ->selectRaw("'ppf' as type, i.id, i.invoice_number, DATE(i.invoice_date) as invoice_date, c.name as customer_name,
                c.phone as phone, i.total_price, i.downpayment, i.remaining_payment, NULL as pelunasan_at,
                CASE WHEN i.status = 'paid' THEN 'paid'
                     WHEN i.downpayment > 0 THEN 'partial' ELSE 'unpaid' END as pay_status");

        $union = $car->unionAll($building)->unionAll($ppf);

        $base = fn () => DB::query()->fromSub($union, 'x')
            ->when($data['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->when($data['from'] ?? null, fn ($q, $d) => $q->where('invoice_date', '>=', $d))
            ->when($data['to'] ?? null, fn ($q, $d) => $q->where('invoice_date', '<=', $d))
            ->when($data['search'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('customer_name', 'like', "%{$s}%")
                ->orWhere('invoice_number', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")));

        // Ringkasan mengikuti filter tipe/tanggal/pencarian, tidak mengikuti filter status.
        $summary = $base()
            ->selectRaw("pay_status, COUNT(*) as n, SUM(total_price) as total, SUM(remaining_payment) as remaining")
            ->groupBy('pay_status')->get()->keyBy('pay_status');

        $rows = $base()
            ->when($status === 'outstanding', fn ($q) => $q->where('pay_status', '!=', 'paid'))
            ->when(in_array($status, ['paid', 'partial', 'unpaid'], true), fn ($q) => $q->where('pay_status', $status))
            ->orderByRaw("CASE WHEN pay_status = 'paid' THEN 1 ELSE 0 END")
            ->orderBy('invoice_date')   // yang paling lama menunggak di atas
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        return view('finance.payment-status', [
            'title'   => 'Status Pembayaran Customer',
            'rows'    => $rows,
            'summary' => $summary,
            'filters' => array_merge(['status' => $status], array_diff_key($data, ['status' => 1])),
        ]);
    }
}
