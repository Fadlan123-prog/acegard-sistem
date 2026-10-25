<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Acegard Indonesia' }}</title>

  <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

  {{-- Google Tag Manager --}}
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-5DH3T7FD');</script>

  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- Iconify (mdi:* icons) --}}
  <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js" async></script>

  {{-- Inter --}}
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/frontend/home/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/frontend/css/tokens.css') }}">
  @stack('styles')
</head>
<body>
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

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')

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
