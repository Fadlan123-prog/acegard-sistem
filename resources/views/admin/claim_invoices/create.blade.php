@extends('dashboard.index')

@section('title', 'Buat Invoice Claim')
@section('breadcrumb', 'Claim Invoices')

@section('content')
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Buat Invoice Claim - {{ $claim->claim_number }}</div>
            <x-ui.button href="{{ route('admin.claims.show', $claim->id) }}" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    <x-ui.alert type="warning" class="mb-3">
        Invoice hanya untuk claim <b>chargeable</b> (cause = user). Subtotal diambil dari charge_amount per item.
    </x-ui.alert>

    <form method="POST" action="{{ route('admin.claim-invoices.store', $claim->id) }}" class="row g-3">
        @csrf

        <div class="col-md-4">
            <x-ui.form-field label="Payment Method" name="payment_method">
                <input type="text" class="form-control" name="payment_method" value="{{ old('payment_method','transfer') }}" required>
            </x-ui.form-field>
        </div>

        <div class="col-md-4">
            <x-ui.form-field label="Discount" name="discount">
                <input type="number" min="0" class="form-control" name="discount" value="{{ old('discount',0) }}">
            </x-ui.form-field>
        </div>

        <div class="col-md-4">
            <x-ui.form-field label="Downpayment" name="downpayment">
                <input type="number" min="0" class="form-control" name="downpayment" value="{{ old('downpayment',0) }}">
            </x-ui.form-field>
        </div>

        <div class="col-md-4">
            <x-ui.form-field label="Status" name="status">
                <select name="status" class="form-select">
                    <option value="unpaid" selected>UNPAID</option>
                    <option value="paid">PAID</option>
                    <option value="void">VOID</option>
                </select>
            </x-ui.form-field>
        </div>

        <div class="col-12">
            <h6 class="mb-2">Items</h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="width:180px;">Charge</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @foreach($claim->items as $it)
                            @php $subtotal += (int)$it->charge_amount; @endphp
                            <tr>
                                <td>{{ $it->item_label ?? $it->item_key ?? '-' }}</td>
                                <td>Rp{{ number_format($it->charge_amount,0,',','.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-end">Subtotal</th>
                            <th>Rp{{ number_format($subtotal,0,',','.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-12">
            <x-ui.button type="submit" variant="danger">Create Invoice</x-ui.button>
        </div>
    </form>
</x-ui.card>
@endsection
