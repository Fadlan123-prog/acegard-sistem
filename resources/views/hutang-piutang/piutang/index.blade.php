@extends('layout.index')

@section('title', 'Piutang')
@section('route-breadcrumb', route('piutang.index'))
@section('breadcrumb', 'Piutang dari Pelanggan')

@section('content')

{{-- Summary Cards --}}
@php $fmtRp = fn($v) => 'Rp '.number_format($v, 0, ',', '.'); @endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <x-ui.stats-card label="Total Piutang" :value="$fmtRp($summary['total'])" color="purple" />
    </div>
    <div class="col-6 col-lg-3">
        <x-ui.stats-card label="Sudah Diterima" :value="$fmtRp($summary['paid'])" color="green" />
    </div>
    <div class="col-6 col-lg-3">
        <x-ui.stats-card label="Sisa Piutang" :value="$fmtRp($summary['remaining'])" color="amber" />
    </div>
    <div class="col-6 col-lg-3">
        <x-ui.stats-card label="Jatuh Tempo" :value="$summary['overdue'].' item'" color="red" />
    </div>
</div>

{{-- Filter & Action Bar --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('piutang.index') }}"
          class="d-flex flex-wrap align-items-end gap-2">

        <div class="flex-grow-1" style="min-width:180px">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Cari pelanggan…" value="{{ $filters['search'] ?? '' }}">
        </div>
        <div style="min-width:150px">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="unpaid"  {{ ($filters['status'] ?? '') === 'unpaid'  ? 'selected' : '' }}>Belum Bayar</option>
                <option value="partial" {{ ($filters['status'] ?? '') === 'partial' ? 'selected' : '' }}>Sebagian</option>
                <option value="paid"    {{ ($filters['status'] ?? '') === 'paid'    ? 'selected' : '' }}>Lunas</option>
            </select>
        </div>
        @if ($isSuperadmin)
        <div style="min-width:170px">
            <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Cabang</option>
                @foreach ($branches as $br)
                    <option value="{{ $br->id }}" {{ ($filters['branch_id'] ?? '') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <x-ui.button type="submit" variant="primary" size="sm"><iconify-icon icon="mdi:magnify" class="me-1"></iconify-icon>Cari</x-ui.button>
        <x-ui.button href="{{ route('piutang.index') }}" variant="outline" size="sm">Reset</x-ui.button>
        <x-ui.button href="{{ route('piutang.export', request()->only(['branch_id','status'])) }}" variant="outline" size="sm">
            <iconify-icon icon="mdi:file-excel-outline" class="me-1"></iconify-icon>Export
        </x-ui.button>
        <x-ui.button href="{{ route('piutang.create') }}" variant="success" size="sm" class="ms-auto">
            <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon>Catat Piutang
        </x-ui.button>
    </form>
</x-ui.card>

{{-- Table --}}
<x-ui.card>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-medium border-bottom">Pelanggan</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Deskripsi</th>
                    @if ($isSuperadmin)<th class="py-3 text-muted fw-medium border-bottom">Cabang</th>@endif
                    <th class="py-3 text-end text-muted fw-medium border-bottom">Total</th>
                    <th class="py-3 text-end text-muted fw-medium border-bottom">Sisa</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Jatuh Tempo</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Status</th>
                    <th class="py-3 pe-4 text-end text-muted fw-medium border-bottom">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($receivables as $r)
                @php
                    $overdue   = $r->isOverdue();
                    $remaining = $r->remaining();
                    $statusVariant = match($r->status) {
                        'paid'    => 'success',
                        'partial' => 'warning',
                        default   => 'danger',
                    };
                    $statusLabel = match($r->status) {
                        'paid'    => 'Lunas',
                        'partial' => 'Sebagian',
                        default   => 'Belum Bayar',
                    };
                @endphp
                <tr style="{{ $overdue ? 'background:rgba(239,68,68,.02)' : '' }}">
                    <td class="ps-4 py-3">
                        <div class="fw-medium">{{ $r->customer_name }}</div>
                        @if ($r->reference_number)
                            <div class="text-muted" style="font-size:11px">{{ $r->reference_number }}</div>
                        @endif
                    </td>
                    <td class="py-3 text-muted" style="font-size:12px;max-width:200px">
                        {{ Str::limit($r->description, 60) }}
                    </td>
                    @if ($isSuperadmin)<td class="py-3 text-muted" style="font-size:12px">{{ $r->branch?->name }}</td>@endif
                    <td class="py-3 text-end fw-medium">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                    <td class="py-3 text-end fw-semibold" style="color:{{ $remaining > 0 ? '#f59e0b' : '#10b981' }}">
                        Rp {{ number_format($remaining, 0, ',', '.') }}
                    </td>
                    <td class="py-3 text-center">
                        @if ($r->due_date)
                            <span class="{{ $overdue ? 'text-danger fw-semibold' : 'text-muted' }}" style="font-size:12px">
                                {{ $overdue ? '⚠ ' : '' }}{{ $r->due_date->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="py-3 text-center">
                        <x-ui.badge :variant="$statusVariant" size="sm">{{ $statusLabel }}</x-ui.badge>
                    </td>
                    <td class="py-3 pe-4">
                        <div class="d-flex gap-1 justify-content-end">
                            <x-ui.button href="{{ route('piutang.show', $r->id) }}" variant="outline" size="sm">
                                <iconify-icon icon="mdi:eye-outline"></iconify-icon>
                            </x-ui.button>
                            @if ($r->status !== 'paid')
                            <x-ui.button href="{{ route('piutang.show', $r->id) }}#bayar" variant="success" size="sm">
                                <iconify-icon icon="mdi:currency-usd-circle-outline"></iconify-icon>
                            </x-ui.button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="99" class="py-5"><x-ui.empty-state title="Belum ada catatan piutang" /></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($receivables->hasPages())
        @slot('footer'){{ $receivables->links() }}@endslot
    @endif
</x-ui.card>

@endsection
