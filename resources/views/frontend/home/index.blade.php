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

{{-- ───────── About ACEGARD ───────── --}}
<section class="ag-section" id="tentang" aria-labelledby="ag-about-title">
  <div class="ag-container">
    <div class="ag-about">
      <div class="ag-reveal">
        <span class="ag-eyebrow">About ACEGARD</span>
        <h2 class="ag-h2" id="ag-about-title">Kaca film Nano Ceramic untuk kendaraan dan bangunan</h2>
      </div>
      <div class="ag-prose ag-reveal">
        <p>ACEGARD Indonesia menyediakan kaca film mobil, kaca film gedung dan rumah, serta Paint Protection Film (PPF). Produk kami diproduksi di pabrik bersertifikat internasional di Korea, melewati kontrol kualitas berlapis, lalu didistribusikan secara resmi di Indonesia.</p>
        <p>Kaca film ACEGARD memakai teknologi Nano Ceramic Matrix Composite. Lapisan dasarnya berupa dual-layer Nano Ceramic yang diproses dengan teknologi sputtering dan diperkuat unsur logam Tin, Titanium, dan Tungsten. Hasilnya tujuh lapisan yang stabil secara optik, tahan lama, dan tidak mengganggu sinyal seluler, WiFi, maupun GPS.</p>
        <p>Pemasangan dikerjakan oleh teknisi di workshop kami di Cipondoh, Kota Tangerang. Setiap pelanggan mendapat konsultasi sebelum pemasangan, garansi, dan layanan after sales.</p>
      </div>
    </div>

    <dl class="ag-stats ag-reveal">
      <div><dt>Tolak inframerah hingga</dt><dd>99,9<small>%</small></dd></div>
      <div><dt>TSER hingga</dt><dd>88<small>%</small></dd></div>
      <div><dt>Perlindungan UV</dt><dd>UV400</dd></div>
      <div><dt>Penurunan suhu interior</dt><dd>&plusmn;5<small>&deg;C</small></dd></div>
    </dl>
    <p class="ag-note ag-reveal">Angka bersumber dari spesifikasi produk ACEGARD. Hasil di lapangan dapat berbeda menurut tipe film, kendaraan atau bangunan, dan kondisi cuaca.</p>
  </div>
</section>

{{-- ───────── Our Services ───────── --}}
<section class="ag-section alt" id="layanan" aria-labelledby="ag-services-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <div>
        <span class="ag-eyebrow">Our Services</span>
        <h2 class="ag-h2" id="ag-services-title">Tiga layanan utama ACEGARD</h2>
      </div>
      <p class="ag-lead">Setiap layanan punya pilihan produk berbeda. Pilih sesuai kebutuhan Anda, lalu konsultasikan tipe film yang paling cocok dengan tim kami sebelum pemasangan.</p>
    </div>

    <ul class="ag-rows is-media">
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.service.car') }}">
          <span class="ag-row-num">01</span>
          <span class="ag-row-title">Automotive<br>Window Film</span>
          <span class="ag-row-desc">Kaca film untuk kaca depan, samping, belakang, dan sunroof. Menolak panas dan sinar UV, mengurangi silau, dan menjaga privasi kabin, dengan pilihan tingkat kegelapan (VLT) sesuai selera dan aturan berkendara.
            <ul><li>Tolak panas &amp; UV</li><li>Anti silau</li><li>Privasi kabin</li><li>Pilihan VLT</li></ul></span>
          <span class="ag-row-media"><img src="{{ asset('assets/frontend/ag/service-otomotif.webp') }}" width="1100" height="488" loading="lazy" alt="Kaca film mobil"></span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.service.building') }}">
          <span class="ag-row-num">02</span>
          <span class="ag-row-title">Building<br>Window Film</span>
          <span class="ag-row-desc">Kaca film untuk rumah, kantor, hotel, dan gedung komersial. Membantu menurunkan panas yang masuk lewat jendela, meringankan kerja AC, melindungi interior dari pudar, dan memberi privasi tanpa menutup pemandangan.
            <ul><li>Hemat energi AC</li><li>Interior awet</li><li>Privasi</li><li>Tampilan modern</li></ul></span>
          <span class="ag-row-media"><img src="{{ asset('assets/frontend/ag/service-bangunan.webp') }}" width="1100" height="488" loading="lazy" alt="Kaca film gedung dan bangunan"></span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
      <li class="ag-reveal">
        <a class="ag-row" href="{{ route('frontend.ppf.catalog') }}">
          <span class="ag-row-num">03</span>
          <span class="ag-row-title">Paint Protection<br>Film</span>
          <span class="ag-row-desc">Lapisan transparan yang melindungi cat bodi dari goresan halus, kotoran, dan paparan lingkungan sehari-hari. Tersedia pilihan tampilan Gloss dan Matte dalam beberapa seri, dipasang oleh teknisi ACEGARD.
            <ul><li>Perlindungan cat</li><li>Gloss &amp; Matte</li><li>Beberapa seri</li><li>Teknisi ACEGARD</li></ul></span>
          <span class="ag-row-media"><img src="{{ asset('assets/frontend/ag/service-ppf.webp') }}" width="1100" height="488" loading="lazy" alt="Paint Protection Film pada mobil"></span>
          <span class="ag-row-arrow" aria-hidden="true"><iconify-icon icon="mdi:arrow-right"></iconify-icon></span>
        </a>
      </li>
    </ul>
  </div>
