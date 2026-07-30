@extends('dashboard.index')

@section('title', 'Edit Purchasing')
@section('route-breadcrumb', 'purchases.index')
@section('breadcrumb', 'Edit Purchasing')

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

@if (session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div>
            <div class="ui-card-title">Edit Purchasing</div>
            <small class="text-muted">{{ $purchase->purchase_number }}</small>
        </div>
        <div class="d-flex gap-2">
            <x-ui.button href="{{ route('purchases.show', $purchase->id) }}" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    @php
        $existingItems = old('items') ? null : $purchase->items;
        $oldItems = old('items', $purchase->items->map(fn($i) => [
            'product_id' => $i->product_id,
            'item_name'  => $i->item_name,
            'qty'        => $i->qty,
            'unit'       => $i->unit,
            'unit_price' => $i->unit_price,
        ])->toArray());
    @endphp

    <form action="{{ route('purchases.update', $purchase->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Header --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">No Purchasing</label>
                <input type="text"
                       name="purchase_number"
                       class="form-control @error('purchase_number') is-invalid @enderror"
                       value="{{ old('purchase_number', $purchase->purchase_number) }}"
                       placeholder="Kosongkan untuk auto generate">

                @error('purchase_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Purchasing <span class="text-danger">*</span></label>
                <input type="date"
                       name="purchase_date"
                       class="form-control @error('purchase_date') is-invalid @enderror"
                       value="{{ old('purchase_date', $purchase->purchase_date?->format('Y-m-d')) }}"
                       required>

                @error('purchase_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status"
                        class="form-select @error('status') is-invalid @enderror"
                        required>
                    <option value="confirmed"
                        {{ old('status', $purchase->status) === 'confirmed' ? 'selected' : '' }}>
                        Confirmed
                    </option>
                    <option value="draft"
                        {{ old('status', $purchase->status) === 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>
                    <option value="cancelled"
                        {{ old('status', $purchase->status) === 'cancelled' ? 'selected' : '' }}>
                        Cancelled
                    </option>
                </select>

                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Cabang & Supplier --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Cabang <span class="text-danger">*</span></label>
                <select name="branch_id"
                        class="form-select @error('branch_id') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Cabang --</option>

                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ old('branch_id', $purchase->branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} - {{ $branch->city ?? '-' }}
                        </option>
                    @endforeach
                </select>

                @error('branch_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id"
                        class="form-select @error('supplier_id') is-invalid @enderror">
                    <option value="">-- Pilih Supplier --</option>

                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>

                @error('supplier_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Notes --}}
        <div class="mb-4">
            <label class="form-label">Catatan</label>
            <textarea name="notes"
                      class="form-control @error('notes') is-invalid @enderror"
                      rows="3"
                      placeholder="Catatan tambahan...">{{ old('notes', $purchase->notes) }}</textarea>

            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Items --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Detail Barang</h5>

            <x-ui.button type="button" variant="primary" size="sm" id="addItemBtn">+ Tambah Barang</x-ui.button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:220px;">Pilih Produk</th>
                        <th>Nama Barang <span class="text-danger">*</span></th>
                        <th style="width:120px;">Qty <span class="text-danger">*</span></th>
                        <th style="width:120px;">Satuan</th>
                        <th style="width:180px;">Harga Satuan <span class="text-danger">*</span></th>
                        <th style="width:180px;">Total</th>
                        <th style="width:70px;">Aksi</th>
                    </tr>
                </thead>

                <tbody id="itemsBody">
                    @foreach($oldItems as $index => $item)
                        <tr class="item-row">
                            <td>
                                <select name="items[{{ $index }}][product_id]"
                                        class="form-select product-select">
                                    <option value="">Manual</option>

                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-name="{{ $product->name }}"
                                            {{ ($item['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text"
                                       name="items[{{ $index }}][item_name]"
                                       class="form-control item-name"
                                       value="{{ $item['item_name'] ?? '' }}"
                                       placeholder="Nama barang"
                                       required>
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $index }}][qty]"
                                       class="form-control qty-input"
                                       value="{{ $item['qty'] ?? 1 }}"
                                       min="0.01"
                                       step="0.01"
                                       required>
                            </td>

                            <td>
                                <input type="text"
                                       name="items[{{ $index }}][unit]"
                                       class="form-control"
                                       value="{{ $item['unit'] ?? '' }}"
                                       placeholder="Roll/Pcs/Meter">
                            </td>

                            <td>
                                <input type="number"
                                       name="items[{{ $index }}][unit_price]"
                                       class="form-control price-input"
                                       value="{{ $item['unit_price'] ?? 0 }}"
                                       min="0"
                                       step="1"
                                       required>
                            </td>

                            <td>
                                <input type="text"
                                       class="form-control row-total"
                                       value="0"
                                       readonly>
                            </td>

                            <td>
                                <x-ui.button type="button" variant="danger" size="sm" class="remove-item">X</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Grand Total</th>
                        <th>
                            <input type="text" id="grandTotal" class="form-control fw-bold" readonly>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Buttons --}}
        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary">Simpan Perubahan</x-ui.button>
            <x-ui.button href="{{ route('purchases.show', $purchase->id) }}" variant="outline">Batal</x-ui.button>
        </div>
    </form>

</x-ui.card>

{{-- Template Row --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[__INDEX__][product_id]" class="form-select product-select">
                <option value="">Manual</option>

                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-name="{{ $product->name }}">
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input type="text"
                   name="items[__INDEX__][item_name]"
                   class="form-control item-name"
                   placeholder="Nama barang"
                   required>
        </td>

        <td>
            <input type="number"
                   name="items[__INDEX__][qty]"
                   class="form-control qty-input"
                   value="1"
                   min="0.01"
                   step="0.01"
                   required>
        </td>

        <td>
            <input type="text"
                   name="items[__INDEX__][unit]"
                   class="form-control"
                   placeholder="Roll/Pcs/Meter">
        </td>

        <td>
            <input type="number"
                   name="items[__INDEX__][unit_price]"
                   class="form-control price-input"
                   value="0"
                   min="0"
                   step="1"
                   required>
        </td>

        <td>
            <input type="text" class="form-control row-total" value="0" readonly>
        </td>

        <td>
            <x-ui.button type="button" variant="danger" size="sm" class="remove-item">X</x-ui.button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemsBody     = document.getElementById('itemsBody');
        const addItemBtn    = document.getElementById('addItemBtn');
        const template      = document.getElementById('itemRowTemplate').innerHTML;
        const grandTotalInput = document.getElementById('grandTotal');

        let itemIndex = document.querySelectorAll('.item-row').length;

        function formatRupiah(number) {
            return 'Rp' + new Intl.NumberFormat('id-ID').format(number || 0);
        }

        function recalculate() {
            let grandTotal = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const qty   = parseFloat(row.querySelector('.qty-input')?.value || 0);
                const price = parseFloat(row.querySelector('.price-input')?.value || 0);
                const total = qty * price;

                row.querySelector('.row-total').value = formatRupiah(total);
                grandTotal += total;
            });

            grandTotalInput.value = formatRupiah(grandTotal);
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

        addItemBtn.addEventListener('click', function () {
            const html = template.replaceAll('__INDEX__', itemIndex);
            itemsBody.insertAdjacentHTML('beforeend', html);
            itemIndex++;
            recalculate();
        });

        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('qty-input') ||
                e.target.classList.contains('price-input')) {
                recalculate();
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('product-select')) {
                const selected    = e.target.options[e.target.selectedIndex];
                const productName = selected.getAttribute('data-name');
                const row         = e.target.closest('tr');

                if (productName) {
                    row.querySelector('.item-name').value = productName;
                }
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

        recalculate();
    });
</script>

@endsection
