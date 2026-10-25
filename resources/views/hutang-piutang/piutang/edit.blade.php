@extends('layout.index')

@section('title', 'Edit Piutang')
@section('route-breadcrumb', route('piutang.index'))
@section('breadcrumb', 'Edit Piutang')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title"><iconify-icon icon="mdi:pencil-outline" class="me-2 text-primary"></iconify-icon>Edit Piutang — {{ $receivable->customer_name }}</div>
            @endslot

            @if ($receivable->paid_amount > 0)
            <x-ui.alert type="warning" class="mb-4">
                <iconify-icon icon="mdi:information-outline" class="fs-5"></iconify-icon>
                Piutang ini sudah ada penerimaan sebesar <strong>Rp {{ number_format($receivable->paid_amount, 0, ',', '.') }}</strong>.
                Nilai piutang tidak boleh kurang dari jumlah yang sudah diterima.
            </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('piutang.update', $receivable->id) }}">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Nama Pelanggan" name="customer_name">
                            <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror"
                                   value="{{ old('customer_name', $receivable->customer_name) }}" required maxlength="255">
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="No. Referensi / Invoice" name="reference_number">
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                                   value="{{ old('reference_number', $receivable->reference_number) }}" maxlength="100">
                        </x-ui.form-field>
                    </div>

                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Jatuh Tempo" name="due_date">
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                   value="{{ old('due_date', $receivable->due_date?->format('Y-m-d')) }}">
                        </x-ui.form-field>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-ui.form-field label="Total Piutang (Rp)" name="amount">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $receivable->amount) }}" required
                                       min="{{ $receivable->paid_amount > 0 ? $receivable->paid_amount : 1 }}">
                            </div>
                            @if ($receivable->paid_amount > 0)
                                <div class="form-text">Minimum: Rp {{ number_format($receivable->paid_amount, 0, ',', '.') }} (sudah diterima)</div>
                            @endif
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Deskripsi" name="description">
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                      required>{{ old('description', $receivable->description) }}</textarea>
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Catatan Tambahan" name="notes">
                            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                                      maxlength="1000">{{ old('notes', $receivable->notes) }}</textarea>
                        </x-ui.form-field>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <x-ui.button type="submit" variant="primary">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Perubahan
                    </x-ui.button>
                    <x-ui.button :href="route('piutang.show', $receivable->id)" variant="outline">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
