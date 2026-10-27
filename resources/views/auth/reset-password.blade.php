@extends('auth.layout')

@section('title', 'Atur Password')
@section('heading', 'Buat Password Baru')
@section('subheading', 'Tautan ini hanya bisa dipakai sekali.')

@section('content')
<form action="{{ route('password.update') }}" method="POST" autocomplete="off">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="icon-field mb-16">
        <span class="icon top-50 translate-middle-y">
            <iconify-icon icon="mage:email"></iconify-icon>
        </span>
        <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control h-56-px bg-neutral-50 radius-12"
               placeholder="Email" autocomplete="username" required>
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

    <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12">Simpan Password</button>
</form>

<div class="text-center mt-24">
    <a href="{{ route('password.request') }}" class="text-primary-600 fw-medium">Minta tautan baru</a>
</div>
@endsection
