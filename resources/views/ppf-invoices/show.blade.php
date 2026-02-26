@extends('dashboard.index')

@section('title', 'Detail Invoice PPF')

@section('breadcrumb', 'PPF Invoices')

@section('content')
@php
    $customer = $invoice->customer;
    $ins      = $invoice->installation;
    $branch   = $invoice->branch;
    $product  = $ins->ppfProduct ?? null;

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
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Detail Invoice #{{ $invoice->invoice_number }}</h5>

        <div class="d-flex gap-2">

            {{-- Print/Download --}}
            <a href="{{ route('ppf-invoices.download', $invoice->id) }}" target="_blank"
                class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                title="Print / Download">
                <iconify-icon icon="ic:baseline-download"></iconify-icon>
            </a>

            {{-- Edit --}}
            <a href="{{ route('ppf-invoices.edit', $invoice->id) }}"
                class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                title="Edit Invoice">
                <iconify-icon icon="lucide:edit"></iconify-icon>
            </a>

            {{-- Delete --}}
            <form action="{{ route('ppf-invoices.destroy', $invoice->id) }}" method="POST"
                onsubmit="return confirm('Hapus invoice ini?')" style="display:inline;">
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
        <div class="card mb-4 bg-white dark:bg-gray-800 rounded-md drop-shadow-2xl">
            <div class="card-body px-4 py-4">

                {{-- HEADER --}}
                <table style="width: 100%;" class="mb-4">
                    <tr>
                        <th style="text-align: left;">
                            <img
                                style="width: 15rem; margin-bottom: 1.5rem;"
                                src="{{ asset('assets/images/logo.png') }}"
                                alt="Logo Light"
                                class="hidden dark:block"
                            >
                            <p class="font-weight-bold">
                                {{ $branch->address ?? 'Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Cipondoh, Kota Tangerang, Banten 15146' }}
                                <br>
                                {{ $branch->city ?? 'Tangerang' }}
                            </p>
                        </th>
                        <th style="text-align: right;">
                            <p>
                                Invoice Date: {{ $invoice->invoice_date }}<br>
                                Invoice Number: {{ $invoice->invoice_number }}<br>
                                Card Number: {{ $customer->card_number ?? '-' }}<br>
                                Warranty Code: {{ $ins->warranty_code ?? '-' }}
                            </p>
                        </th>
                    </tr>
                </table>

                <hr class="border-t-2 border-black my-4">

                {{-- TITLE --}}
                <h6 class="text-center font-bold my-2 text-xl"
                    style="margin-bottom: 1rem !important; background-color:black; color:white; padding: 5px 0;">
                    ACEGARD INVOICE - PPF INSTALLATION
                </h6>

                {{-- CUSTOMER PERSONAL DATA --}}
                <h6 class="text-center font-bold text-xl my-4"
                    style="margin-bottom: 1rem !important; background-color:black; color:white; padding: 5px 0;">
                    CUSTOMER PERSONAL DATA
                </h6>
                <table class="table">
                    <tbody style="border-style: none !important;">
                        <tr style="border-style: none !important;">
                            <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">NAME</th>
                            <td style="border-style: none !important;">: {{ $customer->name ?? '-' }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">EMAIL</th>
                            <td style="border-style: none !important;">: {{ $customer->email ?? '-' }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">PHONE NUMBER</th>
                            <td style="border-style: none !important;">: {{ $customer->phone ?? '-' }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">ADDRESS</th>
                            <td style="border-style: none !important;">: {{ $customer->address ?? '-' }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">CITY / COUNTRY</th>
                            <td style="border-style: none !important;">: {{ $customer->city ?? '-' }} / {{ $customer->country ?? 'Indonesia' }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- PRODUCT DESCRIPTION --}}
                <h6 class="bg-gray-100 dark:bg-gray-950 text-center text-gray-800 dark:text-white font-bold text-xl my-4"
                    style="background-color:black; color:white; padding: 5px 0;">
                    PRODUCT DESCRIPTION
                </h6>
                <table class="table">
                    <tbody style="border-style: none !important;">
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; text-align: left; border-style: none !important;">PPF PRODUCT</td>
                            <td style="border-style: none !important;">
                                : {{ $product->name ?? '-' }}
                                @if(!empty($product->brand)) | {{ $product->brand }} @endif
                                @if(!empty($product->series)) | {{ $product->series }} @endif
                                @if(!empty($product->finish)) | {{ strtoupper($product->finish) }} @endif
                                @if(!empty($product->thickness_microns)) | {{ $product->thickness_microns }} microns @endif
                            </td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; text-align: left; border-style: none !important;">INSTALL TYPE</td>
                            <td style="border-style: none !important;">: {{ strtoupper($ins->install_type ?? '-') }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; text-align: left; border-style: none !important;">INSTALLED PANELS</td>
                            <td style="border-style: none !important;">: {{ $panelText }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; text-align: left; border-style: none !important;">INSTALL DATE</td>
                            <td style="border-style: none !important;">: {{ $ins->install_date ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- VEHICLE DESCRIPTION --}}
                <h6 class="bg-gray-100 dark:bg-gray-950 text-center text-gray-800 dark:text-white font-bold text-xl my-4"
                    style="background-color:black; color:white; padding: 5px 0;">
                    VEHICLE DESCRIPTION
                </h6>
                <table class="table">
                    <tbody style="border-style: none !important;">
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; border-style: none !important;">Brand</td>
                            <td style="border-style: none !important;">: {{ $customer->vehicle_brand ?? '-' }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; border-style: none !important;">Model</td>
                            <td style="border-style: none !important;">: {{ $customer->vehicle_model ?? '-' }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; border-style: none !important;">Police Number</td>
                            <td style="border-style: none !important;">: {{ $customer->vehicle_plate ?? '-' }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; border-style: none !important;">Year</td>
                            <td style="border-style: none !important;">: {{ $customer->vehicle_year ?? '-' }}</td>
                        </tr>
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; border-style: none !important;">Color</td>
                            <td style="border-style: none !important;">: {{ $customer->vehicle_color ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- INSTALLATION TEAM --}}
                <h6 class="text-center font-bold text-xl my-4"
                    style="background-color:black; color:white; padding:5px 0;">
                    INSTALLATION TEAM
                </h6>
                <table class="table">
                    <tbody style="border-style:none!important;">
                        <tr style="border-style:none!important;">
                            <td style="width:15rem; border-style:none!important;">Technicians</td>
                            <td style="border-style:none!important;">: {{ $techNames ?: '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- TRANSACTION DETAILS --}}
                <div class="row g-4">
                    {{-- Kolom Transfer --}}
                    <div class="col-md-6 d-flex flex-column justify-content-between">
                        <div>
                            <div class="card mb-3 border-solid border-2 border-black">
                                <div class="card-body border-solid border-2 border-black">
                                    <p class="text-center fw-bold mb-4">Please transfer in IDR</p>
                                    <table class="table table-borderless table-sm mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="text-start">Bank Company</th>
                                                <td>: Bank Central Asia (BCA)</td>
                                            </tr>
                                            <tr>
                                                <th class="text-start">Account Bank Name</th>
                                                <td>: Hendy Tanpratama</td>
                                            </tr>
                                            <tr>
                                                <th class="text-start">Account Number</th>
                                                <td>: 8900140747</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card border-solid border-2 border-black">
                                <div class="card-body border-solid border-2 border-black">
                                    <p class="text-center fw-bold text-uppercase mb-0">
                                        TYPE OF TRANSACTION : {{ $invoice->payment_method }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p>All Transactions are not refundable</p>
                            <h6 class="mt-2">
                                THANK YOU FOR TRUSTING YOUR <br>
                                VEHICLE WITH US
                            </h6>
                        </div>
                    </div>

                    {{-- Kolom Total Price --}}
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="bg-dark text-center text-light fw-bold mb-4">Total Price</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="text-start">Price</th>
                                            <td>: Rp. {{ number_format($invoice->price, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Discount</th>
                                            <td>: Rp. {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Total</th>
                                            <td>: Rp. {{ number_format($invoice->total_price, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr class="my-3">
                                <table class="table table-borderless table-sm mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="text-start">Down Payment</th>
                                            <td>: Rp. {{ number_format($invoice->downpayment, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-start">Remaining Payment</th>
                                            <td>: Rp. {{ number_format($invoice->remaining_payment, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="text-center">
                            <h6 class="bg-dark text-white py-2 fw-bold rounded text-uppercase">
                                STATUS : {{ $invoice->status }}
                            </h6>
                            <p class="text-dark mt-2 mb-1">SINCERELY,</p>
                            <img src="{{ asset('assets/images/blue-logo.png') }}"
                                 class="img-fluid"
                                 style="width: 15rem; transform: rotate(-5deg);"
                                 alt="cap Acegard">
                            <p class="text-dark mt-2">ACEGARD INDONESIA</p>
                        </div>
                    </div>
                </div>

                {{-- TERMS (PPF) --}}
                <div class="my-4">
                    <h6 class="text-center font-bold py-1 my-8"
                        style="background-color:black; color:white;">
                        ACEGARD PPF WARRANTY TERMS AND CONDITIONS
                    </h6>
                </div>

                <h6 class="text-gray-500"><strong>WARRANTY CAN BE CLAIMED IF:</strong></h6>
                <ol class="list-inside list-decimal p-2" style="list-style: auto; list-style-position: inside;">
                    <li class="text-gray-500">Lifting edge / bubbling caused by installation workmanship.</li>
                    <li class="text-gray-500">Adhesive failure / delamination under normal use (based on product coverage).</li>
                    <li class="text-gray-500">Yellowing / cracking within warranty period (if included in coverage).</li>
                    <li class="text-gray-500">Warranty code valid and warranty period still active.</li>
                </ol>

                <h6 class="text-gray-500"><strong>THE WARRANTY CANNOT BE CLAIMED IF:</strong></h6>
                <ol class="list-inside list-decimal p-2" style="list-style: auto; list-style-position: inside;">
                    <li class="text-gray-500">Accident / collision / impact / scratches due to user usage (paid per panel applies).</li>
                    <li class="text-gray-500">Damage caused by repaint/body repair, sanding, aggressive polishing/compound.</li>
                    <li class="text-gray-500">Use of harsh chemicals or improper maintenance.</li>
                    <li class="text-gray-500">Installation not done by authorized installer / warranty expired / status void.</li>
                </ol>

                <p class="text-gray-500">
                    <br />
                    To obtain service and handling, please contact the authorized dealer where you purchased.
                    <br /><br />
                    <strong>Policy:</strong> If the issue is proven to be caused by installation, repair/replacement is free and follows the remaining warranty period (carry-over).
                    If the issue is caused by user/accident, replacement is charged per panel.
                </p>

            </div>
        </div>
    </div>
</div>
@endsection
