@extends('layout.index')

@section('title', 'Tambah Target KPI')
@section('route-breadcrumb', route('kpi.targets.index'))
@section('breadcrumb', 'Tambah Target KPI')

@section('content')

@if($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Tambah / Ubah Target KPI</div>
            <x-ui.button :href="route('kpi.targets.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('kpi.targets.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Divisi" name="division" :required="true">
                    <select name="division" class="form-select @error('division') is-invalid @enderror" required>
                        <option value="">Pilih divisi</option>
                        @foreach (\App\Models\KpiTarget::DIVISIONS as $key => $label)
                            <option value="{{ $key }}" @selected(old('division') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Metric Key" name="metric_key" help="Kode unik, mis. balas_chat_5menit" :required="true">
                    <input type="text" name="metric_key" class="form-control @error('metric_key') is-invalid @enderror" value="{{ old('metric_key') }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Nama KPI" name="metric_label" :required="true">
                    <input type="text" name="metric_label" class="form-control @error('metric_label') is-invalid @enderror" value="{{ old('metric_label') }}" placeholder="Balas chat ≤5 menit" required>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Target" name="target_value" :required="true">
                    <input type="number" step="0.01" min="0" name="target_value" class="form-control @error('target_value') is-invalid @enderror" value="{{ old('target_value') }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Bobot (%)" name="weight" :required="true">
                    <input type="number" step="0.01" min="0" max="100" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight') }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Arah" name="direction" :required="true">
                    <select name="direction" class="form-select @error('direction') is-invalid @enderror" required>
                        <option value="higher_better" @selected(old('direction', 'higher_better') === 'higher_better')>Makin besar makin baik</option>
                        <option value="lower_better" @selected(old('direction') === 'lower_better')>Makin kecil makin baik</option>
                    </select>
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Periode" name="period_month" :required="true">
                    <div class="d-flex gap-2">
                        <select name="period_month" class="form-select @error('period_month') is-invalid @enderror" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected((int) old('period_month', now()->month) === $m)>{{ $m }}</option>
                            @endfor
                        </select>
                        <input type="number" name="period_year" class="form-control @error('period_year') is-invalid @enderror" value="{{ old('period_year', now()->year) }}" required>
                    </div>
                </x-ui.form-field>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-2">
            <x-ui.button :href="route('kpi.targets.index')" variant="secondary">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
