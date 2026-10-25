@extends('dashboard.index')

@section('title', 'Import Orders (Bahan Baku)')
@section('route-breadcrumb', 'import-orders.index')
@section('breadcrumb', 'Import — Bahan Baku')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Import Orders</h5>
    <x-ui.button :href="route('import-orders.create')" variant="primary">
        <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Buat Import Order
    </x-ui.button>
</div>

<x-ui.card>
    @if($orders->isEmpty())
        <x-ui.empty-state icon='<iconify-icon icon="mdi:package-down" style="font-size:26px;opacity:.5"></iconify-icon>' title="Belum ada import order" description="Order bahan baku dari supplier internasional akan muncul di sini." />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>No. Order</th><th>Supplier</th><th>Tanggal</th><th>Total (IDR)</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        @php
                            $statusVariant = match($order->status) {
                                'ordered' => 'secondary', 'shipped' => 'info', 'customs' => 'warning', 'received' => 'success', default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td class="mono">{{ $order->order_number }}</td>
                            <td>{{ $order->partner->name ?? '-' }}</td>
                            <td>{{ $order->order_date->translatedFormat('d M Y') }}</td>
                            <td class="tab-nums">Rp {{ number_format($order->total_cost_idr, 0, ',', '.') }}</td>
                            <td><x-ui.badge :variant="$statusVariant">{{ \App\Models\ImportOrder::$statusLabels[$order->status] }}</x-ui.badge></td>
                            <td class="text-end"><a href="{{ route('import-orders.show', $order) }}" class="ui-btn ui-btn-sm ui-btn-secondary">Detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
</x-ui.card>

@endsection
