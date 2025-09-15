@extends('frontend.layout.index')


@once
    @push('styles')
        <!-- GLightbox Preview -->
        <link rel="stylesheet" href="{{asset('assets/frontend/car/css/vendor/glightbox.min.css')}}">
        <!-- Animation On Scroll (AOS) -->
        <link rel="stylesheet" href="{{asset('assets/frontend/car/css/vendor/aos.css')}}">
        <!-- Bootstrap Icons CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <!-- Custom CSS -->
        <link rel="stylesheet" href="{{asset('assets/frontend/car/css/style.css')}}">
        <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
        <link rel="icon" type="image/png" href="{{asset('assets/frontend/images/acegard-favicon.png')}}">
    @endpush
@endonce

@section('content')
<!-- Hero section -->
<section class="section hero position-relative bg-size-cover bg-position-center bg-repeat-no-repeat py-5"
  style="background-image: url('{{asset('assets/frontend/car/images/hero.webp')}}');">
  <div class="bg-overlay"></div>
  <div class="b-container h-100 position-relative z-2">
    <div class="row">
      <div class="col-12 col-xl-8 d-flex flex-column justify-content-start text-center text-xl-start"
        style="padding-top: 23vh;">
        <h1 class="fw-bolder mb-4" data-aos="fade-right" data-aos-delay="500" data-aos-duration="3000">
          Kaca Film Mobil Acegard – Teknologi Nano Ceramic & Optical Film Terdepan
        </h1>
        <a href="services.php" class="btn btn-xl btn-cta-primary align-self-center align-self-xl-start mt-4">
          Pasang Sekarang</a>
      </div>
    </div>
  </div>
</section>
<!-- #hero end -->

<!-- Feature Section -->
<section class="feature position-relative">
  <div class="b-container">
    <div class="row d-flex justify-content-end align-items-end feature-box-wrapper g-0 mx-4">

      <!-- Penolakan Panas Maksimal -->
      <div class="col-12 col-md-6 col-xl-3 p-0">
        <div class="feature-box w-100 rounded-start-4" style="background-color: #303030;">
          <h5 class="feature-title">Penolakan Panas Hingga 100%</h5>
          <p>Kaca film mobil Acegard dilengkapi dengan teknologi <strong>multi-layer optical film</strong> yang mampu menolak panas inframerah secara maksimal, menjaga kabin tetap sejuk di siang hari.</p>
        </div>
      </div>

      <!-- Perlindungan Sinar UV Lengkap -->
      <div class="col-12 col-md-6 col-xl-3 p-0">
        <div class="feature-box w-100" style="background-color: #3F3F3F;">
          <h5 class="feature-title">Lindungi dari UVA, UVB, dan UVC</h5>
          <p>Dengan teknologi <strong>Nano Ceramic Matrix Composite</strong>, kaca film Acegard mampu menyaring hingga 100% sinar ultraviolet yang merusak kulit dan interior mobil Anda.</p>
        </div>
      </div>

      <!-- Aman & Ramah Sinyal -->
      <div class="col-12 col-xl-3 p-0">
        <div class="feature-box w-100 rounded-end-4" style="background-color: #4F4F4F;">
          <h5 class="feature-title">Jernih & Tidak Ganggu Sinyal</h5>
          <p>Tanpa bahan logam, kaca film Acegard tidak mengganggu sinyal GPS, Wi-Fi, maupun ponsel. Tetap jernih dari dalam, privat dari luar.</p>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- #feature end -->


<!-- About Section -->
<section class="section py-5 mt-5">
  <div class="b-container">
    <div class="row justify-content-between">
      <div class="col-12 col-xl-2 text-center text-xl-start mb-4 mb-xl-0">
        <h6 class="text-color-2">Tentang Kami</h6>
      </div>
      <div class="col-12 col-xl-10 text-center text-lg-start">
        <div class="row">
          <h2 class="heading text-secondary-color" data-aos="fade-right" data-aos-delay="400" data-aos-duration="2000">
            Kaca Film Mobil Acegard – Teknologi Kuat dari Material Terkokoh di Dunia
            <span class="text-primary-color">Tint & Tungsten untuk Perlindungan Maksimal</span>
          </h2>
        </div>
        <div class="row">
          <div class="col-12 col-xl-3 order-2 order-xl-1">
            <hr class="hr-style-1 mb-4 border-2">
          </div>
          <div class="col-12 col-xl-9 order-1 order-xl-2">
            <p class="text-color-2 mt-5">
              Acegard menggunakan material <strong>Tint berkualitas tinggi</strong> dan <strong>Tungsten</strong> — logam terkuat di bumi — sebagai fondasi teknologi kaca film mobil terbaik. Kombinasi ini menghadirkan daya tahan ekstrem terhadap panas, abrasi, dan sinar matahari intens. Dengan struktur <strong>Nano Ceramic Matrix Composite</strong> yang diperkuat, kaca film Acegard mampu menolak hingga 100% sinar inframerah serta menyaring spektrum penuh UVA, UVB, dan UVC.
              <br><br>
              Produk kami menjaga interior mobil tetap sejuk, melindungi penumpang dari risiko paparan radiasi, serta tidak mengganggu sinyal elektronik seperti GPS, radio, dan ponsel. Inilah standar baru kaca film untuk perlindungan jangka panjang yang elegan dan tangguh.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- #about end -->


