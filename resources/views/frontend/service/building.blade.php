@extends('frontend.layout.index')

@php
  use App\Support\FilmCatalog;
  use App\Support\Whatsapp;
@endphp

@section('title', 'Kaca Film Gedung dan Rumah - Acegard Indonesia')
@section('meta_description', 'Kaca film arsitektural ACEGARD untuk gedung, kantor, dan rumah: FLEX ONE WAY, BLACK MASTER, NOTCH UV400, 4K ALPHA PRO. Lihat spesifikasi 19 varian dan coba simulator jendela.')
@section('body-class', 'ag-page')

@section('content')

@php
  $img = fn ($f) => asset('assets/frontend/ag/' . $f);
  $series = FilmCatalog::architectural();

  // Data simulator: satu daftar datar, tiap varian membawa indeks serinya.
  $sim = [];
  foreach ($series as $si => $s) {
    foreach ($s['variants'] as $vi => $x) {
      $sim[] = [
        'id' => $si . '-' . $vi, 'series' => $si, 'seriesName' => $s['name'], 'warranty' => $s['warranty'],
        'label' => $x['label'] . (str_starts_with($s['name'], 'FLEX') ? ' ' . $x['dark'] : ''),
        'dark' => $x['dark'], 'tser' => (int) $x['tser'], 'irr' => $x['irr'], 'uvr' => $x['uvr'],
        'vlt' => (int) $x['vlt'], 'shgc' => $x['shgc'], 'tone' => $x['tone'], 'sheen' => $x['sheen'],
      ];
    }
  }
  $start = collect($sim)->firstWhere('label', 'AP35');
  $alpha = fn ($vlt) => number_format(min(.95, 1 - $vlt / 100), 2, '.', '');
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-bld-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Building Film</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">Architectural Window Film</span>
    <h1 class="ag-display" id="ag-bld-title">Kaca film gedung <b>sejuk, hemat, privasi terjaga.</b></h1>
    <p class="ag-hero-sub">Empat seri dengan 19 varian untuk rumah, kantor, dan gedung. Lihat spesifikasinya, lalu coba bagaimana jendela Anda tampil dengan tiap film.</p>
    <div class="ag-hero-actions">
      <a class="ag-btn ag-btn-primary" href="#simulator">Coba Simulator Jendela</a>
      <a class="ag-btn ag-btn-secondary whatsapp-btn" data-gtm-label="building-hero-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi soal kaca film gedung.') }}">
        <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi Gratis
      </a>
    </div>
  </div>
</section>

