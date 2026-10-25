@extends('dashboard.index')

@section('title', 'Tambah Saldo Petty Cash')
@section('route-breadcrumb', 'petty-cash.index')
@section('breadcrumb', 'Tambah Saldo Petty Cash')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">
        <x-ui.card>
            @slot('header')
                <div class="d-flex justify-content-between align-items-center">
                    <div class="ui-card-title">
                        <iconify-icon icon="mdi:plus-circle" class="me-2 text-success"></iconify-icon>
                        Input Saldo Masuk Petty Cash
                    </div>
                </div>
            @endslot

            <form method="POST" action="{{ route('petty-cash.store-in') }}">
                @csrf

                <x-ui.form-field label="Cabang" name="branch_id">
                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id', $branchId) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-ui.form-field>

                <x-ui.form-field label="Tanggal" name="date">
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
                           class="form-control @error('date') is-invalid @enderror" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-ui.form-field>

                <x-ui.form-field label="Jumlah Saldo (Rp)" name="amount">
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="amount" value="{{ old('amount') }}"
                               class="form-control @error('amount') is-invalid @enderror"
                               min="1" placeholder="0" required>
                    </div>
                    @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </x-ui.form-field>

                <x-ui.form-field label="Keterangan" name="description">
                    <textarea name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Sumber dana, keterangan, dll...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-ui.form-field>

                <div class="d-flex gap-2">
                    <x-ui.button variant="success" type="submit">
                        <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Simpan
                    </x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('petty-cash.index') }}">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
