@extends('dashboard.index')

@section('title', 'Buat Pengajuan Cuti')
@section('route-breadcrumb', 'leave-requests.index')
@section('breadcrumb', 'Buat Pengajuan Cuti')

@section('content')

<x-ui.card style="max-width:640px">
    @slot('header')
        <div class="ui-card-title">Buat Pengajuan Cuti / Izin</div>
    @endslot

    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-ui.alert>
    @endif

    <form action="{{ route('leave-requests.store') }}" method="POST">
        @csrf

        <x-ui.form-field label="Karyawan *" name="employee_id">
            <select name="employee_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </x-ui.form-field>

        <x-ui.form-field label="Jenis Cuti *" name="leave_type">
            <select name="leave_type" class="form-select" required>
                <option value="">-- Pilih Jenis --</option>
                @foreach($typeLabels as $key => $label)
                    <option value="{{ $key }}" {{ old('leave_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </x-ui.form-field>

        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Dari Tanggal *" name="from_date">
                    <input type="date" name="from_date" class="form-control" value="{{ old('from_date') }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Sampai Tanggal *" name="to_date">
                    <input type="date" name="to_date" class="form-control" value="{{ old('to_date') }}" required>
                </x-ui.form-field>
            </div>
        </div>

        <x-ui.form-field label="Alasan *" name="reason">
            <textarea name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
        </x-ui.form-field>

        <div class="d-flex gap-2">
            <x-ui.button type="submit" variant="primary">Ajukan</x-ui.button>
            <x-ui.button :href="route('leave-requests.index')" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
