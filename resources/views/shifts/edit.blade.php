@extends('dashboard.index')

@section('title', 'Edit Shift')
@section('route-breadcrumb', 'shifts.index')
@section('breadcrumb', 'Edit Shift')

@section('content')

<x-ui.card style="max-width:640px">
    @slot('header')
        <div class="ui-card-title">Edit Shift: {{ $shift->name }}</div>
    @endslot
    <form action="{{ route('shifts.update', $shift) }}" method="POST">
        @csrf @method('PUT')
        @include('shifts._form')
        <div class="mt-3 d-flex gap-2">
            <x-ui.button type="submit" variant="primary">Perbarui</x-ui.button>
            <x-ui.button href="{{ route('shifts.index') }}" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
