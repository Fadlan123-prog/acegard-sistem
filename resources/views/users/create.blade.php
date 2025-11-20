@extends('dashboard.index')

@section('title', 'Tambah User')
@section('route-breadcrumb', 'users.index')
@section('breadcrumb', 'Tambah User')

@section('content')

<div class="card p-4 shadow-sm">
    <div class="container-fluid my-4">

        <h4 class="fw-bold mb-4">Tambah User</h4>

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            {{-- Nama --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Nama</label>
                <input type="text" name="name" class="form-control"
                       placeholder="Masukkan nama lengkap"
                       value="{{ old('name') }}" required>
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="email@domain.com"
                       value="{{ old('email') }}" required>
            </div>

            {{-- Password --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Minimal 6 karakter" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control"
                           placeholder="Ulangi password" required>
                </div>
            </div>

            {{-- Role --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Role</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}"
                            {{ old('role') == $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Cabang --}}
            <div class="mb-4">
                <label class="form-label fw-medium">Cabang</label>
                <select name="branch_ids[]" id="branchSelect" class="form-select" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}"
                            @if(old('branch_ids') && in_array($branch->id, old('branch_ids'))) selected @endif>
                            {{ $branch->name }} - {{ $branch->city }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Tahan tombol <strong>Ctrl</strong> (Windows) atau <strong>⌘ Command</strong> (Mac) untuk memilih lebih dari satu cabang.</small>
            </div>

            <button type="submit" class="btn btn-primary px-4">
                Simpan User
            </button>
        </form>
    </div>
</div>

@endsection
