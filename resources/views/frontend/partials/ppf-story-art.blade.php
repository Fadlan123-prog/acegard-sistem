{{--
  Ilustrasi cerita PPF. Tiga keadaan lewat atribut data-state (1 cat asli, 2 lapisan film, 3 hasil akhir).
  Ilustrasi sengaja sederhana: satu lapisan film bening di atas cat, tanpa menggambar struktur internal material.
  Variabel: $state (keadaan awal), $uid (id unik untuk gradien), $tags (tampilkan label tahap)
--}}
@php $uid = $uid ?? 'ps'; @endphp
<svg class="ag-story-art" data-state="{{ $state ?? 1 }}" viewBox="0 0 400 320" role="img" aria-label="Ilustrasi cat mobil: cat asli, lapisan film PPF, dan hasil akhir">
  <defs>
    <linearGradient id="{{ $uid }}Paint" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#D5DAE1"/><stop offset=".5" stop-color="#A9B0BB"/><stop offset="1" stop-color="#7E8795"/></linearGradient>
    <linearGradient id="{{ $uid }}Gloss" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".45" stop-color="#fff" stop-opacity=".8"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>
    <linearGradient id="{{ $uid }}Film" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#EAF2FF" stop-opacity=".55"/><stop offset=".5" stop-color="#fff" stop-opacity=".18"/><stop offset="1" stop-color="#9CC0FF" stop-opacity=".4"/></linearGradient>
    <clipPath id="{{ $uid }}Clip"><rect width="400" height="320"/></clipPath>
  </defs>
  <g clip-path="url(#{{ $uid }}Clip)">
    <rect width="400" height="320" fill="url(#{{ $uid }}Paint)"/>
    <g class="gloss"><path d="M-40 260 L120 -20 L190 -20 L30 330 Z" fill="url(#{{ $uid }}Gloss)"/><path d="M180 340 L330 40 L360 40 L230 340 Z" fill="url(#{{ $uid }}Gloss)" opacity=".5"/></g>
    <path d="M0 214 Q200 186 400 214" fill="none" stroke="#6B7380" stroke-opacity=".35" stroke-width="2"/>
    {{-- goresan halus dan bekas kerikil pada cat asli --}}
    <g class="marks" fill="none" stroke="#F6F8FB" stroke-width="2" stroke-linecap="round">
      <path d="M52 90 L168 126 L286 104"/><path d="M70 232 L214 262 L352 236"/><path d="M118 40 L146 150 L128 280"/><path d="M262 34 L292 142 L330 270"/>
      <circle cx="318" cy="92" r="3.5" fill="#F6F8FB"/><circle cx="96" cy="184" r="3" fill="#F6F8FB"/><circle cx="224" cy="160" r="2.5" fill="#F6F8FB"/>
    </g>
    {{-- satu lapisan film bening di atas cat --}}
    <g class="film">
      <rect x="14" y="14" width="372" height="292" rx="16" fill="url(#{{ $uid }}Film)" stroke="#fff" stroke-opacity=".85" stroke-width="2"/>
      <path d="M14 70 L150 14 M14 150 L250 14 M14 230 L330 14" stroke="#fff" stroke-opacity=".35" stroke-width="1.5"/>
    </g>
    <g class="sweep"><path d="M130 -10 L190 -10 L110 330 L50 330 Z" fill="url(#{{ $uid }}Gloss)" opacity=".9"/></g>
  </g>
</svg>