</section>

{{-- ───────── Technology ───────── --}}
<section class="ag-section" id="teknologi" aria-labelledby="ag-tech-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <div>
        <span class="ag-eyebrow">Technology</span>
        <h2 class="ag-h2" id="ag-tech-title">Teknologi di balik kaca film ACEGARD</h2>
      </div>
      <p class="ag-lead">Tujuh karakter utama yang membedakan kaca film ACEGARD, dari penolakan panas sampai kestabilan warna.</p>
    </div>
    <ol class="ag-tech">
      @foreach ([
        ['High TSER Performance',            'Menolak energi surya total hingga 88%, sehingga kabin dan ruangan terasa lebih sejuk.'],
        ['Tungsten Thermal Layer',           'Lapisan Tungsten memperkuat penolakan panas inframerah hingga 99,9%.'],
        ['Night Vision Optical Clarity',     'VLT dijaga optimal agar detail jalan tetap terlihat di cahaya rendah, dengan glare lampu yang berkurang.'],
        ['UV400 Full Spectrum Protection',   'Melindungi kulit dan interior dari paparan sinar UV pada seluruh spektrum.'],
        ['Non-Metal Signal Friendly',        'Sinyal seluler, WiFi, dan GPS tetap lancar setelah kaca film terpasang.'],
        ['Color Fade Resistant',             'Warna tetap konsisten untuk pemakaian bertahun-tahun.'],
        ['Energy Efficient Color System',    'Membantu AC bekerja lebih ringan sehingga konsumsi energi harian dapat berkurang.'],
      ] as $i => [$t, $d])
        <li class="ag-tech-item ag-reveal">
          <span class="n">0{{ $i + 1 }}</span>
          <h3>{{ $t }}</h3>
          <p>{{ $d }}</p>
        </li>
      @endforeach
    </ol>
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
        ['mdi:diamond-stone',         'Premium Material',         'Diproduksi di pabrik bersertifikat internasional di Korea dengan kontrol kualitas berlapis, dan didistribusikan resmi di Indonesia.'],
        ['mdi:account-wrench',        'Professional Installation','Pemasangan dikerjakan teknisi ACEGARD di workshop kami, dengan prosedur yang sama untuk setiap pelanggan.'],
        ['mdi:shield-check-outline',  'Protection Technology',    'Nano Ceramic dengan lapisan Titanium dan Tungsten menolak panas inframerah dan sinar UV tanpa mengganggu sinyal.'],
        ['mdi:check-decagram-outline','Quality-Focused',          'Warna stabil, tahan pudar, dan kejernihan optik yang konsisten di setiap varian produk.'],
        ['mdi:handshake-outline',     'Customer-Oriented',        'Konsultasi sebelum pemasangan, garansi, dan layanan after sales yang jelas setelahnya.'],
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

{{-- ───────── Process ───────── --}}
<section class="ag-section" id="proses" aria-labelledby="ag-process-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <div>
        <span class="ag-eyebrow">How It Works</span>
        <h2 class="ag-h2" id="ag-process-title">Dari konsultasi sampai garansi</h2>
      </div>
      <p class="ag-lead">Empat langkah yang sama untuk kendaraan maupun bangunan.</p>
    </div>
    <ol class="ag-steps">
      @foreach ([
        ['Konsultasi',  'Ceritakan kendaraan atau bangunan Anda lewat WhatsApp. Tim kami membantu memilih tipe film sesuai kebutuhan dan anggaran.'],
        ['Pilih produk','Bandingkan pilihan film, tingkat kegelapan (VLT), dan tampilan, lalu tentukan jadwal pemasangan.'],
        ['Pemasangan',  'Teknisi ACEGARD memasang film di workshop kami di Cipondoh, Tangerang, dengan perhatian pada kerapian dan hasil akhir.'],
        ['Garansi & after sales','Setelah pemasangan, Anda mendapat garansi dan dukungan after sales. Cek status garansi kapan saja di halaman Cek Garansi.'],
      ] as $i => [$t, $d])
        <li class="ag-step ag-reveal">
          <span class="n">0{{ $i + 1 }}</span>
          <h3>{{ $t }}</h3>
          <p>{{ $d }}</p>
        </li>
      @endforeach
    </ol>
    <p class="ag-reveal" style="margin-top:32px"><a class="ag-link" href="{{ route('warranty.form') }}">Cek garansi Anda <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a></p>
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

