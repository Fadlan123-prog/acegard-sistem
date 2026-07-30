@extends('layout.index')

@section('title', 'Statistik Penjualan')
@section('route-breadcrumb', route('statistics.index'))
@section('breadcrumb', 'Statistik Penjualan')

@section('content')

@php
    $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    $carYear      = $summary['car']['year'];
    $buildingYear = $summary['building']['year'];
    $ppfYear      = $summary['ppf']['year'];

    $totalYearCount  = $carYear['count']  + $buildingYear['count']  + $ppfYear['count'];
    $totalYearAmount = $carYear['total']  + $buildingYear['total']  + $ppfYear['total'];
    $totalYearPaid   = $carYear['paid']   + $buildingYear['paid']   + $ppfYear['paid'];

    // Monthly chart data
    $monthlyCarAmounts      = array_column(array_values($monthly), 'car_amount');
    $monthlyBuildingAmounts = array_column(array_values($monthly), 'building_amount');
    $monthlyPpfAmounts      = array_column(array_values($monthly), 'ppf_amount');

    // Yearly chart data
    $yearLabels         = array_keys($yearly);
    $yearlyCarAmounts   = array_column(array_values($yearly), 'car_amount');
    $yearlyBldAmounts   = array_column(array_values($yearly), 'building_amount');
    $yearlyPpfAmounts   = array_column(array_values($yearly), 'ppf_amount');
@endphp

