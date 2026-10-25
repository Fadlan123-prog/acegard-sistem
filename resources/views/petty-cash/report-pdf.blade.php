<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
@font-face { font-family:'Poppins'; font-weight:400; src:url("{{ storage_path('fonts/Poppins-Regular.ttf') }}") format('truetype'); }
@font-face { font-family:'Poppins'; font-weight:700; src:url("{{ storage_path('fonts/Poppins-Bold.ttf') }}") format('truetype'); }

* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Poppins',sans-serif; font-size:8pt; color:#1e293b; background:#fff; }

/* ── HEADER ── */
.header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    padding: 16pt 22pt 14pt;
    width: 100%;
    margin-bottom: 14pt;
}
.header-inner { width:100%; border-collapse:collapse; }
.header-inner td { padding:0; vertical-align:middle; }

.co-logo {
    display:inline-block;
    background:#fff;
    color:#0f172a;
    font-size:10pt;
    font-weight:700;
    letter-spacing:2pt;
    padding:3pt 8pt;
    border-radius:3pt;
    margin-bottom:5pt;
}
.co-sub { font-size:7pt; color:#94a3b8; line-height:1.7; }

.doc-title { font-size:14pt; font-weight:700; color:#fff; text-align:right; letter-spacing:.5pt; }
.doc-meta  { font-size:7pt; color:#94a3b8; text-align:right; margin-top:5pt; line-height:1.8; }
.doc-badge {
    display:inline-block;
    background:rgba(255,255,255,0.12);
    border:1pt solid rgba(255,255,255,0.2);
    color:#e2e8f0;
    font-size:7pt;
    font-weight:700;
    padding:2pt 7pt;
    border-radius:30pt;
    margin-top:5pt;
}

/* ── SUMMARY ── */
.sum-row { width:100%; border-collapse:collapse; margin-bottom:14pt; }
.sum-row td { padding:0 4pt; vertical-align:top; }
.sum-row td:first-child { padding-left:0; }
.sum-row td:last-child  { padding-right:0; }

.sum-card {
    border:1pt solid #e2e8f0;
    border-radius:5pt;
    padding:9pt 12pt;
    background:#fff;
}
.sum-card-dark {
    border-radius:5pt;
    padding:9pt 12pt;
    background:#0f172a;
}
.sum-lbl { font-size:6pt; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.6pt; margin-bottom:4pt; }
.sum-val { font-size:12pt; font-weight:700; color:#0f172a; }
.sum-val-in  { color:#15803d; }
.sum-val-out { color:#dc2626; }
.sum-val-dark { font-size:12pt; font-weight:700; color:#fff; }
.sum-sub { font-size:6pt; color:#94a3b8; margin-top:3pt; }
.sum-accent { width:3pt; border-radius:2pt; }

/* ── SECTION HEADER ── */
.sec-header {
    background:#f1f5f9;
    border-left:3pt solid #334155;
    padding:5pt 10pt;
    margin-bottom:8pt;
    border-radius:0 3pt 3pt 0;
}
.sec-title { font-size:7pt; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:.5pt; }
.sec-sub   { font-size:6.5pt; color:#64748b; margin-top:1pt; }

/* ── MAIN TABLE ── */
.main-t { width:100%; border-collapse:collapse; }
.main-t thead tr th {
    background:#1e293b;
    color:#e2e8f0;
    font-size:6.5pt;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.3pt;
    padding:5pt 6pt;
    border:none;
    white-space:nowrap;
}
.main-t thead tr th:first-child { border-radius:3pt 0 0 0; }
.main-t thead tr th:last-child  { border-radius:0 3pt 0 0; }

.main-t tbody tr td {
    font-size:7.5pt;
    padding:5pt 6pt;
    border-bottom:1pt solid #f1f5f9;
    border-right:none;
    border-left:none;
    vertical-align:middle;
}
.main-t tbody tr:nth-child(odd) td  { background:#fff; }
.main-t tbody tr:nth-child(even) td { background:#f8fafc; }
.main-t tbody tr.row-saldo-awal td {
    background:#fefce8;
    font-size:7pt;
    color:#713f12;
    border-bottom:2pt solid #fde047;
}

.main-t tfoot tr td {
    font-size:7.5pt;
    font-weight:700;
    padding:6pt;
    background:#0f172a;
    color:#e2e8f0;
    border:none;
}

/* ── TYPE BADGES ── */
.badge { border-radius:20pt; padding:2pt 6pt; font-size:6pt; font-weight:700; display:inline-block; white-space:nowrap; }
.badge-in  { background:#dcfce7; color:#15803d; }
.badge-out { background:#fee2e2; color:#dc2626; }
.badge-buy { background:#fef9c3; color:#92400e; }

/* ── NUMBER STYLES ── */
.text-right  { text-align:right; }
.text-center { text-align:center; }
.num-green   { color:#15803d; font-weight:700; }
.num-red     { color:#dc2626; font-weight:700; }
.num-dark    { color:#0f172a; font-weight:700; }
.muted       { color:#94a3b8; }

/* ── FORMULA NOTE ── */
.note {
    margin-top:8pt;
    padding:5pt 9pt;
    background:#f8fafc;
    border-left:2pt solid #cbd5e1;
    font-size:6pt;
    color:#64748b;
    border-radius:0 3pt 3pt 0;
}

/* ── SIGNATURES ── */
.sign-section { margin-top:22pt; }
.sign-t { width:100%; border-collapse:collapse; }
.sign-t td { text-align:center; vertical-align:bottom; padding:0 12pt; width:33%; }
.sign-box {
    border:1pt solid #e2e8f0;
    border-radius:4pt;
    padding:8pt 10pt 6pt;
    background:#f8fafc;
}
.sign-space { height:36pt; }
.sign-name  { font-size:8pt; font-weight:700; color:#1e293b; margin-top:5pt; padding-top:5pt; border-top:1pt solid #94a3b8; }
.sign-role  { font-size:6.5pt; color:#64748b; margin-top:2pt; }

.legal-note {
    text-align:center;
    font-size:5.5pt;
    color:#cbd5e1;
    margin-top:3pt;
    line-height:1.8;
}

/* ── FOOTER ── */
.footer {
    margin-top:14pt;
    padding:6pt 0;
    border-top:1pt solid #e2e8f0;
}
.footer-t { width:100%; border-collapse:collapse; }
.footer-t td { font-size:6pt; color:#94a3b8; padding:0; vertical-align:middle; }
</style>
</head>
<body>

{{-- ══════════════════════════ HEADER ══════════════════════════ --}}
<div class="header">
    <table class="header-inner">
        <tr>
            <td style="width:55%;">
                <div class="co-logo">ACEGARD</div>
                <div class="co-sub">
                    {{ $branch->name ?? '-' }}{{ $branch->city ? ' · ' . $branch->city : '' }}<br>
                    Sistem Manajemen Internal
                </div>
            </td>
            <td style="width:45%;">
                <div class="doc-title">LAPORAN PETTY CASH</div>
                <div class="doc-meta">
                    Diterbitkan: {{ now()->isoFormat('D MMMM Y, HH:mm') }}<br>
                    Periode Laporan: <strong style="color:#e2e8f0;">{{ $period }}</strong>
                </div>
            </td>
        </tr>
    </table>
</div>

<div style="padding:0 20pt;">

{{-- ══════════════════════════ SUMMARY ══════════════════════════ --}}
<table class="sum-row">
    <tr>
        <td style="width:22%;">
            <div class="sum-card">
                <div class="sum-lbl">Saldo Awal Bulan</div>
                <div class="sum-val">Rp{{ number_format($balanceBefore, 0, ',', '.') }}</div>
                <div class="sum-sub">Sebelum periode {{ $period }}</div>
            </div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:22%;">
            <div class="sum-card">
                <div class="sum-lbl">Total Pemasukan</div>
                <div class="sum-val sum-val-in">+ Rp{{ number_format($totalIn, 0, ',', '.') }}</div>
                <div class="sum-sub">{{ $transactions->where('type','in')->count() }} transaksi masuk</div>
            </div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:22%;">
            <div class="sum-card">
                <div class="sum-lbl">Total Pengeluaran</div>
                <div class="sum-val sum-val-out">− Rp{{ number_format($totalOut, 0, ',', '.') }}</div>
                <div class="sum-sub">{{ $transactions->whereIn('type',['out','purchase'])->count() }} transaksi keluar</div>
            </div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:28%;">
            <div class="sum-card-dark">
                <div class="sum-lbl" style="color:#64748b;">Saldo Akhir Bulan</div>
                <div class="sum-val-dark">Rp{{ number_format($balanceEnd, 0, ',', '.') }}</div>
                <div class="sum-sub" style="color:{{ $balanceEnd >= 0 ? '#22c55e' : '#f87171' }}; margin-top:3pt;">
                    {{ $balanceEnd >= 0 ? '▲ Saldo positif' : '▼ Saldo minus' }}
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- ══════════════════════════ TABLE HEADER ══════════════════════════ --}}
<div class="sec-header">
    <div class="sec-title">Rincian Transaksi</div>
    <div class="sec-sub">{{ $period }} · {{ $branch->name ?? '-' }} · {{ $transactions->count() }} transaksi</div>
</div>

{{-- ══════════════════════════ MAIN TABLE ══════════════════════════ --}}
@php
    $running = $balanceBefore;
    $no = 1;
@endphp

<table class="main-t">
    <thead>
        <tr>
            <th class="text-center" style="width:4%;">No</th>
            <th style="width:8%;">Tanggal</th>
            <th class="text-center" style="width:7%;">Tipe</th>
            <th style="width:14%;">Karyawan / Ket.</th>
            <th style="width:12%;">Motor</th>
            <th style="width:15%;">Tujuan / Barang</th>
            <th class="text-right" style="width:7%;">Jarak</th>
            <th class="text-right" style="width:10%;">Biaya Bensin</th>
            <th class="text-right" style="width:10%;">Masuk</th>
            <th class="text-right" style="width:10%;">Keluar</th>
            <th class="text-right" style="width:10%;">Saldo</th>
        </tr>
    </thead>
    <tbody>
        {{-- Baris saldo awal --}}
        <tr class="row-saldo-awal">
            <td class="text-center muted">—</td>
            <td colspan="9" style="font-size:7pt; font-weight:700;">Saldo Awal Periode &nbsp;·&nbsp; Sebelum {{ $period }}</td>
            <td class="text-right num-dark">Rp{{ number_format($balanceBefore, 0, ',', '.') }}</td>
        </tr>

        @forelse($transactions as $trx)
            @php
                if ($trx->type === 'in') {
                    $running += $trx->amount;
                } else {
                    $running -= $trx->amount;
                }
            @endphp
            <tr>
                <td class="text-center muted" style="font-size:7pt;">{{ $no++ }}</td>

                <td style="white-space:nowrap; font-size:7pt;">
                    {{ $trx->date->isoFormat('D MMM Y') }}
                </td>

                <td class="text-center">
                    @if($trx->type === 'in')
                        <span class="badge badge-in">&#8595; Masuk</span>
                    @elseif($trx->type === 'out')
                        <span class="badge badge-out">&#127949; Motor</span>
                    @else
                        <span class="badge badge-buy">&#128722; Beli</span>
                    @endif
                </td>

                <td style="font-size:7.5pt;">
                    @if($trx->type === 'in')
                        <span class="muted" style="font-style:italic;">{{ $trx->description ?: 'Penambahan saldo' }}</span>
                    @else
                        {{ $trx->employee?->name ?? '-' }}
                        @if($trx->description)
                            <br><span class="muted" style="font-size:6.5pt;">{{ $trx->description }}</span>
                        @endif
                    @endif
                </td>

                <td style="font-size:7.5pt;">
                    @if($trx->type === 'out')
                        {{ $trx->motor_name ?? '-' }}
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>

                <td style="font-size:7.5pt;">
                    @if($trx->type === 'out')
                        {{ $trx->destination ?? '-' }}
                    @elseif($trx->type === 'purchase')
                        <strong>{{ $trx->item_name ?? '-' }}</strong>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>

                <td class="text-right" style="font-size:7pt;">
                    @if($trx->distance_km)
                        {{ number_format($trx->distance_km, 1, ',', '.') }} km
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>

                <td class="text-right" style="font-size:7pt;">
                    @if($trx->fuel_cost > 0)
                        Rp{{ number_format($trx->fuel_cost, 0, ',', '.') }}
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>

                <td class="text-right">
                    @if($trx->type === 'in')
                        <span class="num-green">Rp{{ number_format($trx->amount, 0, ',', '.') }}</span>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>

                <td class="text-right">
                    @if($trx->type !== 'in')
                        <span class="num-red">Rp{{ number_format($trx->amount, 0, ',', '.') }}</span>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>

                <td class="text-right num-dark" style="color:{{ $running >= 0 ? '#0f172a' : '#dc2626' }}; font-size:7.5pt;">
                    Rp{{ number_format($running, 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center muted" style="padding:18pt; font-style:italic; font-size:7.5pt;">
                    Tidak ada transaksi pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>

    @if($transactions->count())
    <tfoot>
        <tr>
            <td colspan="8" class="text-right" style="color:#94a3b8; font-weight:400; font-size:7pt; letter-spacing:.3pt; text-transform:uppercase;">
                Total Periode {{ $period }}
            </td>
            <td class="text-right" style="color:#4ade80;">
                Rp{{ number_format($totalIn, 0, ',', '.') }}
            </td>
            <td class="text-right" style="color:#f87171;">
                Rp{{ number_format($totalOut, 0, ',', '.') }}
            </td>
            <td class="text-right" style="color:{{ $balanceEnd >= 0 ? '#4ade80' : '#f87171' }};">
                Rp{{ number_format($balanceEnd, 0, ',', '.') }}
            </td>
        </tr>
    </tfoot>
    @endif
</table>

{{-- ══════════════════════════ NOTE ══════════════════════════ --}}
<div class="note">
    <strong>Catatan:</strong> Biaya bensin dihitung dengan rumus <em>(Jarak × Rp425) × 2</em> (pulang-pergi), dibulatkan ke ribuan terdekat.
</div>

{{-- ══════════════════════════ SIGNATURES ══════════════════════════ --}}
<div class="sign-section">
    <table class="sign-t">
        <tr>
            <td style="width:33%;">
                <div class="sign-box">
                    <div class="sign-space"></div>
                    <div class="sign-name">Pimpinan Cabang</div>
                    <div class="sign-role">{{ $branch->name ?? '-' }}</div>
                </div>
            </td>
            <td style="width:34%;">
                <div class="legal-note">
                    Dokumen ini diterbitkan secara digital<br>
                    dan sah tanpa stempel basah.<br>
                    {{ now()->isoFormat('D MMMM Y') }}
                </div>
            </td>
            <td style="width:33%;">
                <div class="sign-box">
                    <div class="sign-space"></div>
                    <div class="sign-name">Bendahara</div>
                    <div class="sign-role">{{ $branch->name ?? '-' }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

</div>{{-- /padding --}}

{{-- ══════════════════════════ FOOTER ══════════════════════════ --}}
<div style="padding:0 20pt;">
    <div class="footer">
        <table class="footer-t">
            <tr>
                <td>ACEGARD Indonesia &nbsp;·&nbsp; Sistem Manajemen Internal</td>
                <td class="text-right">Laporan Petty Cash &nbsp;·&nbsp; {{ $period }} &nbsp;·&nbsp; {{ $branch->name ?? '-' }}</td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
