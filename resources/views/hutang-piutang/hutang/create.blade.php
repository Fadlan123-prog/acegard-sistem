@extends('layout.index')

@section('title', 'Catat Hutang')
@section('route-breadcrumb', route('hutang.index'))
@section('breadcrumb', 'Catat Hutang Baru')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title"><iconify-icon icon="mdi:file-plus-outline" class="me-2 text-danger"></iconify-icon>Catat Hutang Baru</div>
            @endslot

            <form method="POST" action="{{ route('hutang.store') }}">
                @csrf

                @if ($isSuperadmin)
                <x-ui.form-field label="Cabang" name="branch_id" :required="true">
                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                        <option value="">Pilih Cabang</option>
                        @foreach ($branches as $br)
                            <option value="{{ $br->id }}" {{ (old('branch_id', $branchId) == $br->id) ? 'selected' : '' }}>{{ $br->name }}</option>
                        @endforeach
                    </select>
                </x-ui.form-field>
                @else
                    <input type="hidden" name="branch_id" value="{{ $branchId }}">
                @endif

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Nama Vendor" name="vendor_name" :required="true">
                            <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror"
                                   value="{{ old('vendor_name') }}" required maxlength="255" placeholder="Nama perusahaan / individu">
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Supplier (opsional)" name="supplier_id">
                            <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                <option value="">— Tidak ada —</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>

                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="No. Referensi / Invoice" name="reference_number">
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                                   value="{{ old('reference_number') }}" maxlength="100" placeholder="Opsional">
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Jatuh Tempo" name="due_date">
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                   value="{{ old('due_date') }}">
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Deskripsi" name="description" :required="true">
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                      required placeholder="Keterangan hutang, untuk keperluan apa…">{{ old('description') }}</textarea>
                        </x-ui.form-field>
                    </div>

                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Total Hutang (Rp)" name="amount" :required="true">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}" required min="1" placeholder="0">
                            </div>
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Catatan Tambahan" name="notes">
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                                      maxlength="1000" placeholder="Opsional">{{ old('notes') }}</textarea>
                        </x-ui.form-field>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <x-ui.button variant="danger" type="submit">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Hutang
                    </x-ui.button>
                    <x-ui.button :href="route('hutang.index')" variant="outline">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
