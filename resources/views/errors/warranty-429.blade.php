@extends('frontend.layout.index')

@section('title', 'Terlalu Banyak Permintaan')

@section('content')
<style>
  .warranty-hero{
    background: radial-gradient(1200px circle at 10% 10%, rgba(13,110,253,.18), transparent 50%),
                radial-gradient(900px circle at 90% 20%, rgba(25,135,84,.14), transparent 45%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 55%, #ffffff 100%);
    min-height: 60vh;
  }
  .glass-card{
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 10px 30px rgba(0,0,0,.06);
    border-radius: 16px;
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(8px);
  }
  .brand-chip{
    border: 1px dashed rgba(0,0,0,.15);
    border-radius: 999px;
    padding: 6px 12px;
    background: rgba(255,255,255,.8);
  }
  .throttle-icon{
    width: 72px; height: 72px;
    border-radius: 999px;
    display: inline-flex; align-items:center; justify-content:center;
    font-size: 34px;
  }
  .btn-claim{
    border-radius: 12px;
    padding: 10px 14px;
    font-weight: 600;
  }
</style>

<div class="warranty-hero py-5 d-flex align-items-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="glass-card p-4 p-md-5 text-center">
          <div class="d-flex justify-content-center mb-3">
            <div class="brand-chip">
              <span class="fw-semibold">ACEGARD</span>
              <span class="text-muted">&bull; Warranty Center</span>
            </div>
          </div>

          <div class="throttle-icon bg-warning-subtle text-warning mx-auto mb-3">
            <iconify-icon icon="mdi:timer-sand"></iconify-icon>
          </div>

          <h1 class="fw-bold mb-2" style="letter-spacing:-.5px; font-size: 1.6rem;">
            Terlalu Banyak Permintaan
          </h1>
          <p class="text-muted mb-4">
            Anda telah melakukan percobaan terlalu sering dalam waktu singkat.
            Untuk menjaga keamanan sistem, silakan <b>coba lagi 2 menit kemudian</b>.
          </p>

          <a href="{{ route('warranty.form') }}" class="btn btn-primary btn-claim px-4">
            Kembali ke Cek Garansi
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
