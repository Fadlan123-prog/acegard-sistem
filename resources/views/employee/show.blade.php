@extends('dashboard.index')
@section('title','Detail Karyawan')

@section('content')
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Detail Karyawan</div>
            <div class="d-flex gap-2">
                <x-ui.button href="{{ route('employees.edit',$employee) }}" variant="primary" size="sm">Edit</x-ui.button>
                <x-ui.button href="{{ route('employees.index') }}" variant="secondary" size="sm">Kembali</x-ui.button>
            </div>
        </div>
    @endslot
    <dl class="row">
        <dt class="col-sm-3">Nama</dt>
        <dd class="col-sm-9">{{ $employee->name }}</dd>

        <dt class="col-sm-3">Posisi</dt>
        <dd class="col-sm-9">{{ $employee->job_position ?: '-' }}</dd>
    </dl>
</x-ui.card>
@endsection
