@extends('layout.index')

@section('title', 'Tambah Rekening Bank')
@section('route-breadcrumb', route('bank-accounts.index'))
@section('breadcrumb', 'Tambah Rekening Bank')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title"><iconify-icon icon="mdi:bank-outline" class="me-2 text-primary"></iconify-icon>Tambah Rekening Bank</div>
            @endslot

            <form method="POST" action="{{ route('bank-accounts.store') }}">
                @csrf

                @if ($isSuperadmin)
                <x-ui.form-field label="Cabang" name="branch_id">
                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                        <option value="">Pilih Cabang</option>
                        @foreach ($branches as $br)
                            <option value="{{ $br->id }}" {{ old('branch_id', $branchId) == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-ui.form-field>
                @else
                    <input type="hidden" name="branch_id" value="{{ $branchId }}">
                @endif

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Nama Bank" name="bank_name">
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                   value="{{ old('bank_name') }}" required maxlength="100" placeholder="BCA, BRI, Mandiri…">
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="No. Rekening" name="account_number">
                            <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror"
                                   value="{{ old('account_number') }}" required maxlength="50" placeholder="1234567890">
                            @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                    <div class="col-12">
                        <x-ui.form-field label="Nama Pemilik Rekening" name="account_name">
                            <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror"
                                   value="{{ old('account_name') }}" required maxlength="255" placeholder="Nama pemilik / perusahaan">
                            @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Saldo Awal (Rp)" name="opening_balance">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror"
                                       value="{{ old('opening_balance', 0) }}" required min="0">
                                @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </x-ui.form-field>
                    </div>
                    <div class="col-12">
                        <x-ui.form-field label="Catatan" name="notes">
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                                      maxlength="1000" placeholder="Opsional">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <x-ui.button type="submit" variant="primary">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Rekening
                    </x-ui.button>
                    <x-ui.button :href="route('bank-accounts.index')" variant="outline">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
