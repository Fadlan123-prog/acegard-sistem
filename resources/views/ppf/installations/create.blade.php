@extends('layout.index')

@section('title', 'Tambah Pemasangan PPF')
@section('route-breadcrumb', route('ppf-installations.index'))
@section('breadcrumb', 'Pemasangan PPF')

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-10">
        <x-ui.card>
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">Tambah Data Pemasangan PPF</div>
                    <x-ui.button :href="route('ppf-installations.index')" variant="outline" size="sm">
                        <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
                    </x-ui.button>
                </div>
            @endslot

            @php $installation = new \App\Models\CustomerPpfInstallation(); @endphp

            <form method="POST" action="{{ route('ppf-installations.store') }}" enctype="multipart/form-data">
                @csrf

                @include('ppf.installations._form')

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <x-ui.button :href="route('ppf-installations.index')" variant="secondary" size="sm">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="sm">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Pemasangan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
