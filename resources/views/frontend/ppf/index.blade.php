@extends('frontend.layout.index')

@php
  // Pertanyaan dan jawaban: satu sumber di FaqCatalog (dipakai juga di halaman FAQ dan data terstruktur).
  $faqs = \App\Support\FaqCatalog::ppf();
@endphp

@php
  use App\Support\FilmCatalog;
  use App\Support\Whatsapp;
@endphp

@section('title', 'Paint Protection Film (PPF) - Acegard Indonesia')
@section('meta_description', 'PPF ACEGARD: ARMOR, ARMOR PLUS, AEGIS, AEGIS MAX + dan MATTE. Lihat spesifikasi, garansi 5 sampai 8 tahun, dan coba simulator goresan self healing.')
@section('og_image', asset('assets/frontend/ag/service-ppf.webp'))
@section('og_image_alt', 'Paint Protection Film ACEGARD')
@section('body-class', 'ag-page')

@section('content')

@php
  $img = fn ($f) => asset('assets/frontend/ag/' . $f);
  $tiers = FilmCatalog::ppf();
  $heal = ['instant' => 'Instan Self Healing', 'heat' => 'Heat Self Healing'];
  $start = 2; // AEGIS
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-ppf-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">PPF</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">Paint Protection Film</span>
    <h1 class="ag-display" id="ag-ppf-title">Lindungi cat mobil <b>dari goresan dan kerikil.</b></h1>
    <p class="ag-hero-sub">Lima tingkat PPF dengan urethane Lubrizol, garansi 5 sampai 8 tahun, dan teknologi self healing. Coba sendiri bagaimana film memulihkan goresan.</p>
    <div class="ag-hero-actions">
      <a class="ag-btn ag-btn-primary" href="#simulator">Coba Simulator Goresan</a>
      <a class="ag-btn ag-btn-secondary whatsapp-btn" data-gtm-label="ppf-hero-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi soal PPF.') }}">
        <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi Gratis
      </a>
    </div>
    <div class="ag-hero-car is-compact">
      <img src="{{ $img('hero-car-clear.webp') }}" width="1334" height="889" alt="Mobil sedan perak yang dilindungi PPF ACEGARD" fetchpriority="high">
    </div>
  </div>
</section>

