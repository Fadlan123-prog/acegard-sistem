@extends('dashboard.index')

@section('title', 'Create Invoice PPF')
@section('route-breadcrumb', 'ppf-invoices.create')
@section('breadcrumb', 'Invoice PPF')

@section('content')
<x-ui.card>
  @slot('header')
    <div class="d-flex justify-content-between align-items-center">
      <div class="ui-card-title">Buat Invoice PPF</div>
      <x-ui.button :href="route('ppf-customers.index')" variant="secondary" size="sm">Kembali</x-ui.button>
    </div>
  @endslot

  <x-ui.alert type="info" class="mb-3">
    <div><b>Customer:</b> {{ $installation->customerPpf->name }}</div>
    <div><b>Warranty Code:</b> {{ $installation->warranty_code }}</div>
    <div><b>Produk:</b> {{ $installation->ppfProduct->name ?? '-' }}</div>
  </x-ui.alert>

  <form action="{{ route('ppf-invoices.store', $installation->id) }}" method="POST">
    @csrf

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Payment Method</label>
        <select name="payment_method" class="form-select" required>
          <option value="cash">Cash</option>
          <option value="transfer">Transfer</option>
          <option value="debit">Debit</option>
          <option value="credit">Credit</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Invoice Date</label>
        <input type="date" name="invoice_date" class="form-control" value="{{ now()->toDateString() }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">Price</label>
        <input type="number" name="price" class="form-control" required min="0" value="0">
      </div>

      <div class="col-md-4">
        <label class="form-label">Discount</label>
        <input type="number" name="discount" class="form-control" min="0" value="0">
      </div>

      <div class="col-md-4">
        <label class="form-label">Down Payment</label>
        <input type="number" name="downpayment" class="form-control" min="0" value="0">
      </div>

      <div class="col-md-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2"></textarea>
      </div>

      <div class="col-12">
        <x-ui.button type="submit" variant="primary">Simpan Invoice</x-ui.button>
      </div>
    </div>
  </form>

</x-ui.card>
@endsection
