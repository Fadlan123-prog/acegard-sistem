@extends('dashboard.index')

@section('title', 'Quotation')
@section('route-breadcrumb', 'quotations.index')
@section('breadcrumb', 'Quotation')

@section('content')

@php
    $allStatuses  = \App\Models\Quotation::STATUSES;
    $activeStatus = request('status', '');
    $total        = $statusCounts->sum();

    $variantMap = [
        'draft'    => 'neutral',
        'sent'     => 'info',
        'accepted' => 'success',
        'rejected' => 'danger',
        'expired'  => 'warning',
    ];
@endphp

{{-- Status tab filter --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <x-ui.button href="{{ route('quotations.index', request()->except('status', 'page')) }}"
        :variant="$activeStatus === '' ? 'primary' : 'outline'" size="sm">
        Semua
        <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $total }}</x-ui.badge>
    </x-ui.button>
    @foreach($allStatuses as $key => $label)
        @php $count = $statusCounts[$key] ?? 0; @endphp
        <x-ui.button href="{{ route('quotations.index', array_merge(request()->except('status', 'page'), ['status' => $key])) }}"
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
            <div class="ui-card-title">Daftar Quotation</div>
            <x-ui.button href="{{ route('quotations.create') }}" variant="primary" size="sm">+ Buat Quotation</x-ui.button>
        </div>
    @endslot

    <form method="GET" action="{{ route('quotations.index') }}" id="filterForm"
          class="d-flex align-items-center gap-2 flex-wrap mb-3">

        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <input type="text" name="search" value="{{ request('search') }}"
               class="form-control" placeholder="Cari nama / no. quotation..."
               style="max-width:240px">

        <select name="category" class="form-select" style="max-width:180px"
                onchange="document.getElementById('filterForm').submit()">
            <option value="">-- Semua Kategori --</option>
            @foreach(\App\Models\Quotation::CATEGORIES as $key => $label)
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
            <x-ui.button href="{{ route('quotations.index', request()->only('status')) }}" variant="outline">Reset</x-ui.button>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table bordered-table mb-0">
            <thead>
                <tr>
                    <th>No. Quotation</th>
                    <th>Prospek</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                    <th>Berlaku s/d</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    @if(auth()->user()->hasRole('superadmin'))
                    <th>Cabang</th>
                    @endif
                    <th style="width:120px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $q)
                @php $rs = $q->resolvedStatus(); @endphp
                <tr>
                    <td>
                        <div class="fw-semibold small font-monospace">{{ $q->quotation_number }}</div>
                        @if($q->lead)
                            <div class="text-muted" style="font-size:11px">
                                <iconify-icon icon="mdi:target" class="me-1"></iconify-icon>{{ $q->lead->name }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $q->prospect_name }}</div>
                        @if($q->prospect_phone)
                            <div class="text-muted small">{{ $q->prospect_phone }}</div>
                        @endif
                    </td>
                    <td>
                        <x-ui.badge variant="info">{{ $q->category_label }}</x-ui.badge>
                    </td>
                    <td class="small">{{ $q->date->format('d M Y') }}</td>
                    <td class="small">
                        @php $expired = $q->valid_until->isPast() && !in_array($q->status, ['accepted','rejected']); @endphp
                        <span class="{{ $expired ? 'text-danger' : 'text-muted' }}">
                            {{ $q->valid_until->format('d M Y') }}
                        </span>
                    </td>
                    <td class="text-end fw-semibold">
                        Rp {{ number_format($q->total_amount, 0, ',', '.') }}
                    </td>
                    <td>
                        <x-ui.badge variant="{{ $variantMap[$rs] ?? 'neutral' }}" :dot="true">
                            {{ \App\Models\Quotation::STATUSES[$rs] ?? $rs }}
                        </x-ui.badge>
                    </td>
                    @if(auth()->user()->hasRole('superadmin'))
                    <td class="small text-muted">{{ $q->branch?->name ?? '—' }}</td>
                    @endif
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('quotations.show', $q) }}"
                               class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Lihat Detail">
                                <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                            </a>
                            @if($q->isEditable())
                            <a href="{{ route('quotations.edit', $q) }}"
                               class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Edit">
                                <iconify-icon icon="lucide:edit"></iconify-icon>
                            </a>
                            @endif
                            <a href="{{ route('quotations.print', $q) }}" target="_blank"
                               class="w-32-px h-32-px bg-warning-focus text-warning-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Print">
                                <iconify-icon icon="solar:printer-outline"></iconify-icon>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="99" class="py-5">
                        <x-ui.empty-state
                            title="Tidak ada quotation ditemukan"
                            description="Buat quotation pertama dari lead yang sudah ada atau langsung dari tombol di atas."
                        >
                            <a href="{{ route('quotations.create') }}" class="btn btn-primary btn-sm">
                                + Buat Quotation
                            </a>
                        </x-ui.empty-state>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $quotations->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