<!-- Service Section -->
<section class="section py-5 my-5">
  <div class="b-container">
    <h6 class="text-color-2 text-center">PRODUK UNGGULAN KACA FILM MOBIL ACEGARD</h6>
    <h2 class="heading text-center text-secondary-color" data-aos="fade-up" data-aos-delay="400" data-aos-duration="3000">
      Pilihan Kaca Film dengan Teknologi Nano Ceramic & Tolak Panas 99%
    </h2>
    <p class="text-color-2 text-center my-4">
      Acegard menghadirkan serangkaian kaca film mobil yang dirancang untuk berbagai kebutuhan—dari visibilitas tinggi, keamanan, hingga tampilan mewah—semua dengan penolakan panas dan sinar ultraviolet maksimal.
    </p>

    <div class="row justify-content-center">
      <div class="col-12 text-center">
        <img src="{{asset('assets/frontend/car/images/audi-car.webp')}}" class="img-fluid" alt="Kaca film mobil Acegard untuk mobil sport"
          style="width: 100%; max-width: 900px;">
      </div>
    </div>

    <div class="row justify-content-center g-4">
      <!-- Acegard 4K Alpha Pro -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="service-box top-left position-relative h-100" data-aos="fade-right" data-aos-delay="400" data-aos-duration="3000">
          <div class="service-icon position-absolute">
            <i class="bi bi-arrow-right-short"></i>
          </div>
          <div class="p-3"></div>
          <h5 class="heading mt-5">Acegard 4K Alpha Pro</h5>
          <p class="text-color-2 my-4">
            Varian premium kaca film mobil dengan <strong>kejernihan ultra tinggi</strong> dan teknologi <strong>Nano Ceramic Matrix Composite</strong> yang menolak sinar UV hingga 100%, tanpa mengganggu sinyal GPS atau ponsel.
          </p>
        </div>
      </div>

      <!-- UV Protection -->
      <div class="col-12 col-md-6 col-xl-3 pe-xl-4">
        <div class="service-box position-relative h-100" data-aos="fade-up" data-aos-delay="400" data-aos-duration="3000">
          <div class="service-icon position-absolute">
            <i class="bi bi-arrow-right-short"></i>
          </div>
          <div class="p-3"></div>
          <h5 class="heading mt-5">UV Protection Series</h5>
          <p class="text-color-2 my-4">
            Menolak sinar UVA, UVB, dan UVC hingga <strong>99–100%</strong> untuk menjaga kulit dan interior mobil dari kerusakan akibat radiasi matahari.
          </p>
        </div>
      </div>

      <!-- Keamanan -->
      <div class="col-12 col-md-6 col-xl-3 ps-xl-4">
        <div class="service-box position-relative h-100" data-aos="fade-up" data-aos-delay="400" data-aos-duration="3000">
          <div class="service-icon position-absolute">
            <i class="bi bi-arrow-right-short"></i>
          </div>
          <div class="p-3"></div>
          <h5 class="heading mt-5">Safety & Security Film</h5>
          <p class="text-color-2 my-4">
            Kaca film pelindung dengan lapisan anti pecah yang <strong>mencegah penyebaran serpihan kaca</strong> saat terjadi benturan atau kecelakaan.
          </p>
        </div>
      </div>

      <!-- Estetika -->
      <div class="col-12 col-md-6 col-xl-3">
        <div class="service-box top-right p-4 position-relative h-100" data-aos="fade-left" data-aos-delay="500" data-aos-duration="3000">
          <div class="service-icon position-absolute">
            <i class="bi bi-arrow-right-short"></i>
          </div>
          <div class="p-3"></div>
          <h5 class="heading mt-5">Elegant Reflective Series</h5>
          <p class="text-color-2 my-4">
            Memberikan <strong>tampilan stylish dan profesional</strong> dengan efek reflektif elegan tanpa mengorbankan kejernihan pandangan dari dalam kabin.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- #service end -->


