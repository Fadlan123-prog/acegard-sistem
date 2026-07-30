<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankStatementExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private int $bankAccountId,
        private array $filters = [],
        private string $accountName = ''
    ) {}

    public function collection()
    {
        return DB::table('bank_statement_entries as e')
            ->leftJoin('users as u', 'u.id', '=', 'e.created_by')
            ->leftJoin('users as r', 'r.id', '=', 'e.reconciled_by')
            ->select([
                DB::raw("DATE_FORMAT(e.entry_date, '%Y-%m-%d') as tanggal"),
                'e.description as keterangan',
                'e.reference_number as referensi',
                DB::raw("FORMAT(e.credit, 0) as masuk"),
                DB::raw("FORMAT(e.debit, 0) as keluar"),
                DB::raw("IF(e.balance_after IS NULL, '-', FORMAT(e.balance_after, 0)) as saldo"),
                DB::raw("IF(e.is_reconciled, 'Ya', 'Tidak') as rekonsiliasi"),
                DB::raw("IF(e.reconciled_at IS NULL, '-', DATE_FORMAT(e.reconciled_at, '%Y-%m-%d')) as tgl_rekonsiliasi"),
                'u.name as dicatat_oleh',
            ])
            ->where('e.bank_account_id', $this->bankAccountId)
            ->when(!empty($this->filters['month']),
                fn($q) => $q->whereRaw("DATE_FORMAT(e.entry_date,'%Y-%m') = ?", [$this->filters['month']]))
            ->when(isset($this->filters['status']) && $this->filters['status'] === 'reconciled',
                fn($q) => $q->where('e.is_reconciled', true))
            ->when(isset($this->filters['status']) && $this->filters['status'] === 'unreconciled',
                fn($q) => $q->where('e.is_reconciled', false))
            ->orderBy('e.entry_date')
            ->orderBy('e.id')
            ->get()
            ->map(fn($r) => [
                $r->tanggal, $r->keterangan, $r->referensi ?? '-',
                $r->masuk, $r->keluar, $r->saldo,
                $r->rekonsiliasi, $r->tgl_rekonsiliasi, $r->dicatat_oleh,
            ]);
    }

    public function headings(): array
    {
        return ['Tanggal', 'Keterangan', 'No. Ref', 'Masuk (Rp)', 'Keluar (Rp)', 'Saldo (Rp)', 'Rekonsiliasi', 'Tgl Rekonsiliasi', 'Dicatat Oleh'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E0E7FF']]]];
    }

    public function columnWidths(): array
    {
        return ['A'=>13,'B'=>35,'C'=>15,'D'=>16,'E'=>16,'F'=>16,'G'=>14,'H'=>18,'I'=>20];
    }

    public function title(): string
    {
        return mb_substr($this->accountName ?: 'Mutasi Bank', 0, 31);
    }
}
