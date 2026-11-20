@extends('frontend.layout.index')

@php
  use App\Support\FilmCatalog;
  use App\Support\Whatsapp;
@endphp

@section('title', 'Simulator Kaca Film Mobil - Acegard Indonesia')
@section('meta_description', 'Coba kegelapan kaca film mobil ACEGARD. Pilih 20% sampai 90% untuk kaca depan dan samping, lihat varian yang cocok, lalu konsultasi via WhatsApp.')
@section('og_image_alt', 'Simulator kaca film mobil ACEGARD')
@section('body-class', 'ag-page')

@section('content')

@php
  $img = fn ($f) => asset('assets/frontend/ag/' . $f);
  $swatch = FilmCatalog::swatch();

  // Varian dikelompokkan per persentase kegelapan (kolom "Darkness" lembar spesifikasi).
  // Beberapa varian berbagi kegelapan yang sama (mis. BM20, NV40, dan AP35 sama-sama 40%).
  $byDark = FilmCatalog::variants()
    ->groupBy(fn ($x) => (int) $x['dark'])
    ->map(fn ($group) => $group->map(fn ($x) => [
        'code' => $x['code'], 'series' => $x['series'], 'color' => $x['color'], 'swatch' => $swatch[$x['color']] ?? '#222',
        'tser' => $x['tser'], 'uv' => $x['uv'], 'vlt' => $x['vlt'], 'dark' => $x['dark'],
    ])->values())
    ->sortKeys();

  $steps = $byDark->keys()->all();     // 20, 30, 40, 60, 80, 90
  $start = 40;                          // pilihan awal
  $tint  = fn ($dark) => number_format(min(.96, $dark / 100), 2, '.', '');

@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-sim-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Simulator VLT</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">Interactive Window Tint</span>
    <h1 class="ag-display" id="ag-sim-title">Coba kegelapan kaca film <b>sebelum memilih.</b></h1>
    <p class="ag-hero-sub">Pilih persentase kegelapan untuk kaca depan dan kaca samping, lihat tampilannya pada mobil, lalu cocokkan dengan varian ACEGARD yang tersedia.</p>
  </div>
</section>

{{-- ───────── Simulator ───────── --}}
<section class="ag-section" style="padding-top:24px" aria-label="Simulator kegelapan">
  <div class="ag-container">
    <div class="ag-sim" id="agSim" data-wa="{{ Whatsapp::url() }}" style="--tint-front: {{ $tint($start) }}; --tint-side: {{ $tint($start) }}; --split: 50%;">

      <div class="ag-sim-view">
        @include('frontend.partials.sim-car', ['split' => true])
        <label class="ag-win-range" style="margin-top:14px">
          <span class="visually-hidden">Geser untuk membandingkan sebelum dan sesudah dipasang film</span>
          <input type="range" id="agSimRange" min="0" max="100" value="50">
        </label>
        <ul class="ag-sim-readout" aria-live="polite">
          <li><span>Kaca depan</span><b data-out="front">{{ $start }}%</b></li>
          <li><span>Kaca samping</span><b data-out="side">{{ $start }}%</b></li>
        </ul>
      </div>

      <div class="ag-sim-panel">
        <fieldset class="ag-sim-group">
          <legend>1. Kaca yang diubah</legend>
          <div class="ag-seg" role="radiogroup">
            @foreach (['all' => 'Semua kaca', 'front' => 'Kaca depan', 'side' => 'Kaca samping'] as $val => $label)
              <label class="ag-seg-opt"><input type="radio" name="target" value="{{ $val }}" class="visually-hidden" {{ $val === 'all' ? 'checked' : '' }}><span>{{ $label }}</span></label>
            @endforeach
          </div>
        </fieldset>

        <fieldset class="ag-sim-group">
          <legend>2. Pilih kegelapan <small>(makin besar makin gelap)</small></legend>
          <div class="ag-chips is-live">
            <label class="ag-chip"><input type="radio" name="dark" value="0" class="visually-hidden"><span>Tanpa film</span></label>
            @foreach ($steps as $dark)
              <label class="ag-chip"><input type="radio" name="dark" value="{{ $dark }}" class="visually-hidden" {{ $dark === $start ? 'checked' : '' }}><span>{{ $dark }}%</span></label>
            @endforeach
          </div>
        </fieldset>

        <div class="ag-sim-info" aria-live="polite">
          <p class="ag-sim-info-title" data-info-title></p>
          <div class="ag-sim-variants" data-info-list></div>
        </div>

        <a class="ag-btn ag-btn-primary whatsapp-btn" data-gtm-label="simulator-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya mencoba simulator kegelapan kaca film dan ingin berkonsultasi soal kaca film mobil.') }}" data-sim-wa>
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasikan pilihan ini
        </a>
      </div>
    </div>

    <p class="ag-note ag-center" style="margin-top:24px">Simulasi hanya ilustrasi visual di layar. Tampilan nyata bergantung pada kaca asli mobil, pencahayaan, dan layar Anda. Persentase kegelapan, VLT, TSER, dan UV berasal dari lembar spesifikasi produk.</p>
  </div>
