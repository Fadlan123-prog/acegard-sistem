@extends('frontend.layout.index')

@php
  // Pertanyaan dan jawaban: satu sumber di FaqCatalog (dipakai juga di halaman FAQ dan data terstruktur).
  $faqs = \App\Support\FaqCatalog::home();
@endphp

@php use App\Support\Whatsapp; @endphp

@section('title', 'Acegard Indonesia - Kaca Film Mobil, Kaca Film Gedung & PPF')
@section('meta_description', 'ACEGARD menyediakan kaca film mobil, kaca film gedung dan bangunan, serta Paint Protection Film (PPF). Konsultasikan kebutuhan Anda melalui WhatsApp.')
@section('body-class', 'ag-page')

@section('content')

@php
  $clients = \App\Support\SiteContent::clients();
  $img = fn ($f) => asset('assets/frontend/ag/' . $f);
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero" aria-labelledby="ag-hero-title">
  <div class="ag-container">
    <span class="ag-eyebrow ag-hero-tag">Cooler Cabins, Clearer Views</span>
    <h1 class="ag-display" id="ag-hero-title">Kaca film premium <b>untuk mobil dan bangunan.</b></h1>
    <p class="ag-hero-sub">Menolak panas dan sinar UV, menjaga privasi, dan melindungi cat kendaraan. Dipasang oleh teknisi ACEGARD di Tangerang.</p>
    <div class="ag-hero-actions">
      <a class="ag-btn ag-btn-primary" href="#produk">Explore Our Products</a>
      <a class="ag-btn ag-btn-secondary whatsapp-btn" data-gtm-label="hero-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}">
        <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Contact Us
      </a>
    </div>
    <div class="ag-hero-car">
      <img src="{{ $img('hero-car-clear.webp') }}" width="1334" height="889" alt="Mobil sedan perak dengan kaca film ACEGARD" fetchpriority="high">
    </div>
  </div>
  <div class="ag-marquee" aria-label="Dipercaya oleh">
    <div class="ag-marquee-track">
      <ul>@foreach ($clients as $c)<li>{{ $c }}</li>@endforeach</ul>
      <ul aria-hidden="true">@foreach ($clients as $c)<li>{{ $c }}</li>@endforeach</ul>
      <ul aria-hidden="true">@foreach ($clients as $c)<li>{{ $c }}</li>@endforeach</ul>
      <ul aria-hidden="true">@foreach ($clients as $c)<li>{{ $c }}</li>@endforeach</ul>
    </div>
  </div>
</section>

