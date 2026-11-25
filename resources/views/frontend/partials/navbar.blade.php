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

    <div class="ag-nav-menu" id="agNavMenu">
      <nav class="ag-nav-pill" aria-label="Menu utama">
        @foreach ($links as $l)
          <a class="ag-nav-link {{ $l['active'] ? 'is-active' : '' }}" href="{{ $l['href'] }}" @if($l['active']) aria-current="page" @endif>{{ $l['label'] }}</a>
        @endforeach
      </nav>
      <div class="ag-nav-cta">
        <a class="ag-btn ag-btn-secondary ag-btn-sm" href="{{ route('warranty.form') }}">Cek Garansi</a>
        <a class="ag-btn ag-btn-primary ag-btn-sm whatsapp-btn" data-gtm-label="navbar-konsultasi" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}">Konsultasi</a>
      </div>
    </div>
  </div>
</header>
<script>
  (function () {
    var t = document.getElementById('agNavToggle'), m = document.getElementById('agNavMenu');
    if (!t || !m) return;
    var icon = t.querySelector('iconify-icon');
    function set(open) {
      m.classList.toggle('is-open', open);
      t.setAttribute('aria-expanded', open ? 'true' : 'false');
      t.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
      if (icon) icon.setAttribute('icon', open ? 'mdi:close' : 'mdi:menu');
    }
    t.addEventListener('click', function () { set(!m.classList.contains('is-open')); });
    m.addEventListener('click', function (e) { if (e.target.closest('a')) set(false); });
    document.addEventListener('click', function (e) { if (m.classList.contains('is-open') && !m.contains(e.target) && !t.contains(e.target)) set(false); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && m.classList.contains('is-open')) { set(false); t.focus(); }
    });
  })();
</script>
<script>
  (function () {
    var nav = document.querySelector('.ag-nav');
    if (!nav) return;
    function size() {
      var h = nav.offsetHeight + (nav.classList.contains('is-scrolled') ? 12 : 0);
      document.documentElement.style.setProperty('--ag-nav-h', h + 'px');
    }
    function scrolled() { nav.classList.toggle('is-scrolled', (window.pageYOffset || 0) > 24); }
    size(); scrolled();
    window.addEventListener('resize', size);
    window.addEventListener('scroll', scrolled, { passive: true });
  })();
</script>
