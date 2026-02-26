@extends('dashboard.index')

@section('title', 'Detail Claim Invoice')
@section('breadcrumb', 'Claim Invoices')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Invoice Claim #{{ $invoice->invoice_number }}</h5>

    <div class="d-flex gap-2">
      <a href="{{ route('admin.claim-invoices.download', $invoice->id) }}"
         class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
         title="Download PDF">
        <iconify-icon icon="ic:baseline-download"></iconify-icon>
      </a>

      <a href="{{ route('admin.claim-invoices.print', $invoice->id) }}"
         class="w-32-px h-32-px bg-warning-focus text-warning-600 rounded-circle d-inline-flex align-items-center justify-content-center"
         title="Print View">
        <iconify-icon icon="mdi:printer-outline"></iconify-icon>
      </a>

      <a href="{{ route('admin.claim-invoices.edit', $invoice->id) }}"
         class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
         title="Edit Invoice">
        <iconify-icon icon="lucide:edit"></iconify-icon>
      </a>

      <form action="{{ route('admin.claim-invoices.destroy', $invoice->id) }}" method="POST"
            onsubmit="return confirm('Hapus invoice ini?')" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit"
          class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
          title="Hapus Invoice">
          <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
        </button>
      </form>
    </div>
  </div>

  <div class="card-body">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="mb-3">
      <div class="text-muted">Claim: <a href="{{ route('admin.claims.show', $invoice->claim->id) }}">{{ $invoice->claim->claim_number }}</a></div>
      <div class="text-muted">Date: {{ $invoice->invoice_date }}</div>
      <div class="text-muted">Payment: {{ strtoupper($invoice->payment_method) }}</div>
      <div class="text-muted">Status: <span class="badge bg-{{ $invoice->status==='paid'?'success':($invoice->status==='void'?'dark':'danger') }}">{{ strtoupper($invoice->status) }}</span></div>
    </div>

    <div class="table-responsive">
      <table class="table bordered-table mb-0">
        <thead>
          <tr>
            <th>Description</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Unit</th>
            <th class="text-end">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($invoice->items as $it)
            <tr>
              <td>{{ $it->description }}</td>
              <td class="text-end">{{ $it->qty }}</td>
              <td class="text-end">Rp{{ number_format($it->unit_price,0,',','.') }}</td>
              <td class="text-end">Rp{{ number_format($it->total,0,',','.') }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">Subtotal</th>
            <th class="text-end">Rp{{ number_format($invoice->subtotal,0,',','.') }}</th>
          </tr>
          <tr>
            <th colspan="3" class="text-end">Discount</th>
            <th class="text-end">Rp{{ number_format($invoice->discount,0,',','.') }}</th>
          </tr>
          <tr>
            <th colspan="3" class="text-end">Total</th>
            <th class="text-end">Rp{{ number_format($invoice->total_price,0,',','.') }}</th>
          </tr>
          <tr>
            <th colspan="3" class="text-end">Downpayment</th>
            <th class="text-end">Rp{{ number_format($invoice->downpayment,0,',','.') }}</th>
          </tr>
          <tr>
            <th colspan="3" class="text-end">Remaining</th>
            <th class="text-end">Rp{{ number_format($invoice->remaining_payment,0,',','.') }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endsection