{{-- ───────── Layanan ───────── --}}
<section class="ag-section" id="layanan" aria-labelledby="ag-services-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Our Services</span>
      <h2 class="ag-h2" id="ag-services-title">Tiga layanan <b>utama ACEGARD</b></h2>
      <p class="ag-lead">Setiap layanan punya pilihan produk berbeda. Pilih sesuai kebutuhan, lalu konsultasikan tipe film yang paling cocok dengan tim kami.</p>
    </div>
    <div class="ag-grid c3">
      <a class="ag-card ag-reveal" href="{{ route('frontend.service.car') }}">
        <span class="ag-card-media"><img src="{{ $img('service-otomotif.webp') }}" width="1100" height="488" loading="lazy" alt="Kaca film mobil"></span>
        <span class="ag-card-body">
          <h3>Automotive Window Film</h3>
          <p>Kaca depan, samping, belakang, dan sunroof. Menolak panas dan sinar UV, mengurangi silau, dan menjaga privasi kabin dengan pilihan VLT.</p>
          <ul class="ag-pills"><li class="ag-pill">Tolak panas &amp; UV</li><li class="ag-pill">Anti silau</li><li class="ag-pill">Privasi</li></ul>
          <span class="ag-card-foot"><span class="ag-link">Lihat kaca film mobil</span><span class="ag-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span></span>
        </span>
      </a>
      <a class="ag-card ag-reveal" href="{{ route('frontend.service.building') }}">
        <span class="ag-card-media"><img src="{{ $img('service-bangunan.webp') }}" width="1100" height="488" loading="lazy" alt="Kaca film gedung dan bangunan"></span>
        <span class="ag-card-body">
          <h3>Building Window Film</h3>
          <p>Rumah, kantor, hotel, dan gedung komersial. Menurunkan panas dari jendela, meringankan kerja AC, dan melindungi interior dari pudar.</p>
          <ul class="ag-pills"><li class="ag-pill">Hemat energi AC</li><li class="ag-pill">Interior awet</li><li class="ag-pill">Privasi</li></ul>
          <span class="ag-card-foot"><span class="ag-link">Lihat kaca film gedung</span><span class="ag-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span></span>
        </span>
      </a>
      <a class="ag-card ag-reveal" href="{{ route('frontend.ppf.catalog') }}">
        <span class="ag-card-media"><img src="{{ $img('service-ppf.webp') }}" width="1100" height="488" loading="lazy" alt="Paint Protection Film pada mobil"></span>
        <span class="ag-card-body">
          <h3>Paint Protection Film</h3>
          <p>Lapisan transparan yang melindungi cat dari goresan halus, kotoran, dan paparan lingkungan. Pilihan tampilan Gloss dan Matte.</p>
          <ul class="ag-pills"><li class="ag-pill">Perlindungan cat</li><li class="ag-pill">Gloss &amp; Matte</li><li class="ag-pill">Beberapa seri</li></ul>
          <span class="ag-card-foot"><span class="ag-link">Lihat seri PPF</span><span class="ag-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span></span>
        </span>
      </a>
    </div>
  </div>
</section>

{{-- ───────── Produk kaca film mobil ───────── --}}
<section class="ag-section alt" id="produk" aria-labelledby="ag-prod-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Our Products</span>
      <h2 class="ag-h2" id="ag-prod-title">Pilihan <b>kaca film mobil</b></h2>
      <p class="ag-lead">Tiga seri film untuk kebutuhan berbeda. Spesifikasi lengkap dan harga dapat dikonsultasikan langsung dengan tim ACEGARD.</p>
    </div>
    <div class="ag-grid c3 ag-film">
      @foreach ([
        ['film-notch-uv400.webp',   'NOTCH UV400',  'Jernih & tetap sejuk',            'Kejernihan maksimal dengan perlindungan panas yang efektif. Tampilan tetap natural.'],
        ['film-4k-alpha-pro.webp',  '4K ALPHA PRO', 'Nyaman dengan privasi yang pas',  'Perpaduan visibilitas, kenyamanan, dan privasi. Jernih dari dalam, stylish dari luar.'],
        ['film-black-master.webp',  'BLACK MASTER', 'Tampilan gelap yang tegas',       'Pilihan untuk Anda yang mengutamakan privasi dan tampilan kabin yang lebih gelap.'],
      ] as [$file, $name, $tag, $desc])
        <article class="ag-card is-hover ag-reveal">
          <span class="ag-card-media"><img src="{{ $img($file) }}" width="900" height="600" loading="lazy" alt="Kaca film {{ $name }}"></span>
          <div class="ag-card-body">
            <h3>{{ $name }}</h3>
            <p><strong>{{ $tag }}</strong></p>
            <p>{{ $desc }}</p>
            <div class="ag-card-foot">
              <span class="ag-price">Hubungi untuk harga</span>
              <a class="ag-btn ag-btn-secondary ag-btn-sm whatsapp-btn" data-gtm-label="film-{{ \Illuminate\Support\Str::slug($name) }}" target="_blank" rel="noopener"
                 href="{{ Whatsapp::url('Halo ACEGARD, saya tertarik dengan kaca film ' . $name) }}">Tanya</a>
            </div>
          </div>
        </article>
      @endforeach
    </div>
    <p class="ag-center ag-reveal" style="margin-top:36px"><a class="ag-btn ag-btn-primary" href="{{ route('frontend.service.car') }}">Lihat Spesifikasi Lengkap</a></p>
  </div>
