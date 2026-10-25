@extends('dashboard.index')

@section('title', 'Ajukan Klaim')
@section('route-breadcrumb', 'my-claims.create')
@section('breadcrumb', 'Ajukan Klaim')

@section('content')

<x-ui.card>
    @if($healthBalance)
        <x-ui.alert type="info" class="mb-3">
            Sisa saldo biaya kesehatan tahun {{ $healthBalance->year }}:
            <strong>Rp {{ number_format($healthBalance->remaining(), 0, ',', '.') }}</strong>
            dari plafon Rp {{ number_format($healthBalance->allocated_amount, 0, ',', '.') }}.
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('my-claims.store') }}" enctype="multipart/form-data">
        @csrf

        <x-ui.form-field label="Jenis Klaim" name="type" :required="true" :error="$errors->first('type')">
            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                <option value="">— Pilih jenis klaim —</option>
                <option value="pembelian" @selected(old('type') == 'pembelian')>Klaim Pembelian</option>
                <option value="bensin" @selected(old('type') == 'bensin')>Klaim Bensin</option>
                <option value="kesehatan" @selected(old('type') == 'kesehatan')>Klaim Biaya Kesehatan</option>
            </select>
        </x-ui.form-field>

        <x-ui.form-field label="Nominal (Rp)" name="amount" :required="true" :error="$errors->first('amount')">
            <input type="number" name="amount" min="1" value="{{ old('amount') }}"
                   class="form-control @error('amount') is-invalid @enderror" required>
        </x-ui.form-field>

        <x-ui.form-field label="Keterangan" name="description" :error="$errors->first('description')">
            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
        </x-ui.form-field>

        <x-ui.form-field label="Bukti (Foto Struk/Nota)" name="proof" :error="$errors->first('proof')">
            <input type="file" name="proof" accept="image/*" class="form-control @error('proof') is-invalid @enderror">
        </x-ui.form-field>

        <div class="d-flex gap-2 mt-3">
            <x-ui.button type="submit" variant="primary">Ajukan Klaim</x-ui.button>
            <x-ui.button :href="route('my-claims.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
