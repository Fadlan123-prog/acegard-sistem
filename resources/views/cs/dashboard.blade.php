@extends('layout.index')

@section('title', 'Dashboard Customer Service')
@section('route-breadcrumb', route('cs.dashboard'))
@section('breadcrumb', 'Dashboard CS')

@section('content')

@php $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <x-ui.stats-card
            label="Lead Perlu Follow Up"
            :value="number_format($totalFollowUp)"
            color="amber"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Status: new, contacted, follow_up</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-4">
        <x-ui.stats-card
            label="Invoice Belum Lunas"
            :value="number_format($totalPendingPayments)"
            color="red"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Sisa: {{ $idr($totalOutstandingAmount) }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-4">
        <x-ui.stats-card
            label="Klaim Garansi Aktif"
            :value="number_format($totalActiveClaims)"
            color="purple"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Submitted &amp; under review</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">

    {{-- Follow-up Leads --}}
    <div class="col-xl-6">
        <x-ui.card>
            @slot('header')
            <div class="d-flex justify-content-between align-items-center">
                <div class="ui-card-title">Lead Perlu Follow Up</div>
                <x-ui.button href="{{ route('leads.index') }}" variant="outline" size="sm">Semua Lead</x-ui.button>
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
                                    <div class="text-muted" style="font-size:10px">{{ $lead->vehicle_brand }}</div>
                                @endif
                            </td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ $lead->phone ?? '—' }}</td>
                            <td class="py-2 text-center">
                                @php $vm = ['new'=>'primary','contacted'=>'info','follow_up'=>'warning']; @endphp
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

    {{-- Pending Payments --}}
    <div class="col-xl-6">
        <x-ui.card title="Reminder Pelunasan">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Customer</th>
                            <th class="py-2 text-muted fw-medium border-bottom">No. Invoice</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Sisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingPayments as $inv)
                        <tr>
                            <td class="ps-0 py-2">
                                <div style="font-size:12px;font-weight:500">{{ $inv->customer_name }}</div>
                                <div class="text-muted" style="font-size:10px">{{ $inv->customer_phone }}</div>
                            </td>
                            <td class="py-2 font-monospace text-muted" style="font-size:11px">{{ $inv->invoice_number ?? '—' }}</td>
                            <td class="py-2 text-end fw-semibold" style="color:#ef4444">{{ $idr($inv->remaining_payment) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center">
                                <x-ui.empty-state title="Semua invoice sudah lunas" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    {{-- Warranty Expiring (Car Film) --}}
    <div class="col-xl-6">
        <x-ui.card title="Garansi Kaca Film Hampir Expired" subtitle="30 hari ke depan">
            @forelse ($warrantyExpiringCar as $c)
            @php $urgent = $c->warantee_end->diffInDays() <= 7; @endphp
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--ui-border)">
                <div>
                    <div style="font-size:12px;font-weight:500">{{ $c->name }}</div>
                    <div class="text-muted" style="font-size:10px">{{ $c->phone_number }} — {{ $c->vehicle_brand }} {{ $c->vehicle_model }}</div>
                </div>
                <x-ui.badge :variant="$urgent ? 'danger' : 'warning'" size="sm">
                    {{ $c->warantee_end->format('d/m/Y') }}
                </x-ui.badge>
            </div>
            @empty
            <x-ui.empty-state title="Tidak ada garansi yang hampir expired" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Warranty Expiring (PPF) --}}
    <div class="col-xl-6">
        <x-ui.card title="Garansi PPF Hampir Expired" subtitle="30 hari ke depan">
            @forelse ($warrantyExpiringPpf as $inst)
            @php
                $end    = $inst->warranty_end ? \Carbon\Carbon::parse($inst->warranty_end) : null;
                $urgent = $end && $end->diffInDays() <= 7;
            @endphp
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--ui-border)">
                <div>
                    <div style="font-size:12px;font-weight:500">{{ $inst->customerPpf->name ?? '—' }}</div>
                    <div class="text-muted" style="font-size:10px">{{ $inst->warranty_code }}</div>
                </div>
                <x-ui.badge :variant="$urgent ? 'danger' : 'warning'" size="sm">
                    {{ $end ? $end->format('d/m/Y') : '—' }}
                </x-ui.badge>
            </div>
            @empty
            <x-ui.empty-state title="Tidak ada garansi PPF yang hampir expired" />
            @endforelse
        </x-ui.card>
    </div>

</div>

@endsection
