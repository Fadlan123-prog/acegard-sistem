@extends('dashboard.index')

@section('title', 'Input Roll Masuk')
@section('route-breadcrumb', 'film-stock.index')
@section('breadcrumb', 'Input Roll Masuk')

@section('content')

@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <strong>Terjadi kesalahan.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif

@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Input Roll Masuk</div>
                <small class="text-muted">
                    Roll masuk dicatat per kode roll, stok berjalan dihitung berdasarkan stock pool.
                </small>
            </div>

            <x-ui.button href="{{ route('film-stock.index') }}" variant="secondary" size="sm">
                Kembali
            </x-ui.button>
        </div>
    @endslot

    <form action="{{ route('film-stock.store') }}" method="POST" id="filmStockForm">
        @csrf

        <div class="row">

            <x-ui.form-field label="Cabang" name="branch_id" :required="true" class="col-md-4 mb-3">
                <select name="branch_id" class="form-select" required>
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ old('branch_id', session('active_branch_id')) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} - {{ $branch->city ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </x-ui.form-field>

            <x-ui.form-field label="Jenis Pemakaian" name="usage_for" :required="true"
                help="Untuk stok yang dipakai mobil pilih Mobil, untuk bangunan pilih Gedung."
                class="col-md-4 mb-3">
                <select name="usage_for" id="usageFor" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="car" {{ old('usage_for') == 'car' ? 'selected' : '' }}>Mobil</option>
                    <option value="building" {{ old('usage_for') == 'building' ? 'selected' : '' }}>Gedung</option>
                    <option value="universal" {{ old('usage_for') == 'universal' ? 'selected' : '' }}>Universal</option>
                </select>
            </x-ui.form-field>

            <x-ui.form-field label="Tanggal Masuk" name="received_date" class="col-md-4 mb-3">
                <input type="date"
                       name="received_date"
                       class="form-control"
                       value="{{ old('received_date', now()->format('Y-m-d')) }}">
            </x-ui.form-field>
        </div>

        <div id="carProductWrapper" class="border rounded p-3 mb-3 d-none">
            <h6 class="fw-bold mb-3">Produk Mobil</h6>

            <div class="row">
                <x-ui.form-field label="Kategori Produk Mobil" name="category_product_id" :required="true" class="col-md-6 mb-3">
                    <select name="category_product_id" id="categoryProductId" class="form-select">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categoryProducts as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_product_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </x-ui.form-field>

                <x-ui.form-field label="Produk / Kode Kegelapan" name="product_id" :required="true" class="col-md-6 mb-3">
                    <select name="product_id" id="productId" class="form-select">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                    data-category-id="{{ $product->category_id }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} - {{ $product->description ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>
        </div>

        <div id="buildingProductWrapper" class="border rounded p-3 mb-3 d-none">
            <h6 class="fw-bold mb-3">Produk Gedung</h6>

            <div class="row">
                <x-ui.form-field label="Kategori Produk Gedung" name="category_product_building_id" :required="true" class="col-md-6 mb-3">
                    <select name="category_product_building_id" id="categoryProductBuildingId" class="form-select">
                        <option value="">-- Pilih Kategori Gedung --</option>
                        @foreach($categoryProductBuildings as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_product_building_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </x-ui.form-field>

                <x-ui.form-field label="Produk Gedung / Kode Kegelapan" name="product_building_id" :required="true" class="col-md-6 mb-3">
                    <select name="product_building_id" id="productBuildingId" class="form-select">
                        <option value="">-- Pilih Produk Gedung --</option>
                        @foreach($productBuildings as $product)
                            <option value="{{ $product->id }}"
                                    data-category-id="{{ $product->category_product_building_id }}"
                                {{ old('product_building_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} - {{ $product->description ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <x-ui.form-field label="Lebar Roll" name="width_meter" class="col-md-3 mb-3">
                <input type="number"
                       name="width_meter"
                       id="widthMeter"
                       class="form-control"
                       value="{{ old('width_meter', 1.5) }}"
                       step="0.01"
                       min="0.01">
            </x-ui.form-field>

            <x-ui.form-field label="Panjang per Roll" name="length_meter" class="col-md-3 mb-3">
                <input type="number"
                       name="length_meter"
                       id="lengthMeter"
                       class="form-control"
                       value="{{ old('length_meter', 30) }}"
                       step="0.01"
                       min="0.01">
            </x-ui.form-field>

            <x-ui.form-field label="Jumlah Roll" name="qty_roll" :required="true" class="col-md-3 mb-3">
                <input type="number"
                       name="qty_roll"
                       id="qtyRoll"
                       class="form-control"
                       value="{{ old('qty_roll', 1) }}"
                       min="1"
                       required>
            </x-ui.form-field>

            <x-ui.form-field label="Total Meter Masuk" name="total_meter_preview" class="col-md-3 mb-3">
                <input type="text" id="totalMeterPreview" class="form-control fw-bold" readonly>
            </x-ui.form-field>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Kode Roll <span class="text-danger">*</span></label>

                <x-ui.button type="button" variant="outline" size="sm" id="generateRollInput">
                    Generate Field Kode
                </x-ui.button>
            </div>

            <small class="text-muted d-block mb-2">
                Jumlah kode roll harus sama dengan jumlah roll masuk.
            </small>

            <div id="rollCodeWrapper" class="row g-2">
                @php
                    $oldRollCodes = old('roll_codes', ['']);
                @endphp

                @foreach($oldRollCodes as $i => $code)
                    <div class="col-md-4 roll-code-item">
                        <input type="text"
                               name="roll_codes[]"
                               class="form-control"
                               value="{{ $code }}"
                               placeholder="Contoh: AP50-001"
                               required>
                    </div>
                @endforeach
            </div>
        </div>

        <x-ui.form-field label="Catatan" name="notes" class="mb-4">
            <textarea name="notes"
                      class="form-control"
                      rows="3"
                      placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
        </x-ui.form-field>

        <div class="d-flex gap-2">
            <x-ui.button type="submit" variant="primary">Simpan Roll Masuk</x-ui.button>
            <x-ui.button href="{{ route('film-stock.index') }}" variant="outline">Batal</x-ui.button>
        </div>

    </form>
</x-ui.card>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const usageFor = document.getElementById('usageFor');

    const carWrapper = document.getElementById('carProductWrapper');
    const buildingWrapper = document.getElementById('buildingProductWrapper');

    const categoryProductId = document.getElementById('categoryProductId');
    const productId = document.getElementById('productId');

    const categoryProductBuildingId = document.getElementById('categoryProductBuildingId');
    const productBuildingId = document.getElementById('productBuildingId');

    const qtyRoll = document.getElementById('qtyRoll');
    const lengthMeter = document.getElementById('lengthMeter');
    const totalMeterPreview = document.getElementById('totalMeterPreview');

    const generateRollInput = document.getElementById('generateRollInput');
    const rollCodeWrapper = document.getElementById('rollCodeWrapper');

    function toggleProductSource() {
        const value = usageFor.value;

        carWrapper.classList.add('d-none');
        buildingWrapper.classList.add('d-none');

        if (value === 'car' || value === 'universal') {
            carWrapper.classList.remove('d-none');
        }

        if (value === 'building') {
            buildingWrapper.classList.remove('d-none');
        }
    }

    function filterProductOptions() {
        const selectedCategoryId = categoryProductId.value;

        Array.from(productId.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = option.getAttribute('data-category-id') !== selectedCategoryId;
        });

        if (productId.selectedOptions.length && productId.selectedOptions[0].hidden) {
            productId.value = '';
        }
    }

    function filterBuildingProductOptions() {
        const selectedCategoryId = categoryProductBuildingId.value;

        Array.from(productBuildingId.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = option.getAttribute('data-category-id') !== selectedCategoryId;
        });

        if (productBuildingId.selectedOptions.length && productBuildingId.selectedOptions[0].hidden) {
            productBuildingId.value = '';
        }
    }

    function calculateTotalMeter() {
        const qty = parseFloat(qtyRoll.value || 0);
        const length = parseFloat(lengthMeter.value || 0);
        const total = qty * length;

        totalMeterPreview.value = total.toLocaleString('id-ID') + ' meter';
    }

    function generateRollCodeFields() {
        const qty = parseInt(qtyRoll.value || 1);
        rollCodeWrapper.innerHTML = '';

        for (let i = 0; i < qty; i++) {
            const div = document.createElement('div');
            div.className = 'col-md-4 roll-code-item';
            div.innerHTML = `
                <input type="text"
                       name="roll_codes[]"
                       class="form-control"
                       placeholder="Kode Roll ${i + 1}"
                       required>
            `;
            rollCodeWrapper.appendChild(div);
        }
    }

    usageFor.addEventListener('change', toggleProductSource);
    categoryProductId.addEventListener('change', filterProductOptions);
    categoryProductBuildingId.addEventListener('change', filterBuildingProductOptions);

    qtyRoll.addEventListener('input', calculateTotalMeter);
    lengthMeter.addEventListener('input', calculateTotalMeter);

    generateRollInput.addEventListener('click', generateRollCodeFields);

    toggleProductSource();
    filterProductOptions();
    filterBuildingProductOptions();
    calculateTotalMeter();
});
</script>

@endsection
