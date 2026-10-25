@extends('dashboard.index')

@section('title', 'Buat Laporan Berkala')
@section('route-breadcrumb', 'development-reports.create')
@section('breadcrumb', 'Buat Laporan')

@section('content')

<x-ui.card>
    <form method="POST" action="{{ route('development-reports.store') }}">
        @csrf

        <x-ui.form-field label="Judul" name="title" :required="true" :error="$errors->first('title')">
            <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required>
        </x-ui.form-field>

        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Periode Mulai" name="period_start" :required="true" :error="$errors->first('period_start')">
                    <input type="date" name="period_start" value="{{ old('period_start') }}" class="form-control @error('period_start') is-invalid @enderror" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Periode Selesai" name="period_end" :required="true" :error="$errors->first('period_end')">
                    <input type="date" name="period_end" value="{{ old('period_end') }}" class="form-control @error('period_end') is-invalid @enderror" required>
                </x-ui.form-field>
            </div>
        </div>

        <x-ui.form-field label="Isi Laporan" name="content" :required="true" :error="$errors->first('content')">
            <textarea name="content" rows="10" class="form-control @error('content') is-invalid @enderror" required>{{ old('content') }}</textarea>
        </x-ui.form-field>

        <div class="d-flex gap-2 mt-3">
            <x-ui.button type="submit" variant="primary">Simpan Laporan</x-ui.button>
            <x-ui.button :href="route('development-reports.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
