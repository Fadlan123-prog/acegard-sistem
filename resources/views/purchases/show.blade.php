@extends('dashboard.index')

@section('title', 'Detail Purchasing')
@section('route-breadcrumb', 'purchases.index')
@section('breadcrumb', 'Detail Purchasing')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div>
            <div class="ui-card-title">Detail Purchasing</div>
            <small class="text-muted">{{ $purchase->purchase_number }}</small>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            @if($purchase->status === 'draft')
                <x-ui.button href="{{ route('purchases.edit', $purchase->id) }}" variant="primary" size="sm">Edit</x-ui.button>
            @endif

            @if($purchase->isReceivable())
                <x-ui.button href="{{ route('purchase-receipts.create', $purchase->id) }}" variant="success" size="sm">
                    <iconify-icon icon="mdi:package-variant-closed-check" class="me-1"></iconify-icon>
                    Terima Barang
                </x-ui.button>
            @endif

            <x-ui.button href="{{ route('purchases.index') }}" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    {{-- Stat cards --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">No Purchasing</small>
                <h6 class="mb-0 fw-semibold">{{ $purchase->purchase_number }}</h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Tanggal</small>
                <h6 class="mb-0">
                    {{ $purchase->purchase_date ? $purchase->purchase_date->format('d-m-Y') : '-' }}
                </h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Status</small>
                <h6 class="mb-0">
                    @php
                        $statusMap = [
                            'draft'            => ['warning', 'Draft'],
                            'confirmed'        => ['success', 'Confirmed'],
                            'sent'             => ['neutral', 'Terkirim'],
                            'partial_received' => ['primary', 'Sebagian Diterima'],
                            'received'         => ['success', 'Diterima'],
                            'matched'          => ['success', 'Matched'],
                            'cancelled'        => ['danger',  'Cancelled'],
                        ];
                        [$variant, $lbl] = $statusMap[$purchase->status] ?? ['neutral', $purchase->status];
                    @endphp
                    <x-ui.badge :variant="$variant" size="sm">{{ $lbl }}</x-ui.badge>
                </h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Total Nilai</small>
                <h6 class="mb-0 fw-semibold text-success">
                    Rp{{ number_format($purchase->total_amount ?? 0, 0, ',', '.') }}
                </h6>
            </div>
        </div>
    </div>

    {{-- Meta info --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted fw-normal">Cabang</dt>
                <dd class="col-sm-8">{{ $purchase->branch->name ?? '-' }}</dd>

                <dt class="col-sm-4 text-muted fw-normal">Supplier</dt>
                <dd class="col-sm-8">{{ $purchase->supplier->name ?? '-' }}</dd>

                <dt class="col-sm-4 text-muted fw-normal">Input By</dt>
                <dd class="col-sm-8">{{ $purchase->user->name ?? '-' }}</dd>
            </dl>
        </div>

        <div class="col-md-6">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted fw-normal">Dibuat</dt>
                <dd class="col-sm-8">
                    {{ $purchase->created_at ? $purchase->created_at->format('d-m-Y H:i') : '-' }}
                </dd>

                <dt class="col-sm-4 text-muted fw-normal">Jumlah Item</dt>
                <dd class="col-sm-8">{{ $purchase->items->count() }} item</dd>

                <dt class="col-sm-4 text-muted fw-normal">Catatan</dt>
                <dd class="col-sm-8">{{ $purchase->notes ?: '-' }}</dd>
            </dl>
        </div>
    </div>

    {{-- Items table --}}
    <h6 class="mb-2 fw-semibold">Detail Barang</h6>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama Barang</th>
                    <th>Produk Master</th>
                    <th class="text-end">Qty</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Satuan</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>

            <tbody>
                @forelse($purchase->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $item->item_name }}</td>
                        <td>
                            @if($item->product)
                                <x-ui.badge variant="neutral" size="sm">{{ $item->product->name }}</x-ui.badge>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($item->qty, 2, ',', '.') }}</td>
                        <td>{{ $item->unit ?: '-' }}</td>
                        <td class="text-end">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-5"><x-ui.empty-state title="Tidak ada item." /></td></tr>
                @endforelse
            </tbody>

            @if($purchase->items->count())
                <tfoot class="table-light">
                    <tr>
                        <th colspan="6" class="text-end">Grand Total</th>
                        <th class="text-end">
                            Rp{{ number_format($purchase->total_amount ?? 0, 0, ',', '.') }}
                        </th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Actions footer --}}
    @if($purchase->status === 'draft')
        <div class="mt-4 d-flex gap-2">
            <x-ui.button href="{{ route('purchases.edit', $purchase->id) }}" variant="primary" size="sm">Edit Purchasing</x-ui.button>

            <form action="{{ route('purchases.destroy', $purchase->id) }}"
                  method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus data purchasing ini?')">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="danger-outline" size="sm">Hapus</x-ui.button>
            </form>
        </div>
    @endif

</x-ui.card>

{{-- Goods Receipts (GRN) --}}
@if($purchase->receipts->isNotEmpty() || $purchase->isReceivable())
<x-ui.card class="mt-3">
    @slot('header')
        <div class="ui-card-title">Riwayat Penerimaan Barang (GRN)</div>
        @if($purchase->isReceivable())
            <x-ui.button href="{{ route('purchase-receipts.create', $purchase->id) }}" variant="success" size="sm">+ Terima Barang</x-ui.button>
        @endif
    @endslot

    @if($purchase->receipts->isEmpty())
        <x-ui.empty-state title="Belum ada penerimaan barang untuk PO ini." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No GRN</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Diterima Oleh</th>
                        <th>Catatan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->receipts as $receipt)
                        <tr>
                            <td class="fw-semibold">{{ $receipt->receipt_number }}</td>
                            <td>{{ $receipt->receipt_date?->format('d-m-Y') }}</td>
                            <td>
                                @if($receipt->status === 'complete')
                                    <x-ui.badge variant="success" size="sm">Lengkap</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning" size="sm">Sebagian</x-ui.badge>
                                @endif
                            </td>
                            <td>{{ $receipt->receiver?->name ?? '-' }}</td>
                            <td class="text-muted">{{ $receipt->notes ?: '-' }}</td>
                            <td>
                                <x-ui.button href="{{ route('purchase-receipts.show', $receipt->id) }}" variant="secondary" size="sm">Detail</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</x-ui.card>
@endif

@endsection
