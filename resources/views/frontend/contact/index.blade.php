@extends('frontend.layout.index')

@php
  use App\Support\SiteContent;
  use App\Support\Whatsapp;
@endphp

@section('title', 'Kontak - Acegard Indonesia')
@section('meta_description', 'Hubungi ACEGARD Indonesia lewat WhatsApp atau kunjungi workshop kami di Cipondoh, Tangerang untuk konsultasi kaca film dan PPF.')
@section('body-class', 'ag-page')

@section('content')

@php
  $address = config('acegard.address');
  $email = config('acegard.email');
  $mapSrc = 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7932.866895562227!2d106.6881984!3d-6.2064166!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ed2a0d3377ef%3A0x49dc44beb8c49b02!2sAcegard%20Indonesia!5e0!3m2!1sid!2sid!4v1755761775295!5m2!1sid!2s';
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-contact-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Kontak</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">Contact</span>
    <h1 class="ag-display" id="ag-contact-title">Hubungi <b>tim ACEGARD.</b></h1>
    <p class="ag-hero-sub">Konsultasi gratis lewat WhatsApp, atau kunjungi workshop kami di Cipondoh, Tangerang.</p>
  </div>
</section>

{{-- ───────── Informasi dan formulir ───────── --}}
<section class="ag-section" style="padding-top:24px" aria-label="Informasi kontak">
  <div class="ag-container ag-cgrid">

    <div class="ag-cinfo ag-reveal">
      <div class="ag-ccard">
        <span class="ic"><iconify-icon icon="mdi:whatsapp" aria-hidden="true"></iconify-icon></span>
        <div><h3>WhatsApp</h3>
          <a class="whatsapp-btn" data-gtm-label="contact-whatsapp" target="_blank" rel="noopener" href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}">{{ Whatsapp::display() }}</a></div>
      </div>
      <div class="ag-ccard">
        <span class="ic"><iconify-icon icon="mdi:email-outline" aria-hidden="true"></iconify-icon></span>
        <div><h3>Email</h3><a href="mailto:{{ $email }}">{{ $email }}</a></div>
      </div>
      <div class="ag-ccard">
        <span class="ic"><iconify-icon icon="mdi:map-marker-outline" aria-hidden="true"></iconify-icon></span>
        <div><h3>Workshop</h3><p>{{ $address }}</p></div>
      </div>
      <div class="ag-ccard">
        <span class="ic"><iconify-icon icon="mdi:clock-outline" aria-hidden="true"></iconify-icon></span>
        <div><h3>Jam operasional</h3><p>{{ SiteContent::HOURS }}.</p></div>
      </div>
      <div class="ag-ccard">
        <span class="ic"><iconify-icon icon="mdi:account-group-outline" aria-hidden="true"></iconify-icon></span>
        <div><h3>Ikuti kami</h3>
          <div class="ag-social">
            @foreach (SiteContent::social() as [$name, $url, $icon])
              <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $name }} ACEGARD"><iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon></a>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <form class="ag-form ag-reveal" id="agContactForm" onsubmit="return false">
      <h2>Tulis pesan Anda</h2>
      <p class="sub">Pesan akan terbuka di WhatsApp, lalu tim kami membalas langsung ke nomor Anda.</p>
      <label>Nama<input type="text" id="cfName" autocomplete="name" placeholder="Nama Anda"></label>
      <label>Kebutuhan
        <select id="cfNeed">
          <option value="kaca film mobil">Kaca film mobil</option>
          <option value="kaca film gedung atau rumah">Kaca film gedung atau rumah</option>
          <option value="PPF (Paint Protection Film)">PPF (Paint Protection Film)</option>
          <option value="garansi atau layanan purna jual">Garansi atau layanan purna jual</option>
          <option value="lainnya">Lainnya</option>
        </select>
      </label>
      <label>Pesan<textarea id="cfMsg" placeholder="Tulis pertanyaan atau kebutuhan Anda"></textarea></label>
      <a class="ag-btn ag-btn-primary whatsapp-btn" id="cfSend" data-gtm-label="contact-form-whatsapp" target="_blank" rel="noopener"
         href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}">
        <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Kirim lewat WhatsApp
      </a>
    </form>
  </div>
</section>

{{-- ───────── Peta ───────── --}}
<section class="ag-section alt" aria-labelledby="ag-map-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Location</span>
      <h2 class="ag-h2" id="ag-map-title">Kunjungi <b>workshop kami</b></h2>
    </div>
    <div class="ag-map ag-reveal">
      <iframe src="{{ $mapSrc }}" title="Peta lokasi workshop ACEGARD Indonesia" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
      <p class="ag-map-band">{{ $address }}</p>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  var form = document.getElementById('agContactForm');
  if (!form) return;
  var name = document.getElementById('cfName'), need = document.getElementById('cfNeed'), msg = document.getElementById('cfMsg'), send = document.getElementById('cfSend');
  var base = @json(Whatsapp::url());
  function update() {
    var n = name.value.trim(), m = msg.value.trim();
    var text = 'Halo ACEGARD' + (n ? ', saya ' + n : '') + '. Saya ingin berkonsultasi soal ' + need.value + '.' + (m ? ' ' + m : '');
    send.href = base + '?text=' + encodeURIComponent(text);
  }
  ['input', 'change'].forEach(function (e) { form.addEventListener(e, update); });
  update();
})();
</script>
@endpush

@endsection

@section('jsonld')
  {!! \App\Support\Seo::breadcrumbs([['Home', route('frontend.home')], ['Kontak', route('frontend.contact')]]) !!}
@endsection
