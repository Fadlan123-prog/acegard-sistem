@extends('layout.index')

@section('title', 'Edit Pemasangan PPF')
@section('route-breadcrumb', route('ppf-installations.index'))
@section('breadcrumb', 'Pemasangan PPF')

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-10">
        <x-ui.card>
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="ui-card-title mb-1">Edit Pemasangan PPF</div>
                        <div class="text-muted" style="font-size:12px">
                            {{ $installation->customerPpf?->name }} ·
                            <span class="font-monospace">{{ $installation->warranty_code }}</span>
                        </div>
                    </div>
                    <x-ui.button :href="route('ppf-installations.index')" variant="outline" size="sm">
                        <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
                    </x-ui.button>
                </div>
            @endslot

            <form method="POST" action="{{ route('ppf-installations.update', $installation) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                @include('ppf.installations._form')

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <x-ui.button :href="route('ppf-installations.index')" variant="secondary" size="sm">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="sm">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Perubahan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
