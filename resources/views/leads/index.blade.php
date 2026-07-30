@extends('dashboard.index')

@section('title', 'Lead Management')
@section('route-breadcrumb', 'leads.index')
@section('breadcrumb', 'Lead Management')

@section('content')

@php
    $allStatuses  = \App\Models\Lead::STATUSES;
    $activeStatus = request('status', '');
    $total        = $statusCounts->sum();

    $variantMap = [
        'new'       => 'primary',
        'contacted' => 'info',
        'follow_up' => 'warning',
        'quoted'    => 'neutral',
        'won'       => 'success',
        'lost'      => 'danger',
    ];
@endphp

{{-- Status tab filter --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <x-ui.button href="{{ route('leads.index', request()->except('status', 'page')) }}"
        size="sm" variant="{{ $activeStatus === '' ? 'primary' : 'outline' }}">
        Semua
        <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $total }}</x-ui.badge>
    </x-ui.button>
    @foreach($allStatuses as $key => $label)
        @php $count = $statusCounts[$key] ?? 0; @endphp
        <x-ui.button href="{{ route('leads.index', array_merge(request()->except('status', 'page'), ['status' => $key])) }}"
            size="sm" variant="{{ $activeStatus === $key ? 'primary' : 'outline' }}">
            {{ $label }}
            @if($count)
                <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $count }}</x-ui.badge>
            @endif
        </x-ui.button>
    @endforeach
</div>

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Lead</div>
            <x-ui.button href="{{ route('leads.create') }}" variant="primary" size="sm">+ Tambah Lead</x-ui.button>
        </div>
    @endslot

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('leads.index') }}" id="filterForm"
          class="d-flex align-items-center gap-2 flex-wrap mb-3">

        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <input type="text" name="search" value="{{ request('search') }}"
               class="form-control" placeholder="Cari nama, telepon, email..."
               style="max-width:240px">

        <select name="category" class="form-select" style="max-width:180px"
                onchange="document.getElementById('filterForm').submit()">
            <option value="">-- Semua Kategori --</option>
            @foreach(\App\Models\Lead::CATEGORIES as $key => $label)
                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        @if($employees->isNotEmpty())
        <select name="assigned_to" class="form-select" style="max-width:180px"
                onchange="document.getElementById('filterForm').submit()">
            <option value="">-- Semua Marketing --</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ request('assigned_to') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>
        @endif

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

        @if(request()->hasAny(['search', 'category', 'assigned_to', 'branch_id']))
            <x-ui.button href="{{ route('leads.index', request()->only('status')) }}" variant="outline">Reset</x-ui.button>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table bordered-table mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontak</th>
                    <th>Kategori</th>
                    <th>Sumber</th>
                    <th>Status</th>
                    <th>Marketing</th>
                    @if(auth()->user()->hasRole('superadmin'))
                    <th>Cabang</th>
                    @endif
                    <th>Follow Up</th>
                    <th style="width:120px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $lead->name }}</div>
                        @if($lead->vehicle_brand)
                            <div class="text-muted small">
                                {{ $lead->vehicle_brand }} {{ $lead->vehicle_model }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($lead->phone)
                            <div class="small">{{ $lead->phone }}</div>
                        @endif
                        @if($lead->email)
                            <div class="text-muted small">{{ $lead->email }}</div>
                        @endif
                        @if(!$lead->phone && !$lead->email)
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <x-ui.badge variant="info">{{ $lead->category_label }}</x-ui.badge>
                    </td>
                    <td class="small text-muted">{{ $lead->source_label }}</td>
                    <td>
                        <x-ui.badge variant="{{ $variantMap[$lead->status] ?? 'neutral' }}" :dot="true">
                            {{ $lead->status_label }}
                        </x-ui.badge>
                    </td>
                    <td class="small">{{ $lead->assignee?->name ?? '—' }}</td>
                    @if(auth()->user()->hasRole('superadmin'))
                    <td class="small text-muted">{{ $lead->branch?->name ?? '—' }}</td>
                    @endif
                    <td class="small">
                        @if($lead->follow_up_date)
                            @php
                                $isToday = $lead->follow_up_date->isToday();
                                $isPast  = $lead->follow_up_date->isPast() && !$isToday && !in_array($lead->status, ['won','lost']);
                            @endphp
                            <span class="{{ $isToday ? 'text-warning fw-semibold' : ($isPast ? 'text-danger' : 'text-muted') }}">
                                {{ $lead->follow_up_date->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('leads.show', $lead) }}"
                               class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Lihat Detail">
                                <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                            </a>
                            <a href="{{ route('leads.edit', $lead) }}"
                               class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Edit">
                                <iconify-icon icon="lucide:edit"></iconify-icon>
                            </a>
                            <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                                  onsubmit="return confirm('Hapus lead ini?')" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                        title="Hapus">
                                    <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="99" class="py-5">
                        <x-ui.empty-state
                            title="Tidak ada lead ditemukan"
                            description="Belum ada data lead. Klik tombol Tambah Lead untuk memulai."
                        >
                            <x-ui.button href="{{ route('leads.create') }}" variant="primary" size="sm">+ Tambah Lead</x-ui.button>
                        </x-ui.empty-state>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $leads->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
