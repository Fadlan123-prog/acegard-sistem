<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('frontend.home') }}">
      <img src="{{ asset('assets/img/logo.png') }}" alt="Acegard" style="height:34px;">
      <span class="fw-bold text-dark">ACEGARD</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topnav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#product">Product</a></li>
        <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="#partner">Partner</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>

        <li class="nav-item ms-lg-2">
          {{-- di mobile full width --}}
          <a class="btn btn-outline-primary w-100 w-lg-auto" href="{{ route('warranty.form') }}">
            Warranty Claim
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
