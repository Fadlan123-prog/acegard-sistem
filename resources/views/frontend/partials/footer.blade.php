@php
  use App\Support\SiteContent;
  use App\Support\Whatsapp;
  $faqHref     = Route::has('frontend.faq') ? route('frontend.faq') : null;
  $artikelHref = Route::has('frontend.artikel') ? route('frontend.artikel') : null;
  $dealersHref = Route::has('frontend.dealers') ? route('frontend.dealers') : null;
@endphp
<footer class="ag-footer">
  <div class="ag-container">
    <div class="ag-footer-grid">
      <div class="ag-footer-brand">
        <a href="{{ route('frontend.home') }}" aria-label="Acegard — beranda">
          <img src="{{ asset('assets/frontend/ag/logo-white.webp') }}" alt="Acegard" width="236" height="28">
        </a>
        <p>Solusi kaca film mobil, kaca film gedung, dan Paint Protection Film untuk kendaraan dan properti Anda.</p>
        <div class="ag-footer-social">
          @foreach (SiteContent::social() as [$label, $url, $icon])
            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}"><iconify-icon icon="{{ $icon }}" width="20" aria-hidden="true"></iconify-icon></a>
          @endforeach
        </div>
      </div>

      <nav aria-label="Layanan">
        <h4 role="heading" aria-level="3">Layanan</h4>
        <ul class="big">
          <li><a href="{{ route('frontend.service.car') }}">Automotive Film</a></li>
          <li><a href="{{ route('frontend.simulator') }}">Simulator VLT</a></li>
          <li><a href="{{ route('frontend.service.building') }}">Building Film</a></li>
          <li><a href="{{ route('frontend.ppf.catalog') }}">Paint Protection</a></li>
          <li><a href="{{ route('warranty.form') }}">Cek Garansi</a></li>
        </ul>
      </nav>

      <nav aria-label="Perusahaan">
        <h4 role="heading" aria-level="3">Perusahaan</h4>
        <ul class="big">
          <li><a href="{{ route('frontend.about') }}">About Us</a></li>
          <li><a href="{{ route('frontend.contact') }}">Contact</a></li>
          @if ($faqHref)<li><a href="{{ $faqHref }}">FAQ</a></li>@endif
          @if ($artikelHref)<li><a href="{{ $artikelHref }}">Artikel</a></li>@endif
          @if ($dealersHref)<li><a href="{{ $dealersHref }}">Lokasi Dealer</a></li>@endif
        </ul>
      </nav>

      <div>
        <h4 role="heading" aria-level="3">Hubungi Kami</h4>
        <div class="ag-footer-contact">
          <div><iconify-icon icon="mdi:map-marker-outline" width="20" aria-hidden="true"></iconify-icon><span>{{ config('acegard.address') }}</span></div>
          <div><iconify-icon icon="mdi:email-outline" width="20" aria-hidden="true"></iconify-icon><a href="mailto:{{ config('acegard.email') }}">{{ config('acegard.email') }}</a></div>
          <div><iconify-icon icon="mdi:clock-outline" width="20" aria-hidden="true"></iconify-icon><span>{{ SiteContent::HOURS }}</span></div>
        </div>
        <a class="ag-footer-wa whatsapp-btn" data-gtm-label="footer-whatsapp" target="_blank" rel="noopener" href="{{ Whatsapp::url() }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> WhatsApp {{ Whatsapp::display() }}
        </a>
      </div>
    </div>
  </div>

  <p class="ag-footer-mark" aria-hidden="true">ACEGARD</p>

  <div class="ag-container">
    <div class="ag-footer-bottom">
      <span>Copyright {{ date('Y') }} - ACEGARD, All Rights Reserved</span>
      <span>Tangerang, Indonesia</span>
      <button type="button" class="ag-motion-toggle" data-motion-toggle aria-pressed="false">Animasi: nyala</button>
    </div>
  </div>
</footer>
