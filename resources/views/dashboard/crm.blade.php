@extends('layout.index')

@section('title', 'Dashboard CRM')
@section('route-breadcrumb', route('dashboard.crm'))
@section('breadcrumb', 'Dashboard CRM')

@section('content')

@php
    $winRate         = $totalLeads > 0 ? round($wonLeads / $totalLeads * 100, 1) : 0;
    $quotationSent   = $quotationStats->get('sent', 0)
                     + $quotationStats->get('accepted', 0)
                     + $quotationStats->get('rejected', 0);
    $quotationAccepted = $quotationStats->get('accepted', 0);
    $qtConvRate      = $quotationSent > 0 ? round($quotationAccepted / $quotationSent * 100, 1) : 0;
    $qtTotal         = $quotationStats->sum();

    // Lead funnel series (fixed stage order)
    $stageColorMap = [
        'new'       => '#86b6ef',
        'contacted' => '#3987e5',
        'follow_up' => '#eda100',
        'quoted'    => '#2a78d6',
        'won'       => '#008300',
        'lost'      => '#e34948',
    ];
    $funnelLabels = [];
    $funnelData   = [];
    $funnelColors = [];
    foreach (\App\Models\Lead::STATUSES as $key => $label) {
        $funnelLabels[] = $label;
        $funnelData[]   = (int) $leadFunnel->get($key, 0);
        $funnelColors[] = $stageColorMap[$key];
    }

    // SO pipeline series (skip statuses with 0)
    $soColorMap = [
        'pending'     => '#94a3b8',
        'confirmed'   => '#2a78d6',
        'in_progress' => '#eda100',
        'completed'   => '#008300',
        'cancelled'   => '#e34948',
    ];
    $soLabels = [];
    $soData   = [];
    $soColors = [];
    foreach (\App\Models\SalesOrder::STATUSES as $key => $label) {
        $val = (int) $soPipeline->get($key, 0);
        if ($val > 0) {
            $soLabels[] = $label;
            $soData[]   = $val;
            $soColors[] = $soColorMap[$key];
        }
    }

    // Lead by source (categorical palette, fixed order)
    $catPalette = ['#2a78d6','#008300','#e87ba4','#eda100','#1baf7a','#eb6834','#4a3aa7'];
    $srcLabels  = [];
    $srcData    = [];
    $srcColors  = [];
    $ci = 0;
    foreach (\App\Models\Lead::SOURCES as $key => $label) {
        $srcLabels[] = $label;
        $srcData[]   = (int) $leadSources->get($key, 0);
        $srcColors[] = $catPalette[$ci % count($catPalette)];
        $ci++;
    }

    // Status color map reused in table
    $soStatusBg  = ['pending'=>'#f1f5f9','confirmed'=>'#e0f2fe','in_progress'=>'#fef3c7','completed'=>'#dcfce7','cancelled'=>'#fee2e2'];
    $soStatusClr = ['pending'=>'#64748b','confirmed'=>'#0369a1','in_progress'=>'#92400e','completed'=>'#15803d','cancelled'=>'#991b1b'];

    $leadStatusBg  = ['new'=>'#e0f2fe','contacted'=>'#e0f7f4','follow_up'=>'#fef3c7','quoted'=>'#f3e8ff','won'=>'#dcfce7','lost'=>'#fee2e2'];
    $leadStatusClr = ['new'=>'#0369a1','contacted'=>'#065f46','follow_up'=>'#92400e','quoted'=>'#6b21a8','won'=>'#15803d','lost'=>'#991b1b'];
@endphp

