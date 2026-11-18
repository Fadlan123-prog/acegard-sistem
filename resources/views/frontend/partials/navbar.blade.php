@php
  use App\Support\Whatsapp;
  $links = [
    ['label' => 'Home',            'href' => route('frontend.home'),             'active' => request()->routeIs('frontend.home')],
    ['label' => 'Automotive Film', 'href' => route('frontend.service.car'),      'active' => request()->routeIs('frontend.service.car')],
    ['label' => 'Building Film',   'href' => route('frontend.service.building'), 'active' => request()->routeIs('frontend.service.building')],
    ['label' => 'PPF',             'href' => route('frontend.ppf.catalog'),      'active' => request()->routeIs('frontend.ppf.catalog')],
    ['label' => 'About Us',        'href' => route('frontend.about'),            'active' => request()->routeIs('frontend.about')],
    ['label' => 'Contact',         'href' => route('frontend.contact'),          'active' => request()->routeIs('frontend.contact')],
  ];
@endphp
<header class="ag-nav">
  <div class="ag-container">
    <a class="ag-nav-brand" href="{{ route('frontend.home') }}" aria-label="Acegard — beranda">
      <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard" width="236" height="28">
    </a>

    <button class="ag-nav-toggle" type="button" id="agNavToggle" aria-expanded="false" aria-controls="agNavMenu" aria-label="Buka menu">
      <iconify-icon icon="mdi:menu" width="24"></iconify-icon>
    </button>

    <nav class="ag-nav-menu" id="agNavMenu" aria-label="Menu utama">
      @foreach ($links as $l)
        <a class="ag-nav-link {{ $l['active'] ? 'is-active' : '' }}" href="{{ $l['href'] }}" @if($l['active']) aria-current="page" @endif>{{ $l['label'] }}</a>
      @endforeach
      <div class="ag-nav-cta">
        <a class="ag-btn ag-btn-secondary ag-btn-sm" href="{{ route('warranty.form') }}">Cek Garansi</a>
        <a class="ag-btn ag-btn-primary ag-btn-sm whatsapp-btn" data-gtm-label="navbar-konsultasi" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}">Konsultasi</a>
      </div>
    </nav>
  </div>
</header>
<script>
  (function () {
    var t = document.getElementById('agNavToggle'), m = document.getElementById('agNavMenu');
    if (!t || !m) return;
    t.addEventListener('click', function () {
      var open = m.classList.toggle('is-open');
      t.setAttribute('aria-expanded', open ? 'true' : 'false');
      t.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && m.classList.contains('is-open')) { t.click(); t.focus(); }
    });
  })();
</script>