{{-- Filter Bar --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('statistics.index') }}" class="row g-2 align-items-end">
        <div class="col-12 col-sm-4 col-md-3">
            <label class="form-label small fw-medium mb-1">Tahun</label>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach ($years as $y)
                    <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        @if ($isSuperadmin)
            <div class="col-12 col-sm-5 col-md-4">
                <label class="form-label small fw-medium mb-1">Cabang</label>
                <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} ({{ $branch->city }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-12 col-sm-3 col-md-2">
            <x-ui.button variant="outline" size="sm" class="w-100" href="{{ route('statistics.index') }}">Reset</x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">

    {{-- Total Tahun Ini --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-ui.card class="h-100 bg-primary text-white border-0">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="small opacity-75">Total Penjualan {{ $selectedYear }}</div>
                <iconify-icon icon="solar:chart-bold" class="fs-4 opacity-75"></iconify-icon>
            </div>
            <div class="fs-4 fw-bold">Rp {{ number_format($totalYearAmount / 1000000, 1) }}Jt</div>
            <div class="small opacity-75 mt-1">{{ number_format($totalYearCount) }} transaksi</div>
            <div class="mt-2 pt-2 border-top border-white border-opacity-25 small">
                Terkumpul: <strong>Rp {{ number_format($totalYearPaid / 1000000, 1) }}Jt</strong>
            </div>
        </x-ui.card>
    </div>

    {{-- Mobil --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-ui.card class="h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="small text-muted fw-medium">Penjualan Mobil</div>
                <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center"
                     style="width:36px;height:36px">
                    <iconify-icon icon="solar:car-bold"></iconify-icon>
                </div>
            </div>
            <div class="fs-5 fw-bold text-dark">Rp {{ number_format($carYear['total'] / 1000000, 1) }}Jt</div>
            <div class="text-muted small mt-1">{{ $carYear['count'] }} transaksi</div>
            <div class="mt-2 pt-2 border-top small">
                <span class="text-success">
                    <iconify-icon icon="mdi:checkbox-marked-circle-outline"></iconify-icon>
                    Terkumpul: Rp {{ number_format($carYear['paid'] / 1000000, 1) }}Jt
                </span>
            </div>
        </x-ui.card>
    </div>

    {{-- Gedung --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-ui.card class="h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="small text-muted fw-medium">Penjualan Gedung</div>
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center"
                     style="width:36px;height:36px">
                    <iconify-icon icon="solar:buildings-bold"></iconify-icon>
                </div>
            </div>
            <div class="fs-5 fw-bold text-dark">Rp {{ number_format($buildingYear['total'] / 1000000, 1) }}Jt</div>
            <div class="text-muted small mt-1">{{ $buildingYear['count'] }} transaksi</div>
            <div class="mt-2 pt-2 border-top small">
                <span class="text-success">
                    <iconify-icon icon="mdi:checkbox-marked-circle-outline"></iconify-icon>
                    Terkumpul: Rp {{ number_format($buildingYear['paid'] / 1000000, 1) }}Jt
                </span>
            </div>
        </x-ui.card>
    </div>

    {{-- PPF --}}
    <div class="col-12 col-sm-6 col-lg-3">
        <x-ui.card class="h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="small text-muted fw-medium">Penjualan PPF</div>
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center"
                     style="width:36px;height:36px">
                    <iconify-icon icon="solar:shield-bold"></iconify-icon>
                </div>
            </div>
            <div class="fs-5 fw-bold text-dark">Rp {{ number_format($ppfYear['total'] / 1000000, 1) }}Jt</div>
            <div class="text-muted small mt-1">{{ $ppfYear['count'] }} transaksi</div>
            <div class="mt-2 pt-2 border-top small">
                <span class="text-success">
                    <iconify-icon icon="mdi:checkbox-marked-circle-outline"></iconify-icon>
                    Terkumpul: Rp {{ number_format($ppfYear['paid'] / 1000000, 1) }}Jt
                </span>
            </div>
        </x-ui.card>
    </div>

</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">

    {{-- Monthly Chart --}}
    <div class="col-12 col-lg-8">
        <x-ui.card class="h-100">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">Penjualan Bulanan {{ $selectedYear }}</div>
                    <x-ui.badge variant="neutral">Per Bulan</x-ui.badge>
                </div>
            @endslot
            <div id="monthlyChart"></div>
        </x-ui.card>
    </div>

    {{-- Type Distribution --}}
    <div class="col-12 col-lg-4">
        <x-ui.card class="h-100">
            @slot('header')
                <div class="ui-card-title">Distribusi Tipe {{ $selectedYear }}</div>
            @endslot
            <div id="donutChart"></div>
            <div class="mt-3">
                @php
                    $grandTotal = $totalYearAmount ?: 1;
                @endphp
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2 small">
                        <span class="d-inline-block rounded-circle bg-info" style="width:10px;height:10px"></span>
                        Mobil
                    </div>
                    <div class="text-end small">
                        <div class="fw-semibold">{{ $carYear['count'] }} order</div>
                        <div class="text-muted">{{ number_format($carYear['total'] / $grandTotal * 100, 1) }}%</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2 small">
                        <span class="d-inline-block rounded-circle bg-warning" style="width:10px;height:10px"></span>
                        Gedung
                    </div>
                    <div class="text-end small">
                        <div class="fw-semibold">{{ $buildingYear['count'] }} order</div>
                        <div class="text-muted">{{ number_format($buildingYear['total'] / $grandTotal * 100, 1) }}%</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center gap-2 small">
                        <span class="d-inline-block rounded-circle bg-success" style="width:10px;height:10px"></span>
                        PPF
                    </div>
                    <div class="text-end small">
                        <div class="fw-semibold">{{ $ppfYear['count'] }} order</div>
                        <div class="text-muted">{{ number_format($ppfYear['total'] / $grandTotal * 100, 1) }}%</div>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

</div>

{{-- Yearly Chart --}}
@if (count($yearly) > 0)
<x-ui.card class="mb-4">
    @slot('header')
        <div class="d-flex align-items-center justify-content-between">
            <div class="ui-card-title">Tren Penjualan Tahunan</div>
            <x-ui.badge variant="neutral">Semua Tahun</x-ui.badge>
        </div>
    @endslot
    <div id="yearlyChart"></div>
</x-ui.card>
@endif

{{-- Monthly Breakdown Table --}}
<x-ui.card>
    @slot('header')
        <div class="ui-card-title">Rincian Bulanan {{ $selectedYear }}</div>
    @endslot

    {{-- Desktop table --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Bulan</th>
                    <th class="text-center">Mobil<br><small class="fw-normal text-muted">Jumlah / Nilai</small></th>
                    <th class="text-center">Gedung<br><small class="fw-normal text-muted">Jumlah / Nilai</small></th>
                    <th class="text-center">PPF<br><small class="fw-normal text-muted">Jumlah / Nilai</small></th>
                    <th class="text-end pe-3">Total Nilai</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; $grandCount = 0; @endphp
                @foreach ($monthly as $m => $data)
                    @php
                        $rowTotal  = $data['car_amount'] + $data['building_amount'] + $data['ppf_amount'];
                        $rowCount  = $data['car_count']  + $data['building_count']  + $data['ppf_count'];
                        $grandTotal += $rowTotal;
                        $grandCount += $rowCount;
                        $hasData = $rowCount > 0;
                    @endphp
                    <tr class="{{ !$hasData ? 'text-muted' : '' }}">
                        <td class="ps-3 fw-medium">{{ $monthNames[$m - 1] }} {{ $selectedYear }}</td>
                        <td class="text-center small">
                            @if ($data['car_count'] > 0)
                                <x-ui.badge variant="neutral" size="sm" class="me-1">{{ $data['car_count'] }}x</x-ui.badge>
                                Rp {{ number_format($data['car_amount'] / 1000000, 1) }}Jt
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center small">
                            @if ($data['building_count'] > 0)
                                <x-ui.badge variant="warning" size="sm" class="me-1">{{ $data['building_count'] }}x</x-ui.badge>
                                Rp {{ number_format($data['building_amount'] / 1000000, 1) }}Jt
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center small">
                            @if ($data['ppf_count'] > 0)
                                <x-ui.badge variant="success" size="sm" class="me-1">{{ $data['ppf_count'] }}x</x-ui.badge>
                                Rp {{ number_format($data['ppf_amount'] / 1000000, 1) }}Jt
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end pe-3 fw-semibold">
                            @if ($rowTotal > 0)
                                Rp {{ number_format($rowTotal) }}
                            @else
                                <span class="text-muted fw-normal">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td class="ps-3">Total {{ $selectedYear }}</td>
                    <td class="text-center small">
                        {{ $summary['car']['year']['count'] }}x
                        — Rp {{ number_format($summary['car']['year']['total'] / 1000000, 1) }}Jt
                    </td>
                    <td class="text-center small">
                        {{ $summary['building']['year']['count'] }}x
                        — Rp {{ number_format($summary['building']['year']['total'] / 1000000, 1) }}Jt
                    </td>
                    <td class="text-center small">
                        {{ $summary['ppf']['year']['count'] }}x
                        — Rp {{ number_format($summary['ppf']['year']['total'] / 1000000, 1) }}Jt
                    </td>
                    <td class="text-end pe-3">Rp {{ number_format($grandTotal) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Mobile accordion --}}
    <div class="d-md-none">
        @foreach ($monthly as $m => $data)
            @php
                $rowTotal = $data['car_amount'] + $data['building_amount'] + $data['ppf_amount'];
                $rowCount = $data['car_count']  + $data['building_count']  + $data['ppf_count'];
            @endphp
            @if ($rowCount > 0)
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">{{ $monthNames[$m - 1] }} {{ $selectedYear }}</span>
                        <span class="fw-bold text-primary small">Rp {{ number_format($rowTotal) }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small">
                        @if ($data['car_count'] > 0)
                            <x-ui.badge variant="neutral">Mobil {{ $data['car_count'] }}x — Rp {{ number_format($data['car_amount'] / 1000000, 1) }}Jt</x-ui.badge>
                        @endif
                        @if ($data['building_count'] > 0)
                            <x-ui.badge variant="warning">Gedung {{ $data['building_count'] }}x — Rp {{ number_format($data['building_amount'] / 1000000, 1) }}Jt</x-ui.badge>
                        @endif
                        @if ($data['ppf_count'] > 0)
                            <x-ui.badge variant="success">PPF {{ $data['ppf_count'] }}x — Rp {{ number_format($data['ppf_amount'] / 1000000, 1) }}Jt</x-ui.badge>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
        @if ($totalYearAmount == 0)
            <x-ui.empty-state title="Belum ada data penjualan untuk tahun {{ $selectedYear }}." />
        @endif
    </div>
</x-ui.card>

{{-- ApexCharts --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const formatRp = (val) => {
        if (val >= 1_000_000_000) return 'Rp ' + (val / 1_000_000_000).toFixed(1) + 'M';
        if (val >= 1_000_000)     return 'Rp ' + (val / 1_000_000).toFixed(1) + 'Jt';
        if (val >= 1_000)         return 'Rp ' + (val / 1_000).toFixed(0) + 'rb';
        return 'Rp ' + val;
    };

    const baseOptions = {
        chart: { type: 'bar', stacked: true, toolbar: { show: false }, fontFamily: 'inherit' },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        legend: { position: 'top', horizontalAlign: 'right' },
        yaxis: { labels: { formatter: formatRp } },
        tooltip: {
            y: { formatter: (val) => 'Rp ' + val.toLocaleString('id-ID') }
        },
        colors: ['#3b82f6', '#f59e0b', '#10b981'],
        fill: { opacity: 1 },
        dataLabels: { enabled: false },
    };

    // Monthly Chart
    new ApexCharts(document.getElementById('monthlyChart'), {
        ...baseOptions,
        series: [
            { name: 'Mobil',  data: @json($monthlyCarAmounts) },
            { name: 'Gedung', data: @json($monthlyBuildingAmounts) },
            { name: 'PPF',    data: @json($monthlyPpfAmounts) },
        ],
        xaxis: { categories: @json($monthNames) },
        chart: { ...baseOptions.chart, height: 280 },
    }).render();

    // Donut Chart
    @php
        $donutCar  = $carYear['total'];
        $donutBld  = $buildingYear['total'];
        $donutPpf  = $ppfYear['total'];
        $donutHasData = ($donutCar + $donutBld + $donutPpf) > 0;
    @endphp
    @if ($donutHasData)
    new ApexCharts(document.getElementById('donutChart'), {
        chart: { type: 'donut', height: 200, fontFamily: 'inherit', toolbar: { show: false } },
        series: [{{ $donutCar }}, {{ $donutBld }}, {{ $donutPpf }}],
        labels: ['Mobil', 'Gedung', 'PPF'],
        colors: ['#3b82f6', '#f59e0b', '#10b981'],
        legend: { show: false },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '65%' } } },
        tooltip: { y: { formatter: (val) => 'Rp ' + val.toLocaleString('id-ID') } },
    }).render();
    @else
    document.getElementById('donutChart').innerHTML =
        '<div class="text-center text-muted py-4 small"><iconify-icon icon="mdi:chart-pie" class="fs-2 d-block mb-2"></iconify-icon>Belum ada data</div>';
    @endif

    // Yearly Chart
    @if (count($yearly) > 0)
    new ApexCharts(document.getElementById('yearlyChart'), {
        ...baseOptions,
        series: [
            { name: 'Mobil',  data: @json($yearlyCarAmounts) },
            { name: 'Gedung', data: @json($yearlyBldAmounts) },
            { name: 'PPF',    data: @json($yearlyPpfAmounts) },
        ],
        xaxis: { categories: @json($yearLabels) },
        chart: { ...baseOptions.chart, height: 260 },
    }).render();
    @endif

});
</script>

@endsection
