@extends('dashboard.index')

@section('title', 'Edit Customer')
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
        <h5 class="card-title mb-0">Form Edit Customer</h5>
        <a href="{{ route('customer.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('customer.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="wsn" class="form-label">Warranty Serial Number <span class="text-danger">*</span></label>
                    <input type="text" name="wsn" class="form-control" value="{{ old('wsn', $customer->wsn) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Kartu <span class="text-danger">*</span></label>
                    <input type="number" name="card_number" class="form-control" value="{{ old('card_number', $customer->card_number) }}" required>
                </div>

                <hr class="mt-5">
                <h5 class="text-center">Customer Personal Data</h5>

                <div class="col-md-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $customer->phone_number) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $customer->address) }}">
                </div>

                <hr class="mt-5">
                <h5 class="text-center">Authorized Dealer</h5>

                <div class="col-md-6">
                    <label class="form-label">Nama Dealer</label>
                    <input type="text" name="dealer_name" class="form-control" value="{{ old('dealer_name', $customer->dealer_name) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Aplikator</label>
                    <input type="text" name="applicator" class="form-control" value="{{ old('applicator', $customer->applicator) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Negara</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country', $customer->country) }}">
                </div>

                <hr class="mt-5">
                <h5 class="text-center">Vehicle Description</h5>

                <div class="col-md-6">
                    <label class="form-label">Plat Nomor</label>
                    <input type="text" name="plat_number" class="form-control" value="{{ old('plat_number', $customer->plat_number) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Merek Kendaraan</label>
                    <input type="text" name="vehicle_brand" class="form-control" value="{{ old('vehicle_brand', $customer->vehicle_brand) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Model Kendaraan</label>
                    <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model', $customer->vehicle_model) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tahun Kendaraan</label>
                    <input type="number" name="vehicle_year" class="form-control" value="{{ old('vehicle_year', $customer->vehicle_year) }}">
                </div>
            </div>

            <hr class="mt-5">
            <h5 class="mt-5 mb-5 text-center">Produk Customer</h5>
            <div id="product-container">

                @foreach ($customer->products as $index => $productItem)
                    <div class="row product-item">
                        <input type="hidden" name="products[{{ $index }}][id]" value="{{ $productItem->pivot->id }}">

                        <div class="col-md-4">
                            <label>Part</label>
                            <select name="products[{{ $index }}][part_id]" class="form-select">
                                <option value="">Pilih Part</option>
                                @foreach($parts as $part)
                                    <option value="{{ $part->id }}" {{ $part->id == $productItem->pivot->part_id ? 'selected' : '' }}>
                                        {{ $part->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label>Kategori Produk</label>
                            <select name="products[{{ $index }}][category_product_id]" class="form-select category-select" data-index="{{ $index }}">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $cat->id == $productItem->pivot->category_product_id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Produk</label>
                            <select name="products[{{ $index }}][product_id]" class="form-select product-select" data-index="{{ $index }}">
                                <option value="{{ $productItem->id }}">{{ $productItem->name }}</option>
                            </select>
                        </div>
                    </div>
                @endforeach

            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-product">+ Tambah Produk</button>

            <hr class="mt-5">
            <div class="col-md-12 mt-3">
                <label class="form-label">Durasi Garansi</label>
                <select name="warranty_duration" class="form-select" required>
                    <option value="">Pilih Durasi</option>
                    <option value="5" {{ $customer->warantee_duration == 5 ? 'selected' : '' }}>5 Tahun</option>
                    <option value="7" {{ $customer->warantee_duration == 7 ? 'selected' : '' }}>7 Tahun</option>
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
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let productIndex = 1;

document.getElementById('add-product').addEventListener('click', function () {
    const container = document.getElementById('product-container');
    const newItem = container.querySelector('.product-item').cloneNode(true);

    // Update name and data-index
    newItem.querySelectorAll('select').forEach(select => {
        const name = select.getAttribute('name');
        const newName = name.replace(/\[\d+\]/, `[${productIndex}]`);
        select.setAttribute('name', newName);

        // Update data-index jika ada
        if (select.classList.contains('category-select') || select.classList.contains('product-select')) {
            select.setAttribute('data-index', productIndex);
        }

        select.selectedIndex = 0; // reset selection
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
</script>

<script>
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
</script>

@endsection
