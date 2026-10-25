@extends('layout.index')

@section('title', $title)
@section('route-breadcrumb', route('teknisi.orders.index'))
@section('breadcrumb', 'Semua Orderan')

@section('content')

@php
    $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
@endphp

{{-- Flash --}}

{{-- Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <x-ui.button :href="route('teknisi.dashboard')" variant="outline" size="sm">
        <x-slot name="icon"><svg viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot>
        Dashboard
    </x-ui.button>
    <div>
        <h5 class="mb-0 fw-semibold">Semua Orderan</h5>
        <div class="ui-text-muted" style="font-size:12px">{{ $employee->name }}</div>
    </div>
</div>

@include('teknisi._incoming-sales-orders')

{{-- Filter Tabs --}}
<x-ui.card class="mb-3">
    <div class="d-flex gap-2 flex-wrap">
        @foreach (['all' => 'Semua', 'active' => 'Aktif', 'completed' => 'Selesai'] as $key => $label)
            <a href="{{ route('teknisi.orders.index', ['status' => $key]) }}"
               class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</x-ui.card>

{{-- Orders List --}}
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">
                Orderan {{ $status === 'active' ? 'Aktif' : ($status === 'completed' ? 'Selesai' : 'Semua') }}
                <x-ui.badge variant="neutral" class="ms-1">{{ $orders->total() }}</x-ui.badge>
            </div>
        </div>
    @endslot

    @forelse ($orders as $order)
        @php
            $invoice    = $order->invoice;
            $workStatus = $invoice?->work_status ?? 'pending';
            $statusMap  = [
                'pending'   => ['label' => 'Menunggu Diterima', 'variant' => 'neutral'],
                'accepted'  => ['label' => 'Diterima',          'variant' => 'warning'],
                'completed' => ['label' => 'Selesai',           'variant' => 'success'],
            ];
            $ws = $statusMap[$workStatus] ?? ['label' => ucfirst($workStatus), 'variant' => 'neutral'];

            // Commission for this order
            $commRows   = $commissionMap->get($order->id, collect());
            $totalComm  = $commRows->sum('total');
            $commStatus = $commRows->first()?->status ?? null;
            $commVariant = match($commStatus) {
                'paid'     => 'success',
                'eligible' => 'primary',
                'pending'  => 'warning',
                default    => 'neutral',
            };
        @endphp

        <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--ui-border)">
            <div class="d-flex align-items-start gap-3">
                <div class="flex-grow-1 min-w-0">

                    {{-- Customer & status --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="fw-semibold" style="font-size:14px">{{ $order->name }}</span>
                        <x-ui.badge :variant="$ws['variant']" size="sm">{{ $ws['label'] }}</x-ui.badge>
                        @if ($order->install_type)
                            <x-ui.badge variant="neutral" size="sm">{{ strtoupper($order->install_type) }}</x-ui.badge>
                        @endif
                    </div>

                    {{-- Car info --}}
                    <div class="ui-text-muted mb-1" style="font-size:12px">
                        <iconify-icon icon="mdi:car-outline" class="me-1"></iconify-icon>
                        {{ $order->vehicle_brand }} {{ $order->vehicle_model }}
                        @if ($order->plat_number)
                            &bull; <strong>{{ $order->plat_number }}</strong>
                        @endif
                        @if ($order->branch)
                            &bull; <iconify-icon icon="mdi:map-marker-outline"></iconify-icon> {{ $order->branch->name }}
                        @endif
                    </div>

                    {{-- Invoice & commission --}}
                    <div class="d-flex flex-wrap align-items-center gap-3" style="font-size:12px">
                        @if ($invoice)
                            <span class="ui-text-muted font-monospace">{{ $invoice->invoice_number }}</span>
                            @if ($invoice->status === 'paid')
                                <x-ui.badge variant="success" size="sm">Lunas</x-ui.badge>
                            @elseif ($invoice->status === 'partial')
                                <x-ui.badge variant="warning" size="sm">DP</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral" size="sm">Belum Lunas</x-ui.badge>
                            @endif
                        @endif

                        {{-- Commission chip --}}
                        @if ($totalComm > 0)
                            <span class="fw-semibold" style="color:var(--ui-green,#10b981)">
                                <iconify-icon icon="mdi:currency-usd-circle-outline" class="me-1"></iconify-icon>{{ $idr($totalComm) }}
                            </span>
                        @endif
                    </div>

                    @if ($invoice?->completed_at)
                        <div class="ui-text-muted mt-1" style="font-size:11px">
                            <iconify-icon icon="mdi:check-all" class="text-success"></iconify-icon>
                            Selesai {{ $invoice->completed_at->format('d M Y H:i') }}
                        </div>
                    @elseif ($invoice?->accepted_at)
                        <div class="ui-text-muted mt-1" style="font-size:11px">
                            <iconify-icon icon="mdi:check" class="text-warning"></iconify-icon>
                            Diterima {{ $invoice->accepted_at->format('d M Y H:i') }}
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="d-flex flex-column gap-1 flex-shrink-0">
                    <x-ui.button :href="route('teknisi.orders.show', $order->id)" variant="outline" size="sm">
                        Detail
                    </x-ui.button>

                    @if ($isTeknisi && in_array($workStatus, ['pending', 'accepted']))
                        @if ($workStatus === 'pending')
                            <form action="{{ route('teknisi.job.accept', $order->id) }}" method="POST">
                                @csrf
                                <x-ui.button type="submit" variant="primary" size="sm" class="w-100"
                                        onclick="return confirm('Terima order ini?')">
                                    <iconify-icon icon="mdi:check"></iconify-icon> Terima
                                </x-ui.button>
                            </form>
                        @endif
                        <x-ui.button :href="route('teknisi.job.complete.form', $order->id)" variant="success" size="sm">
                            <iconify-icon icon="mdi:camera-outline"></iconify-icon> Selesaikan
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <x-ui.empty-state
            title="Belum ada orderan"
            description="Order dari manager akan muncul di sini setelah ditugaskan." />
    @endforelse

    @if ($orders->hasPages())
        @slot('footer')
            {{ $orders->links() }}
        @endslot
    @endif
</x-ui.card>

@endsection
