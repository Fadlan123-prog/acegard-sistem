@php
  $faqHref = Route::has('frontend.faq') ? route('frontend.faq') : '#';
@endphp
<footer class="ace-footer pt-5 pb-4 mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <a href="{{ route('frontend.home') }}" class="d-inline-flex align-items-center gap-2 mb-3 text-decoration-none">
          <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard" style="height:32px;">
        </a>
        <p class="mb-3 small ace-muted">
          Bergabunglah bersama kami dalam menghadirkan solusi kaca film terbaik untuk kendaraan dan properti Anda. Hubungi Acegard sekarang juga.
        </p>
        <div class="d-flex gap-3 fs-5">
          <a href="#" aria-label="Facebook"><iconify-icon icon="mdi:facebook"></iconify-icon></a>
          <a href="#" aria-label="Instagram"><iconify-icon icon="mdi:instagram"></iconify-icon></a>
          <a href="#" aria-label="TikTok"><iconify-icon icon="mdi:music-note"></iconify-icon></a>
          <a href="#" aria-label="YouTube"><iconify-icon icon="mdi:youtube"></iconify-icon></a>
        </div>
      </div>

      <div class="col-lg-4">
        <h6 class="mb-3">Useful Links</h6>
        <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
          <li><a href="{{ route('frontend.about') }}">About Us</a></li>
          <li><a href="{{ route('frontend.service.car') }}">Our Service</a></li>
          <li><a href="{{ route('frontend.contact') }}">Contact Us</a></li>
          <li><a href="{{ $faqHref }}">FAQs</a></li>
        </ul>
      </div>

      <div class="col-lg-4">
        <h6 class="mb-3">Contact Us</h6>
        <div class="small d-flex flex-column gap-2">
          <div class="ace-muted">Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146</div>
          <div><a href="mailto:acegardindonesia@gmail.com">acegardindonesia@gmail.com</a></div>
          <div class="ace-muted">+6282121212093</div>
        </div>
      </div>
    </div>

    <hr class="my-4" style="border-color: var(--ace-border);">

    <div class="text-center small ace-muted">
      Copyright {{ date('Y') }} - ACEGARD, All Rights Reserved
    </div>
  </div>
</footer>
