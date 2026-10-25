@extends('dashboard.index')

@section('title', $order->order_number)
@section('route-breadcrumb', 'import-orders.show')
@section('breadcrumb', $order->order_number)

@section('content')

@php
    $statusVariant = match($order->status) {
        'ordered' => 'secondary', 'shipped' => 'info', 'customs' => 'warning', 'received' => 'success', default => 'secondary',
    };
    $statusFlow = ['ordered', 'shipped', 'customs', 'received'];
@endphp

<div class="row g-3">
    <div class="col-lg-8">
        <x-ui.card class="mb-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">{{ $order->order_number }}</h5>
                    <p class="text-muted small mb-0">{{ $order->partner->name ?? '-' }} — {{ $order->partner->country ?? '-' }}</p>
                </div>
                <x-ui.badge :variant="$statusVariant">{{ \App\Models\ImportOrder::$statusLabels[$order->status] }}</x-ui.badge>
            </div>

            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Item</th><th>Qty</th><th>Satuan</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td class="tab-nums">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                                <td>{{ $item->unit }}</td>
                                <td class="tab-nums">{{ $order->currency }} {{ number_format($item->unit_price_foreign, 0, ',', '.') }}</td>
                                <td class="tab-nums">{{ $order->currency }} {{ number_format($item->lineTotalForeign(), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr>
            <dl class="row small mb-0">
                <dt class="col-6">Subtotal ({{ $order->currency }})</dt>
                <dd class="col-6 text-end tab-nums">{{ number_format($order->subtotal_foreign, 0, ',', '.') }}</dd>
                <dt class="col-6">Kurs</dt>
                <dd class="col-6 text-end tab-nums">{{ number_format($order->exchange_rate, 4) }}</dd>
                <dt class="col-6">Bea Masuk + Pajak + Freight + Asuransi</dt>
                <dd class="col-6 text-end tab-nums">Rp {{ number_format($order->duty_cost + $order->tax_cost + $order->freight_cost + $order->insurance_cost, 0, ',', '.') }}</dd>
                <dt class="col-6 fw-bold">Total Biaya (IDR)</dt>
                <dd class="col-6 text-end tab-nums fw-bold">Rp {{ number_format($order->total_cost_idr, 0, ',', '.') }}</dd>
            </dl>
        </x-ui.card>

        @if($order->documents->isNotEmpty())
        <x-ui.card>
            <h6 class="mb-2">Dokumen</h6>
            @foreach($order->documents as $doc)
                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="d-block small mb-1">
                    <iconify-icon icon="mdi:file-document-outline"></iconify-icon> {{ \App\Models\ImportOrderDocument::$docTypeLabels[$doc->doc_type] }}
                </a>
            @endforeach
        </x-ui.card>
        @endif
    </div>

    <div class="col-lg-4">
        <x-ui.card>
            <h6 class="mb-3">Update Status</h6>
            <form method="POST" action="{{ route('import-orders.status', $order) }}">
                @csrf @method('PUT')
                <select name="status" class="form-select mb-2">
                    @foreach($statusFlow as $s)
                        <option value="{{ $s }}" @selected($order->status === $s)>{{ \App\Models\ImportOrder::$statusLabels[$s] }}</option>
                    @endforeach
                </select>
                <x-ui.button type="submit" variant="primary" class="w-100">Update Status</x-ui.button>
            </form>

            <hr>
            <dl class="row small mb-0">
                <dt class="col-6">Tanggal Order</dt>
                <dd class="col-6">{{ $order->order_date->translatedFormat('d M Y') }}</dd>
                <dt class="col-6">Estimasi Tiba</dt>
                <dd class="col-6">{{ $order->estimated_arrival?->translatedFormat('d M Y') ?? '-' }}</dd>
                <dt class="col-6">Diterima</dt>
                <dd class="col-6">{{ $order->received_date?->translatedFormat('d M Y') ?? '-' }}</dd>
                <dt class="col-6">Dibuat Oleh</dt>
                <dd class="col-6">{{ $order->creator->name ?? '-' }}</dd>
            </dl>
        </x-ui.card>
    </div>
</div>

@endsection
