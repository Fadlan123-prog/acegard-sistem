@extends('dashboard.index')

@section('title', 'Tambah Invoice Mitra')
@section('route-breadcrumb', 'mitra-invoices.index')
@section('breadcrumb', 'Tambah Invoice Mitra')

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
        <div class="ui-card-title">Form Invoice Penjualan Roll ke Mitra / Non-Franchise</div>
        <x-ui.button :href="route('mitra-invoices.index')" variant="secondary" size="sm">Kembali</x-ui.button>
    @endslot

    <form action="{{ route('mitra-invoices.store') }}" method="POST" id="invoiceForm">
        @csrf

        {{-- Header --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Mitra / Non-Franchise" name="mitra_id" :required="true">
                    <select name="mitra_id" id="mitraSelect"
                            class="form-select @error('mitra_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Mitra --</option>
                        @foreach($mitras as $m)
                            <option value="{{ $m->id }}" data-type="{{ $m->type }}"
                                {{ old('mitra_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->name }} ({{ $m->type_label }})
                            </option>
                        @endforeach
                    </select>
                    @error('mitra_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-ui.form-field>
            </div>

            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Tanggal Invoice" name="invoice_date" :required="true">
                    <input type="date" name="invoice_date" class="form-control"
                           value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                </x-ui.form-field>
            </div>

            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Jatuh Tempo" name="due_date">
                    <input type="date" name="due_date" class="form-control"
                           value="{{ old('due_date') }}">
                </x-ui.form-field>
            </div>
        </div>

        <div class="mb-4">
            <x-ui.form-field label="Catatan" name="notes">
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
            </x-ui.form-field>
        </div>

        {{-- Items --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Detail Roll</h5>
            <x-ui.button type="button" variant="primary" size="sm" id="addItemBtn">+ Tambah Roll</x-ui.button>
        </div>

        <x-ui.alert type="info" class="mb-3">
            Pilih mitra dulu — harga roll otomatis menyesuaikan tipe mitra (Mitra / Non-Franchise). Untuk roll
            katalog, pilih juga persentase kegelapannya — stok yang dicek &amp; dikurangi adalah stok roll
            sungguhan di gudang pusat untuk persentase itu, bukan stok per kategori. Pilih "Manual" kalau
            barangnya bukan roll kaca film dari katalog (harga & stok diisi sendiri, tidak mengurangi stok pusat).
        </x-ui.alert>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width: 200px;">Tipe Roll</th>
                        <th style="width: 190px;">Persentase Kegelapan</th>
                        <th>Nama Barang <span class="text-danger">*</span></th>
                        <th style="width: 100px;">Qty <span class="text-danger">*</span></th>
                        <th style="width: 90px;">Satuan</th>
                        <th style="width: 170px;">Harga Satuan <span class="text-danger">*</span></th>
                        <th style="width: 160px;">Total</th>
                        <th style="width: 50px;">Aksi</th>
                    </tr>
                </thead>

                <tbody id="itemsBody"></tbody>

                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Grand Total</th>
                        <th><input type="text" id="grandTotal" class="form-control fw-bold" readonly></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary" id="submitBtn">Simpan Invoice</x-ui.button>
            <x-ui.button :href="route('mitra-invoices.index')" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Template Row --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][mitra_roll_price_id]" class="form-select roll-select">
                <option value="">Manual (bukan roll katalog)</option>
                @foreach($rollPrices as $rp)
                    <option value="{{ $rp->id }}"
                            data-category-id="{{ $rp->category_product_building_id }}"
                            data-name="{{ $rp->category->name ?? 'Tanpa Kategori' }}"
                            data-unit="{{ $rp->unit }}"
                            data-price-mitra="{{ $rp->price_mitra }}"
                            data-price-non-franchise="{{ $rp->price_non_franchise }}">
                        {{ $rp->category->name ?? 'Tanpa Kategori' }}
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <select name="items[__INDEX__][product_building_id]" class="form-select percentage-select" disabled>
                <option value="">-- Pilih tipe roll dulu --</option>
                @foreach($productBuildings as $pb)
                    <option value="{{ $pb->id }}"
                            data-category-id="{{ $pb->category_product_building_id }}"
                            data-name="{{ $pb->name }}">
                        {{ $pb->name }}{{ $pb->description ? ' - '.$pb->description : '' }}
                    </option>
                @endforeach
            </select>
            <small class="stock-info text-muted d-block mt-1"></small>
        </td>

        <td>
            <input type="text" name="items[__INDEX__][item_name]" class="form-control item-name"
                   placeholder="Nama barang" required>
        </td>

        <td>
            <input type="number" name="items[__INDEX__][qty]" class="form-control qty-input"
                   value="1" min="0.01" step="0.01" required>
        </td>

        <td>
            <input type="text" name="items[__INDEX__][unit]" class="form-control unit-input" placeholder="Roll/Pcs">
        </td>

        <td>
            <input type="number" name="items[__INDEX__][unit_price]" class="form-control price-input"
                   value="0" min="0" step="1" required>
        </td>

        <td>
            <input type="text" class="form-control row-total" value="0" readonly>
        </td>

        <td>
            <x-ui.button type="button" variant="danger" size="sm" class="remove-item">X</x-ui.button>
        </td>
    </tr>
</template>

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const itemsBody     = document.getElementById('itemsBody');
    const addItemBtn    = document.getElementById('addItemBtn');
    const template      = document.getElementById('itemRowTemplate').innerHTML;
    const grandTotalEl  = document.getElementById('grandTotal');
    const mitraSelect   = document.getElementById('mitraSelect');
    const stockUrl      = '{{ route('mitra-invoices.available-stock') }}';

    let itemIndex = 0;

    function formatRupiah(n) {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(n || 0);
    }

    function currentMitraType() {
        const opt = mitraSelect.options[mitraSelect.selectedIndex];
        return opt ? (opt.getAttribute('data-type') || 'mitra') : 'mitra';
    }

    // Persentase hanya menampilkan opsi yang satu kategori dengan tipe roll
    // yang sedang dipilih — persis pola filter kategori di Roll Masuk.
    function filterPercentageOptions(row) {
        const rollSelect = row.querySelector('.roll-select');
        const percentageSelect = row.querySelector('.percentage-select');
        const categoryId = rollSelect.value
            ? rollSelect.options[rollSelect.selectedIndex].getAttribute('data-category-id')
            : null;

        if (!categoryId) {
            percentageSelect.disabled = true;
            percentageSelect.value = '';
            row.querySelector('.stock-info').textContent = '';
            return;
        }

        percentageSelect.disabled = false;
        Array.from(percentageSelect.options).forEach(option => {
            if (!option.value) { option.hidden = false; return; }
            option.hidden = option.getAttribute('data-category-id') !== categoryId;
        });

        if (percentageSelect.selectedOptions.length && percentageSelect.selectedOptions[0].hidden) {
            percentageSelect.value = '';
        }
    }

    function applyItemName(row) {
        const rollSelect = row.querySelector('.roll-select');
        const percentageSelect = row.querySelector('.percentage-select');
        const nameInput = row.querySelector('.item-name');

        if (!rollSelect.value) return;

        const rollName = rollSelect.options[rollSelect.selectedIndex].getAttribute('data-name');
        const pctOpt = percentageSelect.options[percentageSelect.selectedIndex];
        const pctName = (percentageSelect.value && pctOpt) ? pctOpt.getAttribute('data-name') : null;

        nameInput.value = pctName ? `${rollName} - ${pctName}` : rollName;
    }

    async function refreshStockInfo(row) {
        const rollSelect = row.querySelector('.roll-select');
        const percentageSelect = row.querySelector('.percentage-select');
        const stockInfo = row.querySelector('.stock-info');
        const categoryId = rollSelect.value
            ? rollSelect.options[rollSelect.selectedIndex].getAttribute('data-category-id')
            : null;

        if (!categoryId || !percentageSelect.value) {
            stockInfo.textContent = '';
            return;
        }

        stockInfo.textContent = 'Mengecek stok…';

        try {
            const url = `${stockUrl}?category_product_building_id=${categoryId}&product_building_id=${percentageSelect.value}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('stock lookup failed');
            const data = await res.json();
            stockInfo.textContent = `Stok pusat: ${data.available} roll`;
            stockInfo.classList.toggle('text-danger', data.available <= 0);
            stockInfo.classList.toggle('text-muted', data.available > 0);
        } catch (e) {
            stockInfo.textContent = 'Gagal cek stok.';
        }
    }

    function applyRollPrice(row) {
        const rollSelect = row.querySelector('.roll-select');
        const opt = rollSelect.options[rollSelect.selectedIndex];
        const priceInput = row.querySelector('.price-input');
        const nameInput  = row.querySelector('.item-name');
        const unitInput  = row.querySelector('.unit-input');

        filterPercentageOptions(row);

        if (!opt.value) {
            // Manual mode — fields become freely editable again.
            priceInput.readOnly = false;
            nameInput.readOnly  = false;
            unitInput.readOnly  = false;
            row.querySelector('.stock-info').textContent = '';
            return;
        }

        const type  = currentMitraType();
        const price = type === 'non_franchise'
            ? parseFloat(opt.getAttribute('data-price-non-franchise'))
            : parseFloat(opt.getAttribute('data-price-mitra'));

        unitInput.value  = opt.getAttribute('data-unit');
        priceInput.value = price;
        applyItemName(row);

        // Catalog price is fixed by mitra type — lock the fields so it's obvious.
        priceInput.readOnly = true;
        nameInput.readOnly  = true;
        unitInput.readOnly  = true;

        recalculate();
    }

    function recalculate() {
        let grand = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.qty-input')?.value || 0);
            const price = parseFloat(row.querySelector('.price-input')?.value || 0);
            const total = qty * price;
            row.querySelector('.row-total').value = formatRupiah(total);
            grand += total;
        });
        grandTotalEl.value = formatRupiah(grand);
    }

    function reindexRows() {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            row.querySelectorAll('input, select').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
                }
            });
        });
        itemIndex = document.querySelectorAll('.item-row').length;
    }

    function addRow() {
        const html = template.replaceAll('__INDEX__', itemIndex);
        itemsBody.insertAdjacentHTML('beforeend', html);
        itemIndex++;
        recalculate();
    }

    addItemBtn.addEventListener('click', addRow);

    mitraSelect.addEventListener('change', function () {
        // Re-price every catalog-linked row for the newly selected mitra's type.
        document.querySelectorAll('.item-row').forEach(applyRollPrice);
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('roll-select')) {
            applyRollPrice(e.target.closest('tr'));
        }
        if (e.target.classList.contains('percentage-select')) {
            const row = e.target.closest('tr');
            applyItemName(row);
            refreshStockInfo(row);
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            recalculate();
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length <= 1) {
                alert('Minimal harus ada 1 barang.');
                return;
            }
            e.target.closest('tr').remove();
            reindexRows();
            recalculate();
        }
    });

    addRow();
});
</script>
@endpush

@endsection
