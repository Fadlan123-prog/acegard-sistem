<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HutangExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        $q = DB::table('payables as p')
            ->leftJoin('branches as b', 'b.id', '=', 'p.branch_id')
            ->select([
                'p.id',
                'b.name as cabang',
                'p.vendor_name',
                'p.reference_number',
                'p.description',
                DB::raw("FORMAT(p.amount, 0) as total"),
                DB::raw("FORMAT(p.paid_amount, 0) as dibayar"),
                DB::raw("FORMAT(p.amount - p.paid_amount, 0) as sisa"),
                'p.due_date',
                DB::raw("CASE p.status WHEN 'paid' THEN 'Lunas' WHEN 'partial' THEN 'Sebagian' ELSE 'Belum Bayar' END as status"),
                DB::raw("IF(p.status != 'paid' AND p.due_date IS NOT NULL AND p.due_date < CURDATE(), 'Ya', 'Tidak') as terlambat"),
                DB::raw("DATE_FORMAT(p.created_at, '%Y-%m-%d') as tgl_input"),
            ])
            ->when(!empty($this->filters['branch_id']), fn($q) => $q->where('p.branch_id', $this->filters['branch_id']))
            ->when(!empty($this->filters['status']),    fn($q) => $q->where('p.status', $this->filters['status']))
            ->orderByRaw("FIELD(p.status,'unpaid','partial','paid')")
            ->orderBy('p.due_date');

        return $q->get()->map(fn($r) => [
            $r->id, $r->cabang, $r->vendor_name, $r->reference_number ?? '-',
            $r->description, $r->total, $r->dibayar, $r->sisa,
            $r->due_date ?? '-', $r->status, $r->terlambat, $r->tgl_input,
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Cabang', 'Vendor', 'No. Ref', 'Deskripsi', 'Total (Rp)', 'Dibayar (Rp)', 'Sisa (Rp)', 'Jatuh Tempo', 'Status', 'Terlambat', 'Tgl Input'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']]]];
    }

    public function columnWidths(): array
    {
        return ['A'=>6,'B'=>18,'C'=>22,'D'=>15,'E'=>30,'F'=>16,'G'=>16,'H'=>16,'I'=>14,'J'=>14,'K'=>12,'L'=>13];
    }
}
