@extends('dashboard.index')

@section('title', 'Buat Invoice')
@section('route-breadcrumb', 'customer.index')
@section('breadcrumb', 'Customers')

@section('content')
@include('partials.form-errors')

<x-ui.card>
  @slot('header')
    <div class="ui-card-title">Form Buat Invoice</div>
    <x-ui.button :href="route('customer.index')" variant="secondary" size="sm">Kembali</x-ui.button>
  @endslot

  <form action="{{ route('invoice.store') }}" method="POST">
    @csrf
    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

    {{-- Nomor & tanggal --}}
    <div class="row">
      <div class="col-md-6 mb-3">
        <x-ui.form-field label="No Invoice" name="invoice_number" help="Kosongkan juga boleh—akan dibuat otomatis jika tidak diisi.">
          <input type="text" name="invoice_number" class="form-control"
                 value="{{ old('invoice_number', 'INV-' . now()->format('YmdHis')) }}">
        </x-ui.form-field>
      </div>
      <div class="col-md-6 mb-3">
        <x-ui.form-field label="Tanggal Invoice" name="invoice_date">
          <input type="datetime-local" name="invoice_date" class="form-control"
                 value="{{ old('invoice_date', now()->format('Y-m-d\TH:i')) }}" required>
        </x-ui.form-field>
      </div>
    </div>

    {{-- Metode pembayaran (jangan pakai name="type" agar tidak bentrok dengan tipe pemasangan) --}}
    <x-ui.form-field label="Metode Pembayaran" name="payment_method" help="Field ini opsional; kolom tersimpan terpisah dari tipe pemasangan." class="mb-3">
      <select name="payment_method" class="form-select">
        <option value="">Pilih</option>
        <option value="cash" {{ old('payment_method')==='cash' ? 'selected' : '' }}>Cash</option>
        <option value="credit" {{ old('payment_method')==='credit' ? 'selected' : '' }}>Credit</option>
        <option value="transfer" {{ old('payment_method')==='transfer' ? 'selected' : '' }}>Transfer</option>
        <option value="tokopedia" {{ old('payment_method')==='tokopedia' ? 'selected' : '' }}>Tokopedia</option>
      </select>
    </x-ui.form-field>

    {{-- Harga / diskon / DP --}}
    <div class="row">
      <div class="col-md-4 mb-3">
        <x-ui.form-field label="Harga" name="price" :required="true">
          <input type="number" name="price" class="form-control" min="0" value="{{ old('price') }}" required>
        </x-ui.form-field>
      </div>
      <div class="col-md-4 mb-3">
        <x-ui.form-field label="Diskon" name="discount">
          <input type="number" name="discount" class="form-control" min="0" value="{{ old('discount', 0) }}">
        </x-ui.form-field>
      </div>
      <div class="col-md-4 mb-3">
        <x-ui.form-field label="Uang Muka (Downpayment)" name="downpayment">
          <input type="number" name="downpayment" class="form-control" min="0" value="{{ old('downpayment', 0) }}">
        </x-ui.form-field>
      </div>
    </div>

    {{-- Status pembayaran --}}
    <x-ui.form-field label="Status Pembayaran" name="status" class="mb-3">
      <select name="status" class="form-select" required>
        <option value="unpaid"  {{ old('status')==='unpaid' ? 'selected' : '' }}>Belum Lunas</option>
        <option value="partial" {{ old('status')==='partial' ? 'selected' : '' }}>Sebagian</option>
      </select>
    </x-ui.form-field>


    {{-- Applicator: tukang & kenek (untuk komisi) --}}




    {{-- (Alternatif) Checklist produk untuk auto-deteksi panoramic --}}
    {{--
    <div class="mb-3">
      <label class="form-label">Produk Dipasang (opsional)</label>
      <div class="row">
        @foreach ($products as $p)
          <div class="col-md-4">
            <label class="form-check">
              <input type="checkbox" class="form-check-input"
                     name="products[{{ $loop->index }}][product_id]" value="{{ $p->id }}">
              <span class="form-check-label">{{ $p->name }}</span>
            </label>
          </div>
        @endforeach
      </div>
      <small class="text-muted">Jika tidak centang apapun, sistem hanya memakai checkbox panoramic di atas.</small>
    </div>
    --}}

    <x-ui.button type="submit" variant="primary" class="mt-3">Simpan Invoice</x-ui.button>
  </form>
</x-ui.card>
@endsection