<!-- Warranty Section -->
<section class="section py-5 my-5">
  <div class="container-fluid bg-secondary-color rounded-4">
    <div class="b-container warranty">
      <div class="row">
        <div class="col-12 col-xl-3 d-flex flex-column text-center text-xl-start mb-4 mb-xl-0" style="min-height: 100%;">
          <h6 class="text-color-2">KENAPA PILIH ACEGARD?</h6>
          <div class="mt-auto">
            <h1 class="count-up heading text-primary-color display-1" data-count="90">0%</h1>
            <p class="text-color-2 mt-0 mt-xl-4">
              <strong>Lebih dari 90% pelanggan</strong> puas dan merekomendasikan kaca film mobil Acegard<br>
              untuk perlindungan & kenyamanan maksimal.
            </p>
          </div>
        </div>

        <div class="col-12 col-xl-9">
          <div class="row text-center text-xl-start">
            <h2 class="heading" data-aos="fade-right" data-aos-delay="400" data-aos-duration="3000">
              Inovasi, Kualitas Premium, dan<br>
              <span class="text-primary-color">Perlindungan Total untuk Mobil Anda</span>
            </h2>
          </div>

          <div class="row text-center text-xl-start mt-4">
            <div class="col-12 col-xl-2">
              <h4 class="heading">Keunggulan Acegard</h4>
            </div>
            <div class="col-12 col-xl-10">
              <p class="text-color-2">
                Kami menggabungkan teknologi kaca film terbaru seperti <strong>multi-layer optical film</strong> dan <strong>Nano Ceramic Matrix Composite</strong> untuk memberikan pengalaman berkendara yang lebih nyaman, aman, dan bergaya. Dengan perlindungan maksimal dari panas dan sinar UV, mobil Anda tetap sejuk dan interior lebih awet.
              </p>
            </div>
          </div>

          <div class="row mt-5">
            <!-- Item 1 -->
            <div class="col-12 col-md-6" data-aos="fade-up" data-aos-delay="400" data-aos-duration="3000">
              <h6 class="text-primary-color fw-medium">01</h6>
              <hr class="hr-style-1 my-4 border-2">
              <h5>Teknologi Penolak Panas</h5>
              <p class="text-color-2 my-4">
                Dengan kemampuan <strong>infrared rejection hingga 100%</strong>, kaca film Acegard menjaga kabin tetap sejuk meskipun di bawah terik matahari.
              </p>
            </div>

            <!-- Item 2 -->
            <div class="col-12 col-md-6" data-aos="fade-up" data-aos-delay="400" data-aos-duration="3000">
              <h6 class="text-primary-color fw-medium">02</h6>
              <hr class="hr-style-1 my-4 border-2">
              <h5>Perlindungan Sinar UV Lengkap</h5>
              <p class="text-color-2 my-4">
                Menahan <strong>sinar UVA, UVB, dan UVC hingga 100%</strong> – melindungi kulit dan memperpanjang usia interior kendaraan Anda.
              </p>
            </div>

            <!-- Item 3 -->
            <div class="col-12 col-md-6" data-aos="fade-up" data-aos-delay="500" data-aos-duration="3000">
              <h6 class="text-primary-color fw-medium">03</h6>
              <hr class="hr-style-1 my-4 border-2">
              <h5>Tidak Mengganggu Sinyal</h5>
              <p class="text-color-2 my-4">
                Berkat teknologi non-logam, kaca film Acegard tetap <strong>aman untuk sinyal GPS, WiFi, dan ponsel</strong> Anda.
              </p>
            </div>

            <!-- Item 4 -->
            <div class="col-12 col-md-6" data-aos="fade-up" data-aos-delay="600" data-aos-duration="3000">
              <h6 class="text-primary-color fw-medium">04</h6>
              <hr class="hr-style-1 my-4 border-2">
              <h5>Garansi & Kepuasan Pelanggan</h5>
              <p class="text-color-2 my-4">
                Semua pemasangan didukung <strong>garansi resmi dan layanan profesional</strong>. Kami menjamin hasil yang elegan dan fungsional.
              </p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
<!-- #warranty end -->


