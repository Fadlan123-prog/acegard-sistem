@extends('dashboard.index')

@section('title', 'Tambah Cabang')
@section('route-breadcrumb', 'branch.index')
@section('breadcrumb', 'Tambah Cabang')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-3">
        <h4 class="fw-semibold mb-1">Tambah Cabang</h4>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center border-0 bg-white">
                    <h6 class="mb-0">Data Cabang</h6>

                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            Mohon periksa kembali form yang diisi.
                        </div>
                    @endif

                    <form action="{{ route('branches.store') }}" method="POST" novalidate>
                        @csrf

                        {{-- Nama Cabang --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Cabang</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="Nama Cabang"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kota --}}
                        <div class="mb-3">
                            <label for="city" class="form-label">Kota</label>
                            <input
                                type="text"
                                name="city"
                                id="city"
                                class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city') }}"
                                placeholder="Kota"
                                required
                            >
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="mb-4">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea
                                name="address"
                                id="address"
                                rows="3"
                                class="form-control @error('address') is-invalid @enderror"
                                placeholder="Alamat lengkap cabang"
                                required
                            >{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Simpan &amp; Lanjut Tambah User
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
