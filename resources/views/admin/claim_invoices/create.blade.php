@extends('dashboard.index')

@section('title', 'Buat Invoice Claim')
@section('breadcrumb', 'Claim Invoices')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Buat Invoice Claim - {{ $claim->claim_number }}</h5>
    <a href="{{ route('admin.claims.show', $claim->id) }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>

  <div class="card-body">
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="alert alert-warning">
      Invoice hanya untuk claim <b>chargeable</b> (cause = user). Subtotal diambil dari charge_amount per item.
    </div>

    <form method="POST" action="{{ route('admin.claim-invoices.store', $claim->id) }}" class="row g-3">
      @csrf

      <div class="col-md-4">
        <label class="form-label">Payment Method</label>
        <input type="text" class="form-control" name="payment_method" value="{{ old('payment_method','transfer') }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Discount</label>
        <input type="number" min="0" class="form-control" name="discount" value="{{ old('discount',0) }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">Downpayment</label>
        <input type="number" min="0" class="form-control" name="downpayment" value="{{ old('downpayment',0) }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="unpaid" selected>UNPAID</option>
          <option value="paid">PAID</option>
          <option value="void">VOID</option>
        </select>
      </div>

      <div class="col-12">
        <h6 class="mb-2">Items</h6>
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th>Item</th>
                <th style="width:180px;">Charge</th>
              </tr>
            </thead>
            <tbody>
              @php $subtotal = 0; @endphp
              @foreach($claim->items as $it)
                @php $subtotal += (int)$it->charge_amount; @endphp
                <tr>
                  <td>{{ $it->item_label ?? $it->item_key ?? '-' }}</td>
                  <td>Rp{{ number_format($it->charge_amount,0,',','.') }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th class="text-end">Subtotal</th>
                <th>Rp{{ number_format($subtotal,0,',','.') }}</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="col-12">
        <button class="btn btn-danger">Create Invoice</button>
      </div>
    </form>
  </div>
</div>
@endsection
