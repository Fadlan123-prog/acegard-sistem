@extends('layout.index')

@section('title', 'Dashboard Manajer Operasional')
@section('route-breadcrumb', route('manager-ops.dashboard'))
@section('breadcrumb', 'Dashboard Operasional')

@section('content')

@php $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

<x-dashboard.birthday-widget :birthdays="$upcomingBirthdays" />

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Pemasangan Hari Ini"
            :value="number_format($installationsToday)"
            color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Invoice Menunggu Rilis"
            :value="number_format($pendingRelease)"
            color="amber" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Komisi belum dirilis</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Pekerjaan Aktif"
            :value="number_format($activeJobs)"
            color="green" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Confirmed &amp; In Progress</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Stok Film Menipis"
            :value="number_format($lowStock)"
            color="red" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Sisa ≤ 10 meter</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">

    {{-- Pending Commission Release --}}
    <div class="col-xl-6">
        <x-ui.card>
            @slot('header')
            <div class="d-flex justify-content-between align-items-center">
                <div class="ui-card-title">Invoice Menunggu Rilis Komisi</div>
                <x-ui.button variant="outline" size="sm" href="{{ route('commission.index') }}" style="font-size:11px">Rilis Komisi</x-ui.button>
            </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Customer</th>
                            <th class="py-2 text-muted fw-medium border-bottom">No. Invoice</th>
                            <th class="py-2 text-center text-muted fw-medium border-bottom">Status</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingInvoices as $inv)
                        <tr>
                            <td class="ps-0 py-2">
                                <div style="font-size:12px;font-weight:500">{{ $inv->customer->name ?? '—' }}</div>
                            </td>
                            <td class="py-2 font-monospace text-muted" style="font-size:11px">{{ $inv->invoice_number ?? '—' }}</td>
                            <td class="py-2 text-center">
                                <x-ui.badge variant="success" size="sm">Lunas</x-ui.badge>
                            </td>
                            <td class="py-2 text-end text-muted" style="font-size:11px">
                                {{ $inv->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">
                                <x-ui.empty-state title="Semua komisi sudah dirilis" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    {{-- Upcoming Installations --}}
    <div class="col-xl-6">
        <x-ui.card title="Jadwal Pemasangan (7 Hari)" subtitle="Akan datang">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">No. SPK</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Teknisi</th>
                            <th class="py-2 text-center text-muted fw-medium border-bottom">Status</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($upcomingInstallations as $so)
                        @php
                            $statusMap = ['confirmed'=>'primary','in_progress'=>'warning','pending'=>'neutral'];
                            $statusVariant = $statusMap[$so->status] ?? 'neutral';
                        @endphp
                        <tr>
                            <td class="ps-0 py-2 font-monospace text-muted" style="font-size:11px">{{ $so->spk_number ?? '#'.$so->id }}</td>
                            <td class="py-2" style="font-size:12px">
                                {{ $so->category === 'building_film'
                                    ? ($so->technicians->pluck('name')->join(', ') ?: '—')
                                    : ($so->tukang->name ?? '—') }}
                            </td>
                            <td class="py-2 text-center">
                                <x-ui.badge :variant="$statusVariant" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $so->status)) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-2 text-end text-muted" style="font-size:11px">
                                {{ \Carbon\Carbon::parse($so->scheduled_date)->format('d/m/Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">
                                <x-ui.empty-state title="Tidak ada jadwal pemasangan mendatang" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    {{-- Low Stock Alerts --}}
    <div class="col-12">
        <x-ui.card title="Peringatan Stok Film" subtitle="Sisa ≤ 10 meter">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Kode Film</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Nama Film</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Cabang</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Sisa (meter)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockAlerts as $stock)
                        <tr>
                            <td class="ps-0 py-2 font-monospace text-muted" style="font-size:11px">{{ $stock->film_type ?? '—' }}</td>
                            <td class="py-2 fw-medium" style="font-size:12px">{{ $stock->darkness_code ?? '—' }}</td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ $stock->branch->name ?? '—' }}</td>
                            <td class="py-2 text-end">
                                <x-ui.badge variant="{{ $stock->remaining_length_meter <= 5 ? 'danger' : 'warning' }}" size="sm">
                                    {{ number_format($stock->remaining_length_meter, 1) }} m
                                </x-ui.badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">
                                <x-ui.empty-state title="Semua stok film masih aman" />
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
