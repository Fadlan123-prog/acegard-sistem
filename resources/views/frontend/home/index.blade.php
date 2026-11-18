@extends('frontend.layout.index')

@php use App\Support\Whatsapp; @endphp

@section('title', 'Acegard Indonesia - Kaca Film Mobil, Kaca Film Gedung & PPF')
@section('meta_description', 'ACEGARD menyediakan kaca film mobil, kaca film gedung dan bangunan, serta Paint Protection Film (PPF). Konsultasikan kebutuhan Anda melalui WhatsApp.')
@section('body-class', 'ag-page')

@section('content')

{{-- ───────── Hero ───────── --}}
<section class="ag-hero" aria-labelledby="ag-hero-title">
  <div class="ag-container ag-hero-grid">
    <div class="ag-reveal">
      <span class="ag-eyebrow">ACEGARD</span>
      <h1 id="ag-hero-title">Protection, Comfort <span>&amp; Clarity.</span></h1>
      <p>Premium Automotive Window Film, Architectural Film, and Paint Protection Solutions untuk kendaraan dan bangunan Anda.</p>
      <div class="ag-hero-actions">
        <a class="ag-btn ag-btn-primary" href="#produk">Explore Our Products</a>
        <a class="ag-btn ag-btn-secondary whatsapp-btn" data-gtm-label="hero-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Contact Us
        </a>
      </div>
    </div>
    <div class="ag-hero-media ag-reveal">
      <img src="{{ asset('assets/frontend/ag/hero-car.webp') }}" width="1200" height="799" alt="Mobil sedan perak dengan kaca film ACEGARD" fetchpriority="high">
    </div>
  </div>
</section>

{{-- ───────── Our Services ───────── --}}
<section class="ag-section" id="layanan" aria-labelledby="ag-services-title">
  <div class="ag-container">
    <div class="ag-head-center ag-reveal">
      <span class="ag-eyebrow">Our Services</span>
      <h2 class="ag-h2" id="ag-services-title">Tiga layanan utama ACEGARD</h2>
      <p class="ag-lead">Pilih layanan sesuai kebutuhan Anda, lalu konsultasikan pilihan produk dengan tim kami.</p>
    </div>

    <div class="ag-grid-3">
      <article class="ag-card ag-reveal">
        <div class="ag-card-media"><img src="{{ asset('assets/frontend/ag/service-otomotif.webp') }}" width="1100" height="488" loading="lazy" alt="Kaca film mobil"></div>
        <div class="ag-card-body">
          <h3>Automotive Window Film</h3>
          <p>Kaca film untuk kenyamanan dan privasi berkendara, dengan beragam pilihan tingkat kegelapan.</p>
          <ul class="ag-checks">
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Kenyamanan kabin</li>
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Privasi berkendara</li>
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Pilihan tingkat kegelapan</li>
          </ul>
          <a class="ag-link" href="{{ route('frontend.service.car') }}">Lihat detail <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a>
        </div>
      </article>

      <article class="ag-card ag-reveal">
        <div class="ag-card-media"><img src="{{ asset('assets/frontend/ag/service-bangunan.webp') }}" width="1100" height="488" loading="lazy" alt="Kaca film gedung dan bangunan"></div>
        <div class="ag-card-body">
          <h3>Building Window Film</h3>
          <p>Kaca film untuk rumah, kantor, dan gedung komersial agar ruangan lebih nyaman.</p>
          <ul class="ag-checks">
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Membantu mengurangi panas dan silau</li>
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Membantu menjaga privasi</li>
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Tampilan bangunan lebih modern</li>
          </ul>
          <a class="ag-link" href="{{ route('frontend.service.building') }}">Lihat detail <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a>
        </div>
      </article>

      <article class="ag-card ag-reveal">
        <div class="ag-card-media"><img src="{{ asset('assets/frontend/ag/service-ppf.webp') }}" width="1100" height="488" loading="lazy" alt="Paint Protection Film pada mobil"></div>
        <div class="ag-card-body">
          <h3>Paint Protection Film</h3>
          <p>Lapisan pelindung cat bodi kendaraan dengan pilihan tampilan Gloss dan Matte.</p>
          <ul class="ag-checks">
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Perlindungan cat bodi</li>
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Pilihan Gloss dan Matte</li>
            <li><iconify-icon icon="mdi:check-circle" aria-hidden="true"></iconify-icon> Dipasang oleh teknisi ACEGARD</li>
          </ul>
          <a class="ag-link" href="{{ route('frontend.ppf.catalog') }}">Lihat detail <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a>
        </div>
      </article>
    </div>
  </div>
