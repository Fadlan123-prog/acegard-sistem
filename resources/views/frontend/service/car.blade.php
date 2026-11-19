@extends('frontend.layout.index')

@php use App\Support\Whatsapp; @endphp

@section('title', 'Kaca Film Mobil - Acegard Indonesia')
@section('meta_description', 'Kaca film mobil ACEGARD: BLACK MASTER, NOTCH UV400, dan 4K ALPHA PRO. Lihat spesifikasi VLT, TSER, dan penolakan UV tiap varian, lalu konsultasikan lewat WhatsApp.')
@section('body-class', 'ag-page')

@section('content')

@php
  $img = fn ($f) => asset('assets/frontend/ag/' . $f);

  // Sumber: lembar spesifikasi produk ACEGARD. Angka ditampilkan apa adanya.
  $rows = [
    ['color', 'Warna film'],
    ['vlt',   'Visible Light Transmission (VLT)'],
    ['ir9',   'IR Rejection 900 nm'],
    ['ir14',  'IR Rejection 1400 nm'],
    ['uv',    'UV Rejection'],
    ['tser',  'TSER'],
    ['dark',  'Darkness'],
    ['shgc',  'Solar Heat Gain Coefficient'],
  ];
  $series = \App\Support\FilmCatalog::series();
  $swatch = \App\Support\FilmCatalog::swatch();
  $all    = \App\Support\FilmCatalog::variants();
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-car-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Automotive Film</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">Automotive Window Film</span>
    <h1 class="ag-display" id="ag-car-title">Kaca film mobil <b>sejuk, jernih, privasi terjaga.</b></h1>
    <p class="ag-hero-sub">Tiga seri film dengan 13 varian VLT. Lihat spesifikasi lengkapnya, lalu konsultasikan pilihan yang cocok untuk tiap kaca mobil Anda.</p>
    <div class="ag-hero-actions">
      <a class="ag-btn ag-btn-primary" href="#seri">Lihat Spesifikasi</a>
      <a class="ag-btn ag-btn-secondary" href="{{ route('frontend.simulator') }}">Coba Simulator VLT</a>
      <a class="ag-btn ag-btn-secondary whatsapp-btn" data-gtm-label="car-hero-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi soal kaca film mobil.') }}">
        <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi Gratis
      </a>
    </div>
    <div class="ag-hero-car is-compact">
      <img src="{{ $img('hero-car-clear.webp') }}" width="1334" height="889" alt="Mobil sedan perak dengan kaca film ACEGARD" fetchpriority="high">
    </div>
  </div>
</section>

{{-- ───────── Manfaat ───────── --}}
<section class="ag-section" aria-labelledby="ag-benefit-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Benefits</span>
      <h2 class="ag-h2" id="ag-benefit-title">Manfaat kaca film <b>untuk mobil Anda</b></h2>
    </div>
    <div class="ag-grid c3">
      @foreach ([
        ['mdi:white-balance-sunny',  'Tolak panas', 'Penolakan inframerah 99% pada 900 nm dan 1400 nm. TSER mencapai 95% pada varian tergelap.'],
        ['mdi:shield-sun-outline',   'Tolak sinar UV', 'UV Rejection 99,9% pada semua varian, melindungi kulit dan interior dari pudar.'],
        ['mdi:weather-night',        'Night Vision', 'Seri NOTCH UV400 dilengkapi Night Vision untuk menjaga detail jalan di cahaya rendah.'],
        ['mdi:eye-off-outline',      'Privasi kabin', 'Pilihan VLT dari 71% sampai 3% untuk mengatur seberapa terlihat kabin dari luar.'],
        ['mdi:signal',               'Sinyal tetap lancar', 'Lapisan Nano Ceramic non-metal tidak mengganggu sinyal seluler, WiFi, dan GPS.'],
        ['mdi:car-cog',              'Pemasangan profesional', 'Dipasang teknisi ACEGARD di workshop Cipondoh, Tangerang, lengkap dengan garansi.'],
      ] as [$icon, $title, $desc])
        <div class="ag-card ag-feature is-hover ag-reveal">
          <span class="ic"><iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon></span>
          <h3>{{ $title }}</h3>
          <p>{{ $desc }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── Seri & spesifikasi ───────── --}}
