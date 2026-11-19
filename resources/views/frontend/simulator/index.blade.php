@extends('frontend.layout.index')

@php
  use App\Support\FilmCatalog;
  use App\Support\Whatsapp;
@endphp

@section('title', 'Simulator Kaca Film Mobil - Acegard Indonesia')
@section('meta_description', 'Coba tingkat kegelapan kaca film mobil ACEGARD. Pilih VLT 71% sampai 4% untuk kaca depan dan kaca samping, lihat varian yang cocok, lalu konsultasikan lewat WhatsApp.')
@section('body-class', 'ag-page')

@section('content')

@php
  $img = fn ($f) => asset('assets/frontend/ag/' . $f);
  $swatch = FilmCatalog::swatch();

  // Varian dikelompokkan per angka VLT. Dua varian bisa berbagi VLT yang sama (mis. AP05 dan AP02).
  $byVlt = FilmCatalog::variants()
    ->groupBy(fn ($x) => (int) $x['vlt'])
    ->map(fn ($group) => $group->map(fn ($x) => [
        'code' => $x['code'], 'series' => $x['series'], 'color' => $x['color'], 'swatch' => $swatch[$x['color']] ?? '#222',
        'tser' => $x['tser'], 'uv' => $x['uv'], 'ir' => $x['ir14'], 'dark' => $x['dark'],
    ])->values())
    ->sortKeysDesc();

  $steps = $byVlt->keys()->all();      // 71, 68, 51, ... 4
  $start = 30;                          // pilihan awal
  $tint  = fn ($vlt) => number_format(min(.96, 1 - $vlt / 100), 2, '.', '');

  // Kontur kaca pada hero-car-clear.webp (1334 x 889)
  $glass = [
    'side'  => 'M262,327 L301,306 L335,295 L370,281 L405,274 L460,270 L490,271 L520,282 L550,302 L575,327 L588,350 L560,346 L520,343 L499,349 L496,362 L460,366 L405,364 L340,360 L285,356 L262,338 Z',
    'front' => 'M545,274 L590,275 L690,276 L740,288 L790,303 L840,323 L890,348 L925,371 L935,374 L865,372 L790,371 L690,369 L635,366 L607,340 L585,315 L565,290 Z',
  ];
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-sim-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Simulator VLT</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">Interactive Window Tint</span>
    <h1 class="ag-display" id="ag-sim-title">Coba kegelapan kaca film <b>sebelum memilih.</b></h1>
    <p class="ag-hero-sub">Pilih angka VLT untuk kaca depan dan kaca samping, lihat tampilannya pada mobil, lalu cocokkan dengan varian ACEGARD yang tersedia.</p>
  </div>
</section>

{{-- ───────── Simulator ───────── --}}
<section class="ag-section" style="padding-top:24px" aria-label="Simulator VLT">
  <div class="ag-container">
    <div class="ag-sim" id="agSim" data-wa="{{ Whatsapp::url() }}" style="--tint-front: {{ $tint($start) }}; --tint-side: {{ $tint($start) }};">

      <div class="ag-sim-view">
        <div class="ag-sim-car">
          <div class="in">
          <img src="{{ $img('hero-car-clear.webp') }}" width="1334" height="889" alt="Mobil sedan perak untuk simulasi kaca film" fetchpriority="high">
          <svg viewBox="0 0 1334 889" preserveAspectRatio="none" aria-hidden="true" focusable="false">
            <defs>
              <linearGradient id="agGlassBase" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#B9C7D6"/><stop offset=".55" stop-color="#7F90A4"/><stop offset="1" stop-color="#4E5C6E"/>
              </linearGradient>
              <linearGradient id="agGlassShine" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#fff" stop-opacity=".55"/><stop offset=".45" stop-color="#fff" stop-opacity=".08"/><stop offset="1" stop-color="#fff" stop-opacity="0"/>
              </linearGradient>
            </defs>
            @foreach ($glass as $key => $d)
              <g class="ag-glass is-{{ $key }}">
                <path d="{{ $d }}" fill="url(#agGlassBase)"/>
                @if ($key === 'side')
                  <path d="M335,295 L307,357 M445,271 L412,364" stroke="#2A323D" stroke-width="3" fill="none" stroke-linecap="round"/>
                @endif
                <path class="tint" d="{{ $d }}" fill="#05080D"/>
                <path d="{{ $d }}" fill="url(#agGlassShine)"/>
              </g>
            @endforeach
          </svg>
          </div>
        </div>
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
          <legend>2. Pilih VLT <small>(makin kecil makin gelap)</small></legend>
          <div class="ag-chips is-live">
            <label class="ag-chip"><input type="radio" name="vlt" value="100" class="visually-hidden"><span>Tanpa film</span></label>
            @foreach ($steps as $vlt)
              <label class="ag-chip"><input type="radio" name="vlt" value="{{ $vlt }}" class="visually-hidden" {{ $vlt === $start ? 'checked' : '' }}><span>{{ $vlt }}%</span></label>
            @endforeach
          </div>
        </fieldset>

        <div class="ag-sim-info" aria-live="polite">
          <p class="ag-sim-info-title" data-info-title></p>
          <div class="ag-sim-variants" data-info-list></div>
        </div>

        <a class="ag-btn ag-btn-primary whatsapp-btn" data-gtm-label="simulator-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya mencoba simulator VLT dan ingin berkonsultasi soal kaca film mobil.') }}" data-sim-wa>
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasikan pilihan ini
        </a>
      </div>
    </div>

    <p class="ag-note ag-center" style="margin-top:24px">Simulasi hanya ilustrasi visual di layar. Tampilan nyata bergantung pada kaca asli mobil, pencahayaan, dan layar Anda. Angka VLT, TSER, dan UV berasal dari lembar spesifikasi produk.</p>
  </div>
