@extends('dashboard.index')

@section('title', 'Detail Invoice Building')

@section('breadcrumb', 'Invoices-building')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Detail Invoice #{{ $invoice->invoice_number }}</h5>
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

    <div class="card-body">
        <div class="card mb-4 bg-white dark:bg-gray-800 rounded-md drop-shadow-2xl">
            <div class="card-body px-4 py-4">
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
                                    <p class="text-center fw-bold text-uppercase mb-0">TYPE OF TRANSACTION : {{ $invoice->type }}</p>
                                </div>
                            </div>
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
                        <div class="card mb-3">
                            <div class="card-body ">
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
                            <h6 class="bg-dark text-white py-2 fw-bold rounded text-uppercase">STATUS : {{ $invoice->status }}</h6>
                            <p class="text-dark mt-2 mb-1">SINCERELY,</p>
                            <img src="{{ asset('assets/images/blue-logo.png') }}" class="img-fluid" style="width: 15rem; transform: rotate(-5deg);" alt="cap Acegard">
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
            </div>
        </div>
    </div>
</div>
@endsection
