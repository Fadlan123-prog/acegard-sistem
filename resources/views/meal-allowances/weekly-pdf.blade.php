<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
@font-face { font-family:'Poppins'; font-weight:400; font-style:normal;  src:url("{{ storage_path('fonts/Poppins-Regular.ttf') }}") format('truetype'); }
@font-face { font-family:'Poppins'; font-weight:700; font-style:normal;  src:url("{{ storage_path('fonts/Poppins-Bold.ttf') }}") format('truetype'); }
@font-face { font-family:'Poppins'; font-weight:400; font-style:italic;  src:url("{{ storage_path('fonts/Poppins-Italic.ttf') }}") format('truetype'); }

* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Poppins',sans-serif; font-size:8pt; color:#1e293b; background:#fff; }

/* ── HEADER ── */
.header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    padding: 15pt 22pt 13pt;
    width: 100%;
    margin-bottom: 14pt;
}
.header-t { width:100%; border-collapse:collapse; }
.header-t td { padding:0; vertical-align:middle; }

.co-logo {
    display:inline-block;
    background:#fff;
    color:#0f172a;
    font-size:9.5pt;
    font-weight:700;
    letter-spacing:2pt;
    padding:3pt 8pt;
    border-radius:3pt;
    margin-bottom:5pt;
}
.co-sub   { font-size:6.5pt; color:#94a3b8; line-height:1.8; }
.doc-title { font-size:13pt; font-weight:700; color:#fff; text-align:right; letter-spacing:.4pt; }
.doc-meta  { font-size:7pt; color:#94a3b8; text-align:right; margin-top:5pt; line-height:1.8; }

/* ── SUMMARY ── */
.sum-t { width:100%; border-collapse:collapse; margin-bottom:13pt; }
.sum-t td { padding:0 4pt; vertical-align:top; }
.sum-t td:first-child { padding-left:0; }
.sum-t td:last-child  { padding-right:0; }

.sum-card      { border:1pt solid #e2e8f0; border-radius:5pt; padding:9pt 12pt; background:#fff; }
.sum-card-dark { border-radius:5pt; padding:9pt 12pt; background:#0f172a; }
.sum-lbl       { font-size:6pt; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.6pt; margin-bottom:4pt; }
.sum-val       { font-size:13pt; font-weight:700; }
.sum-sub       { font-size:6pt; color:#94a3b8; margin-top:3pt; }

/* ── SECTION HEADER ── */
.sec-hdr {
    background:#f1f5f9;
    border-left:3pt solid #334155;
    padding:5pt 10pt;
    margin-bottom:8pt;
    border-radius:0 3pt 3pt 0;
}
.sec-title { font-size:7pt; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:.5pt; }
.sec-sub   { font-size:6.5pt; color:#64748b; margin-top:1pt; }

/* ── INFO BOX ── */
.info-box {
    background:#eff6ff;
    border-left:3pt solid #3b82f6;
    padding:6pt 10pt;
    font-size:7pt;
    color:#1e40af;
    margin-bottom:11pt;
    border-radius:0 3pt 3pt 0;
    line-height:1.7;
}

/* ── MAIN TABLE ── */
.dt { width:100%; border-collapse:collapse; font-size:7.5pt; }

.dt thead tr th {
    background:#1e293b;
    color:#e2e8f0;
    font-size:6.5pt;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.3pt;
    padding:5pt 4pt;
    text-align:center;
    white-space:nowrap;
    border-right:1pt solid #334155;
}
.dt thead tr th:last-child  { border-right:none; }
.dt thead tr th.th-l { text-align:left; padding-left:7pt; }
.dt thead tr th.we   { background:#2d3f55; color:#64748b; }

.dt tbody tr td {
    padding:5pt 4pt;
    border-bottom:1pt solid #f1f5f9;
    border-right:1pt solid #f1f5f9;
    text-align:center;
    vertical-align:middle;
    font-size:7.5pt;
}
.dt tbody tr td:last-child { border-right:none; }
.dt tbody tr:nth-child(even) td { background:#f8fafc; }
.dt tbody tr:last-child td { border-bottom: none; }

.td-l { text-align:left !important; padding-left:7pt !important; }
.td-r { text-align:right !important; padding-right:6pt !important; }
.we   { background:#f5f7fa !important; }

/* ── EMPLOYEE CELL ── */
.emp-name { font-size:8pt; font-weight:700; color:#0f172a; }
.emp-pos  { font-size:6.5pt; color:#94a3b8; margin-top:1pt; }

/* ── ATTENDANCE BADGES ── */
.att { display:inline-block; padding:2pt 5pt; border-radius:3pt; font-size:7pt; font-weight:700; min-width:18pt; text-align:center; line-height:1.3; }
.att-ok  { background:#dcfce7; color:#166534; }
.att-no  { background:#fee2e2; color:#b91c1c; }
.att-sub { font-size:6pt; color:#94a3b8; display:block; margin-top:1pt; }

/* ── FOOTER ROW ── */
.dt tfoot td {
    background:#0f172a !important;
    color:#e2e8f0;
    font-weight:700;
    font-size:7.5pt;
    padding:6pt 4pt;
    text-align:center;
    border-right:1pt solid #334155;
    border-top:none;
}
.dt tfoot td:last-child { border-right:none; }
.tf-lbl { text-align:right !important; padding-right:8pt !important; color:#94a3b8 !important; font-weight:400 !important; font-size:6.5pt !important; text-transform:uppercase; letter-spacing:.3pt; }

/* ── HELPERS ── */
.muted  { color:#94a3b8; }
.green  { color:#15803d; font-weight:700; }
.red    { color:#b91c1c; font-weight:700; }
.bold   { font-weight:700; }

/* ── NOTE ── */
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
.sign-section { margin-top:20pt; }
.sign-t { width:50%; border-collapse:collapse; margin-left:auto; }
.sign-t td { text-align:center; padding:0 12pt; vertical-align:top; }
.sign-box {
    border:1pt solid #e2e8f0;
    border-radius:4pt;
    padding:8pt 10pt 6pt;
    background:#f8fafc;
}
.sign-space { height:36pt; }
.sign-name  { font-size:7.5pt; font-weight:700; color:#1e293b; margin-top:5pt; padding-top:5pt; border-top:1pt solid #94a3b8; }
.sign-role  { font-size:6.5pt; color:#64748b; margin-top:2pt; }

/* ── FOOTER ── */
.page-footer { margin-top:14pt; padding-top:6pt; border-top:1pt solid #e2e8f0; }
.footer-t { width:100%; border-collapse:collapse; }
.footer-t td { font-size:6pt; color:#94a3b8; padding:0; vertical-align:middle; }
</style>
</head>
<body>

<div style="padding:0;">

{{-- ══════════════════ HEADER ══════════════════ --}}
<div class="header">
    <table class="header-t">
        <tr>
            <td style="width:58%;">
                <div class="co-logo">ACEGARD</div>
                <div class="co-sub">
                    {{ $branch->name ?? '-' }}{{ $branch->city ? ' · ' . $branch->city : '' }}<br>
                    Sistem Manajemen Internal
                </div>
            </td>
            <td style="width:42%;">
                <div class="doc-title">LAPORAN UANG MAKAN</div>
                <div class="doc-meta">
                    Mingguan &nbsp;·&nbsp; {{ $startDate->isoFormat('D MMM Y') }} – {{ $endDate->isoFormat('D MMM Y') }}<br>
                    Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }} WIB
                </div>
            </td>
        </tr>
    </table>
</div>

<div style="padding:0 22pt;">

{{-- ══════════════════ SUMMARY CARDS ══════════════════ --}}
<table class="sum-t">
    <tr>
        <td style="width:22%;">
            <div class="sum-card">
                <div class="sum-lbl">Total Karyawan</div>
                <div class="sum-val" style="color:#1d4ed8;">{{ $mealRows->count() }}</div>
                <div class="sum-sub">karyawan aktif</div>
            </div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:22%;">
            <div class="sum-card">
                <div class="sum-lbl">Hari Masuk</div>
                <div class="sum-val" style="color:#0f172a;">{{ $totalPresentDays }}</div>
                <div class="sum-sub">total kehadiran</div>
            </div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:22%;">
            <div class="sum-card">
                <div class="sum-lbl">Tidak Masuk</div>
                <div class="sum-val" style="color:#b91c1c;">{{ $totalAbsentDays }}</div>
                <div class="sum-sub">tidak dapat uang makan</div>
            </div>
        </td>
        <td style="width:2%;"></td>
        <td style="width:28%;">
            <div class="sum-card-dark">
                <div class="sum-lbl" style="color:#64748b;">Total Uang Makan</div>
                <div class="sum-val" style="color:#4ade80; font-size:11pt;">Rp{{ number_format($grandTotal, 0, ',', '.') }}</div>
                <div class="sum-sub" style="color:#22c55e;">yang dibayarkan</div>
            </div>
        </td>
    </tr>
</table>

{{-- ══════════════════ SECTION HEADER ══════════════════ --}}
<div class="sec-hdr">
    <div class="sec-title">Detail Kehadiran & Uang Makan</div>
    <div class="sec-sub">
        {{ $startDate->isoFormat('D MMMM Y') }} – {{ $endDate->isoFormat('D MMMM Y') }}
        &nbsp;·&nbsp; {{ $branch->name ?? '-' }}
        &nbsp;·&nbsp; {{ $mealRows->count() }} karyawan
    </div>
</div>

{{-- ══════════════════ INFO ══════════════════ --}}
<div class="info-box">
    <strong>Ketentuan:</strong>&nbsp;
    Teknisi <strong>Rp50.000/hari</strong> masuk &nbsp;·&nbsp;
    Kenek <strong>Rp35.000/hari</strong> masuk &nbsp;·&nbsp;
    Marketing dan posisi lain tidak mendapat uang makan &nbsp;·&nbsp;
    Karyawan tidak hadir tidak mendapat uang makan pada hari tersebut.
</div>

{{-- ══════════════════ MAIN TABLE ══════════════════ --}}
@php $dayCount = count($weekDays); @endphp

<table class="dt">
    <thead>
        <tr>
            <th class="th-l" style="width:14pt;">#</th>
            <th class="th-l" style="min-width:80pt;">Nama Karyawan</th>
            @foreach($weekDays as $day)
                @php $isWe = in_array(\Carbon\Carbon::parse($day['date'])->dayOfWeek, [0,6]); @endphp
                <th class="{{ $isWe ? 'we' : '' }}" style="width:44pt;">
                    {{ $day['short'] }}<br>
                    <span style="font-size:6pt; opacity:.65;">{{ $day['day'] }}</span>
                </th>
            @endforeach
            <th style="width:34pt;">Masuk</th>
            <th style="width:32pt;">Tdk</th>
            <th style="width:62pt; text-align:right; padding-right:6pt;">Rate/Hari</th>
            <th style="width:76pt; text-align:right; padding-right:6pt;">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($mealRows as $index => $row)
            <tr>
                <td class="td-l muted" style="font-size:7pt;">{{ $index + 1 }}</td>
                <td class="td-l">
                    <div class="emp-name">{{ $row['employee']->name }}</div>
                    <div class="emp-pos">{{ $row['job_position'] ?? '-' }}</div>
                </td>

                @foreach($weekDays as $day)
                    @php
                        $daily     = $row['daily'][$day['date']] ?? null;
                        $isPresent = $daily['is_present'] ?? false;
                        $status    = $daily['status'] ?? null;
                        $isWe      = in_array(\Carbon\Carbon::parse($day['date'])->dayOfWeek, [0,6]);
                    @endphp
                    <td class="{{ $isWe ? 'we' : '' }}">
                        @if($isPresent)
                            <span class="att att-ok">H</span>
                        @elseif($status)
                            <span class="att att-no">{{ strtoupper(substr($status,0,1)) }}</span>
                            <span class="att-sub">{{ ucfirst($status) }}</span>
                        @else
                            <span class="muted" style="font-size:7pt;">—</span>
                        @endif
                    </td>
                @endforeach

                <td class="green">{{ $row['present_days'] }}</td>
                <td class="red">{{ $row['absent_days'] }}</td>

                <td class="td-r muted" style="font-size:7pt;">
                    @if(!(bool)($row['employee']->meal_allowance_enabled ?? true))
                        <span style="color:#b91c1c; font-size:6.5pt;">—</span>
                    @else
                        Rp{{ number_format($row['daily_rate'], 0, ',', '.') }}
                    @endif
                </td>

                <td class="td-r green" style="font-size:8pt;">
                    Rp{{ number_format($row['total_allowance'], 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $dayCount + 6 }}" style="text-align:center; padding:18pt; color:#94a3b8; font-style:italic; font-size:7.5pt;">
                    Tidak ada data karyawan pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>

    <tfoot>
        <tr>
            <td colspan="2" class="tf-lbl">Grand Total</td>
            @foreach($weekDays as $day)
                @php $isWe = in_array(\Carbon\Carbon::parse($day['date'])->dayOfWeek, [0,6]); @endphp
                <td class="{{ $isWe ? 'we' : '' }}" style="{{ $isWe ? 'background:#253347!important;' : '' }}"></td>
            @endforeach
            <td style="color:#4ade80;">{{ $totalPresentDays }}</td>
            <td style="color:#f87171;">{{ $totalAbsentDays }}</td>
            <td></td>
            <td class="td-r" style="color:#4ade80;">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

{{-- ══════════════════ NOTE ══════════════════ --}}
<div class="note">
    Data kehadiran dihitung otomatis dari absensi harian periode Senin–Minggu.
    Karyawan tanpa data absensi dianggap tidak hadir dan tidak mendapat uang makan pada hari tersebut.
</div>

{{-- ══════════════════ SIGNATURES ══════════════════ --}}
<div class="sign-section">
    <table class="sign-t">
        <tr>
            <td>
                <div class="sign-box">
                    <div class="sign-space"></div>
                    <div class="sign-name">Mengetahui</div>
                    <div class="sign-role">Pimpinan Cabang · {{ $branch->name ?? '-' }}</div>
                </div>
            </td>
            <td>
                <div class="sign-box">
                    <div class="sign-space"></div>
                    <div class="sign-name">Disiapkan oleh</div>
                    <div class="sign-role">Admin HRD · {{ $branch->name ?? '-' }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ══════════════════ FOOTER ══════════════════ --}}
<div class="page-footer">
    <table class="footer-t">
        <tr>
            <td>ACEGARD Indonesia &nbsp;·&nbsp; Sistem Manajemen Internal</td>
            <td style="text-align:right;">
                Uang Makan Mingguan &nbsp;·&nbsp;
                {{ $startDate->isoFormat('D MMM') }}–{{ $endDate->isoFormat('D MMM Y') }} &nbsp;·&nbsp;
                {{ $branch->name ?? '-' }}
            </td>
        </tr>
    </table>
</div>

</div>{{-- /padding --}}
</div>

</body>
</html>
