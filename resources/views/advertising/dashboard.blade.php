@extends('layout.index')

@section('title', 'Dashboard Advertising')
@section('route-breadcrumb', route('advertising.dashboard'))
@section('breadcrumb', 'Dashboard Advertising')

@section('content')

@php
    $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $statusLabels = \App\Models\Lead::STATUSES;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <x-ui.button :href="route('advertising.spend.index', ['month' => $month, 'year' => $year])" variant="outline" size="sm">
        <iconify-icon icon="mdi:cash-plus" class="me-1"></iconify-icon> Kelola Spend
    </x-ui.button>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Ad Spend" :value="$idr($totalSpend)" color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Seluruh channel, bulan ini</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Total Leads" :value="number_format($totalLeads)" color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Dari channel berbayar</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Follow Up" :value="number_format($totalFollowed)" color="amber" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Leads follow up, penawaran, atau berhasil</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Repeat Order" :value="number_format($totalRepeat)" color="green" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Pelanggan lama yang order lagi</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <x-ui.card title="Performa per Channel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Channel</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Spend</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Leads</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Follow Up</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Repeat Order</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Closing Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($channels as $c)
                        <tr>
                            <td class="ps-0 py-2 fw-medium">{{ $c['label'] }}</td>
                            <td class="py-2 text-end">{{ $idr($c['spend']) }}</td>
                            <td class="py-2 text-end">{{ $c['leads'] }}</td>
                            <td class="py-2 text-end">{{ $c['followed'] }}</td>
                            <td class="py-2 text-end">{{ $c['repeat'] }}</td>
                            <td class="py-2 text-end">
                                <x-ui.badge :variant="$c['closing_rate'] >= 5.3 ? 'success' : 'warning'" size="sm">
                                    {{ $c['closing_rate'] }}%
                                </x-ui.badge>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    <div class="col-xl-4">
        <x-ui.card title="Funnel Advertising" subtitle="Leads bulan ini">
            @php
                $allLeads  = (int) $funnel->sum();
                $followed  = (int) (($funnel['follow_up'] ?? 0) + ($funnel['quoted'] ?? 0) + ($funnel['won'] ?? 0));
                $stages = [
                    ['label' => 'Leads Masuk', 'value' => $allLeads, 'text' => number_format($allLeads, 0, ',', '.'), 'bar' => $allLeads],
                    ['label' => 'CPL (biaya per lead)', 'value' => $avgCpl, 'text' => $avgCpl ? 'Rp ' . number_format($avgCpl, 0, ',', '.') : '-', 'bar' => null],
                    ['label' => 'Follow Up', 'value' => $followed, 'text' => number_format($followed, 0, ',', '.'), 'bar' => $followed],
                    ['label' => 'Repeat Order', 'value' => $repeatOrders, 'text' => number_format($repeatOrders, 0, ',', '.'), 'bar' => $repeatOrders],
                ];
                $max = max(1, $allLeads);
            @endphp
            @foreach ($stages as $st)
            <div class="mb-3">
                <div class="d-flex justify-content-between" style="font-size:12px">
                    <span>{{ $st['label'] }}</span>
                    <span class="fw-semibold">{{ $st['text'] }}</span>
                </div>
                @if ($st['bar'] !== null)
                <div class="progress" style="height:6px">
                    <div class="progress-bar" style="width: {{ round($st['bar'] / $max * 100) }}%"></div>
                </div>
                @endif
            </div>
            @endforeach
        </x-ui.card>
    </div>
</div>


<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <x-dashboard.donut id="donutAdSpend" title="Porsi Spend per Channel" format="rp"
            :labels="array_column($channels, 'label')" :values="array_column($channels, 'spend')" />
    </div>
    <div class="col-lg-6">
        <x-dashboard.donut id="donutAdLeads" title="Porsi Leads per Channel"
            :labels="array_column($channels, 'label')" :values="array_column($channels, 'leads')" />
    </div>
</div>

@endsection
