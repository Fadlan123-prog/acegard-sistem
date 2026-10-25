@extends('dashboard.index')

@section('title', 'Edit Mitra')
@section('route-breadcrumb', 'mitras.index')
@section('breadcrumb', 'Edit Mitra')

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
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="ui-card-title">Edit Mitra</div>
            <x-ui.button :href="route('mitras.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('mitras.update', $mitra->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Nama Mitra" name="name" :required="true">
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $mitra->name) }}"
                           required>
                </x-ui.form-field>
            </div>

            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Tipe" name="type" :required="true">
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="mitra" @selected(old('type', $mitra->type) == 'mitra')>Mitra</option>
                        <option value="non_franchise" @selected(old('type', $mitra->type) == 'non_franchise')>Non-Franchise</option>
                    </select>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Nama PIC" name="pic_name">
                    <input type="text"
                           name="pic_name"
                           class="form-control @error('pic_name') is-invalid @enderror"
                           value="{{ old('pic_name', $mitra->pic_name) }}">
                </x-ui.form-field>
            </div>

            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Status" name="status" :required="true">
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" @selected(old('status', $mitra->status) == 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status', $mitra->status) == 'inactive')>Nonaktif</option>
                    </select>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Nomor Telepon" name="phone">
                    <input type="text"
                           name="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $mitra->phone) }}">
                </x-ui.form-field>
            </div>

            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Email" name="email">
                    <input type="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $mitra->email) }}">
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Kota" name="city">
                    <input type="text"
                           name="city"
                           class="form-control @error('city') is-invalid @enderror"
                           value="{{ old('city', $mitra->city) }}">
                </x-ui.form-field>
            </div>
        </div>

        <div class="mb-3">
            <x-ui.form-field label="Alamat" name="address">
                <textarea name="address"
                          class="form-control @error('address') is-invalid @enderror"
                          rows="3">{{ old('address', $mitra->address) }}</textarea>
            </x-ui.form-field>
        </div>

        <div class="mb-4">
            <x-ui.form-field label="Catatan" name="notes">
                <textarea name="notes"
                          class="form-control @error('notes') is-invalid @enderror"
                          rows="2">{{ old('notes', $mitra->notes) }}</textarea>
            </x-ui.form-field>
        </div>

        <div class="d-flex gap-2">
            <x-ui.button type="submit" variant="primary">Simpan Perubahan</x-ui.button>
            <x-ui.button :href="route('mitras.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
