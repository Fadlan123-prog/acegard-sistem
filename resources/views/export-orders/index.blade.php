@extends('dashboard.index')

@section('title', 'Export Orders (Produk Jadi)')
@section('route-breadcrumb', 'export-orders.index')
@section('breadcrumb', 'Export — Produk Jadi')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Export Orders</h5>
    <x-ui.button :href="route('export-orders.create')" variant="primary">
        <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Buat Export Order
    </x-ui.button>
</div>

<x-ui.card>
    @if($orders->isEmpty())
        <x-ui.empty-state icon='<iconify-icon icon="mdi:package-up" style="font-size:26px;opacity:.5"></iconify-icon>' title="Belum ada export order" description="Order produk jadi ke buyer internasional akan muncul di sini." />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>No. Order</th><th>Buyer</th><th>Tanggal</th><th>Nilai (IDR)</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        @php
                            $statusVariant = match($order->status) {
                                'confirmed' => 'secondary', 'shipped' => 'info', 'customs' => 'warning', 'delivered' => 'success', default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td class="mono">{{ $order->order_number }}</td>
                            <td>{{ $order->partner->name ?? '-' }}</td>
                            <td>{{ $order->order_date->translatedFormat('d M Y') }}</td>
                            <td class="tab-nums">Rp {{ number_format($order->total_revenue_idr, 0, ',', '.') }}</td>
                            <td><x-ui.badge :variant="$statusVariant">{{ \App\Models\ExportOrder::$statusLabels[$order->status] }}</x-ui.badge></td>
                            <td class="text-end"><a href="{{ route('export-orders.show', $order) }}" class="ui-btn ui-btn-sm ui-btn-secondary">Detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
</x-ui.card>

@endsection
