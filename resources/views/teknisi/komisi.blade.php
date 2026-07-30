@extends('layout.index')

@section('title', $title)
@section('route-breadcrumb', route('teknisi.komisi'))
@section('breadcrumb', 'Komisi Saya')

@section('content')

@php
    $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');

    $statusConfig = [
        'pending'  => ['label' => 'Pending',       'variant' => 'warning'],
        'eligible' => ['label' => 'Siap Cair',     'variant' => 'success'],
        'paid'     => ['label' => 'Sudah Dibayar', 'variant' => 'neutral'],
        'cancelled'=> ['label' => 'Dibatalkan',    'variant' => 'danger'],
    ];
@endphp

{{-- Flash --}}
@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if (session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

{{-- Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <x-ui.button href="{{ route('teknisi.dashboard') }}" variant="outline" size="sm">
        <x-slot name="icon"><svg viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot>
        Kembali
    </x-ui.button>
    <div>
        <h5 class="mb-0 fw-semibold">Komisi Saya</h5>
        <div class="ui-text-muted" style="font-size:12px">{{ $employee->name }}</div>
    </div>
</div>

{{-- Summary KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <x-ui.stats-card
            label="Pending"
            :value="$idr($summary['pending_total'])"
            color="amber"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span>Menunggu invoice lunas</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-4">
        <x-ui.stats-card
            label="Siap Dicairkan"
            :value="$idr($summary['eligible_total'])"
            color="green"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            @if ($summary['eligible_total'] > 0)
                <a href="{{ route('teknisi.pencairan') }}" class="ui-link" style="font-size:11px">Lihat pencairan &rarr;</a>
            @else
                <span>Bisa diajukan tgl. 15</span>
            @endif
        </x-ui.stats-card>
    </div>
    <div class="col-sm-4">
        <x-ui.stats-card
            label="Total Diterima"
            :value="$idr($summary['paid_total'])"
            color="blue"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span>Sudah dicairkan</span>
        </x-ui.stats-card>
    </div>
</div>

{{-- Commission table --}}
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Riwayat Komisi</div>
            <span class="ui-text-muted" style="font-size:12px">{{ $commissions->total() }} transaksi</span>
        </div>
    @endslot

    {{-- Mobile list --}}
    <div class="d-md-none" style="margin:-1px">
        @forelse ($commissions as $c)
        @php
            $sc = $statusConfig[$c->status] ?? ['label' => ucfirst($c->status), 'variant' => 'neutral'];
            $customer = $c->customer;
            $invoice  = $c->invoice;
        @endphp
        <div class="py-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--ui-border)">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="fw-semibold" style="font-size:13px">{{ $customer?->name ?? '—' }}</span>
                <x-ui.badge :variant="$sc['variant']" size="sm">{{ $sc['label'] }}</x-ui.badge>
            </div>
            <div class="ui-text-muted" style="font-size:12px;margin-bottom:4px">
                {{ $customer?->vehicle_brand ?? '' }} {{ $customer?->vehicle_model ?? '' }}
                @if ($customer?->plat_number) &bull; {{ $customer->plat_number }} @endif
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="ui-text-muted" style="font-size:11px">
                    {{ $c->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                    @if ($invoice) &bull; {{ $invoice->invoice_number }} @endif
                </span>
                <span class="fw-bold" style="color:var(--ui-green,#10b981)">{{ $idr($c->amount) }}</span>
            </div>
            @if ($c->status === 'paid' && $c->batch)
                <div class="ui-text-muted" style="font-size:10px;margin-top:2px">
                    Batch: {{ $c->batch->batch_number }}
                    @if ($c->batch->paid_at)
                        &bull; {{ \Carbon\Carbon::parse($c->batch->paid_at)->timezone('Asia/Jakarta')->format('d M Y') }}
                    @endif
                </div>
            @endif
        </div>
        @empty
            <x-ui.empty-state title="Belum ada riwayat komisi" />
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="d-none d-md-block table-responsive" style="margin:-1px">
        <table class="table table-hover align-middle mb-0" style="font-size:12px">
            <thead>
                <tr>
                    <th class="py-2 fw-medium border-bottom" style="color:var(--ui-text-muted)">Tanggal</th>
                    <th class="py-2 fw-medium border-bottom" style="color:var(--ui-text-muted)">Customer</th>
                    <th class="py-2 fw-medium border-bottom" style="color:var(--ui-text-muted)">Kendaraan</th>
                    <th class="py-2 fw-medium border-bottom" style="color:var(--ui-text-muted)">Invoice</th>
                    <th class="py-2 fw-medium text-center border-bottom" style="color:var(--ui-text-muted)">Status</th>
                    <th class="py-2 fw-medium text-end border-bottom" style="color:var(--ui-text-muted)">Komisi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($commissions as $c)
                @php
                    $sc = $statusConfig[$c->status] ?? ['label' => ucfirst($c->status), 'variant' => 'neutral'];
                    $customer = $c->customer;
                    $invoice  = $c->invoice;
                @endphp
                <tr>
                    <td class="py-2 ui-text-muted" style="font-size:11px;white-space:nowrap">
                        {{ $c->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                    </td>
                    <td class="py-2 fw-medium">{{ $customer?->name ?? '—' }}</td>
                    <td class="py-2 ui-text-muted">
                        {{ $customer?->vehicle_brand ?? '' }} {{ $customer?->vehicle_model ?? '' }}
                        @if ($customer?->plat_number)
                            <br><span style="font-size:11px">{{ $customer->plat_number }}</span>
                        @endif
                    </td>
                    <td class="py-2 font-monospace ui-text-muted" style="font-size:11px">
                        {{ $invoice->invoice_number ?? '—' }}
                        @if ($c->status === 'paid' && $c->batch)
                            <br><span style="font-size:10px">{{ $c->batch->batch_number }}</span>
                        @endif
                    </td>
                    <td class="py-2 text-center">
                        <x-ui.badge :variant="$sc['variant']" size="sm">{{ $sc['label'] }}</x-ui.badge>
                    </td>
                    <td class="py-2 text-end fw-bold" style="color:var(--ui-green,#10b981)">
                        {{ $idr($c->amount) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-5">
                        <x-ui.empty-state title="Belum ada riwayat komisi" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($commissions->hasPages())
        @slot('footer')
            {{ $commissions->links() }}
        @endslot
    @endif
</x-ui.card>

@endsection
