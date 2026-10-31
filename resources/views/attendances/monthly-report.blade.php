@extends('dashboard.index')

@section('title', 'Laporan Gaji Bulanan')
@section('route-breadcrumb', 'attendances.monthlyReport')
@section('breadcrumb', 'Laporan Gaji Bulanan')

@section('content')

@include('partials.form-errors')

@php
    $grandMonthlySalary = $report->sum('monthly_salary');
    $grandDailyProrate  = $report->sum('daily_prorate');
    $grandBaseGaji      = $report->sum('base_gaji');
    $grandDeduction     = $report->sum('deduction_amount');
    $grandNetSalary     = $report->sum('salary');
    $grandTambahan      = $report->sum('total_tambahan');
    $grandPotonganAdj   = $report->sum('total_potongan');
@endphp

{{-- Page Title --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Laporan Gaji Bulanan</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Rekap gaji berdasarkan kehadiran bulanan karyawan</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <x-ui.button variant="outline" size="sm" :href="route('attendances.monthlyReport.export', array_merge(request()->query(), ['format'=>'excel']))">
            <iconify-icon icon="mdi:microsoft-excel" class="me-1"></iconify-icon> Excel
        </x-ui.button>
        <x-ui.button variant="danger-outline" size="sm" :href="route('attendances.monthlyReport.export', array_merge(request()->query(), ['format'=>'pdf']))">
            <iconify-icon icon="mdi:file-pdf-box" class="me-1"></iconify-icon> PDF
        </x-ui.button>
        <x-ui.button variant="outline" size="sm" href="{{ route('attendances.report') }}">Lap. Absensi</x-ui.button>
        <x-ui.button variant="outline" size="sm" href="{{ route('attendances.daily') }}">Input Harian</x-ui.button>
    </div>
</div>

{{-- Filter --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('attendances.monthlyReport') }}" class="row g-3 align-items-end">
        <div class="col-12 col-sm-6 col-md-4">
            <x-ui.form-field label="Bulan" name="month">
                <input type="month" name="month" value="{{ $month }}" class="form-control">
            </x-ui.form-field>
        </div>
        <div class="col-12 col-sm-6 col-md-5">
            <x-ui.form-field label="Cabang" name="branch_id">
                <select name="branch_id" class="form-select">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (int)$branchId === (int)$branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}{{ $branch->city ? ' — ' . $branch->city : '' }}
                        </option>
                    @endforeach
                </select>
            </x-ui.form-field>
        </div>
        <div class="col-12 col-sm-6 col-md-3 d-flex gap-2">
            <x-ui.button variant="primary" type="submit">Filter</x-ui.button>
            @if(request()->query())
                <x-ui.button variant="outline" href="{{ route('attendances.monthlyReport') }}">Reset</x-ui.button>
            @endif
        </div>
    </form>
</x-ui.card>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-3">
        <x-ui.card class="h-100">
            <p class="text-muted mb-1" style="font-size:12px;">Periode</p>
            <p class="fw-bold mb-0" style="font-size:14px;">
                {{ $startDate->format('d M') }} – {{ $endDate->format('d M Y') }}
            </p>
            <p class="text-muted mb-0" style="font-size:11px;">{{ $daysInMonth }} hari dalam bulan ini</p>
        </x-ui.card>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <x-ui.stats-card label="Total Karyawan" :value="$report->count()" color="blue" accent class="h-100" />
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <x-ui.stats-card label="Total Rate/Hari (Prorate)" :value="'Rp'.number_format($grandDailyProrate,0,',','.')" color="purple" accent class="h-100" />
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <x-ui.stats-card label="Total Prorate (Sebelum Potongan)" :value="'Rp'.number_format($grandBaseGaji,0,',','.')" color="purple" accent class="h-100" />
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <x-ui.stats-card label="Total Tambahan" :value="'+Rp'.number_format($grandTambahan,0,',','.')" color="green" accent class="h-100" />
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <x-ui.stats-card label="Total Potongan" :value="'Rp'.number_format($grandDeduction + $grandPotonganAdj,0,',','.')" color="red" accent class="h-100" />
    </div>
    <div class="col-6 col-md-4 col-lg-3">
        <x-ui.stats-card label="Total Gaji Bersih" :value="'Rp'.number_format($grandNetSalary,0,',','.')" color="green" accent class="h-100" />
    </div>
</div>

{{-- Info --}}
<x-ui.alert type="info" class="mb-4">
    <div class="d-flex gap-2" style="font-size:13px;">
        <iconify-icon icon="mdi:information" style="font-size:18px; flex-shrink:0;"></iconify-icon>
        <span>
            Rate/Hari = Gaji Pokok ÷ <strong>({{ $daysInMonth }} hari − Jatah Libur)</strong> ·
            Gaji Bersih = Rate × <strong>Total Masuk</strong> (hari hadir fisik) ·
            Dibulatkan ke kelipatan Rp1.000.
        </span>
    </div>
</x-ui.alert>

{{-- Adjustment Form --}}
<x-ui.card class="mb-4">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-semibold">Tambahan & Potongan Gaji</span>
                <small class="text-muted ms-2">— {{ $startDate->isoFormat('MMMM Y') }}</small>
            </div>
            <x-ui.badge variant="warning">Input per Karyawan</x-ui.badge>
        </div>
    @endslot
    <form method="POST" action="{{ route('attendances.monthlyReport.adjustments') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="branch_id" value="{{ $branchId }}">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width:900px; font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:40px;">#</th>
                        <th style="min-width:160px;">Nama</th>
                        <th class="text-center" style="color:#15803d; min-width:120px;">Uang Motor</th>
                        <th class="text-center" style="color:#15803d; min-width:120px;">Uang Makan</th>
                        <th class="text-center" style="color:#b91c1c; min-width:120px;">Rokok</th>
                        <th class="text-center" style="color:#b91c1c; min-width:120px;">Kasbon</th>
                        <th class="text-center" style="color:#b91c1c; min-width:120px;">Pot. Absen</th>
                    </tr>
                    <tr class="table-light" style="font-size:11px; color:#9ca3af;">
                        <td colspan="2" class="ps-3">Tambahan (+)</td>
                        <td class="text-center" colspan="2" style="color:#15803d;">↑ Menambah Gaji</td>
                        <td class="text-center" colspan="3" style="color:#b91c1c;">↓ Memotong Gaji</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report as $index => $row)
                        <input type="hidden" name="adjustments[{{ $index }}][employee_id]" value="{{ $row['employee']->id }}">
                        <tr>
                            <td class="ps-3 text-muted" style="font-size:12px;">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $row['employee']->name }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $row['employee']->job_position ?? '-' }}</div>
                            </td>
                            @foreach(['uang_motor','uang_makan'] as $field)
                            <td class="text-center">
                                <input type="number" name="adjustments[{{ $index }}][{{ $field }}]"
                                       class="form-control form-control-sm text-center {{ $errors->has("adjustments.$index.$field") ? 'is-invalid' : '' }}"
                                       value="{{ old("adjustments.$index.$field", $row[$field] > 0 ? $row[$field] : '') }}"
                                       min="0" placeholder="0" style="max-width:110px; margin:0 auto;">
                            </td>
                            @endforeach
                            @foreach(['potongan_rokok','kasbon','potongan_absen'] as $field)
                            <td class="text-center">
                                <input type="number" name="adjustments[{{ $index }}][{{ $field }}]"
                                       class="form-control form-control-sm text-center {{ $errors->has("adjustments.$index.$field") ? 'is-invalid' : '' }}"
                                       value="{{ old("adjustments.$index.$field", $row[$field] > 0 ? $row[$field] : '') }}"
                                       min="0" placeholder="0" style="max-width:110px; margin:0 auto;">
                            </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex gap-2">
            <x-ui.button variant="primary" type="submit" class="px-4">
                <iconify-icon icon="mdi:content-save" class="me-1"></iconify-icon> Simpan Tambahan & Potongan
            </x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Table --}}
