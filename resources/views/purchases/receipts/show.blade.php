@extends('dashboard.index')

@section('title', 'Detail GRN — ' . $receipt->receipt_number)
@section('route-breadcrumb', 'purchases.index')
@section('breadcrumb', 'Detail Penerimaan Barang')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Penerimaan Barang (GRN)</div>
                <small class="text-muted">{{ $receipt->receipt_number }}</small>
            </div>
            <x-ui.button :href="route('purchases.show', $receipt->purchase_id)" variant="secondary" size="sm">
                Kembali ke PO
            </x-ui.button>
        </div>
    @endslot

    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <x-ui.stats-card
                label="No GRN"
                :value="$receipt->receipt_number"
                color="blue"
                icon='<iconify-icon icon="mdi:receipt-text-outline"></iconify-icon>'
                accent
                class="h-100"
            />
        </div>
        <div class="col-md-3 mb-2">
            <x-ui.stats-card
                label="Tanggal Terima"
                :value="$receipt->receipt_date?->format('d-m-Y') ?? '-'"
                color="purple"
                icon='<iconify-icon icon="mdi:calendar-outline"></iconify-icon>'
                accent
                class="h-100"
            />
        </div>
        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Status</small>
                <h6 class="mb-0">
                    @if($receipt->status === 'complete')
                        <x-ui.badge variant="success">Lengkap</x-ui.badge>
                    @else
                        <x-ui.badge variant="warning">Sebagian</x-ui.badge>
                    @endif
                </h6>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <x-ui.stats-card
                label="Diterima Oleh"
                :value="$receipt->receiver?->name ?? '-'"
                color="green"
                icon='<iconify-icon icon="mdi:account-check-outline"></iconify-icon>'
                accent
                class="h-100"
            />
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted fw-normal">No PO</dt>
                <dd class="col-sm-8">
                    <a href="{{ route('purchases.show', $receipt->purchase_id) }}">
                        {{ $receipt->purchase->purchase_number }}
                    </a>
                </dd>
                <dt class="col-sm-4 text-muted fw-normal">Supplier</dt>
                <dd class="col-sm-8">{{ $receipt->purchase->supplier->name ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Cabang</dt>
                <dd class="col-sm-8">{{ $receipt->purchase->branch->name ?? '-' }}</dd>
            </dl>
        </div>
        <div class="col-md-6">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted fw-normal">Dibuat</dt>
                <dd class="col-sm-8">{{ $receipt->created_at?->format('d-m-Y H:i') }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Catatan</dt>
                <dd class="col-sm-8">{{ $receipt->notes ?: '-' }}</dd>
            </dl>
        </div>
    </div>

    <h6 class="fw-semibold mb-2">Barang Diterima</h6>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama Barang</th>
                    <th class="text-end">Qty PO</th>
                    <th class="text-end">Qty Diterima</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipt->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $item->purchaseItem->item_name ?? '-' }}</td>
                        <td class="text-end">
                            {{ number_format($item->purchaseItem->qty ?? 0, 2, ',', '.') }}
                            {{ $item->purchaseItem->unit ?? '' }}
                        </td>
                        <td class="text-end fw-semibold text-success">
                            {{ number_format($item->qty_received, 2, ',', '.') }}
                        </td>
                        <td class="text-muted">{{ $item->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-5"><x-ui.empty-state title="Tidak ada item." /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-ui.card>

@endsection
