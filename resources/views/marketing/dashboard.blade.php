@extends('layout.index')

@section('title', 'Dashboard Marketing')
@section('route-breadcrumb', route('marketing.dashboard'))
@section('breadcrumb', 'Dashboard Marketing')

@section('content')

@php $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Lead Aktif Saya"
            :value="number_format($myLeadsActive)"
            color="blue"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Total: {{ number_format($myLeadsTotal) }} lead</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Konversi Bulan Ini"
            :value="$myLeadsWon . ' Won'"
            color="green"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Dari {{ $myLeadsThisMonth }} lead — {{ $conversionRate }}%</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Komisi Bulan Ini"
            :value="$idr($myCommissionMonth)"
            color="amber"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">{{ now()->translatedFormat('F Y') }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Komisi Belum Dibayar"
            :value="$idr($myCommissionTotal)"
            color="red"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Status: eligible</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">

    {{-- Follow-up Leads --}}
    <div class="col-xl-7">
        <x-ui.card>
            @slot('header')
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="ui-card-title">Perlu Follow Up</div>
                </div>
                <x-ui.button href="{{ route('leads.index') }}" variant="outline" size="sm">Lihat Semua</x-ui.button>
            </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Nama</th>
                            <th class="py-2 text-muted fw-medium border-bottom">No. HP</th>
                            <th class="py-2 text-center text-muted fw-medium border-bottom">Status</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Follow Up</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($followUpLeads as $lead)
                        @php $isOverdue = $lead->follow_up_date && $lead->follow_up_date->isPast(); @endphp
                        <tr>
                            <td class="ps-0 py-2">
                                <a href="{{ route('leads.show', $lead) }}" class="fw-medium text-decoration-none" style="font-size:12px">{{ $lead->name }}</a>
                                @if($lead->vehicle_brand)
                                    <div class="text-muted" style="font-size:10px">{{ $lead->vehicle_brand }} {{ $lead->vehicle_model }}</div>
                                @endif
                            </td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ $lead->phone ?? '—' }}</td>
                            <td class="py-2 text-center">
                                @php $vm = ['new'=>'primary','contacted'=>'info','follow_up'=>'warning','qualified'=>'neutral','won'=>'success','lost'=>'danger']; @endphp
                                <x-ui.badge :variant="$vm[$lead->status] ?? 'neutral'" :dot="true" size="sm">
                                    {{ ucfirst(str_replace('_',' ',$lead->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-2 text-end" style="font-size:11px;color:{{ $isOverdue ? '#ef4444' : 'inherit' }}">
                                @if($isOverdue) <iconify-icon icon="mdi:alarm-light-outline" class="me-1"></iconify-icon> @endif
                                {{ $lead->follow_up_date ? $lead->follow_up_date->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">
                                <x-ui.empty-state title="Semua lead sudah di-follow up" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    {{-- Lead Funnel --}}
    <div class="col-xl-5">
        <x-ui.card title="Funnel Lead Saya" class="h-100">
            @php
                $funnelLabels = ['new'=>'Baru','contacted'=>'Dihubungi','follow_up'=>'Follow Up','qualified'=>'Qualified','won'=>'Won','lost'=>'Lost'];
                $funnelColors = ['new'=>'#6366f1','contacted'=>'#0ea5e9','follow_up'=>'#f59e0b','qualified'=>'#8b5cf6','won'=>'#10b981','lost'=>'#ef4444'];
                $funnelTotal  = $leadFunnel->sum();
            @endphp
            @foreach ($funnelLabels as $key => $label)
            @php $count = $leadFunnel->get($key, 0); $pct = $funnelTotal > 0 ? round(($count/$funnelTotal)*100) : 0; @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size:12px">{{ $label }}</span>
                    <span class="fw-semibold" style="font-size:12px">{{ $count }}</span>
                </div>
                <div class="progress" style="height:5px;border-radius:4px">
                    <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $funnelColors[$key] }};border-radius:4px"></div>
                </div>
            </div>
            @endforeach
        </x-ui.card>
    </div>

    {{-- Recent Leads --}}
    <div class="col-12">
        <x-ui.card>
            @slot('header')
            <div class="d-flex justify-content-between align-items-center">
                <div class="ui-card-title">Lead Terbaru</div>
                <x-ui.button href="{{ route('leads.create') }}" variant="primary" size="sm"><iconify-icon icon="mdi:plus" class="me-1"></iconify-icon>Tambah Lead</x-ui.button>
            </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Nama</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Sumber</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Kendaraan</th>
                            <th class="py-2 text-center text-muted fw-medium border-bottom">Status</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentLeads as $lead)
                        <tr>
                            <td class="ps-0 py-2">
                                <a href="{{ route('leads.show', $lead) }}" class="text-decoration-none fw-medium" style="font-size:12px">{{ $lead->name }}</a>
                            </td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ $lead->source ?? '—' }}</td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ $lead->vehicle_brand ? $lead->vehicle_brand.' '.($lead->vehicle_model ?? '') : '—' }}</td>
                            <td class="py-2 text-center">
                                @php $vm = ['new'=>'primary','contacted'=>'info','follow_up'=>'warning','qualified'=>'neutral','won'=>'success','lost'=>'danger']; @endphp
                                <x-ui.badge :variant="$vm[$lead->status] ?? 'neutral'" size="sm">
                                    {{ ucfirst(str_replace('_',' ',$lead->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-2 text-end text-muted" style="font-size:11px">{{ $lead->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center">
                                <x-ui.empty-state title="Belum ada lead" description="Mulai dengan menambah lead baru.">
                                    <x-ui.button href="{{ route('leads.create') }}" variant="primary" size="sm">Tambah Lead</x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

</div>

@endsection