</section>

{{-- ───────── Why ACEGARD ───────── --}}
<section class="ag-section" aria-labelledby="ag-why-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Why ACEGARD</span>
      <h2 class="ag-h2" id="ag-why-title">Mengapa <b>memilih ACEGARD</b></h2>
    </div>
    <div class="ag-grid c3">
      @foreach ([
        ['mdi:diamond-stone',         'Premium Material',          'Diproduksi di pabrik bersertifikat internasional di Korea dengan kontrol kualitas berlapis, dan didistribusikan resmi di Indonesia.'],
        ['mdi:account-wrench',        'Professional Installation', 'Pemasangan dikerjakan teknisi ACEGARD di workshop kami, dengan prosedur yang sama untuk setiap pelanggan.'],
        ['mdi:shield-check-outline',  'Protection Technology',     'Nano Ceramic dengan lapisan Titanium dan Tungsten menolak panas inframerah dan sinar UV tanpa mengganggu sinyal.'],
        ['mdi:check-decagram-outline','Quality-Focused',           'Warna stabil, tahan pudar, dan kejernihan optik yang konsisten di setiap varian produk.'],
        ['mdi:handshake-outline',     'Customer-Oriented',         'Konsultasi sebelum pemasangan, garansi, dan layanan after sales yang jelas setelahnya.'],
      ] as [$icon, $title, $desc])
        <div class="ag-card ag-feature is-hover ag-reveal">
          <span class="ic"><iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon></span>
          <h3>{{ $title }}</h3>
          <p>{{ $desc }}</p>
        </div>
      @endforeach
      <div class="ag-card ag-feature is-cta ag-reveal">
        <div>
          <h3>Punya pertanyaan?</h3>
          <p>Tim ACEGARD siap membantu memilih film yang sesuai.</p>
        </div>
        <a class="ag-btn ag-btn-light ag-btn-sm whatsapp-btn" data-gtm-label="why-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin bertanya.') }}">Tanya via WhatsApp</a>
      </div>
    </div>
  </div>
</section>

{{-- ───────── About ───────── --}}
<section class="ag-section alt" id="tentang" aria-labelledby="ag-about-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal" style="margin-bottom:28px">
      <span class="ag-eyebrow">About ACEGARD</span>
      <h2 class="ag-h2" id="ag-about-title">Kaca film Nano Ceramic <b>untuk kendaraan dan bangunan</b></h2>
    </div>
    <div class="ag-prose ag-reveal">
      <p>ACEGARD Indonesia menyediakan kaca film mobil, kaca film gedung dan rumah, serta Paint Protection Film (PPF). Produk kami diproduksi di pabrik bersertifikat internasional di Korea, melewati kontrol kualitas berlapis, lalu didistribusikan secara resmi di Indonesia.</p>
      <p>Kaca film ACEGARD memakai teknologi Nano Ceramic Matrix Composite. Lapisan dasarnya berupa dual-layer Nano Ceramic yang diproses dengan teknologi sputtering dan diperkuat unsur logam Tin, Titanium, dan Tungsten. Hasilnya tujuh lapisan yang stabil secara optik, tahan lama, dan tidak mengganggu sinyal seluler, WiFi, maupun GPS.</p>
      <p>Pemasangan dikerjakan oleh teknisi di workshop kami di Cipondoh, Kota Tangerang. Setiap pelanggan mendapat konsultasi sebelum pemasangan, garansi, dan layanan after sales.</p>
    </div>
    <dl class="ag-stats ag-reveal">
      <div><dt>Tolak inframerah</dt><dd>99<small>%</small></dd></div>
      <div><dt>TSER hingga</dt><dd>95<small>%</small></dd></div>
      <div><dt>Tolak sinar UV</dt><dd>99,9<small>%</small></dd></div>
      <div><dt>Penurunan suhu interior</dt><dd>&plusmn;5<small>&deg;C</small></dd></div>
    </dl>
    <p class="ag-note ag-center ag-reveal">Angka bersumber dari lembar spesifikasi produk ACEGARD. Hasil di lapangan dapat berbeda menurut tipe film, kendaraan atau bangunan, dan kondisi cuaca.</p>
  </div>
