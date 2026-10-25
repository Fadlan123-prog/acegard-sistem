@extends('dashboard.index')

@section('title', 'Buat Rencana Tahunan')
@section('route-breadcrumb', 'development-plans.create')
@section('breadcrumb', 'Buat Rencana Tahunan')

@section('content')

<x-ui.card>
    <form method="POST" action="{{ route('development-plans.store') }}">
        @csrf

        <x-ui.form-field label="Tahun" name="year" :required="true" :error="$errors->first('year')">
            <input type="number" name="year" value="{{ old('year', now()->year + 1) }}" class="form-control @error('year') is-invalid @enderror" required>
        </x-ui.form-field>

        <x-ui.form-field label="Ringkasan Rencana" name="summary" :required="true" :error="$errors->first('summary')">
            <textarea name="summary" rows="8" class="form-control @error('summary') is-invalid @enderror" required>{{ old('summary') }}</textarea>
        </x-ui.form-field>

        <p class="small text-muted">Inisiatif spesifik bisa ditambahkan setelah rencana ini dibuat, dari halaman detail.</p>

        <div class="d-flex gap-2 mt-3">
            <x-ui.button type="submit" variant="primary">Simpan Rencana</x-ui.button>
            <x-ui.button :href="route('development-plans.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