{{-- ───────── Simulator jendela ───────── --}}
<section class="ag-section" id="simulator" style="padding-top:16px" aria-labelledby="ag-win-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Window Simulator</span>
      <h2 class="ag-h2" id="ag-win-title">Lihat jendela Anda <b>sebelum dan sesudah dipasang film</b></h2>
      <p class="ag-lead">Pilih seri dan varian, lalu geser pembatas untuk membandingkan. Kegelapan, warna, dan panas yang masuk mengikuti angka spesifikasi tiap film.</p>
    </div>

    <div class="ag-win" id="agWin" data-wa="{{ Whatsapp::url() }}"
         style="--split: 50%; --film: {{ $start['tone'] }}; --film-a: {{ $alpha($start['vlt']) }}; --tser: {{ $start['tser'] / 100 }};">

      <div class="ag-win-view">
        <div class="ag-win-stage" id="agWinStage">
          {{-- 1. pemandangan di luar jendela --}}
          <svg class="ag-win-scene" viewBox="0 0 1000 625" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
            <defs>
              <linearGradient id="agSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5DA7F5"/><stop offset=".7" stop-color="#BFDDFF"/><stop offset="1" stop-color="#E6F1FF"/></linearGradient>
              <radialGradient id="agSun" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#fff" stop-opacity=".95"/><stop offset=".35" stop-color="#FFF3C4" stop-opacity=".55"/><stop offset="1" stop-color="#FFF3C4" stop-opacity="0"/></radialGradient>
              <linearGradient id="agGround" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7DBB78"/><stop offset="1" stop-color="#437F4E"/></linearGradient>
              <pattern id="agWinDots" width="14" height="16" patternUnits="userSpaceOnUse"><rect x="3" y="4" width="6" height="7" rx="1" fill="#CFE3FA" fill-opacity=".55"/></pattern>
            </defs>
            <rect width="1000" height="625" fill="url(#agSky)"/>
            <circle cx="770" cy="150" r="190" fill="url(#agSun)"/>
            <circle cx="770" cy="150" r="42" fill="#FFFBE8"/>
            <g fill="#fff" fill-opacity=".9">
              <ellipse cx="220" cy="130" rx="90" ry="22"/><ellipse cx="270" cy="115" rx="60" ry="22"/><ellipse cx="560" cy="200" rx="70" ry="16"/><ellipse cx="600" cy="188" rx="44" ry="16"/>
            </g>
            <g fill="#A9C2E2">
              @foreach ([[40,70,230],[110,60,300],[170,80,210],[250,56,330],[306,90,250],[396,64,360],[460,84,240],[544,70,310],[614,92,270],[706,66,350],[772,84,230],[856,70,300],[926,80,260]] as [$x,$w,$h])
                <rect x="{{ $x }}" y="{{ 500 - $h }}" width="{{ $w }}" height="{{ $h }}"/>
              @endforeach
            </g>
            <g fill="#6F8FBC">
              @foreach ([[60,90,170],[160,70,240],[240,110,150],[360,80,270],[450,100,190],[560,76,300],[646,104,170],[760,72,260],[840,100,200],[944,60,230]] as [$x,$w,$h])
                <rect x="{{ $x }}" y="{{ 500 - $h }}" width="{{ $w }}" height="{{ $h }}"/><rect x="{{ $x }}" y="{{ 500 - $h }}" width="{{ $w }}" height="{{ $h }}" fill="url(#agWinDots)"/>
              @endforeach
            </g>
            <rect x="0" y="470" width="1000" height="155" fill="url(#agGround)"/>
            <g fill="#3F7D48">
              @foreach ([60,150,255,360,470,590,690,800,900] as $i => $cx)
                <circle cx="{{ $cx }}" cy="{{ 486 + ($i % 3) * 6 }}" r="{{ 30 + ($i % 4) * 7 }}"/>
              @endforeach
            </g>
          </svg>

          {{-- 2. film pada kaca (hanya sisi kanan pembatas) --}}
          <div class="ag-win-film {{ $start['sheen'] ? 'is-sheen' : '' }}" aria-hidden="true"></div>

          {{-- 3. dinding dan bingkai jendela --}}
          <svg class="ag-win-frame" viewBox="0 0 1000 625" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
            <defs>
              <linearGradient id="agWall" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F4F7FB"/><stop offset="1" stop-color="#DCE4EF"/></linearGradient>
            </defs>
            <path fill="url(#agWall)" fill-rule="evenodd" d="M0 0H1000V625H0Z M78 64H486Q492 64 492 70V514H78Z M514 64H922Q928 64 928 70V514H514Z"/>
            <g fill="none" stroke="#C7D1DF" stroke-width="3"><rect x="78" y="64" width="414" height="450" rx="4"/><rect x="514" y="64" width="414" height="450" rx="4"/></g>
            <g fill="none" stroke="#fff" stroke-width="10" stroke-opacity=".9"><rect x="73" y="59" width="424" height="460" rx="8"/><rect x="509" y="59" width="424" height="460" rx="8"/></g>
            <rect x="44" y="514" width="912" height="26" rx="6" fill="#fff"/><rect x="44" y="536" width="912" height="8" rx="4" fill="#B9C5D6" fill-opacity=".6"/>
          </svg>

          {{-- 4. panas yang masuk: sebelum (kiri) dan sesudah (kanan) --}}
          <div class="ag-win-glow is-before" aria-hidden="true"></div>
          <div class="ag-win-glow is-after" aria-hidden="true"></div>

          <span class="ag-win-tag is-l">Tanpa film</span>
          <span class="ag-win-tag is-r" data-film-tag>{{ $start['seriesName'] }} {{ $start['label'] }}</span>
          <div class="ag-win-divider" aria-hidden="true"><span><iconify-icon icon="mdi:arrow-left-right" width="22"></iconify-icon></span></div>
        </div>
        <label class="ag-win-range">
          <span class="visually-hidden">Geser untuk membandingkan sebelum dan sesudah</span>
          <input type="range" id="agWinRange" min="0" max="100" value="50">
        </label>
        <p class="ag-win-legend"><i></i> Cahaya oranye di bawah jendela menggambarkan panas yang masuk. Makin tinggi TSER film, makin redup.</p>
      </div>

      <div class="ag-sim-panel">
        <fieldset class="ag-sim-group">
          <legend>1. Pilih seri</legend>
          <div class="ag-seg c2" role="radiogroup">
            @foreach ($series as $si => $s)
              <label class="ag-seg-opt {{ $start['series'] === $si ? 'is-on' : '' }}"><input type="radio" name="bseries" value="{{ $si }}" class="visually-hidden" {{ $start['series'] === $si ? 'checked' : '' }}><span>{{ $s['name'] }}</span></label>
            @endforeach
          </div>
        </fieldset>

        <fieldset class="ag-sim-group">
          <legend>2. Pilih varian</legend>
          <div class="ag-chips is-live" id="agWinChips">
            @foreach ($sim as $x)
              <label class="ag-chip {{ $x['id'] === $start['id'] ? 'is-on' : '' }}" data-series="{{ $x['series'] }}" @if ($x['series'] !== $start['series']) hidden @endif>
                <input type="radio" name="bvar" value="{{ $x['id'] }}" class="visually-hidden" {{ $x['id'] === $start['id'] ? 'checked' : '' }}><span>{{ $x['label'] }}</span>
              </label>
            @endforeach
          </div>
        </fieldset>

        <div class="ag-sim-info" aria-live="polite">
          <p class="ag-sim-info-title" data-i="title"></p>
          <dl class="ag-win-stats">
            <div><dt>VLT</dt><dd data-i="vlt"></dd></div>
            <div><dt>TSER</dt><dd data-i="tser"></dd></div>
            <div><dt>IRR</dt><dd data-i="irr"></dd></div>
            <div><dt>UVR</dt><dd data-i="uvr"></dd></div>
            <div><dt>SHGC</dt><dd data-i="shgc"></dd></div>
            <div><dt>Garansi</dt><dd data-i="warranty"></dd></div>
          </dl>
          <div class="ag-win-bars">
            <div><span>Panas matahari ditolak (TSER)</span><span class="ag-bar"><i data-i="tserbar"></i></span></div>
            <div><span>Cahaya tampak yang masuk (VLT)</span><span class="ag-bar"><i data-i="vltbar"></i></span></div>
          </div>
        </div>

        <a class="ag-btn ag-btn-primary whatsapp-btn" data-gtm-label="building-simulator-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya mencoba simulator jendela dan ingin berkonsultasi soal kaca film gedung.') }}" data-win-wa>
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Minta penawaran film ini
        </a>
      </div>
    </div>

    <p class="ag-note ag-center" style="margin-top:24px">Simulasi hanya ilustrasi visual di layar. Hasil nyata bergantung pada jenis kaca, arah matahari, dan pencahayaan. Angka spesifikasi berasal dari daftar produk ACEGARD.</p>
  </div>
