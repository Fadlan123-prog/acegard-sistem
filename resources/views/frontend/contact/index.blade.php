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
    <!--=== Start Page Banner Area ===-->
<div class="page-banner-area bg-black bg-img" data-background="./assets/images/banner-bg-shape.png">
  <div class="container mw-1680">
    <div class="page-banner-content">
      <h2>Kontak Kami</h2>
      <ul class="ps-0 mb-0 list-unstyled justify-content-center page-breadcrumb d-flex flex-wrap gap-4">
        <li>
          <a href="index.html">Home</a>
        </li>
        <li>
          <span class="active">Kontak</span>
        </li>
      </ul>
    </div>
  </div>
</div>
<!--=== End Page Banner Area ===-->

<!--=== Start Contact Us Area ===-->
<div class="contact-us-main-area ptb-100">
  <div class="container">
    <div class="contact-us-main-form">
      <h3>Kirim Pesan</h3>

      <div class="row">
        <div class="col-lg-8 col-xl-9">
          <form>
            <div class="row">
              <div class="col-lg-6">
                <div class="form-group mb-4">
                  <input type="text" class="form-control" placeholder="Nama Depan">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group mb-4">
                  <input type="text" class="form-control" placeholder="Nama Belakang">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group mb-4">
                  <input type="email" class="form-control" placeholder="Email Anda">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group mb-4">
                  <input type="text" class="form-control" placeholder="Subjek">
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group mb-4">
                  <textarea class="form-control" placeholder="Tulis pesan Anda..." cols="30" rows="5"></textarea>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group mb-0">
                  <button class="main-btn" type="submit">Kirim Pesan</button>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Info Kontak -->
        <div class="col-lg-4 col-xl-3">
          <div class="contact-info">
            <h3>Hubungi Kami</h3>
            <ul class="ps-0 mb-0 list-unstyled info-list">
              <li>
                <span>Alamat:</span>
                Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146
              </li>
              <li>
                <span>Telepon / WhatsApp:</span>
                <a href="https://wa.me/6282121212093" target="_blank">0821-2121-2093</a>
              </li>
              <li>
                <span>Email:</span>
                <a href="mailto:acegardindonesia@gmail.com">acegardindonesia@gmail.com</a>
              </li>
            </ul>

            <h3>Jam Operasional:</h3>
            <p>Setiap Hari: 09.00 – 18.00</p>

            <div class="social-link d-flex">
              <span>Ikuti Kami:</span>
              <div class="d-flex gap-3">
                <a href="https://www.facebook.com/acegard" target="_blank">
                  <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="https://www.instagram.com/acegard.id" target="_blank">
                  <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="https://www.tiktok.com/@acegard" target="_blank">
                  <i class="fa-brands fa-tiktok"></i>
                </a>
                <a href="https://www.youtube.com/@acegard" target="_blank">
                  <i class="fa-brands fa-youtube"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
        <!-- End Info Kontak -->
      </div>
    </div>
  </div>
</div>
<!--=== End Contact Us Area ===-->

<!--=== Start Map Area ===-->
<div class="map-area pb-100">
  <div class="container">
    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7932.866895562227!2d106.6881984!3d-6.2064166!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ed2a0d3377ef%3A0x49dc44beb8c49b02!2sAcegard%20Indonesia!5e0!3m2!1sid!2sid!4v1755761775295!5m2!1sid!2sid" width="100%" height="600" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
</div>
<!--=== End Map Area ===-->

@endsection
