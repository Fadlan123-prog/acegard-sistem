<footer class="pt-5 pb-4 mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-2">
          <img src="{{ asset('assets/img/logo.png') }}" alt="Acegard" style="height:34px;">
          <span class="fw-bold">ACEGARD</span>
        </div>
        <p class="mb-0 small">
          Kaca film nano ceramic dengan solar reject tinggi. Konsultasi & pemasangan profesional.
        </p>
      </div>

      <div class="col-lg-4">
        <h6 class="fw-bold">Useful Links</h6>
        <ul class="list-unstyled small mb-0">
          <li><a href="#product">Product</a></li>
          <li><a href="#gallery">Gallery</a></li>
          <li><a href="#partner">Partner</a></li>
          <li><a href="{{ route('warranty.form') }}">Warranty Claim</a></li>
        </ul>
      </div>

      <div class="col-lg-4">
        <h6 class="fw-bold">Contact</h6>
        <div class="small">
          <div class="mb-2">📍 Jl. KH Hasyim Ashari, Kota Tangerang</div>
          <div class="mb-2">📞 08xx-xxxx-xxxx</div>
          <div>✉️ admin@acegard.example</div>
        </div>
      </div>
    </div>

    <hr class="border-light opacity-25 my-4">

    <div class="d-flex flex-column flex-md-row justify-content-between small">
      <div>© {{ date('Y') }} Acegard. All rights reserved.</div>
      <div class="d-flex gap-3">
        <a href="#">Instagram</a>
        <a href="#">WhatsApp</a>
        <a href="#">Maps</a>
      </div>
    </div>
  </div>
</footer>