</section>

{{-- ───────── Manfaat ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-bben-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Benefits</span>
      <h2 class="ag-h2" id="ag-bben-title">Manfaat kaca film <b>untuk gedung dan rumah</b></h2>
    </div>
    <div class="ag-grid c3">
      @foreach ([
        ['mdi:white-balance-sunny', 'Tolak panas', 'Penolakan inframerah (IRR) 99% pada seri Black Master, Notch UV400, dan 4K Alpha Pro. TSER mencapai 95% pada varian AP02.'],
        ['mdi:shield-sun-outline', 'Tolak sinar UV', 'UVR hingga 99,9% melindungi furnitur, lantai, dan kulit dari paparan UV yang membuat warna pudar.'],
        ['mdi:flash-outline', 'Hemat energi', 'SHGC rendah, sampai 0,000 pada varian tergelap, mengurangi panas yang masuk sehingga AC bekerja lebih ringan.'],
        ['mdi:eye-off-outline', 'Privasi ruangan', 'Pilih VLT rendah atau seri Flex One Way untuk menjaga isi ruangan dari pandangan luar.'],
        ['mdi:shield-check-outline', 'Garansi 3 sampai 7 tahun', 'Flex One Way 3 tahun, Black Master dan Notch UV400 5 tahun, 4K Alpha Pro 7 tahun.'],
        ['mdi:office-building-outline', 'Untuk berbagai bangunan', 'Dipasang teknisi ACEGARD untuk rumah, kantor, dan gedung komersial.'],
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
<section class="ag-section" id="seri" aria-labelledby="ag-bser-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Series &amp; Specification</span>
      <h2 class="ag-h2" id="ag-bser-title">Spesifikasi <b>19 varian kaca film gedung</b></h2>
      <p class="ag-lead">Darkness adalah tingkat kegelapan film, VLT adalah cahaya tampak yang menembus. Harga dihitung per meter persegi dan dapat dikonsultasikan dengan tim kami.<span class="ag-hint"> Geser tabel ke samping untuk melihat semua kolom.</span></p>
    </div>

    <div class="ag-series">
      @foreach ($series as $s)
        <article class="ag-prod ag-reveal" id="{{ \Illuminate\Support\Str::slug($s['name']) }}">
          <div class="ag-prod-info">
            <span class="ag-card-media ag-tones" role="img" aria-label="Warna varian {{ $s['name'] }}">@foreach ($s['variants'] as $x)<i style="background:{{ $x['tone'] }}"></i>@endforeach</span>
            <h3>{{ $s['name'] }}</h3>
            <p class="ag-prod-tag">{{ $s['tag'] }}</p>
            <p>{{ $s['desc'] }}</p>
            <p class="ag-warranty"><iconify-icon icon="mdi:shield-check-outline" aria-hidden="true"></iconify-icon> Garansi {{ $s['warranty'] }}</p>
            <a class="ag-btn ag-btn-primary ag-btn-sm whatsapp-btn" data-gtm-label="building-{{ \Illuminate\Support\Str::slug($s['name']) }}" target="_blank" rel="noopener"
               href="{{ Whatsapp::url('Halo ACEGARD, saya tertarik dengan kaca film gedung ' . $s['name']) }}">Tanya {{ $s['name'] }}</a>
          </div>
          <div class="ag-spec-wrap" tabindex="0" role="region" aria-label="Spesifikasi {{ $s['name'] }}">
            <table class="ag-spec ag-compare">
              <caption class="visually-hidden">Spesifikasi {{ $s['name'] }} per varian</caption>
              <thead>
                <tr><th scope="col">Varian</th><th scope="col">Darkness</th><th scope="col">VLT</th><th scope="col">TSER</th><th scope="col">IRR</th><th scope="col">UVR</th><th scope="col">SHGC</th></tr>
              </thead>
              <tbody>
                @foreach ($s['variants'] as $x)
                  <tr>
                    <th scope="row"><span class="ag-swatch" style="background:{{ $x['tone'] }}" aria-hidden="true"></span>{{ $x['label'] }}</th>
                    <td>{{ $x['dark'] }}</td><td>{{ $x['vlt'] }}</td><td>{{ $x['tser'] }}</td><td>{{ $x['irr'] }}</td><td>{{ $x['uvr'] }}</td><td>{{ $x['shgc'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── Bandingkan varian (interaktif) ───────── --}}
@include('frontend.partials.compare', [
  'cmp' => \App\Support\FilmCatalog::compareBuilding(), 'cmpKey' => 'building', 'cmpNoun' => 'varian', 'cmpAlt' => true,
  'cmpTitle' => 'Bandingkan <b>varian kaca film gedung</b>',
  'cmpWa' => 'Halo ACEGARD, saya sedang membandingkan kaca film gedung.',
])

{{-- ───────── Galeri ───────── --}}
<section class="ag-section" aria-labelledby="ag-bgal-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Gallery</span>
      <h2 class="ag-h2" id="ag-bgal-title">Kaca film <b>di gedung, kantor, dan rumah</b></h2>
    </div>
    <div class="ag-grid c3 ag-reveal">
      @foreach ([['gallery-gedung.webp', 'Gedung bertingkat dengan kaca film'], ['gallery-kantor.webp', 'Ruang kantor dengan jendela kaca besar'], ['gallery-rumah.webp', 'Ruang keluarga dengan jendela kaca besar']] as [$f, $alt])
        <figure class="ag-shot"><img src="{{ $img($f) }}" width="1000" height="700" loading="lazy" alt="{{ $alt }}"></figure>
      @endforeach
    </div>
  </div>
</section>

{{-- ───────── FAQ ───────── --}}
<section class="ag-section" aria-labelledby="ag-bfaq-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">FAQ</span>
      <h2 class="ag-h2" id="ag-bfaq-title">Pertanyaan seputar <b>kaca film gedung</b></h2>
    </div>
    <div class="ag-faq ag-reveal">
      @foreach ([
        ['Apa itu VLT, TSER, IRR, dan UVR?', 'VLT adalah persentase cahaya tampak yang menembus film, makin rendah makin gelap. TSER adalah total energi matahari yang ditolak. IRR adalah penolakan sinar inframerah, dan UVR adalah penolakan sinar ultraviolet. SHGC adalah koefisien panas matahari yang masuk, makin kecil makin sedikit panas masuk.'],
        ['Seri mana yang cocok untuk jendela saya?', 'Untuk pandangan tetap terang, pilih varian VLT tinggi seperti Notch UV400 Sky Blue (71%) atau Soft Green (68%). Untuk privasi dan penolakan panas lebih besar, pilih varian lebih gelap seperti Black Master atau 4K Alpha Pro. Tim kami membantu mencocokkan seri dengan kebutuhan Anda.'],
        ['Berapa lama garansinya?', 'Flex One Way 3 tahun, Black Master dan Notch UV400 5 tahun, 4K Alpha Pro 7 tahun, sesuai daftar produk ACEGARD.'],
        ['Bagaimana cara menghitung harga?', 'Harga dihitung per meter persegi dan berbeda untuk tiap seri. Kirim luas atau jumlah kaca Anda lewat WhatsApp untuk mendapat penawaran.'],
        ['Apakah simulator menunjukkan hasil yang persis?', 'Simulator adalah ilustrasi untuk membandingkan tingkat kegelapan, warna, dan panas yang masuk. Hasil nyata bergantung pada jenis kaca, arah matahari, dan pencahayaan ruangan.'],
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
<section class="ag-section" style="padding-top:0" aria-labelledby="ag-bcta-title">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal">
      <h2 id="ag-bcta-title">Siap membuat ruangan <b>lebih nyaman?</b></h2>
      <p>Sebutkan jenis bangunan dan luas kaca Anda. Tim ACEGARD akan merekomendasikan seri dan varian yang sesuai.</p>
      <div class="ag-cta-actions">
        <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="building-cta-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin konsultasi kaca film gedung.') }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi via WhatsApp
        </a>
        <a class="ag-btn ag-btn-ghost" href="{{ route('frontend.service.car') }}">Lihat Kaca Film Mobil</a>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  var root = document.getElementById('agWin');
  if (!root) return;
  var DATA = @json($sim);
  var byId = {}; DATA.forEach(function (x) { byId[x.id] = x; });
  var cur = byId[@json($start['id'])];
  var stage = document.getElementById('agWinStage'), range = document.getElementById('agWinRange');
  var q = function (s) { return root.querySelector(s); };
  var setI = function (k, v) { q('[data-i="' + k + '"]').textContent = v; };

  function render() {
    var a = Math.min(0.95, 1 - cur.vlt / 100).toFixed(2);
    root.style.setProperty('--film', cur.tone);
    root.style.setProperty('--film-a', a);
    root.style.setProperty('--tser', cur.tser / 100);
    q('.ag-win-film').classList.toggle('is-sheen', !!cur.sheen);
    q('[data-film-tag]').textContent = cur.seriesName + ' ' + cur.label;
    setI('title', cur.seriesName + ' ' + cur.label + ' (darkness ' + cur.dark + ')');
    setI('vlt', cur.vlt + '%'); setI('tser', cur.tser + '%'); setI('irr', cur.irr); setI('uvr', cur.uvr);
    setI('shgc', cur.shgc); setI('warranty', cur.warranty);
    q('[data-i="tserbar"]').style.setProperty('--w', cur.tser + '%');
    q('[data-i="vltbar"]').style.setProperty('--w', cur.vlt + '%');
    var msg = 'Halo ACEGARD, saya mencoba simulator jendela dan tertarik dengan ' + cur.seriesName + ' ' + cur.label +
      ' (VLT ' + cur.vlt + '%, TSER ' + cur.tser + '%). Mohon penawaran untuk kaca film gedung saya.';
    q('[data-win-wa]').href = root.getAttribute('data-wa') + '?text=' + encodeURIComponent(msg);
    root.querySelectorAll('.ag-chip').forEach(function (c) { c.classList.toggle('is-on', c.querySelector('input').value === cur.id); });
    root.querySelectorAll('.ag-seg-opt').forEach(function (c) { c.classList.toggle('is-on', c.querySelector('input').checked); });
  }

  root.addEventListener('change', function (e) {
    var t = e.target;
    if (t.name === 'bseries') {
      var s = Number(t.value), first = null;
      root.querySelectorAll('.ag-chip').forEach(function (c) {
        var show = Number(c.getAttribute('data-series')) === s;
        c.hidden = !show;
        if (show && !first) first = c.querySelector('input');
      });
      if (first) { first.checked = true; cur = byId[first.value]; }
    } else if (t.name === 'bvar') {
      cur = byId[t.value];
    }
    render();
  });

  // pembatas sebelum/sesudah: geser di gambar atau lewat slider
  function setSplit(p) {
    p = Math.max(0, Math.min(100, p));
    root.style.setProperty('--split', p + '%');
    range.value = p;
  }
  range.addEventListener('input', function () { setSplit(Number(range.value)); });
  function fromEvent(e) { var r = stage.getBoundingClientRect(); setSplit((e.clientX - r.left) / r.width * 100); }
  var dragging = false;
  stage.addEventListener('pointerdown', function (e) { dragging = true; stage.setPointerCapture(e.pointerId); fromEvent(e); });
  stage.addEventListener('pointermove', function (e) { if (dragging) fromEvent(e); });
  ['pointerup', 'pointercancel'].forEach(function (n) { stage.addEventListener(n, function () { dragging = false; }); });

  render();
})();
</script>
@endpush

@endsection
