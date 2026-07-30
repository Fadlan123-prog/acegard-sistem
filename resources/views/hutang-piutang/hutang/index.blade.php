@extends('layout.index')

@section('title', 'Hutang')
@section('route-breadcrumb', route('hutang.index'))
@section('breadcrumb', 'Hutang ke Vendor')

@section('content')

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    @php
        $fmtRp = fn($v) => 'Rp '.number_format($v, 0, ',', '.');
        $cards = [
            ['label'=>'Total Hutang',   'val'=>$fmtRp($summary['total']),     'color'=>'purple'],
            ['label'=>'Sudah Dibayar',  'val'=>$fmtRp($summary['paid']),      'color'=>'green'],
            ['label'=>'Sisa Hutang',    'val'=>$fmtRp($summary['remaining']), 'color'=>'amber'],
            ['label'=>'Jatuh Tempo',    'val'=>$summary['overdue'].' item',   'color'=>'red'],
        ];
    @endphp
    @foreach ($cards as $c)
    <div class="col-6 col-lg-3">
        <x-ui.stats-card :label="$c['label']" :value="$c['val']" :color="$c['color']" />
    </div>
    @endforeach
</div>

{{-- Filter & Action Bar --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('hutang.index') }}"
          class="d-flex flex-wrap align-items-end gap-2">

        <div class="flex-grow-1" style="min-width:180px">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Cari vendor…" value="{{ $filters['search'] ?? '' }}">
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
        <x-ui.button variant="primary" size="sm" type="submit"><iconify-icon icon="mdi:magnify" class="me-1"></iconify-icon>Cari</x-ui.button>
        <x-ui.button variant="outline" size="sm" href="{{ route('hutang.index') }}">Reset</x-ui.button>
        <x-ui.button variant="outline" size="sm" href="{{ route('hutang.export', request()->only(['branch_id','status'])) }}">
            <iconify-icon icon="mdi:file-excel-outline" class="me-1"></iconify-icon>Export
        </x-ui.button>
        <x-ui.button variant="danger" size="sm" class="ms-auto" href="{{ route('hutang.create') }}">
            <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon>Catat Hutang
        </x-ui.button>
    </form>
</x-ui.card>

{{-- Table --}}
<x-ui.card>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-medium border-bottom">Vendor</th>
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
                @forelse ($payables as $p)
                @php
                    $overdue  = $p->isOverdue();
                    $remaining = $p->remaining();
                    $statusStyle = match($p->status) {
                        'paid'    => ['variant'=>'success', 'label'=>'Lunas'],
                        'partial' => ['variant'=>'warning', 'label'=>'Sebagian'],
                        default   => ['variant'=>'danger',  'label'=>'Belum Bayar'],
                    };
                @endphp
                <tr style="{{ $overdue ? 'background:rgba(239,68,68,.02)' : '' }}">
                    <td class="ps-4 py-3">
                        <div class="fw-medium">{{ $p->vendor_name }}</div>
                        @if ($p->reference_number)
                            <div class="text-muted" style="font-size:11px">{{ $p->reference_number }}</div>
                        @endif
                    </td>
                    <td class="py-3 text-muted" style="font-size:12px;max-width:200px">
                        {{ Str::limit($p->description, 60) }}
                    </td>
                    @if ($isSuperadmin)<td class="py-3 text-muted" style="font-size:12px">{{ $p->branch?->name }}</td>@endif
                    <td class="py-3 text-end fw-medium">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td class="py-3 text-end fw-semibold" style="color:{{ $remaining > 0 ? '#ef4444' : '#10b981' }}">
                        Rp {{ number_format($remaining, 0, ',', '.') }}
                    </td>
                    <td class="py-3 text-center">
                        @if ($p->due_date)
                            <span class="{{ $overdue ? 'text-danger fw-semibold' : 'text-muted' }}" style="font-size:12px">
                                {{ $overdue ? '⚠ ' : '' }}{{ $p->due_date->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="py-3 text-center">
                        <x-ui.badge :variant="$statusStyle['variant']" size="sm">{{ $statusStyle['label'] }}</x-ui.badge>
                    </td>
                    <td class="py-3 pe-4">
                        <div class="d-flex gap-1 justify-content-end">
                            <x-ui.button variant="outline" size="sm" href="{{ route('hutang.show', $p->id) }}" title="Detail">
                                <iconify-icon icon="mdi:eye-outline"></iconify-icon>
                            </x-ui.button>
                            @if ($p->status !== 'paid')
                            <x-ui.button variant="outline" size="sm" href="{{ route('hutang.show', $p->id) }}#bayar" title="Bayar">
                                <iconify-icon icon="mdi:currency-usd-circle-outline"></iconify-icon>
                            </x-ui.button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="99" class="py-5"><x-ui.empty-state title="Belum ada catatan hutang." /></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($payables->hasPages())
        <div class="px-4 py-3">{{ $payables->links() }}</div>
    @endif
</x-ui.card>

@endsection