<!-- Appointment Section -->
<section class="section bg-secondary-color" style="padding: 10vh 0;">
  <div class="b-container">
    <div class="row g-5 mt-0 mt-xl-5 mx-auto">

      <!-- Informasi Proses -->
      <div class="col-12 col-xl-6 pt-0 mt-0">
        <h6 class="text-color-2">CARA PEMASANGAN</h6>
        <h2 class="heading" data-aos="fade-right" data-aos-delay="400" data-aos-duration="3000">
          Pemasangan Kaca Film Mobil Acegard dalam 3 Langkah Mudah
        </h2>
        <div class="mt-4">
          <a href="contact-us.php" class="btn btn-lg btn-cta-primary">Hubungi Kami Sekarang</a>
        </div>

        <div class="accordion mt-5" id="accordionExample">
          <!-- Step 1 -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                01. Pilih Jenis & Jadwalkan Pemasangan
              </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
              data-bs-parent="#accordionExample">
              <div class="accordion-body-2">
                <p>Pilih tipe kaca film Acegard yang sesuai dengan kebutuhan Anda—baik untuk perlindungan UV, tampilan elegan, atau keamanan tambahan. Kemudian, tentukan jadwal pemasangan di lokasi kami atau melalui layanan home service.</p>
              </div>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                02. Pemasangan oleh Teknisi Profesional
              </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
              data-bs-parent="#accordionExample">
              <div class="accordion-body-2">
                <p>Tim teknisi kami yang berpengalaman akan memasang kaca film menggunakan alat dan metode presisi tinggi. Proses pemasangan cepat, bersih, dan tidak merusak kaca mobil Anda.</p>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                03. Nikmati Kabin Sejuk & Tampilan Baru
              </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
              data-bs-parent="#accordionExample">
              <div class="accordion-body-2">
                <p>Setelah pemasangan selesai, mobil Anda akan terlihat lebih stylish, kabin terasa lebih sejuk, dan perlindungan UV sudah aktif sepenuhnya. Nikmati kenyamanan berkendara yang lebih optimal bersama kaca film Acegard.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gambar dan Quote -->
      <div class="col-12 col-xl-6 mt-5 mt-xl-0">
        <div class="img-wrapper position-relative mx-auto">
          <div class="img-ratio-121">
            <img src="{{asset('assets/frontend/images/home2.webp')}}" alt="Proses pemasangan kaca film mobil Acegard"
              class="w-100 h-100 position-absolute rounded-4" style="inset: 0;">
          </div>
          <div class="position-absolute info-lb-overlay" data-aos="fade-up" data-aos-delay="400" data-aos-duration="3000">
            <div class="d-flex flex-row text-white p-4 align-items-center">
              <h4 class="mb-2"><em>“Mobil Anda layak mendapatkan perlindungan terbaik. Kaca film Acegard memberikan kenyamanan, privasi, dan perlindungan dari sinar matahari yang tak tertandingi.”</em></h4>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- #appointment end -->


