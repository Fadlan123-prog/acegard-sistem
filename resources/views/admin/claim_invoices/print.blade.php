<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $invoice->invoice_number }}</title>
  <style>
    @page { margin: 0.6cm; size: A4; }
    body { font-family: Arial, sans-serif; font-size: 12px; margin:0; }
    .row { display:flex; justify-content:space-between; }
    .box { border:1px solid #000; padding:10px; }
    table { width:100%; border-collapse:collapse; }
    th,td { border:1px solid #000; padding:6px; }
    th { background:#f2f2f2; text-align:left; }
    .right { text-align:right; }
    .muted { color:#555; }
    .title { font-weight:bold; font-size:16px; margin:0 0 10px; }
  </style>
</head>
<body>
  <div class="row">
    <div>
      <div class="title">CLAIM INVOICE</div>
      <div class="muted">Invoice: <b>{{ $invoice->invoice_number }}</b></div>
      <div class="muted">Date: {{ $invoice->invoice_date }}</div>
      <div class="muted">Claim: {{ $invoice->claim->claim_number }}</div>
      <div class="muted">Card Number: {{ $invoice->claim->card_number }}</div>
    </div>
    <div class="box">
      <div><b>Status:</b> {{ strtoupper($invoice->status) }}</div>
      <div><b>Payment:</b> {{ strtoupper($invoice->payment_method) }}</div>
    </div>
  </div>

  <br>

  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="right">Qty</th>
        <th class="right">Unit</th>
        <th class="right">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $it)
      <tr>
        <td>{{ $it->description }}</td>
        <td class="right">{{ $it->qty }}</td>
        <td class="right">Rp{{ number_format($it->unit_price,0,',','.') }}</td>
        <td class="right">Rp{{ number_format($it->total,0,',','.') }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr><th colspan="3" class="right">Subtotal</th><th class="right">Rp{{ number_format($invoice->subtotal,0,',','.') }}</th></tr>
      <tr><th colspan="3" class="right">Discount</th><th class="right">Rp{{ number_format($invoice->discount,0,',','.') }}</th></tr>
      <tr><th colspan="3" class="right">Total</th><th class="right">Rp{{ number_format($invoice->total_price,0,',','.') }}</th></tr>
      <tr><th colspan="3" class="right">Downpayment</th><th class="right">Rp{{ number_format($invoice->downpayment,0,',','.') }}</th></tr>
      <tr><th colspan="3" class="right">Remaining</th><th class="right">Rp{{ number_format($invoice->remaining_payment,0,',','.') }}</th></tr>
    </tfoot>
  </table>

  <br>
  <div class="muted">Notes: Invoice claim ini dibuat untuk biaya per item/panel akibat user damage / chargeable.</div>
</body>
</html>
