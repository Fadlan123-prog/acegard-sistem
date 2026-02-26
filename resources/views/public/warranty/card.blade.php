<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Warranty Card</title>
  <style>
    *{ font-family: DejaVu Sans, sans-serif; }
    body{ font-size: 12px; color:#111; }
    .wrap{ padding: 18px; }
    .header{
      border:1px solid #e5e7eb; padding:14px; border-radius:12px;
    }
    .brand{ font-size: 18px; font-weight: 800; letter-spacing: .5px; }
    .sub{ color:#6b7280; margin-top:4px; }
    .badge{
      display:inline-block; padding:4px 10px; border-radius:999px;
      border:1px solid #e5e7eb; font-size: 11px;
    }
    .row{ margin-top:12px; display:block; }
    .card{
      border:1px solid #e5e7eb; border-radius:12px; padding:12px; margin-top:10px;
    }
    .title{ font-weight: 800; margin-bottom: 8px; }
    table{ width:100%; border-collapse: collapse; }
    td{ padding:6px 0; vertical-align: top; }
    .k{ width: 32%; color:#6b7280; }
    .v{ font-weight: 600; }
    .small{ color:#6b7280; font-size: 11px; }
    .list{ margin: 6px 0 0 16px; }
    .list li{ margin: 3px 0; }
    .divider{ height:1px; background:#e5e7eb; margin:10px 0; }
  </style>
</head>
<body>
@php
  $typeLabel = $type === 'ppf' ? 'PPF' : ($type === 'car_film' ? 'CAR WINDOW FILM' : 'BUILDING WINDOW FILM');
@endphp

<div class="wrap">
  <div class="header">
    <div class="brand">ACEGARD</div>
    <div class="sub">Warranty Card / Warranty Certificate</div>
    <div style="margin-top:10px;">
      <span class="badge">{{ $typeLabel }}</span>
      <span class="badge">Card Number: <b>{{ $card }}</b></span>
      <span class="badge">Issued: <b>{{ now()->format('d M Y') }}</b></span>
    </div>
  </div>

  @if($type === 'ppf')
    @php
      $ppf = $data;
      $installs = ($ppf->installations ?? collect())->sortByDesc('install_date')->values();
      $latest = $installs->first();
    @endphp

    <div class="card">
      <div class="title">Customer</div>
      <table>
        <tr><td class="k">Name</td><td class="v">{{ $ppf->name ?? '-' }}</td></tr>
        <tr><td class="k">Email</td><td class="v">{{ $ppf->email ?? '-' }}</td></tr>
        <tr><td class="k">Phone</td><td class="v">{{ $ppf->phone ?? '-' }}</td></tr>
        <tr><td class="k">Address</td><td class="v">{{ $ppf->address ?? '-' }}, {{ $ppf->city ?? '-' }}</td></tr>
      </table>
    </div>

    <div class="card">
      <div class="title">Vehicle</div>
      <table>
        <tr><td class="k">Plate</td><td class="v">{{ $ppf->vehicle_plate ?? '-' }}</td></tr>
        <tr><td class="k">Brand / Model</td><td class="v">{{ $ppf->vehicle_brand ?? '-' }} / {{ $ppf->vehicle_model ?? '-' }}</td></tr>
        <tr><td class="k">Year</td><td class="v">{{ $ppf->vehicle_year ?? '-' }}</td></tr>
        <tr><td class="k">Color</td><td class="v">{{ $ppf->vehicle_color ?? '-' }}</td></tr>
      </table>
      <div class="divider"></div>
      <div class="small"><b>Installations:</b></div>
      <ul class="list">
        @forelse($installs as $ins)
          <li>
            <b>{{ $ins->ppfProduct->name ?? 'PPF Product' }}</b>
            — Code: <b>{{ $ins->warranty_code ?? '-' }}</b>
            — Date: {{ \Carbon\Carbon::parse($ins->install_date)->format('d M Y') }}
            @if($ins->warranty_end) — End: {{ \Carbon\Carbon::parse($ins->warranty_end)->format('d M Y') }} @endif
          </li>
        @empty
          <li>No installation record found.</li>
        @endforelse
      </ul>
    </div>

  @elseif($type === 'car_film')
    @php
      $car = $data;
      $items = ($car->customerProducts ?? collect())->values();
    @endphp

    <div class="card">
      <div class="title">Customer</div>
      <table>
        <tr><td class="k">Name</td><td class="v">{{ $car->name ?? '-' }}</td></tr>
        <tr><td class="k">Email</td><td class="v">{{ $car->email ?? '-' }}</td></tr>
        <tr><td class="k">Phone</td><td class="v">{{ $car->phone_number ?? '-' }}</td></tr>
        <tr><td class="k">Address</td><td class="v">{{ $car->address ?? '-' }}, {{ $car->city ?? '-' }}</td></tr>
      </table>
    </div>

    <div class="card">
      <div class="title">Vehicle</div>
      <table>
        <tr><td class="k">Plate</td><td class="v">{{ $car->plat_number ?? '-' }}</td></tr>
        <tr><td class="k">Brand / Model</td><td class="v">{{ $car->vehicle_brand ?? '-' }} / {{ $car->vehicle_model ?? '-' }}</td></tr>
        <tr><td class="k">Year</td><td class="v">{{ $car->vehicle_year ?? '-' }}</td></tr>
        <tr><td class="k">Warranty End</td><td class="v">{{ $car->warantee_end ?? '-' }}</td></tr>
      </table>

      <div class="divider"></div>
      <div class="small"><b>Installed Items:</b></div>
      <ul class="list">
        @forelse($items as $row)
          <li>
            {{ trim(($row->part->name ?? '').' | '.($row->categoryProduct->name ?? '').' | '.($row->product->name ?? '')) ?: 'Installed item' }}
          </li>
        @empty
          <li>No installed items recorded.</li>
        @endforelse
      </ul>
    </div>

  @else
    @php $bld = $data; @endphp

    <div class="card">
      <div class="title">Customer</div>
      <table>
        <tr><td class="k">Name</td><td class="v">{{ $bld->name ?? '-' }}</td></tr>
        <tr><td class="k">Email</td><td class="v">{{ $bld->email ?? '-' }}</td></tr>
        <tr><td class="k">Phone</td><td class="v">{{ $bld->phone ?? ($bld->phone_number ?? '-') }}</td></tr>
        <tr><td class="k">Address</td><td class="v">{{ $bld->address ?? '-' }}, {{ $bld->city ?? '-' }}</td></tr>
      </table>
    </div>

    <div class="card">
      <div class="title">Building / Site</div>
      <table>
        <tr><td class="k">City</td><td class="v">{{ $bld->city ?? '-' }}</td></tr>
        <tr><td class="k">Country</td><td class="v">{{ $bld->country ?? 'Indonesia' }}</td></tr>
        <tr><td class="k">Warranty End</td><td class="v">{{ $bld->warantee_end ?? '-' }}</td></tr>
        <tr><td class="k">Notes</td><td class="v">{{ $bld->notes ?? '-' }}</td></tr>
      </table>
    </div>
  @endif

  <div class="card">
    <div class="title">Notes</div>
    <div class="small">
      This document is a warranty card/certificate generated from the Acegard system.
      Warranty validation is subject to inspection and verification according to the applicable terms and conditions.
    </div>
  </div>

</div>
</body>
</html>
