@extends('dashboard.index')

@section('title', 'Edit Invoice PPF')
@section('breadcrumb', 'PPF Invoices')

@section('content')
@php
  $customer = $invoice->customer;
  $ins = $invoice->installation;
@endphp

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Edit Invoice #{{ $invoice->invoice_number }}</h5>
    <a href="{{ route('ppf-invoices.show', $invoice->id) }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>

  <div class="card-body">
    <div class="alert alert-info">
      <div><b>Customer:</b> {{ $customer->name ?? '-' }}</div>
      <div><b>Warranty Code:</b> {{ $ins->warranty_code ?? '-' }}</div>
      <div><b>Produk:</b> {{ $ins->ppfProduct->name ?? '-' }}</div>
    </div>

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
          <button class="btn btn-primary">Update Invoice</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
