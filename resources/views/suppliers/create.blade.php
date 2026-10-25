@extends('dashboard.index')

@section('title', 'Tambah Supplier')
@section('route-breadcrumb', 'suppliers.index')
@section('breadcrumb', 'Tambah Supplier')

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

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Tambah Supplier</div>
            <x-ui.button :href="route('suppliers.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Nama Supplier" name="name" :required="true">
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Nama supplier"
                           required>
                </x-ui.form-field>
            </div>

            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Kota" name="city">
                    <input type="text"
                           name="city"
                           class="form-control @error('city') is-invalid @enderror"
                           value="{{ old('city') }}"
                           placeholder="Kota">
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Nomor Telepon" name="phone_number">
                    <input type="text"
                           name="phone_number"
                           class="form-control @error('phone_number') is-invalid @enderror"
                           value="{{ old('phone_number') }}"
                           placeholder="08xx-xxxx-xxxx">
                </x-ui.form-field>
            </div>

            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Email" name="email">
                    <input type="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="email@supplier.com">
                </x-ui.form-field>
            </div>
        </div>

        <div class="mb-4">
            <x-ui.form-field label="Alamat" name="address">
                <textarea name="address"
                          class="form-control @error('address') is-invalid @enderror"
                          rows="3"
                          placeholder="Alamat lengkap...">{{ old('address') }}</textarea>
            </x-ui.form-field>
        </div>

        <div class="d-flex gap-2">
            <x-ui.button type="submit" variant="primary">Simpan Supplier</x-ui.button>
            <x-ui.button :href="route('suppliers.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
