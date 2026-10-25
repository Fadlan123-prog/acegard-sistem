@extends('layout.index')

@section('title', 'Laporan Analitik')
@section('route-breadcrumb', route('analytics.index'))
@section('breadcrumb', 'Laporan Analitik')

@section('content')

@php
    $fmtRp  = fn($v) => 'Rp '.number_format($v, 0, ',', '.');
    $fmtNum = fn($v) => number_format($v, 0, ',', '.');

    $periodLabel = match ($filters['period'] ?? 'month') {
        '3months' => '3 Bulan Terakhir',
        'year'    => 'Tahun Ini',
        default   => 'Bulan Ini',
    };
    if (!empty($filters['start']) && !empty($filters['end'])) {
        $periodLabel = $startDate->format('d M Y').' – '.$endDate->format('d M Y');
    }
@endphp

{{-- Filter Bar --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('analytics.index') }}"
          class="d-flex flex-wrap align-items-end gap-2">

        {{-- Quick period buttons --}}
        <div class="btn-group btn-group-sm" role="group">
            @foreach (['month'=>'Bulan Ini', '3months'=>'3 Bulan', 'year'=>'Tahun Ini'] as $val => $lbl)
            <x-ui.button
                variant="{{ ($filters['period'] ?? 'month') === $val && empty($filters['start']) ? 'primary' : 'outline' }}"
                size="sm"
                href="{{ route('analytics.index', array_merge($filters, ['period'=>$val, 'start'=>'', 'end'=>''])) }}">
                {{ $lbl }}
            </x-ui.button>
            @endforeach
        </div>

        {{-- Custom range --}}
        <div class="d-flex gap-1 align-items-center">
            <input type="date" name="start" class="form-control form-control-sm" style="width:140px"
                   value="{{ $filters['start'] ?? '' }}" placeholder="Dari">
            <span class="text-muted small">–</span>
            <input type="date" name="end" class="form-control form-control-sm" style="width:140px"
                   value="{{ $filters['end'] ?? '' }}" placeholder="Sampai">
        </div>

        @if ($isSuperadmin)
        <select name="branch_id" class="form-select form-select-sm" style="min-width:170px" onchange="this.form.submit()">
            <option value="">Semua Cabang</option>
            @foreach ($branches as $br)
                <option value="{{ $br->id }}" {{ ($filters['branch_id'] ?? '') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
            @endforeach
        </select>
        @endif

        <x-ui.button variant="primary" size="sm" type="submit"><iconify-icon icon="mdi:magnify" class="me-1"></iconify-icon>Terapkan</x-ui.button>

        <span class="ms-auto text-muted small align-self-center">
            <iconify-icon icon="mdi:calendar-outline" class="me-1"></iconify-icon>{{ $periodLabel }}
        </span>
    </form>
</x-ui.card>

{{-- KPI Tiles --}}
<div class="row g-3 mb-4">
    @php
        $mdIcon = fn (string $name) => '<iconify-icon icon="'.$name.'"></iconify-icon>';
        $kpis = [
            ['label'=>'Total Pendapatan',      'val'=>$fmtRp($revenue['grand_total']), 'sub'=>$revenue['grand_count'].' transaksi',         'color'=>'purple','icon'=>$mdIcon('mdi:currency-usd-circle-outline')],
            ['label'=>'Rata-rata Transaksi',   'val'=>$fmtRp($revenue['avg']),         'sub'=>'per invoice',                                 'color'=>'green', 'icon'=>$mdIcon('mdi:chart-bar-stacked')],
            ['label'=>'Komisi Dibayar',        'val'=>$fmtRp($commissionStats['total_paid']),    'sub'=>$fmtRp($commissionStats['total_eligible']).' eligible', 'color'=>'amber','icon'=>$mdIcon('mdi:account-star-outline')],
            ['label'=>'Pengeluaran Kas',       'val'=>$fmtRp($pettyCash['out'] + $pettyCash['purchase']), 'sub'=>'petty cash + pembelian',  'color'=>'red',   'icon'=>$mdIcon('mdi:wallet-outline')],
        ];
    @endphp
    @foreach ($kpis as $k)
    <div class="col-6 col-lg-3">
        <x-ui.stats-card
            :label="$k['label']"
            :value="$k['val']"
            :color="$k['color']"
            :icon="$k['icon']"
            accent
            class="h-100"
        >
            <span class="text-muted" style="font-size:11px">{{ $k['sub'] }}</span>
        </x-ui.stats-card>
    </div>
    @endforeach
</div>

{{-- Revenue Chart + Breakdown --}}
<div class="row g-4 mb-4">
    {{-- Daily Revenue Chart --}}
    <div class="col-12 col-lg-8">
        <x-ui.card class="h-100">
            @slot('header')
                <div class="ui-card-title">Tren Pendapatan Harian</div>
            @endslot
            <div id="revenueChart" style="height:280px"></div>
        </x-ui.card>
    </div>

    {{-- Type Breakdown Donut --}}
    <div class="col-12 col-lg-4">
        <x-ui.card class="h-100">
            @slot('header')
                <div class="ui-card-title">Breakdown Tipe</div>
            @endslot
            <div class="d-flex flex-column">
                <div id="donutChart" style="height:200px"></div>
                <div class="mt-auto pt-2">
                    @foreach ([['Window Tinting','wt_total','#6366f1'],['PPF','ppf_total','#10b981'],['Gedung','bld_total','#f59e0b']] as [$lbl,$key,$col])
                    <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #f8f9fa;font-size:12px">
                        <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $col }};margin-right:6px"></span>{{ $lbl }}</span>
                        <span class="fw-semibold">{{ $fmtRp($revenue[$key]) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </x-ui.card>
    </div>
</div>

{{-- Branch Comparison (superadmin) --}}
@if ($isSuperadmin && count($branchRevenue) > 1)
<x-ui.card class="mb-4">
    @slot('header')
        <div class="ui-card-title">Pendapatan per Cabang</div>
    @endslot
    <div id="branchChart" style="height:220px"></div>
</x-ui.card>
@endif

{{-- Top Performers --}}
<div class="row g-4 mb-4">
    @foreach ([['Top Marketing','topMarketing','Komisi','#6366f1'],['Top Teknisi','topTeknisi','Komisi','#10b981']] as [$title,$var,$sub,$col])
    <div class="col-12 col-md-6">
        <x-ui.card class="h-100">
            @slot('header')
                <div class="ui-card-title">{{ $title }}</div>
            @endslot
            @php $rows = $$var; @endphp
            @if (empty($rows))
                <x-ui.empty-state title="Belum ada data periode ini." />
            @else
            <table class="table table-sm mb-0" style="font-size:13px">
                <tbody>
                    @foreach ($rows as $i => $row)
                    <tr>
                        <td class="ps-4 py-2" style="width:32px">
                            <span class="fw-bold" style="color:{{ ['#f59e0b','#94a3b8','#cd7f32'][$i] ?? '#94a3b8' }}">{{ $i+1 }}</span>
                        </td>
                        <td class="py-2">{{ $row->name }}</td>
                        <td class="py-2 text-end pe-4 fw-semibold" style="color:{{ $col }}">{{ $fmtRp($row->total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </x-ui.card>
    </div>
    @endforeach
</div>

{{-- Hutang & Piutang Aging --}}
<div class="row g-4 mb-4">
    @foreach ([['Aging Hutang',$hutangAging,'#ef4444'],['Aging Piutang',$piutangAging,'#f59e0b']] as [$title,$aging,$col])
    <div class="col-12 col-md-6">
        <x-ui.card class="h-100">
            @slot('header')
                <div class="ui-card-title">{{ $title }}</div>
            @endslot
            @php
                $buckets = $aging['buckets'];
                $agingRows = [
                    'Belum Jatuh Tempo' => $buckets['current'],
                    '1–30 Hari'         => $buckets['1_30'],
                    '31–60 Hari'        => $buckets['31_60'],
                    '61–90 Hari'        => $buckets['61_90'],
                    '> 90 Hari'         => $buckets['over90'],
                ];
                $agingColors = ['#10b981','#f59e0b','#f97316','#ef4444','#7f1d1d'];
                $agingTotal = array_sum($agingRows);
            @endphp
            @if ($agingTotal === 0)
                <x-ui.empty-state title="Tidak ada outstanding." />
            @else
                @php $agingIdx = 0; @endphp
                @foreach ($agingRows as $lbl => $val)
                @php $pct = $agingTotal > 0 ? round($val / $agingTotal * 100) : 0; $c = $agingColors[$agingIdx++]; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">{{ $lbl }}</span>
                        <span class="fw-semibold" style="color:{{ $c }}">{{ $fmtRp($val) }}</span>
                    </div>
                    <div class="progress" style="height:5px;border-radius:3px">
                        <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $c }};border-radius:3px"></div>
                    </div>
                </div>
                @endforeach
            @endif
        </x-ui.card>
    </div>
    @endforeach
</div>

{{-- Petty Cash Summary --}}
<x-ui.card class="mb-4">
    @slot('header')
        <div class="ui-card-title">Ringkasan Kas (Petty Cash)</div>
    @endslot
    <div class="row g-3">
        @php
            $pcRows = [
                ['Kas Masuk',    $pettyCash['in'],       'green', $mdIcon('mdi:arrow-down-circle-outline')],
                ['Pengeluaran',  $pettyCash['out'],       'red',   $mdIcon('mdi:arrow-up-circle-outline')],
                ['Pembelian',    $pettyCash['purchase'],  'amber', $mdIcon('mdi:cart-outline')],
                ['Net Kas',      $pettyCash['in'] - $pettyCash['out'] - $pettyCash['purchase'],
                                 $pettyCash['in'] >= $pettyCash['out'] + $pettyCash['purchase'] ? 'green' : 'red',
                                 $mdIcon('mdi:scale-balance')],
            ];
        @endphp
        @foreach ($pcRows as [$lbl, $val, $col, $icon])
        <div class="col-6 col-lg-3">
            <x-ui.stats-card
                :label="$lbl"
                :value="$fmtRp($val)"
                :color="$col"
                :icon="$icon"
                accent
            />
        </div>
        @endforeach
    </div>
</x-ui.card>

@push('page-scripts')
<script>
(function () {
    // ── Data from PHP ──────────────────────────────────────────────────────
    const days       = {!! json_encode(array_keys($revenueByDay)) !!};
    const wtSeries   = {!! json_encode(array_values(array_map(fn($d) => $d['wt'],  $revenueByDay))) !!};
    const ppfSeries  = {!! json_encode(array_values(array_map(fn($d) => $d['ppf'], $revenueByDay))) !!};
    const bldSeries  = {!! json_encode(array_values(array_map(fn($d) => $d['bld'], $revenueByDay))) !!};

    const typeLabels = {!! json_encode($revenueByType['labels']) !!};
    const typeValues = {!! json_encode($revenueByType['values']) !!};

    @if ($isSuperadmin && count($branchRevenue) > 1)
    const branchNames  = {!! json_encode(array_column($branchRevenue, 'name')) !!};
    const branchTotals = {!! json_encode(array_column($branchRevenue, 'total')) !!};
    @endif

    // ── Helpers ────────────────────────────────────────────────────────────
    const fmtRp = v => 'Rp ' + v.toLocaleString('id-ID');

    const isDark = () =>
        document.documentElement.getAttribute('data-theme') === 'dark' ||
        (window.matchMedia('(prefers-color-scheme: dark)').matches &&
         document.documentElement.getAttribute('data-theme') !== 'light');

    function textColor() { return isDark() ? '#94a3b8' : '#64748b'; }
    function gridColor() { return isDark() ? '#1e293b' : '#f1f5f9'; }

    // ── Revenue Line Chart ─────────────────────────────────────────────────
    const revChart = new ApexCharts(document.getElementById('revenueChart'), {
        chart: { type: 'bar', height: 280, stacked: true, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [
            { name: 'Window Tinting', data: wtSeries, color: '#6366f1' },
            { name: 'PPF',            data: ppfSeries, color: '#10b981' },
            { name: 'Gedung',         data: bldSeries, color: '#f59e0b' },
        ],
        xaxis: {
            categories: days,
            labels: { rotate: -45, style: { fontSize: '10px', colors: textColor() },
                      formatter: v => v.slice(8) },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: { labels: { formatter: v => 'Rp ' + (v/1e6).toFixed(1) + 'jt', style: { colors: textColor(), fontSize: '11px' } } },
        grid: { borderColor: gridColor(), strokeDashArray: 3 },
        legend: { position: 'top', fontSize: '12px', labels: { colors: textColor() } },
        tooltip: { y: { formatter: fmtRp } },
        plotOptions: { bar: { borderRadius: 2, columnWidth: '70%' } },
        dataLabels: { enabled: false },
    });
    revChart.render();

    // ── Donut Chart ────────────────────────────────────────────────────────
    const donut = new ApexCharts(document.getElementById('donutChart'), {
        chart: { type: 'donut', height: 200, fontFamily: 'inherit' },
        series: typeValues,
        labels: typeLabels,
        colors: ['#6366f1', '#10b981', '#f59e0b'],
        legend: { show: false },
        tooltip: { y: { formatter: fmtRp } },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true,
            total: { show: true, label: 'Total', color: textColor(), formatter: w => fmtRp(w.globals.seriesTotals.reduce((a,b)=>a+b,0)) }
        } } } },
        dataLabels: { enabled: false },
    });
    donut.render();

    @if ($isSuperadmin && count($branchRevenue) > 1)
    const branchChart = new ApexCharts(document.getElementById('branchChart'), {
        chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [{ name: 'Pendapatan', data: branchTotals, color: '#6366f1' }],
        xaxis: { categories: branchNames, labels: { style: { colors: textColor(), fontSize: '12px' } }, axisBorder: { show: false } },
        yaxis: { labels: { formatter: v => 'Rp '+(v/1e6).toFixed(0)+'jt', style: { colors: textColor(), fontSize: '11px' } } },
        grid: { borderColor: gridColor(), strokeDashArray: 3 },
        legend: { show: false },
        tooltip: { y: { formatter: fmtRp } },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%', distributed: true } },
        dataLabels: { enabled: false },
    });
    branchChart.render();
    @endif
})();
</script>
@endpush


@endsection
