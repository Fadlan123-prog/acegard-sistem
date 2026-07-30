@extends('layout.index')

@section('title', 'Tambah Barang Pendukung')
@section('route-breadcrumb', route('supporting-goods.index'))
@section('breadcrumb', 'Tambah Barang Pendukung')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <x-ui.card title="Tambah Barang Pendukung">
            <form method="POST" action="{{ route('supporting-goods.store') }}">
                @csrf

                @if ($isSuperadmin)
                <x-ui.form-field label="Cabang" name="branch_id" :required="true" :error="$errors->first('branch_id')">
                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach ($branches as $br)
                            <option value="{{ $br->id }}" {{ old('branch_id', $branchId) == $br->id ? 'selected' : '' }}>
                                {{ $br->name }}
                            </option>
                        @endforeach
                    </select>
                </x-ui.form-field>
                @else
                    <input type="hidden" name="branch_id" value="{{ $branchId }}">
                @endif

                <x-ui.form-field label="Nama Barang" name="name" :required="true" :error="$errors->first('name')">
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="Contoh: Solar Gard IQ Spray" required>
                </x-ui.form-field>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <x-ui.form-field label="Kategori" name="category" :required="true" :error="$errors->first('category')">
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">-- Pilih --</option>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                    <div class="col-6">
                        <x-ui.form-field label="Satuan" name="unit" :required="true" :error="$errors->first('unit')">
                            <input type="text" name="unit" list="unitList"
                                   class="form-control @error('unit') is-invalid @enderror"
                                   value="{{ old('unit', 'pcs') }}" placeholder="pcs" required>
                            <datalist id="unitList">
                                <option value="pcs">
                                <option value="botol">
                                <option value="liter">
                                <option value="kg">
                                <option value="roll">
                                <option value="meter">
                                <option value="set">
                                <option value="lusin">
                            </datalist>
                        </x-ui.form-field>
                    </div>
                </div>

                <x-ui.form-field label="Stok Minimum" name="minimum_stock" :error="$errors->first('minimum_stock')">
                    <input type="number" name="minimum_stock"
                           class="form-control @error('minimum_stock') is-invalid @enderror"
                           value="{{ old('minimum_stock', 0) }}" min="0" step="0.01"
                           placeholder="0 = tidak ada alert">
                    <div class="form-text">Sistem akan menandai stok kritis jika stok ≤ nilai ini.</div>
                </x-ui.form-field>

                <x-ui.form-field label="Deskripsi" name="description" :error="$errors->first('description')">
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="3" placeholder="Keterangan tambahan (opsional)">{{ old('description') }}</textarea>
                </x-ui.form-field>

                <div class="d-flex gap-2">
                    <x-ui.button type="submit" variant="primary">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan
                    </x-ui.button>
                    <x-ui.button href="{{ route('supporting-goods.index') }}" variant="outline">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
