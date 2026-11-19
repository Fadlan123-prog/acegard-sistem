<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', $title ?? 'Acegard Indonesia')</title>
  @hasSection('meta_description')
  <meta name="description" content="@yield('meta_description')">
  <link rel="canonical" href="{{ url()->current() }}">
  <meta property="og:site_name" content="ACEGARD Indonesia">
  <meta property="og:locale" content="id_ID">
  <meta property="og:title" content="@yield('title', $title ?? 'Acegard Indonesia')">
  <meta property="og:description" content="@yield('meta_description')">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="@yield('og_image', asset('assets/frontend/ag/hero-car.webp'))">
  <meta property="og:image:alt" content="@yield('og_image_alt', 'ACEGARD kaca film dan Paint Protection Film')">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('title', $title ?? 'Acegard Indonesia')">
  <meta name="twitter:description" content="@yield('meta_description')">
  <meta name="twitter:image" content="@yield('og_image', asset('assets/frontend/ag/hero-car.webp'))">
  @endif
  @yield('jsonld')
  <script>document.documentElement.classList.add('js');</script>
  @if (! View::hasSection('hide-navbar'))
  {{-- Penjaga kilatan animasi: judul disembunyikan sebentar sampai ag-motion.js siap (dilepas otomatis maksimal 2,5 detik) --}}
  <script>(function(){var r=document.documentElement,off=false;try{off=localStorage.getItem('agMotion')==='off';}catch(e){}var rm=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;if(!off&&!rm){r.classList.add('ag-m-wait');}})();</script>
  @endif

  <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

  {{-- Google Tag Manager --}}
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-5DH3T7FD');</script>

  {{-- Koneksi awal ke CDN agar CSS, font, dan ikon dimuat lebih cepat --}}
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://api.iconify.design" crossorigin>

  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- Iconify (mdi:* icons) --}}
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js" async></script>

  {{-- Inter --}}
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/frontend/home/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/frontend/css/tokens.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/frontend/css/ag-design.css') }}?v={{ @filemtime(public_path('assets/frontend/css/ag-design.css')) }}">
  @if (! View::hasSection('hide-navbar'))
  <link rel="stylesheet" href="{{ asset('assets/frontend/css/ag-motion.css') }}?v={{ @filemtime(public_path('assets/frontend/css/ag-motion.css')) }}">
  @endif
  @stack('styles')
</head>
<body class="@yield('body-class')">
  {{-- Google Tag Manager (noscript) --}}
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DH3T7FD"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

  {{-- Navbar --}}
  {{-- Landing page ads dapat menyembunyikan navbar via @section('hide-navbar', '1') --}}
  @if (! View::hasSection('hide-navbar'))
    @include('frontend.partials.navbar')
  @endif

  {{-- Content --}}
  <main class="flex-shrink-0">
    @yield('content')
  </main>

  {{-- Footer --}}
  {{-- Landing page ads dapat memakai footer khusus via @section('hide-footer', '1') + markup sendiri di @section('content') --}}
  @if (! View::hasSection('hide-footer'))
    @include('frontend.partials.footer')
  @endif

  @if (! View::hasSection('hide-navbar'))
    @include('frontend.partials.wa-float')
  @endif

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')

  {{-- Tampilkan bagian .ag-reveal saat masuk layar. Berlaku untuk semua halaman; pengaman memastikan konten tidak pernah tersembunyi. --}}
  <script>
  (function () {
    var els = document.querySelectorAll('.ag-reveal');
    if (!els.length) return;
    var show = function (e) { e.classList.add('is-visible'); };
    if (!('IntersectionObserver' in window)) { els.forEach(show); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { show(en.target); io.unobserve(en.target); } });
    }, { threshold: 0.01, rootMargin: '0px 0px -4% 0px' });
    els.forEach(function (e) { io.observe(e); });
    // Pengaman: jika pengamat gagal, semua konten tetap tampil setelah 3 detik.
    setTimeout(function () { els.forEach(show); }, 3000);
  })();
  </script>

  @if (! View::hasSection('hide-navbar'))
  {{-- Animasi scroll: GSAP + ScrollTrigger (cdnjs, dengan SRI) lalu ag-motion.js. Semua konten tetap tampil bila gagal dimuat. --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" integrity="sha384-g4NTh/Iv5PPU4xPyhEWqPcwtNXOvdaDI8LLnyYfyNZOjKJeYQyjzQ9X5275eBjpt" crossorigin="anonymous" referrerpolicy="no-referrer" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js" integrity="sha384-Z3REaz79l2IaAZqJsSABtTbhjgOUYyV3p90XNnAPCSHg3EMTz1fouunq9WZRtj3d" crossorigin="anonymous" referrerpolicy="no-referrer" defer></script>
  <script src="{{ asset('assets/frontend/js/ag-motion.js') }}?v={{ @filemtime(public_path('assets/frontend/js/ag-motion.js')) }}" defer></script>
  @endif

  {{-- WhatsApp Click Tracking (GTM dataLayer) --}}
  <script>
  (function () {
    window.dataLayer = window.dataLayer || [];

    function handleClick(e) {
      e.preventDefault();
      const a = e.target.closest('a.whatsapp-btn');
      if (!a) return;

      const url    = a.getAttribute('href');
      const label  = a.dataset.gtmLabel || a.textContent.trim();
      const isBlank = a.getAttribute('target') === '_blank';

      let done = false;
      const go = () => {
        if (done) return;
        done = true;
        isBlank ? window.open(url, '_blank', 'noopener') : (window.location.href = url);
      };

      window.dataLayer.push({
        event          : 'whatsapp_click',
        link_url       : url,
        link_text      : label,
        page_path      : window.location.pathname,
        event_callback : go,
        event_timeout  : 1000,
      });

      setTimeout(go, 1100);
    }

    document.addEventListener('click', function (e) {
      if (e.target.closest('a.whatsapp-btn')) handleClick(e);
    });
  })();
  </script>
</body>
</html>
