@extends('layout.index')

@section('title', 'Dashboard')
@section('route-breadcrumb', route('dashboard'))
@section('breadcrumb', 'Dashboard')

@section('content')

@php
    // Defaults — nilai sesungguhnya dikirim dari DashboardController.
    // Cek struktur spesifik agar tidak bentrok dengan $summary dari controller lain.
    if (!isset($summary) || !is_array($summary) || !array_key_exists('car', $summary)) {
        $summary = ['car' => ['year' => ['total'=>0,'count'=>0,'paid'=>0]], 'building' => ['year' => ['total'=>0,'count'=>0,'paid'=>0]], 'ppf' => ['year' => ['total'=>0,'count'=>0,'paid'=>0]]];
    }
    $monthly        ??= [];
    $yearly         ??= [];
    $years          ??= collect([now()->year]);
    $selectedYear   ??= now()->year;
    $selectedBranch ??= null;
    $isSuperadmin   ??= false;
    $branches       ??= collect();
    $products       ??= collect();
    $totalUsers     ??= 0;
    $totalCustomers ??= 0;
    $totalBranches  ??= 0;

    $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    $carY  = $summary['car']['year'];
    $bldY  = $summary['building']['year'];
    $ppfY  = $summary['ppf']['year'];

    $totalAmt  = $carY['total']  + $bldY['total']  + $ppfY['total'];
    $totalCnt  = $carY['count']  + $bldY['count']  + $ppfY['count'];
    $totalPaid = $carY['paid']   + $bldY['paid']   + $ppfY['paid'];
    $gt        = $totalAmt ?: 1;

    $fmtRp = fn(float $v): string =>
        $v >= 1_000_000_000 ? 'Rp '.number_format($v/1_000_000_000, 1).'M'
        : ($v >= 1_000_000  ? 'Rp '.number_format($v/1_000_000, 1).'Jt'
        :                     'Rp '.number_format($v, 0, ',', '.'));

    $monthlyCarAmt = array_column(array_values($monthly), 'car_amount');
    $monthlyBldAmt = array_column(array_values($monthly), 'building_amount');
    $monthlyPpfAmt = array_column(array_values($monthly), 'ppf_amount');
    $yearLabels    = array_keys($yearly);
    $yearlyCarAmt  = array_column(array_values($yearly), 'car_amount');
    $yearlyBldAmt  = array_column(array_values($yearly), 'building_amount');
    $yearlyPpfAmt  = array_column(array_values($yearly), 'ppf_amount');
@endphp

