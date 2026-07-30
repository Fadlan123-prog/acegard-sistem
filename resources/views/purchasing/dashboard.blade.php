@extends('layout.index')

@section('title', 'Dashboard Purchasing')
@section('route-breadcrumb', route('purchasing.dashboard'))
@section('breadcrumb', 'Dashboard Purchasing')

@section('content')

@php $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Pembelian Bulan Ini"
            :value="$idr($purchasesThisMonth)"
            color="blue"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">{{ number_format($purchasesCount) }} transaksi</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Jumlah Pembelian"
            :value="number_format($purchasesCount)"
            color="green"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">{{ now()->translatedFormat('F Y') }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Stok Film Menipis"
            :value="number_format($lowFilmStock)"
            color="red"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Sisa &le; 10 meter</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Barang Penunjang Habis"
            :value="number_format($lowSupportingGoods)"
            color="amber"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Stok &le; 5</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">

    {{-- Recent Purchases --}}
    <div class="col-xl-7">
        <x-ui.card>
            @slot('header')
            <div class="d-flex justify-content-between align-items-center">
                <div class="ui-card-title">Pembelian Terbaru</div>
                <x-ui.button variant="outline" size="sm" href="{{ route('purchases.index') }}" style="font-size:11px">Semua Pembelian</x-ui.button>
            </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Supplier</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Deskripsi</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Tanggal</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentPurchases as $p)
                        <tr>
                            <td class="ps-0 py-2 fw-medium" style="font-size:12px">{{ $p->supplier_name ?? '—' }}</td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ Str::limit($p->description ?? $p->notes ?? '—', 30) }}</td>
                            <td class="py-2 text-muted" style="font-size:11px">
                                {{ $p->purchase_date ? \Carbon\Carbon::parse($p->purchase_date)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="py-2 text-end fw-semibold" style="font-size:12px">{{ $idr($p->total_amount) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">
                                <x-ui.empty-state title="Belum ada pembelian bulan ini" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    {{-- Low Supporting Goods --}}
    <div class="col-xl-5">
        <x-ui.card title="Barang Penunjang Menipis" subtitle="Stok &le; 5">
            @forelse ($lowGoods as $g)
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--ui-border)">
                <div>
                    <div style="font-size:12px;font-weight:500">{{ $g->name }}</div>
                    <div class="text-muted" style="font-size:10px">{{ $g->unit ?? 'pcs' }}</div>
                </div>
                <x-ui.badge :variant="$g->current_stock <= $g->minimum_stock / 2 ? 'danger' : 'warning'" size="sm">
                    {{ number_format($g->current_stock, 0) }} tersisa
                </x-ui.badge>
            </div>
            @empty
            <x-ui.empty-state title="Semua stok barang penunjang aman" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Low Film Stock --}}
    <div class="col-12">
        <x-ui.card title="Stok Film Menipis" subtitle="Sisa &le; 10 meter">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Kode Film</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Nama Film</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Cabang</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Sisa (meter)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockAlerts as $stock)
                        <tr>
                            <td class="ps-0 py-2 font-monospace text-muted" style="font-size:11px">{{ $stock->film_type ?? '—' }}</td>
                            <td class="py-2 fw-medium" style="font-size:12px">{{ $stock->darkness_code ?? '—' }}</td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ $stock->branch->name ?? '—' }}</td>
                            <td class="py-2 text-end">
                                <x-ui.badge :variant="$stock->remaining_length_meter <= 5 ? 'danger' : 'warning'" size="sm">
                                    {{ number_format($stock->remaining_length_meter, 1) }} m
                                </x-ui.badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">
                                <x-ui.empty-state title="Semua stok film masih aman" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

</div>

@endsection
