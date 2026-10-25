@extends('dashboard.index')

@section('title', 'Tambah Artikel')
@section('route-breadcrumb', 'articles.index')
@section('breadcrumb', 'Tambah Artikel')

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
            <div class="ui-card-title">Tambah Artikel</div>
            <x-ui.button :href="route('articles.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <x-ui.form-field label="Judul Artikel" name="title" :required="true">
                <input type="text"
                       name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}"
                       placeholder="Judul artikel"
                       required>
            </x-ui.form-field>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Kategori" name="category" :required="true">
                    <select name="category" class="form-control @error('category') is-invalid @enderror" required>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>

            <div class="col-md-6 mb-3">
                <x-ui.form-field label="Cover Image" name="cover_image">
                    <input type="file"
                           name="cover_image"
                           accept="image/*"
                           class="form-control @error('cover_image') is-invalid @enderror">
                </x-ui.form-field>
            </div>
        </div>

        <div class="mb-3">
            <x-ui.form-field label="Ringkasan (Excerpt)" name="excerpt">
                <textarea name="excerpt"
                          class="form-control @error('excerpt') is-invalid @enderror"
                          rows="2"
                          placeholder="Ringkasan singkat yang tampil di listing artikel...">{{ old('excerpt') }}</textarea>
            </x-ui.form-field>
        </div>

        <div class="mb-3">
            <x-ui.form-field label="Isi Artikel" name="body">
                <textarea name="body"
                          class="form-control @error('body') is-invalid @enderror"
                          rows="8"
                          placeholder="Isi lengkap artikel...">{{ old('body') }}</textarea>
            </x-ui.form-field>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <x-ui.form-field label="Tanggal Publish" name="published_at">
                    <input type="datetime-local"
                           name="published_at"
                           class="form-control @error('published_at') is-invalid @enderror"
                           value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
                </x-ui.form-field>
            </div>

            <div class="col-md-4 mb-4 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="is_published"
                           {{ old('is_published', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_published">Publish sekarang</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <x-ui.button type="submit" variant="primary">Simpan Artikel</x-ui.button>
            <x-ui.button :href="route('articles.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
