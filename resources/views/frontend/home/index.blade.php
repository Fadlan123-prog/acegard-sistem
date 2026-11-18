@extends('frontend.layout.index')

@php use App\Support\Whatsapp; @endphp

@section('title', 'Acegard Indonesia - Kaca Film Mobil, Kaca Film Gedung & PPF')
@section('meta_description', 'ACEGARD menyediakan kaca film mobil, kaca film gedung dan bangunan, serta Paint Protection Film (PPF). Konsultasikan kebutuhan Anda melalui WhatsApp.')
@section('body-class', 'ag-page')

@section('content')

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-lines" aria-labelledby="ag-hero-title">
  <div class="ag-container">
    <div class="ag-hero-meta">
      <span>Window Film &middot; Paint Protection</span>
      <span>Tangerang, Indonesia</span>
    </div>

    <h1 class="ag-display" id="ag-hero-title">
      <span class="ln" style="--i:0"><span>Protection,</span></span>
      <span class="ln" style="--i:1"><span>Comfort</span></span>
      <span class="ln" style="--i:2"><span class="accent">&amp; Clarity.</span></span>
    </h1>

    <div class="ag-hero-stage">
      <div class="ag-hero-copy">
        <p>Premium Automotive Window Film, Architectural Film, and Paint Protection Solutions untuk kendaraan dan bangunan Anda.</p>
        <div class="ag-hero-actions">
          <a class="ag-btn ag-btn-primary" href="#produk">Explore Our Products</a>
          <a class="ag-btn ag-btn-secondary whatsapp-btn" data-gtm-label="hero-whatsapp" target="_blank" rel="noopener"
             href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}">
            <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Contact Us
          </a>
        </div>
      </div>
      <div class="ag-hero-car">
        <img src="{{ asset('assets/frontend/ag/hero-car.webp') }}" width="1200" height="799" alt="Mobil sedan perak dengan kaca film ACEGARD" fetchpriority="high">
      </div>
    </div>

    <nav class="ag-hero-index" aria-label="Layanan">
      <a href="{{ route('frontend.service.car') }}"><span><small>01</small>Automotive Film</span><iconify-icon icon="mdi:arrow-top-right" aria-hidden="true"></iconify-icon></a>
      <a href="{{ route('frontend.service.building') }}"><span><small>02</small>Building Film</span><iconify-icon icon="mdi:arrow-top-right" aria-hidden="true"></iconify-icon></a>
      <a href="{{ route('frontend.ppf.catalog') }}"><span><small>03</small>Paint Protection Film</span><iconify-icon icon="mdi:arrow-top-right" aria-hidden="true"></iconify-icon></a>
    </nav>
  </div>
</section>

{{-- ───────── Our Services ───────── --}}
<section class="ag-section" id="layanan" aria-labelledby="ag-services-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <div>
        <span class="ag-eyebrow">Our Services</span>
        <h2 class="ag-h2" id="ag-services-title">Tiga layanan utama ACEGARD</h2>
      </div>
      <p class="ag-lead">Pilih layanan sesuai kebutuhan Anda, lalu konsultasikan pilihan produk dengan tim kami.</p>
    </div>

    <ul class="ag-rows is-media">
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.service.car') }}">
          <span class="ag-row-num">01</span>
          <span class="ag-row-title">Automotive<br>Window Film</span>
          <span class="ag-row-desc">Kaca film untuk kenyamanan dan privasi berkendara, dengan beragam pilihan tingkat kegelapan.
            <ul><li>Kenyamanan kabin</li><li>Privasi</li><li>Pilihan VLT</li></ul></span>
          <span class="ag-row-media"><img src="{{ asset('assets/frontend/ag/service-otomotif.webp') }}" width="1100" height="488" loading="lazy" alt="Kaca film mobil"></span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.service.building') }}">
          <span class="ag-row-num">02</span>
          <span class="ag-row-title">Building<br>Window Film</span>
          <span class="ag-row-desc">Kaca film untuk rumah, kantor, dan gedung komersial agar ruangan lebih nyaman.
            <ul><li>Panas &amp; silau</li><li>Privasi</li><li>Tampilan modern</li></ul></span>
          <span class="ag-row-media"><img src="{{ asset('assets/frontend/ag/service-bangunan.webp') }}" width="1100" height="488" loading="lazy" alt="Kaca film gedung dan bangunan"></span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.ppf.catalog') }}">
          <span class="ag-row-num">03</span>
          <span class="ag-row-title">Paint Protection<br>Film</span>
          <span class="ag-row-desc">Lapisan pelindung cat bodi kendaraan dengan pilihan tampilan Gloss dan Matte.
            <ul><li>Perlindungan cat</li><li>Gloss &amp; Matte</li><li>Teknisi ACEGARD</li></ul></span>
          <span class="ag-row-media"><img src="{{ asset('assets/frontend/ag/service-ppf.webp') }}" width="1100" height="488" loading="lazy" alt="Paint Protection Film pada mobil"></span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
    </ul>
  </div>
</section>

