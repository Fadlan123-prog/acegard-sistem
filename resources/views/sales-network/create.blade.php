@extends('dashboard.index')

@section('title', 'Rekrut Sales Baru')
@section('route-breadcrumb', 'sales-network.create')
@section('breadcrumb', 'Rekrut Sales Baru')

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

<x-ui.card style="max-width: 560px;">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Data Calon Sales</div>
            <x-ui.button :href="route('sales-network.my-network')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form method="POST" action="{{ route('sales-network.store') }}">
        @csrf

        <x-ui.form-field label="Nama Lengkap" name="name" :required="true" :error="$errors->first('name')">
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="Nama lengkap calon sales" required>
        </x-ui.form-field>

        <x-ui.form-field label="Email" name="email" :required="true" :error="$errors->first('email')" class="mt-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="email@contoh.com" required>
            <div class="form-text">Dipakai untuk login — pastikan aktif, akun belum bisa dipakai sampai disetujui admin.</div>
        </x-ui.form-field>

        <x-ui.form-field label="No. HP" name="phone" :error="$errors->first('phone')" class="mt-3">
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx">
        </x-ui.form-field>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary">Daftarkan</x-ui.button>
            <x-ui.button :href="route('sales-network.my-network')" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