{{-- ───────── Simulator goresan ───────── --}}
<section class="ag-section alt" id="simulator" data-sec="Simulator" aria-labelledby="ag-scr-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Scratch Simulator</span>
      <h2 class="ag-h2" id="ag-scr-title">Gores catnya, <b>lihat filmnya pulih</b></h2>
      <p class="ag-lead">Geser mouse atau jari di atas panel untuk menggores. Kiri adalah cat tanpa PPF, kanan adalah cat dengan PPF pilihan Anda.</p>
    </div>

    <div class="ag-win ag-ppf" id="agPpf" data-wa="{{ Whatsapp::url() }}">
      <div class="ag-win-view">
        <div class="ag-ppf-stage" id="agPpfStage">
          <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
            <defs>
              <linearGradient id="ppfPaint" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#D5DAE1"/><stop offset=".5" stop-color="#A9B0BB"/><stop offset="1" stop-color="#7E8795"/></linearGradient>
              <linearGradient id="ppfMatte" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#A4A9B1"/><stop offset="1" stop-color="#8A909A"/></linearGradient>
              <linearGradient id="ppfGloss" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".45" stop-color="#fff" stop-opacity=".75"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>
              <radialGradient id="ppfHeat" cx=".5" cy=".5" r=".6"><stop offset="0" stop-color="#FFB45A" stop-opacity=".9"/><stop offset="1" stop-color="#FF7A1A" stop-opacity="0"/></radialGradient>
            </defs>
          </svg>
          @foreach (['bare' => 'Tanpa PPF', 'film' => 'Dengan PPF'] as $key => $label)
            <figure class="ag-ppf-pane is-{{ $key }}" data-pane="{{ $key }}">
              <svg viewBox="0 0 400 320" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
                <rect class="paint" width="400" height="320" fill="url(#ppfPaint)"/>
                <path class="gloss" d="M-40 260 L120 -20 L190 -20 L30 330 Z" fill="url(#ppfGloss)"/>
                <path class="gloss" d="M180 340 L330 40 L360 40 L230 340 Z" fill="url(#ppfGloss)" opacity=".5"/>
                <path d="M0 214 Q200 186 400 214" fill="none" stroke="#6B7380" stroke-opacity=".35" stroke-width="2"/>
                <g class="strokes"></g>
                @if ($key === 'film') <rect class="heat" width="400" height="320" fill="url(#ppfHeat)"/> @endif
              </svg>
              <figcaption data-label="{{ $key }}">@if ($key === 'film')<span class="pre">{{ $label }}</span> <b data-tier-tag>{{ $tiers[$start]['name'] }}</b>@else{{ $label }}@endif</figcaption>
            </figure>
          @endforeach
        </div>
        <div class="ag-ppf-actions">
          <button type="button" class="ag-btn ag-btn-secondary ag-btn-sm" data-act="auto"><iconify-icon icon="mdi:draw" aria-hidden="true"></iconify-icon> Gores otomatis</button>
          <button type="button" class="ag-btn ag-btn-secondary ag-btn-sm" data-act="heat"><iconify-icon icon="mdi:white-balance-sunny" aria-hidden="true"></iconify-icon> Beri panas</button>
          <button type="button" class="ag-btn ag-btn-ghost-dark ag-btn-sm" data-act="reset"><iconify-icon icon="mdi:refresh" aria-hidden="true"></iconify-icon> Ulangi</button>
        </div>
        <p class="ag-win-legend" data-hint aria-live="polite"></p>
      </div>

      <div class="ag-sim-panel">
        <fieldset class="ag-sim-group">
          <legend>Pilih tingkat PPF</legend>
          <div class="ag-chips is-live">
            @foreach ($tiers as $i => $t)
              <label class="ag-chip {{ $i === $start ? 'is-on' : '' }}"><input type="radio" name="tier" value="{{ $i }}" class="visually-hidden" {{ $i === $start ? 'checked' : '' }}><span>{{ $t['name'] }}</span></label>
            @endforeach
          </div>
        </fieldset>

        <div class="ag-sim-info" aria-live="polite">
          <p class="ag-sim-info-title" data-i="title"></p>
          <dl class="ag-win-stats">
            <div><dt>Finish</dt><dd data-i="finish"></dd></div>
            <div><dt>Self healing</dt><dd data-i="heal"></dd></div>
            <div><dt>Garansi</dt><dd data-i="warranty"></dd></div>
            <div><dt>Urethane</dt><dd data-i="urethane"></dd></div>
            <div><dt>Ketebalan</dt><dd data-i="mils"></dd></div>
            <div><dt>Daya tahan</dt><dd data-i="durability"></dd></div>
          </dl>
          <p class="ag-sim-info-title" style="margin:14px 0 8px" data-i="best"></p>
          <div class="ag-ppf-rate" data-rates></div>
        </div>

        <a class="ag-btn ag-btn-primary whatsapp-btn" data-gtm-label="ppf-simulator-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin konsultasi PPF.') }}" data-ppf-wa>
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasikan tingkat ini
        </a>
      </div>
    </div>

    <p class="ag-note ag-center" style="margin-top:24px">Simulasi hanya ilustrasi untuk membandingkan perilaku self healing. Hasil nyata bergantung pada kedalaman goresan, suhu, dan kondisi film.</p>
  </div>
</section>

{{-- ───────── Cerita produk: tiga tahap (sticky di desktop, bertahap di mobile) ───────── --}}
@php
  $story = [
    ['Original <b>Paint</b>', 'Cat asli kendaraan menerima gesekan, debu, dan percikan kerikil setiap hari. Goresan halus dan bekas kerikil perlahan mengurangi kilap cat.'],
    ['Protection <b>Layer</b>', 'PPF dipasang sebagai satu lapisan film bening di atas cat. Teknisi ACEGARD memasangnya dengan teliti mengikuti bentuk bodi.'],
    ['Final <b>Result</b>', 'Cat tetap tampil seperti aslinya dan terlindungi oleh film. Goresan ringan dapat pulih sesuai jenis self healing pada tingkat PPF yang dipilih.'],
  ];
