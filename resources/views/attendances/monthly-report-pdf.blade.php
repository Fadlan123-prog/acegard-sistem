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

/* A4 landscape usable width: ~257mm */
body {
    font-family: 'Poppins', sans-serif;
    font-size: 7pt;
    color: #1e293b;
    background: #fff;
    padding: 10pt 14pt;
}

/* ── HEADER ── */
.hdr { width: 100%; border-collapse: collapse; margin-bottom: 8pt; }
.hdr td { vertical-align: top; padding: 0; }
.h-title { font-size: 13pt; font-weight: 700; color: #0f172a; margin-bottom: 2pt; }
.h-sub   { font-size: 7pt; color: #64748b; line-height: 1.6; }
.h-brand { font-size: 10pt; font-weight: 700; color: #0f172a; text-align: right; letter-spacing: 1pt; }
.h-meta  { font-size: 6.5pt; color: #94a3b8; text-align: right; line-height: 1.6; margin-top: 2pt; }
.divider { border-top: 1.5pt solid #0f172a; margin-bottom: 8pt; }

/* ── SUMMARY ── */
.sum-t { width: 100%; border-collapse: separate; border-spacing: 4pt 0; margin-bottom: 8pt; }
.sum-c { width: 16.6%; border: 1pt solid #e2e8f0; border-radius: 4pt; padding: 5pt 8pt; vertical-align: top; }
.sum-c.green { border-color: #bbf7d0; background: #f0fdf4; }
.sl { font-size: 6pt; font-weight: 700; text-transform: uppercase; letter-spacing: .4pt; color: #94a3b8; }
.sl.g { color: #16a34a; }
.sv { font-size: 9pt; font-weight: 800; line-height: 1.2; margin-top: 2pt; }
.c-blue  { color: #1d4ed8; }
.c-dark  { color: #0f172a; }
.c-red   { color: #b91c1c; }
.c-green { color: #15803d; }

/* ── INFO ── */
.info { background: #f0fdf4; border-left: 2pt solid #16a34a; padding: 4pt 8pt; font-size: 6.5pt; color: #166534; margin-bottom: 7pt; line-height: 1.4; }

/* ── DATA TABLE ── */
.dt { width: 100%; border-collapse: collapse; font-size: 6.5pt; }

/* Header row 1 */
.dt thead tr.h1 th {
    background: #0f172a; color: #f1f5f9;
    padding: 5pt 3pt; font-size: 6.5pt; font-weight: 700;
    text-align: center; border-right: 1pt solid #1e293b;
}
.dt thead tr.h1 th.thl { text-align: left; padding-left: 5pt; }

/* Header row 2 */
.dt thead tr.h2 th {
    padding: 3pt 3pt; font-size: 6pt; font-weight: 600;
    text-align: center; border-right: 1pt solid rgba(255,255,255,.15);
}
.th-att   { background: #1e3a5f; color: #93c5fd; }
.th-att-b { background: #1e3a5f; color: #f87171; }
.th-sal   { background: #14532d; color: #86efac; }
.th-sal-r { background: #14532d; color: #86efac; text-align: right !important; padding-right: 4pt !important; }
.th-cut   { background: #7f1d1d; color: #fca5a5; text-align: right !important; padding-right: 4pt !important; }
.th-add   { background: #166534; color: #bbf7d0; text-align: right !important; padding-right: 4pt !important; }
.th-ded   { background: #7f1d1d; color: #fecaca; text-align: right !important; padding-right: 4pt !important; }
.th-net   { background: #064e3b; color: #a7f3d0; text-align: right !important; padding-right: 4pt !important; }
.th-sep   { background: #f1f5f9; width: 3pt; padding: 0 !important; border-right: 1pt solid #e2e8f0 !important; }

/* Body */
.dt tbody tr td {
    padding: 4pt 3pt;
    border-bottom: 1pt solid #f1f5f9;
    border-right: 1pt solid #f1f5f9;
    text-align: center; vertical-align: middle;
}
.dt tbody tr:nth-child(even) td { background: #fafafa; }
.dt tbody tr:last-child td { border-bottom: none; }
.td-l { text-align: left !important; padding-left: 5pt !important; }
.td-r { text-align: right !important; padding-right: 4pt !important; }
.td-sep { background: #f8fafc !important; width: 3pt; padding: 0 !important; border-right: 1pt solid #e2e8f0 !important; }

.en { font-size: 7pt; font-weight: 700; color: #0f172a; }
.ep { font-size: 5.5pt; color: #94a3b8; margin-top: 1pt; }

.b { display: inline-block; padding: 1pt 4pt; border-radius: 2pt; font-size: 6pt; font-weight: 700; min-width: 14pt; text-align: center; }
.bg  { background: #dcfce7; color: #166534; }
.bgr { background: #f1f5f9; color: #475569; border: .5pt solid #e2e8f0; }
.br  { background: #fee2e2; color: #b91c1c; }

.sal   { font-size: 6.5pt; font-weight: 700; color: #0f172a; }
.sal-s { font-size: 6pt; color: #64748b; }
.cut   { font-size: 6.5pt; font-weight: 700; color: #b91c1c; }
.add   { font-size: 6.5pt; font-weight: 700; color: #15803d; }
.net   { font-size: 7pt; font-weight: 700; color: #15803d; }
.mut   { color: #94a3b8; font-size: 6pt; }

/* Total row */
.dt tfoot td {
    background: #f1f5f9 !important;
    font-weight: 700; font-size: 6.5pt;
    padding: 5pt 3pt; text-align: center;
    border-top: 1.5pt solid #94a3b8;
}
.tf-l { text-align: right !important; padding-right: 6pt !important; color: #475569; }

/* ── FOOTER ── */
.ftr { width: 100%; border-collapse: collapse; margin-top: 10pt; padding-top: 6pt; border-top: 1pt solid #e2e8f0; }
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
@endphp

<table class="sum-t">
    <tr>
        <td class="sum-c"><div class="sl">Karyawan</div><div class="sv c-blue">{{ $report->count() }}</div></td>
        <td class="sum-c"><div class="sl">Total Gaji Pokok</div><div class="sv c-dark" style="font-size:7.5pt;">Rp{{ number_format($totalSalary, 0, ',', '.') }}</div></td>
        <td class="sum-c"><div class="sl">Total Potongan Absen</div><div class="sv c-red" style="font-size:7.5pt;">Rp{{ number_format($totalDeduction, 0, ',', '.') }}</div></td>
        <td class="sum-c"><div class="sl">Total Tambahan</div><div class="sv c-green" style="font-size:7.5pt;">Rp{{ number_format($totalTambahan, 0, ',', '.') }}</div></td>
        <td class="sum-c"><div class="sl">Total Potongan Lain</div><div class="sv c-red" style="font-size:7.5pt;">Rp{{ number_format($totalPotAdj, 0, ',', '.') }}</div></td>
        <td class="sum-c green"><div class="sl g">Gaji Bersih</div><div class="sv c-green" style="font-size:7.5pt;">Rp{{ number_format($totalNet, 0, ',', '.') }}</div></td>
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
            <th rowspan="2" class="thl" style="width:72pt;">Nama</th>
            <th colspan="6" style="background:#1e3a5f;">Kehadiran</th>
            <th rowspan="2" class="th-sep"></th>
            <th colspan="3" style="background:#14532d;">Kalkulasi Dasar</th>
            <th rowspan="2" class="th-sep"></th>
            <th colspan="2" style="background:#166534;">Tambahan</th>
            <th colspan="3" style="background:#7f1d1d;">Potongan</th>
            <th rowspan="2" class="th-sep"></th>
            <th rowspan="2" class="th-net" style="width:52pt;">Gaji Bersih</th>
        </tr>
        <tr class="h2">
            <th class="th-att">Masuk</th>
            <th class="th-att">Libur</th>
            <th class="th-att">Jatah</th>
            <th class="th-att">Sakit</th>
            <th class="th-att-b">Lebih</th>
            <th class="th-att-b">Alpha</th>
            <th class="th-sal-r" style="width:52pt;">Gaji Pokok</th>
            <th class="th-sal-r" style="width:44pt;">Rate/Hr</th>
            <th class="th-cut" style="width:46pt;">Pot.Absen</th>
            <th class="th-add" style="width:44pt;">+Motor</th>
            <th class="th-add" style="width:44pt;">+Makan</th>
            <th class="th-ded" style="width:40pt;">-Rokok</th>
            <th class="th-ded" style="width:44pt;">-Kasbon</th>
            <th class="th-ded" style="width:44pt;">-Pot.Abs</th>
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
                <td><span class="b bg">{{ $row['present'] }}</span></td>
                <td><span class="b bgr">{{ $row['off'] }}</span></td>
                <td><span class="b bgr">{{ $row['off_quota'] }}</span></td>
                <td><span class="b bgr">{{ $row['sick'] }}</span></td>
                <td><span class="b {{ $row['excess_off'] > 0 ? 'br' : 'bgr' }}">{{ $row['excess_off'] }}</span></td>
                <td><span class="b {{ $row['absent'] > 0 ? 'br' : 'bgr' }}">{{ $row['absent'] }}</span></td>
                <td class="td-sep"></td>
                <td class="td-r sal">Rp{{ number_format($row['monthly_salary'], 0, ',', '.') }}</td>
                <td class="td-r sal-s">Rp{{ number_format($row['daily_prorate'], 0, ',', '.') }}</td>
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
                <td colspan="19" style="text-align:center; padding:16pt; color:#94a3b8;">Tidak ada data.</td>
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
            <td>—</td>
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
