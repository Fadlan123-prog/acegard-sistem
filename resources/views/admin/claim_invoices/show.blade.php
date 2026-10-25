@extends('dashboard.index')

@section('title', 'Detail Claim Invoice')
@section('breadcrumb', 'Claim Invoices')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Invoice Claim #{{ $invoice->invoice_number }}</div>
            <div class="d-flex gap-2">
                <x-ui.button :href="route('admin.claim-invoices.download', $invoice->id)" variant="outline" size="sm">
                    <iconify-icon icon="ic:baseline-download"></iconify-icon> Download
                </x-ui.button>
                <x-ui.button :href="route('admin.claim-invoices.print', $invoice->id)" variant="outline" size="sm">
                    <iconify-icon icon="mdi:printer-outline"></iconify-icon> Print
                </x-ui.button>
                <x-ui.button :href="route('admin.claim-invoices.edit', $invoice->id)" variant="outline" size="sm">
                    <iconify-icon icon="lucide:edit"></iconify-icon> Edit
                </x-ui.button>
                <form action="{{ route('admin.claim-invoices.destroy', $invoice->id) }}" method="POST"
                      onsubmit="return confirm('Hapus invoice ini?')" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger-outline" size="sm">
                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon> Hapus
                    </x-ui.button>
                </form>
            </div>
        </div>
    @endslot

    <div class="mb-3">
        <div class="ui-text-muted">Claim: <a href="{{ route('admin.claims.show', $invoice->claim->id) }}">{{ $invoice->claim->claim_number }}</a></div>
        <div class="ui-text-muted">Date: {{ $invoice->invoice_date }}</div>
        <div class="ui-text-muted">Payment: {{ strtoupper($invoice->payment_method) }}</div>
        <div class="ui-text-muted">Status:
            @php
                $statusVariant = $invoice->status === 'paid' ? 'success' : ($invoice->status === 'void' ? 'neutral' : 'danger');
            @endphp
            <x-ui.badge :variant="$statusVariant">{{ strtoupper($invoice->status) }}</x-ui.badge>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table bordered-table mb-0">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $it)
                    <tr>
                        <td>{{ $it->description }}</td>
                        <td class="text-end">{{ $it->qty }}</td>
                        <td class="text-end">Rp{{ number_format($it->unit_price,0,',','.') }}</td>
                        <td class="text-end">Rp{{ number_format($it->total,0,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Subtotal</th>
                    <th class="text-end">Rp{{ number_format($invoice->subtotal,0,',','.') }}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">Discount</th>
                    <th class="text-end">Rp{{ number_format($invoice->discount,0,',','.') }}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th class="text-end">Rp{{ number_format($invoice->total_price,0,',','.') }}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">Downpayment</th>
                    <th class="text-end">Rp{{ number_format($invoice->downpayment,0,',','.') }}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">Remaining</th>
                    <th class="text-end">Rp{{ number_format($invoice->remaining_payment,0,',','.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</x-ui.card>

@endsection
