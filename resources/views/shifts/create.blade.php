@extends('dashboard.index')

@section('title', 'Tambah Shift')
@section('route-breadcrumb', 'shifts.index')
@section('breadcrumb', 'Tambah Shift')

@section('content')

<x-ui.card title="Tambah Shift Baru" style="max-width:640px">
    <form action="{{ route('shifts.store') }}" method="POST">
        @csrf
        @include('shifts._form')
        <div class="mt-3 d-flex gap-2">
            <x-ui.button variant="primary" type="submit">Simpan</x-ui.button>
            <x-ui.button variant="secondary" href="{{ route('shifts.index') }}">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
