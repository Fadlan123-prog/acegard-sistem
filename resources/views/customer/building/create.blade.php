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
        <h5 class="card-title mb-0">Form Tambah Customer</h5>
        <a href="{{ route('customer.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('customer.building.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="wsn" class="form-label">Warranty Serial Number <span class="text-danger">*</span></label>
                    <input type="text" name="wsn" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Kartu <span class="text-danger">*</span></label>
                    <input type="number" name="card_number" class="form-control" required>
                </div>

                <hr class="mt-5">

                <h5 class="text-center">Customer Personal Data</h5>

                <div class="col-md-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                    <input type="text" name="phone_number" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="address" class="form-control">
                </div>

                <hr class="mt-5">

                <h5 class="text-center">Authorized Dealer</h5>

                <div class="col-md-6">
                    <label class="form-label">Nama Dealer</label>
                    <input type="text" name="dealer_name" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Aplikator</label>
                    <input type="text" name="applicator" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Negara</label>
                    <input type="text" name="country" class="form-control">
                </div>

            </div>

            <hr class="mt-5">

            <h5 class="mt-5 mb-5 text-center">Produk Customer</h5>
            <div id="product-container">
            <div class="row align-items-end mb-3 product-item">
                <div class="col-md-3">
                <label>Kategori Produk</label>
                <select name="products[0][category_product_building_id]" class="form-select category-select" data-index="0" required>
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                </div>

                <div class="col-md-3">
                <label>Produk</label>
                <select name="products[0][product_building_id]" class="form-select product-select" data-index="0" required>
                    <option value="">Pilih Produk</option>
                </select>
                </div>

                <div class="col-md-3">
                <label class="form-label">Durasi Garansi</label>
                <select name="products[0][warantee_duration]" class="form-select duration-select" required>
                    <option value="">Pilih Durasi</option>
                    <option value="3">3 Tahun</option>
                    <option value="5">5 Tahun</option>
                    <option value="7">7 Tahun</option>
                </select>
                </div>

                <div class="col-md-2">
                <label>Ukuran (m²)</label>
                <input type="number" step="0.01" min="0.01" name="products[0][meters]" class="form-control meters-input" required>
                </div>

                <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm remove-product">×</button>
                </div>
            </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary" id="add-product">+ Tambah Produk</button>

<hr class="mt-5">





<div class="col-md-6 mt-3">
    <label class="form-label">Konfirmasi Password Anda <span class="text-danger">*</span></label>
    <input type="password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" required>
    @error('admin_password')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-primary mt-5">
    Simpan Customer
</button>
</div>

</div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let productIndex = 1;

document.getElementById('add-product').addEventListener('click', function () {
  const container = document.getElementById('product-container');
  const template  = container.querySelector('.product-item');
  const clone     = template.cloneNode(true);

  // Update semua name + data-index
  clone.querySelectorAll('select, input').forEach(el => {
    const name = el.getAttribute('name');              // contoh: products[0][meters]
    if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${productIndex}]`));

    // set data-index untuk select yang butuh ajax
    if (el.classList.contains('category-select') || el.classList.contains('product-select')) {
      el.setAttribute('data-index', productIndex);
    }

    // reset nilai
    if (el.tagName === 'SELECT') {
      el.selectedIndex = 0;
      if (el.classList.contains('product-select')) {
        el.innerHTML = '<option value="">Pilih Produk</option>';
      }
    } else {
      el.value = '';
    }
  });

  container.appendChild(clone);
  productIndex++;
});

// Remove row
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('remove-product')) {
    const item = e.target.closest('.product-item');
    if (document.querySelectorAll('.product-item').length > 1) item.remove();
  }
});

// AJAX isi produk sesuai kategori
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
