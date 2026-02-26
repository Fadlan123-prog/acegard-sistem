@php
  $mask = fn($s) => $s ? substr($s,0,3).'***'.substr($s,-2) : '-';
@endphp

<div class="row g-3">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-2">Customer (Kaca Film Gedung)</h6>
        <div><b>Nama:</b> {{ $b->name }}</div>
        <div><b>Phone:</b> {{ $mask($b->phone_number) }}</div>
        <div><b>Email:</b> {{ $b->email ? '***@'.explode('@',$b->email)[1] : '-' }}</div>
        <div><b>Alamat:</b> {{ $b->address }}, {{ $b->city }}, {{ $b->country }}</div>
        <div><b>Dealer:</b> {{ $b->dealer_name ?? '-' }}</div>
        <div><b>Applicator:</b> {{ $b->applicator ?? '-' }}</div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-3">Ajukan Claim (Per Customer Building)</h6>

        <form method="POST" action="{{ route('warranty.claim.store') }}" class="row g-3">
          @csrf
          <input type="hidden" name="type" value="building_film">
          <input type="hidden" name="id" value="{{ $b->id }}">
          <input type="hidden" name="card_number" value="{{ $card }}">

          <div class="col-md-12">
            <label class="form-label">Keluhan</label>
            <textarea name="complaint" class="form-control" rows="3" required>{{ old('complaint') }}</textarea>
            <div class="form-text">Claim akan dicatat untuk customer building ini (tanpa pilih produk).</div>
          </div>

          <div class="col-12">
            <button class="btn btn-primary">Kirim Claim</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