{{-- ───────── Clients ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-clients-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <div>
        <span class="ag-eyebrow">Trusted By</span>
        <h2 class="ag-h2" id="ag-clients-title">Dipercaya instansi dan pelaku usaha</h2>
      </div>
      <p class="ag-lead">ACEGARD telah dipasang di kantor, hotel, dan fasilitas milik berbagai pelanggan korporasi dan instansi, selain kendaraan pribadi dari banyak merek.</p>
    </div>
    <ul class="ag-clients ag-reveal">
      @foreach (['BTN', 'Jasamarga', 'Kemenko Bidang Kemaritiman dan Investasi', 'Mercure Hotels', 'Cengkareng Heliport'] as $c)
        <li>{{ $c }}</li>
      @endforeach
    </ul>
  </div>
</section>

{{-- ───────── FAQ ───────── --}}
<section class="ag-section" id="faq" aria-labelledby="ag-faq-title">
  <div class="ag-container ag-faq-wrap">
    <div class="ag-reveal">
      <span class="ag-eyebrow">FAQ</span>
      <h2 class="ag-h2" id="ag-faq-title" style="margin-top:16px">Pertanyaan yang sering diajukan</h2>
      <p class="ag-lead" style="margin-top:20px">Belum menemukan jawabannya? <a class="ag-link" href="{{ route('frontend.faq') }}">Lihat FAQ lengkap</a> atau tanyakan langsung lewat WhatsApp.</p>
    </div>
    <div class="ag-faq ag-reveal">
      @foreach ([
        ['Apa bedanya kaca film ACEGARD dengan kaca film biasa?', 'ACEGARD memakai teknologi Nano Ceramic Matrix Composite dengan lapisan Titanium dan Tungsten. Teknologi ini menolak panas inframerah dan sinar UV tanpa lapisan logam yang mengganggu sinyal seluler, WiFi, dan GPS.'],
        ['Apakah kaca film ACEGARD mengganggu sinyal?', 'Tidak. ACEGARD memakai lapisan non-metal yang signal friendly, jadi sinyal seluler, WiFi, dan GPS tetap berfungsi normal.'],
        ['Apa itu VLT?', 'VLT (Visible Light Transmission) adalah persentase cahaya tampak yang menembus kaca film. Semakin rendah angkanya, semakin gelap filmnya. Pilihan VLT sebaiknya disesuaikan dengan kebutuhan privasi, kenyamanan, dan aturan berkendara.'],
        ['Apakah kaca film aman untuk berkendara malam hari?', 'Lapisan Nano Ceramic ACEGARD dirancang menjaga VLT tetap optimal dan mengurangi glare lampu kendaraan lain, sehingga detail jalan tetap terlihat. Tim kami dapat membantu memilih VLT yang sesuai untuk tiap kaca.'],
        ['Di mana pemasangan dilakukan dan bagaimana garansinya?', 'Pemasangan dilakukan oleh teknisi ACEGARD di workshop kami di Cipondoh, Kota Tangerang. Setiap pemasangan mendapat garansi, dan Anda bisa memeriksanya di halaman Cek Garansi.'],
        ['Berapa harga pemasangannya?', 'Harga bergantung pada jenis kendaraan atau luas bangunan dan tipe film. Hubungi kami lewat WhatsApp untuk mendapat penawaran yang sesuai.'],
      ] as $i => [$q, $a])
        <details class="ag-faq-item" @if($i === 0) open @endif>
          <summary>{{ $q }}</summary>
          <p>{{ $a }}</p>
        </details>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── Location ───────── --}}
<section class="ag-section alt" id="lokasi" aria-labelledby="ag-loc-title">
  <div class="ag-container ag-split">
    <div class="ag-reveal">
      <span class="ag-eyebrow">Visit Us</span>
      <h2 class="ag-h2" id="ag-loc-title" style="margin-top:16px">Kunjungi workshop kami</h2>
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