{{-- ─ Palet warna terpusat ──────────────────────────────────────────────────── --}}
<style>
    :root {
        --c-total : #6366f1;
        --c-car   : #3b82f6;
        --c-bld   : #f59e0b;
        --c-ppf   : #10b981;
        --c-users : #8b5cf6;
        --c-cust  : #06b6d4;
        --c-branch: #f97316;

        --c-total-bg : rgba(99,102,241,.10);
        --c-car-bg   : rgba(59,130,246,.10);
        --c-bld-bg   : rgba(245,158,11,.10);
        --c-ppf-bg   : rgba(16,185,129,.10);
        --c-users-bg : rgba(139,92,246,.10);
        --c-cust-bg  : rgba(6,182,212,.10);
        --c-branch-bg: rgba(249,115,22,.10);
    }

    /* Card accent kiri */
    .dash-card          { border: 1px solid #f1f5f9 !important; }
    .dash-card-total    { border-left: 4px solid var(--c-total)  !important; }
    .dash-card-car      { border-left: 4px solid var(--c-car)    !important; }
    .dash-card-building { border-left: 4px solid var(--c-bld)    !important; }
    .dash-card-ppf      { border-left: 4px solid var(--c-ppf)    !important; }
    .dash-card-users    { border-left: 4px solid var(--c-users)  !important; }
    .dash-card-cust     { border-left: 4px solid var(--c-cust)   !important; }
    .dash-card-branch   { border-left: 4px solid var(--c-branch) !important; }

    /* Icon wrapper */
    .dash-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: 20px;
    }

    /* Legend dot */
    .legend-dot {
        width: 9px; height: 9px;
        border-radius: 3px;
        display: inline-block; flex-shrink: 0;
    }

    /* Divider tipis */
    .dash-divider { border-top: 1px solid #f1f5f9; margin-top: 12px; padding-top: 10px; }

    /* Padding seragam untuk semua card body */
    .p-card-chart { padding: 12px 16px 8px; }

    /* Donut legend row */
    .donut-legend-row {
        display: grid;
        grid-template-columns: 9px 1fr auto auto;
        align-items: center;
        gap: 8px;
        padding: 9px 0;
    }
</style>

{{-- ─ Filter Bar ───────────────────────────────────────────────────────────── --}}
<x-ui.card class="mb-4 dash-card">
    <form method="GET" action="{{ route('dashboard') }}"
          class="w-100 d-flex align-items-center gap-0">

        {{-- Kiri: icon + teks --}}
        <div class="d-flex align-items-center gap-2 pe-3">
            <div style="width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:var(--c-total-bg);color:var(--c-total);font-size:14px;flex-shrink:0">
                <iconify-icon icon="mdi:filter-variant"></iconify-icon>
            </div>
            <span class="fw-semibold" style="font-size:13px;color:#1e293b">Filter</span>
        </div>

        {{-- Divider --}}
        <div style="width:1px;height:22px;background:#e2e8f0;flex-shrink:0"></div>

        {{-- Tahun --}}
        <div class="d-flex align-items-center gap-2 px-3">
            <span style="font-size:11px;font-weight:500;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Tahun</span>
            <iconify-icon icon="mdi:calendar-blank-outline" style="font-size:13px;color:#94a3b8"></iconify-icon>
            <select name="year" class="border-0 fw-semibold p-0"
                    style="background:transparent;box-shadow:none;font-size:13px;color:#1e293b;cursor:pointer;outline:none"
                    onchange="this.form.submit()">
                @foreach ($years as $y)
                    <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        @if ($isSuperadmin)
            {{-- Divider --}}
            <div style="width:1px;height:22px;background:#e2e8f0;flex-shrink:0"></div>

            {{-- Cabang --}}
            <div class="d-flex align-items-center gap-2 px-3">
                <span style="font-size:11px;font-weight:500;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Cabang</span>
                <iconify-icon icon="mdi:map-marker-outline" style="font-size:13px;color:#94a3b8"></iconify-icon>
                <select name="branch_id" class="border-0 fw-semibold p-0"
                        style="background:transparent;box-shadow:none;font-size:13px;color:#1e293b;cursor:pointer;outline:none"
                        onchange="this.form.submit()">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $br)
                        <option value="{{ $br->id }}" {{ $selectedBranch?->id == $br->id ? 'selected' : '' }}>
                            {{ $br->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($selectedBranch)
                <span class="d-flex align-items-center gap-1 rounded-pill px-3"
                      style="height:26px;background:var(--c-total-bg);color:var(--c-total);font-size:11px;font-weight:500;white-space:nowrap">
                    <iconify-icon icon="mdi:map-marker" style="font-size:11px"></iconify-icon>
                    {{ $selectedBranch->name }}
                </span>
            @endif
        @endif

        {{-- Reset --}}
        <a href="{{ route('dashboard') }}"
           class="ms-auto d-flex align-items-center gap-1 text-decoration-none"
           style="height:30px;padding:0 14px;border-radius:8px;background:#f1f5f9;color:#64748b;font-size:12px;font-weight:500;white-space:nowrap;border:none;flex-shrink:0">
            <iconify-icon icon="mdi:refresh" style="font-size:13px"></iconify-icon> Reset
        </a>

    </form>
</x-ui.card>

{{-- ─ Info Bar (Superadmin only) ──────────────────────────────────────────── --}}
@if ($isSuperadmin)
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-4">
        <x-ui.card class="h-100 dash-card dash-card-users">
            <div class="d-flex align-items-center gap-3">
                <div class="dash-icon" style="background:var(--c-users-bg);color:var(--c-users)">
                    <iconify-icon icon="mdi:account-group-outline"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small">Total Users</div>
                    <div class="fw-bold fs-5 lh-1 mt-1">{{ number_format($totalUsers) }}</div>
                </div>
            </div>
        </x-ui.card>
    </div>
    <div class="col-12 col-sm-4">
        <x-ui.card class="h-100 dash-card dash-card-cust">
            <div class="d-flex align-items-center gap-3">
                <div class="dash-icon" style="background:var(--c-cust-bg);color:var(--c-cust)">
                    <iconify-icon icon="mdi:account-outline"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small">Customers</div>
                    <div class="fw-bold fs-5 lh-1 mt-1">{{ number_format($totalCustomers) }}</div>
                </div>
            </div>
        </x-ui.card>
    </div>
    <div class="col-12 col-sm-4">
        <x-ui.card class="h-100 dash-card dash-card-branch">
            <div class="d-flex align-items-center gap-3">
                <div class="dash-icon" style="background:var(--c-branch-bg);color:var(--c-branch)">
                    <iconify-icon icon="mdi:office-building-outline"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small">Cabang</div>
                    <div class="fw-bold fs-5 lh-1 mt-1">{{ number_format($totalBranches) }}</div>
                </div>
            </div>
        </x-ui.card>
    </div>
</div>
@endif

{{-- ─ Sales Summary Cards ───────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

    @php
        $statCards = [
            ['label' => 'Total Penjualan '.$selectedYear, 'icon' => 'mdi:chart-line',   'c' => 'total',    'amt' => $totalAmt,      'cnt' => $totalCnt,      'paid' => $totalPaid],
            ['label' => 'Penjualan Mobil',                'icon' => 'mdi:car-outline',           'c' => 'car',      'amt' => $carY['total'], 'cnt' => $carY['count'], 'paid' => $carY['paid']],
            ['label' => 'Penjualan Gedung',               'icon' => 'mdi:home-city-outline',    'c' => 'building', 'amt' => $bldY['total'], 'cnt' => $bldY['count'], 'paid' => $bldY['paid']],
            ['label' => 'Penjualan PPF',                  'icon' => 'mdi:shield-check-outline',  'c' => 'ppf',      'amt' => $ppfY['total'], 'cnt' => $ppfY['count'], 'paid' => $ppfY['paid']],
        ];
    @endphp

    @foreach ($statCards as $s)
    <div class="col-12 col-sm-6 col-xl-3">
        <x-ui.card class="h-100 dash-card dash-card-{{ $s['c'] }}">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <p class="text-muted small fw-medium mb-0">{{ $s['label'] }}</p>
                <div class="dash-icon" style="background:var(--c-{{ $s['c'] }}-bg);color:var(--c-{{ $s['c'] }})">
                    <iconify-icon icon="{{ $s['icon'] }}"></iconify-icon>
                </div>
            </div>
            <div class="fw-bold mb-1" style="font-size:1.5rem;color:var(--c-{{ $s['c'] }})">{{ $fmtRp($s['amt']) }}</div>
            <div class="text-muted small">{{ number_format($s['cnt']) }} transaksi</div>
            <div class="dash-divider d-flex justify-content-between align-items-center small">
                <span class="text-muted">Terkumpul</span>
                <span class="fw-semibold" style="color:var(--c-ppf)">{{ $fmtRp($s['paid']) }}</span>
            </div>
        </x-ui.card>
    </div>
    @endforeach

</div>

{{-- ─ Charts Row ────────────────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Monthly Chart ─ 8/12 --}}
    <div class="col-12 col-lg-8">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">Penjualan Bulanan {{ $selectedYear }}</div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-flex align-items-center gap-1 small text-muted">
                            <span class="legend-dot" style="background:var(--c-car)"></span>Mobil
                        </span>
                        <span class="d-flex align-items-center gap-1 small text-muted">
                            <span class="legend-dot" style="background:var(--c-bld)"></span>Gedung
                        </span>
                        <span class="d-flex align-items-center gap-1 small text-muted">
                            <span class="legend-dot" style="background:var(--c-ppf)"></span>PPF
                        </span>
                    </div>
                </div>
            @endslot
            <div class="p-card-chart">
                <div id="monthlyChart"></div>
            </div>
        </x-ui.card>
    </div>

    {{-- Donut ─ 4/12 --}}
    <div class="col-12 col-lg-4">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="ui-card-title">Distribusi {{ $selectedYear }}</div>
            @endslot
            {{-- Batasi lebar konten agar tidak melebar di layar besar/mobile --}}
            <div class="mx-auto mt-5">
                <div id="donutChart"></div>
                <div class="mt-2">
                    {{-- Mobil --}}
                    <div class="donut-legend-row" style="border-bottom:1px solid #f1f5f9">
                        <span class="legend-dot" style="background:var(--c-car)"></span>
                        <span class="small">Mobil</span>
                        <span class="small fw-semibold" style="color:var(--c-car)">{{ $carY['count'] }} order</span>
                        <span class="text-muted" style="font-size:11px;min-width:36px;text-align:right">
                            {{ number_format($carY['total'] / $gt * 100, 1) }}%
                        </span>
                    </div>
                    {{-- Gedung --}}
                    <div class="donut-legend-row" style="border-bottom:1px solid #f1f5f9">
                        <span class="legend-dot" style="background:var(--c-bld)"></span>
                        <span class="small">Gedung</span>
                        <span class="small fw-semibold" style="color:var(--c-bld)">{{ $bldY['count'] }} order</span>
                        <span class="text-muted" style="font-size:11px;min-width:36px;text-align:right">
                            {{ number_format($bldY['total'] / $gt * 100, 1) }}%
                        </span>
                    </div>
                    {{-- PPF --}}
                    <div class="donut-legend-row">
                        <span class="legend-dot" style="background:var(--c-ppf)"></span>
                        <span class="small">PPF</span>
                        <span class="small fw-semibold" style="color:var(--c-ppf)">{{ $ppfY['count'] }} order</span>
                        <span class="text-muted" style="font-size:11px;min-width:36px;text-align:right">
                            {{ number_format($ppfY['total'] / $gt * 100, 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

</div>

{{-- ─ Yearly Chart ──────────────────────────────────────────────────────────── --}}
@if (count($yearly) > 0)
<x-ui.card class="mb-4 dash-card">
    @slot('header')
        <div class="d-flex align-items-center justify-content-between">
            <div class="ui-card-title">Tren Penjualan Tahunan</div>
            <div class="d-flex align-items-center gap-3">
                <span class="d-flex align-items-center gap-1 small text-muted">
                    <span class="legend-dot" style="background:var(--c-car)"></span>Mobil
                </span>
                <span class="d-flex align-items-center gap-1 small text-muted">
                    <span class="legend-dot" style="background:var(--c-bld)"></span>Gedung
                </span>
                <span class="d-flex align-items-center gap-1 small text-muted">
                    <span class="legend-dot" style="background:var(--c-ppf)"></span>PPF
                </span>
            </div>
        </div>
    @endslot
    <div class="p-card-chart">
        <div id="yearlyChart"></div>
    </div>
</x-ui.card>
@endif

{{-- ─ Monthly Table ─────────────────────────────────────────────────────────── --}}
<x-ui.card class="mb-4 dash-card">
    @slot('header')
        <div class="ui-card-title">Rincian Bulanan {{ $selectedYear }}</div>
    @endslot

    {{-- Desktop --}}
    <div class="table-responsive d-none d-md-block px-3">
        <table class="table table-hover mb-0 align-middle" style="font-size:13px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-medium border-bottom">Bulan</th>
                    <th class="text-center py-3 fw-medium border-bottom" style="color:var(--c-car)">
                        <span class="legend-dot me-1" style="background:var(--c-car)"></span>Mobil
                    </th>
                    <th class="text-center py-3 fw-medium border-bottom" style="color:var(--c-bld)">
                        <span class="legend-dot me-1" style="background:var(--c-bld)"></span>Gedung
                    </th>
                    <th class="text-center py-3 fw-medium border-bottom" style="color:var(--c-ppf)">
                        <span class="legend-dot me-1" style="background:var(--c-ppf)"></span>PPF
                    </th>
                    <th class="text-end pe-4 py-3 text-muted fw-medium border-bottom">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthly as $m => $d)
                    @php
                        $rowTotal = $d['car_amount'] + $d['building_amount'] + $d['ppf_amount'];
                        $rowCount = $d['car_count']  + $d['building_count']  + $d['ppf_count'];
                        $isCurrent = ($m == now()->month && $selectedYear == now()->year);
                    @endphp
                    <tr style="{{ $isCurrent ? 'background:#fafafa' : '' }}">
                        <td class="ps-4 py-3 fw-medium">
                            {{ $months[$m-1] }} {{ $selectedYear }}
                            @if ($isCurrent)
                                <x-ui.badge variant="primary" size="sm" class="ms-1">Bulan Ini</x-ui.badge>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            @if ($d['car_count'])
                                <span class="fw-semibold" style="color:var(--c-car)">{{ $d['car_count'] }}x</span>
                                <span class="text-muted ms-1">{{ $fmtRp($d['car_amount']) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            @if ($d['building_count'])
                                <span class="fw-semibold" style="color:var(--c-bld)">{{ $d['building_count'] }}x</span>
                                <span class="text-muted ms-1">{{ $fmtRp($d['building_amount']) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            @if ($d['ppf_count'])
                                <span class="fw-semibold" style="color:var(--c-ppf)">{{ $d['ppf_count'] }}x</span>
                                <span class="text-muted ms-1">{{ $fmtRp($d['ppf_amount']) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 py-3 fw-semibold">
                            @if ($rowTotal > 0)
                                {{ $fmtRp($rowTotal) }}
                            @else
                                <span class="text-muted fw-normal">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#fafafa;font-size:13px;font-weight:600">
                    <td class="ps-4 py-3" style="border-top:2px solid #e5e7eb">Total {{ $selectedYear }}</td>
                    <td class="text-center py-3" style="border-top:2px solid #e5e7eb;color:var(--c-car)">
                        {{ $carY['count'] }}x
                        <span class="text-muted fw-normal ms-1">{{ $fmtRp($carY['total']) }}</span>
                    </td>
                    <td class="text-center py-3" style="border-top:2px solid #e5e7eb;color:var(--c-bld)">
                        {{ $bldY['count'] }}x
                        <span class="text-muted fw-normal ms-1">{{ $fmtRp($bldY['total']) }}</span>
                    </td>
                    <td class="text-center py-3" style="border-top:2px solid #e5e7eb;color:var(--c-ppf)">
                        {{ $ppfY['count'] }}x
                        <span class="text-muted fw-normal ms-1">{{ $fmtRp($ppfY['total']) }}</span>
                    </td>
                    <td class="text-end pe-4 py-3" style="border-top:2px solid #e5e7eb;color:var(--c-total)">
                        {{ $fmtRp($totalAmt) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Mobile --}}
    <div class="d-md-none">
        @php $hasData = false; @endphp
        @foreach ($monthly as $m => $d)
            @php
                $rowTotal = $d['car_amount'] + $d['building_amount'] + $d['ppf_amount'];
                $rowCount = $d['car_count']  + $d['building_count']  + $d['ppf_count'];
                $isCurrent = ($m == now()->month && $selectedYear == now()->year);
                if ($rowCount > 0) $hasData = true;
            @endphp
            @if ($rowCount > 0)
                <div class="px-3 py-3" style="border-bottom:1px solid #f1f5f9;{{ $isCurrent ? 'background:#fafafa' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold small">
                            {{ $months[$m-1] }} {{ $selectedYear }}
                            @if ($isCurrent)
                                <x-ui.badge variant="primary" size="sm" class="ms-1">Bulan Ini</x-ui.badge>
                            @endif
                        </span>
                        <span class="fw-bold small" style="color:var(--c-total)">{{ $fmtRp($rowTotal) }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        @if ($d['car_count'])
                            <span class="badge rounded-pill px-3 py-1" style="background:var(--c-car-bg);color:var(--c-car);font-size:11px">
                                Mobil {{ $d['car_count'] }}x · {{ $fmtRp($d['car_amount']) }}
                            </span>
                        @endif
                        @if ($d['building_count'])
                            <span class="badge rounded-pill px-3 py-1" style="background:var(--c-bld-bg);color:var(--c-bld);font-size:11px">
                                Gedung {{ $d['building_count'] }}x · {{ $fmtRp($d['building_amount']) }}
                            </span>
                        @endif
                        @if ($d['ppf_count'])
                            <span class="badge rounded-pill px-3 py-1" style="background:var(--c-ppf-bg);color:var(--c-ppf);font-size:11px">
                                PPF {{ $d['ppf_count'] }}x · {{ $fmtRp($d['ppf_amount']) }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
        @if (!$hasData)
            <x-ui.empty-state title="Belum ada data penjualan untuk {{ $selectedYear }}." />
        @endif
    </div>
</x-ui.card>

{{-- ─ Building Products (Superadmin only) ─────────────────────────────────── --}}
@if ($isSuperadmin && count($products))
<x-ui.card class="mb-4 dash-card">
    @slot('header')
        <div class="ui-card-title">Rekap Produk Building</div>
    @endslot
    <div class="row g-3">
        @foreach ($products as $p)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="rounded-3 p-3 h-100" style="background:#fafafa;border:1px solid #f1f5f9">
                    <x-ui.badge variant="warning" size="sm" class="mb-2">{{ $p->category_name }}</x-ui.badge>
                    <div class="fw-semibold small mb-3">{{ $p->name }}</div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-bold" style="color:var(--c-bld)">{{ number_format($p->total_meters, 2) }} m²</div>
                            <div class="text-muted" style="font-size:11px">Total meter</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ number_format($p->total_rows) }}</div>
                            <div class="text-muted" style="font-size:11px">Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-ui.card>
@endif

{{-- ─ Top Performers ────────────────────────────────────────────────────────── --}}
@php
    $topMarketing ??= collect();
    $topTeknisi   ??= collect();
    $stockAlerts  ??= collect();
    $plSummary    ??= ['revenue'=>0,'commissions_paid'=>0,'expenses'=>0,'estimated_profit'=>0];
    $fmtRpFull    = fn($v) => 'Rp '.number_format(abs($v), 0, ',', '.');
@endphp
<div class="row g-4 mb-4">

    {{-- Top Marketing --}}
    <div class="col-12 col-lg-6">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title"><iconify-icon icon="mdi:trophy-outline" class="me-1" style="color:#f59e0b"></iconify-icon> Top Marketing {{ $selectedYear }}</div>
                    <x-ui.badge variant="primary" size="sm">Top 5</x-ui.badge>
                </div>
            @endslot
            @if ($topMarketing->isEmpty())
                <x-ui.empty-state title="Belum ada data komisi marketing {{ $selectedYear }}." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" style="font-size:13px">
                        <tbody>
                            @foreach ($topMarketing as $i => $m)
                            @php
                                $medalColor = match($i) { 0 => '#f59e0b', 1 => '#94a3b8', 2 => '#cd7f32', default => '#e2e8f0' };
                            @endphp
                            <tr>
                                <td class="ps-3 pe-1 py-2 fw-bold text-center" style="width:32px;color:{{ $medalColor }}">#{{ $i+1 }}</td>
                                <td class="py-2 fw-medium">
                                    {{ $m->name ?? $m->employee_name ?? '' }}
                                    @if($m->job_position ?? null)<div class="text-muted" style="font-size:11px">{{ $m->job_position }}</div>@endif
                                </td>
                                <td class="py-2 text-end text-muted" style="font-size:11px;white-space:nowrap">{{ $m->job_count ?? $m->cnt ?? 0 }}x job</td>
                                <td class="pe-3 py-2 text-end fw-semibold" style="white-space:nowrap;color:var(--c-total)">{{ $fmtRp($m->total_commission ?? $m->total ?? 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Top Teknisi --}}
    <div class="col-12 col-lg-6">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title"><iconify-icon icon="mdi:medal-outline" class="me-1" style="color:var(--c-car)"></iconify-icon> Top Teknisi {{ $selectedYear }}</div>
                    <x-ui.badge variant="primary" size="sm">Top 5</x-ui.badge>
                </div>
            @endslot
            @if ($topTeknisi->isEmpty())
                <x-ui.empty-state title="Belum ada data komisi teknisi {{ $selectedYear }}." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" style="font-size:13px">
                        <tbody>
                            @foreach ($topTeknisi as $i => $t)
                            @php
                                $medalColor = match($i) { 0 => '#f59e0b', 1 => '#94a3b8', 2 => '#cd7f32', default => '#e2e8f0' };
                            @endphp
                            <tr>
                                <td class="ps-3 pe-1 py-2 fw-bold text-center" style="width:32px;color:{{ $medalColor }}">#{{ $i+1 }}</td>
                                <td class="py-2 fw-medium">
                                    {{ $t->name ?? $t->employee_name ?? '' }}
                                    @if($t->job_position ?? null)<div class="text-muted" style="font-size:11px">{{ $t->job_position }}</div>@endif
                                </td>
                                <td class="py-2 text-end text-muted" style="font-size:11px;white-space:nowrap">{{ $t->job_count ?? $t->cnt ?? 0 }}x job</td>
                                <td class="pe-3 py-2 text-end fw-semibold" style="white-space:nowrap;color:var(--c-car)">{{ $fmtRp($t->total_commission ?? $t->total ?? 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>

</div>

{{-- ─ P&L + Stok Kritis ─────────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- P&L Summary --}}
    <div class="col-12 col-lg-5">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="ui-card-title"><iconify-icon icon="mdi:cash-multiple" class="me-1"></iconify-icon> Ringkasan Keuangan {{ $selectedYear }}</div>
            @endslot
            @php $pl = $plSummary; $profitPos = $pl['estimated_profit'] >= 0; @endphp
            <div class="d-flex flex-column gap-3">

                <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.15)">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:8px;background:rgba(16,185,129,.15);display:flex;align-items:center;justify-content:center;color:#10b981;font-size:16px">
                            <iconify-icon icon="mdi:arrow-down-circle-outline"></iconify-icon>
                        </div>
                        <div>
                            <div style="font-size:11px;color:#64748b;font-weight:500">Total Pendapatan (Lunas)</div>
                            <div class="fw-bold" style="font-size:15px;color:#10b981">{{ $fmtRp($pl['revenue']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15)">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:8px;background:rgba(99,102,241,.12);display:flex;align-items:center;justify-content:center;color:#6366f1;font-size:16px">
                            <iconify-icon icon="mdi:medal-outline"></iconify-icon>
                        </div>
                        <div>
                            <div style="font-size:11px;color:#64748b;font-weight:500">Komisi Dibayar</div>
                            <div class="fw-bold" style="font-size:15px;color:#6366f1">{{ $fmtRp($pl['commissions_paid']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.15)">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:8px;background:rgba(245,158,11,.12);display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:16px">
                            <iconify-icon icon="mdi:arrow-up-circle-outline"></iconify-icon>
                        </div>
                        <div>
                            <div style="font-size:11px;color:#64748b;font-weight:500">Kas Keluar Operasional</div>
                            <div class="fw-bold" style="font-size:15px;color:#f59e0b">{{ $fmtRp($pl['expenses']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                     style="background:{{ $profitPos ? 'rgba(59,130,246,.06)' : 'rgba(239,68,68,.06)' }};border:1px solid {{ $profitPos ? 'rgba(59,130,246,.2)' : 'rgba(239,68,68,.2)' }}">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:8px;background:{{ $profitPos ? 'rgba(59,130,246,.12)' : 'rgba(239,68,68,.12)' }};display:flex;align-items:center;justify-content:center;color:{{ $profitPos ? '#3b82f6' : '#ef4444' }};font-size:16px">
                            <iconify-icon icon="mdi:chart-line"></iconify-icon>
                        </div>
                        <div>
                            <div style="font-size:11px;color:#64748b;font-weight:500">Estimasi Laba Bersih</div>
                            <div class="fw-bold" style="font-size:15px;color:{{ $profitPos ? '#3b82f6' : '#ef4444' }}">
                                {{ $profitPos ? '' : '−' }}{{ $fmtRp(abs($pl['estimated_profit'])) }}
                            </div>
                        </div>
                    </div>
                    <x-ui.badge variant="{{ $profitPos ? 'success' : 'danger' }}" size="sm">
                        {{ $profitPos ? 'Surplus' : 'Defisit' }}
                    </x-ui.badge>
                </div>

                <p class="text-muted mb-0 text-center" style="font-size:11px">
                    * Estimasi: pendapatan lunas dikurangi komisi + kas keluar. Belum termasuk HPP &amp; biaya lain.
                </p>
            </div>
        </x-ui.card>
    </div>

    {{-- Stok Kritis --}}
    <div class="col-12 col-lg-7">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">
                        <iconify-icon icon="mdi:alert-circle-outline" class="me-1 text-danger"></iconify-icon>
                        Stok Kritis
                        <span class="text-muted fw-normal" style="font-size:12px">≤ 10 meter</span>
                    </div>
                    <x-ui.button href="{{ route('film-stock.index') }}" variant="ghost" size="sm">Lihat Semua</x-ui.button>
                </div>
            @endslot
            @if ($stockAlerts->isEmpty())
                <x-ui.empty-state title="Semua stok aman." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" style="font-size:13px">
                        <thead style="background:#fafafa">
                            <tr>
                                <th class="ps-3 py-2 text-muted fw-medium border-bottom" style="font-size:11px">Film / Produk</th>
                                @if($isSuperadmin)
                                <th class="py-2 text-muted fw-medium border-bottom" style="font-size:11px">Cabang</th>
                                @endif
                                <th class="py-2 text-center text-muted fw-medium border-bottom" style="font-size:11px">Sisa</th>
                                <th class="py-2 pe-3 border-bottom" style="font-size:11px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockAlerts as $stock)
                            @php
                                $rem     = (float) $stock->remaining_length_meter;
                                $isCrit  = $rem <= 5;
                                $bgColor = $isCrit ? 'rgba(239,68,68,.1)' : 'rgba(245,158,11,.1)';
                                $txtColor= $isCrit ? '#ef4444' : '#f59e0b';
                            @endphp
                            <tr>
                                <td class="ps-3 py-2">
                                    <div class="fw-medium">{{ $stock->film_type }}</div>
                                    <div class="text-muted" style="font-size:11px">
                                        {{ $stock->darkness_code ?? '—' }}
                                        @if($stock->usage_for) · {{ $stock->usage_for }} @endif
                                    </div>
                                </td>
                                @if($isSuperadmin)
                                <td class="py-2 text-muted" style="font-size:12px">{{ $stock->branch?->name ?? '—' }}</td>
                                @endif
                                <td class="py-2 text-center">
                                    <span class="badge rounded-pill px-3 py-1 fw-bold"
                                          style="background:{{ $bgColor }};color:{{ $txtColor }};font-size:12px">
                                        {{ number_format($rem, 1) }} m
                                    </span>
                                </td>
                                <td class="py-2 pe-3 text-end">
                                    <span class="badge" style="background:{{ $bgColor }};color:{{ $txtColor }};font-size:10px;border-radius:4px">
                                        {{ $isCrit ? 'Kritis!' : 'Rendah' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>

</div>

{{-- ─ ApexCharts ────────────────────────────────────────────────────────────── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const C = {
        car  : '#3b82f6',
        bld  : '#f59e0b',
        ppf  : '#10b981',
        total: '#6366f1',
        grid : '#f1f5f9',
        text : '#94a3b8',
    };

    const fmtY = (v) => {
        if (v >= 1e9) return 'Rp ' + (v/1e9).toFixed(1) + 'M';
        if (v >= 1e6) return 'Rp ' + (v/1e6).toFixed(1) + 'Jt';
        if (v >= 1e3) return 'Rp ' + (v/1e3).toFixed(0) + 'rb';
        return 'Rp ' + v;
    };
    const fmtTip = (v) => 'Rp ' + v.toLocaleString('id-ID');

    const anim = {
        enabled          : true,
        easing           : 'easeinout',
        speed            : 900,
        animateGradually : { enabled: true, delay: 100 },
        dynamicAnimation : { enabled: true, speed: 400 },
    };

    const baseLine = {
        chart: {
            type       : 'area',
            toolbar    : { show: false },
            fontFamily : 'inherit',
            animations : anim,
        },
        colors     : [C.car, C.bld, C.ppf],
        stroke     : { curve: 'smooth', width: [2.5, 2.5, 2.5], lineCap: 'round' },
        fill: {
            type    : 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.30, opacityTo: 0.02, stops: [0, 85, 100] },
        },
        markers    : { size: 4, strokeWidth: 2, strokeColors: '#fff', hover: { size: 6 } },
        legend     : { show: false },
        dataLabels : { enabled: false },
        yaxis      : { labels: { formatter: fmtY, style: { colors: C.text, fontSize: '11px' } } },
        xaxis      : {
            labels      : { style: { colors: C.text, fontSize: '11px' } },
            axisBorder  : { show: false },
            axisTicks   : { show: false },
        },
        tooltip    : { theme: 'light', y: { formatter: fmtTip }, shared: true, intersect: false },
        grid       : { borderColor: C.grid, strokeDashArray: 4, padding: { top: -10 } },
    };

    // ── Monthly
    new ApexCharts(document.getElementById('monthlyChart'), {
        ...baseLine,
        chart : { ...baseLine.chart, height: 290 },
        series: [
            { name: 'Mobil',  data: @json($monthlyCarAmt) },
            { name: 'Gedung', data: @json($monthlyBldAmt) },
            { name: 'PPF',    data: @json($monthlyPpfAmt) },
        ],
        xaxis : { ...baseLine.xaxis, categories: @json($months) },
    }).render();

    // ── Donut
    @php $cV = $carY['total']; $bV = $bldY['total']; $pV = $ppfY['total']; @endphp
    @if ($cV + $bV + $pV > 0)
    new ApexCharts(document.getElementById('donutChart'), {
        chart      : { type: 'donut', height: 180, fontFamily: 'inherit', toolbar: { show: false }, animations: anim },
        series     : [{{ $cV }}, {{ $bV }}, {{ $pV }}],
        labels     : ['Mobil', 'Gedung', 'PPF'],
        colors     : [C.car, C.bld, C.ppf],
        legend     : { show: false },
        dataLabels : { enabled: false },
        stroke     : { width: 2, colors: ['#fff'] },
        plotOptions: {
            pie: { donut: { size: '70%', labels: {
                show  : true,
                value : { fontSize: '16px', fontWeight: 700, color: '#1e293b', formatter: fmtY },
                total : { show: true, label: '{{ $totalCnt }} order', fontSize: '11px', color: C.text,
                          formatter: (w) => fmtY(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) },
            }}},
        },
        tooltip    : { theme: 'light', y: { formatter: fmtTip } },
    }).render();
    @else
    document.getElementById('donutChart').innerHTML =
        '<div class="text-center text-muted py-4"><iconify-icon icon="mdi:chart-pie" class="fs-2 d-block mb-2"></iconify-icon><small>Belum ada data</small></div>';
    @endif

    // ── Yearly
    @if (count($yearly) > 0)
    new ApexCharts(document.getElementById('yearlyChart'), {
        ...baseLine,
        chart : { ...baseLine.chart, height: 250 },
        series: [
            { name: 'Mobil',  data: @json($yearlyCarAmt) },
            { name: 'Gedung', data: @json($yearlyBldAmt) },
            { name: 'PPF',    data: @json($yearlyPpfAmt) },
        ],
        xaxis : { ...baseLine.xaxis, categories: @json($yearLabels) },
    }).render();
    @endif

});
</script>

@endsection
