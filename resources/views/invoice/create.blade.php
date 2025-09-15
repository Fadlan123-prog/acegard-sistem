@extends('dashboard.index')

@section('title', 'Buat Invoice')
@section('route-breadcrumb', 'customer.index')
@section('breadcrumb', 'Customers')

@section('content')
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Form Buat Invoice</h5>
    <a href="{{ route('customer.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>

  <div class="card-body">
    <form action="{{ route('invoice.store') }}" method="POST">
      @csrf
      <input type="hidden" name="customer_id" value="{{ $customer->id }}">

      {{-- Nomor & tanggal --}}
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">No Invoice</label>
          <input type="text" name="invoice_number" class="form-control"
                 value="{{ old('invoice_number', 'INV-' . now()->format('YmdHis')) }}">
          <small class="text-muted">Kosongkan juga boleh—akan dibuat otomatis jika tidak diisi.</small>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Tanggal Invoice</label>
          <input type="datetime-local" name="invoice_date" class="form-control"
                 value="{{ old('invoice_date', now()->format('Y-m-d\TH:i')) }}" required>
        </div>
      </div>

      {{-- Metode pembayaran (jangan pakai name="type" agar tidak bentrok dengan tipe pemasangan) --}}
      <div class="mb-3">
        <label class="form-label">Metode Pembayaran</label>
        <select name="payment_method" class="form-select">
          <option value="">Pilih</option>
          <option value="cash" {{ old('payment_method')==='cash' ? 'selected' : '' }}>Cash</option>
          <option value="credit" {{ old('payment_method')==='credit' ? 'selected' : '' }}>Credit</option>
        </select>
        <small class="text-muted">Field ini opsional; kolom tersimpan terpisah dari tipe pemasangan.</small>
      </div>

      {{-- Harga / diskon / DP --}}
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Harga</label>
          <input type="number" name="price" class="form-control" min="0" value="{{ old('price') }}" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Diskon</label>
          <input type="number" name="discount" class="form-control" min="0" value="{{ old('discount', 0) }}">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Uang Muka (Downpayment)</label>
          <input type="number" name="downpayment" class="form-control" min="0" value="{{ old('downpayment', 0) }}">
        </div>
      </div>

      {{-- Status pembayaran --}}
      <div class="mb-3">
        <label class="form-label">Status Pembayaran</label>
        <select name="status" class="form-select" required>
          <option value="unpaid"  {{ old('status')==='unpaid' ? 'selected' : '' }}>Belum Lunas</option>
          <option value="partial" {{ old('status')==='partial' ? 'selected' : '' }}>Sebagian</option>
          <option value="paid"    {{ old('status')==='paid' ? 'selected' : '' }}>Lunas</option>
        </select>
      </div>


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

      <button type="submit" class="btn btn-primary mt-3">Simpan Invoice</button>
    </form>
  </div>
</div>
@endsection
