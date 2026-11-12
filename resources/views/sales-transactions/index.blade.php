@extends('dashboard.index')

@section('title', 'Transaksi Sales')
@section('route-breadcrumb', 'sales-transactions.index')
@section('breadcrumb', 'Transaksi Sales')

@section('content')

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Transaksi</div>
            @if(auth()->user()->hasRole('sales'))
            <x-ui.button :href="route('sales-transactions.create')" variant="primary" size="sm">+ Transaksi Baru</x-ui.button>
            @endif
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Customer</th>
                    <th>Sales</th>
                    <th>Produk</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th style="width:70px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td>{{ $transactions->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="fw-semibold">{{ $tx->customer_name }}</div>
                        <div class="text-muted small">{{ $tx->customer_phone }}</div>
                    </td>
                    <td class="small">{{ $tx->salesEmployee?->name ?? '-' }}</td>
                    <td class="small">{{ $tx->product?->name ?? '-' }}</td>
                    <td class="text-end fw-semibold">Rp {{ number_format($tx->total_price, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $variant = match($tx->status) { 'paid' => 'success', 'partial' => 'warning', default => 'neutral' };
                            $statusIcon = match($tx->status) { 'paid' => 'mdi:check-circle-outline', 'partial' => 'mdi:clock-outline', default => 'mdi:alert-circle-outline' };
                        @endphp
                        <x-ui.badge :variant="$variant"><iconify-icon icon="{{ $statusIcon }}" class="me-1"></iconify-icon>{{ \App\Models\SalesTransaction::$statusLabels[$tx->status] ?? $tx->status }}</x-ui.badge>
                        @if($tx->commission_released_at)
                            <x-ui.badge variant="info" class="mt-1 d-block d-xl-inline mt-xl-0">Komisi Dirilis</x-ui.badge>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $tx->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('sales-transactions.show', $tx) }}"
                           class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                           title="Lihat Detail">
                            <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-5"><x-ui.empty-state title="Belum ada transaksi." description="Transaksi yang dibuat sales akan muncul di sini." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $transactions->links() }}@endslot
</x-ui.card>

@endsection
