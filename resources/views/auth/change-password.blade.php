@extends('auth.layout')

@section('title', 'Ganti Password')
@section('heading', 'Ganti Password')
@section('subheading', $forced
    ? 'Password Anda dibuat sementara oleh admin. Buat password milik Anda sendiri untuk melanjutkan.'
    : 'Masukkan password saat ini, lalu password baru.')

@section('content')
<form action="{{ route('password.change.update') }}" method="POST" autocomplete="off">
    @csrf

    <div class="position-relative mb-16">
        <div class="icon-field">
            <span class="icon top-50 translate-middle-y"><iconify-icon icon="solar:lock-outline"></iconify-icon></span>
            <input type="password" name="current_password" id="cur-password" class="form-control h-56-px bg-neutral-50 radius-12"
                   placeholder="Password saat ini" autocomplete="current-password" required>
        </div>
        <iconify-icon icon="mdi:eye-outline" class="toggle-password cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#cur-password"></iconify-icon>
    </div>

    <div class="position-relative mb-16">
        <div class="icon-field">
            <span class="icon top-50 translate-middle-y"><iconify-icon icon="solar:lock-password-outline"></iconify-icon></span>
            <input type="password" name="password" id="new-password" class="form-control h-56-px bg-neutral-50 radius-12"
                   placeholder="Password baru" autocomplete="new-password" required>
        </div>
        <iconify-icon icon="mdi:eye-outline" class="toggle-password cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#new-password"></iconify-icon>
    </div>

    <div class="position-relative mb-12">
        <div class="icon-field">
            <span class="icon top-50 translate-middle-y"><iconify-icon icon="solar:lock-password-outline"></iconify-icon></span>
            <input type="password" name="password_confirmation" id="new-password-confirm" class="form-control h-56-px bg-neutral-50 radius-12"
                   placeholder="Ulangi password baru" autocomplete="new-password" required>
        </div>
        <iconify-icon icon="mdi:eye-outline" class="toggle-password cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light" data-toggle="#new-password-confirm"></iconify-icon>
    </div>

    <p class="text-secondary-light small mb-24">
        Minimal {{ config('password_setup.min_length', 10) }} karakter, mengandung huruf besar, huruf kecil, dan angka.
    </p>

    <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12">Simpan Password Baru</button>
</form>

<form action="{{ route('logout') }}" method="POST" class="text-center mt-24">
    @csrf
    <button type="submit" class="btn btn-link text-primary-600 fw-medium p-0">Keluar</button>
</form>
@endsection
