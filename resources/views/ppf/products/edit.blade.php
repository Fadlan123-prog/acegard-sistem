@extends('layout.index')

@section('title', 'Edit Produk PPF')
@section('route-breadcrumb', route('ppf-products.index'))
@section('breadcrumb', 'Produk PPF')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <x-ui.card>
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">Edit Produk: {{ $product->brand }} {{ $product->name }}</div>
                    <x-ui.button href="{{ route('ppf-products.index') }}" variant="outline" size="sm">
                        <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
                    </x-ui.button>
                </div>
            @endslot

            @include('partials.form-errors')

            <form method="POST" action="{{ route('ppf-products.update', $product) }}">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-ui.form-field label="Nama Produk *" name="name">
                            <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-6">
                        <x-ui.form-field label="Brand *" name="brand">
                            <input type="text" name="brand" class="form-control form-control-sm @error('brand') is-invalid @enderror"
                                   value="{{ old('brand', $product->brand) }}" required>
                            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-6">
                        <x-ui.form-field label="Seri / Tipe" name="series">
                            <input type="text" name="series" class="form-control form-control-sm @error('series') is-invalid @enderror"
                                   value="{{ old('series', $product->series) }}">
                            @error('series')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-3">
                        <x-ui.form-field label="Ketebalan (μm)" name="thickness_microns">
                            <input type="number" name="thickness_microns" class="form-control form-control-sm @error('thickness_microns') is-invalid @enderror"
                                   value="{{ old('thickness_microns', $product->thickness_microns) }}" min="0">
                            @error('thickness_microns')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-3">
                        <x-ui.form-field label="Finish" name="finish">
                            <select name="finish" class="form-select form-select-sm @error('finish') is-invalid @enderror">
                                <option value="">— Pilih —</option>
                                @foreach (['glossy', 'matte', 'satin', 'ceramic'] as $f)
                                    <option value="{{ $f }}" {{ old('finish', $product->finish) === $f ? 'selected' : '' }}>
                                        {{ ucfirst($f) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('finish')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-4">
                        <x-ui.form-field label="Garansi Default (tahun) *" name="default_warranty_years">
                            <input type="number" name="default_warranty_years" class="form-control form-control-sm @error('default_warranty_years') is-invalid @enderror"
                                   value="{{ old('default_warranty_years', $product->default_warranty_years) }}" min="1" max="20" required>
                            @error('default_warranty_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Coverage Default" name="default_coverage">
                            <textarea name="default_coverage" rows="2" class="form-control form-control-sm @error('default_coverage') is-invalid @enderror">{{ old('default_coverage', $product->default_coverage) }}</textarea>
                            @error('default_coverage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Pengecualian Garansi Default" name="default_exclusions">
                            <textarea name="default_exclusions" rows="2" class="form-control form-control-sm @error('default_exclusions') is-invalid @enderror">{{ old('default_exclusions', $product->default_exclusions) }}</textarea>
                            @error('default_exclusions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <x-ui.button href="{{ route('ppf-products.index') }}" variant="outline" size="sm">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="sm">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Perubahan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
