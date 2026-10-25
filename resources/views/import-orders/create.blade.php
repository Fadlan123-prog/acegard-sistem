@extends('dashboard.index')

@section('title', 'Buat Import Order')
@section('route-breadcrumb', 'import-orders.create')
@section('breadcrumb', 'Buat Import Order')

@section('content')

<form method="POST" action="{{ route('import-orders.store') }}" enctype="multipart/form-data">
    @csrf

    <x-ui.card class="mb-3">
        <h6 class="mb-3">Data Order</h6>
        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Supplier" name="international_partner_id" :required="true" :error="$errors->first('international_partner_id')">
                    <select name="international_partner_id" class="form-select @error('international_partner_id') is-invalid @enderror" required>
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" data-currency="{{ $supplier->default_currency }}" @selected(old('international_partner_id') == $supplier->id)>
                                {{ $supplier->name }} ({{ $supplier->country }})
                            </option>
                        @endforeach
                    </select>
                    @if($suppliers->isEmpty())
                        <div class="form-text text-danger">Belum ada supplier — <a href="{{ route('international-partners.create') }}">tambahkan dulu</a>.</div>
                    @endif
                </x-ui.form-field>
            </div>
            <div class="col-md-3">
                <x-ui.form-field label="Mata Uang" name="currency" :required="true" :error="$errors->first('currency')">
                    <input type="text" name="currency" id="currency" maxlength="3" value="{{ old('currency', 'USD') }}" class="form-control text-uppercase @error('currency') is-invalid @enderror" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3">
                <x-ui.form-field label="Kurs ke IDR" name="exchange_rate" :required="true" :error="$errors->first('exchange_rate')">
                    <input type="number" step="0.0001" name="exchange_rate" value="{{ old('exchange_rate') }}" class="form-control @error('exchange_rate') is-invalid @enderror" required>
                </x-ui.form-field>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.form-field label="Tanggal Order" name="order_date" :required="true" :error="$errors->first('order_date')">
                    <input type="date" name="order_date" value="{{ old('order_date', now()->toDateString()) }}" class="form-control @error('order_date') is-invalid @enderror" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-4">
                <x-ui.form-field label="Estimasi Tiba" name="estimated_arrival" :error="$errors->first('estimated_arrival')">
                    <input type="date" name="estimated_arrival" value="{{ old('estimated_arrival') }}" class="form-control">
                </x-ui.form-field>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Item Bahan Baku</h6>
            <button type="button" class="ui-btn ui-btn-sm ui-btn-secondary" id="addItemBtn">
                <iconify-icon icon="mdi:plus"></iconify-icon> Tambah Item
            </button>
        </div>
        <div id="itemRows"></div>
        @error('items')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </x-ui.card>

    <x-ui.card class="mb-3">
        <h6 class="mb-3">Biaya Tambahan (Rupiah)</h6>
        <div class="row">
            <div class="col-md-3">
                <x-ui.form-field label="Bea Masuk" name="duty_cost">
                    <input type="number" name="duty_cost" value="{{ old('duty_cost', 0) }}" class="form-control">
                </x-ui.form-field>
            </div>
            <div class="col-md-3">
                <x-ui.form-field label="PPN Impor" name="tax_cost">
                    <input type="number" name="tax_cost" value="{{ old('tax_cost', 0) }}" class="form-control">
                </x-ui.form-field>
            </div>
            <div class="col-md-3">
                <x-ui.form-field label="Freight" name="freight_cost">
                    <input type="number" name="freight_cost" value="{{ old('freight_cost', 0) }}" class="form-control">
                </x-ui.form-field>
            </div>
            <div class="col-md-3">
                <x-ui.form-field label="Asuransi" name="insurance_cost">
                    <input type="number" name="insurance_cost" value="{{ old('insurance_cost', 0) }}" class="form-control">
                </x-ui.form-field>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="mb-3">
        <h6 class="mb-3">Dokumen (opsional)</h6>
        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Invoice" name="documents[invoice]">
                    <input type="file" name="documents[invoice]" class="form-control">
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Packing List" name="documents[packing_list]">
                    <input type="file" name="documents[packing_list]" class="form-control">
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Bill of Lading" name="documents[bill_of_lading]">
                    <input type="file" name="documents[bill_of_lading]" class="form-control">
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Certificate of Origin" name="documents[certificate_of_origin]">
                    <input type="file" name="documents[certificate_of_origin]" class="form-control">
                </x-ui.form-field>
            </div>
        </div>
    </x-ui.card>

    <div class="d-flex gap-2">
        <x-ui.button type="submit" variant="primary">Simpan Import Order</x-ui.button>
        <x-ui.button :href="route('import-orders.index')" variant="secondary">Batal</x-ui.button>
    </div>
</form>

@push('page-scripts')
<script>
(function(){
    var container = document.getElementById('itemRows');
    var addBtn = document.getElementById('addItemBtn');
    var idx = 0;

    function addRow(values) {
        values = values || {};
        var row = document.createElement('div');
        row.className = 'row g-2 align-items-end mb-2 item-row';
        row.innerHTML =
            '<div class="col-md-4"><label class="form-label small mb-1">Nama Barang</label>' +
            '<input type="text" name="items[' + idx + '][item_name]" class="form-control form-control-sm" required></div>' +
            '<div class="col-md-2"><label class="form-label small mb-1">Qty</label>' +
            '<input type="number" step="0.01" name="items[' + idx + '][quantity]" class="form-control form-control-sm" required></div>' +
            '<div class="col-md-2"><label class="form-label small mb-1">Satuan</label>' +
            '<input type="text" name="items[' + idx + '][unit]" class="form-control form-control-sm" placeholder="roll, kg, dst" required></div>' +
            '<div class="col-md-3"><label class="form-label small mb-1">Harga Satuan (mata uang asing)</label>' +
            '<input type="number" name="items[' + idx + '][unit_price_foreign]" class="form-control form-control-sm" required></div>' +
            '<div class="col-md-1"><button type="button" class="ui-btn ui-btn-sm ui-btn-danger remove-item"><iconify-icon icon="mdi:trash-can-outline"></iconify-icon></button></div>';
        container.appendChild(row);
        row.querySelector('.remove-item').addEventListener('click', function(){ row.remove(); });
        idx++;
    }

    addBtn.addEventListener('click', function(){ addRow(); });
    addRow(); // start with one row
})();
</script>
@endpush

@endsection
