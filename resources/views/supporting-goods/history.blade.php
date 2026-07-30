@extends('layout.index')

@section('title', 'Riwayat Mutasi — '.$good->name)
@section('route-breadcrumb', route('supporting-goods.index'))
@section('breadcrumb', 'Riwayat Mutasi Stok')

@section('content')

{{-- Info Card --}}
<x-ui.card class="mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <div class="fw-bold fs-6">{{ $good->name }}</div>
            <div class="text-muted" style="font-size:12px">
                {{ \App\Models\SupportingGood::$categories[$good->category] ?? $good->category }}
                @if ($good->branch) · {{ $good->branch->name }} @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <div class="text-muted" style="font-size:11px">Stok Saat Ini</div>
                <div class="fw-bold fs-5">{{ number_format($good->current_stock, 2) }} {{ $good->unit }}</div>
            </div>
            <x-ui.button href="{{ route('supporting-goods.index') }}" variant="outline" size="sm">
                <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
            </x-ui.button>
        </div>
    </div>
</x-ui.card>

{{-- Mutation Table --}}
<x-ui.card>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-medium border-bottom">Tanggal</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Tipe</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Jumlah</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Sebelum</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Sesudah</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Catatan</th>
                    <th class="py-3 pe-4 text-muted fw-medium border-bottom">Dicatat Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mutations as $mut)
                @php
                    $typeIcon = match($mut->type) {
                        'in'     => 'mdi:arrow-down-circle-outline',
                        'out'    => 'mdi:arrow-up-circle-outline',
                        'opname' => 'mdi:qrcode-scan',
                        default  => 'mdi:help-circle-outline',
                    };
                    $typeVariant = match($mut->type) {
                        'in'     => 'success',
                        'out'    => 'danger',
                        'opname' => 'primary',
                        default  => 'neutral',
                    };
                    $delta = (float)$mut->stock_after - (float)$mut->stock_before;
                @endphp
                <tr>
                    <td class="ps-4 py-3">
                        <div class="fw-medium">{{ $mut->created_at->format('d/m/Y') }}</div>
                        <div class="text-muted" style="font-size:11px">{{ $mut->created_at->format('H:i') }}</div>
                    </td>
                    <td class="py-3">
                        <x-ui.badge :variant="$typeVariant" size="sm">
                            <iconify-icon icon="{{ $typeIcon }}"></iconify-icon>
                            {{ $mut->typeLabel() }}
                        </x-ui.badge>
                    </td>
                    <td class="py-3 text-center fw-semibold"
                        style="color:{{ $delta >= 0 ? '#10b981' : '#ef4444' }}">
                        {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 2) }}
                        <span class="text-muted fw-normal" style="font-size:11px">{{ $good->unit }}</span>
                    </td>
                    <td class="py-3 text-center text-muted" style="font-size:12px">
                        {{ number_format($mut->stock_before, 2) }}
                    </td>
                    <td class="py-3 text-center fw-medium">{{ number_format($mut->stock_after, 2) }}</td>
                    <td class="py-3 text-muted" style="font-size:12px">{{ $mut->notes ?? '—' }}</td>
                    <td class="py-3 pe-4 text-muted" style="font-size:12px">{{ $mut->recorder?->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="99" class="py-5">
                        <x-ui.empty-state title="Belum ada riwayat mutasi" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($mutations->hasPages())
    <div class="px-4 py-3">{{ $mutations->links() }}</div>
    @endif
</x-ui.card>

@endsection