</section>

{{-- ───────── Technology ───────── --}}
<section class="ag-section" id="teknologi" aria-labelledby="ag-tech-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Technology</span>
      <h2 class="ag-h2" id="ag-tech-title">Teknologi di balik <b>kaca film ACEGARD</b></h2>
      <p class="ag-lead">Tujuh karakter utama yang membedakan kaca film ACEGARD, dari penolakan panas sampai kestabilan warna.</p>
    </div>
    <div class="ag-grid c4">
      @foreach ([
        ['High TSER Performance',          'Menolak energi surya total (TSER) hingga 95% pada varian tergelap, sehingga kabin dan ruangan terasa lebih sejuk.'],
        ['Tungsten Thermal Layer',         'Lapisan Tungsten memperkuat penolakan panas inframerah hingga 99% pada 900 nm dan 1400 nm.'],
        ['Night Vision Optical Clarity',   'VLT dijaga optimal agar detail jalan tetap terlihat di cahaya rendah, dengan glare lampu yang berkurang.'],
        ['UV400 Full Spectrum Protection', 'Menangkal UVA, UVB, dan UVC hingga 99,9% sampai 400 nm, melindungi kulit dan interior.'],
        ['Non-Metal Signal Friendly',      'Sinyal seluler, WiFi, dan GPS tetap lancar setelah kaca film terpasang.'],
        ['Color Fade Resistant',           'Warna tetap konsisten untuk pemakaian bertahun-tahun.'],
        ['Energy Efficient Color System',  'Membantu AC bekerja lebih ringan sehingga konsumsi energi harian dapat berkurang.'],
      ] as $i => [$t, $d])
        <div class="ag-card ag-feature is-hover ag-reveal">
          <span class="n">0{{ $i + 1 }}</span>
          <h3>{{ $t }}</h3>
          <p>{{ $d }}</p>
        </div>
      @endforeach
      <a class="ag-card ag-feature is-cta ag-reveal" href="{{ route('frontend.about') }}" style="text-decoration:none">
        <div>
          <h3>Pelajari lebih lanjut</h3>
          <p>Cerita lengkap teknologi Nano Ceramic ACEGARD.</p>
        </div>
        <span class="ag-btn ag-btn-light ag-btn-sm">Tentang kami</span>
      </a>
    </div>
  </div>
</section>