<section class="ag-section alt" id="seri" aria-labelledby="ag-series-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Series &amp; Specification</span>
      <h2 class="ag-h2" id="ag-series-title">Pilih seri <b>dan varian VLT</b></h2>
      <p class="ag-lead">VLT adalah persentase cahaya tampak yang menembus film. Semakin rendah angkanya, semakin gelap filmnya. Harga dapat dikonsultasikan dengan tim kami.<span class="ag-hint"> Geser tabel ke samping untuk melihat semua varian.</span></p>
    </div>

    <div class="ag-series">
      @foreach ($series as $s)
        <article class="ag-prod ag-reveal" id="{{ \Illuminate\Support\Str::slug($s['name']) }}">
          <div class="ag-prod-info">
            <span class="ag-card-media"><img src="{{ $img($s['image']) }}" width="900" height="600" loading="lazy" alt="Kaca film {{ $s['name'] }}"></span>
            <h3>{{ $s['name'] }}</h3>
            <p class="ag-prod-tag">{{ $s['tag'] }}</p>
            <p>{{ $s['desc'] }}</p>
            <a class="ag-btn ag-btn-primary ag-btn-sm whatsapp-btn" data-gtm-label="car-{{ \Illuminate\Support\Str::slug($s['name']) }}" target="_blank" rel="noopener"
               href="{{ Whatsapp::url('Halo ACEGARD, saya tertarik dengan kaca film ' . $s['name']) }}">Tanya {{ $s['name'] }}</a>
          </div>
          <div class="ag-spec-wrap" tabindex="0" role="region" aria-label="Spesifikasi {{ $s['name'] }}">
            <table class="ag-spec">
              <caption class="visually-hidden">Spesifikasi {{ $s['name'] }} per varian</caption>
              <thead>
                <tr><th scope="col">Spesifikasi</th>@foreach ($s['variants'] as $x)<th scope="col">{{ $x['code'] }}</th>@endforeach</tr>
              </thead>
              <tbody>
                @foreach ($rows as [$key, $label])
                  <tr>
                    <th scope="row">{{ $label }}</th>
                    @foreach ($s['variants'] as $x)
                      <td>
                        @if ($key === 'color')<span class="ag-swatch" style="background:{{ $swatch[$x['color']] ?? '#222' }}" aria-hidden="true"></span>@endif{{ $x[$key] }}
                      </td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </article>
      @endforeach
    </div>
    <p class="ag-note ag-center ag-reveal">Angka bersumber dari lembar spesifikasi produk ACEGARD. Hasil di lapangan dapat berbeda menurut kendaraan dan kondisi cuaca. Mencari film untuk rumah atau gedung? Lihat <a class="ag-link" href="{{ route('frontend.service.building') }}">Building Film</a>.</p>
  </div>
</section>