@endphp
<section class="ag-section" id="cerita" data-sec="Cerita" data-story aria-labelledby="ag-story-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">How It Works</span>
      <h2 class="ag-h2" id="ag-story-title">Dari cat asli <b>ke cat terlindungi</b></h2>
      <p class="ag-lead">Tiga tahap perlindungan cat dengan PPF. Gambar hanya ilustrasi sederhana, bukan struktur material.</p>
    </div>
    <div class="ag-story-grid">
      <div class="ag-story-stage" data-state="1">
        @include('frontend.partials.ppf-story-art', ['state' => 1, 'uid' => 'psS'])
        <div class="ag-story-tags" aria-hidden="true"><span class="t1">Cat asli</span><span class="t2">Lapisan film</span><span class="t3">Hasil akhir</span></div>
      </div>
      <ol class="ag-story-list">
        @foreach ($story as $i => [$title, $desc])
          <li class="ag-story-item">
            @include('frontend.partials.ppf-story-art', ['state' => $i + 1, 'uid' => 'psI' . $i])
            <span class="ag-story-no">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} / 03</span>
            <h3>{!! $title !!}</h3>
            <p>{{ $desc }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </div>
</section>

{{-- ───────── Manfaat ───────── --}}
<section class="ag-section alt" id="manfaat" data-sec="Manfaat" aria-labelledby="ag-pben-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Benefits</span>
      <h2 class="ag-h2" id="ag-pben-title">Manfaat PPF <b>untuk kendaraan Anda</b></h2>
    </div>
    <div class="ag-grid c3">
      @foreach ([
        ['mdi:shield-car', 'Lindungi dari kerikil dan goresan', 'Film urethane menjadi lapisan pertama yang menerima gesekan, debu, dan percikan kerikil di jalan.'],
        ['mdi:auto-fix', 'Self healing', 'Goresan ringan memulihkan diri. Tingkat AEGIS dan AEGIS MAX + memakai Instan Self Healing, tingkat lainnya Heat Self Healing.'],
        ['mdi:water-outline', 'Hydrophobic', 'Permukaan menolak air, debu, dan kotoran sehingga mobil lebih mudah dibersihkan.'],
        ['mdi:flask-outline', 'Urethane Lubrizol', 'Bahan dasar Lubrizol Mix Grade pada ARMOR dan ARMOR PLUS, serta Lubrizol Estane USA pada AEGIS dan AEGIS MAX +.'],
        ['mdi:shield-check-outline', 'Garansi 5 sampai 8 tahun', 'ARMOR dan MATTE 5 tahun, ARMOR PLUS 7 tahun, AEGIS dan AEGIS MAX + 8 tahun.'],
        ['mdi:car-cog', 'Pemasangan profesional', 'Dipasang teknisi ACEGARD di workshop Cipondoh, Tangerang.'],
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

{{-- ───────── Perbandingan ───────── --}}
@php
  $four = collect($tiers);
  $specRows = [
    ['Finish',            fn ($t) => $t['finish']],
    ['Tipe produk',       fn ($t) => 'TPU'],
    ['Warna',             fn ($t) => 'Transparan'],
    ['Ketebalan produk',  fn ($t) => $t['mils']],
    ['Urethane',          fn ($t) => $t['spec']['urethane_mils']],
    ['Urethane original', fn ($t) => $t['urethane']],
    ['Clear coating',     fn ($t) => $t['spec']['clear']],
    ['Acrylic adhesive',  fn ($t) => $t['spec']['adhesive']],
    ['Elongation',        fn ($t) => $t['spec']['elongation']],
    ['Peel adhesion',     fn ($t) => $t['spec']['peel']],
    ['Self healing',      fn ($t) => $heal[$t['heal']]],
    ['Tear strength',     fn ($t) => $t['spec']['tear']],
    ['Garansi',           fn ($t) => $t['warranty']],
    ['Dibuat di',         fn ($t) => $t['spec']['made']],
  ];
  $featRows = [
    ['Daya tahan', fn ($t) => $t['durability']],
    ['Hydrophobic', 'hydrophobic'], ['Perlindungan UV', 'uv'], ['Self healing', 'healing'],
    ['Tahan gores', 'scratch'], ['Fleksibilitas', 'flexibility'], ['Kejernihan', 'clarity'],
    ['Cocok untuk', fn ($t) => $t['best']],
  ];
@endphp
<section class="ag-section" id="tingkat" data-sec="Spesifikasi" aria-labelledby="ag-ptier-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Specification</span>
      <h2 class="ag-h2" id="ag-ptier-title">Spesifikasi <b>tingkat PPF</b></h2>
      <p class="ag-lead">Harga bergantung pada tipe kendaraan dan cakupan pemasangan. Hubungi tim kami untuk penawaran.<span class="ag-hint"> Geser tabel ke samping untuk melihat semua kolom.</span></p>
    </div>
    <div class="ag-spec-wrap ag-reveal" tabindex="0" role="region" aria-label="Spesifikasi tingkat PPF">
      <table class="ag-spec">
        <caption class="visually-hidden">Spesifikasi lima tingkat PPF ACEGARD</caption>
        <thead><tr><th scope="col">Spesifikasi</th>@foreach ($four as $t)<th scope="col">{{ $t['name'] }}</th>@endforeach</tr></thead>
        <tbody>
          @foreach ($specRows as [$label, $fn])
            <tr><th scope="row">{{ $label }}</th>@foreach ($four as $t)<td>{{ $fn($t) }}</td>@endforeach</tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="ag-section alt" aria-labelledby="ag-pfeat-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Features &amp; Structures</span>
      <h2 class="ag-h2" id="ag-pfeat-title">Fitur dan <b>kualitas perlindungan</b></h2>
      <p class="ag-lead">Penilaian bintang dari 1 sampai 5 menurut lembar fitur ACEGARD.</p>
    </div>
    <div class="ag-spec-wrap ag-reveal" tabindex="0" role="region" aria-label="Fitur tingkat PPF">
      <table class="ag-spec">
        <caption class="visually-hidden">Fitur lima tingkat PPF ACEGARD</caption>
        <thead><tr><th scope="col">Fitur</th>@foreach ($four as $t)<th scope="col">{{ $t['name'] }}</th>@endforeach</tr></thead>
        <tbody>
          @foreach ($featRows as [$label, $key])
            <tr><th scope="row">{{ $label }}</th>
              @foreach ($four as $t)
                @if (is_string($key))
                  @php $n = $t['rate'][$key]; @endphp
                  <td><span class="ag-stars" role="img" aria-label="{{ $n }} dari 5"><span class="on">{{ str_repeat('★', $n) }}</span><span class="off">{{ str_repeat('★', 5 - $n) }}</span></span></td>
                @else
                  <td>{{ $key($t) }}</td>
                @endif
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <p class="ag-note ag-center ag-reveal">MATTE memiliki spesifikasi yang sama dengan ARMOR. Perbedaannya hanya pada tampilan akhir yang doff.</p>
  </div>
</section>

{{-- ───────── Bandingkan tingkat (interaktif) ───────── --}}
@include('frontend.partials.compare', [
  'cmp' => \App\Support\FilmCatalog::comparePpf(), 'cmpKey' => 'ppf', 'cmpNoun' => 'tingkat', 'cmpAlt' => false,
  'cmpTitle' => 'Bandingkan <b>tingkat PPF</b>',
  'cmpWa' => 'Halo ACEGARD, saya sedang membandingkan tingkat PPF.',
])

{{-- ───────── FAQ ───────── --}}
<section class="ag-section alt" id="faq" data-sec="FAQ" aria-labelledby="ag-pfaq-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">FAQ</span>
      <h2 class="ag-h2" id="ag-pfaq-title">Pertanyaan seputar <b>PPF</b></h2>
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

{{-- ───────── CTA ───────── --}}
<section class="ag-section" style="padding-top:0" aria-labelledby="ag-pcta-title">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal">
      <h2 id="ag-pcta-title">Siap melindungi <b>cat mobil Anda?</b></h2>
      <p>Sebutkan tipe mobil dan cakupan yang Anda inginkan. Tim ACEGARD akan merekomendasikan tingkat PPF yang sesuai.</p>
      <div class="ag-cta-actions">
        <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="ppf-cta-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin konsultasi PPF.') }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi via WhatsApp
        </a>
        <a class="ag-btn ag-btn-ghost" href="{{ route('warranty.form') }}">Cek Garansi</a>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  var root = document.getElementById('agPpf');
  if (!root) return;
  var TIERS = @json($tiers);
  var HEAL = { instant: 'Instan Self Healing', heat: 'Heat Self Healing' };
  var cur = TIERS[{{ $start }}];
  var stage = document.getElementById('agPpfStage');
  var bare = stage.querySelector('[data-pane="bare"]'), film = stage.querySelector('[data-pane="film"]');
  var bareG = bare.querySelector('.strokes'), filmG = film.querySelector('.strokes');
  var q = function (s) { return root.querySelector(s); };
  var NS = 'http://www.w3.org/2000/svg';
  var strokes = []; // { filmPath }

  function hint() {
    q('[data-hint]').textContent = cur.heal === 'instant'
      ? 'Instan Self Healing: goresan ringan di sisi kanan pulih sendiri tanpa panas.'
      : 'Heat Self Healing: goresan di sisi kanan pulih setelah film diberi panas. Tekan "Beri panas".';
    q('[data-act="heat"]').hidden = cur.heal === 'instant';
  }

  function render(ann) {
    q('[data-i="title"]').textContent = cur.name + ' ' + (cur.finish === 'Matte' ? '(doff)' : '(glossy)');
    q('[data-i="finish"]').textContent = cur.finish;
    q('[data-i="heal"]').textContent = HEAL[cur.heal];
    q('[data-i="warranty"]').textContent = cur.warranty;
    q('[data-i="urethane"]').textContent = cur.urethane;
    q('[data-i="mils"]').textContent = cur.mils;
    q('[data-i="best"]').textContent = 'Cocok untuk: ' + cur.best.toLowerCase();
    q('[data-i="durability"]').textContent = cur.durability || 'Hubungi kami';
    var L = { hydrophobic: 'Hydrophobic', uv: 'Perlindungan UV', healing: 'Self healing', scratch: 'Tahan gores', flexibility: 'Fleksibilitas', clarity: 'Kejernihan' };
    var box = q('[data-rates]');
    if (cur.rate) {
      box.innerHTML = Object.keys(L).map(function (k) {
        var n = cur.rate[k], dots = '';
        for (var i = 1; i <= 5; i++) dots += '<i' + (i <= n ? ' class="on"' : '') + '></i>';
        return '<div><span>' + L[k] + '</span><span class="dots" role="img" aria-label="' + n + ' dari 5">' + dots + '</span></div>';
      }).join('');
    } else {
      box.innerHTML = '<p class="ag-note" style="margin:0">Spesifikasi lengkap ' + cur.name + ' dapat ditanyakan ke tim kami.</p>';
    }
    q('[data-tier-tag]').textContent = cur.name;
    stage.classList.toggle('is-matte', cur.finish === 'Matte');
    [bare, film].forEach(function (p) { p.querySelector('.paint').setAttribute('fill', cur.finish === 'Matte' ? 'url(#ppfMatte)' : 'url(#ppfPaint)'); });
    q('[data-ppf-wa]').href = root.getAttribute('data-wa') + '?text=' + encodeURIComponent('Halo ACEGARD, saya mencoba simulator PPF dan tertarik dengan ' + cur.name + ' (' + HEAL[cur.heal] + ', garansi ' + cur.warranty + '). Mohon penawarannya.');
    hint();
    if (ann) window.dispatchEvent(new CustomEvent('ag:selection', { detail: cur.name + ' (' + HEAL[cur.heal] + ', garansi ' + cur.warranty + ')' }));
  }

  function pathD(pts) { return pts.map(function (p, i) { return (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join(' '); }

  function addStroke(pts) {
    var d = pathD(pts), mk = function (g) {
      var s = document.createElementNS(NS, 'path');
      s.setAttribute('d', d); s.setAttribute('class', 'sc'); g.appendChild(s); return s;
    };
    mk(bareG);
    var fp = mk(filmG);
    var item = { el: fp, healed: false };
    strokes.push(item);
    if (cur.heal === 'instant') setTimeout(function () { heal(item); }, 700);
    return item;
  }
  function heal(item) { if (!item.healed) { item.healed = true; item.el.classList.add('is-healed'); } }

  function point(e, pane) {
    var r = pane.getBoundingClientRect();
    return [(e.clientX - r.left) / r.width * 400, (e.clientY - r.top) / r.height * 320];
  }
  var drawing = null;
  stage.addEventListener('pointerdown', function (e) {
    var pane = e.target.closest('.ag-ppf-pane'); if (!pane) return;
    stage.setPointerCapture(e.pointerId);
    drawing = { pane: pane, pts: [point(e, pane)] };
  });
  stage.addEventListener('pointermove', function (e) {
    if (!drawing) return;
    drawing.pts.push(point(e, drawing.pane));
    if (drawing.live) { var d = pathD(drawing.pts); drawing.live[0].setAttribute('d', d); drawing.live[1].setAttribute('d', d); }
    else {
      var a = document.createElementNS(NS, 'path'), b = document.createElementNS(NS, 'path');
      a.setAttribute('class', 'sc'); b.setAttribute('class', 'sc');
      bareG.appendChild(a); filmG.appendChild(b); drawing.live = [a, b];
    }
  });
  function end() {
    if (!drawing) return;
    if (drawing.live) {
      var item = { el: drawing.live[1], healed: false };
      strokes.push(item);
      if (cur.heal === 'instant') setTimeout(function () { heal(item); }, 700);
    }
    drawing = null;
  }
  ['pointerup', 'pointercancel'].forEach(function (n) { stage.addEventListener(n, end); });

  q('[data-act="auto"]').addEventListener('click', function () {
    [[[40, 70], [180, 120], [330, 96]], [[60, 200], [210, 238], [350, 214]], [[120, 40], [150, 160], [130, 290]], [[250, 30], [290, 150], [330, 280]]].forEach(function (pts, i) {
      setTimeout(function () { addStroke(pts); }, i * 180);
    });
  });
  q('[data-act="heat"]').addEventListener('click', function () {
    film.classList.add('is-warm');
    setTimeout(function () { strokes.forEach(heal); }, 700);
    setTimeout(function () { film.classList.remove('is-warm'); }, 1800);
  });
  q('[data-act="reset"]').addEventListener('click', function () {
    bareG.innerHTML = ''; filmG.innerHTML = ''; strokes = []; film.classList.remove('is-warm');
  });

  root.addEventListener('change', function (e) {
    if (e.target.name !== 'tier') return;
    cur = TIERS[Number(e.target.value)];
    root.querySelectorAll('.ag-chip').forEach(function (c) { c.classList.toggle('is-on', c.querySelector('input').checked); });
    bareG.innerHTML = ''; filmG.innerHTML = ''; strokes = [];
    render(true);
  });

  render();
})();
</script>
@endpush

@endsection

@section('jsonld')
  {!! \App\Support\Seo::breadcrumbs([['Home', route('frontend.home')], ['PPF', route('frontend.ppf.catalog')]]) !!}
  {!! \App\Support\Seo::faq($faqs) !!}
@endsection
