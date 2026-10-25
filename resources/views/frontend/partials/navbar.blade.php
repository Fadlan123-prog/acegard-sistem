@php
  $ppfHref     = Route::has('frontend.ppf.catalog') ? route('frontend.ppf.catalog') : '#';
  $dealersHref = Route::has('frontend.dealers') ? route('frontend.dealers') : '#';
  $faqHref     = Route::has('frontend.faq') ? route('frontend.faq') : '#';
  $artikelHref = Route::has('frontend.artikel') ? route('frontend.artikel') : '#';
@endphp
<nav class="navbar navbar-expand-lg ace-navbar bg-white border-bottom sticky-top py-3">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('frontend.home') }}">
      <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard" style="height:32px;">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topnav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('frontend.service.car') ? 'active' : '' }}" href="{{ route('frontend.service.car') }}">Kaca Film Otomotif</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('frontend.service.building') ? 'active' : '' }}" href="{{ route('frontend.service.building') }}">Kaca Film Bangunan</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('frontend.ppf.catalog') ? 'active' : '' }}" href="{{ $ppfHref }}">PPF</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('frontend.about') ? 'active' : '' }}" href="{{ route('frontend.about') }}">Tentang Kami</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('frontend.dealers') ? 'active' : '' }}" href="{{ $dealersHref }}">Dealers</a>
        </li>

        <li class="nav-item ms-lg-2">
          <a class="btn-ace-outline w-100 w-lg-auto" href="{{ route('warranty.form') }}">
            Warranty Claim
          </a>
        </li>

        <li class="nav-item dropdown ms-lg-2">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Selengkapnya
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ $faqHref }}">FAQ</a></li>
            <li><a class="dropdown-item" href="{{ $artikelHref }}">Artikel</a></li>
            <li><a class="dropdown-item" href="{{ route('frontend.contact') }}">Contact Us</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
