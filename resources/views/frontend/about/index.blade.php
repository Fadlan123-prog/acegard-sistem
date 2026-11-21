@extends('frontend.layout.index')

@php
  use App\Support\SiteContent;
  use App\Support\Whatsapp;
@endphp

@section('title', 'Tentang Kami - Acegard Indonesia')
@section('meta_description', 'Kenali ACEGARD Indonesia: kaca film Nano Ceramic untuk kendaraan dan bangunan, teknologi yang dipakai, dan pemasangan oleh teknisi kami di Tangerang.')
@section('body-class', 'ag-page')

@section('content')

@php
  $img = fn ($f) => asset('assets/frontend/ag/' . $f);

  $layers = [
    'High TSER Performance', 'Tungsten Thermal Layer', 'Night Vision Optical Clarity', 'UV400 Full Spectrum Protection',
    'Non-Metal Signal Friendly', 'Color Fade Resistant', 'Energy Efficient Color System',
  ];

  $why = [
    ['mdi:certificate-outline', 'Diproduksi di Korea', 'Window film ACEGARD dibuat di pabrik bersertifikat internasional di Korea dan didistribusikan resmi di Indonesia.'],
    ['mdi:white-balance-sunny', 'Inframerah ditolak 99%', 'Pada seri Black Master, Notch UV400, dan 4K Alpha Pro. TSER mencapai 95% pada varian AP02.'],
    ['mdi:shield-sun-outline', 'Proteksi UV', 'UV Rejection hingga 99,9% melindungi kulit dan interior dari paparan sinar UV.'],
    ['mdi:flash-outline', 'Hemat energi', 'Panas yang masuk berkurang, sehingga AC bekerja lebih ringan.'],
    ['mdi:palette-outline', 'Warna stabil', 'Warna film tetap konsisten untuk pemakaian bertahun-tahun.'],
    ['mdi:signal', 'Ramah sinyal', 'Lapisan non-metal tidak mengganggu sinyal seluler, WiFi, maupun GPS.'],
  ];

  $brands = ['nissan', 'toyota', 'bmw', 'hyundai', 'mercy', 'honda', 'mazda', 'vw'];
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-about-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">About Us</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">About ACEGARD</span>
    <h1 class="ag-display" id="ag-about-title">Kaca film Nano Ceramic <b>untuk kendaraan dan bangunan.</b></h1>
    <p class="ag-hero-sub">ACEGARD memadukan teknologi Nano Ceramic Matrix Composite dengan pemasangan oleh teknisi kami sendiri di Tangerang. Ruangan lebih sejuk, kabin lebih nyaman, pandangan tetap jernih.</p>
    <div class="ag-hero-actions">
      <a class="ag-btn ag-btn-primary" href="{{ route('frontend.service.car') }}">Lihat Produk</a>
      <a class="ag-btn ag-btn-secondary whatsapp-btn" data-gtm-label="about-hero-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin tahu lebih lanjut tentang kaca film ACEGARD.') }}">
        <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi Gratis
      </a>
    </div>
  </div>
</section>

{{-- ───────── Perjalanan ACEGARD (tampil hanya bila SiteContent::history() terisi) ───────── --}}
@php $history = SiteContent::history(); @endphp
@if (count($history))
<section class="ag-section" id="perjalanan" data-sec="Perjalanan" aria-labelledby="ag-hist-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Our Journey</span>
      <h2 class="ag-h2" id="ag-hist-title">Perjalanan <b>ACEGARD</b></h2>
      <p class="ag-lead">Dari awal berdiri sampai sekarang, tahun demi tahun.</p>
    </div>
    <ol class="ag-tl">
      @foreach ($history as $h)
        <li class="ag-tl-item ag-reveal">
          <span class="ag-tl-year">{{ $h['year'] }}</span>
          <div class="ag-tl-card">
            @if (! empty($h['image']))
              <figure class="ag-tl-fig"><img src="{{ $img($h['image']) }}" alt="{{ $h['title'] }}" width="800" height="500" loading="lazy"></figure>
            @endif
            <h3>{{ $h['title'] }}</h3>
            <p>{{ $h['text'] }}</p>
            @if (! empty($h['stat']))
              <span class="ag-tl-stat"><b>{{ $h['stat'][1] }}</b> {{ $h['stat'][0] }}</span>
            @endif
          </div>
        </li>
      @endforeach
    </ol>
  </div>
</section>
@endif

