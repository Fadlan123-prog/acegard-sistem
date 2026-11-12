@extends('layout.index')

@section('title', 'Catat Sesi Live')
@section('route-breadcrumb', route('live-sessions.index'))
@section('breadcrumb', 'Catat Sesi Live')

@section('content')

@if($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <strong>Terjadi kesalahan.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Catat Sesi Live</div>
            <x-ui.button :href="route('live-sessions.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('live-sessions.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Host" name="employee_id" :required="true">
                    <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">Pilih host</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" @selected((string) old('employee_id') === (string) $emp->id)>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Tanggal" name="session_date" :required="true">
                    <input type="date" name="session_date" class="form-control @error('session_date') is-invalid @enderror"
                           value="{{ old('session_date', now()->toDateString()) }}" required>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Durasi (menit)" name="duration_minutes" :required="true">
                    <input type="number" min="0" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', 0) }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Peak Viewers" name="viewers_peak" :required="true">
                    <input type="number" min="0" name="viewers_peak" class="form-control @error('viewers_peak') is-invalid @enderror" value="{{ old('viewers_peak', 0) }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Average Viewers" name="viewers_avg" :required="true">
                    <input type="number" min="0" name="viewers_avg" class="form-control @error('viewers_avg') is-invalid @enderror" value="{{ old('viewers_avg', 0) }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Jumlah Chat" name="chat_count" :optional="true">
                    <input type="number" min="0" name="chat_count" class="form-control @error('chat_count') is-invalid @enderror" value="{{ old('chat_count', 0) }}">
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Pertanyaan Produk" name="product_questions" :optional="true">
                    <input type="number" min="0" name="product_questions" class="form-control @error('product_questions') is-invalid @enderror" value="{{ old('product_questions', 0) }}">
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Leads Dihasilkan" name="leads_generated" :required="true">
                    <input type="number" min="0" name="leads_generated" class="form-control @error('leads_generated') is-invalid @enderror" value="{{ old('leads_generated', 0) }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Closing" name="closing_count" :optional="true">
                    <input type="number" min="0" name="closing_count" class="form-control @error('closing_count') is-invalid @enderror" value="{{ old('closing_count', 0) }}">
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Omzet (Rp)" name="revenue" :optional="true">
                    <input type="number" step="0.01" min="0" name="revenue" class="form-control @error('revenue') is-invalid @enderror" value="{{ old('revenue', 0) }}">
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <x-ui.form-field label="Catatan" name="notes" :optional="true">
                    <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}">
                </x-ui.form-field>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-2">
            <x-ui.button :href="route('live-sessions.index')" variant="secondary">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
