@php
  use App\Support\Whatsapp;
  $faqHref     = Route::has('frontend.faq') ? route('frontend.faq') : null;
  $artikelHref = Route::has('frontend.artikel') ? route('frontend.artikel') : null;
  $dealersHref = Route::has('frontend.dealers') ? route('frontend.dealers') : null;
@endphp
<footer class="ag-footer">
  <div class="ag-container">
    <div class="ag-footer-grid">
      <div class="ag-footer-cell">
        <a href="{{ route('frontend.home') }}" aria-label="Acegard — beranda">
          <img src="{{ asset('assets/frontend/ag/logo-white.webp') }}" alt="Acegard" width="236" height="28">
        </a>
        <p>Solusi kaca film mobil, kaca film gedung, dan Paint Protection Film untuk kendaraan dan properti Anda.</p>
      </div>

      <div class="ag-footer-cell">
        <h4 role="heading" aria-level="3">Layanan</h4>
        <ul class="big">
          <li><a href="{{ route('frontend.service.car') }}">Automotive Film</a></li>
          <li><a href="{{ route('frontend.simulator') }}">Simulator VLT</a></li>
          <li><a href="{{ route('frontend.service.building') }}">Building Film</a></li>
          <li><a href="{{ route('frontend.ppf.catalog') }}">Paint Protection</a></li>
          <li><a href="{{ route('warranty.form') }}">Cek Garansi</a></li>
        </ul>
      </div>

      <div class="ag-footer-cell">
        <h4 role="heading" aria-level="3">Perusahaan</h4>
        <ul class="big">
          <li><a href="{{ route('frontend.about') }}">About Us</a></li>
          <li><a href="{{ route('frontend.contact') }}">Contact</a></li>
          @if ($faqHref)<li><a href="{{ $faqHref }}">FAQ</a></li>@endif
          @if ($artikelHref)<li><a href="{{ $artikelHref }}">Artikel</a></li>@endif
          @if ($dealersHref)<li><a href="{{ $dealersHref }}">Lokasi Dealer</a></li>@endif
        </ul>
      </div>

      <div class="ag-footer-cell">
        <h4 role="heading" aria-level="3">Kontak</h4>
        <p>{{ config('acegard.address') }}</p>
        <p><a href="mailto:{{ config('acegard.email') }}">{{ config('acegard.email') }}</a></p>
        <p><a class="whatsapp-btn" data-gtm-label="footer-whatsapp" target="_blank" rel="noopener" href="{{ Whatsapp::url() }}">WhatsApp {{ Whatsapp::display() }}</a></p>
      </div>
    </div>

    <div class="ag-footer-bottom">
      <span>Copyright {{ date('Y') }} - ACEGARD, All Rights Reserved</span>
      <span>Tangerang, Indonesia</span>
    </div>
  </div>
</footer>
