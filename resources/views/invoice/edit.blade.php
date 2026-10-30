@extends('dashboard.index')

@section('title', 'Edit Invoice')
@section('route-breadcrumb', 'customer.index')
@section('breadcrumb', 'Customers')

@section('content')

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
    <div class="ui-card-title">Form Edit Invoice</div>
    <x-ui.button :href="route('customer.index')" variant="secondary" size="sm">Kembali</x-ui.button>
  @endslot

  <form action="{{ route('invoice.update', $invoice->id) }}" method="POST">
    @csrf
    @method('PUT')

    <input type="hidden" name="customer_id" value="{{ old('customer_id', $invoice->customer_id) }}">

    @php
      $invoiceDate = $invoice->invoice_date
          ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d\TH:i')
          : now()->format('Y-m-d\TH:i');

      $selectedPaymentMethod = old('payment_method', $invoice->payment_method ?? '');
      $selectedStatus = old('status', $invoice->status ?? 'unpaid');
    @endphp

    {{-- Nomor & Tanggal Invoice --}}
    <div class="row">
      <div class="col-md-6 mb-3">
        <x-ui.form-field label="No Invoice" name="invoice_number" help="Kosongkan juga boleh—akan dibuat otomatis jika tidak diisi.">
          <input type="text"
                 name="invoice_number"
                 class="form-control"
                 value="{{ old('invoice_number', $invoice->invoice_number) }}">
        </x-ui.form-field>
      </div>

      <div class="col-md-6 mb-3">
        <x-ui.form-field label="Tanggal Invoice" name="invoice_date" help="Tanggal tidak dapat diubah setelah invoice dibuat.">
          <input type="datetime-local"
                 class="form-control"
                 value="{{ $invoiceDate }}"
                 disabled>
        </x-ui.form-field>
      </div>
    </div>

    {{-- Metode Pembayaran --}}
    <x-ui.form-field label="Metode Pembayaran" name="payment_method" help="Field ini opsional; kolom tersimpan terpisah dari tipe pemasangan." class="mb-3">
      <select name="payment_method" class="form-select">
        <option value="">Pilih</option>

        <option value="cash" {{ $selectedPaymentMethod === 'cash' ? 'selected' : '' }}>
          Cash
        </option>

        <option value="credit" {{ $selectedPaymentMethod === 'credit' ? 'selected' : '' }}>
          Credit
        </option>

        <option value="transfer" {{ $selectedPaymentMethod === 'transfer' ? 'selected' : '' }}>
          Transfer
        </option>

        <option value="tokopedia" {{ $selectedPaymentMethod === 'tokopedia' ? 'selected' : '' }}>
          Tokopedia
        </option>
      </select>
    </x-ui.form-field>

    {{-- Harga / Diskon / DP --}}
    <div class="row">
      <div class="col-md-4 mb-3">
        <x-ui.form-field label="Harga" name="price" :required="true">
          <input type="number"
                 name="price"
                 class="form-control"
                 min="0"
                 value="{{ old('price', $invoice->price) }}"
                 required>
        </x-ui.form-field>
      </div>

      <div class="col-md-4 mb-3">
        <x-ui.form-field label="Diskon" name="discount">
          <input type="number"
                 name="discount"
                 class="form-control"
                 min="0"
                 value="{{ old('discount', $invoice->discount ?? 0) }}">
        </x-ui.form-field>
      </div>

      <div class="col-md-4 mb-3">
        <x-ui.form-field label="Uang Muka (Downpayment)" name="downpayment">
          <input type="number"
                 name="downpayment"
                 class="form-control"
                 min="0"
                 value="{{ old('downpayment', $invoice->downpayment ?? 0) }}">
        </x-ui.form-field>
      </div>
    </div>

    {{-- Status Pembayaran --}}
    <x-ui.form-field label="Status Pembayaran" name="status" class="mb-3">
      <select name="status" class="form-select" required>
        <option value="unpaid" {{ $selectedStatus === 'unpaid' ? 'selected' : '' }}>
          Belum Lunas
        </option>

        <option value="partial" {{ $selectedStatus === 'partial' ? 'selected' : '' }}>
          Sebagian
        </option>
      </select>
    </x-ui.form-field>

    <x-ui.button type="submit" variant="primary" class="mt-3">
      Update Invoice
    </x-ui.button>
  </form>
</x-ui.card>

@endsection
