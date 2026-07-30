<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-UA-Compatible" content="ie=edge"/>

  <title>Promo Kaca Film Gedung | Acegard</title>
  <meta name="description" content="Promo kaca film gedung Acegard. Konsultasi & survei gratis, garansi resmi, pemasangan rapi. Hubungi WhatsApp untuk penawaran terbaik."/>
  <meta name="keywords" content="kaca film gedung, kaca film bangunan, promo kaca film, Acegard, tolak panas, UV protection, night vision"/>

  <meta property="og:title" content="Promo Kaca Film Gedung | Acegard"/>
  <meta property="og:description" content="Upgrade kenyamanan & estetika gedung Anda dengan kaca film Acegard. Konsultasi & survei gratis."/>
  <meta property="og:type" content="website"/>
  <meta property="og:image" content="{{ asset('assets/frontend/building/img/og/acegard-building.jpg') }}"/>

  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/plugins/bootstrap-grid.css') }}"/>
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/plugins/font-awesome.min.css') }}"/>
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/plugins/swiper.min.css') }}"/>
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/plugins/magnific-popup.css') }}"/>
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/style.css') }}"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


  <link rel="icon" type="image/png" href="{{ asset('assets/frontend/images/acegard-favicon.png') }}"/>
  
  

  <style>
    /* ===== Minor tune for landing ===== */
    .mil-banner .mil-overlay{
      background:linear-gradient(180deg,rgba(0,0,0,.45),rgba(0,0,0,.65));
    }
    .badge-list{display:flex;flex-wrap:wrap;gap:.6rem}
    .badge{background:#0d0f10;color:#fff;border:1px solid rgba(255,255,255,.12);border-radius:999px;padding:.4rem .8rem;font-size:.9rem}
    .feature-list{list-style:none;margin:0;padding:0}
    .feature-list li{display:flex;gap:.6rem;align-items:flex-start;margin:.4rem 0}
    .feature-list i{margin-top:.2rem}

    /* Buttons - emphasize */
    .cta-main{
      --c: #3ffc3f;
      background: var(--c);
      color:#071407;
      border:none;
      padding: .95rem 1.35rem;
      border-radius: 12px;
      font-weight:800;
      letter-spacing:.2px;
      box-shadow:0 10px 24px rgba(63,252,63,.25), 0 2px 0 rgba(0,0,0,.06) inset;
      transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
    }
    .cta-main:focus{outline:2px solid #00c853; outline-offset:2px}
    .cta-main:hover{transform:translateY(-1px); filter:saturate(1.15); box-shadow:0 16px 34px rgba(63,252,63,.32)}
    .cta-main .fa{margin-right:.5rem}

    .cta-secondary{
      background:#101316;
      color:#dfe6e9;
      border:1px solid rgba(255,255,255,.14);
      padding:.9rem 1.2rem;
      border-radius:12px;
      font-weight:700;
      transition: background .12s ease, transform .12s ease;
    }
    .cta-secondary:hover{transform:translateY(-1px); background:#151a1f}

    /* Cards & sections */
    .mil-pricing{border:1px solid rgba(0,0,0,.08);border-radius:18px;padding:24px;background:#fff}
    .mil-pricing .price{font-size:1.9rem;font-weight:800}
    .mil-soft-bg{background:#fbfbfb}

    /* FAQ accordion – keep template style, make edges softer */
    .mil-accordion-group{border-radius:14px; overflow:hidden}

    /* Small helpers for spacing in hero badges */
    .hero-badges{display:flex;flex-wrap:wrap;gap:.6rem}

    /* Gallery: keep original grid variants */
    .mil-portfolio-item{position:relative; overflow:hidden; border-radius:16px}
    .mil-portfolio-item img{display:block; width:100%; height:100%}
    .mil-square-item img{aspect-ratio:1/1; object-fit:cover}
    .mil-long-item img{aspect-ratio:3/4; object-fit:cover}
    .mil-project-descr{
      position:absolute; left:14px; bottom:14px; right:14px;
      padding:12px 14px; border-radius:12px;
      background:linear-gradient(180deg, rgba(0,0,0,.0), rgba(0,0,0,.45));
      color:#fff;
    }

    /* Section titles center spacing */
    .mil-center h2{letter-spacing:.3px}

    /* ===== Floating WhatsApp Button ===== */
    .wa-floating{
      position:fixed;
      right:18px;
      bottom:18px;
      z-index:1100;
      display:flex;
      align-items:center;
      gap:10px;
      text-decoration:none;
      user-select:none;
    }
    .wa-bubble{
      width:56px; height:56px; border-radius:999px;
      background:#25D366; color:#fff;
      display:grid; place-items:center;
      box-shadow:0 12px 24px rgba(37,211,102,.35), inset 0 2px 0 rgba(255,255,255,.25);
      transition:transform .12s ease, box-shadow .12s ease, filter .12s ease;
    }
    .wa-bubble i{font-size:26px}
    .wa-label{
      background:#101316; color:#fff; font-weight:800;
      padding:.6rem .8rem; border-radius:12px;
      border:1px solid rgba(255,255,255,.12);
      display:none; white-space:nowrap;
    }
    .wa-floating:hover .wa-bubble{
      transform:translateY(-1px);
      filter:saturate(1.1);
      box-shadow:0 18px 34px rgba(37,211,102,.45), inset 0 2px 0 rgba(255,255,255,.25);
    }
    @media (min-width: 992px){
      .wa-label{display:block}
    }
    @media (prefers-reduced-motion: reduce){
      .wa-bubble{transition:none}
    }
  </style>
  
  <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5DH3T7FD');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    
<div class="mil-wrapper">

  <!-- Top bar -->
  <div class="mil-top-panel">
    <div class="container-fluid">
      <div class="mil-top-panel-content">
        <a href="{{ route('frontend.service.building') }}" class="mil-logo">
          <img src="{{ asset('assets/images/acegard-logo.svg') }}" alt="Acegard" style="width:130px"/>
        </a>
      </div>
    </div>
  </div>

  <div id="content">

    <!-- HERO -->
    <section id="promo" class="mil-banner" aria-label="Promo Kaca Film Gedung">
      <img src="{{ asset('assets/frontend/building/img/photo/building-10.jpg') }}"
           class="mil-bg-img mil-scale"
           data-value-1=".9" data-value-2="1.2"
           alt="Promo Kaca Film Gedung Acegard"/>
      <div class="mil-overlay"></div>

      <div class="container">
        <div class="mil-background-grid mil-top-space"></div>
        <div class="mil-banner-content">
          <div class="row align-items-end">
            <div class="col-xl-7">
              <div class="mil-mb-60">
                <span class="mil-suptitle mil-light mil-upper mil-mb-30">Promo Building</span>
                <h1 class="mil-upper mil-light mil-mb-20">
                  Kaca Film <span class="mil-accent">Gedung</span><br/>
                  Tolak Panas Maksimal, Ruang Lebih Sejuk
                </h1>
                <p class="mil-light-soft mil-mb-25">
                  Kurangi panas & silau, hemat energi, dan tingkatkan kenyamanan gedung
                  dengan pemasangan kaca film profesional <strong>Acegard</strong>.
                </p>

                <div class="hero-badges mil-mb-35" style="margin: 10px 0px;">
                  <span class="badge">Tolak Panas s/d 99%</span>
                  <span class="badge">UV Rejection 99%</span>
                  <span class="badge">Konsultasi & Survei Gratis</span>
                  <span class="badge">Pemasangan Rapi</span>
                </div>

                <div class="whatsapp-grid" style="display:flex;gap:.75rem;flex-wrap:wrap">
                  <a class="cta-main whatsapp-btn"
                     href="https://wa.me/628212003331?text=Halo%20Acegard%2C%20saya%20tertarik%20dengan%20promo%20kaca%20gedungnya"
                     target="_blank" rel="noopener" aria-label="Chat WhatsApp 0821-2111-2093">
                     Hubungi Sekarang
                  </a>
                  <a class="cta-secondary" href="#harga">Lihat Paket Harga</a>
                </div>
              </div>
            </div>

            <div class="col-xl-5">
              <div class="row mil-mb-30">
                <div class="col-6">
                  <div class="mil-counter-frame mil-light mil-mb-20">
                    <h4 class="mil-accent mil-thin mil-mb-10"><span class="mil-counter" data-number="500">0</span>+</h4>
                    <p class="mil-light">Proyek Gedung</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="mil-counter-frame mil-light mil-mb-20">
                    <h4 class="mil-accent mil-thin mil-mb-10"><span class="mil-counter" data-number="7">0</span> th</h4>
                    <p class="mil-light">Dukungan Garansi*</p>
                  </div>
                </div>
                <div class="col-12">
                  <p class="mil-light-soft" style="font-size:.8rem">*Detail garansi mengikuti ketentuan produk & invoice.</p>
                </div>
              </div>
            </div>

          </div><!-- row -->
        </div>
      </div>
    </section>
    <!-- HERO end -->

    <!-- KEUNGGULAN (Kenapa Acegard) -->
    <section aria-label="Keunggulan Kaca Film Gedung">
      <div class="container mil-p-120-60">
        <div class="mil-background-grid mil-softened"></div>
        <div class="row justify-content-between align-items-center">
          <div class="col-lg-6 mil-mb-30">
            <span class="mil-suptitle mil-upper mil-up mil-mb-20">Kenapa Acegard</span>
            <h2 class="mil-upper mil-up mil-mb-20">Lebih Sejuk, Hemat Energi, Berkelas</h2>

            <p class="mil-up mil-mb-20">
              Kaca film <strong>Acegard</strong> membuat gedung lebih nyaman, mengurangi panas & silau,
              sekaligus meningkatkan efisiensi energi dengan tampilan modern.
            </p>

            <ul class="feature-list mil-up">
              <li><i class="fa fa-check"></i> Konsultasi & survei gratis</li>
              <li><i class="fa fa-check"></i> Kontrol visibilitas & privasi</li>
              <li><i class="fa fa-check"></i> Pemasangan profesional & rapi</li>
              <li><i class="fa fa-check"></i> Garansi & after-sales support</li>
            </ul>

            <div class="badge-list mil-up mil-mt-20">
              <span class="badge">Home Service</span>
              <span class="badge">Finishing Rapi</span>
              <span class="badge">Kualitas Teruji</span>
            </div>
          </div>

          <div class="col-lg-5 mil-mb-30">
            <div class="mil-illustration mil-up">
              <div class="mil-image-frame">
                <img
                  src="{{ asset('assets/frontend/building/img/photo/building-12.jpg') }}"
                  alt="Keunggulan Kaca Film Gedung Acegard"
                  class="mil-scale"
                  data-value-1="1"
                  data-value-2="1.1"
                  loading="lazy"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- TESTIMONI -->
    <section id="testimoni" class="mil-soft-bg mil-relative" aria-label="Testimoni Pelanggan">
      <img src="{{ asset('assets/frontend/building/img/other/bg.svg')}}" class="mil-bg-img" alt="">
      <div class="container mil-p-120-90">
        <div class="mil-background-grid mil-softened"></div>

        <div class="mil-center mil-mb-40">
          <span class="mil-suptitle mil-upper mil-up mil-mb-15">Apa Kata Mereka</span>
          <h2 class="mil-upper mil-up">Testimoni Pelanggan</h2>
        </div>

        <div class="swiper" id="testimonial-slider">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="mil-pricing">
                <p class="mil-mb-10"><strong>PT Maju Sejahtera</strong></p>
                <p>“Ruang kerja jauh lebih sejuk, silau berkurang drastis. Tim Acegard rapi & cepat.”</p>
              </div>
            </div>
            <div class="swiper-slide">
              <div class="mil-pricing">
                <p class="mil-mb-10"><strong>Grand Tower</strong></p>
                <p>“Hasil pemasangan presisi, look gedung makin premium. Recommended!”</p>
              </div>
            </div>
            <div class="swiper-slide">
              <div class="mil-pricing">
                <p class="mil-mb-10"><strong>Ruko Harmoni</strong></p>
                <p>“Customer nyaman, AC tidak kerja keras. Worth it banget.”</p>
              </div>
            </div>
          </div>
          <div class="swiper-pagination mil-mt-20"></div>
        </div>
      </div>
    </section>
    <!-- TESTIMONI end -->

    <!-- GALERI -->
    <section id="galeri" aria-label="Galeri Pemasangan">
      <div class="container-fluid">
        <div class="row">
          <!-- Kolom 1: 2 item square -->
          <div class="col-md-6 col-lg-3">
            <a href="{{ asset('assets/frontend/building/img/covers/building-2.png')}}"
               class="mil-portfolio-item mil-square-item mil-up mil-mb-30 has-popup-image"
               aria-label="Fasad Kantor">
              <img src="{{ asset('assets/frontend/building/img/covers/building-2.png')}}" alt="Pemasangan Kaca Film Gedung 1"/>
              <div class="mil-project-descr">
                <h4 class="mil-upper mil-mb-10">Fasad Kantor</h4>
                <p>Contoh pemasangan untuk area fasad.</p>
              </div>
            </a>

            <a href="{{ asset('assets/frontend/building/img/covers/building-1.png')}}"
               class="mil-portfolio-item mil-square-item mil-up mil-mb-30 has-popup-image"
               aria-label="Meeting Room">
              <img src="{{ asset('assets/frontend/building/img/covers/building-1.png')}}" alt="Pemasangan Kaca Film Gedung 2"/>
              <div class="mil-project-descr">
                <h4 class="mil-upper mil-mb-10">Meeting Room</h4>
                <p>Reduksi silau & privasi terjaga.</p>
              </div>
            </a>
          </div>

          <!-- Kolom 2: 1 item long -->
          <div class="col-md-6 col-lg-3">
            <a href="{{ asset('assets/frontend/building/img/covers/building-4.png')}}"
               class="mil-portfolio-item mil-long-item mil-up mil-mb-30 has-popup-image"
               aria-label="Lobby">
              <img src="{{ asset('assets/frontend/building/img/covers/building-4.png')}}" alt="Pemasangan Kaca Film Gedung 3"/>
              <div class="mil-project-descr">
                <h4 class="mil-upper mil-mb-10">Lobby</h4>
                <p>Nyaman untuk tamu & karyawan.</p>
              </div>
            </a>
          </div>

          <!-- Kolom 3: 2 item square -->
          <div class="col-md-6 col-lg-3">
            <a href="{{ asset('assets/frontend/building/img/covers/building-14.png')}}"
               class="mil-portfolio-item mil-square-item mil-up mil-mb-30 has-popup-image"
               aria-label="Area Operasional">
              <img src="{{ asset('assets/frontend/building/img/covers/building-14.png')}}" alt="Pemasangan Kaca Film Gedung 4"/>
              <div class="mil-project-descr">
                <h4 class="mil-upper mil-mb-10">Area Operasional</h4>
                <p>Menjaga suhu stabil sepanjang hari.</p>
              </div>
            </a>

            <a href="{{ asset('assets/frontend/building/img/covers/building-15.png')}}"
               class="mil-portfolio-item mil-square-item mil-up mil-mb-30 has-popup-image"
               aria-label="Ruang Direktur">
              <img src="{{ asset('assets/frontend/building/img/covers/building-15.png')}}" alt="Pemasangan Kaca Film Gedung 5"/>
              <div class="mil-project-descr">
                <h4 class="mil-upper mil-mb-10">Ruang Direktur</h4>
                <p>Privasi & tampilan premium.</p>
              </div>
            </a>
          </div>

          <!-- Kolom 4: 1 item long -->
          <div class="col-md-6 col-lg-3">
            <a href="{{ asset('assets/frontend/building/img/covers/building-13.png')}}"
               class="mil-portfolio-item mil-long-item mil-up mil-mb-30 has-popup-image"
               aria-label="Gedung Komersial">
              <img src="{{ asset('assets/frontend/building/img/covers/building-13.png')}}" alt="Pemasangan Kaca Film Gedung 6"/>
              <div class="mil-project-descr">
                <h4 class="mil-upper mil-mb-10">Gedung Komersial</h4>
                <p>Estetika modern & konsisten.</p>
              </div>
            </a>
          </div>
        </div>
      </div>
    </section>
    <!-- GALERI end -->

    <!-- HARGA -->
    <section id="harga" class="mil-soft-bg mil-relative" aria-label="Paket Promo">
      <img src="{{ asset('assets/frontend/building/img/other/bg.svg')}}" class="mil-bg-img" alt="">
      <div class="container mil-p-120-90">
        <div class="mil-background-grid mil-softened"></div>

        <div class="mil-center mil-mb-60">
          <span class="mil-suptitle mil-upper mil-up mil-mb-15">Building Window Film</span>
          <h2 class="mil-upper mil-up">Pricelist Promo</h2>
          <p class="mil-up">Garansi 3 – 7 Tahun • Harga termasuk pemasangan</p>
        </div>

        <div class="row">
          <!-- Paket 1 -->
          <div class="col-lg-3 col-md-6 mil-mb-30">
            <div class="mil-pricing mil-up">
              <h4 class="mil-upper mil-mb-10">Flex One Way<br><small>Series</small></h4>
              <p class="mil-text-sm mil-mb-10"><del>Rp 500.000</del></p>
              <div class="price mil-mb-10">Rp 230.000 <span class="mil-text-sm">/ m²</span></div>
              <ul class="feature-list mil-mb-20" style="margin-bottom: 20px !important;">
                <li><i class="fa fa-check"></i> Tolak Panas 75%</li>
                <li><i class="fa fa-check"></i> UV Rejection 99%</li>
              </ul>
              <a class="cta-main whatsapp-btn" href="https://wa.me/628212003331?text=Halo%20Acegard%2C%20saya%20ingin%20pesan%20Flex%20One%20Way" target="_blank" rel="noopener">
                 Pesan Sekarang
              </a>
            </div>
          </div>

          <!-- Paket 2 -->
          <div class="col-lg-3 col-md-6 mil-mb-30">
            <div class="mil-pricing mil-up">
              <h4 class="mil-upper mil-mb-10">Black Master<br><small>Series</small></h4>
              <p class="mil-text-sm mil-mb-10"><del>Rp 650.000</del></p>
              <div class="price mil-mb-10">Rp 350.000 <span class="mil-text-sm">/ m²</span></div>
              <ul class="feature-list mil-mb-20" style="margin-bottom: 20px !important;">
                <li><i class="fa fa-check"></i> Tolak Panas 90%</li>
                <li><i class="fa fa-check"></i> UV Rejection 99%</li>
              </ul>
              <a class="cta-main whatsapp-btn" href="https://wa.me/628212003331?text=Halo%20Acegard%2C%20saya%20ingin%20pesan%20Black%20Master" target="_blank" rel="noopener">
                 Pesan Sekarang
              </a>
            </div>
          </div>

          <!-- Paket 3 -->
          <div class="col-lg-3 col-md-6 mil-mb-30">
            <div class="mil-pricing mil-up">
              <h4 class="mil-upper mil-mb-10">Notch UV 400<br><small>Series</small></h4>
              <p class="mil-text-sm mil-mb-10"><del>Rp 900.000</del></p>
              <div class="price mil-mb-10">Rp 500.000 <span class="mil-text-sm">/ m²</span></div>
              <ul class="feature-list mil-mb-20" style="margin-bottom: 20px !important;">
                <li><i class="fa fa-check"></i> Tolak Panas 95%</li>
                <li><i class="fa fa-check"></i> UV Rejection 99%</li>
              </ul>
              <a class="cta-main whatsapp-btn" href="https://wa.me/628212003331?text=Halo%20Acegard%2C%20saya%20ingin%20pesan%20Notch%20UV%20400" target="_blank" rel="noopener">
                 Pesan Sekarang
              </a>
            </div>
          </div>

          <!-- Paket 4 -->
          <div class="col-lg-3 col-md-6 mil-mb-30">
            <div class="mil-pricing mil-up">
              <h4 class="mil-upper mil-mb-10">4K Alpha Pro<br><small>Series</small></h4>
              <p class="mil-text-sm mil-mb-10"><del>Rp 1.200.000</del></p>
              <div class="price mil-mb-10">Rp 700.000 <span class="mil-text-sm">/ m²</span></div>
              <ul class="feature-list mil-mb-20" style="margin-bottom: 20px !important;">
                <li><i class="fa fa-check"></i> Tolak Panas 99%</li>
                <li><i class="fa fa-check"></i> UV Rejection 99%</li>
              </ul>
              <a class="cta-main whatsapp-btn" href="https://wa.me/628212003331?text=Halo%20Acegard%2C%20saya%20ingin%20pesan%204K%20Alpha%20Pro" target="_blank" rel="noopener">
                 Pesan Sekarang
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- HARGA end -->

    <!-- GARANSI -->
    <section id="garansi" aria-label="Garansi Resmi">
      <div class="container mil-p-120-60">
        <div class="mil-background-grid mil-softened"></div>
        <div class="mil-center mil-mb-50">
          <span class="mil-suptitle mil-upper mil-up mil-mb-15">Garansi Resmi</span>
          <h2 class="mil-upper mil-up">Tenang Hingga 7 Tahun</h2>
          <p class="mil-up">Perlindungan sesuai tipe produk & tercantum di invoice.</p>
        </div>

        <div class="row">
          <div class="col-md-4 mil-mb-30">
            <div class="mil-pricing">
              <h4 class="mil-upper mil-mb-10">3 Tahun</h4>
              <p>Proteksi dasar untuk varian tertentu.</p>
              <ul class="feature-list mil-mt-10">
                <li><i class="fa fa-check"></i> Cacat material</li>
                <li><i class="fa fa-check"></i> Delaminasi / bubbling</li>
              </ul>
            </div>
          </div>
          <div class="col-md-4 mil-mb-30">
            <div class="mil-pricing">
              <h4 class="mil-upper mil-mb-10">5 Tahun</h4>
              <p>Best value untuk kebutuhan komersial.</p>
              <ul class="feature-list mil-mt-10">
                <li><i class="fa fa-check"></i> Cacat material & warna pudar</li>
                <li><i class="fa fa-check"></i> Panduan klaim jelas</li>
              </ul>
            </div>
          </div>
          <div class="col-md-4 mil-mb-30">
            <div class="mil-pricing">
              <h4 class="mil-upper mil-mb-10">7 Tahun</h4>
              <p>Perlindungan maksimal untuk seri premium.</p>
              <ul class="feature-list mil-mt-10">
                <li><i class="fa fa-check"></i> Perlindungan menyeluruh</li>
                <li><i class="fa fa-check"></i> After-sales prioritas</li>
              </ul>
            </div>
          </div>
        </div>

        <p class="mil-text-sm mil-mt-20">
          *Syarat & ketentuan berlaku. Cakupan garansi mengikuti tipe film di invoice.
        </p>
      </div>
    </section>
    <!-- GARANSI end -->

    <!-- FAQ -->
    <section id="faq" class="mil-faq mil-dark-bg mil-relative mil-o-hidden" aria-label="Pertanyaan Umum">
      <img src="{{ asset('assets/frontend/building/img/photo/building-9.jpg')}}" class="mil-bg-img mil-scale" alt="FAQ Kaca Film Gedung" data-value-1="1" data-value-2="1.1"/>
      <div class="mil-overlay"></div>
      <div class="container mil-p-120-90">
        <div class="mil-background-grid"></div>
        <div class="row justify-content-between">
          <div class="col-lg-5">
            <div class="mil-mb-40">
              <span class="mil-suptitle mil-upper mil-light mil-up mil-mb-20">FAQ</span>
              <h2 class="mil-upper mil-light mil-up mil-mb-20">Yang Sering Ditanyakan</h2>
              <p class="mil-light-soft mil-up">Jawaban singkat terkait promo & pemasangan kaca film gedung.</p>
            </div>
          </div>

          <div class="col-lg-6 mil-mt-suptitle-offset">
            <div class="mil-accordion-group mil-up">
              <div class="mil-accordion-menu">
                <div class="mil-symbol mil-light mil-thin mil-h3"><div class="mil-plus">+</div><div class="mil-minus">-</div></div>
                <h6 class="mil-upper mil-light">Apakah ada survei lokasi?</h6>
              </div>
              <div class="mil-accordion-content">
                <p class="mil-light-soft mil-mb-20">Ya, kami sediakan konsultasi & survei agar rekomendasi film sesuai kondisi lapangan.</p>
              </div>
            </div>

            <div class="mil-accordion-group mil-up">
              <div class="mil-accordion-menu">
                <div class="mil-symbol mil-light mil-thin mil-h3"><div class="mil-plus">+</div><div class="mil-minus">-</div></div>
                <h6 class="mil-upper mil-light">Bagaimana penentuan harga?</h6>
              </div>
              <div class="mil-accordion-content">
                <p class="mil-light-soft mil-mb-20">Mengacu luas m², tipe film, dan tingkat performa. Dapatkan penawaran via WA.</p>
              </div>
            </div>

            <div class="mil-accordion-group mil-up">
              <div class="mil-accordion-menu">
                <div class="mil-symbol mil-light mil-thin mil-h3"><div class="mil-plus">+</div><div class="mil-minus">-</div></div>
                <h6 class="mil-upper mil-light">Apakah ada garansi?</h6>
              </div>
              <div class="mil-accordion-content">
                <p class="mil-light-soft mil-mb-20">Ada. 3–7 tahun tergantung seri produk, tertera pada invoice.</p>
              </div>
            </div>

            <div class="mil-accordion-group mil-up">
              <div class="mil-accordion-menu">
                <div class="mil-symbol mil-light mil-thin mil-h3"><div class="mil-plus">+</div><div class="mil-minus">-</div></div>
                <h6 class="mil-upper mil-light">Apakah pemasangan mengganggu operasional?</h6>
              </div>
              <div class="mil-accordion-content">
                <p class="mil-light-soft mil-mb-20">Kami atur jadwal & area kerja bertahap agar gangguan minimal.</p>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
    <!-- FAQ end -->

    <!-- CTA -->
    <section id="cta" aria-label="Hubungi Kami">
      <div class="container mil-p-120-90">
        <div class="mil-background-grid mil-softened"></div>
        <div class="row align-items-center">
          <div class="col-lg-8 mil-mb-20">
            <h2 class="mil-upper mil-mb-10">Siap upgrade gedung Anda?</h2>
            <p class="mil-mb-0">Klik tombol di bawah untuk konsultasi & penawaran terbaik hari ini.</p>
          </div>
          <div class="col-lg-4">
            <div class="whatsapp-grid" style="display:flex;gap:.75rem;justify-content:flex-start;">
              <a class="cta-main whatsapp-btn"
                 href="https://wa.me/628212003331?text=Halo%20Acegard%2C%20saya%20tertarik%20dengan%20promo%20kaca%20gedungnya"
                 target="_blank" rel="noopener">
                 Hubungi Sekarang
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="mil-relative">
      <img src="{{ asset('assets/frontend/building/img/photo/building-footer.jpg') }}"
           class="mil-bg-img mil-parallax" alt="Acegard Footer"
           style="object-position:center" data-value-1="-25%" data-value-2="18%">
      <div class="mil-overlay"></div>
      <div class="container mil-p-120-90">
        <div class="mil-background-grid"></div>
        <div class="row align-items-end">
          <div class="col-lg-8">
            <div class="row">
              <div class="col-md-6 col-lg-4">
                <span class="mil-suptitle mil-light mil-upper mil-up mil-mb-20">Workshop</span>
                <p class="mil-text-sm mil-up mil-light-soft mil-mb-20">Jabodetabek, Bandung, Medan, Jambi & Bengkulu*</p>
                <p class="mil-text-sm mil-up mil-light-soft">*Home service tersedia.</p>
              </div>
              <div class="col-md-6 col-lg-4">
                <span class="mil-suptitle mil-light mil-upper mil-up mil-mb-20">WhatsApp</span>
                <p class="mil-text-sm mil-up mil-light-soft">0821-2121-2093</p>
              </div>
              <div class="col-md-6 col-lg-4">
                <span class="mil-suptitle mil-light mil-upper mil-up mil-mb-20">Email</span>
                <p class="mil-text-sm mil-up mil-light-soft">acegardindonesia@gmail.com</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <a href="{{ route('frontend.home') }}" class="mil-footer-logo mil-up mil-mb-20">
              <img src="{{ asset('assets/images/acegard-logo.svg') }}" alt="Acegard" style="width:130px"/>
            </a>
          </div>
        </div>
      </div>
      <div class="container-fluid">
        <div class="mil-footer-bottom">
          <p class="mil-light-soft mil-mb-15">© 2025 Acegard. All rights reserved.</p>
        </div>
      </div>
    </footer>
    <!-- FOOTER end -->

  </div><!--/content-->
</div><!--/wrapper-->

<!-- Floating WhatsApp Button -->
<a class="wa-floating whatsapp-btn"
   href="https://wa.me/628212003331?text=Halo%20Acegard%2C%20saya%20ingin%20konsultasi%20kaca%20film%20gedung"
   target="_blank" rel="noopener"
   aria-label="WhatsApp Acegard 0821-2003-331">
  <span class="wa-label">Chat via WhatsApp</span>
  <span class="wa-bubble"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
</a>

<script src="{{ asset('assets/frontend/building/js/plugins/jquery.min.js') }}"></script>
<script src="{{ asset('assets/frontend/building/js/plugins/swiper.min.js') }}"></script>
<script src="{{ asset('assets/frontend/building/js/plugins/gsap.min.js') }}"></script>
<script src="{{ asset('assets/frontend/building/js/plugins/imagesloaded.pkgd.js') }}"></script>
<script src="{{ asset('assets/frontend/building/js/plugins/isotope.min.js') }}"></script>
<script src="{{ asset('assets/frontend/building/js/plugins/smooth-scroll.js') }}"></script>
<script src="{{ asset('assets/frontend/building/js/plugins/ScrollTrigger.min.js') }}"></script>
<script src="{{ asset('assets/frontend/building/js/plugins/magnific-popup.js') }}"></script>
<script src="{{ asset('assets/frontend/building/js/main.js') }}"></script>

<script>
  // Testimonial slider
  new Swiper('#testimonial-slider', {
    speed: 600,
    slidesPerView: 1,
    spaceBetween: 20,
    pagination: { el: '.swiper-pagination', clickable: true },
    breakpoints: { 768: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
  });

  // Popup for gallery images (Magnific)
  $(document).ready(function () {
    $('.has-popup-image').magnificPopup({type:'image', gallery:{enabled:true}});
  });
</script>


    <script>
(function () {
  // Pastikan dataLayer ada
  window.dataLayer = window.dataLayer || [];

  function handleClick(e) {
    e.preventDefault();

    // Elemen <a> yang diklik (pakai closest untuk aman kalau ikon di dalamnya diklik)
    const a = e.target.closest('a.whatsapp-btn');
    if (!a) return;

    const url = a.getAttribute('href');
    const label = a.dataset.gtmLabel || a.textContent.trim();
    const targetBlank = (a.getAttribute('target') === '_blank');

    let done = false;
    const go = () => {
      if (done) return;
      done = true;
      if (targetBlank) {
        // Buka tab baru
        window.open(url, '_blank', 'noopener');
      } else {
        // Pindah di tab yang sama
        window.location.href = url;
      }
    };

    // Push ke dataLayer dengan callback
    window.dataLayer.push({
      event: 'whatsapp_click',
      link_url: url,
      link_text: label,
      page_path: window.location.pathname,
      event_callback: go,
      event_timeout: 1000 // fallback jika tidak ada tag yang menembak
    });

    // Cadangan ekstra kalau browser membatasi callback
    setTimeout(go, 1100);
  }

  // Delegation: berlaku untuk SEMUA tombol .whatsapp-btn (termasuk yang muncul dinamis)
  document.addEventListener('click', function (e) {
    if (e.target.closest('a.whatsapp-btn')) handleClick(e);
  });
})();
</script>


<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DH3T7FD"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
</body>


</html>
