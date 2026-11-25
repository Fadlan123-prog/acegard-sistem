@extends('dashboard.index')

@section('title', 'Detail Invoice Roll Antar Cabang')
@section('route-breadcrumb', 'branch-roll-invoices.index')
@section('breadcrumb', $invoice->invoice_number)

@section('content')

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
            <div class="ui-card-title">Invoice {{ $invoice->invoice_number }}</div>
            <div class="d-flex gap-2 flex-wrap">
                <x-ui.button :href="route('branch-roll-invoices.pdf', $invoice->id)" variant="outline" size="sm" target="_blank">
                    <iconify-icon icon="mdi:file-pdf-box" class="me-1"></iconify-icon>Cetak PDF
                </x-ui.button>

                @if($isBuyer && $invoice->delivery_status === 'sent')
                    <form action="{{ route('branch-roll-invoices.receive', $invoice->id) }}" method="POST"
                          onsubmit="return confirm('Roll sudah diterima fisik? Roll akan masuk ke stok cabang pembeli.')">
                        @csrf
                        <x-ui.button type="submit" variant="primary" size="sm"><iconify-icon icon="mdi:package-variant-closed-check" class="me-1"></iconify-icon>Terima Roll</x-ui.button>
                    </form>
                @endif

                @if($isSeller && $invoice->delivery_status !== 'cancelled' && $invoice->payment_status === 'unpaid')
                    <form action="{{ route('branch-roll-invoices.mark-paid', $invoice->id) }}" method="POST" onsubmit="return confirm('Tandai invoice ini sebagai lunas?')">
                        @csrf @method('PATCH')
                        <x-ui.button type="submit" variant="outline" size="sm"><iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>Tandai Lunas</x-ui.button>
                    </form>
                @endif

                @if($isSeller && $invoice->delivery_status === 'sent' && $invoice->payment_status === 'unpaid')
                    <form action="{{ route('branch-roll-invoices.cancel', $invoice->id) }}" method="POST" onsubmit="return confirm('Batalkan invoice? Roll kembali ke stok penjual.')">
                        @csrf
                        <x-ui.button type="submit" variant="danger" size="sm">Batalkan</x-ui.button>
                    </form>
                @endif

                <x-ui.button :href="route('branch-roll-invoices.index')" variant="secondary" size="sm">Kembali</x-ui.button>
            </div>
        </div>
    @endslot

    <div class="row mb-4">
        <div class="col-md-6">
            <table class="table table-borderless mb-0">
                <tr><td class="text-muted" style="width:160px;">Cabang Penjual</td><td class="fw-semibold">{{ $invoice->fromBranch->name }}</td></tr>
                <tr><td class="text-muted">Cabang Pembeli</td><td class="fw-semibold">{{ $invoice->toBranch->name }}</td></tr>
                <tr><td class="text-muted">Dibuat Oleh</td><td>{{ $invoice->creator->name ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-borderless mb-0">
                <tr><td class="text-muted" style="width:160px;">Tanggal Invoice</td><td class="fw-semibold">{{ $invoice->invoice_date?->format('d M Y') }}</td></tr>
                <tr><td class="text-muted">Jatuh Tempo</td><td>{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td></tr>
                <tr><td class="text-muted">Pengiriman</td>
                    <td><x-ui.badge variant="{{ ['sent' => 'warning', 'received' => 'success', 'cancelled' => 'neutral'][$invoice->delivery_status] }}" size="sm">{{ $invoice->delivery_label }}</x-ui.badge>
                        @if($invoice->received_at) <small class="text-muted ms-1">{{ $invoice->received_at->format('d M Y H:i') }} oleh {{ $invoice->receiver->name ?? '-' }}</small>@endif</td></tr>
                <tr><td class="text-muted">Pembayaran</td>
                    <td><x-ui.badge variant="{{ $invoice->payment_status === 'paid' ? 'success' : 'warning' }}" size="sm">{{ $invoice->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}</x-ui.badge>
                        @if($invoice->paid_at) <small class="text-muted ms-1">{{ $invoice->paid_at->format('d M Y H:i') }}</small>@endif</td></tr>
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
                <tr><th>Film</th><th>Kode Roll</th><th class="text-end">Qty</th><th>Satuan</th><th class="text-end">Harga Satuan</th><th class="text-end">Total</th></tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td><small>{{ $item->rolls->pluck('roll_code')->implode(', ') }}</small></td>
                        <td class="text-end">{{ $item->qty }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-end">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><th colspan="5" class="text-end">Grand Total</th><th class="text-end">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</th></tr>
            </tfoot>
        </table>
    </div>
</x-ui.card>

@endsection
