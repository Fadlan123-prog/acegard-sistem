@extends('layout.index')

@section('title', 'Tambah Mutasi')
@section('route-breadcrumb', route('bank-accounts.entries', $account->id))
@section('breadcrumb', 'Tambah Mutasi Bank')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">

        {{-- Account info chip --}}
        <div class="d-flex align-items-center gap-2 mb-4 text-muted small">
            <iconify-icon icon="mdi:bank-outline"></iconify-icon>
            <span>{{ $account->account_name }} · {{ $account->bank_name }} {{ $account->account_number }}</span>
            <span class="ms-auto fw-semibold" style="color:#6366f1">
                Saldo: Rp {{ number_format($account->current_balance, 0, ',', '.') }}
            </span>
        </div>

        <x-ui.card>
            @slot('header')
                <div class="ui-card-title"><iconify-icon icon="mdi:plus-circle-outline" class="me-2 text-primary"></iconify-icon>Tambah Mutasi</div>
            @endslot

            <form method="POST" action="{{ route('bank-accounts.entries.store', $account->id) }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Tanggal Mutasi" name="entry_date">
                            <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror"
                                   value="{{ old('entry_date', today()->format('Y-m-d')) }}" required>
                            @error('entry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="No. Referensi" name="reference_number">
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                                   value="{{ old('reference_number') }}" maxlength="100" placeholder="Opsional">
                            @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Keterangan" name="description">
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                                   value="{{ old('description') }}" required maxlength="255"
                                   placeholder="Transfer pembayaran invoice, biaya operasional, dll…">
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium mb-2">Jenis Mutasi <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <div class="flex-fill">
                                <label class="form-label small text-success fw-medium">
                                    <iconify-icon icon="mdi:arrow-down-circle-outline" class="me-1"></iconify-icon>Masuk / Kredit (Rp)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">+</span>
                                    <input type="number" name="credit" class="form-control @error('credit') is-invalid @enderror"
                                           value="{{ old('credit', 0) }}" min="0" placeholder="0">
                                    @error('credit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="flex-fill">
                                <label class="form-label small text-danger fw-medium">
                                    <iconify-icon icon="mdi:arrow-up-circle-outline" class="me-1"></iconify-icon>Keluar / Debit (Rp)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">−</span>
                                    <input type="number" name="debit" class="form-control @error('debit') is-invalid @enderror"
                                           value="{{ old('debit', 0) }}" min="0" placeholder="0">
                                    @error('debit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-text">Isi salah satu saja: masuk atau keluar.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Saldo Setelah (dari rekening koran)" name="balance_after">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="balance_after" class="form-control @error('balance_after') is-invalid @enderror"
                                       value="{{ old('balance_after') }}" min="0" placeholder="Opsional">
                                @error('balance_after')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">Saldo dari rekening koran bank untuk verifikasi.</div>
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
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Mutasi
                    </x-ui.button>
                    <x-ui.button :href="route('bank-accounts.entries', $account->id)" variant="outline">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
