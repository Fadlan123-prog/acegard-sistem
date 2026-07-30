@extends('dashboard.index')

@section('title', 'Terima Barang — ' . $purchase->purchase_number)
@section('route-breadcrumb', 'purchases.index')
@section('breadcrumb', 'Terima Barang')

@section('content')

@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

<form action="{{ route('purchase-receipts.store', $purchase->id) }}" method="POST">
@csrf

<x-ui.card class="mb-3">
    @slot('header')
        <div>
            <div class="ui-card-title">Terima Barang (GRN)</div>
            <small class="text-muted">PO: {{ $purchase->purchase_number }} — {{ $purchase->supplier->name ?? '-' }}</small>
        </div>
        <x-ui.button href="{{ route('purchases.show', $purchase->id) }}" variant="secondary" size="sm">Batal</x-ui.button>
    @endslot

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Tanggal Terima <span class="text-danger">*</span></label>
            <input type="date" name="receipt_date" class="form-control @error('receipt_date') is-invalid @enderror"
                   value="{{ old('receipt_date', now()->format('Y-m-d')) }}" required>
            @error('receipt_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-8">
            <label class="form-label">Catatan</label>
            <input type="text" name="notes" class="form-control"
                   value="{{ old('notes') }}" placeholder="Catatan penerimaan (opsional)">
        </div>
    </div>

    <h6 class="fw-semibold mb-3">Detail Penerimaan Barang</h6>

    <div class="table-responsive border rounded">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama Barang</th>
                    <th class="text-end">Qty PO</th>
                    <th class="text-end">Sudah Diterima</th>
                    <th class="text-end" style="width:180px;">Qty Terima Sekarang</th>
                    <th>Catatan Item</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $i => $item)
                    @php
                        $alreadyReceived = (float) ($receivedQtys[$item->id] ?? 0);
                        $remaining = max(0, (float) $item->qty - $alreadyReceived);
                    @endphp

                    <input type="hidden" name="items[{{ $i }}][purchase_item_id]" value="{{ $item->id }}">

                    <tr @class(['table-success' => $remaining <= 0])>
                        <td>
                            <div class="fw-semibold">{{ $item->item_name }}</div>
                            @if($item->product)
                                <small class="text-muted">{{ $item->product->name }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            {{ number_format($item->qty, 2, ',', '.') }}
                            {{ $item->unit }}
                        </td>
                        <td class="text-end">
                            @if($alreadyReceived > 0)
                                <span class="text-success fw-semibold">{{ number_format($alreadyReceived, 2, ',', '.') }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($remaining <= 0)
                                <x-ui.badge variant="success" size="sm">Lunas</x-ui.badge>
                                <input type="hidden" name="items[{{ $i }}][qty_received]" value="0">
                            @else
                                <input type="number"
                                       name="items[{{ $i }}][qty_received]"
                                       class="form-control form-control-sm text-end @error('items.'.$i.'.qty_received') is-invalid @enderror"
                                       value="{{ old('items.'.$i.'.qty_received', $remaining) }}"
                                       min="0"
                                       max="{{ $remaining }}"
                                       step="0.01">
                            @endif
                        </td>
                        <td>
                            <input type="text" name="items[{{ $i }}][notes]"
                                   class="form-control form-control-sm"
                                   value="{{ old('items.'.$i.'.notes') }}"
                                   placeholder="Catatan">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @slot('footer')
        <x-ui.button type="submit" variant="primary">
            <iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>
            Simpan Penerimaan
        </x-ui.button>
        <x-ui.button href="{{ route('purchases.show', $purchase->id) }}" variant="secondary">Batal</x-ui.button>
    @endslot

</x-ui.card>

</form>
@endsection