<style>
    /* table-layout:fixed + colgroup eksplisit supaya lebar tiap kolom presisi (bukan
       tebakan browser) — tanpa ini offset sticky left: 44px pada kolom Nama bisa meleset
       dari lebar asli kolom #, muncul celah putih tipis di antara keduanya saat scroll. */
    .jp-report-table { table-layout: fixed; }
    /* Kolom # dan Nama dibekukan (sticky) saat scroll horizontal, supaya baris karyawan
       tetap terbaca meski tabel punya banyak kolom rincian di kanan. */
    .jp-report-table thead { position: sticky; top: 0; z-index: 5; }
    .jp-report-table th:nth-child(1),
    .jp-report-table td:nth-child(1) { position: sticky; left: 0; z-index: 3; width: 44px; }
    .jp-report-table th:nth-child(2),
    .jp-report-table td:nth-child(2) { position: sticky; left: 44px; z-index: 3; width: 170px; }
    .jp-report-table thead th:nth-child(1),
    .jp-report-table thead th:nth-child(2) { z-index: 6; }
    .jp-report-table tbody td:nth-child(1),
    .jp-report-table tbody td:nth-child(2) { background-color: #fff; }
    .jp-report-table tbody tr:hover td:nth-child(1),
    .jp-report-table tbody tr:hover td:nth-child(2) { background-color: #f5f5f7; }
    .jp-report-table tfoot td:nth-child(1),
    .jp-report-table tfoot td:nth-child(2) { background-color: #e2e3e5; }
    .jp-report-table .grp-att   { background: #1e3a5f; color: #93c5fd; }
    .jp-report-table .grp-calc  { background: #14532d; color: #86efac; }
    .jp-report-table .grp-cut1  { background: #7f1d1d; color: #fca5a5; }
    .jp-report-table .grp-bonus { background: #166534; color: #bbf7d0; }
    .jp-report-table .grp-add   { background: #166534; color: #bbf7d0; }
    .jp-report-table .grp-cut2  { background: #7f1d1d; color: #fca5a5; }
    .jp-report-table .grp-final { background: #064e3b; color: #a7f3d0; }
    .jp-report-table thead tr.hdr-grp th { font-size: 12px; }
    .jp-report-table thead tr.hdr-sub th { font-size: 11.5px; font-weight: 600; }
</style>
<x-ui.card>
    <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
        <table class="table table-hover align-middle mb-0 jp-report-table" style="width:2175px; font-size:13px; white-space:nowrap;">
            <colgroup>
                <col style="width:44px;">
                <col style="width:170px;">
                <col style="width:52px;"><col style="width:52px;"><col style="width:52px;"><col style="width:52px;">
                <col style="width:52px;"><col style="width:52px;"><col style="width:52px;"><col style="width:52px;">
                <col style="width:110px;">
                <col style="width:95px;">
                <col style="width:80px;">
                <col style="width:120px;">
                <col style="width:90px;">
                <col style="width:110px;">
                <col style="width:80px;">
                <col style="width:120px;">
                <col style="width:100px;">
                <col style="width:100px;">
                <col style="width:95px;">
                <col style="width:95px;">
                <col style="width:95px;">
                <col style="width:120px;">
                <col style="width:135px;">
            </colgroup>
            <thead class="table-dark">
                <tr class="hdr-grp">
                    <th class="ps-3" rowspan="2">#</th>
                    <th rowspan="2">Nama</th>
                    <th class="text-center grp-att" colspan="8">Kehadiran</th>
                    <th class="text-center grp-calc" colspan="4">Kalkulasi Dasar</th>
                    <th class="text-center grp-cut1" colspan="2">Potongan Absen</th>
                    <th class="text-center grp-bonus" colspan="2">Bonus Libur</th>
                    <th class="text-center grp-add" colspan="2">Tambahan Manual</th>
                    <th class="text-center grp-cut2" colspan="3">Potongan Manual</th>
                    <th class="text-center grp-final" colspan="2">Hasil Akhir</th>
                </tr>
                <tr class="hdr-sub">
                    <th class="text-center">Masuk</th>
                    <th class="text-center">Libur</th>
                    <th class="text-center">Jatah</th>
                    <th class="text-center">Dibayar</th>
                    <th class="text-center">Lebih</th>
                    <th class="text-center">Sakit</th>
                    <th class="text-center">Izin</th>
                    <th class="text-center">Alpha</th>
                    <th class="text-end">Gaji Pokok</th>
                    <th class="text-end">Rate/Hari</th>
                    <th class="text-center">Total Masuk</th>
                    <th class="text-end">Sebelum Potongan</th>
                    <th class="text-center">Alpha+Lebih</th>
                    <th class="text-end">Potongan</th>
                    <th class="text-center">Sisa Libur</th>
                    <th class="text-end">Gaji Tambahan</th>
                    <th class="text-end">+ Motor</th>
                    <th class="text-end">+ Makan</th>
                    <th class="text-end">- Rokok</th>
                    <th class="text-end">- Kasbon</th>
                    <th class="text-end">- Pot.Absen</th>
                    <th class="text-end">Sebelum Bulat</th>
                    <th class="text-end pe-3">Gaji Bersih (Bulat)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report as $row)
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12px;">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $row['employee']->name }}</div>
                            <div class="text-muted" style="font-size:11.5px;">{{ $row['employee']->job_position ?? '-' }}</div>
                        </td>
                        <td class="text-center"><x-ui.badge variant="success" size="sm">{{ $row['present'] }}</x-ui.badge></td>
                        <td class="text-center"><x-ui.badge variant="neutral" size="sm">{{ $row['off'] }}</x-ui.badge></td>
                        <td class="text-center"><x-ui.badge variant="neutral" size="sm">{{ $row['off_quota'] }}</x-ui.badge></td>
                        <td class="text-center"><x-ui.badge variant="success" size="sm">{{ $row['paid_off'] }}</x-ui.badge></td>
                        <td class="text-center">
                            <x-ui.badge :variant="$row['excess_off'] > 0 ? 'danger' : 'neutral'" size="sm">{{ $row['excess_off'] }}</x-ui.badge>
                        </td>
                        <td class="text-center"><x-ui.badge variant="neutral" size="sm">{{ $row['sick'] }}</x-ui.badge></td>
                        <td class="text-center"><x-ui.badge variant="warning" size="sm">{{ $row['permission'] }}</x-ui.badge></td>
                        <td class="text-center">
                            <x-ui.badge :variant="$row['absent'] > 0 ? 'danger' : 'neutral'" size="sm">{{ $row['absent'] }}</x-ui.badge>
                        </td>
                        <td class="text-end fw-semibold">Rp{{ number_format($row['monthly_salary'], 0, ',', '.') }}</td>
                        <td class="text-end text-muted" style="font-size:12px;">Rp{{ number_format($row['daily_prorate'], 0, ',', '.') }}</td>
                        <td class="text-center">
                            <x-ui.badge variant="success" size="sm">{{ $row['present'] }} hr</x-ui.badge>
                        </td>
                        <td class="text-end fw-semibold">Rp{{ number_format($row['base_gaji'], 0, ',', '.') }}</td>
                        <td class="text-center">
                            <x-ui.badge :variant="$row['deduction_days'] > 0 ? 'danger' : 'neutral'" size="sm">{{ $row['deduction_days'] }} hr</x-ui.badge>
                        </td>
                        <td class="text-end text-danger">
                            {{ $row['deduction_amount'] > 0 ? 'Rp'.number_format($row['deduction_amount'],0,',','.') : '—' }}
                        </td>
                        <td class="text-center">
                            <x-ui.badge :variant="$row['unused_off'] > 0 ? 'success' : 'neutral'" size="sm">{{ $row['unused_off'] }} hr</x-ui.badge>
                        </td>
                        <td class="text-end fw-semibold text-success">
                            {{ $row['bonus_amount'] > 0 ? '+Rp'.number_format($row['bonus_amount'],0,',','.') : '—' }}
                        </td>
                        <td class="text-end text-success" style="font-size:12px;">{{ $row['uang_motor'] > 0 ? '+Rp'.number_format($row['uang_motor'],0,',','.') : '—' }}</td>
                        <td class="text-end text-success" style="font-size:12px;">{{ $row['uang_makan'] > 0 ? '+Rp'.number_format($row['uang_makan'],0,',','.') : '—' }}</td>
                        <td class="text-end text-danger" style="font-size:12px;">{{ $row['potongan_rokok'] > 0 ? '-Rp'.number_format($row['potongan_rokok'],0,',','.') : '—' }}</td>
                        <td class="text-end text-danger" style="font-size:12px;">{{ $row['kasbon'] > 0 ? '-Rp'.number_format($row['kasbon'],0,',','.') : '—' }}</td>
                        <td class="text-end text-danger" style="font-size:12px;">{{ $row['potongan_absen'] > 0 ? '-Rp'.number_format($row['potongan_absen'],0,',','.') : '—' }}</td>
                        <td class="text-end text-muted" style="font-size:12px;">
                            Rp{{ number_format($row['salary_raw'], 0, ',', '.') }}
                        </td>
                        <td class="text-end pe-3 fw-bold text-success">Rp{{ number_format($row['salary'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="26" class="py-5"><x-ui.empty-state title="Tidak ada data karyawan" /></td></tr>
                @endforelse
            </tbody>
            @if($report->count())
            <tfoot class="table-secondary fw-bold">
                <tr>
                    <td colspan="2" class="text-end ps-3">Total</td>
                    <td class="text-center">{{ $report->sum('present') }}</td>
                    <td class="text-center">{{ $report->sum('off') }}</td>
                    <td></td>
                    <td class="text-center">{{ $report->sum('paid_off') }}</td>
                    <td class="text-center">{{ $report->sum('excess_off') }}</td>
                    <td class="text-center">{{ $report->sum('sick') }}</td>
                    <td class="text-center">{{ $report->sum('permission') }}</td>
                    <td class="text-center">{{ $report->sum('absent') }}</td>
                    <td class="text-end">Rp{{ number_format($grandMonthlySalary, 0, ',', '.') }}</td>
                    <td class="text-end">Rp{{ number_format($grandDailyProrate, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $report->sum('present') }} hr</td>
                    <td class="text-end">Rp{{ number_format($report->sum('base_gaji'), 0, ',', '.') }}</td>
                    <td class="text-center">{{ $report->sum('deduction_days') }} hr</td>
                    <td class="text-end text-danger">Rp{{ number_format($grandDeduction, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $report->sum('unused_off') }} hr</td>
                    <td class="text-end text-success">+Rp{{ number_format($report->sum('bonus_amount'), 0, ',', '.') }}</td>
                    <td class="text-end text-success">+Rp{{ number_format($report->sum('uang_motor'), 0, ',', '.') }}</td>
                    <td class="text-end text-success">+Rp{{ number_format($report->sum('uang_makan'), 0, ',', '.') }}</td>
                    <td class="text-end text-danger">-Rp{{ number_format($report->sum('potongan_rokok'), 0, ',', '.') }}</td>
                    <td class="text-end text-danger">-Rp{{ number_format($report->sum('kasbon'), 0, ',', '.') }}</td>
                    <td class="text-end text-danger">-Rp{{ number_format($report->sum('potongan_absen'), 0, ',', '.') }}</td>
                    <td class="text-end text-muted">Rp{{ number_format($report->sum('salary_raw'), 0, ',', '.') }}</td>
                    <td class="text-end text-success pe-3">Rp{{ number_format($grandNetSalary, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="border-top p-3">
        <small class="text-muted">
            Gaji Bersih = (Gaji Pokok ÷ ({{ $daysInMonth }} hari − Jatah Libur)) × Total Masuk &nbsp;·&nbsp;
            Dibulatkan ke kelipatan Rp1.000
        </small>
    </div>
</x-ui.card>

@endsection
