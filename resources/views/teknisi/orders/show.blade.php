@extends('layout.index')

@section('title', $title)
@section('route-breadcrumb', route('teknisi.orders.index'))
@section('breadcrumb', 'Detail Order')

@section('content')

@php
    $idr        = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $invoice    = $invoice ?? null;
    $workStatus = $invoice?->work_status ?? 'pending';
    $statusMap  = [
        'pending'   => ['label' => 'Menunggu Diterima', 'variant' => 'neutral'],
        'accepted'  => ['label' => 'Sedang Dikerjakan', 'variant' => 'warning'],
        'completed' => ['label' => 'Selesai',            'variant' => 'success'],
    ];
    $ws = $statusMap[$workStatus] ?? ['label' => ucfirst($workStatus), 'variant' => 'neutral'];
@endphp

{{-- Flash --}}
@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if (session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

{{-- Back + actions --}}
<div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
    <div class="d-flex align-items-center gap-3">
        <x-ui.button href="{{ route('teknisi.orders.index') }}" variant="outline" size="sm">
            <x-slot name="icon"><svg viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot>
            Semua Order
        </x-ui.button>
        <div>
            <h5 class="mb-0 fw-semibold">{{ $customer->name }}</h5>
            <div class="ui-text-muted" style="font-size:12px">
                {{ $customer->vehicle_brand }} {{ $customer->vehicle_model }}
                @if ($customer->plat_number) &bull; {{ $customer->plat_number }} @endif
            </div>
        </div>
    </div>
    <x-ui.badge :variant="$ws['variant']">{{ $ws['label'] }}</x-ui.badge>
</div>

<div class="row g-3">

    {{-- Left column --}}
    <div class="col-lg-7">

        {{-- Order info --}}
        <x-ui.card class="mb-3">
            @slot('header')
                <div class="ui-card-title">Informasi Kendaraan &amp; Order</div>
            @endslot

            <div class="row g-3" style="font-size:13px">
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Pelanggan</div>
                    <div class="fw-semibold">{{ $customer->name }}</div>
                    @if ($customer->phone)
                        <div class="ui-text-muted"><iconify-icon icon="mdi:phone-outline" class="me-1"></iconify-icon>{{ $customer->phone }}</div>
                    @endif
                </div>
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Kendaraan</div>
                    <div class="fw-semibold">{{ $customer->vehicle_brand }} {{ $customer->vehicle_model }}</div>
                    @if ($customer->plat_number)
                        <div class="ui-text-muted">Plat: <strong>{{ $customer->plat_number }}</strong></div>
                    @endif
                    @if ($customer->vehicle_color)
                        <div class="ui-text-muted">Warna: {{ $customer->vehicle_color }}</div>
                    @endif
                </div>
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Tipe Pemasangan</div>
                    <x-ui.badge variant="primary">{{ strtoupper($customer->install_type ?? '-') }}</x-ui.badge>
                </div>
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Cabang</div>
                    <div>{{ $customer->branch?->name ?? '-' }}</div>
                    @if ($customer->branch?->city)
                        <div class="ui-text-muted">{{ $customer->branch->city }}</div>
                    @endif
                </div>
                @if ($customer->tukang && $customer->kenek)
                    <div class="col-sm-6">
                        <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Teknisi</div>
                        <div>{{ $customer->tukang->name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Kenek</div>
                        <div>{{ $customer->kenek->name }}</div>
                    </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Invoice info --}}
        @if ($invoice)
        <x-ui.card class="mb-3">
            @slot('header')
                <div class="d-flex justify-content-between align-items-center">
                    <div class="ui-card-title">Informasi Invoice</div>
                    @if ($invoice->status === 'paid')
                        <x-ui.badge variant="success">Lunas</x-ui.badge>
                    @elseif ($invoice->status === 'partial')
                        <x-ui.badge variant="warning">DP</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">Belum Lunas</x-ui.badge>
                    @endif
                </div>
            @endslot

            <div class="row g-3" style="font-size:13px">
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">No. Invoice</div>
                    <div class="fw-semibold font-monospace">{{ $invoice->invoice_number ?? '-' }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Total Harga</div>
                    <div class="fw-bold" style="font-size:16px;color:var(--ui-green,#10b981)">
                        {{ $idr($invoice->total_price ?? 0) }}
                    </div>
                </div>
                @if ($invoice->downpayment)
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Down Payment</div>
                    <div>{{ $idr($invoice->downpayment) }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Sisa Pembayaran</div>
                    <div class="fw-semibold text-danger">{{ $idr(max(0, ($invoice->total_price ?? 0) - ($invoice->downpayment ?? 0))) }}</div>
                </div>
                @endif
            </div>
        </x-ui.card>
        @endif

        {{-- Timeline --}}
        <x-ui.card class="mb-3">
            @slot('header')
                <div class="ui-card-title">Status Pekerjaan</div>
            @endslot

            <div class="py-1" style="font-size:13px">

                {{-- Step 1: Assigned --}}
                <div class="d-flex gap-3 align-items-start mb-3">
                    <div class="flex-shrink-0 d-flex flex-column align-items-center" style="width:28px">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:28px;height:28px;background:var(--ui-green,#10b981);color:white;font-size:12px">
                            <iconify-icon icon="mdi:account-plus-outline"></iconify-icon>
                        </div>
                        <div style="width:2px;height:32px;background:var(--ui-border);margin-top:4px"></div>
                    </div>
                    <div>
                        <div class="fw-semibold">Ditugaskan oleh Manager</div>
                        <div class="ui-text-muted" style="font-size:12px">
                            {{ $customer->created_at?->format('d M Y H:i') ?? '-' }}
                        </div>
                    </div>
                </div>

                {{-- Step 2: Accepted --}}
                <div class="d-flex gap-3 align-items-start mb-3">
                    <div class="flex-shrink-0 d-flex flex-column align-items-center" style="width:28px">
                        @if ($invoice?->accepted_at)
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;background:var(--ui-amber,#f59e0b);color:white;font-size:12px">
                                <iconify-icon icon="mdi:check"></iconify-icon>
                            </div>
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;background:var(--ui-border);color:var(--ui-text-muted);font-size:12px">
                                <iconify-icon icon="mdi:clock-outline"></iconify-icon>
                            </div>
                        @endif
                        <div style="width:2px;height:32px;background:var(--ui-border);margin-top:4px"></div>
                    </div>
                    <div>
                        <div class="fw-semibold {{ $invoice?->accepted_at ? '' : 'ui-text-muted' }}">
                            Diterima Teknisi
                        </div>
                        <div class="ui-text-muted" style="font-size:12px">
                            {{ $invoice?->accepted_at?->format('d M Y H:i') ?? 'Belum diterima' }}
                        </div>
                    </div>
                </div>

                {{-- Step 3: Completed --}}
                <div class="d-flex gap-3 align-items-start">
                    <div class="flex-shrink-0" style="width:28px">
                        @if ($invoice?->completed_at)
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;background:var(--ui-green,#10b981);color:white;font-size:12px">
                                <iconify-icon icon="mdi:check-all"></iconify-icon>
                            </div>
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;background:var(--ui-border);color:var(--ui-text-muted);font-size:12px">
                                <iconify-icon icon="mdi:clock-outline"></iconify-icon>
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="fw-semibold {{ $invoice?->completed_at ? '' : 'ui-text-muted' }}">
                            Pekerjaan Selesai
                        </div>
                        <div class="ui-text-muted" style="font-size:12px">
                            {{ $invoice?->completed_at?->format('d M Y H:i') ?? 'Belum selesai' }}
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Action buttons --}}
        @if ($isTeknisi && in_array($workStatus, ['pending', 'accepted']))
        <div class="d-flex gap-2">
            @if ($workStatus === 'pending')
                <form action="{{ route('teknisi.job.accept', $customer->id) }}" method="POST" class="flex-fill">
                    @csrf
                    <x-ui.button type="submit" variant="primary" class="w-100"
                            onclick="return confirm('Terima order ini?')">
                        <iconify-icon icon="mdi:check" class="me-1"></iconify-icon> Terima Order
                    </x-ui.button>
                </form>
            @endif
            <x-ui.button href="{{ route('teknisi.job.complete.form', $customer->id) }}"
                    variant="success" class="{{ $workStatus === 'pending' ? 'flex-fill' : 'w-100' }}">
                <iconify-icon icon="mdi:camera-outline" class="me-1"></iconify-icon> Selesaikan Pekerjaan
            </x-ui.button>
        </div>
        @endif

    </div>

    {{-- Right column --}}
    <div class="col-lg-5">

        {{-- Komisi per mobil ini --}}
        <x-ui.card class="mb-3">
            @slot('header')
                <div class="ui-card-title">
                    <iconify-icon icon="mdi:currency-usd-circle-outline" class="me-1 text-success"></iconify-icon>
                    Komisi dari Order Ini
                </div>
            @endslot

            @forelse ($myCommissions as $c)
            @php
                $cVariant = match($c->status) {
                    'paid'     => 'success',
                    'eligible' => 'primary',
                    'pending'  => 'warning',
                    default    => 'neutral',
                };
                $cLabel = match($c->status) {
                    'paid'     => 'Sudah Dibayar',
                    'eligible' => 'Siap Cair',
                    'pending'  => 'Menunggu Invoice Lunas',
                    'cancelled'=> 'Dibatalkan',
                    default    => ucfirst($c->status),
                };
            @endphp
            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                 style="border-color:var(--ui-border)">
                <div>
                    <div class="fw-semibold" style="font-size:14px">{{ $idr($c->amount) }}</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <x-ui.badge :variant="$cVariant" size="sm">{{ $cLabel }}</x-ui.badge>
                        @if ($c->install_type)
                            <x-ui.badge variant="neutral" size="sm">{{ strtoupper($c->install_type) }}</x-ui.badge>
                        @endif
                    </div>
                    @if ($c->status === 'paid' && $c->paid_at)
                        <div class="ui-text-muted mt-1" style="font-size:11px">
                            Dibayar: {{ \Carbon\Carbon::parse($c->paid_at)->format('d M Y') }}
                        </div>
                    @endif
                    @if ($c->status === 'eligible' && $c->eligible_at)
                        <div class="ui-text-muted mt-1" style="font-size:11px">
                            Siap sejak: {{ \Carbon\Carbon::parse($c->eligible_at)->format('d M Y') }}
                        </div>
                    @endif
                </div>
            </div>
            @empty
                @if ($workStatus === 'completed')
                    <x-ui.alert type="info" :dismissible="false">
                        Komisi akan dihitung setelah invoice pelanggan ini lunas.
                    </x-ui.alert>
                @else
                    <x-ui.empty-state
                        title="Komisi belum tersedia"
                        description="Komisi dihitung setelah pekerjaan selesai dan invoice lunas." />
                @endif
            @endforelse

            @if ($myCommissions->isNotEmpty())
                <div class="border-top pt-3 mt-1 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold ui-text-muted" style="font-size:12px">Total Komisi</span>
                    <span class="fw-bold" style="font-size:16px;color:var(--ui-green,#10b981)">
                        {{ $idr($myCommissions->sum('amount')) }}
                    </span>
                </div>
            @endif
        </x-ui.card>

        {{-- Foto pekerjaan --}}
        @if ($invoice && ($invoice->job_photo || $invoice->checklist_photo))
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title">Foto Pekerjaan</div>
            @endslot

            <div class="d-flex gap-3 flex-wrap">
                @if ($invoice->job_photo)
                    <div>
                        <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Foto Hasil</div>
                        <a href="{{ Storage::url($invoice->job_photo) }}" target="_blank">
                            <img src="{{ Storage::url($invoice->job_photo) }}"
                                 class="rounded border"
                                 style="width:140px;height:140px;object-fit:cover"
                                 alt="Foto Pekerjaan">
                        </a>
                    </div>
                @endif
                @if ($invoice->checklist_photo)
                    <div>
                        <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Foto Checklist</div>
                        <a href="{{ Storage::url($invoice->checklist_photo) }}" target="_blank">
                            <img src="{{ Storage::url($invoice->checklist_photo) }}"
                                 class="rounded border"
                                 style="width:140px;height:140px;object-fit:cover"
                                 alt="Foto Checklist">
                        </a>
                    </div>
                @endif
            </div>
        </x-ui.card>
        @endif

    </div>
</div>

@endsection