{{-- ───────── Galeri ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-gal-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Gallery</span>
      <h2 class="ag-h2" id="ag-gal-title">Hasil pemasangan <b>di workshop ACEGARD</b></h2>
      <p class="ag-lead">Proses pemasangan dan kendaraan pelanggan yang sudah ditangani teknisi kami.</p>
    </div>
    <div class="ag-gallery ag-reveal">
      <figure class="w2"><img src="{{ $img('gallery-install.webp') }}" width="1000" height="726" loading="lazy" alt="Teknisi memasang kaca film pada jendela mobil"></figure>
      <figure><img src="{{ $img('proj-aion-hyptec.webp') }}" width="412" height="513" loading="lazy" alt="Aion Hyptec dengan PPF ACEGARD"></figure>
      <figure><img src="{{ $img('proj-bmw-ix-drive.webp') }}" width="412" height="513" loading="lazy" alt="BMW iX xDrive dengan PPF ACEGARD"></figure>
      <figure><img src="{{ $img('proj-mazda-3.webp') }}" width="412" height="513" loading="lazy" alt="Mazda 3 dengan PPF ACEGARD"></figure>
      <figure><img src="{{ $img('proj-mini-cooper.webp') }}" width="412" height="513" loading="lazy" alt="Mini Cooper dengan PPF ACEGARD"></figure>
      <figure class="w2"><img src="{{ $img('gallery-view.webp') }}" width="998" height="664" loading="lazy" alt="Pemandangan dari balik kaca film mobil"></figure>
      <figure><img src="{{ $img('proj-vinfast.webp') }}" width="412" height="513" loading="lazy" alt="VinFast dengan PPF ACEGARD"></figure>
      <figure><img src="{{ $img('proj-denza-d-9.webp') }}" width="412" height="513" loading="lazy" alt="Denza D9 dengan PPF ACEGARD"></figure>
      <figure><img src="{{ $img('proj-jaecoo-j5.webp') }}" width="412" height="513" loading="lazy" alt="Jaecoo J5 dengan PPF ACEGARD"></figure>
      <figure><img src="{{ $img('gallery-sticker.webp') }}" width="1000" height="666" loading="lazy" alt="Stiker ACEGARD pada kaca mobil"></figure>
    </div>
  </div>
</section>

{{-- ───────── Proses ───────── --}}
<section class="ag-section" id="proses" aria-labelledby="ag-process-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">How It Works</span>
      <h2 class="ag-h2" id="ag-process-title">Dari konsultasi <b>sampai garansi</b></h2>
      <p class="ag-lead">Empat langkah yang sama untuk kendaraan maupun bangunan.</p>
    </div>
    <div class="ag-grid c4">
      @foreach ([
        ['Konsultasi',           'Ceritakan kendaraan atau bangunan Anda lewat WhatsApp. Tim kami membantu memilih tipe film sesuai kebutuhan dan anggaran.'],
        ['Pilih produk',         'Bandingkan pilihan film, tingkat kegelapan (VLT), dan tampilan, lalu tentukan jadwal pemasangan.'],
        ['Pemasangan',           'Teknisi ACEGARD memasang film di workshop kami di Cipondoh, Tangerang, dengan perhatian pada kerapian dan hasil akhir.'],
        ['Garansi & after sales','Setelah pemasangan, Anda mendapat garansi dan dukungan after sales. Cek status garansi kapan saja.'],
      ] as $i => [$t, $d])
        <div class="ag-card ag-feature is-hover ag-reveal">
          <span class="n">0{{ $i + 1 }}</span>
          <h3>{{ $t }}</h3>
          <p>{{ $d }}</p>
        </div>
      @endforeach
    </div>
    <p class="ag-center ag-reveal" style="margin-top:28px"><a class="ag-link" href="{{ route('warranty.form') }}">Cek garansi Anda <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a></p>
  </div>
</section>

{{-- ───────── Simulator ───────── --}}
<section class="ag-section tint" aria-labelledby="ag-sim-title">
  <div class="ag-container ag-split">
    <div class="ag-reveal">
      <span class="ag-eyebrow">Interactive Experience</span>
      <h2 class="ag-h2" id="ag-sim-title" style="margin-top:16px">Coba tingkat <b>kegelapan kaca film</b></h2>
      <p class="ag-lead">Simulator kaca film mobil membantu Anda melihat tampilan berbagai persentase VLT pada kaca depan dan kaca samping sebelum memilih. Semakin rendah angka VLT, semakin gelap kaca film.</p>
      <a class="ag-btn ag-btn-primary" href="{{ route('frontend.simulator') }}">Coba Simulator</a>
    </div>
    <div class="ag-reveal">
      <div class="ag-sim-stage" id="agHomeSim" style="--tint-front: .68; --tint-side: .68;">
        @include('frontend.partials.sim-car')
        <div class="ag-chips is-live" role="radiogroup" aria-label="Pilih VLT kaca film">
          @foreach ([71, 49, 32, 15, 6] as $v)
            <label class="ag-chip {{ $v === 32 ? 'is-on' : '' }}"><input type="radio" name="homevlt" value="{{ $v }}" class="visually-hidden" {{ $v === 32 ? 'checked' : '' }}><span>{{ $v }}%</span></label>
          @endforeach
        </div>
      </div>
      <p class="ag-note">Hasil simulator hanya ilustrasi visual, bukan pengukuran VLT.</p>
    </div>
  </div>
</section>

{{-- ───────── Testimoni ───────── --}}
<section class="ag-section" aria-labelledby="ag-testi-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Testimonials</span>
      <h2 class="ag-h2" id="ag-testi-title">Kata mereka <b>tentang ACEGARD</b></h2>
    </div>
    <div class="ag-grid c3">
      @foreach (\App\Support\SiteContent::testimonials() as [$name, $role, $photo, $quote])
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

{{-- ───────── FAQ ───────── --}}
<section class="ag-section alt" id="faq" aria-labelledby="ag-faq-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">FAQ</span>
      <h2 class="ag-h2" id="ag-faq-title">Pertanyaan yang <b>sering diajukan</b></h2>
      <p class="ag-lead">Belum menemukan jawabannya? <a class="ag-link" href="{{ route('frontend.faq') }}">Lihat FAQ lengkap</a> atau tanyakan langsung lewat WhatsApp.</p>
    </div>
    <div class="ag-faq ag-reveal">
      @foreach ($faqs as $i => [$q, $a])
        <details class="ag-faq-item" @if($i === 0) open @endif>
          <summary>{{ $q }}</summary>
          <p>{{ $a }}</p>
        </details>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── Lokasi ───────── --}}