{{-- ───────── Why ACEGARD ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-why-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <div>
        <span class="ag-eyebrow">Why ACEGARD</span>
        <h2 class="ag-h2" id="ag-why-title">Mengapa memilih ACEGARD</h2>
      </div>
    </div>
    <div class="ag-why">
      @foreach ([
        ['mdi:diamond-stone',         'Premium Material',         'Pilihan material film untuk berbagai kebutuhan kendaraan dan bangunan.'],
        ['mdi:account-wrench',        'Professional Installation','Pemasangan dikerjakan oleh teknisi ACEGARD.'],
        ['mdi:shield-check-outline',  'Protection Technology',    'Solusi perlindungan untuk kaca dan cat bodi.'],
        ['mdi:check-decagram-outline','Quality-Focused',          'Pemasangan dengan perhatian pada kerapian dan hasil akhir.'],
        ['mdi:handshake-outline',     'Customer-Oriented',        'Konsultasi yang jelas sebelum, saat, dan setelah pemasangan.'],
      ] as $i => [$icon, $title, $desc])
        <div class="ag-why-item ag-reveal">
          <span class="n">0{{ $i + 1 }}</span>
          <iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon>
          <h3>{{ $title }}</h3>
          <p>{{ $desc }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── Interactive experience (pratinjau) ───────── --}}
<section class="ag-section tint" aria-labelledby="ag-sim-title">
  <div class="ag-container ag-split">
    <div class="ag-reveal">
      <span class="ag-eyebrow">Interactive Experience</span>
      <h2 class="ag-h2" id="ag-sim-title" style="margin-top:16px">Coba tingkat kegelapan kaca film</h2>
      <p class="ag-lead">Simulator kaca film mobil akan membantu Anda membayangkan tampilan berbagai persentase VLT sebelum memilih. Semakin rendah angka VLT, semakin gelap kaca film.</p>
      <a class="ag-btn ag-btn-primary" href="{{ route('frontend.service.car') }}">Lihat Kaca Film Mobil</a>
    </div>
    <div class="ag-reveal">
      <div class="ag-sim-stage">
        <span class="ag-soon">Segera hadir</span>
        <img src="{{ asset('assets/frontend/ag/hero-car.webp') }}" width="1200" height="799" loading="lazy" alt="Pratinjau simulator kaca film mobil">
        <div class="ag-chips" aria-hidden="true">
          @foreach ([5, 20, 35, 50, 70] as $v)
            <span class="ag-chip {{ $v === 35 ? 'is-on' : '' }}">{{ $v }}%</span>
          @endforeach
        </div>
      </div>
      <p class="ag-note">Pratinjau tampilan. Simulator interaktif sedang disiapkan. Hasil simulator nantinya hanya ilustrasi visual, bukan pengukuran VLT.</p>
    </div>
  </div>
</section>

{{-- ───────── Our Products ───────── --}}
<section class="ag-section" id="produk" aria-labelledby="ag-prod-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <div>
        <span class="ag-eyebrow">Our Products</span>
        <h2 class="ag-h2" id="ag-prod-title">Pilih kategori produk</h2>
      </div>
      <p class="ag-lead">Detail spesifikasi dan harga dapat dikonsultasikan langsung dengan tim ACEGARD.</p>
    </div>

    <ul class="ag-rows is-simple">
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.service.car') }}">
          <span class="ag-row-num">01</span>
          <span class="ag-row-title">Kaca Film Mobil</span>
          <span class="ag-row-desc">Kaca film untuk kaca depan, samping, belakang, dan sunroof.</span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.service.building') }}">
          <span class="ag-row-num">02</span>
          <span class="ag-row-title">Kaca Film Gedung</span>
          <span class="ag-row-desc">Kaca film untuk rumah, kantor, dan gedung komersial.</span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.ppf.catalog') }}">
          <span class="ag-row-num">03</span>
          <span class="ag-row-title">Paint Protection Film</span>
          <span class="ag-row-desc">
            <span class="visually-hidden">Seri PPF:</span>
            <ul class="ag-tags">@foreach (['ARMOR', 'ARMOR PLUS', 'AEGIS', 'AEGIS MAX+', 'MATTE'] as $s)<li>{{ $s }}</li>@endforeach</ul>
          </span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
    </ul>
  </div>
</section>

{{-- ───────── CTA ───────── --}}
<section class="ag-section" style="padding-top:0" aria-labelledby="ag-cta-title">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal">
      <h2 id="ag-cta-title">Siap berkonsultasi?</h2>
      <p>Ceritakan kebutuhan Anda melalui WhatsApp. Tim ACEGARD akan membantu memilih produk yang sesuai.</p>
      <div class="ag-cta-actions">
        <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="cta-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi dan booking pemasangan.') }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi via WhatsApp
        </a>
        <a class="ag-btn ag-btn-ghost" href="{{ route('frontend.contact') }}">Hubungi Kami</a>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
  (function () {
    var els = document.querySelectorAll('.ag-reveal');
    if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('is-visible'); }); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); } });
    }, { threshold: .1, rootMargin: '0px 0px -5% 0px' });
    els.forEach(function (e) { io.observe(e); });
  })();
</script>
@endpush
