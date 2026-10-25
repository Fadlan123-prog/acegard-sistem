<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            margin: 0.3cm 0.2cm;
            size: A4;
            padding: 10px;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        table, tr, td {
            page-break-inside: avoid;
        }

        tr, th {
            vertical-align: top;
        }

        hr {
            border-top: 2px solid black;
            margin: 0;
        }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .section-title {
            background-color: #000000 !important;
            color: white;
            text-align: center;
            font-weight: bold;
            margin: 0.6rem 0;
            padding: 0.3rem;
            font-size: 1.05rem;
        }

        .boxed {
            border: 1px solid #000;
            padding: 0.75rem 1rem;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-weight: bold;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge-dark {
            background: #000;
            color: #fff;
        }
    </style>
</head>

<body>
@php
    $customer = $invoice->customer;
    $ins      = $invoice->installation;
    $branch   = $invoice->branch;
    $product  = $ins->ppfProduct ?? null;

    // mapping panels
    $panelLabels = [
      'hood' => 'Kap Mesin',
      'front_bumper' => 'Bumper Depan',
      'rear_bumper' => 'Bumper Belakang',
      'fender_left' => 'Fender Kiri',
      'fender_right' => 'Fender Kanan',
      'door_fl' => 'Pintu Depan Kiri',
      'door_fr' => 'Pintu Depan Kanan',
      'door_rl' => 'Pintu Belakang Kiri',
      'door_rr' => 'Pintu Belakang Kanan',
      'mirror_left' => 'Spion Kiri',
      'mirror_right' => 'Spion Kanan',
      'roof' => 'Atap',
      'trunk' => 'Bagasi',
      'side_skirt_left' => 'Side Skirt Kiri',
      'side_skirt_right' => 'Side Skirt Kanan',
    ];

    $panels = is_array($ins->panels ?? null) ? $ins->panels : [];
    $panelText = $panels
      ? collect($panels)->map(fn($k) => $panelLabels[$k] ?? $k)->implode(', ')
      : '-';

    $techNames = $ins && method_exists($ins,'technicians')
      ? ($ins->technicians ?? collect())->pluck('name')->implode(', ')
      : '-';

    $idr = fn($n) => 'Rp. ' . number_format((int)($n ?? 0), 0, ',', '.');

    $today = now()->toDateString();
    $isWarrantyActive = $ins
        && ($ins->warranty_status ?? 'active') === 'active'
        && !empty($ins->warranty_end)
        && $ins->warranty_end >= $today;
@endphp

<div style="padding: 1.5rem;">

    {{-- HEADER --}}
    <table style="width: 100%;">
        <tr>
            <th style="text-align: left;">
                <img style="width: 9.5rem;" src="{{ public_path('assets/frontend/images/new-acegard-logo.webp') }}" alt="Logo">
                <p style="font-weight: bold; margin-top: 0.75rem;">
                    {{ $branch->address ?? 'Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146' }}
                    <br>
                    {{ $branch->city ?? 'Tangerang' }}
                </p>
            </th>
            <th style="text-align: right;">
                <p style="margin:0;">
                    Invoice Date: {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') : '-' }}<br>
                    Invoice Number: {{ $invoice->invoice_number }}<br>
                    Card Number: {{ $customer->card_number ?? '-' }}<br>
                    {{-- Warranty Code: {{ $ins->warranty_code ?? '-' }} --}}
                </p>
            </th>
        </tr>
    </table>

    <hr style="border: 1px solid black; margin: 0.6rem 0 0;">

    <div class="section-title">ACEGARD INVOICE - PPF INSTALLATION</div>

    {{-- CUSTOMER DATA --}}
    <div class="section-title">CUSTOMER PERSONAL DATA</div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
        <tbody>
            <tr>
                <th style="width: 15rem; text-align: left;">NAME</th>
                <td>: {{ $customer->name ?? '-' }}</td>
            </tr>
            <tr>
                <th style="text-align: left;">EMAIL</th>
                <td>: {{ $customer->email ?? '-' }}</td>
            </tr>
            <tr>
                <th style="text-align: left;">PHONE NUMBER</th>
                <td>: {{ $customer->phone ?? '-' }}</td>
            </tr>
            <tr>
                <th style="text-align: left;">ADDRESS</th>
                <td>: {{ $customer->address ?? '-' }}</td>
            </tr>
            <tr>
                <th style="text-align: left;">CITY / COUNTRY</th>
                <td>: {{ $customer->city ?? '-' }} / {{ $customer->country ?? 'Indonesia' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- PRODUCT DESCRIPTION --}}
    <div class="section-title" style="margin-top: 1rem;">PRODUCT DESCRIPTION</div>

    <table style="width: 100%;">
        <tbody>
            <tr>
                <td style="width: 17rem; text-align: left;">PPF PRODUCT</td>
                <td>
                    : {{ $product->name ?? '-' }}
                    @if(!empty($product->brand)) | {{ $product->brand }} @endif

            </tr>
            <tr>
                <td style="text-align: left;">INSTALL TYPE</td>
                <td>: {{ strtoupper($ins->install_type ?? '-') }}</td>
            </tr>
            {{-- <tr>
                <td style="text-align: left;">INSTALLED PANELS</td>
                <td>: {{ $panelText }}</td>
            </tr> --}}
            <tr>
                <td style="text-align: left;">INSTALL DATE</td>
                <td>: {{ $ins->install_date ? \Carbon\Carbon::parse($ins->warranty_start)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: left;">TECHNICIANS</td>
                <td>: {{ $techNames ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- VEHICLE DESCRIPTION --}}
    <div class="section-title" style="margin-top: 1rem;">VEHICLE DESCRIPTION</div>

    <table style="width: 100%;">
        <tbody>
            <tr>
                <td style="width: 9.5rem;">Brand</td>
                <td>: {{ $customer->vehicle_brand ?? '-' }}</td>
            </tr>
            <tr>
                <td>Model</td>
                <td>: {{ $customer->vehicle_model ?? '-' }}</td>
            </tr>
            <tr>
                <td>Police Number</td>
                <td>: {{ $customer->vehicle_plate ?? '-' }}</td>
            </tr>
            <tr>
                <td>Year</td>
                <td>: {{ $customer->vehicle_year ?? '-' }}</td>
            </tr>
            <tr>
                <td>Color</td>
                <td>: {{ $customer->vehicle_color ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- PAYMENT + TOTAL --}}
    <table style="width: 100%; table-layout: fixed; margin-top: 1rem;">
        <tr>
            <td style="vertical-align: top; width: 50%; padding-right: 1rem;">
                <div class="boxed">
                    <p style="text-align: center; font-weight: bold; margin: 0 0 0.75rem;">Please transfer in IDR</p>
                    <table style="width: 100%;">
                        <tbody>
                            <tr>
                                <th style="text-align: left;">Bank Company</th>
                                <td>: Bank Central Asia (BCA)</td>
                            </tr>
                            <tr>
                                <th style="text-align: left;">Account Bank Name</th>
                                <td>: Hendy Tanpratama</td>
                            </tr>
                            <tr>
                                <th style="text-align: left;">Account Number</th>
                                <td>: 8900140747</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="boxed" style="margin-top: 0.75rem; text-align:center; font-weight:bold; text-transform:uppercase;">
                    TYPE OF TRANSACTION : {{ $invoice->payment_method }}
                </div>

                <div style="margin-top: 0.75rem;">
                    <p style="margin:0;">All Transactions are not refundable</p>
                    <p style="font-weight: bold; margin:0.35rem 0 0;">
                        THANK YOU FOR TRUSTING YOUR <br> VEHICLE WITH US
                    </p>
                </div>
            </td>

            <td style="vertical-align: top; width: 50%; padding-left: 1rem;">
                <h5 style="text-align: center; font-weight: bold; background-color: black; color: white; padding: 0.5rem; text-transform:uppercase; margin:0;">
                    Total Price
                </h5>

                <table style="width: 100%; margin-top:0.5rem;">
                    <tbody>
                        <tr>
                            <th style="text-align: left;">Price</th>
                            <td>: {{ $idr($invoice->price) }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">Discount</th>
                            <td>: {{ $idr($invoice->discount) }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">Total</th>
                            <td>: {{ $idr($invoice->total_price) }}</td>
                        </tr>
                    </tbody>
                </table>

                <hr style="border-top: 2px solid black; margin: 0.75rem 0;">

                <table style="width: 100%;">
                    <tbody>
                        <tr>
                            <th style="text-align: left;">Down Payment</th>
                            <td>: {{ $idr($invoice->downpayment) }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left;">Remaining Payment</th>
                            <td>: {{ $idr($invoice->remaining_payment) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div style="text-align: center; font-weight: bold; background-color: black; color: white; margin-top: 0.75rem; padding: 0.5rem; text-transform:uppercase;">
                    STATUS : {{ $invoice->status }}
                </div>

                <div style="text-align:center; margin-top: 0.75rem;">
                    <span class="badge badge-dark">
                        WARRANTY: {{ $isWarrantyActive ? 'ACTIVE' : 'EXPIRED/INACTIVE' }}
                    </span>
                    <div style="margin-top:0.3rem; font-size: 13px;">
                        Warranty Period:
                        {{ $ins->warranty_start ? \Carbon\Carbon::parse($ins->warranty_start)->format('d/m/Y') : '-' }}
                        s/d
                        {{ $ins->warranty_end ? \Carbon\Carbon::parse($ins->warranty_end)->format('d/m/Y') : '-' }}
                        ({{ $ins->warranty_years ?? '-' }} tahun)
                    </div>
                </div>

                <div style="text-align: center; margin-top: 1rem;">
                    <p style="margin: 0;">SINCERELY,</p>
                    <img src="{{ public_path('assets/frontend/images/new-acegard-logo.webp') }}" style="width: 9.5rem; transform: rotate(-5deg); margin: 0.75rem auto;" alt="cap Acegard">
                    <p style="margin: 0;">ACEGARD INDONESIA</p>
                </div>
            </td>
        </tr>
    </table>

    {{-- TERMS --}}
    <div style="margin-top: 1rem;">
        <h4 class="text-center font-bold" style="background-color:black; color:white; padding: 6px 0; margin: 0.75rem 0;">
            ACEGARD PPF WARRANTY TERMS AND CONDITIONS
        </h4>

        <h5 class="muted" style="margin:0.5rem 0;"><strong>WARRANTY CAN BE CLAIMED IF:</strong></h5>
        <ol class="muted" style="list-style: auto; list-style-position: inside; margin:0; padding:0;">
            <li>Lifting edge / bubbling caused by installation workmanship.</li>
            <li>Adhesive failure / delamination under normal use (based on product coverage).</li>
            <li>Yellowing / cracking within warranty period (if included in coverage).</li>
            <li>Warranty code valid and within warranty period.</li>
        </ol>

        <h5 class="muted" style="margin:0.75rem 0 0.5rem;"><strong>THE WARRANTY CANNOT BE CLAIMED IF:</strong></h5>
        <ol class="muted" style="list-style: auto; list-style-position: inside; margin:0; padding:0;">
            <li>Accident / collision / impact / scratches due to user usage (paid replacement per panel).</li>
            <li>Damage caused by repaint/body repair, sanding, aggressive polishing/compound.</li>
            <li>Use of harsh chemicals or improper maintenance.</li>
            <li>Warranty expired or warranty status is void.</li>
        </ol>

        <p class="muted" style="margin-top:0.75rem;">
            <strong>Important Notice: PPF Maintenance</strong><br>
            Avoid abrasive cleaners/brushes. Use microfiber cloth and pH-neutral shampoo.
            Avoid high-pressure wash directly on edges in the first days after installation.
            <br><br>
            <strong>Policy:</strong> If the issue is proven to be caused by installation, repair/replacement is free and follows the remaining warranty period (carry-over).
            If the issue is caused by user/accident, replacement is charged per panel.
        </p>
    </div>

</div>
</body>
</html>
