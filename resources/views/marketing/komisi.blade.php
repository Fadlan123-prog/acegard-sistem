@extends('layout.index')

@section('title', $title)
@section('route-breadcrumb', route('marketing.komisi'))
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

{{-- Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <x-ui.button :href="route('marketing.dashboard')" variant="outline" size="sm">
        <x-slot name="icon"><svg viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot>
        Kembali
    </x-ui.button>
    <div>
        <h5 class="mb-0 fw-semibold">Komisi Saya</h5>
        <div class="ui-text-muted" style="font-size:12px">{{ $employee->name }} &bull; Marketing</div>
    </div>
</div>

{{-- Withdrawal banner --}}
@if ($pendingWithdrawal)
    <x-ui.alert type="info" class="mb-3" :dismissible="false">
        <x-slot name="title">Pengajuan Sedang Diproses</x-slot>
        Total <strong>{{ $idr($pendingWithdrawal->total_amount) }}</strong> — menunggu persetujuan superadmin.
    </x-ui.alert>
@elseif ($canRequestWithdrawal && $eligibleUnbatchedCount > 0)
    <x-ui.alert type="success" class="mb-3" :dismissible="false">
        <x-slot name="title">Hari ini tanggal 15 — Komisi siap dicairkan!</x-slot>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-1">
            <span>{{ $eligibleUnbatchedCount }} komisi senilai <strong>{{ $idr($eligibleUnbatchedTotal) }}</strong></span>
            <x-ui.button variant="success" size="sm" data-bs-toggle="modal" data-bs-target="#modalPencairan">
                Ajukan Pencairan
            </x-ui.button>
        </div>
    </x-ui.alert>
@elseif ($eligibleUnbatchedCount > 0)
    <x-ui.alert type="warning" class="mb-3" :dismissible="false">
        <x-slot name="title">{{ $eligibleUnbatchedCount }} komisi siap dicairkan ({{ $idr($eligibleUnbatchedTotal) }})</x-slot>
        Pencairan hanya pada <strong>tanggal 15</strong> setiap bulan.
        @if ($nowWib->day < 15)
            Tersisa <strong>{{ 15 - $nowWib->day }} hari</strong> lagi.
        @else
            Coba bulan depan tanggal 15.
        @endif
    </x-ui.alert>
@endif

{{-- Summary KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <x-ui.stats-card
            label="Pending"
            :value="$idr($summary['pending_total'])"
            color="amber" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span>Menunggu invoice lunas</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-4">
        <x-ui.stats-card
            label="Siap Dicairkan"
            :value="$idr($summary['eligible_total'])"
            color="green" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span>Bisa diajukan tgl. 15</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-4">
        <x-ui.stats-card
            label="Total Diterima"
            :value="$idr($summary['paid_total'])"
            color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span>Sudah dicairkan</span>
        </x-ui.stats-card>
    </div>
</div>

{{-- Tier info --}}
<x-ui.card class="mb-4">
    <div class="d-flex flex-wrap gap-3 align-items-center" style="font-size:12px">
        <span class="ui-text-muted fw-medium"><iconify-icon icon="mdi:information-outline" class="me-1"></iconify-icon>Tier Komisi Marketing:</span>
        <span><x-ui.badge variant="neutral" size="sm">&lt; Rp 200 jt</x-ui.badge> &rarr; 2%</span>
        <span><x-ui.badge variant="primary" size="sm">Rp 200–499 jt</x-ui.badge> &rarr; 3%</span>
        <span><x-ui.badge variant="success" size="sm">≥ Rp 500 jt</x-ui.badge> &rarr; 5%</span>
    </div>
</x-ui.card>

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
                <span class="fw-semibold" style="font-size:13px">{{ $customer->name ?? '—' }}</span>
                <x-ui.badge :variant="$sc['variant']" size="sm">{{ $sc['label'] }}</x-ui.badge>
            </div>
            @if ($customer && ($customer->vehicle_brand || $customer->vehicle_model))
                <div class="ui-text-muted" style="font-size:12px;margin-bottom:4px">
                    {{ $customer->vehicle_brand }} {{ $customer->vehicle_model }}
                </div>
            @endif
            <div class="d-flex justify-content-between align-items-center">
                <span class="ui-text-muted" style="font-size:11px">
                    {{ $c->created_at->timezone('Asia/Jakarta')->format('d M Y') }}
                    @if ($invoice) &bull; {{ $invoice->invoice_number }} @endif
                </span>
                <span class="fw-bold" style="color:var(--ui-green,#10b981)">{{ $idr($c->amount) }}</span>
            </div>
            @if ($invoice && $invoice->total_price)
                <div class="ui-text-muted" style="font-size:10px;margin-top:2px">
                    Omset: {{ $idr($invoice->total_price) }}
                </div>
            @endif
        </div>
        @empty
            <x-ui.empty-state title="Belum ada riwayat komisi marketing" />
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
                    <th class="py-2 fw-medium text-end border-bottom" style="color:var(--ui-text-muted)">Omset</th>
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
                    <td class="py-2 fw-medium">{{ $customer->name ?? '—' }}</td>
                    <td class="py-2 ui-text-muted">
                        {{ $customer->vehicle_brand ?? '' }} {{ $customer->vehicle_model ?? '' }}
                    </td>
                    <td class="py-2 text-end ui-text-muted" style="font-size:11px">
                        {{ $invoice && $invoice->total_price ? $idr($invoice->total_price) : '—' }}
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
                        <x-ui.empty-state title="Belum ada riwayat komisi marketing" />
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

{{-- Withdrawal modal --}}
@if ($canRequestWithdrawal && $eligibleUnbatchedCount > 0)
<div class="modal fade" id="modalPencairan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('marketing.withdrawal.request') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-semibold">Ajukan Pencairan Komisi</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <x-ui.alert type="success" :dismissible="false" class="mb-3">
                        <strong>{{ $eligibleUnbatchedCount }}</strong> komisi siap dicairkan —
                        total <strong>{{ $idr($eligibleUnbatchedTotal) }}</strong>
                    </x-ui.alert>
                    <x-ui.form-field label="Catatan (opsional)" name="requester_notes">
                        <textarea name="requester_notes" class="form-control" rows="2"
                                  placeholder="Nomor rekening atau catatan lain…"></textarea>
                    </x-ui.form-field>
                </div>
                <div class="modal-footer">
                    <x-ui.button variant="outline" size="sm" data-bs-dismiss="modal">Batal</x-ui.button>
                    <x-ui.button variant="success" size="sm" type="submit">Kirim Pengajuan</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
