@extends('frontend.layout.index')

@section('content')
<section class="hero py-5 py-lg-5">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-6">
        <span class="badge badge-soft rounded-pill px-3 py-2 mb-3 fw-semibold">
          ACEGARD NANO CERAMIC WINDOW FILM
        </span>

        <h1 class="hero-title display-4 mb-2">
          KACA FILM <span class="hero-subtitle">NANO CERAMIC</span><br>
          DENGAN <span class="hero-subtitle">SOLAR REJECT</span><br>
          TERBAIK
        </h1>

        <p class="hero-lead mt-3 mb-4">
          ACEGARD mengadaptasi teknologi <strong>Nano Ceramic Matrix Composite</strong> yang diperkuat
          elemen logam tingkat tinggi. Stabil secara optik, tahan lama, dan mampu menolak gelombang
          energi matahari hingga ke spektrum 2500 nm tanpa mengganggu sinyal elektronik.
        </p>

        <div class="d-flex gap-3 flex-wrap">
          <a href="#" class="btn btn-dark btn-pill px-4 py-2">More Information</a>
          <a href="#" class="btn btn-outline-dark btn-pill px-4 py-2">Produk Kami</a>
        </div>

        <div class="d-flex gap-4 mt-4 small text-secondary">
          <div><strong>7+</strong> multi-layer</div>
          <div><strong>Ti • Ta • W</strong> reinforced</div>
          <div><strong>2500nm</strong> spectrum block</div>
        </div>
      </div>

      <div class="col-lg-6 text-center">
        {{-- Ganti ke gambar hero kamu (mobil + roll film). Pakai asset() atau storage --}}
        <img src="{{ asset('img/hero-car.png') }}" alt="Acegard Car" class="hero-img">
      </div>
    </div>
  </div>
</section>
@endsection
