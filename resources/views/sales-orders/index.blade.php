@extends('dashboard.index')

@section('title', 'Sales Order')
@section('route-breadcrumb', 'sales-orders.index')
@section('breadcrumb', 'Sales Order')

@section('content')

@php
    $allStatuses  = \App\Models\SalesOrder::STATUSES;
    $activeStatus = request('status', '');
    $total        = $statusCounts->sum();

    $variantMap = [
        'pending'     => 'neutral',
        'confirmed'   => 'info',
        'in_progress' => 'warning',
        'completed'   => 'success',
        'cancelled'   => 'danger',
    ];
@endphp

{{-- Status tab filter --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <x-ui.button href="{{ route('sales-orders.index', request()->except('status', 'page')) }}"
        :variant="$activeStatus === '' ? 'primary' : 'outline'" size="sm">
        Semua
        <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $total }}</x-ui.badge>
    </x-ui.button>
    @foreach($allStatuses as $key => $label)
        @php $count = $statusCounts[$key] ?? 0; @endphp
        <x-ui.button href="{{ route('sales-orders.index', array_merge(request()->except('status','page'), ['status' => $key])) }}"
            :variant="$activeStatus === $key ? 'primary' : 'outline'" size="sm">
            {{ $label }}
            @if($count)
                <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $count }}</x-ui.badge>
            @endif
        </x-ui.button>
    @endforeach
</div>

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Sales Order</div>
            <x-ui.button href="{{ route('sales-orders.create') }}" variant="primary" size="sm">+ Buat SO</x-ui.button>
        </div>
    @endslot

    <form method="GET" action="{{ route('sales-orders.index') }}" id="filterForm"
          class="d-flex align-items-center gap-2 flex-wrap mb-3">

        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <input type="text" name="search" value="{{ request('search') }}"
               class="form-control" placeholder="Cari nama / no. SO..."
               style="max-width:240px">

        <select name="category" class="form-select" style="max-width:180px"
                onchange="document.getElementById('filterForm').submit()">
            <option value="">-- Semua Kategori --</option>
            @foreach(\App\Models\SalesOrder::CATEGORIES as $key => $label)
                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        @if(auth()->user()->hasRole('superadmin') && $branches->isNotEmpty())
        <select name="branch_id" class="form-select" style="max-width:180px"
                onchange="document.getElementById('filterForm').submit()">
            <option value="">-- Semua Cabang --</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @endif

        <x-ui.button type="submit" variant="outline">Cari</x-ui.button>

        @if(request()->hasAny(['search', 'category', 'branch_id']))
            <x-ui.button href="{{ route('sales-orders.index', request()->only('status')) }}" variant="outline">Reset</x-ui.button>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table bordered-table mb-0">
            <thead>
                <tr>
                    <th>No. SO</th>
                    <th>Prospek</th>
                    <th>Kategori</th>
                    <th>Tgl SO</th>
                    <th>Jadwal Pasang</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    @if(auth()->user()->hasRole('superadmin'))
                    <th>Cabang</th>
                    @endif
                    <th style="width:120px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesOrders as $so)
                <tr>
                    <td>
                        <div class="fw-semibold small font-monospace">{{ $so->so_number }}</div>
                        @if($so->quotation)
                            <div class="text-muted" style="font-size:11px">
                                <iconify-icon icon="mdi:file-document-outline" class="me-1"></iconify-icon>{{ $so->quotation->quotation_number }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $so->prospect_name }}</div>
                        @if($so->prospect_phone)
                            <div class="text-muted small">{{ $so->prospect_phone }}</div>
                        @endif
                    </td>
                    <td>
                        <x-ui.badge variant="info">{{ $so->category_label }}</x-ui.badge>
                    </td>
                    <td class="small">{{ $so->date->format('d M Y') }}</td>
                    <td class="small">
                        @if($so->scheduled_date)
                            @php $isPast = $so->scheduled_date->isPast() && !in_array($so->status, ['completed','cancelled']); @endphp
                            <span class="{{ $isPast ? 'text-danger' : 'text-muted' }}">
                                {{ $so->scheduled_date->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold">
                        Rp {{ number_format($so->total_amount, 0, ',', '.') }}
                    </td>
                    <td>
                        <x-ui.badge variant="{{ $variantMap[$so->status] ?? 'neutral' }}" :dot="true">
                            {{ $so->status_label }}
                        </x-ui.badge>
                    </td>
                    @if(auth()->user()->hasRole('superadmin'))
                    <td class="small text-muted">{{ $so->branch?->name ?? '—' }}</td>
                    @endif
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('sales-orders.show', $so) }}"
                               class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Lihat Detail">
                                <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                            </a>
                            @if($so->isEditable())
                            <a href="{{ route('sales-orders.edit', $so) }}"
                               class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Edit">
                                <iconify-icon icon="lucide:edit"></iconify-icon>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="99" class="py-5">
                        <x-ui.empty-state
                            title="Tidak ada Sales Order ditemukan"
                            description="Buat SO dari Quotation yang sudah diterima, atau langsung dari tombol di atas."
                        >
                            <a href="{{ route('sales-orders.create') }}" class="btn btn-primary btn-sm">
                                + Buat Sales Order
                            </a>
                        </x-ui.empty-state>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $salesOrders->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
