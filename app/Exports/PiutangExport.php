<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PiutangExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        $q = DB::table('receivables as r')
            ->leftJoin('branches as b', 'b.id', '=', 'r.branch_id')
            ->select([
                'r.id',
                'b.name as cabang',
                'r.customer_name',
                'r.reference_number',
                'r.description',
                DB::raw("FORMAT(r.amount, 0) as total"),
                DB::raw("FORMAT(r.paid_amount, 0) as diterima"),
                DB::raw("FORMAT(r.amount - r.paid_amount, 0) as sisa"),
                'r.due_date',
                DB::raw("CASE r.status WHEN 'paid' THEN 'Lunas' WHEN 'partial' THEN 'Sebagian' ELSE 'Belum Bayar' END as status"),
                DB::raw("IF(r.status != 'paid' AND r.due_date IS NOT NULL AND r.due_date < CURDATE(), 'Ya', 'Tidak') as terlambat"),
                DB::raw("DATE_FORMAT(r.created_at, '%Y-%m-%d') as tgl_input"),
            ])
            ->when(!empty($this->filters['branch_id']), fn($q) => $q->where('r.branch_id', $this->filters['branch_id']))
            ->when(!empty($this->filters['status']),    fn($q) => $q->where('r.status', $this->filters['status']))
            ->orderByRaw("FIELD(r.status,'unpaid','partial','paid')")
            ->orderBy('r.due_date');

        return $q->get()->map(fn($r) => [
            $r->id, $r->cabang, $r->customer_name, $r->reference_number ?? '-',
            $r->description, $r->total, $r->diterima, $r->sisa,
            $r->due_date ?? '-', $r->status, $r->terlambat, $r->tgl_input,
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Cabang', 'Pelanggan', 'No. Ref', 'Deskripsi', 'Total (Rp)', 'Diterima (Rp)', 'Sisa (Rp)', 'Jatuh Tempo', 'Status', 'Terlambat', 'Tgl Input'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DCFCE7']]]];
    }

    public function columnWidths(): array
    {
        return ['A'=>6,'B'=>18,'C'=>22,'D'=>15,'E'=>30,'F'=>16,'G'=>16,'H'=>16,'I'=>14,'J'=>14,'K'=>12,'L'=>13];
    }
}