{{-- ─ CSS tokens ──────────────────────────────────────────────────────────── --}}
<style>
:root {
    --crm-lead  : #2a78d6;
    --crm-won   : #008300;
    --crm-so    : #eda100;
    --crm-spk   : #eb6834;

    --crm-lead-bg : rgba(42,120,214,.10);
    --crm-won-bg  : rgba(0,131,0,.10);
    --crm-so-bg   : rgba(237,161,0,.10);
    --crm-spk-bg  : rgba(235,104,52,.10);
}
.dash-card         { border: 1px solid #f1f5f9 !important; }
.dash-card-lead    { border-left: 4px solid var(--crm-lead) !important; }
.dash-card-won     { border-left: 4px solid var(--crm-won)  !important; }
.dash-card-so      { border-left: 4px solid var(--crm-so)   !important; }
.dash-card-spk     { border-left: 4px solid var(--crm-spk)  !important; }

.crm-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 20px;
}
.dash-divider  { border-top: 1px solid #f1f5f9; margin-top: 12px; padding-top: 10px; }
.tbl-head    { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
</style>

{{-- ─ Page header ─────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-24">
    <div>
        <h5 class="mb-1">Dashboard CRM</h5>
        <p class="text-muted small mb-0">Ringkasan funnel lead, pipeline sales order, dan jadwal pemasangan</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <x-ui.button :href="route('leads.create')" variant="outline" size="sm">
            <iconify-icon icon="solar:add-circle-outline"></iconify-icon> Lead Baru
        </x-ui.button>
        <x-ui.button :href="route('sales-orders.index')" variant="primary" size="sm">
            <iconify-icon icon="solar:bill-list-outline"></iconify-icon> Sales Order
        </x-ui.button>
    </div>
</div>

{{-- ─ KPI Row ──────────────────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Total Lead --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <x-ui.card class="h-100 dash-card dash-card-lead">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <p class="text-muted small fw-medium mb-0">Total Lead</p>
                <div class="crm-icon" style="background:var(--crm-lead-bg);color:var(--crm-lead)">
                    <iconify-icon icon="mdi:account-plus-outline"></iconify-icon>
                </div>
            </div>
            <div class="fw-bold mb-1" style="font-size:1.5rem;color:var(--crm-lead)">
                {{ number_format($totalLeads) }}
            </div>
            <div class="text-muted small">Semua lead tercatat</div>
            <div class="dash-divider d-flex justify-content-between align-items-center small">
                <span class="text-muted">Bulan ini</span>
                <span class="fw-semibold" style="color:var(--crm-lead)">
                    +{{ number_format($leadsThisMonth) }}
                </span>
            </div>
        </x-ui.card>
    </div>

    {{-- Win Rate --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <x-ui.card class="h-100 dash-card dash-card-won">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <p class="text-muted small fw-medium mb-0">Win Rate</p>
                <div class="crm-icon" style="background:var(--crm-won-bg);color:var(--crm-won)">
                    <iconify-icon icon="mdi:trophy-outline"></iconify-icon>
                </div>
            </div>
            <div class="fw-bold mb-1" style="font-size:1.5rem;color:var(--crm-won)">
                {{ $winRate }}%
            </div>
            <div class="text-muted small">Dari {{ number_format($totalLeads) }} lead</div>
            <div class="dash-divider d-flex justify-content-between align-items-center small">
                <span class="text-muted">Berhasil (won)</span>
                <span class="fw-semibold" style="color:var(--crm-won)">
                    {{ number_format($wonLeads) }}
                </span>
            </div>
        </x-ui.card>
    </div>

    {{-- Active SO --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <x-ui.card class="h-100 dash-card dash-card-so">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <p class="text-muted small fw-medium mb-0">Sales Order Aktif</p>
                <div class="crm-icon" style="background:var(--crm-so-bg);color:var(--crm-so)">
                    <iconify-icon icon="mdi:clipboard-list-outline"></iconify-icon>
                </div>
            </div>
            <div class="fw-bold mb-1" style="font-size:1.5rem;color:var(--crm-so)">
                {{ number_format($activeSo) }}
            </div>
            <div class="text-muted small">Pending, dikonfirmasi, dikerjakan</div>
            <div class="dash-divider d-flex justify-content-between align-items-center small">
                <span class="text-muted">Sudah selesai</span>
                <span class="fw-semibold">{{ number_format($soPipeline->get('completed', 0)) }}</span>
            </div>
        </x-ui.card>
    </div>

    {{-- SPK Pending --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <x-ui.card class="h-100 dash-card dash-card-spk">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <p class="text-muted small fw-medium mb-0">SPK Menunggu</p>
                <div class="crm-icon" style="background:var(--crm-spk-bg);color:var(--crm-spk)">
                    <iconify-icon icon="mdi:qrcode"></iconify-icon>
                </div>
            </div>
            <div class="fw-bold mb-1" style="font-size:1.5rem;color:{{ $spkPending > 0 ? 'var(--crm-spk)' : '#94a3b8' }}">
                {{ number_format($spkPending) }}
            </div>
            <div class="text-muted small">SO aktif belum diterbitkan SPK</div>
            <div class="dash-divider small">
                @if($spkPending > 0)
                    <a href="{{ route('sales-orders.index') }}" class="fw-semibold text-decoration-none" style="color:var(--crm-spk)">
                        Terbitkan SPK <iconify-icon icon="mdi:arrow-right"></iconify-icon>
                    </a>
                @else
                    <span class="text-muted">Semua SO sudah ada SPK</span>
                @endif
            </div>
        </x-ui.card>
    </div>

</div>

{{-- ─ Charts Row 1: Lead Funnel + SO Pipeline ─────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Lead Funnel --}}
    <div class="col-12 col-lg-8">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">Lead Funnel</div>
                    <x-ui.button :href="route('leads.index')" variant="ghost" size="sm">
                        Lihat semua <iconify-icon icon="mdi:arrow-right"></iconify-icon>
                    </x-ui.button>
                </div>
            @endslot
            <div id="leadFunnelChart"></div>
        </x-ui.card>
    </div>

    {{-- SO Pipeline Donut --}}
    <div class="col-12 col-lg-4">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="ui-card-title">Pipeline Sales Order</div>
            @endslot
            @if(count($soData) > 0)
                <div id="soPipelineDonut" class="mb-3"></div>
                <div id="so-legend" class="d-flex flex-column gap-2"></div>
            @else
                <x-ui.empty-state title="Belum ada Sales Order" />
            @endif
        </x-ui.card>
    </div>

</div>

{{-- ─ Charts Row 2: Quotation Stats + Lead Source ─────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Quotation Stats --}}
    <div class="col-12 col-lg-5">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">Status Quotation</div>
                    <x-ui.button :href="route('quotations.index')" variant="ghost" size="sm">
                        Lihat semua <iconify-icon icon="mdi:arrow-right"></iconify-icon>
                    </x-ui.button>
                </div>
            @endslot
            @php
                $qtStatusMap = [
                    'draft'    => ['label' => 'Draft',       'color' => '#94a3b8'],
                    'sent'     => ['label' => 'Terkirim',    'color' => '#1baf7a'],
                    'accepted' => ['label' => 'Diterima',    'color' => '#008300'],
                    'rejected' => ['label' => 'Ditolak',     'color' => '#e34948'],
                    'expired'  => ['label' => 'Kedaluwarsa', 'color' => '#eda100'],
                ];
            @endphp

            @if($qtTotal > 0)
                {{-- Conversion highlight --}}
                <div class="rounded-3 p-3 mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <iconify-icon icon="mdi:percent-outline" style="color:#008300;font-size:15px"></iconify-icon>
                        <span class="small fw-medium" style="color:#14532d">Conversion Rate</span>
                    </div>
                    <div class="fw-bold" style="font-size:1.4rem;color:#008300">{{ $qtConvRate }}%</div>
                    <div class="text-muted small">
                        {{ $quotationAccepted }} diterima dari {{ $quotationSent }} terkirim
                    </div>
                </div>

                {{-- Breakdown list --}}
                <div class="d-flex flex-column gap-3">
                    @foreach($qtStatusMap as $key => $meta)
                        @php $count = $quotationStats->get($key, 0); @endphp
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:9px;height:9px;border-radius:2px;background:{{ $meta['color'] }};flex-shrink:0"></div>
                            <span class="small text-muted flex-grow-1">{{ $meta['label'] }}</span>
                            <span class="fw-semibold small">{{ $count }}</span>
                            <div style="width:60px;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden">
                                <div style="height:100%;background:{{ $meta['color'] }};border-radius:3px;width:{{ $qtTotal > 0 ? round($count/$qtTotal*100) : 0 }}%"></div>
                            </div>
                            <span class="text-muted" style="font-size:11px;width:28px;text-align:right">
                                {{ $qtTotal > 0 ? round($count/$qtTotal*100) : 0 }}%
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-ui.empty-state title="Belum ada quotation" />
            @endif
        </x-ui.card>
    </div>

    {{-- Lead by Source --}}
    <div class="col-12 col-lg-7">
        <x-ui.card class="h-100 dash-card">
            @slot('header')
                <div class="ui-card-title">Sumber Lead</div>
            @endslot
            @if(array_sum($srcData) > 0)
                <div id="leadSourceChart"></div>
            @else
                <x-ui.empty-state title="Belum ada data sumber lead" />
            @endif
        </x-ui.card>
    </div>

</div>

{{-- ─ Upcoming Jadwal Pemasangan ───────────────────────────────────────────── --}}
<x-ui.card class="mb-4 dash-card">
    @slot('header')
        <div class="d-flex align-items-center justify-content-between">
            <div class="ui-card-title">Jadwal Pemasangan — 7 Hari ke Depan</div>
            <x-ui.button :href="route('jadwal.index')" variant="ghost" size="sm">
                Kalender lengkap <iconify-icon icon="mdi:arrow-right"></iconify-icon>
            </x-ui.button>
        </div>
    @endslot

    @if($upcomingInstallations->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:13px">
            <thead style="background:#f8fafc">
                <tr>
                    <th class="ps-4 py-3 fw-semibold text-muted tbl-head">Tanggal</th>
                    <th class="py-3 fw-semibold text-muted tbl-head">Prospek</th>
                    <th class="py-3 fw-semibold text-muted tbl-head">Nomor SO</th>
                    <th class="py-3 fw-semibold text-muted tbl-head">Kategori</th>
                    <th class="py-3 fw-semibold text-muted tbl-head">Teknisi</th>
                    <th class="pe-4 py-3 fw-semibold text-muted tbl-head">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingInstallations as $so)
                <tr>
                    <td class="ps-4 py-3">
                        <div class="fw-semibold">
                            {{ $so->scheduled_date->translatedFormat('d M Y') }}
                        </div>
                        @if($so->scheduled_date->isToday())
                            <x-ui.badge variant="primary" size="sm" class="mt-1">Hari Ini</x-ui.badge>
                        @elseif($so->scheduled_date->isTomorrow())
                            <x-ui.badge variant="warning" size="sm" class="mt-1">Besok</x-ui.badge>
                        @endif
                    </td>
                    <td class="py-3">
                        <div class="fw-medium">{{ $so->prospect_name }}</div>
                        @if($so->vehicle_plate)
                            <div class="text-muted" style="font-size:11px">
                                {{ strtoupper($so->vehicle_plate) }}
                            </div>
                        @endif
                    </td>
                    <td class="py-3">
                        <a href="{{ route('sales-orders.show', $so) }}"
                           class="text-decoration-none fw-semibold"
                           style="color:var(--crm-lead)">
                            {{ $so->so_number }}
                        </a>
                    </td>
                    <td class="py-3">
                        <x-ui.badge variant="primary" size="sm">{{ $so->category_label }}</x-ui.badge>
                    </td>
                    <td class="py-3 text-muted small">
                        {{ $so->category === 'building_film'
                            ? ($so->technicians->pluck('name')->join(', ') ?: '—')
                            : ($so->tukang?->name ?? '—') }}
                    </td>
                    <td class="pe-4 py-3">
                        <span class="badge rounded-pill px-3 py-1"
                              style="background:{{ $soStatusBg[$so->status] ?? '#f1f5f9' }};color:{{ $soStatusClr[$so->status] ?? '#64748b' }};font-size:11px;font-weight:600">
                            {{ $so->status_label }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <x-ui.empty-state title="Tidak ada jadwal pemasangan dalam 7 hari ke depan" />
    @endif
</x-ui.card>

{{-- ─ Recent Leads ─────────────────────────────────────────────────────────── --}}
<x-ui.card class="mb-4 dash-card">
    @slot('header')
        <div class="d-flex align-items-center justify-content-between">
            <div class="ui-card-title">Lead Terbaru</div>
            <x-ui.button :href="route('leads.index')" variant="ghost" size="sm">
                Semua lead <iconify-icon icon="mdi:arrow-right"></iconify-icon>
            </x-ui.button>
        </div>
    @endslot

    @if($recentLeads->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:13px">
            <thead style="background:#f8fafc">
                <tr>
                    <th class="ps-4 py-3 fw-semibold text-muted tbl-head">Nama</th>
                    <th class="py-3 fw-semibold text-muted tbl-head">Kategori</th>
                    <th class="py-3 fw-semibold text-muted tbl-head">Sumber</th>
                    <th class="py-3 fw-semibold text-muted tbl-head">Cabang</th>
                    <th class="py-3 fw-semibold text-muted tbl-head">Status</th>
                    <th class="pe-4 py-3 fw-semibold text-muted tbl-head">Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentLeads as $lead)
                <tr>
                    <td class="ps-4 py-3">
                        <a href="{{ route('leads.show', $lead) }}"
                           class="fw-medium text-decoration-none text-dark">
                            {{ $lead->name }}
                        </a>
                        @if($lead->phone)
                            <div class="text-muted" style="font-size:11px">{{ $lead->phone }}</div>
                        @endif
                    </td>
                    <td class="py-3 text-muted small">{{ $lead->category_label }}</td>
                    <td class="py-3 text-muted small">{{ $lead->source_label }}</td>
                    <td class="py-3 text-muted small">{{ $lead->branch?->name ?? '—' }}</td>
                    <td class="py-3">
                        <span class="badge rounded-pill px-3 py-1"
                              style="background:{{ $leadStatusBg[$lead->status] ?? '#f1f5f9' }};color:{{ $leadStatusClr[$lead->status] ?? '#64748b' }};font-size:11px;font-weight:600">
                            {{ $lead->status_label }}
                        </span>
                    </td>
                    <td class="pe-4 py-3 text-muted small">
                        {{ $lead->created_at->translatedFormat('d M Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <x-ui.empty-state title="Belum ada lead yang tercatat" />
    @endif
</x-ui.card>

@endsection

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const anim = { enabled: true, easing: 'easeinout', speed: 800 };
    const gridClr = '#f1f5f9';
    const textClr = '#94a3b8';

    // ─ Lead Funnel: horizontal bar (distributed, one color per stage) ─────────
    const funnelLabels = {!! json_encode($funnelLabels) !!};
    const funnelData   = {!! json_encode($funnelData) !!};
    const funnelColors = {!! json_encode($funnelColors) !!};

    if (funnelData.some(v => v > 0)) {
        new ApexCharts(document.getElementById('leadFunnelChart'), {
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false },
                fontFamily: 'inherit',
                animations: anim,
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    borderRadiusApplication: 'end',
                    distributed: true,
                    barHeight: '52%',
                    dataLabels: { position: 'right' },
                }
            },
            colors: funnelColors,
            series: [{ name: 'Jumlah Lead', data: funnelData }],
            xaxis: {
                categories: funnelLabels,
                labels: {
                    style: { colors: textClr, fontSize: '11px' },
                    formatter: v => Math.round(v),
                },
                axisBorder: { show: false },
                axisTicks:  { show: false },
            },
            yaxis: {
                labels: { style: { colors: '#374151', fontSize: '12px', fontWeight: '500' } },
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', fontWeight: '600', colors: ['#374151'] },
                offsetX: 6,
                formatter: v => v > 0 ? v : '',
            },
            grid: {
                borderColor: gridClr,
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
            },
            legend:  { show: false },
            tooltip: { theme: 'light', y: { formatter: v => v + ' lead' } },
        }).render();
    } else {
        document.getElementById('leadFunnelChart').innerHTML =
            '<div class="text-center text-muted py-5">' +
            '<iconify-icon icon="mdi:chart-bar" style="font-size:2rem;display:block;margin-bottom:8px"></iconify-icon>' +
            '<small>Belum ada data lead</small></div>';
    }

    // ─ SO Pipeline: donut ─────────────────────────────────────────────────────
    const soLabels = {!! json_encode($soLabels) !!};
    const soData   = {!! json_encode($soData) !!};
    const soColors = {!! json_encode($soColors) !!};

    if (soData.length > 0 && soData.some(v => v > 0)) {
        const soTotal = soData.reduce((a, b) => a + b, 0);

        new ApexCharts(document.getElementById('soPipelineDonut'), {
            chart: {
                type: 'donut',
                height: 200,
                fontFamily: 'inherit',
                toolbar: { show: false },
                animations: anim,
            },
            series: soData,
            labels: soLabels,
            colors: soColors,
            legend:     { show: false },
            dataLabels: { enabled: false },
            stroke:     { width: 2, colors: ['#fff'] },
            plotOptions: {
                pie: { donut: { size: '68%', labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Total SO',
                        fontSize: '11px',
                        color: textClr,
                        formatter: () => soTotal,
                    },
                    value: { fontSize: '20px', fontWeight: '700', color: '#1e293b' },
                }}},
            },
            tooltip: { theme: 'light', y: { formatter: v => v + ' SO' } },
        }).render();

        // Manual legend
        const legend = document.getElementById('so-legend');
        soLabels.forEach((lbl, i) => {
            const pct = soTotal > 0 ? Math.round(soData[i] / soTotal * 100) : 0;
            const row = document.createElement('div');
            row.style.cssText = 'display:flex;align-items:center;gap:8px;font-size:12px';
            row.innerHTML =
                '<span style="width:9px;height:9px;border-radius:2px;background:' + soColors[i] + ';flex-shrink:0;display:inline-block"></span>' +
                '<span style="flex:1;color:#64748b">' + lbl + '</span>' +
                '<span style="font-weight:600;color:#1e293b">' + soData[i] + '</span>' +
                '<span style="color:#94a3b8;width:32px;text-align:right">' + pct + '%</span>';
            legend.appendChild(row);
        });
    }

    // ─ Lead by Source: horizontal bar (distributed) ───────────────────────────
    const srcLabels = {!! json_encode($srcLabels) !!};
    const srcData   = {!! json_encode($srcData) !!};
    const srcColors = {!! json_encode($srcColors) !!};

    if (srcData.some(v => v > 0)) {
        new ApexCharts(document.getElementById('leadSourceChart'), {
            chart: {
                type: 'bar',
                height: 260,
                toolbar: { show: false },
                fontFamily: 'inherit',
                animations: anim,
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    borderRadiusApplication: 'end',
                    distributed: true,
                    barHeight: '52%',
                    dataLabels: { position: 'right' },
                }
            },
            colors: srcColors,
            series: [{ name: 'Lead', data: srcData }],
            xaxis: {
                categories: srcLabels,
                labels: {
                    style: { colors: textClr, fontSize: '11px' },
                    formatter: v => Math.round(v),
                },
                axisBorder: { show: false },
                axisTicks:  { show: false },
            },
            yaxis: {
                labels: { style: { colors: '#374151', fontSize: '12px', fontWeight: '500' } },
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', fontWeight: '600', colors: ['#374151'] },
                offsetX: 6,
                formatter: v => v > 0 ? v : '',
            },
            grid: {
                borderColor: gridClr,
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
            },
            legend:  { show: false },
            tooltip: { theme: 'light', y: { formatter: v => v + ' lead' } },
        }).render();
    }

});
</script>
@endpush
