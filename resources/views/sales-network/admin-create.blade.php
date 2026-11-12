@extends('dashboard.index')

@section('title', 'Tambah Anggota Jaringan')
@section('route-breadcrumb', 'sales-network.admin-create')
@section('breadcrumb', 'Tambah Anggota Jaringan')

@section('content')

<x-ui.alert type="info" class="mb-3">
    <span class="small">Dipakai untuk memasukkan karyawan yang <strong>sudah ada</strong> (role sales/marketing) langsung ke jaringan — misalnya "Marketing Pusat" sebagai akar jaringan yang tidak punya sponsor. Untuk sales baru yang direkrut lewat kode referral, gunakan jalur rekrut mandiri (menu "Jaringan Sales Saya" milik si perekrut), bukan halaman ini.</span>
</x-ui.alert>

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

<x-ui.card style="max-width: 560px;">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Tambah Anggota</div>
            <x-ui.button :href="route('sales-network.admin-index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form method="POST" action="{{ route('sales-network.admin-store') }}">
        @csrf

        <x-ui.form-field label="Karyawan" name="employee_id" :required="true" :error="$errors->first('employee_id')">
            <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                <option value="">— Pilih karyawan (role sales/marketing) —</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(old('employee_id') == $emp->id)>
                        {{ $emp->name }} — {{ $emp->job_position }} ({{ $emp->branch?->name }})
                    </option>
                @endforeach
            </select>
            <div class="form-text">Hanya menampilkan karyawan role sales/marketing yang belum punya profil jaringan.</div>
        </x-ui.form-field>

        <x-ui.form-field label="Sponsor (opsional)" name="sponsor_id" :error="$errors->first('sponsor_id')" class="mt-3">
            <select name="sponsor_id" class="form-select @error('sponsor_id') is-invalid @enderror">
                <option value="">— Tidak ada (jadi akar jaringan) —</option>
                @foreach($sponsorCandidates as $candidate)
                    <option value="{{ $candidate->employee_id }}" @selected(old('sponsor_id') == $candidate->employee_id)>
                        {{ $candidate->employee?->name }} ({{ $candidate->referral_code }})
                    </option>
                @endforeach
            </select>
            <div class="form-text">Kosongkan kalau karyawan ini adalah akar jaringan (mis. Marketing Pusat).</div>
        </x-ui.form-field>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary">Simpan</x-ui.button>
            <x-ui.button :href="route('sales-network.admin-index')" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
