<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 400;
    src: url("{{ storage_path('fonts/Poppins-Regular.ttf') }}") format('truetype');
}
@font-face {
    font-family: 'Poppins';
    font-style: normal;
    font-weight: 700;
    src: url("{{ storage_path('fonts/Poppins-Bold.ttf') }}") format('truetype');
}

* { box-sizing: border-box; margin: 0; padding: 0; }

/* A4 landscape usable width: ~257mm. Palette: single navy accent (#0f172a),
   neutral grays for structure, red/green used only as TEXT color on the two
   columns where sign matters (potongan / gaji bersih) — not as fills. This
   mirrors how printed payroll statements are conventionally styled: one
   accent color, thin rules, no dashboard-style color blocking. */
body {
    font-family: 'Poppins', sans-serif;
    font-size: 7pt;
    color: #1e293b;
    background: #fff;
    padding: 12pt 16pt;
}

/* ── HEADER (letterhead) ── */
.hdr { width: 100%; border-collapse: collapse; margin-bottom: 6pt; }
.hdr td { vertical-align: top; padding: 0; }
.h-title { font-size: 14pt; font-weight: 700; color: #0f172a; letter-spacing: .2pt; margin-bottom: 3pt; }
.h-sub   { font-size: 7pt; color: #475569; line-height: 1.6; }
.h-brand { font-size: 11pt; font-weight: 700; color: #0f172a; text-align: right; letter-spacing: 1.5pt; }
.h-meta  { font-size: 6.5pt; color: #94a3b8; text-align: right; line-height: 1.6; margin-top: 3pt; }
.divider { border-top: 1.75pt solid #0f172a; border-bottom: .5pt solid #0f172a; height: 2.5pt; margin-bottom: 9pt; }

/* ── SUMMARY (formal metrics strip, not dashboard cards) ── */
.sum-t { width: 100%; table-layout: fixed; border-collapse: collapse; border: .75pt solid #cbd5e1; margin-bottom: 8pt; }
.sum-c { width: 14.28%; padding: 5pt 8pt; vertical-align: top; border-right: .75pt solid #e2e8f0; }
.sum-c:last-child { border-right: none; background: #f8fafc; }
.sl { display: block; min-height: 16pt; font-size: 6pt; font-weight: 700; line-height: 1.3; text-transform: uppercase; letter-spacing: .4pt; color: #64748b; }
.sv { font-size: 8.5pt; font-weight: 700; line-height: 1.2; margin-top: 2pt; color: #0f172a; }
.sv.neg { color: #b91c1c; }
.sv.pos { color: #15803d; font-size: 9.5pt; }

/* ── INFO (plain footnote, not a callout) ── */
.info { font-size: 6.5pt; color: #64748b; margin-bottom: 8pt; line-height: 1.4; padding-bottom: 6pt; border-bottom: .5pt solid #e2e8f0; }
.info strong { color: #475569; }

/* ── DATA TABLE ── */
.dt { width: 100%; border-collapse: collapse; font-size: 6.5pt; border: .75pt solid #cbd5e1; }

/* Header row 1 — subtle group band, not colored blocks */
.dt thead tr.h1 th {
    background: #eef2f7; color: #334155;
    padding: 4pt 3pt; font-size: 6pt; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3pt;
    text-align: center; border-bottom: .75pt solid #cbd5e1; border-right: .75pt solid #e2e8f0;
}
.dt thead tr.h1 th.thl { text-align: left; padding-left: 5pt; }
.dt thead tr.h1 th.grp-net { background: #0f172a; color: #f1f5f9; }

/* Header row 2 — single navy header, uniform (no per-section color) */
.dt thead tr.h2 th {
    background: #0f172a; color: #e2e8f0;
    padding: 4pt 3pt; font-size: 6pt; font-weight: 600;
    text-align: center; border-right: .75pt solid #334155;
}
.dt thead tr.h2 th.tr-end { text-align: right; padding-right: 4pt; }
.th-sep { background: #eef2f7; width: 4pt; padding: 0 !important; border-right: .75pt solid #cbd5e1 !important; }

/* Body */
.dt tbody tr td {
    padding: 3.5pt 3pt;
    border-bottom: .5pt solid #e2e8f0;
    border-right: .5pt solid #eef2f7;
    text-align: center; vertical-align: middle;
    color: #1e293b;
}
.dt tbody tr:nth-child(even) td { background: #f8fafc; }
.dt tbody tr:last-child td { border-bottom: .75pt solid #cbd5e1; }
.dt tbody tr { page-break-inside: avoid; }
.dt thead { display: table-header-group; }
.td-l { text-align: left !important; padding-left: 5pt !important; }
.td-r { text-align: right !important; padding-right: 4pt !important; }
.td-sep { background: #fbfcfd !important; width: 4pt; padding: 0 !important; border-right: .75pt solid #e2e8f0 !important; }

.en { font-size: 7pt; font-weight: 700; color: #0f172a; }
.ep { font-size: 5.5pt; color: #94a3b8; margin-top: 1pt; }

/* Plain numbers — color only marks meaning (negative/alpha in red), no pill backgrounds */
.num     { font-size: 6.5pt; color: #1e293b; }
.num-bad { font-size: 6.5pt; color: #b91c1c; font-weight: 700; }

.sal   { font-size: 6.5pt; font-weight: 700; color: #0f172a; }
.sal-s { font-size: 6pt; color: #64748b; }
.cut   { font-size: 6.5pt; font-weight: 700; color: #b91c1c; }
.add   { font-size: 6.5pt; font-weight: 600; color: #166534; }
.net   { font-size: 7.5pt; font-weight: 700; color: #0f172a; }
.mut   { color: #94a3b8; font-size: 6pt; }

/* Total row */
.dt tfoot td {
    background: #eef2f7 !important;
    font-weight: 700; font-size: 6.5pt;
    padding: 5pt 3pt; text-align: center;
    border-top: 1.25pt solid #0f172a;
    color: #0f172a;
}
.tf-l { text-align: right !important; padding-right: 6pt !important; color: #334155; }

/* ── FOOTER ── */
.ftr { width: 100%; border-collapse: collapse; margin-top: 12pt; padding-top: 6pt; border-top: .5pt solid #e2e8f0; }
.ftr td { vertical-align: bottom; padding: 0; }
.fm { font-size: 6.5pt; color: #94a3b8; line-height: 1.6; }
.si { text-align: center; padding: 0 14pt; vertical-align: top; }
.sl2 { height: 32pt; border-bottom: .75pt solid #94a3b8; width: 110pt; margin: 0 auto; }
.slb { font-size: 6.5pt; color: #475569; text-align: center; margin-top: 3pt; line-height: 1.4; }
</style>
</head>
<body>

{{-- HEADER --}}
<table class="hdr">
    <tr>
        <td style="width:68%;">
            <div class="h-title">LAPORAN GAJI BULANAN</div>
            <div class="h-sub">
                Periode: <strong>{{ $startDate->isoFormat('D MMMM Y') }} – {{ $endDate->isoFormat('D MMMM Y') }}</strong>
                &nbsp;|&nbsp; Cabang: <strong>{{ $branchName }}</strong>
                &nbsp;|&nbsp; Total Hari: <strong>{{ $daysInMonth }} hari</strong>
            </div>
        </td>
        <td style="width:32%;">
            <div class="h-brand">ACEGARD</div>
            <div class="h-meta">Sistem Manajemen Internal<br>Dicetak: {{ now()->isoFormat('D MMM Y, HH:mm') }} WIB</div>
        </td>
    </tr>
</table>
<div class="divider"></div>

{{-- SUMMARY --}}
@php
    $totalSalary    = $report->sum('monthly_salary');
    $totalDeduction = $report->sum('deduction_amount');
    $totalNet       = $report->sum('salary');
    $totalTambahan  = $report->sum('total_tambahan');
    $totalPotAdj    = $report->sum('total_potongan');
    $totalProrate   = $report->sum('daily_prorate');
    $totalBaseGaji  = $report->sum('base_gaji');
    $totalPotongan  = $totalDeduction + $totalPotAdj;
@endphp

<table class="sum-t">
    <tr>
        <td class="sum-c"><div class="sl">Karyawan</div><div class="sv">{{ $report->count() }}</div></td>
        <td class="sum-c"><div class="sl">Total Gaji Pokok</div><div class="sv">Rp{{ number_format($totalSalary, 0, ',', '.') }}</div></td>
        <td class="sum-c"><div class="sl">Total Rate/Hari (Prorate)</div><div class="sv">Rp{{ number_format($totalProrate, 0, ',', '.') }}</div></td>
        <td class="sum-c"><div class="sl">Total Prorate (Sebelum Potongan)</div><div class="sv">Rp{{ number_format($totalBaseGaji, 0, ',', '.') }}</div></td>
        <td class="sum-c"><div class="sl">Total Tambahan</div><div class="sv">Rp{{ number_format($totalTambahan, 0, ',', '.') }}</div></td>
        <td class="sum-c"><div class="sl">Total Potongan</div><div class="sv neg">Rp{{ number_format($totalPotongan, 0, ',', '.') }}</div></td>
        <td class="sum-c"><div class="sl">Gaji Bersih</div><div class="sv pos">Rp{{ number_format($totalNet, 0, ',', '.') }}</div></td>
    </tr>
</table>

{{-- INFO --}}
<div class="info">
    <strong>Formula:</strong>
    Rate/Hari = Gaji Pokok ÷ ({{ $daysInMonth }} hari − Jatah Libur) ·
    Gaji = Rate × (Masuk + Sakit) + Tambahan (Motor + Makan) − Potongan (Rokok + Kasbon + Pot.Absen) ·
    Dibulatkan ke Rp1.000
</div>

{{-- TABLE --}}
<table class="dt">
    <thead>
        <tr class="h1">
            <th rowspan="2" style="width:14pt;">#</th>
            <th rowspan="2" class="thl" style="width:64pt;">Nama</th>
            <th colspan="6">Kehadiran</th>
            <th rowspan="2" class="th-sep"></th>
            <th colspan="3">Gaji Pokok</th>
            <th>Absen</th>
            <th rowspan="2" class="th-sep"></th>
            <th colspan="2">Tambahan</th>
            <th colspan="3">Potongan Manual</th>
            <th rowspan="2" class="th-sep"></th>
            <th rowspan="2" class="grp-net" style="width:48pt;">Gaji Bersih</th>
        </tr>
        <tr class="h2">
            <th style="width:24pt;">Masuk</th>
            <th style="width:24pt;">Libur</th>
            <th style="width:24pt;">Jatah</th>
            <th style="width:24pt;">Sakit</th>
            <th style="width:24pt;">Lebih</th>
            <th style="width:24pt;">Alpha</th>
            <th class="tr-end" style="width:48pt;">Gaji Pokok</th>
            <th class="tr-end" style="width:42pt;">Rate/Hr</th>
            <th class="tr-end" style="width:46pt;">Prorate</th>
            <th class="tr-end" style="width:42pt;">Pot.Absen</th>
            <th class="tr-end" style="width:40pt;">+Motor</th>
            <th class="tr-end" style="width:40pt;">+Makan</th>
            <th class="tr-end" style="width:38pt;">-Rokok</th>
            <th class="tr-end" style="width:40pt;">-Kasbon</th>
            <th class="tr-end" style="width:40pt;">-Pot.Abs</th>
        </tr>
    </thead>
    <tbody>
        @forelse($report as $row)
            <tr>
                <td class="mut">{{ $loop->iteration }}</td>
                <td class="td-l">
                    <div class="en">{{ $row['employee']->name }}</div>
                    <div class="ep">{{ $row['employee']->job_position ?? '-' }}</div>
                </td>
                <td class="num">{{ $row['present'] }}</td>
                <td class="num">{{ $row['off'] }}</td>
                <td class="num">{{ $row['off_quota'] }}</td>
                <td class="num">{{ $row['sick'] }}</td>
                <td class="{{ $row['excess_off'] > 0 ? 'num-bad' : 'num' }}">{{ $row['excess_off'] }}</td>
                <td class="{{ $row['absent'] > 0 ? 'num-bad' : 'num' }}">{{ $row['absent'] }}</td>
                <td class="td-sep"></td>
                <td class="td-r sal">Rp{{ number_format($row['monthly_salary'], 0, ',', '.') }}</td>
                <td class="td-r sal-s">Rp{{ number_format($row['daily_prorate'], 0, ',', '.') }}</td>
                <td class="td-r sal">Rp{{ number_format($row['base_gaji'], 0, ',', '.') }}</td>
                <td class="td-r cut">{{ $row['deduction_amount'] > 0 ? 'Rp'.number_format($row['deduction_amount'],0,',','.') : '—' }}</td>
                <td class="td-sep"></td>
                <td class="td-r add">{{ $row['uang_motor'] > 0 ? 'Rp'.number_format($row['uang_motor'],0,',','.') : '—' }}</td>
                <td class="td-r add">{{ $row['uang_makan'] > 0 ? 'Rp'.number_format($row['uang_makan'],0,',','.') : '—' }}</td>
                <td class="td-r cut">{{ $row['potongan_rokok'] > 0 ? 'Rp'.number_format($row['potongan_rokok'],0,',','.') : '—' }}</td>
                <td class="td-r cut">{{ $row['kasbon'] > 0 ? 'Rp'.number_format($row['kasbon'],0,',','.') : '—' }}</td>
                <td class="td-r cut">{{ $row['potongan_absen'] > 0 ? 'Rp'.number_format($row['potongan_absen'],0,',','.') : '—' }}</td>
                <td class="td-sep"></td>
                <td class="td-r net">Rp{{ number_format($row['salary'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="21" style="text-align:center; padding:16pt; color:#94a3b8;">Tidak ada data.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" class="tf-l">TOTAL</td>
            <td>{{ $report->sum('present') }}</td>
            <td>{{ $report->sum('off') }}</td>
            <td>—</td>
            <td>{{ $report->sum('sick') }}</td>
            <td>{{ $report->sum('excess_off') }}</td>
            <td class="cut">{{ $report->sum('absent') }}</td>
            <td class="td-sep"></td>
            <td class="td-r sal">Rp{{ number_format($totalSalary, 0, ',', '.') }}</td>
            <td class="td-r sal-s">Rp{{ number_format($totalProrate, 0, ',', '.') }}</td>
            <td class="td-r sal">Rp{{ number_format($totalBaseGaji, 0, ',', '.') }}</td>
            <td class="td-r cut">Rp{{ number_format($totalDeduction, 0, ',', '.') }}</td>
            <td class="td-sep"></td>
            <td class="td-r add">Rp{{ number_format($report->sum('uang_motor'), 0, ',', '.') }}</td>
            <td class="td-r add">Rp{{ number_format($report->sum('uang_makan'), 0, ',', '.') }}</td>
            <td class="td-r cut">Rp{{ number_format($report->sum('potongan_rokok'), 0, ',', '.') }}</td>
            <td class="td-r cut">Rp{{ number_format($report->sum('kasbon'), 0, ',', '.') }}</td>
            <td class="td-r cut">Rp{{ number_format($report->sum('potongan_absen'), 0, ',', '.') }}</td>
            <td class="td-sep"></td>
            <td class="td-r net">Rp{{ number_format($totalNet, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

{{-- FOOTER + SIGNATURES --}}
<table class="ftr">
    <tr>
        <td class="fm">
            Dicetak: {{ now()->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB &nbsp;·&nbsp;
            Acegard Indonesia — Sistem Manajemen Internal
        </td>
        <td style="text-align:right; vertical-align:bottom;">
            <table style="border-collapse:collapse; margin-left:auto;">
                <tr>
                    <td class="si">
                        <div class="sl2"></div>
                        <div class="slb">Mengetahui,<br>Pimpinan Cabang</div>
                    </td>
                    <td class="si">
                        <div class="sl2"></div>
                        <div class="slb">Disiapkan oleh,<br>Admin HRD</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
