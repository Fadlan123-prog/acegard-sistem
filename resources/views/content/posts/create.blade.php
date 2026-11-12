@extends('layout.index')

@section('title', 'Catat Konten')
@section('route-breadcrumb', route('content.posts.index'))
@section('breadcrumb', 'Catat Konten')

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
            <div class="ui-card-title">Catat Konten</div>
            <x-ui.button :href="route('content.posts.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('content.posts.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Creator" name="employee_id" :required="true">
                    <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">Pilih creator</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" @selected((string) old('employee_id') === (string) $emp->id)>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Platform" name="platform" :required="true">
                    <select name="platform" class="form-select @error('platform') is-invalid @enderror" required>
                        <option value="">Pilih platform</option>
                        @foreach (\App\Models\ContentPost::PLATFORMS as $key => $label)
                            <option value="{{ $key }}" @selected(old('platform') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-3">
                <x-ui.form-field label="Judul / Topik" name="title" :required="true">
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" placeholder="Judul video/konten" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Tanggal Tayang" name="published_at" :required="true">
                    <input type="date" name="published_at" class="form-control @error('published_at') is-invalid @enderror"
                           value="{{ old('published_at', now()->toDateString()) }}" required>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Views" name="views" :required="true">
                    <input type="number" min="0" name="views" class="form-control @error('views') is-invalid @enderror"
                           value="{{ old('views', 0) }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Engagement Rate (%)" name="engagement_rate" :optional="true">
                    <input type="number" step="0.01" min="0" max="100" name="engagement_rate"
                           class="form-control @error('engagement_rate') is-invalid @enderror"
                           value="{{ old('engagement_rate') }}">
                </x-ui.form-field>
            </div>
            <div class="col-md-4 mb-3">
                <x-ui.form-field label="Leads Dihasilkan" name="leads_generated" :required="true">
                    <input type="number" min="0" name="leads_generated"
                           class="form-control @error('leads_generated') is-invalid @enderror"
                           value="{{ old('leads_generated', 0) }}" required>
                </x-ui.form-field>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <x-ui.form-field label="Catatan" name="notes" :optional="true">
                    <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                           value="{{ old('notes') }}">
                </x-ui.form-field>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-2">
            <x-ui.button :href="route('content.posts.index')" variant="secondary">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
