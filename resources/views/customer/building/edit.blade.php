@extends('dashboard.index')

@section('title', 'Edit Customer')
@section('route-breadcrumb', 'customer.building.index')
@section('breadcrumb', 'Customers Building')

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
        <a href="{{ route('customer.building.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>

    <div class="card-body">
        <form action="{{ route('customer.building.update', $customer->id) }}" method="POST">
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
            </div>

            <hr class="mt-5">

            <h5 class="mt-5 mb-5 text-center">Produk Customer</h5>
            <div id="product-container">
                @foreach ($customer->products as $index => $productItem)
                <div class="row align-items-end mb-3 product-item">
                    <div class="col-md-3">
                        <label>Kategori Produk</label>
                        <select name="products[{{ $index }}][category_product_building_id]" class="form-select category-select" data-index="{{ $index }}" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $category->id == $productItem->category_product_building_id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Produk</label>
                        <select name="products[{{ $index }}][product_building_id]" class="form-select product-select" data-index="{{ $index }}" required>
                            <option value="">Pilih Produk</option>
                            @foreach ($products->where('category_product_building_id', $productItem->category_product_building_id) as $product)
                                <option value="{{ $product->id }}" {{ $product->id == $productItem->product_building_id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Durasi Garansi</label>
                        <select name="products[{{ $index }}][warantee_duration]" class="form-select" required>
                            <option value="">Pilih Durasi</option>
                            @foreach([3, 5, 7] as $year)
                                <option value="{{ $year }}" {{ $productItem->warantee_duration == $year ? 'selected' : '' }}>
                                    {{ $year }} Tahun
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Ukuran</label>
                        <input type="text" name="products[{{ $index }}][meters]" class="form-control" value="{{ $productItem->meters }}">
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product text-center">×</button>
                    </div>
                </div>
                @endforeach
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
            <button type="submit" class="btn btn-primary mt-5">Update Customer</button>
        </form>
    </div>
</div>
<script>
let productIndex = {{ count($customer->products) }};

document.getElementById('add-product').addEventListener('click', function () {
    const container = document.getElementById('product-container');
    const newItem = container.querySelector('.product-item').cloneNode(true);

    newItem.querySelectorAll('select, input').forEach(el => {
        const name = el.getAttribute('name');
        const newName = name.replace(/\[\d+\]/, `[${productIndex}]`);
        el.setAttribute('name', newName);
        el.setAttribute('data-index', productIndex);

        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
    });

    container.appendChild(newItem);
    productIndex++;
});

$(document).on('change', '.category-select', function () {
    let categoryId = $(this).val();
    let index = $(this).data('index');
    let $productSelect = $(`select[name="products[${index}][product_building_id]"]`);
    $productSelect.html('<option value="">Memuat...</option>');

    if (categoryId) {
        $.ajax({
            url: '{{ url("products-by-category-building") }}/' + categoryId,
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

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-product')) {
        const item = e.target.closest('.product-item');
        if (document.querySelectorAll('.product-item').length > 1) {
            item.remove();
        }
    }
});
</script>

@endsection
