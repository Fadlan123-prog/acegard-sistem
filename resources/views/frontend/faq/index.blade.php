@extends('frontend.layout.index')

@php
  use App\Support\FaqCatalog;
  use App\Support\Whatsapp;
@endphp

@section('title', 'FAQ - Acegard Indonesia')
@section('meta_description', 'Pertanyaan yang sering diajukan seputar kaca film, PPF, pemasangan, dan garansi di ACEGARD Indonesia.')
@section('body-class', 'ag-page')

@section('content')

@php
  $categories = FaqCatalog::categories();
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-faqp-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">FAQ</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">FAQ</span>
    <h1 class="ag-display" id="ag-faqp-title">Pertanyaan yang <b>sering diajukan.</b></h1>
    <p class="ag-hero-sub">Jawaban seputar kaca film mobil, kaca film gedung, PPF, pemasangan, dan garansi. Cari kata kunci atau pilih kategori.</p>
  </div>
</section>

{{-- ───────── Daftar ───────── --}}
<section class="ag-section" style="padding-top:24px" aria-label="Daftar pertanyaan">
  <div class="ag-container" id="agFaqPage">

    <div class="ag-fbar">
      <label class="ag-search">
        <span class="visually-hidden">Cari pertanyaan</span>
        <iconify-icon icon="mdi:magnify" aria-hidden="true"></iconify-icon>
        <input type="search" id="agFaqSearch" placeholder="Cari pertanyaan, misalnya garansi atau VLT" autocomplete="off">
      </label>
      <div class="ag-chips is-live" role="radiogroup" aria-label="Kategori">
        <label class="ag-chip is-on"><input type="radio" name="faqcat" value="all" class="visually-hidden" checked><span>Semua</span></label>
        @foreach ($categories as $key => $cat)
          <label class="ag-chip"><input type="radio" name="faqcat" value="{{ $key }}" class="visually-hidden"><span>{{ $cat['label'] }}</span></label>
        @endforeach
      </div>
    </div>

    @foreach ($categories as $key => $cat)
      <section class="ag-fcat" data-cat="{{ $key }}" aria-labelledby="ag-fc-{{ $key }}">
        <h2 id="ag-fc-{{ $key }}">{{ $cat['label'] }}</h2>
        <div class="ag-faq">
          @foreach ($cat['items'] as $i => [$q, $a])
            <details class="ag-faq-item" @if ($key === 'umum' && $i === 0) open @endif>
              <summary>{{ $q }}</summary>
              <p>{{ $a }}</p>
            </details>
          @endforeach
        </div>
      </section>
    @endforeach

    <div class="ag-fempty" id="agFaqEmpty" hidden>
      <p style="margin:0 0 16px">Tidak ada pertanyaan yang cocok. Tanyakan langsung ke tim kami.</p>
      <a class="ag-btn ag-btn-primary ag-btn-sm whatsapp-btn" data-gtm-label="faq-empty-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya punya pertanyaan.') }}">Tanya via WhatsApp</a>
    </div>
  </div>
</section>

{{-- ───────── CTA ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-fcta-title">
  <div class="ag-container">
    <div class="ag-cta-panel ag-reveal">
      <h2 id="ag-fcta-title">Belum menemukan <b>jawabannya?</b></h2>
      <p>Tim ACEGARD siap membantu menjawab pertanyaan Anda lewat WhatsApp.</p>
      <div class="ag-cta-actions">
        <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="faq-cta-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya punya pertanyaan.') }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi via WhatsApp
        </a>
        <a class="ag-btn ag-btn-ghost" href="{{ route('frontend.contact') }}">Kontak dan Lokasi</a>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  var root = document.getElementById('agFaqPage');
  if (!root) return;
  var input = document.getElementById('agFaqSearch'), empty = document.getElementById('agFaqEmpty');
  var cats = root.querySelectorAll('.ag-fcat');

  function apply() {
    var term = input.value.trim().toLowerCase();
    var active = root.querySelector('input[name="faqcat"]:checked').value;
    var any = false;
    cats.forEach(function (c) {
      var inCat = active === 'all' || c.getAttribute('data-cat') === active, shown = 0;
      c.querySelectorAll('.ag-faq-item').forEach(function (d) {
        var ok = inCat && (!term || d.textContent.toLowerCase().indexOf(term) !== -1);
        d.hidden = !ok;
        if (ok) shown++;
        if (term && ok) d.open = true;
      });
      c.hidden = shown === 0;
      if (shown) any = true;
    });
    empty.hidden = any;
    root.querySelectorAll('.ag-chip').forEach(function (l) { l.classList.toggle('is-on', l.querySelector('input').checked); });
  }

  input.addEventListener('input', apply);
  root.addEventListener('change', function (e) { if (e.target.name === 'faqcat') apply(); });
})();
</script>
@endpush

@endsection

@section('jsonld')
  @php
    $all = collect($categories)->flatMap(fn ($c) => $c['items'])->unique(0)->values()->all();
  @endphp
  {!! \App\Support\Seo::breadcrumbs([['Home', route('frontend.home')], ['FAQ', route('frontend.faq')]]) !!}
  {!! \App\Support\Seo::faq($all) !!}
@endsection
