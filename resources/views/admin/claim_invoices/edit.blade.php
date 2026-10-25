@extends('dashboard.index')

@section('title', 'Edit Invoice Claim')
@section('breadcrumb', 'Claim Invoices')

@section('content')
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Edit Invoice Claim — {{ $invoice->claim?->claim_number ?? '#'.$invoice->id }}</div>
            <x-ui.button :href="route('admin.claim-invoices.show', $invoice->id)" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    
    @if($errors->any())
        <x-ui.alert type="danger" class="mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.claim-invoices.update', $invoice->id) }}" class="row g-3">
        @csrf @method('PUT')

        <div class="col-md-4">
            <x-ui.form-field label="Payment Method" name="payment_method">
                <input type="text" class="form-control" name="payment_method"
                       value="{{ old('payment_method', $invoice->payment_method) }}" required>
            </x-ui.form-field>
        </div>

        <div class="col-md-4">
            <x-ui.form-field label="Discount" name="discount">
                <input type="number" min="0" class="form-control" name="discount"
                       value="{{ old('discount', $invoice->discount) }}">
            </x-ui.form-field>
        </div>

        <div class="col-md-4">
            <x-ui.form-field label="Downpayment" name="downpayment">
                <input type="number" min="0" class="form-control" name="downpayment"
                       value="{{ old('downpayment', $invoice->downpayment) }}">
            </x-ui.form-field>
        </div>

        <div class="col-md-4">
            <x-ui.form-field label="Status" name="status">
                <select name="status" class="form-select">
                    @foreach (['unpaid', 'paid', 'void'] as $s)
                        <option value="{{ $s }}" {{ old('status', $invoice->status) === $s ? 'selected' : '' }}>
                            {{ strtoupper($s) }}
                        </option>
                    @endforeach
                </select>
            </x-ui.form-field>
        </div>

        <div class="col-12">
            <h6 class="mb-2">Items (read-only)</h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="width:180px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @foreach($invoice->items as $it)
                            @php $subtotal += (int)$it->total; @endphp
                            <tr>
                                <td>{{ $it->item_label ?? $it->item_key ?? '-' }}</td>
                                <td>Rp{{ number_format($it->total,0,',','.') }}</td>
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
            <x-ui.button type="submit" variant="primary">Simpan Perubahan</x-ui.button>
        </div>
    </form>
</x-ui.card>
@endsection
