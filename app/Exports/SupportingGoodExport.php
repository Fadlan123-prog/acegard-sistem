<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupportingGoodExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(private array $filters = []) {}

    public function collection()
    {
        return DB::table('supporting_goods as g')
            ->leftJoin('branches as b', 'b.id', '=', 'g.branch_id')
            ->select([
                'g.id',
                'b.name as cabang',
                'g.name as nama',
                'g.category as kategori',
                'g.unit as satuan',
                DB::raw("FORMAT(g.current_stock, 2) as stok_saat_ini"),
                DB::raw("FORMAT(g.minimum_stock, 2) as stok_minimum"),
                DB::raw("CASE WHEN g.minimum_stock > 0 AND g.current_stock <= g.minimum_stock * 0.5 THEN 'Kritis'
                              WHEN g.minimum_stock > 0 AND g.current_stock <= g.minimum_stock THEN 'Rendah'
                              ELSE 'Aman' END as status_stok"),
                'g.description as keterangan',
                DB::raw("DATE_FORMAT(g.updated_at, '%Y-%m-%d') as tgl_update"),
            ])
            ->when(!empty($this->filters['branch_id']), fn($q) => $q->where('g.branch_id', $this->filters['branch_id']))
            ->when(!empty($this->filters['category']),  fn($q) => $q->where('g.category', $this->filters['category']))
            ->orderBy('b.name')
            ->orderBy('g.category')
            ->orderBy('g.name')
            ->get()
            ->map(fn($r) => [
                $r->id, $r->cabang, $r->nama, $r->kategori, $r->satuan,
                $r->stok_saat_ini, $r->stok_minimum, $r->status_stok,
                $r->keterangan ?? '-', $r->tgl_update,
            ]);
    }

    public function headings(): array
    {
        return ['ID', 'Cabang', 'Nama', 'Kategori', 'Satuan', 'Stok Saat Ini', 'Stok Minimum', 'Status', 'Keterangan', 'Tgl Update'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3E8FF']]]];
    }

    public function columnWidths(): array
    {
        return ['A'=>6,'B'=>18,'C'=>25,'D'=>14,'E'=>10,'F'=>14,'G'=>14,'H'=>12,'I'=>25,'J'=>13];
    }
}