</section>

{{-- ───────── Panduan memilih ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-sim-guide">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Panduan</span>
      <h2 class="ag-h2" id="ag-sim-guide">Cara membaca <b>angka VLT</b></h2>
    </div>
    <div class="ag-grid c3">
      @foreach ([
        ['mdi:brightness-6', 'VLT menunjukkan cahaya yang lolos', 'VLT 70% berarti 70% cahaya tampak menembus film. VLT 5% berarti hanya 5% yang menembus, sehingga kabin terlihat sangat gelap dari luar.'],
        ['mdi:white-balance-sunny', 'Gelap tidak selalu lebih sejuk', 'Penolakan panas ditentukan teknologi filmnya. Varian terang seperti NV72 (VLT 71%) tetap menolak inframerah 99%. Lihat angka TSER di tiap varian.'],
        ['mdi:scale-balance', 'Perhatikan aturan setempat', 'Tingkat kegelapan kaca depan dan kaca samping bisa diatur regulasi lokal. Tim kami membantu memilih VLT yang aman dan nyaman untuk mata Anda.'],
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
      <h2>Bingung memilih VLT?</h2>
      <p>Ceritakan jenis mobil dan kebutuhan Anda. Tim ACEGARD akan merekomendasikan seri dan VLT untuk tiap kaca.</p>
      <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="simulator-cta-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin konsultasi memilih VLT kaca film mobil.') }}">
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
  var DATA = @json($byVlt);
  var state = { front: {{ $start }}, side: {{ $start }}, target: 'all' };

  var tint = function (v) { return Math.min(0.96, 1 - v / 100).toFixed(2); };
  var q = function (s) { return root.querySelector(s); };
  var label = function (v) { return v >= 100 ? 'Tanpa film' : v + '%'; };
  var codes = function (v) { return (DATA[v] || []).map(function (x) { return x.code; }).join(' / '); };
  var esc = function (t) { return String(t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };

  function render() {
    root.style.setProperty('--tint-front', tint(state.front));
    root.style.setProperty('--tint-side', tint(state.side));
    q('[data-out="front"]').textContent = label(state.front);
    q('[data-out="side"]').textContent = label(state.side);

    // varian untuk kaca yang sedang dipilih (untuk "semua kaca" dipakai kaca samping)
    var active = state.target === 'front' ? state.front : state.side;
    var title = q('[data-info-title]'), list = q('[data-info-list]');
    var where = state.target === 'front' ? 'kaca depan' : state.target === 'side' ? 'kaca samping' : 'semua kaca';
    if (active >= 100) {
      title.textContent = 'Tanpa film pada ' + where + '. Ini tampilan kaca standar pabrik.';
      list.innerHTML = '';
    } else {
      var items = DATA[active] || [];
      title.textContent = 'Varian dengan VLT ' + active + '% untuk ' + where + ':';
      list.innerHTML = items.map(function (x) {
        return '<div class="ag-sim-variant">' +
          '<div class="h"><span class="ag-swatch" style="background:' + x.swatch + '"></span><b>' + esc(x.code) + '</b><span>' + esc(x.series) + ' · ' + esc(x.color) + '</span></div>' +
          '<dl><div><dt>TSER</dt><dd>' + esc(x.tser) + '</dd></div><div><dt>IR 1400 nm</dt><dd>' + esc(x.ir) + '</dd></div>' +
          '<div><dt>UV</dt><dd>' + esc(x.uv) + '</dd></div><div><dt>Darkness</dt><dd>' + esc(x.dark) + '</dd></div></dl></div>';
      }).join('');
    }

    // pesan WhatsApp berisi pilihan terakhir
    var wa = q('[data-sim-wa]');
    var part = function (name, v) { return name + ': ' + (v >= 100 ? 'tanpa film' : 'VLT ' + v + '% (' + codes(v) + ')'); };
    var msg = 'Halo ACEGARD, saya mencoba simulator VLT. ' + part('Kaca depan', state.front) + '; ' + part('kaca samping', state.side) + '. Mohon rekomendasinya.';
    wa.href = root.getAttribute('data-wa') + '?text=' + encodeURIComponent(msg);
  }

  function syncChips() {
    var active = state.target === 'front' ? state.front : state.side;
    root.querySelectorAll('input[name="vlt"]').forEach(function (r) {
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
    } else if (t.name === 'vlt') {
      var v = Number(t.value);
      if (state.target === 'front') state.front = v;
      else if (state.target === 'side') state.side = v;
      else { state.front = v; state.side = v; }
    }
    syncChips(); render();
  });

  syncChips(); render();
})();
</script>
@endpush

@endsection
