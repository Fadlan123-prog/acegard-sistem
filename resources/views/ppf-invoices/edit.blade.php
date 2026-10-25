@extends('dashboard.index')

@section('title', 'Edit Invoice PPF')
@section('breadcrumb', 'PPF Invoices')

@section('content')
@php
  $customer = $invoice->customer;
  $ins = $invoice->installation;
@endphp

@if ($errors->any())
  <x-ui.alert type="danger" class="mb-3">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </x-ui.alert>
@endif

<x-ui.card>
  @slot('header')
    <div class="d-flex justify-content-between align-items-center">
      <div class="ui-card-title">Edit Invoice #{{ $invoice->invoice_number }}</div>
      <x-ui.button :href="route('ppf-invoices.show', $invoice->id)" variant="secondary" size="sm">Kembali</x-ui.button>
    </div>
  @endslot

  <x-ui.alert type="info" class="mb-3">
    <div><b>Customer:</b> {{ $customer->name ?? '-' }}</div>
    <div><b>Warranty Code:</b> {{ $ins->warranty_code ?? '-' }}</div>
    <div><b>Produk:</b> {{ $ins->ppfProduct->name ?? '-' }}</div>
  </x-ui.alert>

  <form action="{{ route('ppf-invoices.update', $invoice->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Invoice Date</label>
        <input type="date" name="invoice_date" class="form-control"
               value="{{ old('invoice_date', optional($invoice->invoice_date)->format('Y-m-d')) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Payment Method</label>
        <select name="payment_method" class="form-select" required>
          @php $pm = old('payment_method', $invoice->payment_method); @endphp
          <option value="cash"     {{ $pm==='cash' ? 'selected':'' }}>Cash</option>
          <option value="transfer" {{ $pm==='transfer' ? 'selected':'' }}>Transfer</option>
          <option value="debit"    {{ $pm==='debit' ? 'selected':'' }}>Debit</option>
          <option value="credit"   {{ $pm==='credit' ? 'selected':'' }}>Credit</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        @php $st = old('status', $invoice->status); @endphp
        <select name="status" class="form-select" required>
          <option value="unpaid"   {{ $st==='unpaid' ? 'selected':'' }}>Unpaid</option>
          <option value="partial"  {{ $st==='partial' ? 'selected':'' }}>Partial</option>
          <option value="paid"     {{ $st==='paid' ? 'selected':'' }}>Paid</option>
          <option value="void"     {{ $st==='void' ? 'selected':'' }}>Void</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Price</label>
        <input type="number" name="price" class="form-control" min="0"
               value="{{ old('price', $invoice->price) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Discount</label>
        <input type="number" name="discount" class="form-control" min="0"
               value="{{ old('discount', $invoice->discount) }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">Down Payment</label>
        <input type="number" name="downpayment" class="form-control" min="0"
               value="{{ old('downpayment', $invoice->downpayment) }}">
      </div>

      <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
      </div>

      <div class="col-12">
        <x-ui.button type="submit" variant="primary">Update Invoice</x-ui.button>
      </div>
    </div>
  </form>
</x-ui.card>
@endsection