<section class="ag-section" id="lokasi" aria-labelledby="ag-loc-title">
  <div class="ag-container ag-split">
    <div class="ag-reveal">
      <span class="ag-eyebrow">Visit Us</span>
      <h2 class="ag-h2" id="ag-loc-title" style="margin-top:16px">Kunjungi <b>workshop kami</b></h2>
      <p class="ag-lead">Lihat langsung contoh film, konsultasi tatap muka, dan jadwalkan pemasangan di workshop ACEGARD.</p>
      <dl class="ag-contact">
        <div><dt>Alamat</dt><dd>{{ config('acegard.address') }}</dd></div>
        <div><dt>WhatsApp</dt><dd><a class="ag-link whatsapp-btn" data-gtm-label="location-whatsapp" target="_blank" rel="noopener" href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkunjung ke workshop.') }}">{{ Whatsapp::display() }}</a></dd></div>
        <div><dt>Email</dt><dd><a class="ag-link" href="mailto:{{ config('acegard.email') }}">{{ config('acegard.email') }}</a></dd></div>
      </dl>
    </div>
    <div class="ag-reveal ag-map">
      <iframe title="Lokasi ACEGARD Indonesia di Google Maps" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen
        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7932.866895562227!2d106.6881984!3d-6.2064166!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ed2a0d3377ef%3A0x49dc44beb8c49b02!2sAcegard%20Indonesia!5e0!3m2!1sid!2sid!4v1755761775295!5m2!1sid!2sid"></iframe>
    </div>
  </div>
</section>

{{-- ───────── CTA ───────── --}}
<section class="ag-section" style="padding-top:0" aria-labelledby="ag-cta-title">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal">
      <h2 id="ag-cta-title">Siap <b>berkonsultasi?</b></h2>
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
  var stage = document.getElementById('agHomeSim');
  if (!stage) return;
  stage.addEventListener('change', function (e) {
    if (e.target.name !== 'homevlt') return;
    var t = Math.min(0.96, 1 - Number(e.target.value) / 100).toFixed(2);
    stage.style.setProperty('--tint-front', t);
    stage.style.setProperty('--tint-side', t);
    stage.querySelectorAll('.ag-chip').forEach(function (c) { c.classList.toggle('is-on', c.querySelector('input').checked); });
  });
})();
</script>
<script>
  (function () {
    var els = document.querySelectorAll('.ag-reveal');
    if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('is-visible'); }); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); } });
    }, { threshold: .08, rootMargin: '0px 0px -4% 0px' });
    els.forEach(function (e) { io.observe(e); });
  })();
</script>
@endpush

@section('jsonld')
  {!! \App\Support\Seo::organization() !!}
  {!! \App\Support\Seo::faq($faqs) !!}
@endsection
