@extends('layout.index')

@section('title', 'Edit Hutang')
@section('route-breadcrumb', route('hutang.index'))
@section('breadcrumb', 'Edit Hutang')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title"><iconify-icon icon="mdi:pencil-outline" class="me-2 text-primary"></iconify-icon>Edit Hutang — {{ $payable->vendor_name }}</div>
            @endslot

            @if ($payable->paid_amount > 0)
            <x-ui.alert type="warning" class="mb-4">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="mdi:information-outline" class="fs-5"></iconify-icon>
                    <div>Hutang ini sudah memiliki pembayaran sebesar <strong>Rp {{ number_format($payable->paid_amount, 0, ',', '.') }}</strong>.
                    Nilai hutang tidak boleh kurang dari jumlah yang sudah dibayar.</div>
                </div>
            </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('hutang.update', $payable->id) }}">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Nama Vendor" name="vendor_name" :required="true">
                            <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror"
                                   value="{{ old('vendor_name', $payable->vendor_name) }}" required maxlength="255">
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Supplier (opsional)" name="supplier_id">
                            <select name="supplier_id" class="form-select">
                                <option value="">— Tidak ada —</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id', $payable->supplier_id) == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>

                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="No. Referensi / Invoice" name="reference_number">
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                                   value="{{ old('reference_number', $payable->reference_number) }}" maxlength="100">
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Jatuh Tempo" name="due_date">
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                   value="{{ old('due_date', $payable->due_date?->format('Y-m-d')) }}">
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Deskripsi" name="description" :required="true">
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                      required>{{ old('description', $payable->description) }}</textarea>
                        </x-ui.form-field>
                    </div>

                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Total Hutang (Rp)" name="amount" :required="true">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $payable->amount) }}" required
                                       min="{{ $payable->paid_amount > 0 ? $payable->paid_amount : 1 }}">
                            </div>
                            @if ($payable->paid_amount > 0)
                                <div class="form-text">Minimum: Rp {{ number_format($payable->paid_amount, 0, ',', '.') }} (sudah dibayar)</div>
                            @endif
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Catatan Tambahan" name="notes">
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                                      maxlength="1000">{{ old('notes', $payable->notes) }}</textarea>
                        </x-ui.form-field>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <x-ui.button variant="primary" type="submit">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Perubahan
                    </x-ui.button>
                    <x-ui.button href="{{ route('hutang.show', $payable->id) }}" variant="outline">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
