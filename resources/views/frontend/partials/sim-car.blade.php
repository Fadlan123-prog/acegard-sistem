@php
  $glass = [
    'side'  => 'M262,327 L301,306 L335,295 L370,281 L405,274 L460,270 L490,271 L520,282 L550,302 L575,327 L588,350 L560,346 L520,343 L499,349 L496,362 L460,366 L405,364 L340,360 L285,356 L262,338 Z',
    'front' => 'M545,274 L590,275 L690,276 L740,288 L790,303 L840,323 L890,348 L925,371 L935,374 L865,372 L790,371 L690,369 L635,366 L607,340 L585,315 L565,290 Z',
  ];
@endphp
<div class="ag-sim-car">
  <div class="in">
  <img src="{{ $img('hero-car-clear.webp') }}" width="1334" height="889" alt="Mobil sedan perak untuk simulasi kaca film" loading="lazy">
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
