@extends('dashboard.index')

@section('title', 'Transaksi Baru')
@section('route-breadcrumb', 'sales-transactions.create')
@section('breadcrumb', 'Transaksi Baru')

@section('content')

@if($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <strong>Terjadi kesalahan.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif

<x-ui.card style="max-width: 640px;">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Transaksi Baru</div>
            <x-ui.button :href="route('sales-transactions.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form method="POST" action="{{ route('sales-transactions.store') }}">
        @csrf

        <x-ui.form-field label="Produk" name="sales_product_id" :required="true" :error="$errors->first('sales_product_id')">
            <select name="sales_product_id" class="form-select @error('sales_product_id') is-invalid @enderror" required>
                <option value="">— Pilih produk —</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(old('sales_product_id') == $product->id)>
                        {{ $product->name }} — Rp {{ number_format($product->price, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </x-ui.form-field>

        <div class="row mt-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <x-ui.form-field label="Nama Customer" name="customer_name" :required="true" :error="$errors->first('customer_name')">
                    <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror"
                           value="{{ old('customer_name') }}" placeholder="Nama lengkap customer" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="No. HP Customer" name="customer_phone" :error="$errors->first('customer_phone')">
                    <input type="text" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror"
                           value="{{ old('customer_phone') }}" placeholder="08xx-xxxx-xxxx">
                </x-ui.form-field>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <x-ui.form-field label="Diskon (Rp)" name="discount" :error="$errors->first('discount')">
                    <input type="number" name="discount" class="form-control @error('discount') is-invalid @enderror"
                           value="{{ old('discount', 0) }}" min="0" placeholder="0">
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="DP / Pembayaran Awal (Rp)" name="downpayment" :error="$errors->first('downpayment')">
                    <input type="number" name="downpayment" class="form-control @error('downpayment') is-invalid @enderror"
                           value="{{ old('downpayment', 0) }}" min="0" placeholder="0">
                    <div class="form-text">Isi 0 kalau belum ada pembayaran, atau isi penuh (harga − diskon) kalau langsung lunas.</div>
                </x-ui.form-field>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary">Simpan Transaksi</x-ui.button>
            <x-ui.button :href="route('sales-transactions.index')" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
