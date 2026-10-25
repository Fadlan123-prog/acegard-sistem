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

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
            <div>
                <div class="ui-card-title">Input Roll Masuk</div>
                <small class="text-muted">
                    Satu kiriman bisa berisi beberapa varian (kategori &amp; persentase kegelapan berbeda) —
                    tambahkan varian sebanyak yang datang, kode roll tiap varian otomatis terisi.
                </small>
            </div>

            <x-ui.button :href="route('film-stock.index')" variant="secondary" size="sm">
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

        {{-- Varian Produk — looping: satu blok per kategori/persentase kegelapan yang datang --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">Varian Produk yang Masuk</h6>
            <x-ui.button type="button" variant="primary" size="sm" id="addBatchBtn">
                <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Tambah Varian
            </x-ui.button>
        </div>

        <div id="batchWrapper"></div>

        <div class="d-flex justify-content-end mb-4">
            <div class="text-end">
                <small class="text-muted d-block">Total Meter Semua Varian</small>
                <span class="fw-bold fs-5" id="grandTotalMeter">0 meter</span>
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
            <x-ui.button :href="route('film-stock.index')" variant="outline">Batal</x-ui.button>
        </div>

    </form>
</x-ui.card>

{{-- Template satu blok varian --}}
<template id="batchTemplate">
    <div class="border rounded p-3 mb-3 batch-item">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Varian <span class="batch-number">1</span></h6>
            <x-ui.button type="button" variant="danger-outline" size="sm" class="remove-batch">
                <iconify-icon icon="mdi:trash-can-outline" class="me-1"></iconify-icon>Hapus
            </x-ui.button>
        </div>

        <div class="row car-product-wrapper d-none">
            <div class="col-md-6 mb-3">
                <label class="form-label">Kategori Produk Mobil <span class="text-danger">*</span></label>
                <select name="batches[__INDEX__][category_product_id]" class="form-select category-product-id">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categoryProducts as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Produk / Kode Kegelapan <span class="text-danger">*</span></label>
                <select name="batches[__INDEX__][product_id]" class="form-select product-id">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                                data-category-id="{{ $product->category_id }}"
                                data-name="{{ $product->name }}">
                            {{ $product->name }} - {{ $product->description ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row building-product-wrapper d-none">
            <div class="col-md-6 mb-3">
                <label class="form-label">Kategori Produk Gedung <span class="text-danger">*</span></label>
                <select name="batches[__INDEX__][category_product_building_id]" class="form-select category-product-building-id">
                    <option value="">-- Pilih Kategori Gedung --</option>
                    @foreach($categoryProductBuildings as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Produk Gedung / Kode Kegelapan <span class="text-danger">*</span></label>
                <select name="batches[__INDEX__][product_building_id]" class="form-select product-building-id">
                    <option value="">-- Pilih Produk Gedung --</option>
                    @foreach($productBuildings as $product)
                        <option value="{{ $product->id }}"
                                data-category-id="{{ $product->category_product_building_id }}"
                                data-name="{{ $product->name }}">
                            {{ $product->name }} - {{ $product->description ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Lebar Roll</label>
                <input type="number" name="batches[__INDEX__][width_meter]" class="form-control width-meter" value="1.5" step="0.01" min="0.01">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Panjang per Roll</label>
                <input type="number" name="batches[__INDEX__][length_meter]" class="form-control length-meter" value="30" step="0.01" min="0.01">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Jumlah Roll <span class="text-danger">*</span></label>
                <input type="number" name="batches[__INDEX__][qty_roll]" class="form-control qty-roll" value="1" min="1" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Total Meter Varian</label>
                <input type="text" class="form-control fw-bold total-meter-preview" readonly>
            </div>
        </div>

        <div class="mb-1">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Kode Roll <span class="text-danger">*</span></label>
                <x-ui.button type="button" variant="outline" size="sm" class="regenerate-codes-btn">
                    <iconify-icon icon="mdi:refresh" class="me-1"></iconify-icon>Generate Ulang
                </x-ui.button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;" class="text-center">#</th>
                            <th>Kode Roll</th>
                        </tr>
                    </thead>
                    <tbody class="roll-code-wrapper"></tbody>
                </table>
            </div>
        </div>
    </div>
</template>

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const usageFor = document.getElementById('usageFor');
    const batchWrapper = document.getElementById('batchWrapper');
    const addBatchBtn = document.getElementById('addBatchBtn');
    const grandTotalMeter = document.getElementById('grandTotalMeter');
    const batchTemplate = document.getElementById('batchTemplate').innerHTML;
    const suggestUrl = '{{ route('film-stock.suggest-roll-codes') }}';

    let batchIndex = 0;

    function toggleProductSource(block) {
        const value = usageFor.value;
        const car = block.querySelector('.car-product-wrapper');
        const building = block.querySelector('.building-product-wrapper');

        car.classList.add('d-none');
        building.classList.add('d-none');

        if (value === 'car' || value === 'universal') car.classList.remove('d-none');
        if (value === 'building') building.classList.remove('d-none');
    }

    function toggleAllProductSources() {
        document.querySelectorAll('.batch-item').forEach(toggleProductSource);
    }

    function filterProductOptions(block) {
        const categorySel = block.querySelector('.category-product-id');
        const productSel = block.querySelector('.product-id');
        const selectedCategoryId = categorySel.value;

        Array.from(productSel.options).forEach(option => {
            if (!option.value) { option.hidden = false; return; }
            option.hidden = option.getAttribute('data-category-id') !== selectedCategoryId;
        });

        if (productSel.selectedOptions.length && productSel.selectedOptions[0].hidden) {
            productSel.value = '';
        }
    }

    function filterBuildingProductOptions(block) {
        const categorySel = block.querySelector('.category-product-building-id');
        const productSel = block.querySelector('.product-building-id');
        const selectedCategoryId = categorySel.value;

        Array.from(productSel.options).forEach(option => {
            if (!option.value) { option.hidden = false; return; }
            option.hidden = option.getAttribute('data-category-id') !== selectedCategoryId;
        });

        if (productSel.selectedOptions.length && productSel.selectedOptions[0].hidden) {
            productSel.value = '';
        }
    }

    function calculateGrandTotal() {
        let grand = 0;
        document.querySelectorAll('.batch-item').forEach(block => {
            const qty = parseFloat(block.querySelector('.qty-roll').value || 0);
            const length = parseFloat(block.querySelector('.length-meter').value || 0);
            const total = qty * length;
            block.querySelector('.total-meter-preview').value = total.toLocaleString('id-ID') + ' meter';
            grand += total;
        });
        grandTotalMeter.textContent = grand.toLocaleString('id-ID') + ' meter';
    }

    function currentProductName(block) {
        const building = block.querySelector('.building-product-wrapper');
        const select = building.classList.contains('d-none')
            ? block.querySelector('.product-id')
            : block.querySelector('.product-building-id');
        const opt = select.options[select.selectedIndex];
        return opt ? (opt.getAttribute('data-name') || '') : '';
    }

    function renderRollCodeRows(block, values) {
        const wrapper = block.querySelector('.roll-code-wrapper');
        const index = block.getAttribute('data-index');
        wrapper.innerHTML = '';
        values.forEach((value, i) => {
            const tr = document.createElement('tr');
            tr.className = 'roll-code-item';
            tr.innerHTML = `
                <td class="text-center text-muted fw-semibold">${i + 1}</td>
                <td>
                    <input type="text"
                           name="batches[${index}][roll_codes][]"
                           class="form-control roll-code-input"
                           value="${value}"
                           placeholder="Contoh: AP50-001"
                           required>
                </td>
            `;
            wrapper.appendChild(tr);
        });
    }

    // Kode yang sudah dipakai di varian LAIN pada form ini — supaya saran kode
    // tidak saling bentrok antar varian sebelum sempat divalidasi ke server.
    function codesUsedInOtherBatches(exceptBlock) {
        const used = new Set();
        document.querySelectorAll('.batch-item').forEach(block => {
            if (block === exceptBlock) return;
            block.querySelectorAll('.roll-code-input').forEach(inp => {
                if (inp.value) used.add(inp.value.toUpperCase());
            });
        });
        return used;
    }

    async function refreshRollCodes(block) {
        const qty = Math.max(1, parseInt(block.querySelector('.qty-roll').value || 1));
        const prefix = currentProductName(block);

        if (!prefix) {
            renderRollCodeRows(block, Array(qty).fill(''));
            return;
        }

        try {
            // minta ekstra supaya ada cadangan kalau beberapa saran bentrok dengan varian lain di form ini
            const url = `${suggestUrl}?prefix=${encodeURIComponent(prefix)}&qty=${qty + 10}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('suggest-roll-codes failed');
            const data = await res.json();

            const usedElsewhere = codesUsedInOtherBatches(block);
            const codes = data.codes.filter(c => !usedElsewhere.has(c.toUpperCase())).slice(0, qty);

            renderRollCodeRows(block, codes.length === qty ? codes : Array(qty).fill(''));
        } catch (e) {
            renderRollCodeRows(block, Array(qty).fill(''));
        }
    }

    function scheduleRefresh(block) {
        clearTimeout(block._refreshTimer);
        block._refreshTimer = setTimeout(() => refreshRollCodes(block), 350);
    }

    function wireBatch(block) {
        toggleProductSource(block);
        filterProductOptions(block);
        filterBuildingProductOptions(block);

        block.querySelector('.category-product-id').addEventListener('change', () => filterProductOptions(block));
        block.querySelector('.category-product-building-id').addEventListener('change', () => filterBuildingProductOptions(block));
        block.querySelector('.product-id').addEventListener('change', () => scheduleRefresh(block));
        block.querySelector('.product-building-id').addEventListener('change', () => scheduleRefresh(block));
        block.querySelector('.qty-roll').addEventListener('input', () => { calculateGrandTotal(); scheduleRefresh(block); });
        block.querySelector('.length-meter').addEventListener('input', calculateGrandTotal);
        block.querySelector('.regenerate-codes-btn').addEventListener('click', () => refreshRollCodes(block));

        block.querySelector('.remove-batch').addEventListener('click', () => {
            if (document.querySelectorAll('.batch-item').length <= 1) {
                alert('Minimal harus ada 1 varian.');
                return;
            }
            block.remove();
            renumberBatches();
            calculateGrandTotal();
        });
    }

    function renumberBatches() {
        document.querySelectorAll('.batch-item').forEach((block, i) => {
            block.querySelector('.batch-number').textContent = i + 1;
        });
    }

    function addBatch(oldData) {
        const html = batchTemplate.replaceAll('__INDEX__', batchIndex);
        const div = document.createElement('div');
        div.innerHTML = html;
        const block = div.firstElementChild;
        block.setAttribute('data-index', batchIndex);
        batchWrapper.appendChild(block);

        if (oldData) {
            if (oldData.category_product_id) block.querySelector('.category-product-id').value = oldData.category_product_id;
            if (oldData.product_id) { filterProductOptions(block); block.querySelector('.product-id').value = oldData.product_id; }
            if (oldData.category_product_building_id) block.querySelector('.category-product-building-id').value = oldData.category_product_building_id;
            if (oldData.product_building_id) { filterBuildingProductOptions(block); block.querySelector('.product-building-id').value = oldData.product_building_id; }
            if (oldData.width_meter) block.querySelector('.width-meter').value = oldData.width_meter;
            if (oldData.length_meter) block.querySelector('.length-meter').value = oldData.length_meter;
            if (oldData.qty_roll) block.querySelector('.qty-roll').value = oldData.qty_roll;
        }

        wireBatch(block);
        renumberBatches();

        if (oldData && Array.isArray(oldData.roll_codes) && oldData.roll_codes.length) {
            renderRollCodeRows(block, oldData.roll_codes);
        } else {
            refreshRollCodes(block);
        }

        batchIndex++;
        return block;
    }

    usageFor.addEventListener('change', toggleAllProductSources);
    addBatchBtn.addEventListener('click', () => addBatch());

    const oldBatches = @json(old('batches', []));
    if (oldBatches.length) {
        oldBatches.forEach(b => addBatch(b));
        toggleAllProductSources();
    } else {
        addBatch();
    }

    calculateGrandTotal();
});
</script>
@endpush

@endsection