<!-- Testimonial Section -->
<section class="section reviews position-relative text-white py-5">
  <!-- Background Video -->
  <img src="{{asset('assets/frontend/car/images/experience-img.webp')}}" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover z-n1" alt="">
  {{-- <video autoplay muted loop playsinline class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover z-n1">
    <source src="{{ asset('assets/frontend/car/videos/video-background.mp4')}}" type="video/mp4">
  </video> --}}

  <!-- Background Overlay -->
  <div class="position-absolute w-100 h-100 top-0 start-0 z-0"
    style="background: linear-gradient(to top,#111111 50%,#111111 65%, transparent)">
  </div>

  <!-- Testimonials Content -->
  <div class="b-container position-relative z-1 my-5">
    <h6 class="text-color-2 text-center pt-4">APA KATA MEREKA</h6>
    <h2 class="heading text-center mt-2" data-aos="fade-up" data-aos-delay="400" data-aos-duration="3000">
      Pengalaman <span class="text-primary-color">Pelanggan Acegard</span><br>Yang Nyata & Terbukti
    </h2>

    <div class="row g-4 g-xl-5 my-5 pb-5">
      <!-- Testimonial Cards -->
      <div class="col-12 col-md-6 col-xl-4" data-aos="fade-right" data-aos-delay="400" data-aos-duration="3000">
        <div class="d-flex flex-column h-100 gap-4">
          <div class="card p-4">
            <p>Saya memilih Acegard karena perlindungan panasnya, dan hasilnya luar biasa. Kabin mobil jadi lebih sejuk, dan tampilan kaca makin elegan. Proses pemasangan pun sangat rapi!</p>
            <div class="d-flex align-items-center mt-auto pt-3">
              <img src="{{asset('assets/frontend/car/images/Place-Holder-64x64.jpg')}}" alt="Raul Axios" class="rounded-circle me-3" width="60" height="60">
              <div>
                <h5 class="mb-0">Raul Axios</h5>
                <small class="text-color-2">Auto Dealer</small>
              </div>
            </div>
          </div>

          <div class="card p-4">
            <p>Pernah pasang kaca film di tempat lain, tapi Acegard jauh lebih unggul. Nano Ceramic-nya benar-benar efektif menolak panas. Pemasangannya cepat, dan tidak mengganggu sinyal ponsel.</p>
            <div class="d-flex align-items-center mt-auto pt-3">
              <img src="{{asset('assets/frontend/car/images/Place-Holder-64x64.jpg')}}" alt="Ubeid Una" class="rounded-circle me-3" width="60" height="60">
              <div>
                <h5 class="mb-0">Ubeid Una</h5>
                <small class="text-color-2">Car Owner</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="800" data-aos-duration="3000">
        <div class="d-flex flex-column h-100 gap-4">
          <div class="card p-4">
            <p>Interior mobil saya jadi lebih awet karena terlindungi dari sinar UV. Acegard berhasil memberikan hasil yang tidak hanya cantik tapi juga fungsional. Saya sangat merekomendasikan!</p>
            <div class="d-flex align-items-center mt-4">
              <img src="{{asset('assets/frontend/car/images/Place-Holder-64x64.jpg')}}" alt="Taki Wanabe" class="rounded-circle me-3" width="60" height="60">
              <div>
                <h5 class="mb-0">Taki Wanabe</h5>
                <small class="text-color-2">Vintage Car Owner</small>
              </div>
            </div>
          </div>

          <div class="card p-4">
            <p>Pelayanannya ramah dan teknisinya profesional. Pemasangan kaca film Acegard di mobil keluarga saya benar-benar memuaskan. Kabin jadi adem, privasi juga lebih terjaga.</p>
            <div class="d-flex align-items-center mt-auto pt-3">
              <img src="{{asset('assets/frontend/car/images/Place-Holder-64x64.jpg')}}" alt="Hafsha Jasmine" class="rounded-circle me-3" width="60" height="60">
              <div>
                <h5 class="mb-0">Hafsha Jasmine</h5>
                <small class="text-color-2">Parent & Driver</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4" data-aos="fade-left" data-aos-delay="550" data-aos-duration="3000">
        <div class="d-flex flex-column h-100 gap-4">
          <div class="card p-4">
            <p>Setelah pasang kaca film Acegard, mobil saya tampak lebih eksklusif. Efek reflektifnya pas, tidak berlebihan. Harganya juga sebanding dengan kualitas dan garansi yang diberikan.</p>
            <div class="d-flex align-items-center mt-auto pt-3">
              <img src="{{asset('assets/frontend/car/images/Place-Holder-64x64.jpg')}}" alt="Akio Mirfaq" class="rounded-circle me-3" width="60" height="60">
              <div>
                <h5 class="mb-0">Akio Mirfaq</h5>
                <small class="text-color-2">Car Owner</small>
              </div>
            </div>
          </div>

          <div class="card p-4">
            <p>Setiap perjalanan terasa lebih nyaman sekarang. Kaca film Acegard benar-benar membuat perbedaan dalam kenyamanan dan tampilan. Pasti akan saya rekomendasikan ke teman-teman!</p>
            <div class="d-flex align-items-center mt-auto pt-3">
              <img src="{{asset('assets/frontend/car/images/Place-Holder-64x64.jpg')}}" alt="Olivia Seamo" class="rounded-circle me-3" width="60" height="60">
              <div>
                <h5 class="mb-0">Olivia Seamo</h5>
                <small class="text-color-2">Car Collector</small>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- #testimonial end -->


