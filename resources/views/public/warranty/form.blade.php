@extends('frontend.layout.index')

@section('title', 'Warranty Claim')

@section('content')
<style>
  .warranty-hero{
    background: radial-gradient(1200px circle at 10% 10%, rgba(13,110,253,.18), transparent 50%),
                radial-gradient(900px circle at 90% 20%, rgba(25,135,84,.14), transparent 45%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 55%, #ffffff 100%);
  }
  .glass-card{
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 10px 30px rgba(0,0,0,.06);
    border-radius: 16px;
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(8px);
  }
  .step-dot{
    width: 34px; height: 34px;
    border-radius: 999px;
    display: inline-flex; align-items:center; justify-content:center;
    font-weight: 700;
  }
  .brand-chip{
    border: 1px dashed rgba(0,0,0,.15);
    border-radius: 999px;
    padding: 6px 12px;
    background: rgba(255,255,255,.8);
  }
  .btn-claim{
    border-radius: 12px;
    padding: 10px 14px;
    font-weight: 600;
  }
</style>

<div class="warranty-hero py-5">
  <div class="container">

    {{-- Alerts --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <div class="fw-bold mb-1">Something went wrong:</div>
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row align-items-center g-4">
      {{-- Left: Hero copy --}}
      <div class="col-lg-6">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="brand-chip">
            <span class="fw-semibold">ACEGARD</span>
            <span class="text-muted">• Warranty Center</span>
          </div>
          <span class="badge bg-success-subtle text-success">Fast Claim</span>
        </div>

        <h1 class="fw-bold mb-2" style="letter-spacing:-.5px;">
          Warranty Claim
        </h1>
        <p class="text-muted mb-4" style="max-width: 520px;">
          Enter your <b>Card Number</b> to view installation history and submit a warranty claim.
          Supported services: <b>automotive window film</b>, <b>building window film</b>, and <b>PPF</b>.
        </p>

        <div class="row g-3">
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-3 bg-white border">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="step-dot bg-primary-subtle text-primary">1</span>
                <span class="fw-semibold">Enter</span>
              </div>
              <div class="text-muted small">Type your Card Number</div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-3 bg-white border">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="step-dot bg-warning-subtle text-warning">2</span>
                <span class="fw-semibold">Select</span>
              </div>
              <div class="text-muted small">Choose a category if more than one is available</div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-3 bg-white border">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="step-dot bg-success-subtle text-success">3</span>
                <span class="fw-semibold">Claim</span>
              </div>
              <div class="text-muted small">Select panels/items and submit your claim</div>
            </div>
          </div>
        </div>

        <div class="mt-4 text-muted small">
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light text-dark border">Secure lookup</span>
            <span class="badge bg-light text-dark border">Brief summary</span>
            <span class="badge bg-light text-dark border">Mobile friendly</span>
          </div>
        </div>
      </div>

      {{-- Right: Form Card --}}
      <div class="col-lg-6">
        <div class="glass-card p-4 p-md-5">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <h5 class="fw-bold mb-1">Find Warranty Details</h5>
              <div class="text-muted small">Enter the Card Number shown on your warranty card/registration.</div>
            </div>
            <span class="badge bg-dark">Warranty</span>
          </div>

          <form method="POST" action="{{ route('warranty.lookup') }}" id="warrantyForm" class="mt-3">
            @csrf

            <label class="form-label fw-semibold">Card Number</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-white">
                <span class="text-muted">#</span>
              </span>
              <input
                type="text"
                name="card_number"
                class="form-control"
                placeholder="Example: PPF-1-20251230-0002"
                value="{{ old('card_number') }}"
                required
                autocomplete="off"
                inputmode="text"
              >
            </div>

            <label class="form-label fw-semibold mt-3">4 digit terakhir nomor HP</label>
            <input
              type="text"
              name="phone_last4"
              class="form-control form-control-lg"
              placeholder="Contoh: 1234"
              maxlength="4"
              pattern="[0-9]{4}"
              inputmode="numeric"
              required
              autocomplete="off"
            >

            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mt-3">
              <div class="text-muted small">
                Tip: you can usually find it on your <b>warranty card</b> or <b>invoice</b>.
              </div>
              <button type="submit" class="btn btn-primary btn-claim" id="btnSubmit">
                Search
              </button>
            </div>

            <hr class="my-4">

            <div class="p-3 rounded-3 bg-light border">
              <div class="fw-semibold mb-1">What may be covered at no cost</div>
              <div class="text-muted small">
                Defects caused by <b>installation workmanship</b> or <b>material issues</b> during the warranty period.
                Issues caused by <b>usage/accidents (e.g., collision)</b> may be chargeable per panel/item.
              </div>
            </div>
          </form>

          <div class="accordion mt-4" id="faqWarranty">
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq1">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                  Card Number not found — what should I do?
                </button>
              </h2>
              <div id="faqCollapse1" class="accordion-collapse collapse" data-bs-parent="#faqWarranty">
                <div class="accordion-body text-muted">
                  Make sure there are no spaces, double-check letters/numbers, and try again.
                  If it still fails, please contact the dealer/branch where the installation was performed.
                </div>
              </div>
            </div>

            <div class="accordion-item">
              <h2 class="accordion-header" id="faq2">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                  Do I need to log in?
                </button>
              </h2>
              <div id="faqCollapse2" class="accordion-collapse collapse" data-bs-parent="#faqWarranty">
                <div class="accordion-body text-muted">
                  No. Just enter your Card Number to view your warranty information.
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

<script>
(function(){
  const form = document.getElementById('warrantyForm');
  const btn  = document.getElementById('btnSubmit');
  if(!form || !btn) return;

  form.addEventListener('submit', function(){
    btn.disabled = true;
    btn.innerText = 'Processing...';
  });

  // auto trim (optional)
  const input = form.querySelector('input[name="card_number"]');
  if(input){
    input.addEventListener('change', () => {
      input.value = (input.value || '').trim();
    });
  }
})();
</script>
@endsection
