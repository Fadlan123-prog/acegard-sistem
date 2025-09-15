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
        <form action="{{ route('customer.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="wsn" class="form-label">Warranty Serial Number <span class="text-danger">*</span></label>
                    <input type="text" name="wsn" class="form-control" required value="{{ old('wsn') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Kartu <span class="text-danger">*</span></label>
                    <input type="number" name="card_number" class="form-control" disabled >
                </div>

                <hr class="mt-5">

                <h5 class="text-center">Customer Personal Data</h5>

                <div class="col-md-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                    <input type="text" name="phone_number" class="form-control" required value="{{ old('phone_number') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                </div>

                <hr class="mt-5">

                <h5 class="text-center">Authorized Dealer</h5>

                <div class="col-md-6">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Dealer</label>
                            <input type="text" name="dealer_name" class="form-control" value="{{ old('dealer_name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marketing</label>
                            <select name="marketing_id" class="form-select">
                                <option value="">— Tidak ada —</option>
                                @foreach ($employees as $e)
                                    @if ($e->job_position === 'Marketing')
                                        <option value="{{ $e->id }}" {{ old('marketing_id')==$e->id ? 'selected' : '' }}>
                                        {{ $e->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                        <label class="form-label">Tukang <span class="text-danger">*</span></label>
                        <select name="tukang_id" class="form-select" required>
                            <option value="">Pilih</option>
                            @foreach ($employees as $e)
                            @if ($e->job_position === 'Teknisi')
                            <option value="{{ $e->id }}" {{ old('tukang_id')==$e->id ? 'selected' : '' }}>
                                {{ $e->name }}
                            </option>
                            @endif
                            @endforeach
                        </select>

                        </div>
                        <div class="col-md-6 mb-3">
                        <label class="form-label">Kenek (opsional)</label>
                        <select name="kenek_id" class="form-select">
                            <option value="">— Tidak ada —</option>
                            @foreach ($employees as $e)
                            @if ($e->job_position === 'Kenek')

                            <option value="{{ $e->id }}" {{ old('kenek_id')==$e->id ? 'selected' : '' }}>
                                {{ $e->name }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Negara</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                </div>

                <hr class="mt-5">

                <h5 class="text-center">Vehicle Description</h5>

                <div class="col-md-6">
                    <label class="form-label">Plat Nomor</label>
                    <input type="text" name="plat_number" class="form-control" value="{{ old('plat_number') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Merek Kendaraan</label>
                    <input type="text" name="vehicle_brand" class="form-control" value="{{ old('vehicle_brand') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Model Kendaraan</label>
                    <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tahun Kendaraan</label>
                    <input type="number" name="vehicle_year" class="form-control" value="{{ old('vehicle_year') }}">
                </div>



            </div>

            <hr class="mt-5">

            <h5 class="mt-5 mb-5 text-center">Produk Customer</h5>
            <div id="product-container">
                @php
                    $oldProducts = old('products', [['part_id'=>'','category_product_id'=>'','product_id'=>'']]);
                @endphp
                <div class="row">
                   <div class="col-md-4">
                    <label class="form-label">Tipe Pemasangan <span class="text-danger">*</span></label>
                        <select name="install_type" id="install_type" class="form-select mb-2" required>
                            <option value="">— Pilih —</option>
                            <option value="fullset" {{ old('install_type')==='fullset' ? 'selected' : '' }}>Fullset</option>
                            <option value="skkb"    {{ old('install_type')==='skkb' ? 'selected' : '' }}>SKKB</option>
                            <option value="dsp"     {{ old('install_type')==='dsp' ? 'selected' : '' }}>D/S/P</option>
                        </select>

                        <div id="panoramic_wrap"
                            class="mt-3 {{ old('install_type','')==='fullset' ? '' : 'd-none' }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="has_panoramic"
                                name="has_panoramic" value="1" {{ old('has_panoramic') ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_panoramic">Dengan Panoramic</label>
                        </div>
                        <div class="form-text">
                            Jika dicentang, komisi Fullset untuk hari ini mengikuti aturan panoramic.
                        </div>
                        </div>
                    </div>
                </div>

                @foreach($oldProducts as $i => $prod)
                    <div class="row align-items-end mb-3 product-item">
                    <div class="col-md-4">
                        <label>Part</label>
                        <select name="products[{{ $i }}][part_id]" class="form-select" required>
                        <option value="">Pilih Part</option>
                        @foreach($parts as $part)
                            <option value="{{ $part->id }}" {{ (old("products.$i.part_id", $prod['part_id'] ?? '') == $part->id) ? 'selected' : '' }}>
                            {{ $part->name }}
                            </option>
                        @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Kategori Produk</label>
                        <select name="products[{{ $i }}][category_product_id]"
                                class="form-select category-select" data-index="{{ $i }}" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old("products.$i.category_product_id", $prod['category_product_id'] ?? '') == $cat->id) ? 'selected' : '' }}>
                            {{ $cat->name }}
                            </option>
                        @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Nama Produk</label>
                        <select name="products[{{ $i }}][product_id]"
                                class="form-select product-select" data-index="{{ $i }}" required>
                        {{-- akan diisi via AJAX, tapi jika old() ada dan option tersedia, akan diset di JS --}}
                        <option value="">Pilih Produk</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product text-center">×</button>
                    </div>
                    </div>
                @endforeach

            </div>

<button type="button" class="btn btn-sm btn-outline-primary" id="add-product">+ Tambah Produk</button>

<hr class="mt-5">

<div class="col-md-12 mt-3">
                    <label class="form-label">Durasi Garansi</label>
                    <select name="warantee_duration" class="form-select" required>
                        <option value="">Pilih Durasi</option>
                        <option value="5">5 Tahun</option>
                        <option value="7">7 Tahun</option>
                    </select>
                </div>



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
  let productIndex = {{ count(old('products', [['part_id'=>'','category_product_id'=>'','product_id'=>'']])) }};

  document.getElementById('add-product').addEventListener('click', function () {
      const container = document.getElementById('product-container');
      const template  = container.querySelector('.product-item');
      const newItem   = template.cloneNode(true);

      // reset values & rename indexes
      newItem.querySelectorAll('select').forEach(select => {
          const name = select.getAttribute('name');
          const newName = name.replace(/\[\d+\]/, `[${productIndex}]`);
          select.setAttribute('name', newName);

          if (select.classList.contains('category-select') || select.classList.contains('product-select')) {
              select.setAttribute('data-index', productIndex);
          }

          // kosongkan pilihan
          select.selectedIndex = 0;
          if (select.classList.contains('product-select')) {
              select.innerHTML = '<option value="">Pilih Produk</option>';
          }
      });

      container.appendChild(newItem);
      productIndex++;
  });

  // Remove row
  document.addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-product')) {
          const item = e.target.closest('.product-item');
          if (document.querySelectorAll('.product-item').length > 1) {
              item.remove();
          }
      }
  });

  // AJAX isi produk sesuai kategori
  $(document).on('change', '.category-select', function () {
      let categoryId = $(this).val();
      let index = $(this).data('index');
      let $productSelect = $(`select[name="products[${index}][product_id]"]`);

      $productSelect.html('<option value="">Memuat...</option>');

      if (categoryId) {
          $.ajax({
              url: '{{ url("products-by-category") }}/' + categoryId,
              type: 'GET',
              success: function (data) {
                  let options = '<option value="">Pilih Produk</option>';
                  data.forEach(product => {
                      options += `<option value="${product.id}">${product.name}</option>`;
                  });
                  $productSelect.html(options);

                  // SET old product_id jika ada
                  const oldVal = @json(old('products', []));
                  if (oldVal[index] && oldVal[index].product_id) {
                      $productSelect.val(oldVal[index].product_id);
                  }
              },
              error: function () {
                  alert('Gagal mengambil produk.');
                  $productSelect.html('<option value="">Pilih Produk</option>');
              }
          });
      } else {
          $productSelect.html('<option value="">Pilih Produk</option>');
      }
  });

  // PRELOAD: trigger change untuk semua baris yang punya kategori lama
  $(function() {
      const oldProducts = @json(old('products', []));
      oldProducts.forEach((row, i) => {
          if (row.category_product_id) {
              $(`select[name="products[${i}][category_product_id]"]`).trigger('change');
          }
      });
  });
</script>
<script>
  const selectType = document.getElementById('install_type');
  const panoWrap   = document.getElementById('panoramic_wrap');

  function togglePano() {
    panoWrap.classList.toggle('d-none', selectType.value !== 'fullset');
  }
  selectType.addEventListener('change', togglePano);
  // onload (kalau old value bukan fullset, tetap hidden)
  togglePano();
</script>

@endsection
