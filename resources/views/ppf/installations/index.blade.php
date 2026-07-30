@extends('layout.index')

@section('title', 'Pemasangan PPF')
@section('route-breadcrumb', route('ppf-installations.index'))
@section('breadcrumb', 'Pemasangan PPF')

@section('content')

{{-- Action Bar --}}
<x-ui.card class="mb-4">
    @slot('header')
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <p class="mb-0 text-muted small">{{ $installations->total() }} data pemasangan</p>
            <x-ui.button href="{{ route('ppf-installations.create') }}" variant="primary" size="sm">
                <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon>Tambah Pemasangan
            </x-ui.button>
        </div>
    @endslot
</x-ui.card>

{{-- Table --}}
<x-ui.card>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-medium border-bottom">Customer</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Produk PPF</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Tipe</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Tanggal Pasang</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Kode Garansi</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Status Garansi</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Teknisi</th>
                    <th class="py-3 pe-4 text-end text-muted fw-medium border-bottom">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($installations as $inst)
                @php
                    $warrantyLabel = match($inst->warranty_status) {
                        'active'  => 'Aktif',
                        'expired' => 'Expired',
                        'void'    => 'Void',
                        default   => $inst->warranty_status,
                    };
                    $warrantyVariant = match($inst->warranty_status) {
                        'active'  => 'success',
                        'expired' => 'neutral',
                        'void'    => 'danger',
                        default   => 'neutral',
                    };
                @endphp
                <tr>
                    <td class="ps-4 py-3">
                        <div class="fw-medium">{{ $inst->customerPpf?->name ?? '—' }}</div>
                        @if ($inst->branch)
                            <div class="text-muted" style="font-size:11px">{{ $inst->branch->name }}</div>
                        @endif
                    </td>
                    <td class="py-3">
                        <div>{{ $inst->ppfProduct?->name ?? '—' }}</div>
                        <div class="text-muted" style="font-size:11px">{{ $inst->ppfProduct?->brand }}</div>
                    </td>
                    <td class="py-3">
                        <x-ui.badge variant="{{ $inst->install_type === 'fullbody' ? 'primary' : 'warning' }}" size="sm">
                            {{ ucfirst($inst->install_type) }}
                        </x-ui.badge>
                    </td>
                    <td class="py-3 text-center text-muted" style="font-size:12px">
                        {{ $inst->install_date?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="py-3">
                        <span class="font-monospace text-muted" style="font-size:11px">{{ $inst->warranty_code }}</span>
                        <div class="text-muted" style="font-size:10px">
                            @if($inst->warranty_end)
                                s/d {{ $inst->warranty_end->format('d/m/Y') }}
                            @endif
                        </div>
                    </td>
                    <td class="py-3 text-center">
                        <x-ui.badge :variant="$warrantyVariant" size="sm">
                            {{ $warrantyLabel }}
                        </x-ui.badge>
                    </td>
                    <td class="py-3 text-muted" style="font-size:12px">
                        {{ collect([$inst->tukang?->name, $inst->kenek?->name])->filter()->join(', ') ?: '—' }}
                    </td>
                    <td class="py-3 pe-4">
                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                            @if($inst->invoice)
                                <x-ui.button href="{{ route('ppf-invoices.show', $inst->invoice->id) }}" variant="success" size="sm" title="Lihat Invoice">
                                    <iconify-icon icon="mdi:receipt"></iconify-icon>
                                </x-ui.button>
                            @else
                                <x-ui.button href="{{ route('ppf-invoices.create', $inst->id) }}" variant="warning" size="sm" title="Buat Invoice">
                                    <iconify-icon icon="mdi:file-plus-outline"></iconify-icon>
                                </x-ui.button>
                            @endif
                            <x-ui.button href="{{ route('ppf-installations.edit', $inst) }}" variant="outline" size="sm" title="Edit">
                                <iconify-icon icon="mdi:pencil-outline"></iconify-icon>
                            </x-ui.button>
                            <form method="POST" action="{{ route('ppf-installations.destroy', $inst) }}">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger-outline" size="sm" title="Hapus"
                                        onclick="return confirm('Hapus data pemasangan ini?')">
                                    <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                </x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-5">
                        <x-ui.empty-state title="Belum ada data pemasangan PPF." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($installations->hasPages())
        <div class="px-4 py-3">{{ $installations->links() }}</div>
    @endif
</x-ui.card>

@endsection
