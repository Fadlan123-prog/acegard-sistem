@extends('layout.index')

@section('title', $title)
@section('route-breadcrumb', route('teknisi.pencairan'))
@section('breadcrumb', 'Riwayat Pencairan')

@section('content')

@php
    $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');

    $statusConfig = [
        'draft'    => ['label' => 'Menunggu Persetujuan', 'variant' => 'warning'],
        'approved' => ['label' => 'Disetujui',            'variant' => 'primary'],
        'rejected' => ['label' => 'Ditolak',              'variant' => 'danger'],
        'paid'     => ['label' => 'Dibayar',              'variant' => 'success'],
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
        <h5 class="mb-0 fw-semibold">Riwayat Pencairan</h5>
        <div class="ui-text-muted" style="font-size:12px">{{ $employee->name }}</div>
    </div>
</div>

{{-- Total paid --}}
<x-ui.stats-card
    label="Total Komisi Diterima"
    :value="$idr($totalPaid)"
    color="green"
    class="mb-4"
    icon='<svg viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
>
    <span>Semua pencairan berstatus dibayar</span>
</x-ui.stats-card>

{{-- Batch list --}}
@forelse ($batches as $batch)
@php
    $sc = $statusConfig[$batch->status] ?? ['label' => ucfirst($batch->status), 'variant' => 'neutral'];
@endphp
<x-ui.card class="mb-3">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="fw-semibold font-monospace" style="font-size:13px">{{ $batch->batch_number }}</div>
            <div class="ui-text-muted" style="font-size:12px;margin-top:2px">
                Periode:
                {{ \Carbon\Carbon::parse($batch->period_from)->format('d M Y') }}
                –
                {{ \Carbon\Carbon::parse($batch->period_to)->format('d M Y') }}
            </div>
        </div>
        <x-ui.badge :variant="$sc['variant']">{{ $sc['label'] }}</x-ui.badge>
    </div>

    @if ($batch->status === 'rejected' && $batch->notes)
        <x-ui.alert type="danger" :dismissible="false" class="mb-3">{{ $batch->notes }}</x-ui.alert>
    @endif

    @if ($batch->status === 'paid' && $batch->paid_at)
        <x-ui.alert type="success" :dismissible="false" class="mb-3">
            Dibayar pada {{ \Carbon\Carbon::parse($batch->paid_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
        </x-ui.alert>
    @endif

    @if ($batch->requester_notes)
        <div class="ui-text-muted mb-3" style="font-size:12px">
            <iconify-icon icon="mdi:chat-outline" class="me-1"></iconify-icon>{{ $batch->requester_notes }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid var(--ui-border)">
        <div>
            <span class="ui-text-muted" style="font-size:12px">{{ $batch->commissions->count() }} komisi</span>
            @if ($batch->commissions->isNotEmpty())
                <button class="btn btn-link btn-sm p-0 ms-2 ui-text-muted text-decoration-none"
                        style="font-size:11px"
                        data-bs-toggle="collapse"
                        data-bs-target="#batch-{{ $batch->id }}-detail">
                    Lihat detail <iconify-icon icon="mdi:chevron-down"></iconify-icon>
                </button>
            @endif
        </div>
        <span class="fw-bold" style="font-size:16px;color:{{ $batch->status === 'paid' ? 'var(--ui-green,#10b981)' : 'inherit' }}">
            {{ $idr($batch->total_amount) }}
        </span>
    </div>

    @if ($batch->commissions->isNotEmpty())
        <div class="collapse mt-2" id="batch-{{ $batch->id }}-detail">
            @foreach ($batch->commissions->take(20) as $c)
            <div class="d-flex justify-content-between py-1 ui-text-muted" style="font-size:11px;border-bottom:1px solid var(--ui-border)">
                <span>{{ $c->created_at->format('d/m/Y') }}</span>
                <span class="fw-medium">{{ $idr($c->amount) }}</span>
            </div>
            @endforeach
            @if ($batch->commissions->count() > 20)
                <div class="ui-text-muted mt-1" style="font-size:11px">
                    ... dan {{ $batch->commissions->count() - 20 }} lainnya
                </div>
            @endif
        </div>
    @endif
</x-ui.card>
@empty
<x-ui.card>
    <x-ui.empty-state title="Belum ada riwayat pengajuan pencairan">
        <x-ui.button href="{{ route('teknisi.dashboard') }}" variant="outline" size="sm">
            Kembali ke Dashboard
        </x-ui.button>
    </x-ui.empty-state>
</x-ui.card>
@endforelse

@if ($batches->hasPages())
    <div class="mt-3">{{ $batches->links() }}</div>
@endif

@endsection
