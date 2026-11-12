@extends('frontend.layout.index')

@section('title', 'Daftar Sales — Acegard')

@section('hide-navbar', '1')
@section('hide-footer', '1')

@section('content')
<style>
    .register-hero{
        background: radial-gradient(1200px circle at 10% 10%, rgba(13,110,253,.18), transparent 50%),
                    radial-gradient(900px circle at 90% 20%, rgba(25,135,84,.14), transparent 45%),
                    linear-gradient(180deg, #ffffff 0%, #f8fafc 55%, #ffffff 100%);
        min-height: 80vh;
    }
    .register-card{
        border: 1px solid rgba(0,0,0,.06);
        box-shadow: 0 10px 30px rgba(0,0,0,.06);
        border-radius: 16px;
        background: #fff;
    }
</style>

<div class="register-hero py-5 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="text-center mb-4">
                    <h2 class="fw-bold">Daftar Jadi Sales Acegard</h2>
                    <p class="text-muted">Punya kode referral dari sales/marketing Acegard? Daftar di sini untuk gabung jaringan mereka.</p>
                </div>

                @if(session('success'))
                     <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <li class="ml-2">{{ $error }}</li>
                    @endforeach
                </div>
                @endif

                <div class="register-card p-4 p-md-5">
                    <form method="POST" action="{{ route('sales-network.public-register.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode Referral Sponsor <span class="text-danger">*</span></label>
                            <input type="text" name="referral_code" class="form-control form-control-lg text-uppercase @error('referral_code') is-invalid @enderror"
                                   value="{{ old('referral_code') }}" placeholder="REF-XXXXXX" style="letter-spacing:1px;" required>
                            <div class="form-text">Minta kode ini ke sales/marketing yang mengajak Anda bergabung.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Nama sesuai KTP" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="email@contoh.com" required>
                                <div class="form-text">Dipakai untuk login nanti.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">No. WhatsApp</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-semibold mt-2">Daftar Sekarang</button>

                        <p class="text-muted small text-center mt-3 mb-0">
                            Pendaftaran Anda akan ditinjau admin sebelum akun bisa dipakai.
                        </p>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-decoration-none">Sudah punya akun? Masuk di sini</a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
