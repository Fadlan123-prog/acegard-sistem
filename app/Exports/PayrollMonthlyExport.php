<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PayrollMonthlyExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(
        private \Illuminate\Support\Collection $report,
        private string  $month,
        private int     $workingDays,
        private string  $branchName,
    ) {}

    public function title(): string
    {
        return 'Laporan Gaji';
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Karyawan',
            'Posisi',
            'Masuk',
            'Libur',
            'Jatah Libur',
            'Libur Dibayar',
            'Libur Lebih',
            'Sakit',
            'Izin',
            'Alpha',
            'Gaji Bulanan (Rp)',
            'Prorate/Hari (Rp)',
            'Hari Dipotong',
            'Potongan (Rp)',
            'Sisa Libur (hr)',
            'Hari Dibayar',
            'Tdk Dibayar (hr)',
            'Sebelum Dibulatkan (Rp)',
            'Gaji Bersih Bulat (Rp)',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->report as $i => $row) {
            $rows[] = [
                $i + 1,
                $row['employee']->name,
                $row['employee']->job_position ?? '-',
                $row['present'],
                $row['off'],
                $row['off_quota'],
                $row['paid_off'],
                $row['excess_off'],
                $row['sick'],
                $row['permission'],
                $row['absent'],
                $row['monthly_salary'],
                round($row['daily_prorate']),
                $row['deduction_days'],
                round($row['deduction_amount']),
                $row['unused_off'],
                $row['present'],
                $row['deduction_days'],
                $row['salary_raw'],
                $row['salary'],
            ];
        }

        // Total row
        $rows[] = [
            '',
            'TOTAL',
            '',
            $this->report->sum('present'),
            $this->report->sum('off'),
            '',
            $this->report->sum('paid_off'),
            $this->report->sum('excess_off'),
            $this->report->sum('sick'),
            $this->report->sum('permission'),
            $this->report->sum('absent'),
            $this->report->sum('monthly_salary'),
            '',
            $this->report->sum('deduction_days'),
            round($this->report->sum('deduction_amount')),
            $this->report->sum('unused_off'),
            $this->report->sum('present'),
            $this->report->sum('deduction_days'),
            $this->report->sum('salary_raw'),
            $this->report->sum('salary'),
        ];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 28,
            'C' => 18,
            'D' => 8,
            'E' => 8,
            'F' => 12,
            'G' => 14,
            'H' => 12,
            'I' => 8,
            'J' => 8,
            'K' => 8,
            'L' => 20,
            'M' => 18,
            'N' => 14,
            'O' => 16,
            'P' => 20,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row (row 3, because we'll insert 2 title rows via AfterSheet)
        return [
            3 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $lastRow    = $this->report->count() + 3; // 2 title rows + 1 header
                $totalRow   = $lastRow + 1;
                $lastCol    = 'P';

                // ── Insert 2 title rows at top ──
                $sheet->insertNewRowBefore(1, 2);

                $sheet->setCellValue('A1', 'LAPORAN GAJI BULANAN');
                $sheet->setCellValue('A2', 'Periode: ' . \Carbon\Carbon::parse($this->month . '-01')->isoFormat('MMMM Y') . '  |  Cabang: ' . $this->branchName . '  |  Hari Kerja: ' . $this->workingDays . ' hari');

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // ── Header row (row 3) already styled via styles() ──
                $sheet->getRowDimension(3)->setRowHeight(32);

                // ── Data rows alternating background ──
                for ($r = 4; $r <= $lastRow; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                        ]);
                    }
                    // Center numeric columns
                    $sheet->getStyle("D{$r}:K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Right-align salary columns
                    $sheet->getStyle("L{$r}:{$lastCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // ── Total row styling ──
                $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
                    'borders'   => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF94A3B8']]],
                ]);
                $sheet->getStyle("L{$totalRow}:{$lastCol}{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("D{$totalRow}:K{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ── Format salary columns as number ──
                $salaryFormat = '#,##0';
                foreach (['L', 'M', 'O', 'P'] as $col) {
                    $sheet->getStyle("{$col}4:{$col}{$totalRow}")->getNumberFormat()->setFormatCode($salaryFormat);
                }

                // ── Borders around data area ──
                $sheet->getStyle("A3:{$lastCol}{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']],
                        'outline'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF94A3B8']],
                    ],
                ]);

                // ── Freeze pane setelah header ──
                $sheet->freezePane('D4');

                // ── Center align header ──
                $sheet->getStyle("A3:{$lastCol}3")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            },
        ];
    }
}