{{-- ───────── Teknologi ───────── --}}
<section class="ag-section" id="teknologi" data-sec="Teknologi" aria-labelledby="ag-tech-title">
  <div class="ag-container ag-split">
    <div class="ag-reveal">
      <span class="ag-eyebrow">Technology</span>
      <h2 class="ag-h2" id="ag-tech-title" style="margin-top:16px">Nano Ceramic <b>Matrix Composite</b></h2>
      <div class="ag-prose">
        <p>Lapisan dasar dual-layer Nano Ceramic diproses dengan teknologi sputtering, lalu diperkuat elemen logam tingkat tinggi: Tin, Titanium, dan Tungsten.</p>
        <p>Kombinasi ini menghasilkan 7 lapisan yang stabil secara optik dan tahan lama. Film menolak energi matahari sampai spektrum 2500 nanometer tanpa mengganggu sinyal elektronik.</p>
      </div>
    </div>
    <div class="ag-reveal">
      <dl class="ag-stats ag-stats-2">
        <div><dt>Tolak inframerah</dt><dd>99<small>%</small></dd></div>
        <div><dt>TSER hingga</dt><dd>95<small>%</small></dd></div>
        <div><dt>Tolak sinar UV</dt><dd>99,9<small>%</small></dd></div>
        <div><dt>Lapisan teknologi</dt><dd>7</dd></div>
      </dl>
      <p class="ag-note">Angka bersumber dari lembar spesifikasi produk ACEGARD. TSER 95% pada varian AP02, IRR 99% pada seri Black Master, Notch UV400, dan 4K Alpha Pro.</p>
    </div>
  </div>
</section>

<section class="ag-section alt" aria-labelledby="ag-layers-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Keunggulan</span>
      <h2 class="ag-h2" id="ag-layers-title">Tujuh karakter <b>kaca film ACEGARD</b></h2>
    </div>
    <ol class="ag-layers ag-reveal">
      @foreach ($layers as $i => $label)
        <li><span class="n">{{ $i + 1 }}</span><span>{{ $label }}</span></li>
      @endforeach
    </ol>
  </div>
</section>

{{-- ───────── Night Vision ───────── --}}
<section class="ag-section" id="night-vision" data-sec="Night Vision" aria-labelledby="ag-night-title">
  <div class="ag-container">
    <div class="ag-grid c2 ag-reveal" style="margin-bottom:40px">
      <figure class="ag-shot" style="aspect-ratio:4/3"><img src="{{ $img('gallery-view.webp') }}" width="1000" height="700" loading="lazy" alt="Pemandangan dari balik kaca film ACEGARD"></figure>
      <figure class="ag-shot" style="aspect-ratio:4/3"><img src="{{ $img('gallery-kantor.webp') }}" width="1000" height="444" loading="lazy" alt="Ruang kantor dengan jendela kaca besar"></figure>
    </div>
    <div class="ag-head ag-reveal" style="margin-bottom:24px">
      <span class="ag-eyebrow">Night Vision</span>
      <h2 class="ag-h2" id="ag-night-title">Detail jalan tetap terlihat <b>saat cahaya rendah</b></h2>
    </div>
    <div class="ag-prose ag-reveal">
      <p>Night Vision dirancang lewat optical-grade Nano Ceramic engineering yang bekerja presisi pada spektrum cahaya tampak. Struktur Nano Ceramic Matrix Composite dikalibrasi agar VLT tetap optimal, sehingga mata menangkap lebih banyak detail permukaan jalan di kondisi cahaya rendah.</p>
      <p>Lapisan non-metal juga mengurangi glare dari lampu kendaraan lain dan lampu jalan. Efek halo pada pupil menurun, mata tidak cepat lelah, dan respons visual pengemudi tetap tajam pada perjalanan panjang.</p>
      <p>Seri NOTCH UV400 membawa teknologi Night Vision ini. <a class="ag-link" href="{{ route('frontend.service.car') }}#notch-uv400">Lihat spesifikasinya</a>.</p>
    </div>
  </div>
</section>

