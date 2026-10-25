@extends('dashboard.index')

@section('title', 'Tambah Dealer')
@section('route-breadcrumb', 'dealers.index')
@section('breadcrumb', 'Tambah Dealer')

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
            <div class="ui-card-title">Tambah Dealer</div>
            <x-ui.button :href="route('dealers.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('dealers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Nama Dealer" name="name" :required="true">
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Contoh: ACEGARD - Tangerang Kota"
                           required>
                </x-ui.form-field>
            </div>

            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Tipe" name="type" :required="true">
                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="dealer" {{ old('type') === 'dealer' ? 'selected' : '' }}>Dealer</option>
                        <option value="cabang_utama" {{ old('type') === 'cabang_utama' ? 'selected' : '' }}>Cabang Utama</option>
                    </select>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Lokasi" name="location">
                    <input type="text"
                           name="location"
                           class="form-control @error('location') is-invalid @enderror"
                           value="{{ old('location') }}"
                           placeholder="Contoh: Lokasi Spesifik / area">
                </x-ui.form-field>
            </div>

            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Foto" name="image">
                    <input type="file"
                           name="image"
                           accept="image/*"
                           class="form-control @error('image') is-invalid @enderror">
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="No. WhatsApp Admin" name="admin_whatsapp">
                    <input type="text"
                           name="admin_whatsapp"
                           class="form-control @error('admin_whatsapp') is-invalid @enderror"
                           value="{{ old('admin_whatsapp') }}"
                           placeholder="08xx-xxxx-xxxx">
                </x-ui.form-field>
            </div>

            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Link Google Maps" name="maps_url">
                    <input type="url"
                           name="maps_url"
                           class="form-control @error('maps_url') is-invalid @enderror"
                           value="{{ old('maps_url') }}"
                           placeholder="https://maps.google.com/...">
                </x-ui.form-field>
            </div>

            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Link Instagram" name="instagram_url">
                    <input type="url"
                           name="instagram_url"
                           class="form-control @error('instagram_url') is-invalid @enderror"
                           value="{{ old('instagram_url') }}"
                           placeholder="https://instagram.com/...">
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <x-ui.form-field label="Urutan Tampil" name="sort_order">
                    <input type="number"
                           name="sort_order"
                           class="form-control @error('sort_order') is-invalid @enderror"
                           value="{{ old('sort_order', 0) }}">
                </x-ui.form-field>
            </div>

            <div class="col-md-4 mb-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Tampilkan di website</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <x-ui.button type="submit" variant="primary">Simpan Dealer</x-ui.button>
            <x-ui.button :href="route('dealers.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
