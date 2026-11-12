@extends('layout.index')

@section('title', 'Dashboard Pemilik')
@section('route-breadcrumb', route('owner.dashboard'))
@section('breadcrumb', 'Dashboard Pemilik')

@section('content')

@php $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

<x-dashboard.birthday-widget :birthdays="$upcomingBirthdays" />

{{-- Ringkasan Eksekutif --}}
@php
    $ex = $executive;
    $monthNames = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $short = fn($v) => $v >= 1e9 ? 'Rp ' . number_format($v/1e9, 2, ',', '.') . ' M' : ($v >= 1e6 ? 'Rp ' . number_format($v/1e6, 1, ',', '.') . ' jt' : 'Rp ' . number_format($v, 0, ',', '.'));
@endphp
<x-ui.card title="Ringkasan Eksekutif" subtitle="Omzet = invoice lunas (mobil, PPF, gedung) per tanggal invoice" class="mb-4">
    <form method="GET" class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <label for="ex-month" class="visually-hidden">Bulan</label>
        <select id="ex-month" name="month" class="form-select" style="width:auto" onchange="this.form.submit()">
            @foreach ($monthNames as $n => $l)<option value="{{ $n }}" @selected($n === $execMonth)>{{ $l }}</option>@endforeach
        </select>
        <label for="ex-year" class="visually-hidden">Tahun</label>
        <input id="ex-year" type="number" name="year" value="{{ $execYear }}" min="2020" max="2100" class="form-control" style="width:96px" onchange="this.form.submit()">
        <span class="text-muted" style="font-size:12px">{{ $ex['start']->translatedFormat('d M') }} – {{ $ex['end']->translatedFormat('d M Y') }}</span>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="text-muted" style="font-size:12px">Total Omzet</div>
            <div class="fw-bold" style="font-size:22px">{{ $short($ex['revenue']['total']) }}</div>
            @if ($ex['growth_pct'] !== null)
                <div style="font-size:12px" class="{{ $ex['growth_pct'] >= 0 ? 'text-success' : 'text-danger' }}">
                    <iconify-icon icon="mdi:{{ $ex['growth_pct'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></iconify-icon>
                    {{ abs($ex['growth_pct']) }}% vs bulan lalu
                </div>
            @else
                <div class="text-muted" style="font-size:12px">Belum ada pembanding bulan lalu</div>
            @endif
        </div>
        <div class="col-6 col-xl-3">
            <div class="text-muted" style="font-size:12px">Total Advertising Spend</div>
            <div class="fw-bold" style="font-size:22px">{{ $short($ex['ad_spend']) }}</div>
            <div class="text-muted" style="font-size:12px">ROAS {{ $ex['roas'] !== null ? $ex['roas'].'x' : '-' }}</div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="text-muted" style="font-size:12px">Total Leads</div>
            <div class="fw-bold" style="font-size:22px">{{ number_format($ex['leads'], 0, ',', '.') }}</div>
            <div class="text-muted" style="font-size:12px">Closing {{ $ex['closing_rate'] }}% · CPL {{ $ex['cpl'] ? $short($ex['cpl']) : '-' }}</div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="text-muted" style="font-size:12px">Total Transaksi</div>
            <div class="fw-bold" style="font-size:22px">{{ number_format($ex['revenue']['count'], 0, ',', '.') }}</div>
            <div class="text-muted" style="font-size:12px">Invoice lunas</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="fw-semibold mb-1" style="font-size:13px">Omzet per Kategori</div>
            <div id="execCategoryChart" style="height:240px"></div>
        </div>
        <div class="col-lg-6">
            <div class="fw-semibold mb-1" style="font-size:13px">Tren Omzet {{ count($ex['trend']) }} Bulan</div>
            <div id="execTrendChart" style="height:240px"></div>
        </div>
    </div>
