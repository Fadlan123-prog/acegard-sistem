@extends('auth.layout')

@section('title', 'Lupa Password')
@section('heading', 'Lupa Password')
@section('subheading', 'Masukkan email akun sales Anda. Jika terdaftar dan aktif, kami kirim tautan untuk membuat password baru.')

@section('content')
<form action="{{ route('password.email') }}" method="POST">
    @csrf
    <div class="icon-field mb-20">
        <span class="icon top-50 translate-middle-y">
            <iconify-icon icon="mage:email"></iconify-icon>
        </span>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control h-56-px bg-neutral-50 radius-12"
               placeholder="Email" autocomplete="email" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12">Kirim Tautan</button>

    <p class="text-secondary-light small text-center mt-24 mb-0">
        Lupa password akun admin/owner/staf? Hubungi superadmin — akun tersebut tidak bisa direset lewat email.
    </p>
</form>

<div class="text-center mt-24">
    <a href="{{ route('login') }}" class="text-primary-600 fw-medium">Kembali ke Login</a>
</div>
@endsection