</section>

{{-- ───────── Why ACEGARD ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-why-title">
  <div class="ag-container">
    <div class="ag-head-center ag-reveal">
      <span class="ag-eyebrow">Why ACEGARD</span>
      <h2 class="ag-h2" id="ag-why-title">Mengapa memilih ACEGARD</h2>
    </div>
    <div class="ag-why">
      @foreach ([
        ['mdi:diamond-stone',        'Premium Material',        'Pilihan material film untuk berbagai kebutuhan kendaraan dan bangunan.'],
        ['mdi:account-wrench',       'Professional Installation','Pemasangan dikerjakan oleh teknisi ACEGARD.'],
        ['mdi:shield-check-outline', 'Protection Technology',   'Solusi perlindungan untuk kaca dan cat bodi.'],
        ['mdi:check-decagram-outline','Quality-Focused',         'Pemasangan dengan perhatian pada kerapian dan hasil akhir.'],
        ['mdi:handshake-outline',    'Customer-Oriented',       'Konsultasi yang jelas sebelum, saat, dan setelah pemasangan.'],
      ] as [$icon, $title, $desc])
        <div class="ag-why-item ag-reveal">
          <span class="ag-why-icon"><iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon></span>
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
      <h2 class="ag-h2" id="ag-sim-title">Coba tingkat kegelapan kaca film</h2>
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
    <div class="ag-head-center ag-reveal">
      <span class="ag-eyebrow">Our Products</span>
      <h2 class="ag-h2" id="ag-prod-title">Pilih kategori produk</h2>
      <p class="ag-lead">Detail spesifikasi dan harga dapat dikonsultasikan langsung dengan tim ACEGARD.</p>
    </div>
    <div class="ag-grid-3">
      <div class="ag-prod ag-reveal">
        <h3>Kaca Film Mobil</h3>
        <p>Kaca film untuk kaca depan, samping, belakang, dan sunroof.</p>
        <a class="ag-link" href="{{ route('frontend.service.car') }}">Lihat produk <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a>
      </div>
      <div class="ag-prod ag-reveal">
        <h3>Kaca Film Gedung</h3>
        <p>Kaca film untuk rumah, kantor, dan gedung komersial.</p>
        <a class="ag-link" href="{{ route('frontend.service.building') }}">Lihat produk <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a>
      </div>
      <div class="ag-prod ag-reveal">
        <h3>Paint Protection Film</h3>
        <ul class="ag-tags" aria-label="Seri PPF">
          @foreach (['ARMOR', 'ARMOR PLUS', 'AEGIS', 'AEGIS MAX+', 'MATTE'] as $s)<li>{{ $s }}</li>@endforeach
        </ul>
        <a class="ag-link" href="{{ route('frontend.ppf.catalog') }}">Lihat produk <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a>
      </div>
    </div>
  </div>
</section>

{{-- ───────── CTA ───────── --}}
<section class="ag-section tint ag-cta" aria-labelledby="ag-cta-title">
  <div class="ag-container ag-reveal">
    <h2 class="ag-h2" id="ag-cta-title">Siap berkonsultasi?</h2>
    <p class="ag-lead">Ceritakan kebutuhan Anda melalui WhatsApp. Tim ACEGARD akan membantu memilih produk yang sesuai.</p>
    <div class="ag-cta-actions">
      <a class="ag-btn ag-btn-primary whatsapp-btn" data-gtm-label="cta-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi dan booking pemasangan.') }}">
        <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi via WhatsApp
      </a>
      <a class="ag-btn ag-btn-secondary" href="{{ route('frontend.contact') }}">Hubungi Kami</a>
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
    }, { threshold: .12 });
    els.forEach(function (e) { io.observe(e); });
  })();
</script>
@endpush
