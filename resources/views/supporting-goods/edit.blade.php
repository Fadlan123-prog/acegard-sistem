@extends('layout.index')

@section('title', 'Edit Barang Pendukung')
@section('route-breadcrumb', route('supporting-goods.index'))
@section('breadcrumb', 'Edit Barang Pendukung')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <x-ui.card title="Edit: {{ $good->name }}">
            <form method="POST" action="{{ route('supporting-goods.update', $good->id) }}">
                @csrf @method('PUT')

                @if ($isSuperadmin)
                <x-ui.form-field label="Cabang" name="branch_id">
                    <input type="text" class="form-control" value="{{ $good->branch?->name }}" disabled>
                </x-ui.form-field>
                @endif

                <x-ui.form-field label="Nama Barang" name="name" :required="true" :error="$errors->first('name')">
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $good->name) }}" required>
                </x-ui.form-field>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <x-ui.form-field label="Kategori" name="category" :required="true" :error="$errors->first('category')">
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category', $good->category) === $key ? 'selected' : '' }}>
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
                                   value="{{ old('unit', $good->unit) }}" required>
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
                           value="{{ old('minimum_stock', $good->minimum_stock) }}" min="0" step="0.01">
                    <div class="form-text">Stok saat ini: <strong>{{ number_format($good->current_stock, 2) }} {{ $good->unit }}</strong></div>
                </x-ui.form-field>

                <x-ui.form-field label="Deskripsi" name="description" :error="$errors->first('description')">
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="3">{{ old('description', $good->description) }}</textarea>
                </x-ui.form-field>

                <div class="d-flex gap-2">
                    <x-ui.button type="submit" variant="primary">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Update
                    </x-ui.button>
                    <x-ui.button :href="route('supporting-goods.index')" variant="outline">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