</section>

{{-- ───────── Panduan memilih ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-sim-guide">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Panduan</span>
      <h2 class="ag-h2" id="ag-sim-guide">Cara membaca <b>persentase kegelapan</b></h2>
    </div>
    <div class="ag-grid c3">
      @foreach ([
        ['mdi:brightness-6', 'Kegelapan makin besar, kabin makin tertutup', 'Kegelapan 20% terlihat terang dan jernih. Kegelapan 90% sangat pekat, sehingga kabin sulit terlihat dari luar. Tiap varian punya persentase kegelapannya sendiri di lembar spesifikasi.'],
        ['mdi:white-balance-sunny', 'Gelap tidak selalu lebih sejuk', 'Penolakan panas ditentukan teknologi filmnya. Varian terang seperti NV70 (kegelapan 20%) tetap menolak inframerah 99%. Lihat angka TSER di tiap varian.'],
        ['mdi:scale-balance', 'Perhatikan aturan setempat', 'Tingkat kegelapan kaca depan dan kaca samping bisa diatur regulasi lokal. Tim kami membantu memilih kegelapan yang aman dan nyaman untuk mata Anda.'],
      ] as [$icon, $title, $desc])
        <div class="ag-card ag-feature ag-reveal">
          <span class="ic"><iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon></span>
          <h3>{{ $title }}</h3>
          <p>{{ $desc }}</p>
        </div>
      @endforeach
    </div>
    <p class="ag-center ag-reveal" style="margin-top:28px"><a class="ag-link" href="{{ route('frontend.service.car') }}#seri">Lihat spesifikasi lengkap 13 varian <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></a></p>
  </div>
</section>

{{-- ───────── CTA ───────── --}}
<section class="ag-section" aria-label="Konsultasi">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal">
      <h2>Bingung memilih kegelapan?</h2>
      <p>Ceritakan jenis mobil dan kebutuhan Anda. Tim ACEGARD akan merekomendasikan seri dan tingkat kegelapan untuk tiap kaca.</p>
      <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="simulator-cta-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin konsultasi memilih kegelapan kaca film mobil.') }}">
        <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi Gratis
      </a>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  var root = document.getElementById('agSim');
  if (!root) return;
  var DATA = @json($byDark);
  var state = { front: {{ $start }}, side: {{ $start }}, target: 'all' };

  var tint = function (v) { return Math.min(0.96, v / 100).toFixed(2); };
  var q = function (s) { return root.querySelector(s); };
  var label = function (v) { return v <= 0 ? 'Tanpa film' : v + '%'; };
  var codes = function (v) { return (DATA[v] || []).map(function (x) { return x.code; }).join(' / '); };
  var esc = function (t) { return String(t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };

  function render(ann) {
    root.style.setProperty('--tint-front', tint(state.front));
    root.style.setProperty('--tint-side', tint(state.side));
    q('[data-out="front"]').textContent = label(state.front);
    q('[data-out="side"]').textContent = label(state.side);
    var stag = document.querySelector('[data-split-tag]'); if (stag) stag.textContent = state.side <= 0 ? 'Tanpa film' : 'Film ' + state.side + '%';

    // varian untuk kaca yang sedang dipilih (untuk "semua kaca" dipakai kaca samping)
    var active = state.target === 'front' ? state.front : state.side;
    var title = q('[data-info-title]'), list = q('[data-info-list]');
    var where = state.target === 'front' ? 'kaca depan' : state.target === 'side' ? 'kaca samping' : 'semua kaca';
    if (active <= 0) {
      title.textContent = 'Tanpa film pada ' + where + '. Ini tampilan kaca standar pabrik.';
      list.innerHTML = '';
    } else {
      var items = DATA[active] || [];
      title.textContent = 'Varian dengan kegelapan ' + active + '% untuk ' + where + ':';
      list.innerHTML = items.map(function (x) {
        return '<div class="ag-sim-variant">' +
          '<div class="h"><span class="ag-swatch" style="background:' + x.swatch + '"></span><b>' + esc(x.code) + '</b><span>' + esc(x.series) + ' · ' + esc(x.color) + '</span></div>' +
          '<dl><div><dt>Kegelapan</dt><dd>' + esc(x.dark) + '</dd></div><div><dt>TSER</dt><dd>' + esc(x.tser) + '</dd></div>' +
          '<div><dt>UV</dt><dd>' + esc(x.uv) + '</dd></div><div><dt>VLT</dt><dd>' + esc(x.vlt) + '</dd></div></dl></div>';
      }).join('');
    }

    // pesan WhatsApp berisi pilihan terakhir
    var wa = q('[data-sim-wa]');
    var part = function (name, v) { return name + ': ' + (v <= 0 ? 'tanpa film' : 'kegelapan ' + v + '% (' + codes(v) + ')'); };
    var msg = 'Halo ACEGARD, saya mencoba simulator kegelapan kaca film. ' + part('Kaca depan', state.front) + '; ' + part('kaca samping', state.side) + '. Mohon rekomendasinya.';
    wa.href = root.getAttribute('data-wa') + '?text=' + encodeURIComponent(msg);
    if (ann) window.dispatchEvent(new CustomEvent('ag:selection', { detail: 'simulator kegelapan, ' + part('kaca depan', state.front) + '; ' + part('kaca samping', state.side) }));
  }

  function syncChips() {
    var active = state.target === 'front' ? state.front : state.side;
    root.querySelectorAll('input[name="dark"]').forEach(function (r) {
      r.checked = Number(r.value) === active;
      r.closest('.ag-chip').classList.toggle('is-on', r.checked);
    });
    root.querySelectorAll('input[name="target"]').forEach(function (r) {
      r.closest('.ag-seg-opt').classList.toggle('is-on', r.checked);
    });
  }

  root.addEventListener('change', function (e) {
    var t = e.target;
    if (t.name === 'target') {
      state.target = t.value;
      // "Semua kaca" menyamakan kaca depan dengan kaca samping
      if (state.target === 'all') state.front = state.side;
    } else if (t.name === 'dark') {
      var v = Number(t.value);
      if (state.target === 'front') state.front = v;
      else if (state.target === 'side') state.side = v;
      else { state.front = v; state.side = v; }
    }
    syncChips(); render(true);
  });

  // Pembatas sebelum/sesudah: geser di gambar atau lewat slider
  var car = document.getElementById('agSimCar'), range = document.getElementById('agSimRange');
  function setSplit(p) { p = Math.max(0, Math.min(100, p)); root.style.setProperty('--split', p + '%'); if (range) range.value = p; }
  if (range) range.addEventListener('input', function () { setSplit(Number(range.value)); });
  if (car) {
    var drag = false;
    var pos = function (e) { var r = car.getBoundingClientRect(); setSplit((e.clientX - r.left) / r.width * 100); };
    car.addEventListener('pointerdown', function (e) { drag = true; car.setPointerCapture(e.pointerId); pos(e); });
    car.addEventListener('pointermove', function (e) { if (drag) pos(e); });
    ['pointerup', 'pointercancel'].forEach(function (n) { car.addEventListener(n, function () { drag = false; }); });
  }

  syncChips(); render();
})();
</script>
@endpush

@endsection

@section('jsonld')
  {!! \App\Support\Seo::breadcrumbs([['Home', route('frontend.home')], ['Simulator VLT', route('frontend.simulator')]]) !!}
@endsection
