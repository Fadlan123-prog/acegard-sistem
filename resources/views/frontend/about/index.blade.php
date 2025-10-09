@extends('frontend.layout.index')

@once
    @push('styles')
        <link rel="stylesheet" href="{{asset('assets/frontend/css/bootstrap.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/meanmenu.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/owl.theme.default.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/owl.carousel.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/icofont.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/font-awesome-pro.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/scrollcue.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/magnific-popup.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/style.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/responsive.css')}}">
		<!-- Favicon -->
		<link rel="icon" type="image/png" href="{{asset('assets/frontend/images/acegard-favicon.png')}}">
    @endpush
@endonce

@section('content')
    <!--=== Start About Area (Acegard) ===-->
<div class="about-area ptb-100">
  <div class="container">
    <div class="row align-items-center">
      <!-- Gambar About -->
      <div class="col-lg-6">
        <div class="about-img-three">
          <img src="{{ asset('assets/frontend/building/img/photo/about-acegard.jpg') }}" alt="Tentang Acegard">
        </div>
      </div>

      <!-- Konten About -->
      <div class="col-lg-6">
        <div class="about-content-three">
          <span class="top-title">Tentang Kami</span>
          <h2>Acegard – Spesialis Kaca Film Mobil & Gedung</h2>

          <p>
            Acegard hadir untuk memberikan solusi perlindungan, kenyamanan, dan estetika terbaik
            melalui produk kaca film <strong>nano ceramic berkualitas tinggi</strong>. Dengan pengalaman
            bertahun-tahun, kami dipercaya oleh ribuan pelanggan, mulai dari hunian pribadi,
            perkantoran, hingga bangunan komersial.
          </p>

          <p>
            Kami berkomitmen menghadirkan <strong>produk premium</strong> dengan teknologi terkini:
            perlindungan panas maksimal, tolak UV hingga 99%, serta tampilan elegan yang menambah nilai
            estetika. Semua pemasangan dilakukan oleh tim profesional dengan garansi resmi 3–7 tahun.
          </p>

          <p>
            Acegard selalu mengutamakan <em>after-sales support</em>, memastikan setiap pelanggan
            mendapatkan layanan yang cepat, ramah, dan terpercaya.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!--=== End About Area ===-->

<!--=== Start Services Area (Acegard) ===-->
<div class="services-area pb-75">
  <div class="container">
    <div class="section-title" data-cues="slideInUp">
      <span class="top-title">Layanan Kami</span>
      <h2>Solusi Lengkap Kaca Film Acegard</h2>
    </div>

    <div class="row justify-content-center" data-cues="slideInUp">
      <div class="col-xl-3 col-sm-6">
        <div class="services-single-item style-two">
          <div class="icon">
            <img src="{{ asset('assets/frontend/images/car.svg') }}" alt="Kaca Film Mobil">
          </div>
          <h3>Kaca Film Mobil</h3>
          <p>Pilihan kaca film premium untuk mobil pribadi, SUV, hingga kendaraan mewah.</p>
        </div>
      </div>

      <div class="col-xl-3 col-sm-6">
        <div class="services-single-item style-two">
          <div class="icon">
            <img src="{{ asset('assets/frontend/images/building.svg') }}" alt="Kaca Film Gedung">
          </div>
          <h3>Kaca Film Gedung</h3>
          <p>Solusi hemat energi & privasi untuk kantor, rumah, ruko, dan bangunan komersial.</p>
        </div>
      </div>

      <div class="col-xl-3 col-sm-6">
        <div class="services-single-item style-two">
          <div class="icon">
            <img src="{{ asset('assets/frontend/images/card.svg') }}" alt="Garansi Resmi">
          </div>
          <h3>Garansi Resmi</h3>
          <p>Perlindungan 3–7 tahun untuk setiap pemasangan, menjamin kualitas & ketahanan.</p>
        </div>
      </div>

      <div class="col-xl-3 col-sm-6">
        <div class="services-single-item style-two">
          <div class="icon">
            <img src="{{ asset('assets/frontend/images/customer-service.svg') }}" alt="After Sales Service">
          </div>
          <h3>After Sales Service</h3>
          <p>Dukungan purna jual untuk memastikan kepuasan dan ketenangan jangka panjang.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<!--=== End Services Area ===-->

<!--=== Start Testimonials Area (Acegard) ===-->
<div class="testimonials-area-three overflow-hidden">
  <div class="container">
    <div class="row align-items-end">
      <div class="col-lg-8">
        <div class="section-title ms-0 text-start">
          <h2>Apa Kata Pelanggan</h2>
        </div>

        <div class="testimonial-slide-two owl-carousel owl-theme">
          <div class="testimonials-single-item-two style-three">
            <ul class="ps-0 list-unstyled d-flex gap-2 review">
              <li><i class="fa-solid fa-star"></i></li>
              <li><i class="fa-solid fa-star"></i></li>
              <li><i class="fa-solid fa-star"></i></li>
              <li><i class="fa-solid fa-star"></i></li>
              <li><i class="fa-solid fa-star"></i></li>
            </ul>
            <p>
              Pemasangan kaca film gedung cepat & rapi, ruangan jadi jauh lebih sejuk.
              Tim Acegard sangat profesional dan hasilnya memuaskan.
            </p>

            <div class="d-flex align-items-center info">
              <div class="flex-shrink-0">

              </div>
              <div class="flex-grow-1 ms-3">
                <h3>Bapak Andi</h3>
                <span>Pemilik Ruko, Jakarta</span>
              </div>
            </div>
          </div>

          <div class="testimonials-single-item-two style-three">
            <ul class="ps-0 list-unstyled d-flex gap-2 review">
              <li><i class="fa-solid fa-star"></i></li>
              <li><i class="fa-solid fa-star"></i></li>
              <li><i class="fa-solid fa-star"></i></li>
              <li><i class="fa-solid fa-star"></i></li>
              <li><i class="fa-solid fa-star"></i></li>
            </ul>
            <p>
              Mobil saya dipasang kaca film Acegard, hasilnya mantap. Siang hari tidak panas,
              malam tetap jelas. Recommended banget!
            </p>

            <div class="d-flex align-items-center info">
              <div class="flex-shrink-0">

              <div class="flex-grow-1 ms-3">
                <h3>Ibu Rina</h3>
                <span>Pengguna Mobil, Bandung</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gambar Testimonial -->
      <div class="col-lg-4">
        <div class="testimonial-img-three mt-4 mt-lg-0">

        </div>
      </div>
    </div>
  </div>
</div>
<!--=== End Testimonials Area ===-->

@endsection