<!-- Pricing Section -->
<section class="section py-5 my-5">
  <div class="b-container">
    <div class="row text-center text-lg-start">
      <div class="col-12 col-xl-7">
        <h6 class="text-color-2">PAKET HARGA</h6>
        <h2 class="heading text-secondary-color" data-aos="fade-right" data-aos-delay="400" data-aos-duration="3000">
          Pilih Paket Pemasangan <br>Kaca Film Acegard Terbaik untuk Mobil Anda
        </h2>
      </div>
      <div class="col-12 col-xl-5 pt-4">
        <p class="text-color-2">
          Tersedia berbagai pilihan paket kaca film mobil Acegard yang dirancang untuk kebutuhan Anda — dari perlindungan dasar hingga tampilan premium. Semua paket dilengkapi teknologi penolak panas & UV terbaik dengan hasil pemasangan rapi dan bergaransi.
        </p>
      </div>
    </div>

    <div class="row g-4 mt-5 justify-content-center">
      <!-- Paket Premium -->
      <div class="col-12 col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="800" data-aos-duration="3000">
        <div class="card bg-secondary-color text-white rounded-4 p-4">
          <div class="d-flex justify-content-between align-items-center">
            <h4>NOTCH UV400</h4>
          </div>
          <div class="heading">
            <h5 class="mt-4">Mulai dari</h5>
            <h2 class="fw-bolder text-primary-color">Rp 1.900.000</h2>
          </div>
          <p class="text-color-2 mt-4">Kaca film nano ceramic dengan night vision dan perlindungan sinar UV hingga 99,9%.</p>
          <a href="contact-us.html" class="btn btn-lg btn-cta-primary my-3">Pilih Paket Ini</a>
          <div class="row bg-accent-color-2 rounded-4 mt-4 py-4 px-2">
            <h4 class="heading">Spesifikasi Utama:</h4>
            <ul class="list-unstyled text-color-2">
              <li class="mt-3"><i class="bi bi-check-square-fill text-primary-color me-2"></i>IR Rejection hingga 99%</li>
              <li class="mt-3"><i class="bi bi-check-square-fill text-primary-color me-2"></i>UV Rejection 99,9%</li>
              <li class="mt-3"><i class="bi bi-check-square-fill text-primary-color me-2"></i>TSER hingga 82%</li>
              <li class="mt-3"><i class="bi bi-check-square-fill text-primary-color me-2"></i>Tersedia dalam berbagai kegelapan: 20%, 30%, 40%, 60%, 80%</li>
            </ul>
          </div>
          <p class="text-color-2 mt-4"><strong>Cocok untuk:</strong> Mobil pribadi, keluarga, dan operasional yang butuh kenyamanan siang-malam.</p>
        </div>
      </div>

      <!-- Paket Ultimate -->
      <div class="col-12 col-xl-4" data-aos="fade-left" data-aos-delay="650" data-aos-duration="3000">
        <div class="card bg-secondary-color text-white rounded-4 p-4">
          <div class="d-flex justify-content-between align-items-center">
            <h4>4K ALPHA PRO</h4>
          </div>
          <div class="heading">
            <h5 class="mt-4">Mulai dari</h5>
            <h2 class="fw-bolder text-primary-color">Rp 2.800.000</h2>
          </div>
          <p class="text-color-2 mt-4">Kaca film double nano ceramic dengan performa penolakan panas tertinggi dan tampilan mewah.</p>
          <a href="contact-us.html" class="btn btn-lg btn-cta-primary my-3">Pilih Paket Ini</a>
          <div class="row bg-accent-color-2 rounded-4 mt-4 py-4 px-2">
            <h4 class="heading">Spesifikasi Utama:</h4>
            <ul class="list-unstyled text-color-2">
              <li class="mt-3"><i class="bi bi-check-square-fill text-primary-color me-2"></i>IR Rejection hingga 99,9%</li>
              <li class="mt-3"><i class="bi bi-check-square-fill text-primary-color me-2"></i>UV Rejection 99,9%</li>
              <li class="mt-3"><i class="bi bi-check-square-fill text-primary-color me-2"></i>TSER hingga 95%</li>
              <li class="mt-3"><i class="bi bi-check-square-fill text-primary-color me-2"></i>Tersedia dalam 4 tingkat kegelapan: 20%, 40%, 60%, 80%</li>
            </ul>
          </div>
          <p class="text-color-2 mt-4"><strong>Cocok untuk:</strong> Mobil premium & enthusiast yang mengutamakan kenyamanan dan ketahanan.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- #pricing end -->



<!-- CTA Section -->
<section class="section position-relative"
  style="background-image: url('/assets/frontend/car/images/cta-img.webp'); background-attachment: fixed;">
  <div class="bg-overlay"></div>
  <div class="b-container position-relative z-2 my-5 my-xl-0">
    <div class="d-flex flex-column align-items-center mx-auto text-center text-white font-2 gap-4"
      style="max-width: 900px;">
      <h2 class="fw-bolder">Tingkatkan Tampilan & Kenyamanan Mobil Anda dengan Kaca Film Acegard</h2>
      <h4 class="fw-semibold">Booking Sekarang dan Dapatkan <span class="text-primary-color">Diskon Hingga 30%</span></h4>
      <a href="contact-us.php" class="btn btn-lg btn-cta-primary">Pesan Sekarang</a>
    </div>
  </div>
</section>
<!-- #cta end -->

