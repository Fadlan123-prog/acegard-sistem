@php
  $glass = [
    'side'  => 'M262,327 L301,306 L335,295 L370,281 L405,274 L460,270 L490,271 L520,282 L550,302 L575,327 L588,350 L560,346 L520,343 L499,349 L496,362 L460,366 L405,364 L340,360 L285,356 L262,338 Z',
    'front' => 'M545,274 L590,275 L690,276 L740,288 L790,303 L840,323 L890,348 L925,371 L935,374 L865,372 L790,371 L690,369 L635,366 L607,340 L585,315 L565,290 Z',
  ];
@endphp
<div class="ag-sim-car {{ ! empty($split) ? 'is-split' : '' }}" @if (! empty($split)) id="agSimCar" @endif>
  <div class="in">
  <img src="{{ $img('hero-car-clear.webp') }}" width="1334" height="889" alt="Mobil sedan perak untuk simulasi kaca film" loading="lazy">

  {{-- Lapisan 1: kaca dasar (selalu terlihat, sisi "tanpa film") --}}
  <svg class="ag-sim-layer" viewBox="0 0 1334 889" preserveAspectRatio="none" aria-hidden="true" focusable="false">
    <defs>
      <linearGradient id="agGlassBase" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0" stop-color="#B9C7D6"/><stop offset=".55" stop-color="#7F90A4"/><stop offset="1" stop-color="#4E5C6E"/>
      </linearGradient>
      <linearGradient id="agGlassShine" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#fff" stop-opacity=".55"/><stop offset=".45" stop-color="#fff" stop-opacity=".08"/><stop offset="1" stop-color="#fff" stop-opacity="0"/>
      </linearGradient>
    </defs>
    @foreach ($glass as $key => $d)
      <g>
        <path d="{{ $d }}" fill="url(#agGlassBase)"/>
        @if ($key === 'side')
          <path d="M335,295 L307,357 M445,271 L412,364" stroke="#2A323D" stroke-width="3" fill="none" stroke-linecap="round"/>
        @endif
      </g>
    @endforeach
  </svg>

  {{-- Lapisan 2: tint film, dipotong di titik pembatas pada mode sebelum/sesudah --}}
  <svg class="ag-sim-layer ag-sim-tint" viewBox="0 0 1334 889" preserveAspectRatio="none" aria-hidden="true" focusable="false">
    @foreach ($glass as $key => $d)
      <g class="ag-glass is-{{ $key }}"><path class="tint" d="{{ $d }}" fill="#05080D"/></g>
    @endforeach
  </svg>

  {{-- Lapisan 3: kilap kaca di atas semuanya --}}
  <svg class="ag-sim-layer" viewBox="0 0 1334 889" preserveAspectRatio="none" aria-hidden="true" focusable="false">
    @foreach ($glass as $key => $d)
      <path d="{{ $d }}" fill="url(#agGlassShine)"/>
    @endforeach
  </svg>
  </div>

  @if (! empty($split))
    <span class="ag-win-tag is-l">Tanpa film</span>
    <span class="ag-win-tag is-r" data-split-tag>Dengan film</span>
    <div class="ag-win-divider" aria-hidden="true"><span><iconify-icon icon="mdi:arrow-left-right" width="22"></iconify-icon></span></div>
  @endif
</div>
