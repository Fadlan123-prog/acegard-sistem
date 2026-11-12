@extends('dashboard.index')

@section('title', 'Buat Tugas Baru')
@section('route-breadcrumb', 'creative-tasks.create')
@section('breadcrumb', 'Buat Tugas Baru')

@section('content')

<x-ui.card>
    <form method="POST" action="{{ route('creative-tasks.store') }}">
        @csrf

        @include('creative-tasks._form-fields')

        <div class="d-flex gap-2 mt-3">
            <x-ui.button type="submit" variant="primary">Buat & Tugaskan</x-ui.button>
            <x-ui.button :href="route('creative-tasks.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
