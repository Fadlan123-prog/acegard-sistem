<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Slip Komisi — {{ $employee->name }}</title>
<style>
@font-face { font-family:'Poppins'; font-weight:400; src:url("{{ storage_path('fonts/Poppins-Regular.ttf') }}") format('truetype'); }
@font-face { font-family:'Poppins'; font-weight:700; src:url("{{ storage_path('fonts/Poppins-Bold.ttf') }}") format('truetype'); }

@page { size: A4 portrait; margin: 14mm 14mm 12mm; }

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Poppins', Arial, sans-serif;
    font-size: 8.5pt;
    color: #1e293b;
    background: #fff;
}

/* ── SCREEN ONLY ── */
.screen-bar {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 20px;
}
.btn-print {
    background: #1d4ed8; color: #fff;
    border: none; border-radius: 6px;
    padding: 7px 18px; font-size: 12px;
    cursor: pointer; font-family: inherit;
}
.btn-back {
    background: #fff; color: #475569;
    border: 1px solid #e2e8f0; border-radius: 6px;
    padding: 7px 14px; font-size: 12px;
    text-decoration: none; display: inline-flex;
    align-items: center; font-family: inherit;
}

/* ── HEADER ── */
.header { background: #0f172a; padding: 14pt 18pt; margin-bottom: 14pt; }
.header-t { width: 100%; border-collapse: collapse; }
.header-t td { padding: 0; vertical-align: middle; }
.co-name { font-size: 18pt; font-weight: 700; color: #fff; letter-spacing: 1pt; }
.co-sub  { font-size: 7pt; color: #94a3b8; margin-top: 3pt; line-height: 1.6; }
.doc-title { font-size: 10pt; font-weight: 700; color: #fff; text-align: right; }
.doc-sub   { font-size: 7pt; color: #94a3b8; text-align: right; margin-top: 4pt; }

/* ── SUMMARY STRIP ── */
.summary-t { width: 100%; border-collapse: collapse; margin-bottom: 14pt; }
.summary-t td { padding: 0 5pt; vertical-align: top; }
.summary-t td:first-child { padding-left: 0; }
.summary-t td:last-child  { padding-right: 0; }
.s-box { border: 1pt solid #e2e8f0; border-radius: 5pt; padding: 8pt 10pt; }
.s-lbl { font-size: 6.5pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5pt; }
.s-val { font-size: 11pt; font-weight: 700; color: #0f172a; margin-top: 2pt; }
.s-sub { font-size: 6pt; color: #94a3b8; margin-top: 2pt; }
.s-highlight { background: #0f172a; border-color: #0f172a; }
.s-highlight .s-lbl { color: #64748b; }
.s-highlight .s-val { color: #fff; }
.s-highlight .s-sub { color: #475569; }

/* ── DEDUCTION TABLE ── */
.ded-t { width: 100%; border-collapse: collapse; margin-bottom: 14pt; border: 1pt solid #e2e8f0; border-radius: 6pt; }
.ded-t th { background: #f1f5f9; font-size: 6.5pt; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4pt; padding: 5pt 10pt; }
.ded-t td { padding: 7pt 10pt; font-size: 8pt; border-top: 1pt solid #f8fafc; vertical-align: middle; }
.ded-t td:last-child { text-align: right; font-weight: 700; }
.ded-red   { color: #dc2626; }
.ded-green { color: #15803d; }
.ded-total td { background: #f8fafc; font-weight: 700; border-top: 1pt solid #e2e8f0 !important; }

/* ── DETAIL TABLE ── */
.sec-lbl { font-size: 6.5pt; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5pt; margin-bottom: 5pt; }
.det-t { width: 100%; border-collapse: collapse; table-layout: fixed; }
.det-t thead tr th {
    background: #f1f5f9;
    font-size: 6.5pt; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: .4pt;
    padding: 4pt 6pt; border: 1pt solid #e2e8f0;
}
.det-t tbody tr td {
    font-size: 7.5pt; padding: 5pt 6pt;
    border: 1pt solid #e2e8f0; vertical-align: middle;
    height: 22pt;
}
.det-t tbody tr:nth-child(even) td { background: #fafafa; }
.det-t tfoot tr td {
    font-size: 7.5pt; font-weight: 700;
    padding: 5pt 6pt; border: 1pt solid #e2e8f0;
    background: #f1f5f9;
}
.c-no    { width: 26pt; text-align: center; }
.c-date  { width: 60pt; text-align: center; }
.c-cust  { width: 130pt; }
.c-car   { width: 130pt; text-align: center; }
.c-svc   { width: 90pt; text-align: center; }
.c-amt   { width: 72pt; text-align: right; }
.text-r { text-align: right; }
.text-c { text-align: center; }

/* ── SIGN ── */
.sign-t { width: 100%; border-collapse: collapse; margin-top: 20pt; }
.sign-t td { text-align: center; padding: 0 12pt; vertical-align: bottom; }
.sign-line { height: 36pt; border-bottom: 1pt solid #94a3b8; width: 100pt; margin: 0 auto; }
.sign-name { font-size: 8pt; font-weight: 700; color: #1e293b; margin-top: 4pt; }
.sign-role { font-size: 6.5pt; color: #94a3b8; margin-top: 2pt; }
.sign-mid  { font-size: 6pt; color: #cbd5e1; line-height: 1.8; }

/* ── FOOTER ── */
.footer { background: #f8fafc; border-top: 1pt solid #e2e8f0; padding: 5pt 18pt; margin-top: 14pt; }
.footer-t { width: 100%; border-collapse: collapse; }
.footer-t td { font-size: 6.5pt; color: #94a3b8; padding: 0; vertical-align: middle; }

/* ── PRINT ── */
@media print {
    .screen-bar { display: none !important; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>

<div class="screen-bar">
    <button onclick="window.print()" class="btn-print">&#128438; Print / Simpan PDF</button>
    <a href="{{ route('commission.index') }}" class="btn-back">&#8592; Kembali</a>
</div>

{{-- HEADER --}}
<div class="header">
    <table class="header-t">
        <tr>
            <td style="width:55%;">
                <div class="co-name">ACEGARD</div>
                <div class="co-sub">Acegard Indonesia &mdash; Sistem Manajemen Internal<br>Slip Komisi Karyawan</div>
            </td>
            <td style="width:45%;">
                <div class="doc-title">SLIP KOMISI</div>
                <div class="doc-sub">
                    {{ $from->format('d M Y') }} &mdash; {{ $to->format('d M Y') }}<br>
                    Dicetak: {{ now()->isoFormat('D MMMM Y') }}
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- SUMMARY STRIP --}}
<table class="summary-t">
    <tr>
        <td style="width:30%;">
            <div class="s-box">
                <div class="s-lbl">Nama Karyawan</div>
                <div class="s-val" style="font-size:10pt;">{{ $employee->name }}</div>
                <div class="s-sub">{{ $employee->job_position ?? '-' }}</div>
            </div>
        </td>
        <td style="width:23%;">
            <div class="s-box">
                <div class="s-lbl">Total Komisi</div>
                <div class="s-val">Rp{{ number_format($totalKomisi, 0, ',', '.') }}</div>
                <div class="s-sub">{{ $rows->count() }} transaksi</div>
            </div>
        </td>
        <td style="width:23%;">
            <div class="s-box">
                <div class="s-lbl">Potongan</div>
                <div class="s-val" style="color:#dc2626;">
                    - Rp{{ number_format($kasbon + $uangMotor + $uangMakan, 0, ',', '.') }}
                </div>
                <div class="s-sub">kasbon + motor + makan</div>
            </div>
        </td>
        <td style="width:24%;">
            <div class="s-box s-highlight">
                <div class="s-lbl">Yang Diterima</div>
                <div class="s-val" style="font-size:12pt;">Rp{{ number_format($grandTotal, 0, ',', '.') }}</div>
                <div class="s-sub">sudah dipotong</div>
            </div>
        </td>
    </tr>
</table>

{{-- RINCIAN POTONGAN (hanya jika ada) --}}
@if($kasbon > 0 || $uangMotor > 0 || $uangMakan > 0)
<div class="sec-lbl" style="margin-bottom:6pt;">Rincian Potongan & Tambahan</div>
<table class="ded-t">
    <tr>
        <th style="width:60%;">Keterangan</th>
        <th style="text-align:right;">Jumlah</th>
    </tr>
    <tr>
        <td>Total Komisi</td>
        <td class="ded-green">Rp{{ number_format($totalKomisi, 0, ',', '.') }}</td>
    </tr>
    @if($kasbon > 0)
    <tr>
        <td>Kasbon</td>
        <td class="ded-red">- Rp{{ number_format($kasbon, 0, ',', '.') }}</td>
    </tr>
    @endif
    @if($uangMotor > 0)
    <tr>
        <td>Uang Motor</td>
        <td class="ded-red">- Rp{{ number_format($uangMotor, 0, ',', '.') }}</td>
    </tr>
    @endif
    @if($uangMakan > 0)
    <tr>
        <td>Uang Makan</td>
        <td class="ded-red">- Rp{{ number_format($uangMakan, 0, ',', '.') }}</td>
    </tr>
    @endif
    <tr class="ded-total">
        <td>Total Diterima</td>
        <td class="ded-green">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
    </tr>
</table>
@endif

{{-- DETAIL TRANSAKSI --}}
<div class="sec-lbl">Rincian Transaksi Komisi</div>
<table class="det-t">
    <thead>
        <tr>
            <th class="c-no">No</th>
            <th class="c-date">Tanggal</th>
            <th class="c-cust">Nama Customer</th>
            <th class="c-car">Kendaraan</th>
            <th class="c-svc">Service</th>
            <th class="c-amt">Komisi</th>
        </tr>
    </thead>
    <tbody>
        @php $maxRows = max(30, $rows->count()); @endphp
        @for($i = 0; $i < $maxRows; $i++)
        @php
            $row = $rows[$i] ?? null;
            $carName = '-';
            if ($row) {
                $carName = trim(($row->vehicle_brand ?? '') . ' ' . ($row->vehicle_model ?? ''));
                if ($carName === '') $carName = '-';
            }
            $service = $row?->install_type ?? $row?->role ?? '-';
            $serviceLabel = match($service) {
                'fullset'             => 'Full Set',
                'fullset_ekstra'      => 'Full Set Ekstra',
                'fullset_ekstra_plus' => 'Full Set Ekstra+',
                'full_panoramic'      => 'Full + Panoramic',
                'ekstra_panoramic'    => 'Ekstra Panoramic',
                'dsp'                 => 'D / S / P',
                'skkb'                => 'SKKB',
                'installer'           => 'Installer',
                'marketing'           => 'Marketing',
                default               => $service,
            };
        @endphp
        <tr>
            <td class="c-no">{{ $row ? $i + 1 : '' }}</td>
            <td class="c-date">{{ $row ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') : '' }}</td>
            <td class="c-cust">{{ $row?->customer_name ?? '' }}</td>
            <td class="c-car">{{ $row ? $carName : '' }}</td>
            <td class="c-svc">{{ $row ? $serviceLabel : '' }}</td>
            <td class="c-amt">{{ $row ? 'Rp'.number_format($row->amount, 0, ',', '.') : '' }}</td>
        </tr>
        @endfor
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="text-r">Total Komisi</td>
            <td class="text-r">Rp{{ number_format($totalKomisi, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

{{-- TANDA TANGAN --}}
<table class="sign-t">
    <tr>
        <td>
            <div class="sign-line"></div>
            <div class="sign-name">Pimpinan</div>
            <div class="sign-role">Acegard Indonesia</div>
        </td>
        <td>
            <div class="sign-mid">
                Dokumen ini sah tanpa stempel.<br>
                Dicetak: {{ now()->isoFormat('D MMMM Y') }}
            </div>
        </td>
        <td>
            <div class="sign-line"></div>
            <div class="sign-name">{{ $employee->name }}</div>
            <div class="sign-role">Karyawan</div>
        </td>
    </tr>
</table>

{{-- FOOTER --}}
<div class="footer">
    <table class="footer-t">
        <tr>
            <td>ACEGARD Indonesia &mdash; Sistem Manajemen Internal</td>
            <td style="text-align:right;">Slip Komisi &nbsp;·&nbsp; {{ $from->format('d M Y') }} &mdash; {{ $to->format('d M Y') }}</td>
        </tr>
    </table>
</div>

</body>
</html>
