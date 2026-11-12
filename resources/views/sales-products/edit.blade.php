@extends('dashboard.index')

@section('title', 'Edit Produk Sales')
@section('route-breadcrumb', 'sales-products.edit')
@section('breadcrumb', 'Edit Produk Sales')

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

<x-ui.card style="max-width: 520px;">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Edit Produk</div>
            <x-ui.button :href="route('sales-products.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form method="POST" action="{{ route('sales-products.update', $product) }}">
        @csrf
        @method('PUT')

        <x-ui.form-field label="Nama Produk" name="name" :required="true" :error="$errors->first('name')">
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $product->name) }}" required>
        </x-ui.form-field>

        <div class="row mt-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <x-ui.form-field label="Kategori" name="category" :required="true" :error="$errors->first('category')">
                    <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                           value="{{ old('category', $product->category) }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Harga (Rp)" name="price" :required="true" :error="$errors->first('price')">
                    <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $product->price) }}" min="0" required>
                </x-ui.form-field>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
            <x-ui.button :href="route('sales-products.index')" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
