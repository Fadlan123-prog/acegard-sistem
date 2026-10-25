@extends('dashboard.index')

@section('title', 'Warranty Result')
@section('breadcrumb', 'Warranty Claim')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Warranty Result</h5>
    <a href="{{ route('warranty.form') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>

  <div class="card-body">
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="alert alert-info">
      Card Number: <b>{{ $card }}</b>
    </div>

    @if($type === 'car_film')
      @include('public.warranty.partials.car_film', ['customer' => $data, 'card' => $card])
    @elseif($type === 'building_film')
      @include('public.warranty.partials.building_film', ['b' => $data, 'card' => $card])
    @else
      @include('public.warranty.partials.ppf', ['ppf' => $data, 'card' => $card])
    @endif
  </div>
</div>
@endsection
