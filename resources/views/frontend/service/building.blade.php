<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />

  <!-- SEO -->
  <title>Promo Kaca Film Gedung | Acegard</title>
  <meta name="description" content="Promo kaca film gedung Acegard. Konsultasi & survei gratis, garansi resmi, pemasangan rapi. Hubungi WhatsApp untuk penawaran terbaik." />
  <meta name="keywords" content="kaca film gedung, kaca film bangunan, promo kaca film, Acegard, tolak panas, UV protection, night vision" />

  <!-- Open Graph -->
  <meta property="og:title" content="Promo Kaca Film Gedung | Acegard" />
  <meta property="og:description" content="Upgrade kenyamanan & estetika gedung Anda dengan kaca film Acegard. Konsultasi & survei gratis." />
  <meta property="og:type" content="website" />
  <meta property="og:image" content="img/og/acegard-building.jpg" />

  <!-- Assets asli template -->
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/plugins/bootstrap-grid.css')}}" />
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/plugins/font-awesome.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/plugins/swiper.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/plugins/magnific-popup.css')}}" />
  <link rel="stylesheet" href="{{ asset('assets/frontend/building/css/style.css')}}" />

  <link rel="icon" type="image/png" href="{{asset('assets/frontend/images/acegard-favicon.png')}}">

  <style>
    /* Penyesuaian kecil agar cocok untuk landing promo */
    .mil-banner .mil-overlay { background: linear-gradient(180deg, rgba(0,0,0,.45), rgba(0,0,0,.55)); }
    .badge-list { display:flex; flex-wrap:wrap; gap:.75rem; }
    .badge { background:#101010; color:#fff; border:1px solid rgba(255,255,255,.12); border-radius:999px; padding:.45rem .85rem; font-size:.9rem; }
    .mil-pricing { border:1px solid rgba(0,0,0,.08); border-radius:20px; padding:28px; background:#fff; transition:transform .2s ease, box-shadow .2s ease; }
    .mil-pricing:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .mil-pricing .price { font-size:2rem; font-weight:700; letter-spacing:.5px; }
    .feature-list { list-style:none; padding:0; margin:0; }
    .feature-list li { display:flex; gap:.6rem; align-items:flex-start; margin:.5rem 0; }
    .feature-list i { margin-top:.25rem; }
    .whatsapp-btn { display:inline-flex; align-items:center; gap:.5rem; background:#25D366; color:#fff; padding:.9rem 1.2rem; border-radius:999px; text-decoration:none; font-weight:600; }
    .whatsapp-grid { display:flex; gap:.6rem; flex-wrap:wrap; }
    .whatsapp-btn:focus { outline: 2px solid #128C7E; outline-offset:2px; }

    .mil-footer-logo img { filter: drop-shadow(0 2px 8px rgba(0,0,0,.2)); }
  </style>
</head>

<body>
  <div class="mil-wrapper">

    <!-- Top bar -->
    <div class="mil-top-panel">
      <div class="container-fluid">
        <div class="mil-top-panel-content">
          <a href="{{ route('frontend.home') }}" class="mil-logo"><img src="{{ asset('assets/images/acegard-logo.svg')}}" alt="Acegard" style="width:130px" /></a>

          <div class="mil-navigation">
            <nav>
              <ul>
                <li><a href="{{ route('frontend.home') }}">Home</a></li>
                <li class="mil-has-children mil-active">
                  <a href="#promo">Service</a>
                  <ul>
                    <li class="mil-active"><a href="{{ route('frontend.service.car') }}">Car</a></li>
                    <li><a href="{{ route('frontend.service.building') }}">Building</a></li>
                  </ul>
                </li>
                <li><a href="{{ route('frontend.about') }}">Tentang Kami</a></li>
                <li><a href="{{ route('frontend.contact') }}">Kontak Kami</a></li>
              </ul>
            </nav>
          </div>

          {{-- <div class="mil-top-panel-buttons">
            <a href="#cta" class="mil-button mil-sm">Ambil Promo</a>
            <div class="mil-menu-btn"><span></span></div>
          </div> --}}
        </div>
      </div>
    </div>
    <!-- Top bar end -->

    <div id="content">

      <!-- HERO / BANNER -->
      <section id="promo" class="mil-banner" aria-label="Promo Kaca Film Gedung">
        <img src="{{ asset('assets/frontend/building/img/photo/building-10.jpg')}}" class="mil-bg-img mil-scale" data-value-1=".9" data-value-2="1.2" alt="Promo Kaca Film Gedung Acegard" />
        <div class="mil-overlay"></div>
        <div class="container">
          <div class="mil-background-grid mil-top-space"></div>
          <div class="mil-banner-content">
            <div class="row align-items-end">
              <div class="col-xl-7">
                <div class="mil-mb-60">
                  <span class="mil-suptitle mil-light mil-upper mil-mb-30">Promo Building</span>
                  <h1 class="mil-upper mil-light mil-mb-20">
                    Kaca Film <span class="mil-accent">Gedung</span><br />Lebih Sejuk & Elegan
                  </h1>
                  <p class="mil-light-soft mil-mb-30">
                    Tingkatkan kenyamanan, hemat energi, dan jaga privasi gedung dengan pemasangan kaca film profesional.
                  </p>

                  <div class="badge-list mil-mb-40">
                    <span class="badge">Konsultasi & Survei Gratis</span>
                    <span class="badge">Garansi Resmi</span>
                    <span class="badge">Pemasangan Rapi</span>
                    <span class="badge">Home Service</span>
                  </div>

                  <div class="whatsapp-grid">
                    <a class="whatsapp-btn" href="https://wa.me/6282121212093?text=Halo%20Acegard%2C%20saya%20tertarik%20dengan%20promo%20kaca%20gedungnya" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp 0821-2111-3688">
                      <i class="fa fa-whatsapp" aria-hidden="true"></i> Hubungi Sekarang
                    </a>
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

      <!-- HIGHLIGHTS / WHY ACEGARD -->
<section aria-label="Keunggulan Kaca Film Gedung">
  <div class="container mil-p-120-60">
    <div class="mil-background-grid mil-softened"></div>
    <div class="row justify-content-between align-items-center">
      <div class="col-lg-6 mil-mb-30">
        <span class="mil-suptitle mil-upper mil-up mil-mb-20">Kenapa Acegard</span>
        <h2 class="mil-upper mil-up mil-mb-20">Lebih Sejuk, Hemat Energi, Berkelas</h2>

        <p class="mil-up mil-mb-20">
          Kaca film Acegard membuat gedung lebih nyaman, mengurangi panas & silau,
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

<!-- PAKET / PRICING -->
<section class="mil-soft-bg mil-relative" aria-label="Paket Promo">
  <img src="{{ asset('assets/frontend/building/img/other/bg.svg')}}" class="mil-bg-img" alt="">
  <div class="container mil-p-120-90">
    <div class="mil-background-grid mil-softened"></div>

    <div class="mil-center mil-mb-60">
      <span class="mil-suptitle mil-upper mil-up mil-mb-15">Building Window Film</span>
      <h2 class="mil-upper mil-up">Pricelist Promo</h2>
      <p class="mil-up">Garansi 3 – 7 Tahun • Harga sudah termasuk biaya pemasangan</p>
    </div>

    <div class="row">
      <!-- Paket 1 -->
      <div class="col-lg-3 col-md-6 mil-mb-30">
        <div class="mil-pricing mil-up">
          <h4 class="mil-upper mil-mb-10">Flex One Way<br><small>Series</small></h4>
          <p class="mil-text-sm mil-mb-10"><del>Rp 500.000</del></p>
          <div class="price mil-mb-10">Rp 230.000 <span class="mil-text-sm">/ m²</span></div>
          <ul class="feature-list mil-mb-20">
            <li><i class="fa fa-check"></i> Tolak Panas 75%</li>
            <li><i class="fa fa-check"></i> Tolak UVR 99%</li>
          </ul>
          <a class="mil-button" href="#cta">Pesan Sekarang</a>
        </div>
      </div>

      <!-- Paket 2 -->
      <div class="col-lg-3 col-md-6 mil-mb-30">
        <div class="mil-pricing mil-up">
          <h4 class="mil-upper mil-mb-10">Black Master<br><small>Series</small></h4>
          <p class="mil-text-sm mil-mb-10"><del>Rp 650.000</del></p>
          <div class="price mil-mb-10">Rp 350.000 <span class="mil-text-sm">/ m²</span></div>
          <ul class="feature-list mil-mb-20">
            <li><i class="fa fa-check"></i> Tolak Panas 90%</li>
            <li><i class="fa fa-check"></i> Tolak UVR 99%</li>
          </ul>
          <a class="mil-button" href="#cta">Pesan Sekarang</a>
        </div>
      </div>

      <!-- Paket 3 -->
      <div class="col-lg-3 col-md-6 mil-mb-30">
        <div class="mil-pricing mil-up">
          <h4 class="mil-upper mil-mb-10">Notch UV 400<br><small>Series</small></h4>
          <p class="mil-text-sm mil-mb-10"><del>Rp 900.000</del></p>
          <div class="price mil-mb-10">Rp 500.000 <span class="mil-text-sm">/ m²</span></div>
          <ul class="feature-list mil-mb-20">
            <li><i class="fa fa-check"></i> Tolak Panas 95%</li>
            <li><i class="fa fa-check"></i> Tolak UVR 99%</li>
          </ul>
          <a class="mil-button" href="#cta">Pesan Sekarang</a>
        </div>
      </div>

      <!-- Paket 4 -->
      <div class="col-lg-3 col-md-6 mil-mb-30">
        <div class="mil-pricing mil-up">
          <h4 class="mil-upper mil-mb-10">4K Alpha Pro<br><small>Series</small></h4>
          <p class="mil-text-sm mil-mb-10"><del>Rp 1.200.000</del></p>
          <div class="price mil-mb-10">Rp 700.000 <span class="mil-text-sm">/ m²</span></div>
          <ul class="feature-list mil-mb-20">
            <li><i class="fa fa-check"></i> Tolak Panas 99%</li>
            <li><i class="fa fa-check"></i> Tolak UVR 99%</li>
          </ul>
          <a class="mil-button" href="#cta">Pesan Sekarang</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- advantages -->
<section aria-label="Keunggulan Acegard">
  <div class="container mil-p-120-60">
    <div class="mil-background-grid mil-softened"></div>

    <div class="row">
      <div class="col-12">
        <div class="mil-center mil-mb-90">
          <span class="mil-suptitle mil-upper mil-up mil-mb-30">Keunggulan Kami</span>
          <h2 class="mil-upper mil-up mil-mb-30">Acegard Selalu Utamakan<br>Kenyamanan & Kualitas</h2>
          <p class="mil-up">Dari konsultasi hingga pemasangan, semua kami tangani secara profesional untuk hasil maksimal.</p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="mil-advantage mil-icon-box mil-center mil-up mil-mb-60">
          <h4 class="mil-upper mil-mb-20">Konsultasi Gratis</h4>
          <div class="mil-icon mil-icon-border mil-mb-20">
            <img src="{{ asset('assets/frontend/building/img/icons/6.svg') }}" alt="Konsultasi Gratis">
          </div>
          <p>Survey & rekomendasi sesuai kebutuhan gedung Anda.</p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="mil-advantage mil-icon-box mil-center mil-up mil-mb-60">
          <h4 class="mil-upper mil-mb-20">Tim Profesional</h4>
          <div class="mil-icon mil-icon-border mil-mb-20">
            <img src="{{ asset('assets/frontend/building/img/icons/6.svg') }}" alt="Tim Profesional">
          </div>
          <p>Pemasangan rapi dengan standar kualitas tinggi.</p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="mil-advantage mil-icon-box mil-center mil-up mil-mb-60">
          <h4 class="mil-upper mil-mb-20">Produk Berkualitas</h4>
          <div class="mil-icon mil-icon-border mil-mb-20">
            <img src="{{ asset('assets/frontend/building/img/icons/6.svg') }}" alt="Produk Berkualitas">
          </div>
          <p>Film kaca dengan performa tinggi & garansi resmi 3–7 tahun.</p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3">
        <div class="mil-advantage mil-icon-box mil-center mil-up mil-mb-60">
          <h4 class="mil-upper mil-mb-20">Layanan Purna Jual</h4>
          <div class="mil-icon mil-icon-border mil-mb-20">
            <img src="{{ asset('assets/frontend/building/img/icons/6.svg') }}" alt="After Sales">
          </div>
          <p>Dukungan after-sales untuk ketenangan jangka panjang.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- advantages end -->



      <!-- CASE / PROJECT GALLERY -->
      <section aria-label="Galeri Pemasangan">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-6 col-lg-3">
              <a href="{{ asset('assets/frontend/building/img/covers/building-2.png')}}" class="mil-portfolio-item mil-square-item mil-up mil-mb-30 has-popup-image" aria-label="Galeri ">
                <img src="{{ asset('assets/frontend/building/img/covers/building-2.png')}}" alt="Pemasangan Kaca Film Gedung 1" />
                <div class="mil-project-descr">
                  <h4 class="mil-upper mil-mb-10">Fasad Kantor</h4>
                  <p>Contoh pemasangan untuk area fasad.</p>
                </div>
              </a>
              <a href="{{asset('assets/frontend/building/img/covers/building-1.png')}}" class="mil-portfolio-item mil-square-item mil-up mil-mb-30 has-popup-image" aria-label="Galeri 2">
                <img src="{{asset('assets/frontend/building/img/covers/building-1.png')}}" alt="Pemasangan Kaca Film Gedung 2" />
                <div class="mil-project-descr">
                  <h4 class="mil-upper mil-mb-10">Meeting Room</h4>
                  <p>Reduksi silau & privasi terjaga.</p>
                </div>
              </a>
            </div>

            <div class="col-md-6 col-lg-3">
              <a href="{{ asset('assets/frontend/building/img/covers/building-4.png')}}" class="mil-portfolio-item mil-long-item mil-up mil-mb-30 has-popup-image" aria-label="Galeri 3">
                <img src="{{ asset('assets/frontend/building/img/covers/building-4.png')}}" alt="Pemasangan Kaca Film Gedung 3" />
                <div class="mil-project-descr">
                  <h4 class="mil-upper mil-mb-10">Lobby</h4>
                  <p>Nyaman untuk tamu & karyawan.</p>
                </div>
              </a>
            </div>

            <div class="col-md-6 col-lg-3">
              <a href="{{ asset('assets/frontend/building/img/covers/building-14.png')}}" class="mil-portfolio-item mil-square-item mil-up mil-mb-30 has-popup-image" aria-label="Galeri 4">
                <img src="{{ asset('assets/frontend/building/img/covers/building-14.png')}}" alt="Pemasangan Kaca Film Gedung 4" />
                <div class="mil-project-descr">
                  <h4 class="mil-upper mil-mb-10">Area Operasional</h4>
                  <p>Menjaga suhu stabil sepanjang hari.</p>
                </div>
              </a>
              <a href="{{ asset('assets/frontend/building/img/covers/building-15.png')}}" class="mil-portfolio-item mil-square-item mil-up mil-mb-30 has-popup-image" aria-label="Galeri 5">
                <img src="{{ asset('assets/frontend/building/img/covers/building-15.png')}}" alt="Pemasangan Kaca Film Gedung 5" />
                <div class="mil-project-descr">
                  <h4 class="mil-upper mil-mb-10">Ruang Direktur</h4>
                  <p>Privasi & tampilan premium.</p>
                </div>
              </a>
            </div>

            <div class="col-md-6 col-lg-3">
              <a href="{{ asset('assets/frontend/building/img/covers/building-13.png')}}" class="mil-portfolio-item mil-long-item mil-up mil-mb-30 has-popup-image" aria-label="Galeri 6">
                <img src="{{ asset('assets/frontend/building/img/covers/building-13.png')}}" alt="Pemasangan Kaca Film Gedung 6" />
                <div class="mil-project-descr">
                  <h4 class="mil-upper mil-mb-10">Gedung Komersial</h4>
                  <p>Estetika modern & konsisten.</p>
                </div>
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section class="mil-faq mil-dark-bg mil-relative mil-o-hidden" aria-label="Pertanyaan Umum">
        <img src="{{ asset('assets/frontend/building/img/photo/building-9.jpg')}}" class="mil-bg-img mil-scale" alt="FAQ Kaca Film Gedung" data-value-1="1" data-value-2="1.1" />
        <div class="mil-overlay"></div>
        <div class="container mil-p-120-90">
          <div class="mil-background-grid"></div>
          <div class="row justify-content-between">
            <div class="col-lg-5">
              <div class="mil-mb-40">
                <span class="mil-suptitle mil-upper mil-light mil-up mil-mb-20">FAQ</span>
                <h2 class="mil-upper mil-light mil-up mil-mb-20">Yang Sering Ditanyakan</h2>
                <p class="mil-light-soft mil-up">Berikut jawaban singkat terkait promo & pemasangan kaca film gedung.</p>
              </div>
            </div>

            <div class="col-lg-6 mil-mt-suptitle-offset">
              <div class="mil-accordion-group mil-up">
                <div class="mil-accordion-menu">
                  <div class="mil-symbol mil-light mil-thin mil-h3"><div class="mil-plus">+</div><div class="mil-minus">-</div></div>
                  <h6 class="mil-upper mil-light">Apakah ada survei lokasi?</h6>
                </div>
                <div class="mil-accordion-content">
                  <p class="mil-light-soft mil-mb-20">Ya, kami menyediakan konsultasi & survei agar rekomendasi tipe film sesuai kondisi gedung Anda.</p>
                </div>
              </div>

              <div class="mil-accordion-group mil-up">
                <div class="mil-accordion-menu">
                  <div class="mil-symbol mil-light mil-thin mil-h3"><div class="mil-plus">+</div><div class="mil-minus">-</div></div>
                  <h6 class="mil-upper mil-light">Bagaimana penentuan harga?</h6>
                </div>
                <div class="mil-accordion-content">
                  <p class="mil-light-soft mil-mb-20">Harga mengacu pada luas m², tipe film, serta tingkat kegelapan/performanya. Dapatkan penawaran melalui WA.</p>
                </div>
              </div>

              <div class="mil-accordion-group mil-up">
                <div class="mil-accordion-menu">
                  <div class="mil-symbol mil-light mil-thin mil-h3"><div class="mil-plus">+</div><div class="mil-minus">-</div></div>
                  <h6 class="mil-upper mil-light">Apakah ada garansi?</h6>
                </div>
                <div class="mil-accordion-content">
                  <p class="mil-light-soft mil-mb-20">Tersedia garansi resmi sesuai tipe produk. Detail garansi tercantum pada invoice.</p>
                </div>
              </div>

              <div class="mil-accordion-group mil-up">
                <div class="mil-accordion-menu">
                  <div class="mil-symbol mil-light mil-thin mil-h3"><div class="mil-plus">+</div><div class="mil-minus">-</div></div>
                  <h6 class="mil-upper mil-light">Apakah pemasangan mengganggu operasional?</h6>
                </div>
                <div class="mil-accordion-content">
                  <p class="mil-light-soft mil-mb-20">Tim kami mengatur jadwal & area kerja agar gangguan minimal, termasuk opsi pekerjaan bertahap.</p>
                </div>
              </div>

              <!-- TODO: Tambahkan Q&A spesifik dari halaman promo bila diperlukan -->
            </div>
          </div>
        </div>
      </section>

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
              <div class="whatsapp-grid">
                <a class="whatsapp-btn" href="https://wa.me/6282121212093?text=Halo%20Acegard%2C%20saya%20tertarik%20dengan%20promo%20kaca%20gedungnya" target="_blank" rel="noopener">Hubungi Sekarang</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <footer class="mil-relative">
        <img src="img/photo/building-footer.jpg" class="mil-bg-img mil-parallax" alt="Acegard Footer" style="object-position: center;" data-value-1="-25%" data-value-2="18%">
        <div class="mil-overlay"></div>
        <div class="container mil-p-120-90">
          <div class="mil-background-grid"></div>
          <div class="row align-items-end">
            <div class="col-lg-8">
              <div class="row">
                <div class="col-12">
                  <div class="mil-footer-navigation mil-up mil-mb-60">
                    <nav>
                      <ul>
                        <li><a href="{{ route('frontend.home')}}">Home</a></li>
                        <li class="mil-active"><a href="#promo">Service</a></li>
                        <li><a href="{{ route('frontend.about')}}">Tentang Kami</a></li>
                        <li><a href="{{ route('frontend.contact')}}">Kontak Kami</a></li>
                      </ul>
                    </nav>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <span class="mil-suptitle mil-light mil-upper mil-up mil-mb-20">Workshop</span>
                  <p class="mil-text-sm mil-up mil-light-soft mil-mb-20">Jabodetabek, Bandung, Medan, Jambi & Bengkulu*</p>
                  <p class="mil-text-sm mil-up mil-light-soft">*Layanan home service tersedia.</p>
                </div>
                <div class="col-md-6 col-lg-4">
                  <span class="mil-suptitle mil-light mil-upper mil-up mil-mb-20">WhatsApp</span>
                  <p class="mil-text-sm mil-up mil-light-soft">
                    0821-2121-2093
                  </p>
                </div>
                <div class="col-md-6 col-lg-4">
                  <span class="mil-suptitle mil-light mil-upper mil-up mil-mb-20">Email</span>
                  <p class="mil-text-sm mil-up mil-light-soft">acegardindonesia@gmail.com</p>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <a href="{{ route('frontend.home')}}" class="mil-footer-logo mil-up mil-mb-20"><img src="{{ asset('assets/images/acegard-logo.svg')}}" alt="Acegard" style="width:130px" /></a>
            </div>
          </div>
        </div>
        <div class="container-fluid">
          <div class="mil-footer-bottom">
            <p class="mil-light-soft mil-mb-15">© 2025 Acegard. All rights reserved.</p>

          </div>
        </div>
      </footer>
      <!-- footer end -->

    </div><!-- content -->

  </div><!-- wrapper -->

  <!-- Scripts -->
  <script src="{{ asset('assets/frontend/building/js/plugins/jquery.min.js')}}"></script>
  <script src="{{ asset('assets/frontend/building/js/plugins/swiper.min.js')}}"></script>
  <script src="{{ asset('assets/frontend/building/js/plugins/gsap.min.js')}}"></script>
  <script src="{{ asset('assets/frontend/building/js/plugins/imagesloaded.pkgd.js')}}"></script>
  <script src="{{ asset('assets/frontend/building/js/plugins/isotope.min.js')}}"></script>
  <script src="{{ asset('assets/frontend/building/js/plugins/smooth-scroll.js')}}"></script>
  <script src="{{ asset('assets/frontend/building/js/plugins/ScrollTrigger.min.js')}}"></script>
  <script src="{{ asset('assets/frontend/building/js/plugins/magnific-popup.js')}}"></script>
  <script src="{{ asset('assets/frontend/building/js/main.js')}}"></script>
</body>
</html>