<!-- FAQs Section -->
<section class="section py-5 my-5">
  <div class="b-container">
    <div class="row d-flex align-items-end g-md-5">
      <div class="col-12 col-xl-4 text-center text-xl-end order-2">
        <p class="text-color-2">Masih punya pertanyaan? Temukan jawabannya di sini atau <span class="text-primary-color">hubungi kami</span></p>
      </div>
      <div class="col-12 col-xl-8 text-center text-xl-start order-1 order-xl-2">
        <h2 class="fw-bolder text-secondary-color" data-aos="fade-left" data-aos-delay="400" data-aos-duration="3000">Pertanyaan Umum Seputar <span class="text-primary-color">Kaca Film Mobil Acegard</span></h2>
      </div>
    </div>

    <div class="row mt-4 g-md-5">
      <!-- Vertical Tabs -->
      <div class="col-12 col-md-4">
        <nav class="nav flex-column nav-pills" id="faq-tabs" role="tablist" aria-orientation="vertical">
          <button class="nav-link active" id="faq-general-tab" data-bs-toggle="pill" data-bs-target="#faq-general"
            type="button" role="tab" aria-controls="faq-general" aria-selected="true">
            Umum
          </button>
          <button class="nav-link" id="faq-services-tab" data-bs-toggle="pill" data-bs-target="#faq-services"
            type="button" role="tab" aria-controls="faq-services" aria-selected="false">
            Produk & Proses
          </button>
          <button class="nav-link" id="faq-pricing-tab" data-bs-toggle="pill" data-bs-target="#faq-pricing"
            type="button" role="tab" aria-controls="faq-pricing" aria-selected="false">
            Harga & Pembayaran
          </button>
        </nav>
      </div>

      <!-- Tab Content Area -->
      <div class="col-12 col-md-8">
        <div class="tab-content" id="faq-tab-content" data-aos="fade-left" data-aos-delay="650" data-aos-duration="3000">

          <!-- UMUM -->
          <div class="tab-pane fade show active" id="faq-general" role="tabpanel" aria-labelledby="faq-general-tab">
            <div class="accordion" id="accordionGeneral">
              <div class="accordion-item">
                <h5 class="accordion-header" id="gen-heading-1">
                  <button class="accordion-button text-secondary-color" type="button" data-bs-toggle="collapse" data-bs-target="#gen-collapse-1" aria-expanded="true" aria-controls="gen-collapse-1">
                    Apa itu kaca film mobil?
                  </button>
                </h5>
                <div id="gen-collapse-1" class="accordion-collapse collapse show" aria-labelledby="gen-heading-1" data-bs-parent="#accordionGeneral">
                  <div class="accordion-body">
                    <p>Kaca film mobil adalah lapisan tipis yang ditempelkan pada kaca mobil untuk menolak panas matahari, mengurangi silau, menjaga privasi, dan meningkatkan tampilan kendaraan.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h5 class="accordion-header" id="gen-heading-2">
                  <button class="accordion-button text-secondary-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gen-collapse-2" aria-expanded="false" aria-controls="gen-collapse-2">
                    Mengapa saya harus menggunakan kaca film Acegard?
                  </button>
                </h5>
                <div id="gen-collapse-2" class="accordion-collapse collapse" aria-labelledby="gen-heading-2" data-bs-parent="#accordionGeneral">
                  <div class="accordion-body">
                    <p>Acegard menggunakan teknologi terkini untuk memberikan perlindungan maksimal dari panas, sinar UV, serta menjaga privasi tanpa mengorbankan visibilitas dan estetika.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h5 class="accordion-header" id="gen-heading-3">
                  <button class="accordion-button text-secondary-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gen-collapse-3" aria-expanded="false" aria-controls="gen-collapse-3">
                    Apakah kaca film Acegard aman untuk visibilitas malam hari?
                  </button>
                </h5>
                <div id="gen-collapse-3" class="accordion-collapse collapse" aria-labelledby="gen-heading-3" data-bs-parent="#accordionGeneral">
                  <div class="accordion-body">
                    <p>Ya, kaca film Acegard dirancang dengan tingkat kegelapan yang ideal dan teknologi optik jernih agar tetap nyaman digunakan siang maupun malam hari.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PRODUK & PROSES -->
          <div class="tab-pane fade" id="faq-services" role="tabpanel" aria-labelledby="faq-services-tab">
            <div class="accordion" id="accordionServices">
              <div class="accordion-item">
                <h5 class="accordion-header" id="ser-heading-1">
                  <button class="accordion-button text-secondary-color" type="button" data-bs-toggle="collapse" data-bs-target="#ser-collapse-1" aria-expanded="true" aria-controls="ser-collapse-1">
                    Apakah semua jenis mobil bisa dipasangi kaca film Acegard?
                  </button>
                </h5>
                <div id="ser-collapse-1" class="accordion-collapse collapse show" aria-labelledby="ser-heading-1" data-bs-parent="#accordionServices">
                  <div class="accordion-body">
                    <p>Ya, kaca film Acegard tersedia untuk semua jenis mobil, mulai dari city car, SUV, MPV hingga kendaraan premium.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h5 class="accordion-header" id="ser-heading-2">
                  <button class="accordion-button text-secondary-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ser-collapse-2" aria-expanded="false" aria-controls="ser-collapse-2">
                    Apakah tersedia layanan pemasangan di rumah (home service)?
                  </button>
                </h5>
                <div id="ser-collapse-2" class="accordion-collapse collapse" aria-labelledby="ser-heading-2" data-bs-parent="#accordionServices">
                  <div class="accordion-body">
                    <p>Ya, Acegard menyediakan layanan home service tanpa biaya tambahan hingga radius 40 km dari lokasi kami. Untuk jarak lebih jauh, akan dikenakan biaya transportasi tambahan.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h5 class="accordion-header" id="ser-heading-3">
                  <button class="accordion-button text-secondary-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ser-collapse-3" aria-expanded="false" aria-controls="ser-collapse-3">
                    Berapa lama proses pemasangan kaca film mobil?
                  </button>
                </h5>
                <div id="ser-collapse-3" class="accordion-collapse collapse" aria-labelledby="ser-heading-3" data-bs-parent="#accordionServices">
                  <div class="accordion-body">
                    <p>Rata-rata pemasangan kaca film membutuhkan waktu 1–2 jam, tergantung tipe mobil dan jumlah kaca yang dipasangi.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- HARGA & PEMBAYARAN -->
          <div class="tab-pane fade" id="faq-pricing" role="tabpanel" aria-labelledby="faq-pricing-tab">
            <div class="accordion" id="accordionPricing">
              <div class="accordion-item">
                <h5 class="accordion-header" id="pri-heading-1">
                  <button class="accordion-button text-secondary-color" type="button" data-bs-toggle="collapse" data-bs-target="#pri-collapse-1" aria-expanded="true" aria-controls="pri-collapse-1">
                    Berapa harga pemasangan kaca film Acegard?
                  </button>
                </h5>
                <div id="pri-collapse-1" class="accordion-collapse collapse show" aria-labelledby="pri-heading-1" data-bs-parent="#accordionPricing">
                  <div class="accordion-body">
                    <p>Harga bervariasi tergantung pada tipe kendaraan dan jenis kaca film yang dipilih. Hubungi kami untuk konsultasi dan penawaran harga terbaik sesuai kebutuhan Anda.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h5 class="accordion-header" id="pri-heading-2">
                  <button class="accordion-button text-secondary-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pri-collapse-2" aria-expanded="false" aria-controls="pri-collapse-2">
                    Apa metode pembayaran yang tersedia?
                  </button>
                </h5>
                <div id="pri-collapse-2" class="accordion-collapse collapse" aria-labelledby="pri-heading-2" data-bs-parent="#accordionPricing">
                  <div class="accordion-body">
                    <p>Kami menerima pembayaran tunai, transfer bank, QRIS, dan kartu debit/kredit untuk kemudahan Anda.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h5 class="accordion-header" id="pri-heading-3">
                  <button class="accordion-button text-secondary-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pri-collapse-3" aria-expanded="false" aria-controls="pri-collapse-3">
                    Apakah ada garansi untuk produk kaca film?
                  </button>
                </h5>
                <div id="pri-collapse-3" class="accordion-collapse collapse" aria-labelledby="pri-heading-3" data-bs-parent="#accordionPricing">
                  <div class="accordion-body">
                    <p>Ya, semua produk Acegard dilengkapi garansi resmi hingga 5 tahun tergantung tipe produk yang Anda pilih.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
<!-- #faqs end -->

<script src="{{asset('assets/frontend/car/js/vendor/jquery.min.js')}}"></script>
<!-- GLightbox Preview -->
<script src="{{asset('assets/frontend/car/js/vendor/glightbox.min.js')}}"></script>
<!-- Animation On Scroll (AOS) -->
<script src="{{asset('assets/frontend/car/js/vendor/aos.js')}}"></script>
<!-- Bootstrap JS -->
<script src="{{asset('assets/frontend/car/js/vendor/bootstrap.bundle.min.js')}}"></script>
<!-- Custom JS -->
<script src="{{asset('assets/frontend/car/js/script.js')}}"></script>
<!-- End Scripts -->

  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>

@endsection
