@extends('frontend.layout.index')

@section('title', 'Pilih Warranty')

@section('content')
<div class="container py-5" style="max-width:720px;">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Card Number ditemukan di beberapa kategori</h5>
      <a href="{{ route('warranty.form') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>

    <div class="card-body">
      <div class="alert alert-warning mb-3">
        Card Number: <b>{{ $card }}</b>. Silakan pilih kategori yang sesuai.
      </div>

      <div class="list-group">
        @foreach($matches as $m)
          <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
             href="{{ route('warranty.result', ['type'=>$m['type'],'id'=>$m['id'],'card_number'=>$card]) }}">
            <div>
              <div class="fw-bold">{{ $m['label'] }}</div>
              <div class="text-muted small">{{ $m['summary'] }}</div>
            </div>
            <span class="badge bg-primary">Pilih</span>
          </a>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