{{-- ───────── Perbandingan VLT ───────── --}}
<section class="ag-section" id="bandingkan" aria-labelledby="ag-compare-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Compare</span>
      <h2 class="ag-h2" id="ag-compare-title">Bandingkan semua varian <b>dari terang ke gelap</b></h2>
      <p class="ag-lead">Diurutkan menurut VLT. Pilih VLT sesuai kebutuhan privasi, kenyamanan, dan aturan berkendara setempat.</p>
    </div>
    <div class="ag-spec-wrap ag-reveal" tabindex="0" role="region" aria-label="Perbandingan semua varian">
      <table class="ag-spec ag-compare">
        <caption class="visually-hidden">Perbandingan 13 varian kaca film mobil ACEGARD menurut VLT</caption>
        <thead>
          <tr><th scope="col">Varian</th><th scope="col">Seri</th><th scope="col">Warna</th><th scope="col">VLT</th><th scope="col">TSER</th><th scope="col">Darkness</th></tr>
        </thead>
        <tbody>
          @foreach ($all as $x)
            <tr>
              <th scope="row">{{ $x['code'] }}</th>
              <td>{{ $x['series'] }}</td>
              <td><span class="ag-swatch" style="background:{{ $swatch[$x['color']] ?? '#222' }}" aria-hidden="true"></span>{{ $x['color'] }}</td>
              <td><span class="ag-bar" style="--w:{{ (int) $x['vlt'] }}%"><i></i></span>{{ $x['vlt'] }}</td>
              <td>{{ $x['tser'] }}</td>
              <td>{{ $x['dark'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

{{-- ───────── Galeri ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-cargal-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Gallery</span>
      <h2 class="ag-h2" id="ag-cargal-title">Pemasangan <b>di workshop ACEGARD</b></h2>
    </div>
    <div class="ag-grid c3 ag-reveal">
      @foreach ([['gallery-install.webp', 'Teknisi memasang kaca film pada jendela mobil'], ['gallery-view.webp', 'Pemandangan dari balik kaca film mobil'], ['gallery-sticker.webp', 'Stiker ACEGARD pada kaca mobil']] as [$f, $alt])
        <figure class="ag-shot"><img src="{{ $img($f) }}" width="1000" height="700" loading="lazy" alt="{{ $alt }}"></figure>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── FAQ ───────── --}}
<section class="ag-section" aria-labelledby="ag-carfaq-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">FAQ</span>
      <h2 class="ag-h2" id="ag-carfaq-title">Pertanyaan seputar <b>kaca film mobil</b></h2>
    </div>
    <div class="ag-faq ag-reveal">
      @foreach ([
        ['Apa itu VLT dan TSER?', 'VLT (Visible Light Transmission) adalah persentase cahaya tampak yang menembus film, makin rendah makin gelap. TSER (Total Solar Energy Rejected) adalah persentase total energi matahari yang ditolak film, makin tinggi makin sejuk.'],
        ['Seri mana yang cocok untuk saya?', 'NOTCH UV400 cocok bila Anda mengutamakan Night Vision dan proteksi UV. 4K ALPHA PRO menawarkan penolakan panas tinggi dengan Nano Ceramic Tungsten dan Tin. BLACK MASTER cocok untuk tampilan Charcoal Black. Tim kami membantu mencocokkan seri dengan kebutuhan Anda.'],
        ['Apakah kaca film mengganggu sinyal?', 'Tidak. Lapisan Nano Ceramic ACEGARD bersifat non-metal sehingga sinyal seluler, WiFi, dan GPS tetap berfungsi.'],
        ['Apakah aman dipakai berkendara malam hari?', 'Seri NOTCH UV400 dilengkapi Night Vision. Untuk kaca depan, pilih VLT yang lebih tinggi agar visibilitas tetap baik dan sesuai aturan setempat. Konsultasikan dengan tim kami.'],
        ['Berapa harga dan di mana pemasangannya?', 'Harga bergantung pada tipe kendaraan dan varian film. Hubungi kami lewat WhatsApp untuk penawaran. Pemasangan dilakukan oleh teknisi ACEGARD di workshop kami di Cipondoh, Kota Tangerang, dan setiap pemasangan mendapat garansi.'],
      ] as $i => [$q, $a])
        <details class="ag-faq-item" @if($i === 0) open @endif>
          <summary>{{ $q }}</summary>
          <p>{{ $a }}</p>
        </details>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── CTA ───────── --}}
<section class="ag-section" style="padding-top:0" aria-labelledby="ag-carcta-title">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal">
      <h2 id="ag-carcta-title">Bingung <b>memilih VLT?</b></h2>
      <p>Sebutkan tipe mobil dan kebutuhan Anda. Tim ACEGARD akan merekomendasikan seri dan varian untuk tiap kaca.</p>
      <div class="ag-cta-actions">
        <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="car-cta-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin konsultasi memilih VLT kaca film mobil.') }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi via WhatsApp
        </a>
        <a class="ag-btn ag-btn-ghost" href="{{ route('warranty.form') }}">Cek Garansi</a>
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
    }, { threshold: .06, rootMargin: '0px 0px -4% 0px' });
    els.forEach(function (e) { io.observe(e); });
  })();
</script>
@endpush
