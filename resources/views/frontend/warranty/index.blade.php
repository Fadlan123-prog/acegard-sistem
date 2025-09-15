@extends('frontend.layout.index')

@once
    @push('styles')
        <link rel="stylesheet" href="{{asset('assets/frontend/css/bootstrap.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/meanmenu.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/owl.theme.default.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/owl.carousel.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/icofont.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/font-awesome-pro.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/scrollcue.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/magnific-popup.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/style.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/responsive.css')}}">
		<!-- Favicon -->
		<link rel="icon" type="image/png" href="{{asset('assets/frontend/images/acegard-favicon.png')}}">
    @endpush
@endonce

@section('content')
    <div class="banner-area bg-black bg-img" data-background="./assets/frontend/images/warranty-bg.jpg">
  <div class="container mw-1680">
    <div class="position-relative z-1" data-cues="slideInUp" data-group="images">
      <div class="text-center">
        <img src="{{ asset('assets/images/acegard-logo.svg') }}" alt="logo" style="width: 350px; height:auto;">
        <div class="row">
          <div class="col-md-4">
            <h3 style="color:#fff; text-align:end;">Enter Your Warranty <br>Card Number</h3>
          </div>

          <div class="col-md-4">
            <form method="GET" action="{{ route('frontend.warranty') }}">
              <div class="form-group">
                <input
                  type="text"
                  name="card_number"
                  value="{{ old('card_number', $card_number ?? '') }}"
                  class="form-control"
                  placeholder="Card Number"
                  style="color: #fff;"
                >

                @if(isset($customer) && $customer && $customer->invoice)
                  {{-- Ketemu invoice: ubah tombol menjadi VIEW CARD (link ke halaman kartu) --}}
                  <a href="{{ route('frontend.warranty.invoice', $customer->invoice->id) }}"
                     class="btn btn-success mt-2 px-4" style="font-size:26px;">
                     VIEW CARD
                  </a>

                  {{-- Opsional: tampilkan ringkas info invoice di halaman ini --}}
                  <div class="mt-3 text-start text-white-50">
                    <div><strong>Customer:</strong> {{ $customer->name }}</div>
                    <div><strong>Card #:</strong> {{ $customer->card_number }}</div>
                    <div><strong>Invoice #:</strong> {{ $customer->invoice->number ?? $customer->invoice->id }}</div>
                    <div><strong>Date:</strong> {{ optional($customer->invoice->created_at)->format('d M Y') }}</div>
                  </div>
                @elseif(!empty($card_number))
                  {{-- Card number diisi tapi tidak ketemu --}}
                  <div class="alert alert-warning mt-2">Card number tidak ditemukan.</div>
                  <button type="submit" class="btn btn-primary mt-2 px-4" style="font-size:26px;">SUBMIT</button>
                @else
                  {{-- Belum ada input: tombol SUBMIT biasa --}}
                  <button type="submit" class="btn btn-primary mt-2 px-4" style="font-size:26px;">SUBMIT</button>
                @endif
              </div>
            </form>
          </div>

          <div class="col-md-4"></div>
        </div>
      </div>
    </div>
  </div>
</div>
		<!--=== End Banner Area ===-->
@endsection
