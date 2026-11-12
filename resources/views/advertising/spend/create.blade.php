@extends('layout.index')

@section('title', 'Tambah Spend Iklan')
@section('route-breadcrumb', route('advertising.spend.index'))
@section('breadcrumb', 'Tambah Spend Iklan')

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
            <div class="ui-card-title">Tambah Spend Iklan</div>
            <x-ui.button :href="route('advertising.spend.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('advertising.spend.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Channel" name="channel" :required="true">
                    <select name="channel" class="form-select @error('channel') is-invalid @enderror" required>
                        <option value="">Pilih channel</option>
                        @foreach (\App\Models\AdSpendEntry::CHANNELS as $key => $label)
                            <option value="{{ $key }}" @selected(old('channel') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>

            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Tanggal" name="spend_date" :required="true">
                    <input type="date" name="spend_date" class="form-control @error('spend_date') is-invalid @enderror"
                           value="{{ old('spend_date', now()->toDateString()) }}" required>
                </x-ui.form-field>
            </div>

            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Jumlah (Rp)" name="amount" :required="true">
                    <input type="number" step="0.01" min="0" name="amount"
                           class="form-control @error('amount') is-invalid @enderror"
                           value="{{ old('amount') }}" placeholder="0" required>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <x-ui.form-field label="Catatan" name="notes" :optional="true">
                    <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                           value="{{ old('notes') }}" placeholder="Campaign, iklan, dll (opsional)">
                </x-ui.form-field>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-2">
            <x-ui.button :href="route('advertising.spend.index')" variant="secondary">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