{{-- ───────── Hemat energi ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-energy-title">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal" style="background:var(--ag-deep)">
      <span class="ag-eyebrow" style="background:rgba(255,255,255,.14);color:#fff">Energy Efficiency</span>
      <h2 id="ag-energy-title" style="margin-top:16px">Suhu stabil, <b>AC lebih ringan.</b></h2>
      <p>ACEGARD mengurangi panas yang masuk ke kabin mobil atau ruangan, menurunkan suhu interior hingga ±5°C, dan membuat AC bekerja lebih stabil. Konsumsi energi kendaraan dapat berkurang hingga ±25% pada pemakaian harian, dengan efisiensi bahan bakar dan umur kompresor yang lebih panjang.</p>
    </div>
  </div>
</section>

{{-- ───────── Mengapa ACEGARD ───────── --}}
<section class="ag-section" id="alasan" data-sec="Alasan" aria-labelledby="ag-why-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Why ACEGARD</span>
      <h2 class="ag-h2" id="ag-why-title">Kenapa memilih kaca film <b>dari ACEGARD</b></h2>
      <p class="ag-lead">Kontrol kualitas berlapis sebelum distribusi, garansi yang jelas, layanan after sales, dan teknisi berpengalaman.</p>
    </div>
    <div class="ag-grid c3">
      @foreach ($why as [$icon, $title, $desc])
        <div class="ag-card ag-feature is-hover ag-reveal">
          <span class="ic"><iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon></span>
          <h3>{{ $title }}</h3>
          <p>{{ $desc }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── Layanan ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-svc-title">
  <div class="ag-container ag-split">
    <div class="ag-reveal">
      <span class="ag-eyebrow">Our Service</span>
      <h2 class="ag-h2" id="ag-svc-title" style="margin-top:16px">Layanan cepat, profesional, <b>dan transparan</b></h2>
      <div class="ag-prose">
        <p>Produk yang baik perlu layanan yang baik. Kami mendampingi pelanggan dari konsultasi, pemasangan, sampai dukungan after sales.</p>
        <p>Komitmen kami sederhana: setiap pelanggan merasa terlindungi dan yakin telah memilih kaca film dengan kualitas dan pelayanan yang jujur.</p>
      </div>
      <a class="ag-btn ag-btn-primary" href="{{ route('frontend.contact') }}" style="margin-top:8px">Hubungi Kami</a>
    </div>
    <div class="ag-reveal">
      <ul class="ag-steps">
        @foreach ([['Konsultasi', 'Ceritakan kendaraan atau bangunan Anda lewat WhatsApp. Kami bantu memilih seri dan varian.'], ['Pemasangan', 'Teknisi ACEGARD memasang film dengan teliti sesuai prosedur.'], ['Garansi dan after sales', 'Garansi bisa diperiksa kapan saja di halaman Cek Garansi.']] as $i => [$t, $d])
          <li><span class="n">{{ $i + 1 }}</span><div><strong>{{ $t }}</strong><p>{{ $d }}</p></div></li>
        @endforeach
      </ul>
    </div>
  </div>
</section>

{{-- ───────── Klien dan mitra ───────── --}}
<section class="ag-section" id="klien" data-sec="Klien" aria-labelledby="ag-client-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Clients</span>
      <h2 class="ag-h2" id="ag-client-title">Dipercaya klien <b>bangunan dan kendaraan</b></h2>
    </div>
    <ul class="ag-clients ag-reveal">
      @foreach (SiteContent::clients() as $c)<li>{{ $c }}</li>@endforeach
    </ul>
    <p class="ag-center ag-note ag-reveal" style="margin-top:32px">Kompatibel dengan berbagai merek kendaraan</p>
    <ul class="ag-brands ag-reveal">
      @foreach ($brands as $b)
        <li><img src="{{ asset('assets/frontend/images/cars-logo/' . $b . '.png') }}" alt="{{ ucfirst($b === 'mercy' ? 'Mercedes-Benz' : $b) }}" height="40" loading="lazy"></li>
      @endforeach
    </ul>
  </div>
</section>

{{-- ───────── Testimoni ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-testi-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Testimonials</span>
      <h2 class="ag-h2" id="ag-testi-title">Kata mereka <b>tentang ACEGARD</b></h2>
    </div>
    <div class="ag-grid c3">
      @foreach (SiteContent::testimonials() as [$name, $role, $photo, $quote])
        <figure class="ag-card ag-quote ag-reveal" style="margin:0">
          <blockquote style="margin:0"><q>{{ $quote }}</q></blockquote>
          <footer>
            <span class="av">@if($photo)<img src="{{ $photo }}" alt="" width="44" height="44" loading="lazy">@else{{ mb_substr($name, 0, 1) }}@endif</span>
            <span><strong>{{ $name }}</strong><small>{{ $role }}</small></span>
          </footer>
        </figure>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── CTA ───────── --}}
<section class="ag-section" aria-labelledby="ag-acta-title">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal">
      <h2 id="ag-acta-title">Ingin tahu lebih lanjut <b>tentang ACEGARD?</b></h2>
      <p>Tim kami siap menjawab pertanyaan soal teknologi, produk, dan pemasangan.</p>
      <div class="ag-cta-actions">
        <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="about-cta-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin tahu lebih lanjut tentang ACEGARD.') }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi via WhatsApp
        </a>
        <a class="ag-btn ag-btn-ghost" href="{{ route('frontend.contact') }}">Kontak dan Lokasi</a>
      </div>
    </div>
  </div>
</section>

@endsection

@section('jsonld')
  {!! \App\Support\Seo::breadcrumbs([['Home', route('frontend.home')], ['About Us', route('frontend.about')]]) !!}
@endsection
