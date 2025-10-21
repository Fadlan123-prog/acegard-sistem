@extends('dashboard.index')

@section('title', 'Add Customers')
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
    <h5 class="card-title mb-0">Form Tambah Customer (Gedung)</h5>
    <a href="{{ route('customer.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>

  <div class="card-body">
    <form action="{{ route('customer.building.store') }}" method="POST">
      @csrf

      <div class="row g-3">
        <div class="col-md-6">
          <label for="wsn" class="form-label">Warranty Serial Number <span class="text-danger">*</span></label>
          <input
            type="text"
            name="wsn"
            id="wsn"
            class="form-control @error('wsn') is-invalid @enderror"
            value="{{ old('wsn') }}"
            required>
          @error('wsn') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Nomor Kartu</label>
          {{-- Card number dibuat otomatis di server saat simpan --}}
          <input type="text" class="form-control" value="(otomatis saat disimpan)" disabled>
        </div>

        <hr class="mt-5">

        <h5 class="text-center">Customer Personal Data</h5>

        <div class="col-md-6">
          <label class="form-label">Nama <span class="text-danger">*</span></label>
          <input
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name') }}"
            required>
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
          <input
            type="text"
            name="phone_number"
            class="form-control @error('phone_number') is-invalid @enderror"
            value="{{ old('phone_number') }}"
            required>
          @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input
            type="email"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email') }}">
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Alamat</label>
          <input
            type="text"
            name="address"
            class="form-control @error('address') is-invalid @enderror"
            value="{{ old('address') }}">
          @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <hr class="mt-5">

        <h5 class="text-center">Authorized Dealer</h5>

        <div class="col-md-6">
          <label class="form-label">Nama Dealer</label>
          <input
            type="text"
            name="dealer_name"
            class="form-control @error('dealer_name') is-invalid @enderror"
            value="{{ old('dealer_name') }}">
          @error('dealer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Aplikator</label>
          <input
            type="text"
            name="applicator"
            class="form-control @error('applicator') is-invalid @enderror"
            value="{{ old('applicator') }}">
          @error('applicator') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Kota</label>
          <input
            type="text"
            name="city"
            class="form-control @error('city') is-invalid @enderror"
            value="{{ old('city') }}">
          @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Negara</label>
          <input
            type="text"
            name="country"
            class="form-control @error('country') is-invalid @enderror"
            value="{{ old('country') }}">
          @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
      </div>

      <hr class="mt-5">

      <h5 class="mt-5 mb-5 text-center">Produk Customer</h5>

      @php
        // Default: 1 baris kosong bila tidak ada old input
        $oldProducts = old('products', [
          ['category_product_building_id' => '', 'product_building_id' => '', 'warantee_duration' => '', 'meters' => '']
        ]);
      @endphp

      <div id="product-container">
        @foreach($oldProducts as $i => $p)
          <div class="row align-items-end mb-3 product-item"
               data-index="{{ $i }}"
               data-old-category="{{ $p['category_product_building_id'] ?? '' }}"
               data-old-product="{{ $p['product_building_id'] ?? '' }}">
            <div class="col-md-3">
              <label>Kategori Produk</label>
              <select
                name="products[{{ $i }}][category_product_building_id]"
                class="form-select category-select @error("products.$i.category_product_building_id") is-invalid @enderror"
                data-index="{{ $i }}"
                required>
                <option value="">Pilih Kategori</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}"
                    {{ (string)$category->id === (string)($p['category_product_building_id'] ?? '') ? 'selected' : '' }}>
                    {{ $category->name }}
                  </option>
                @endforeach
              </select>
              @error("products.$i.category_product_building_id")
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-3">
              <label>Produk</label>
              <select
                name="products[{{ $i }}][product_building_id]"
                class="form-select product-select @error("products.$i.product_building_id") is-invalid @enderror"
                data-index="{{ $i }}"
                required>
                @if(!empty($p['product_building_id']))
                  {{-- placeholder, akan diganti via AJAX onload --}}
                  <option value="{{ $p['product_building_id'] }}" selected>Memuat…</option>
                @else
                  <option value="">Pilih Produk</option>
                @endif
              </select>
              @error("products.$i.product_building_id")
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Durasi Garansi</label>
              <select
                name="products[{{ $i }}][warantee_duration]"
                class="form-select duration-select @error("products.$i.warantee_duration") is-invalid @enderror"
                required>
                <option value="">Pilih Durasi</option>
                <option value="3" {{ (string)($p['warantee_duration'] ?? '') === '3' ? 'selected' : '' }}>3 Tahun</option>
                <option value="5" {{ (string)($p['warantee_duration'] ?? '') === '5' ? 'selected' : '' }}>5 Tahun</option>
                <option value="7" {{ (string)($p['warantee_duration'] ?? '') === '7' ? 'selected' : '' }}>7 Tahun</option>
              </select>
              @error("products.$i.warantee_duration")
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-2">
              <label>Ukuran (m²)</label>
              <input
                type="number"
                step="0.01"
                min="0.01"
                name="products[{{ $i }}][meters]"
                class="form-control meters-input @error("products.$i.meters") is-invalid @enderror"
                value="{{ $p['meters'] ?? '' }}"
                required>
              @error("products.$i.meters")
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-1">
              <button type="button" class="btn btn-danger btn-sm remove-product">×</button>
            </div>
          </div>
        @endforeach
      </div>

      <button type="button" class="btn btn-sm btn-outline-primary" id="add-product">+ Tambah Produk</button>

      <hr class="mt-5">

      <div class="col-md-6 mt-3">
        <label class="form-label">Konfirmasi Password Anda <span class="text-danger">*</span></label>
        <input
          type="password"
          name="admin_password"
          class="form-control @error('admin_password') is-invalid @enderror"
          required>
        @error('admin_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <button type="submit" class="btn btn-primary mt-5">Simpan Customer</button>
    </form>
  </div>
</div>

{{-- jQuery untuk prefill & dinamis produk --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let productIndex = {{ count($oldProducts) }};

// Tambah baris produk
document.getElementById('add-product').addEventListener('click', function () {
  const container = document.getElementById('product-container');
  const template  = container.querySelector('.product-item');
  const clone     = template.cloneNode(true);

  // Update semua name + data-index + reset nilai
  clone.querySelectorAll('select, input').forEach(el => {
    const name = el.getAttribute('name'); // contoh: products[0][meters]
    if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${productIndex}]`));

    if (el.classList.contains('category-select') || el.classList.contains('product-select')) {
      el.setAttribute('data-index', productIndex);
    }

    if (el.tagName === 'SELECT') {
      el.selectedIndex = 0;
      if (el.classList.contains('product-select')) {
        el.innerHTML = '<option value="">Pilih Produk</option>';
      }
      el.classList.remove('is-invalid');
    } else {
      el.value = '';
      el.classList.remove('is-invalid');
    }
  });

  // bersihkan data-old
  clone.setAttribute('data-old-category', '');
  clone.setAttribute('data-old-product', '');

  container.appendChild(clone);
  productIndex++;
});

// Hapus baris
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('remove-product')) {
    const item = e.target.closest('.product-item');
    if (document.querySelectorAll('.product-item').length > 1) item.remove();
  }
});

// Prefill produk via AJAX saat halaman dimuat (untuk old input)
$(function(){
  $('.product-item').each(function(){
    const $row = $(this);
    const idx  = $row.data('index');
    const cat  = String($row.data('old-category') || '');
    const prod = String($row.data('old-product') || '');
    if (!cat) return;

    const $productSelect = $(`select[name="products[${idx}][product_building_id]"]`);
    $productSelect.html('<option value="">Memuat...</option>');

    $.get(`{{ url('products-by-category-building') }}/${cat}`)
      .done(function (data) {
        let options = '<option value="">Pilih Produk</option>';
        data.forEach(function(p){
          const selected = (String(p.id) === prod) ? 'selected' : '';
          options += `<option value="${p.id}" ${selected}>${p.name}</option>`;
        });
        $productSelect.html(options);
      })
      .fail(function () {
        $productSelect.html('<option value="">Pilih Produk</option>');
      });
  });
});

// AJAX isi produk saat kategori berubah
$(document).on('change', '.category-select', function () {
  const categoryId = $(this).val();
  const index = $(this).data('index');
  const $productSelect = $(`select[name="products[${index}][product_building_id]"]`);
  $productSelect.html('<option value="">Memuat...</option>');

  if (!categoryId) return $productSelect.html('<option value="">Pilih Produk</option>');

  $.get(`{{ url('products-by-category-building') }}/${categoryId}`)
    .done(function (data) {
      let options = '<option value="">Pilih Produk</option>';
      data.forEach(p => options += `<option value="${p.id}">${p.name}</option>`);
      $productSelect.html(options);
    })
    .fail(function () {
      alert('Gagal mengambil produk.');
      $productSelect.html('<option value="">Pilih Produk</option>');
    });
});
</script>

@endsection
