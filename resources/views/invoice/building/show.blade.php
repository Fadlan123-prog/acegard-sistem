@extends('dashboard.index')

@section('title', 'Detail Invoice Building')

@section('breadcrumb', 'Invoices-building')

@section('content')

{{-- ═══ STEP TRACKER ══════════════════════════════════════════════════════════ --}}
@php
    $s1 = $invoice->isStep1Done();
    $s2 = $invoice->isStep2Done();
    $stockDone = $invoice->isStockDeducted();
@endphp

<x-ui.card class="mb-3">

    {{-- STEP BAR --}}
    <div class="d-flex align-items-center gap-0 mb-4" style="position:relative;">
        <div style="position:absolute;top:20px;left:25%;right:25%;height:3px;
            background:{{ $s2 ? '#16a34a' : '#e2e8f0' }};z-index:0;"></div>

        @foreach([
            ['num'=>1,'done'=>$s1,'label'=>'DP + Kartu Garansi','sub'=>'Invoice dibuat','icon'=>'mdi:file-document-check-outline'],
            ['num'=>2,'done'=>$s2,'label'=>'Pelunasan + Stok','sub'=>$s2 ? ($invoice->pelunasan_at?->isoFormat('D MMM Y, HH:mm') ?? '-') : 'Bayar sisa & potong stok','icon'=>'mdi:package-check'],
        ] as $step)
        <div class="d-flex flex-column align-items-center flex-fill" style="position:relative;z-index:1;">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold mb-2"
                 style="width:40px;height:40px;font-size:1rem;
                 background:{{ $step['done'] ? '#16a34a' : '#e2e8f0' }};
                 color:{{ $step['done'] ? '#fff' : '#94a3b8' }};
                 border:3px solid {{ $step['done'] ? '#16a34a' : '#e2e8f0' }};">
                @if($step['done'])
                    <iconify-icon icon="mdi:check" style="font-size:20px;"></iconify-icon>
                @else
                    {{ $step['num'] }}
                @endif
            </div>
            <div class="text-center">
                <div class="fw-semibold small {{ $step['done'] ? 'text-success' : 'text-muted' }}">
                    {{ $step['label'] }}
                </div>
                <div class="text-muted" style="font-size:.7rem;">{{ $step['sub'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- AKSI STEP 2 --}}
    @if($s1 && !$s2)
    <div class="border rounded-3 p-3" style="background:#f0f9ff;border-color:#bae6fd!important;">
        <div class="fw-semibold mb-2" style="color:#0369a1;">
            <iconify-icon icon="mdi:package-check" class="me-1"></iconify-icon>Catat Pelunasan & Potong Stok
        </div>
        <div class="text-muted small mb-3">
            Sisa tagihan:
            <strong class="text-danger">Rp{{ number_format($invoice->remaining_payment, 0, ',', '.') }}</strong>
            &nbsp;·&nbsp; Waste stok default: <strong>{{ $invoice->waste_percent }}%</strong>
        </div>
        <form method="POST" action="{{ route('invoice.building.pelunasan', $invoice->id) }}"
              onsubmit="return confirm('Konfirmasi pelunasan invoice ini?\nStok bahan akan dikurangi sekaligus.')">
            @csrf
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small mb-1">Metode Bayar</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Sama seperti sebelumnya</option>
                        @foreach(['cash'=>'Cash','transfer'=>'Transfer','credit'=>'Kredit'] as $v=>$l)
                            <option value="{{ $v }}" {{ $invoice->type === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small mb-1">Waste Stok (%)</label>
                    <input type="number" name="waste_percent" class="form-control form-control-sm"
                           value="{{ $invoice->waste_percent }}" min="0" max="100" step="0.5">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <x-ui.button type="submit" variant="primary" size="sm" class="w-100">
                        <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Tandai Lunas + Potong Stok
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
    @elseif($s2)
    <div class="d-flex gap-3 flex-wrap">
        <div class="border rounded-3 p-3" style="background:#f0fdf4;border-color:#bbf7d0!important;min-width:200px;">
            <div class="fw-semibold text-success small">
                <iconify-icon icon="mdi:check-circle" class="me-1"></iconify-icon>Pelunasan Tercatat
            </div>
            <div class="text-muted" style="font-size:.75rem;">
                {{ $invoice->pelunasan_at?->isoFormat('D MMM Y, HH:mm') ?? '-' }}
            </div>
        </div>
        <div class="border rounded-3 p-3" style="background:{{ $stockDone ? '#f0fdf4' : '#fef9c3' }};border-color:{{ $stockDone ? '#bbf7d0' : '#fde047' }}!important;min-width:200px;">
            <div class="fw-semibold small {{ $stockDone ? 'text-success' : '' }}" style="{{ !$stockDone ? 'color:#a16207;' : '' }}">
                <iconify-icon icon="{{ $stockDone ? 'mdi:check-circle' : 'mdi:alert-circle-outline' }}" class="me-1"></iconify-icon>
                Stok {{ $stockDone ? 'Terpotong' : 'Belum Terpotong' }}
            </div>
            @if($stockDone)
            <div class="text-muted" style="font-size:.75rem;">
                {{ $invoice->stock_deducted_at?->isoFormat('D MMM Y, HH:mm') ?? '-' }}
            </div>
            @endif
        </div>
    </div>
    @endif

</x-ui.card>
{{-- ═══ END STEP TRACKER ═════════════════════════════════════════════════════ --}}

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Detail Invoice #{{ $invoice->invoice_number }}</div>
            <div class="d-flex gap-2">

                {{-- Download PDF --}}
                <a href="{{ route('invoice.building.download', $invoice->id) }}"
                    class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                    title="Download PDF">
                    <iconify-icon icon="ic:baseline-download"></iconify-icon>
                </a>

                {{-- Edit --}}
                <a href="{{ route('invoice.building.edit', $invoice->id) }}"
                    class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                    title="Edit Invoice">
                    <iconify-icon icon="lucide:edit"></iconify-icon>
                </a>

                {{-- Delete --}}
                <form action="{{ route('invoice.building.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Hapus invoice ini?')" style="display: inline;">
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
    @endslot

    <x-ui.card class="mb-4">
        <table style="width: 100%;" class="mb-4">
            <tr>
                <th style="text-align: left;">
                    <img
                        style="width: 15rem; margin-bottom: 1.5rem;"
                        src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}"
                        alt="Logo Light"
                        class="hidden dark:block"
                    >
                    <p class="font-weight-bold">
                        Jl. KH. Hasyim Ashari, RT.007/RW.002,<br> Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146
                    </p>
                </th>
                <th style="text-align: right;">
                    <p>
                        Invoice Date: {{ $invoice->invoice_date }}<br>
                        Invoice Number: {{ $invoice->invoice_number }}<br>
                        Card Number: {{ $customer->card_number }}
                    </p>
                </th>
            </tr>
        </table>

        <hr class="border-t-2 border-black my-4">

        <!-- Customer Data Section -->
        <h6 class="text-center font-bold my-2 text-xl" style="margin-bottom: 1rem !important; background-color:black; color:white; padding: 5px 0;">
            ACEGARD INVOICE
        </h6>
        <h6 class="text-center font-bold text-xl my-4" style="margin-bottom: 1rem !important; background-color:black; color:white; padding: 5px 0;">
            CUSTOMER PERSONAL DATA
        </h6>
        <table class="table">
            <tbody style="border-style: none !important;">
                <tr style="border-style: none !important;">
                    <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">NAME</th>
                    <td style="border-style: none !important;">: {{ $customer->name }}</td>
                </tr>
                <tr style="border-style: none !important;">
                    <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">EMAIl</th>
                    <td style="border-style: none !important;">: {{ $customer->email }}</td>
                </tr>
                <tr style="border-style: none !important;">
                    <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">PHONE NUMBER</th>
                    <td style="border-style: none !important;">: {{ $customer->phone_number }}</td>
                </tr>
                <tr style="border-style: none !important;">
                    <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">ADDRESS</th>
                    <td style="border-style: none !important;">: {{ $customer->address }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Product Description Section -->
        <h6 class="bg-gray-100 dark:bg-gray-950 text-center text-gray-800 dark:text-white font-bold text-xl my-4" style="background-color:black; color:white; padding: 5px 0;">
           PRODUCT DESCRIPTION
        </h6>
        <table class="table">
            <tbody style="border-style: none !important;">
                @foreach ($customer->products as $product)
                    <tr style="border-style: none !important;">
                        <td style="width: 15rem; text-align: left; border-style: none !important;">{{ $product->category->name }}</td>
                        <td style="border-style: none !important;">: {{ $product->meters }} | {{ $product->product->name }} | {{ $product->product->description }} | {{ \Carbon\Carbon::parse($product->warantee_start)->format('Y-m-d')}} s/d {{ \Carbon\Carbon::parse($product->warantee_end)->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Tech Description Section -->
        <h6 class="bg-gray-100 dark:bg-gray-950 text-center text-gray-800 dark:text-white font-bold text-xl my-4" style="background-color:black; color:white; padding: 5px 0;">
           TECH DESCRIPTION
        </h6>
        <table class="table">
            <tbody style="border-style: none !important;">
                <tr style="border-style: none !important;">
                    <th scope="row" style="width: 15rem; text-align:left; border-style: none !important;">APPLICATOR</th>
                    <td style="border-style: none !important;">: {{ $customer->applicator }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Vehicle Description Section -->
        <h6 class="bg-gray-100 dark:bg-gray-950 text-center text-gray-800 dark:text-white font-bold text-xl my-4" style="background-color:black; color:white; padding: 5px 0;">
            VALIDATE WARRANTY CARD
        </h6>
        <table class="table">
            <tbody style="border-style: none !important;">
                <tr style="border-style: none !important;">
                    <td style="width: 15rem; border-style: none !important;">INSTALATION DATE</td>
                    <td style="border-style: none !important;">: {{ $customer->products->first()->warantee_start }}</td>
                </tr>
                <tr style="border-style: none !important;">
                    <td style="width: 15rem; border-style: none !important;">VALID UNTIL</td>
                    <td style="border-style: none !important;">: {{ $customer->products->first()->warantee_end }}</td>
                </tr>

            </tbody>
        </table>

        <!-- Transaction Details Section -->
        <div class="row g-4">
            <!-- Kolom Transfer -->
            <div class="col-md-6 d-flex flex-column justify-content-between">
                <!-- Bagian atas -->
                <div>
                    <x-ui.card class="mb-3 border-solid border-2 border-black">
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
                    </x-ui.card>

                    <x-ui.card class="border-solid border-2 border-black">
                        <p class="text-center fw-bold text-uppercase mb-0">TYPE OF TRANSACTION : {{ $invoice->type }}</p>
                    </x-ui.card>
                </div>

                <!-- Bagian bawah -->
                <div class="mt-4">
                    <p>All Transactions are not refundable</p>
                    <h6 class="mt-2">
                        THANK YOU FOR TRUSTING YOUR <br>
                        VEHICLE WITH US
                    </h6>
                </div>
            </div>

            <!-- Kolom Total Price -->
            <div class="col-md-6">
                <x-ui.card class="mb-3">
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
                                <td>: Rp. {{ number_format($invoice->down_payment, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Remaining Payment</th>
                                <td>: Rp. {{ number_format($invoice->remaining_payment, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </x-ui.card>

                <div class="text-center">
                    <h6 class="bg-dark text-white py-2 fw-bold rounded text-uppercase">STATUS : {{ $invoice->status }}</h6>
                    <p class="text-dark mt-2 mb-1">SINCERELY,</p>
                    <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" class="img-fluid" style="width: 15rem; transform: rotate(-5deg);" alt="cap Acegard">
                    <p class="text-dark mt-2">ACEGARD INDONESIA</p>
                </div>
            </div>
        </div>

        <div class="my-4">
            <h6 class="bg-gray-100 dark:bg-gray-950 text-center text-gray-800 dark:text-white font-bold py-1 my-8" style="background-color:black; color:white; ">
                ACEGARD NANO CERAMIC FILM WARRANTY TERMS AND CONDITIONS
            </h6>
        </div>
        <h6 class="text-gray-500"><strong>WARRANTY CAN BE CLAIMED IF:</strong></h6>
        <ol class="list-inside list-decimal p-2" style="list-style: auto; list-style-position: inside;">
            <li class="text-gray-500">Window film interferes with signals in the car cabin.</li>
            <li class="text-gray-500">Bubbled window film.</li>
            <li class="text-gray-500">The window film is peeled or not sticking to the car window.</li>
            <li class="text-gray-500">Window film changes color.</li>
        </ol>
        <h6 class="text-gray-500"><strong>THE WARRANTY CANNOT BE CLAIMED IF:</strong></h6>
        <ol class="list-inside list-decimal p-2" style="list-style: auto; list-style-position: inside;">
            <li class="text-gray-500">Transferred from the first buyer.</li>
            <li class="text-gray-500">Damage that occors due to user error or negligence, whether unintentional or deliberate, or due to natural disaster.</li>
            <li class="text-gray-500">Installation is carried out in layers with other brands. and if a layered installation is carried out with Acegard products at the customer's request, the warranty only applies to the first layer.</li>
            <li class="text-gray-500">Installation is not carried out by an ACEGARD NANO CERAMIC AUTHORIZED DEALER.</li>
            <li class="text-gray-500">Registration was not carried out according to customer data.</li>
        </ol>
        <p class="text-gray-500"><br />To obtain service and handling, please contact the authorized dealer where you purchased.<br /><br />Customer service E-mail :&nbsp;acegardindonesia@gmail.com<br />Jl. Tanjung Barat No.2B, Lenteng Agung, Jagakarsa, Jakarta Selatan - 12530. Indonesia<br /><br /><strong>Important Notice : Window Film Maintenance</strong><br />After installing the window film, a haze-like appearance may occur, this is temporary and is caused by the humidity of the water used when applying the window film. Acegard Nano Ceramic Film can be cleaned with certain liquids that are not acidic and can be wiped using a microfiber cloth after 4 weeks of installation. do not operate the window until the installed window film is completely dry. Abrasive type cleaning agents, bristle brushes, which can scratch the window film should not be used.<br /><br /><strong>Disclaimer :</strong>&nbsp;All Acegard Nano Ceramic Film users are expected to comply with applicable laws and regulations in Indonesia. All official Acegard dealers are not responsible for violations of user non-compliance with Indonesian traffic laws and regulations.</p>
    </x-ui.card>
</x-ui.card>
@endsection