</x-ui.card>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Omset Hari Ini"
            :value="$idr($revenueToday)"
            color="green" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Invoice terbayar lunas</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Omset Bulan Ini"
            :value="$idr($revenueMonth)"
            color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">{{ now()->translatedFormat('F Y') }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Invoice Belum Lunas"
            :value="number_format($outstandingCount)"
            color="red" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Sisa: {{ $idr($outstandingAmount) }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Total Customer"
            :value="number_format($totalCustomers)"
            color="purple" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">{{ $totalBranches }} cabang aktif</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">

    {{-- Revenue by Branch --}}
    <div class="col-xl-4">
        <x-ui.card title="Omset per Cabang" subtitle="bulan ini" class="h-100">
            @forelse ($revenueByBranch as $rb)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:13px">{{ $rb->name }}</span>
                <span class="fw-semibold" style="font-size:13px">{{ $idr($rb->total) }}</span>
            </div>
            @empty
            <x-ui.empty-state title="Belum ada data" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Top Marketing --}}
    <div class="col-xl-4">
        <x-ui.card title="Top Marketing" subtitle="komisi bulan ini" class="h-100">
            @php $rankColors = ['#10b981','#6366f1','#f59e0b','#0ea5e9','#8b5cf6']; @endphp
            @forelse ($topMarketing as $i => $m)
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge rounded-circle d-flex align-items-center justify-content-center fw-bold"
                      style="width:24px;height:24px;background:{{ $rankColors[$i] ?? '#64748b' }};color:#fff;font-size:10px;flex-shrink:0">
                    {{ $i+1 }}
                </span>
                <div class="flex-grow-1 min-width-0">
                    <div style="font-size:13px;font-weight:500" class="text-truncate">{{ $m->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $m->job_count }} job</div>
                </div>
                <span class="fw-semibold" style="font-size:12px">{{ $idr($m->total_commission) }}</span>
            </div>
            @empty
            <x-ui.empty-state title="Belum ada data" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Top Teknisi --}}
    <div class="col-xl-4">
        <x-ui.card title="Top Teknisi" subtitle="komisi bulan ini" class="h-100">
            @forelse ($topTeknisi as $i => $t)
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge rounded-circle d-flex align-items-center justify-content-center fw-bold"
                      style="width:24px;height:24px;background:{{ $rankColors[$i] ?? '#64748b' }};color:#fff;font-size:10px;flex-shrink:0">
                    {{ $i+1 }}
                </span>
                <div class="flex-grow-1 min-width-0">
                    <div style="font-size:13px;font-weight:500" class="text-truncate">{{ $t->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $t->job_count }} job</div>
                </div>
                <span class="fw-semibold" style="font-size:12px">{{ $idr($t->total_commission) }}</span>
            </div>
            @empty
            <x-ui.empty-state title="Belum ada data" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Recent Invoices --}}
    <div class="col-12">
        <x-ui.card title="Invoice Terbaru">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">No. Invoice</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Customer</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Total</th>
                            <th class="py-2 text-center text-muted fw-medium border-bottom">Status</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentInvoices as $inv)
                        <tr>
                            <td class="ps-0 py-2 font-monospace" style="font-size:11px">{{ $inv->invoice_number ?? '—' }}</td>
                            <td class="py-2">{{ $inv->customer->name ?? '—' }}</td>
                            <td class="py-2 text-end fw-semibold">{{ $idr($inv->total_price) }}</td>
                            <td class="py-2 text-center">
                                @php $vm = ['paid'=>'success','unpaid'=>'danger','partial'=>'warning','void'=>'neutral']; @endphp
                                <x-ui.badge :variant="$vm[$inv->status] ?? 'neutral'" size="sm">{{ ucfirst($inv->status) }}</x-ui.badge>
                            </td>
                            <td class="py-2 text-end text-muted" style="font-size:11px">{{ $inv->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center">
                                <x-ui.empty-state title="Belum ada invoice" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

</div>

@push('page-scripts')
<script>
(function () {
    const fmt = v => 'Rp ' + Number(v).toLocaleString('id-ID');
    const axis = v => v >= 1e9 ? (v/1e9).toFixed(1) + ' M' : (v/1e6).toFixed(0) + ' jt';
    const cat = {!! json_encode($executive['revenue']['by_category']) !!};
    const trend = {!! json_encode($executive['trend']) !!};
    new ApexCharts(document.getElementById('execCategoryChart'), {
        chart: { type: 'bar', height: 240, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [{ name: 'Omzet', data: Object.values(cat) }],
        xaxis: { categories: Object.keys(cat) },
        yaxis: { labels: { formatter: axis } },
        colors: ['#2563eb'], dataLabels: { enabled: false },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        tooltip: { y: { formatter: fmt } },
    }).render();
    new ApexCharts(document.getElementById('execTrendChart'), {
        chart: { type: 'line', height: 240, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [{ name: 'Omzet', data: trend.map(t => t.total) }],
        xaxis: { categories: trend.map(t => t.label) },
        yaxis: { labels: { formatter: axis } },
        colors: ['#2563eb'], stroke: { width: 3, curve: 'smooth' }, markers: { size: 4 },
        tooltip: { y: { formatter: fmt } },
    }).render();
})();
</script>
@endpush

@endsection
