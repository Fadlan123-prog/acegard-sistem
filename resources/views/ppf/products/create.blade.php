@extends('layout.index')

@section('title', 'Tambah Produk PPF')
@section('route-breadcrumb', route('ppf-products.index'))
@section('breadcrumb', 'Produk PPF')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <x-ui.card>
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">Tambah Produk PPF Baru</div>
                    <x-ui.button href="{{ route('ppf-products.index') }}" variant="outline" size="sm">
                        <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
                    </x-ui.button>
                </div>
            @endslot

            @include('partials.form-errors')

            <form method="POST" action="{{ route('ppf-products.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-ui.form-field label="Nama Produk *" name="name">
                            <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="cth. Supreme PPF" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-6">
                        <x-ui.form-field label="Brand *" name="brand">
                            <input type="text" name="brand" class="form-control form-control-sm @error('brand') is-invalid @enderror"
                                   value="{{ old('brand') }}" placeholder="cth. XPEL, 3M, SunTek" required>
                            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-6">
                        <x-ui.form-field label="Seri / Tipe" name="series">
                            <input type="text" name="series" class="form-control form-control-sm @error('series') is-invalid @enderror"
                                   value="{{ old('series') }}" placeholder="cth. ULTIMATE, PRIME XR">
                            @error('series')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-3">
                        <x-ui.form-field label="Ketebalan (μm)" name="thickness_microns">
                            <input type="number" name="thickness_microns" class="form-control form-control-sm @error('thickness_microns') is-invalid @enderror"
                                   value="{{ old('thickness_microns') }}" placeholder="cth. 150" min="0">
                            @error('thickness_microns')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-3">
                        <x-ui.form-field label="Finish" name="finish">
                            <select name="finish" class="form-select form-select-sm @error('finish') is-invalid @enderror">
                                <option value="">— Pilih —</option>
                                <option value="glossy"  {{ old('finish') === 'glossy'  ? 'selected' : '' }}>Glossy</option>
                                <option value="matte"   {{ old('finish') === 'matte'   ? 'selected' : '' }}>Matte</option>
                                <option value="satin"   {{ old('finish') === 'satin'   ? 'selected' : '' }}>Satin</option>
                                <option value="ceramic" {{ old('finish') === 'ceramic' ? 'selected' : '' }}>Ceramic</option>
                            </select>
                            @error('finish')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-4">
                        <x-ui.form-field label="Garansi Default (tahun) *" name="default_warranty_years">
                            <input type="number" name="default_warranty_years" class="form-control form-control-sm @error('default_warranty_years') is-invalid @enderror"
                                   value="{{ old('default_warranty_years', 5) }}" min="1" max="20" required>
                            @error('default_warranty_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Coverage Default" name="default_coverage">
                            <textarea name="default_coverage" rows="2" class="form-control form-control-sm @error('default_coverage') is-invalid @enderror"
                                      placeholder="Panel yang dicakup garansi secara default…">{{ old('default_coverage') }}</textarea>
                            @error('default_coverage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Pengecualian Garansi Default" name="default_exclusions">
                            <textarea name="default_exclusions" rows="2" class="form-control form-control-sm @error('default_exclusions') is-invalid @enderror"
                                      placeholder="Hal-hal yang tidak dicakup garansi…">{{ old('default_exclusions') }}</textarea>
                            @error('default_exclusions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <x-ui.button href="{{ route('ppf-products.index') }}" variant="outline" size="sm">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" size="sm">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Produk
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
