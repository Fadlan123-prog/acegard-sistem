<?php

namespace App\Services;

use App\Models\Payable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Total pengeluaran (basis kas) untuk halaman Analytics, per kategori biaya. Tidak ada hitungan ganda antar kategori:
 *  - Petty cash: transaksi 'out' dan 'purchase' (termasuk klaim karyawan yang dicairkan finance).
 *  - Gaji: payroll berstatus 'paid' pada bulan dalam rentang — jumlah gaji bersih (rounded_salary).
 *  - Komisi: komisi berstatus 'paid' menurut tanggal DIBAYAR (paid_at), bukan tanggal dibuat.
 *  - Hutang: pembayaran hutang ke vendor yang sudah disetujui, menurut tanggal pembayaran.
 * Pembelian PO (modul Purchasing) adalah komitmen, bukan kas keluar, dan bisa saja juga dicatat sebagai hutang: ditampilkan
 * terpisah (memo) dan TIDAK masuk total kas agar tidak terhitung dua kali.
 */
class ExpenseSummaryService
{
    /**
     * @return array{rows:array<int,array{key:string,label:string,amount:int,hint:string}>,total:int,po_commitment:int,total_with_po:int}
     */
    public function summarize(Carbon $start, Carbon $end, ?int $branchId): array
    {
        $petty = DB::table('petty_cash_transactions')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $pettyOut      = (int) (clone $petty)->where('type', 'out')->sum('amount');
        $pettyPurchase = (int) (clone $petty)->where('type', 'purchase')->sum('amount');

        $salary = (int) DB::table('payroll_items as pi')
            ->join('payrolls as p', 'p.id', '=', 'pi.payroll_id')
            ->where('p.status', 'paid')
            ->whereBetween('p.month', [$start->format('Y-m'), $end->format('Y-m')])
            ->when($branchId, fn ($q) => $q->where('p.branch_id', $branchId))
            ->sum('pi.rounded_salary');

        $commission = (int) DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->where('c.status', 'paid')
            ->whereBetween('c.paid_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->whereRaw('COALESCE(c.branch_id, e.branch_id) = ?', [$branchId]))
            ->sum('c.amount');

        $debt = (int) DB::table('debt_payments as dp')
            ->join('payables as pa', 'pa.id', '=', 'dp.payable_id')
            ->where('dp.payable_type', Payable::class)
            ->where('dp.status', 'approved')
            ->whereBetween('dp.payment_date', [$start->toDateString(), $end->toDateString()])
            ->when($branchId, fn ($q) => $q->where('pa.branch_id', $branchId))
            ->sum('dp.amount');

        $po = (int) DB::table('purchases')
            ->whereNull('deleted_at')
            ->whereIn('status', ['confirmed', 'sent', 'partial_received', 'received'])
            ->whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $rows = [
            ['key' => 'petty_out',      'label' => 'Petty cash — pengeluaran', 'amount' => $pettyOut,      'hint' => 'Transaksi keluar petty cash, termasuk klaim karyawan yang dicairkan'],
            ['key' => 'petty_purchase', 'label' => 'Petty cash — pembelian',   'amount' => $pettyPurchase, 'hint' => 'Klaim pembelian yang dicatat di petty cash'],
            ['key' => 'salary',         'label' => 'Gaji karyawan',            'amount' => $salary,        'hint' => 'Payroll berstatus Paid pada bulan dalam rentang (gaji bersih)'],
            ['key' => 'commission',     'label' => 'Komisi dibayarkan',        'amount' => $commission,    'hint' => 'Komisi berstatus Paid menurut tanggal dibayar'],
            ['key' => 'debt',           'label' => 'Pembayaran hutang vendor', 'amount' => $debt,          'hint' => 'Pembayaran hutang yang sudah disetujui'],
        ];

        $total = array_sum(array_column($rows, 'amount'));

        return [
            'rows'          => $rows,
            'total'         => $total,
            'po_commitment' => $po,
            'total_with_po' => $total + $po,
        ];
    }
}
