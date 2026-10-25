@extends('layout.index')

@section('title', 'Barang Pendukung')
@section('route-breadcrumb', route('supporting-goods.index'))
@section('breadcrumb', 'Barang Pendukung')

@section('content')

@php
    $catVariants = [
        'chemical'  => 'primary',
        'cutter'    => 'danger',
        'squeegee'  => 'success',
        'heat_gun'  => 'warning',
        'tools'     => 'neutral',
        'other'     => 'neutral',
    ];
@endphp

{{-- Alert stok rendah --}}
@if ($lowStockCount > 0)
<x-ui.alert type="danger" class="mb-4">
    <iconify-icon icon="mdi:alert-circle" class="fs-5"></iconify-icon>
    <span><strong>{{ $lowStockCount }} barang</strong> stoknya di bawah atau sama dengan stok minimum.</span>
</x-ui.alert>
@endif

{{-- Filter & Action Bar --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('supporting-goods.index') }}"
          class="d-flex flex-wrap align-items-end gap-2">

        <div class="flex-grow-1" style="min-width:180px">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Cari nama barang…" value="{{ $filters['search'] ?? '' }}">
        </div>

        <div style="min-width:160px">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" {{ ($filters['category'] ?? '') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        @if ($isSuperadmin)
        <div style="min-width:180px">
            <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Cabang</option>
                @foreach ($branches as $br)
                    <option value="{{ $br->id }}" {{ ($filters['branch_id'] ?? '') == $br->id ? 'selected' : '' }}>
                        {{ $br->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <x-ui.button type="submit" variant="primary" size="sm">
            <iconify-icon icon="mdi:magnify" class="me-1"></iconify-icon>Cari
        </x-ui.button>
        <x-ui.button :href="route('supporting-goods.index')" variant="outline" size="sm">Reset</x-ui.button>

        <x-ui.button :href="route('supporting-goods.export', request()->only(['branch_id','category']))"
                     variant="outline" size="sm">
            <iconify-icon icon="mdi:file-excel-outline" class="me-1"></iconify-icon>Export
        </x-ui.button>
        <x-ui.button :href="route('supporting-goods.create')" variant="success" size="sm" class="ms-auto">
            <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon>Tambah Barang
        </x-ui.button>
    </form>
</x-ui.card>

{{-- Table --}}
<x-ui.card>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-medium border-bottom">Nama Barang</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Kategori</th>
                    @if ($isSuperadmin)
                        <th class="py-3 text-muted fw-medium border-bottom">Cabang</th>
                    @endif
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Stok Saat Ini</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Minimum</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Status</th>
                    <th class="py-3 pe-4 text-end text-muted fw-medium border-bottom">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($goods as $good)
                @php
                    $isLow  = $good->isLow();
                    $isCrit = $good->isCritical();
                @endphp
                <tr style="{{ $isCrit ? 'background:rgba(239,68,68,.03)' : ($isLow ? 'background:rgba(245,158,11,.03)' : '') }}">
                    <td class="ps-4 py-3">
                        <div class="fw-medium">{{ $good->name }}</div>
                        @if ($good->description)
                            <div class="text-muted" style="font-size:11px">{{ Str::limit($good->description, 60) }}</div>
                        @endif
                    </td>
                    <td class="py-3">
                        <x-ui.badge :variant="$catVariants[$good->category] ?? 'neutral'" size="sm">
                            {{ $categories[$good->category] ?? $good->category }}
                        </x-ui.badge>
                    </td>
                    @if ($isSuperadmin)
                        <td class="py-3 text-muted" style="font-size:12px">{{ $good->branch?->name ?? '—' }}</td>
                    @endif
                    <td class="py-3 text-center">
                        <span class="fw-bold" style="font-size:14px;color:{{ $isCrit ? '#ef4444' : ($isLow ? '#f59e0b' : '#1e293b') }}">
                            {{ number_format($good->current_stock, 2) }}
                        </span>
                        <span class="text-muted ms-1" style="font-size:11px">{{ $good->unit }}</span>
                    </td>
                    <td class="py-3 text-center text-muted" style="font-size:12px">
                        {{ $good->minimum_stock > 0 ? number_format($good->minimum_stock, 2).' '.$good->unit : '—' }}
                    </td>
                    <td class="py-3 text-center">
                        @if ($isCrit)
                            <x-ui.badge variant="danger" size="sm">
                                <iconify-icon icon="mdi:alarm-light-outline" class="me-1"></iconify-icon>Kritis
                            </x-ui.badge>
                        @elseif ($isLow)
                            <x-ui.badge variant="warning" size="sm">
                                <iconify-icon icon="mdi:alert-circle-outline" class="me-1"></iconify-icon>Rendah
                            </x-ui.badge>
                        @else
                            <x-ui.badge variant="success" size="sm">
                                <iconify-icon icon="mdi:checkbox-marked-circle-outline" class="me-1"></iconify-icon>Aman
                            </x-ui.badge>
                        @endif
                    </td>
                    <td class="py-3 pe-4">
                        <div class="d-flex gap-1 justify-content-end">
                            {{-- Masuk --}}
                            <x-ui.button type="button" variant="outline" size="sm" title="Barang Masuk"
                                         onclick="openMutateModal({{ $good->id }}, '{{ addslashes($good->name) }}', '{{ $good->unit }}', '{{ $good->current_stock }}', 'in')">
                                <iconify-icon icon="mdi:plus-circle-outline"></iconify-icon>
                            </x-ui.button>
                            {{-- Keluar --}}
                            <x-ui.button type="button" variant="warning" size="sm" title="Pemakaian"
                                         onclick="openMutateModal({{ $good->id }}, '{{ addslashes($good->name) }}', '{{ $good->unit }}', '{{ $good->current_stock }}', 'out')">
                                <iconify-icon icon="mdi:minus"></iconify-icon>
                            </x-ui.button>
                            {{-- Opname --}}
                            <x-ui.button type="button" variant="outline" size="sm" title="Stock Opname"
                                         onclick="openMutateModal({{ $good->id }}, '{{ addslashes($good->name) }}', '{{ $good->unit }}', '{{ $good->current_stock }}', 'opname')">
                                <iconify-icon icon="mdi:qrcode-scan"></iconify-icon>
                            </x-ui.button>
                            {{-- Riwayat --}}
                            <x-ui.button :href="route('supporting-goods.history', $good->id)"
                                         variant="outline" size="sm" title="Riwayat">
                                <iconify-icon icon="mdi:history"></iconify-icon>
                            </x-ui.button>
                            {{-- Edit --}}
                            <x-ui.button :href="route('supporting-goods.edit', $good->id)"
                                         variant="outline" size="sm" title="Edit">
                                <iconify-icon icon="mdi:pencil-outline"></iconify-icon>
                            </x-ui.button>
                            {{-- Hapus --}}
                            <form method="POST" action="{{ route('supporting-goods.destroy', $good->id) }}">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger-outline" size="sm" title="Hapus"
                                             onclick="return confirm('Hapus barang {{ addslashes($good->name) }}?')">
                                    <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                </x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="99" class="py-5">
                        <x-ui.empty-state title="Belum ada barang pendukung" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($goods->hasPages())
    <div class="px-4 py-3">{{ $goods->links() }}</div>
    @endif
</x-ui.card>

{{-- Mutation Modal --}}
<div class="modal fade" id="mutateModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form id="mutateForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="mutateModalTitle">Mutasi Stok</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Barang</div>
                        <div class="fw-medium" id="mutateGoodName">—</div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="text-muted small">Stok Saat Ini</div>
                            <div class="fw-bold" id="mutateCurrentStock">0</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-medium" id="mutateQtyLabel">Jumlah</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="quantity" class="form-control" min="0.01"
                                   step="0.01" required placeholder="0" id="mutateQty">
                            <span class="input-group-text" id="mutateUnit">pcs</span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-medium">Catatan <span class="text-muted">(opsional)</span></label>
                        <input type="text" name="notes" class="form-control form-control-sm"
                               placeholder="Keterangan…" maxlength="255">
                    </div>
                    <input type="hidden" name="type" id="mutateType">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <x-ui.button type="button" variant="outline" size="sm" data-bs-dismiss="modal">Batal</x-ui.button>
                    <button type="submit" class="btn btn-sm" id="mutateSubmitBtn">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('page-scripts')
<script>
function openMutateModal(goodId, goodName, unit, currentStock, type) {
    const modal   = document.getElementById('mutateModal');
    const titles  = { in: 'Barang Masuk', out: 'Pemakaian / Keluar', opname: 'Stock Opname' };
    const qtyLbls = { in: 'Jumlah Masuk', out: 'Jumlah Keluar', opname: 'Stok Aktual (hasil hitung)' };
    const btnCls  = { in: 'btn-success', out: 'btn-warning', opname: 'btn-info' };

    document.getElementById('mutateModalTitle').textContent = titles[type];
    document.getElementById('mutateGoodName').textContent   = goodName;
    document.getElementById('mutateCurrentStock').textContent = parseFloat(currentStock).toFixed(2) + ' ' + unit;
    document.getElementById('mutateQtyLabel').textContent   = qtyLbls[type];
    document.getElementById('mutateUnit').textContent       = unit;
    document.getElementById('mutateType').value             = type;
    document.getElementById('mutateQty').value              = '';

    const btn = document.getElementById('mutateSubmitBtn');
    btn.className = 'btn btn-sm ' + btnCls[type];
    btn.textContent = { in: 'Catat Masuk', out: 'Catat Keluar', opname: 'Simpan Opname' }[type];

    document.getElementById('mutateForm').action =
        '/supporting-goods/' + goodId + '/mutate';

    new bootstrap.Modal(modal).show();
}
</script>
@endpush

@endsection
