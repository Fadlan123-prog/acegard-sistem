@extends('dashboard.index')

@section('title', 'Detail Invoice Mitra')
@section('route-breadcrumb', 'mitra-invoices.index')
@section('breadcrumb', $invoice->invoice_number)

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="ui-card-title">Invoice {{ $invoice->invoice_number }}</div>
            <div class="d-flex gap-2">
                <x-ui.button :href="route('mitra-invoices.pdf', $invoice->id)" variant="outline" size="sm" target="_blank">
                    <iconify-icon icon="mdi:file-pdf-box" class="me-1"></iconify-icon>Cetak PDF
                </x-ui.button>
                @if($invoice->status === 'unpaid')
                    <form action="{{ route('mitra-invoices.mark-paid', $invoice->id) }}" method="POST"
                          onsubmit="return confirm('Tandai invoice ini sebagai lunas?')">
                        @csrf
                        @method('PATCH')
                        <x-ui.button type="submit" variant="primary" size="sm">
                            <iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>Tandai Lunas
                        </x-ui.button>
                    </form>
                @endif
                <x-ui.button :href="route('mitra-invoices.index')" variant="secondary" size="sm">Kembali</x-ui.button>
            </div>
        </div>
    @endslot

    <div class="row mb-4">
        <div class="col-md-6">
            <table class="table table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width:160px;">Mitra</td>
                    <td class="fw-semibold">{{ $invoice->mitra->name }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Tipe</td>
                    <td>
                        <x-ui.badge variant="{{ $invoice->mitra->type === 'mitra' ? 'info' : 'neutral' }}" size="sm">
                            {{ $invoice->mitra->type_label }}
                        </x-ui.badge>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">PIC</td>
                    <td>{{ $invoice->mitra->pic_name ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Telepon</td>
                    <td>{{ $invoice->mitra->phone ?: '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width:160px;">Tanggal Invoice</td>
                    <td class="fw-semibold">{{ $invoice->invoice_date?->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Jatuh Tempo</td>
                    <td>{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Status</td>
                    <td>
                        <x-ui.badge variant="{{ $invoice->status === 'paid' ? 'success' : 'warning' }}" size="sm">
                            {{ $invoice->status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                        </x-ui.badge>
                    </td>
                </tr>
                @if($invoice->status === 'paid')
                    <tr>
                        <td class="text-muted">Dibayar Pada</td>
                        <td>{{ $invoice->paid_at?->format('d M Y H:i') }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="text-muted">Dibuat Oleh</td>
                    <td>{{ $invoice->creator->name ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($invoice->notes)
        <x-ui.alert type="info" class="mb-4">{{ $invoice->notes }}</x-ui.alert>
    @endif

    <h6 class="fw-semibold mb-3">Detail Roll</h6>

    <div class="table-responsive">
        <table class="table bordered-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th class="text-end">Qty</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Satuan</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($item->qty, 2, ',', '.'), '0'), ',') }}</td>
                        <td>{{ $item->unit ?: '-' }}</td>
                        <td class="text-end">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Grand Total</th>
                    <th class="text-end">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</x-ui.card>

@endsection
