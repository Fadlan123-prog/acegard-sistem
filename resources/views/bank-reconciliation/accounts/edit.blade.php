@extends('layout.index')

@section('title', 'Edit Rekening Bank')
@section('route-breadcrumb', route('bank-accounts.index'))
@section('breadcrumb', 'Edit Rekening Bank')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title"><iconify-icon icon="mdi:pencil-outline" class="me-2 text-primary"></iconify-icon>Edit Rekening — {{ $account->account_name }}</div>
            @endslot

            <form method="POST" action="{{ route('bank-accounts.update', $account->id) }}">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Nama Bank" name="bank_name">
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                   value="{{ old('bank_name', $account->bank_name) }}" required maxlength="100">
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="No. Rekening" name="account_number">
                            <input type="text" name="account_number" class="form-control @error('account_number') is-invalid @enderror"
                                   value="{{ old('account_number', $account->account_number) }}" required maxlength="50">
                            @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                    <div class="col-12">
                        <x-ui.form-field label="Nama Pemilik Rekening" name="account_name">
                            <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror"
                                   value="{{ old('account_name', $account->account_name) }}" required maxlength="255">
                            @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Rekening Aktif</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <x-ui.form-field label="Catatan" name="notes">
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                                      maxlength="1000">{{ old('notes', $account->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                </div>

                <x-ui.alert type="info" class="mt-3">
                    <iconify-icon icon="mdi:information-outline" class="me-1"></iconify-icon>
                    Saldo awal tidak dapat diubah setelah rekening dibuat. Saldo berjalan: <strong>Rp {{ number_format($account->current_balance, 0, ',', '.') }}</strong>
                </x-ui.alert>

                <div class="d-flex gap-2 mt-3">
                    <x-ui.button type="submit" variant="primary">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Perubahan
                    </x-ui.button>
                    <x-ui.button href="{{ route('bank-accounts.index') }}" variant="outline">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
