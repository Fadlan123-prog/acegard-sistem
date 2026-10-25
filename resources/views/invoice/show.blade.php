@extends('dashboard.index')

@section('title', 'Detail Invoice')

@section('breadcrumb', 'Invoices')

@section('content')

{{-- ═══ STEP TRACKER ══════════════════════════════════════════════════════════ --}}
@php
    $s1 = $invoice->isStep1Done();
    $s2 = $invoice->isStep2Done();
    $s3 = $invoice->isStep3Done();
    $stockDone = $invoice->isStockDeducted();
    $commissionPreviewData = $s2 && !$s3 ? app(\App\Services\CommissionReleaseService::class)->preview($invoice) : [];
    $commissionPreview     = $commissionPreviewData['rows'] ?? [];
    $isSecondCar           = $commissionPreviewData['is_second_car'] ?? false;
    $isThirdCar            = $commissionPreviewData['is_third_car']  ?? false;
@endphp

<x-ui.card class="mb-3">
    <div class="py-1">

        {{-- STEP BAR --}}
        <div class="d-flex align-items-center gap-0 mb-4" style="position:relative;">
            {{-- Line behind steps --}}
            <div style="position:absolute;top:20px;left:calc(16.67%);right:calc(16.67%);height:3px;
                background:linear-gradient(to right,
                    {{ $s2 ? '#16a34a' : '#e2e8f0' }} 50%,
                    {{ $s3 ? '#16a34a' : '#e2e8f0' }} 50%);
                z-index:0;"></div>

            @foreach([
                ['num'=>1,'done'=>$s1,'label'=>'DP + Kartu Garansi','sub'=>'Invoice & warranty card dibuat','icon'=>'mdi:file-document-check-outline'],
                ['num'=>2,'done'=>$s2,'label'=>'Pelunasan + Stok','sub'=>$s2 ? ($invoice->pelunasan_at?->isoFormat('D MMM Y') ?? '-') : 'Bayar sisa & potong stok','icon'=>'mdi:cash-check'],
                ['num'=>3,'done'=>$s3,'label'=>'Komisi Dirilis','sub'=>$s3 ? $invoice->commission_released_at?->isoFormat('D MMM Y, HH:mm') : 'Komisi ke teknisi & marketing','icon'=>'mdi:account-cash'],
            ] as $step)
            <div class="d-flex flex-column align-items-center flex-fill" style="position:relative;z-index:1;">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold mb-2"
                     style="width:40px;height:40px;font-size:1rem;
                     background:{{ $step['done'] ? '#16a34a' : ($step['num'] == $invoice->currentStep()+1 ? '#1d4ed8' : '#e2e8f0') }};
                     color:{{ $step['done'] || $step['num'] == $invoice->currentStep()+1 ? '#fff' : '#94a3b8' }};
                     border:3px solid {{ $step['done'] ? '#16a34a' : '#e2e8f0' }};">
                    @if($step['done'])
                        <iconify-icon icon="mdi:check" style="font-size:20px;"></iconify-icon>
                    @else
                        {{ $step['num'] }}
                    @endif
                </div>
                <div class="text-center">
                    <div class="fw-semibold small {{ $step['done'] ? 'text-success' : ($step['num'] == $invoice->currentStep()+1 ? 'text-primary' : 'text-muted') }}">
                        {{ $step['label'] }}
                    </div>
                    <div class="text-muted" style="font-size:.7rem;">{{ $step['sub'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- AKSI STEP --}}
        <div class="d-flex gap-3 flex-wrap">

            {{-- STEP 2: Pelunasan --}}
            @if($s1 && !$s2 && $invoice->isQcBlocking())
            <div class="border rounded-3 p-3 flex-fill" style="background:{{ $invoice->isQcFailed() ? '#fef2f2' : '#fffbeb' }};border-color:{{ $invoice->isQcFailed() ? '#fecaca' : '#fde68a' }}!important;">
                <div class="fw-semibold mb-2" style="color:{{ $invoice->isQcFailed() ? '#b91c1c' : '#92400e' }};">
                    <iconify-icon icon="{{ $invoice->isQcFailed() ? 'mdi:close-circle-outline' : 'mdi:clock-outline' }}" class="me-1"></iconify-icon>
                    {{ $invoice->isQcFailed() ? 'QC Ditolak' : 'Menunggu Pemeriksaan QC' }}
                </div>
                <div class="text-muted small">
                    @if($invoice->isQcFailed())
                        Pekerjaan dikembalikan ke teknisi untuk diperbaiki.
                        @if($invoice->qc_notes)
                            <br><strong>Catatan:</strong> {{ $invoice->qc_notes }}
                        @endif
                    @else
                        Pelunasan tidak dapat dicatat sampai supervisor menyetujui QC di halaman
                        <a href="{{ route('work-orders.index', ['work_status' => 'pending_qc']) }}">Penugasan Pekerjaan</a>.
                    @endif
                </div>
            </div>
            @elseif($s1 && !$s2)
            <div class="border rounded-3 p-3 flex-fill" style="background:#f0f9ff;border-color:#bae6fd!important;">
                <div class="fw-semibold mb-2" style="color:#0369a1;">
                    <iconify-icon icon="mdi:cash-check" class="me-1"></iconify-icon>Catat Pelunasan
                </div>
                <div class="text-muted small mb-3">
                    Sisa tagihan:
                    <strong class="text-danger">Rp{{ number_format($invoice->remaining_payment, 0, ',', '.') }}</strong>
                </div>
                <form method="POST" action="{{ route('invoice.pelunasan', $invoice->id) }}"
                      onsubmit="return confirm('Konfirmasi pelunasan Rp{{ number_format($invoice->remaining_payment, 0, ',', ".") }}?')">
                    @csrf
                    <div class="d-flex gap-2 align-items-end">
                        <div class="flex-fill">
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="">Metode bayar...</option>
                                @foreach(['cash'=>'Cash','transfer'=>'Transfer','credit'=>'Kredit','tokopedia'=>'Tokopedia'] as $v=>$l)
                                    <option value="{{ $v }}" {{ $invoice->payment_method === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-ui.button type="submit" variant="primary" size="sm" class="text-nowrap">
                            <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Tandai Lunas
                        </x-ui.button>
                    </div>
                </form>
            </div>
            @elseif($s2)
            <div class="d-flex gap-2 flex-wrap">
                <div class="border rounded-3 p-3" style="background:#f0fdf4;border-color:#bbf7d0!important;min-width:180px;">
                    <div class="fw-semibold text-success small">
                        <iconify-icon icon="mdi:check-circle" class="me-1"></iconify-icon>Pelunasan Tercatat
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">
                        {{ $invoice->pelunasan_at?->isoFormat('D MMM Y, HH:mm') ?? '-' }}
                    </div>
                </div>
                <div class="border rounded-3 p-3"
                     style="background:{{ $stockDone ? '#f0fdf4' : '#fef9c3' }};border-color:{{ $stockDone ? '#bbf7d0' : '#fde047' }}!important;min-width:180px;">
                    <div class="fw-semibold small {{ $stockDone ? 'text-success' : '' }}"
                         style="{{ !$stockDone ? 'color:#a16207;' : '' }}">
                        <iconify-icon icon="{{ $stockDone ? 'mdi:check-circle' : 'mdi:alert-circle-outline' }}" class="me-1"></iconify-icon>
                        Stok {{ $stockDone ? 'Terpotong' : 'Belum Terpotong' }}
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">
                        {{ $stockDone ? $invoice->stock_deducted_at?->isoFormat('D MMM Y, HH:mm') : 'Dipotong saat pelunasan' }}
                    </div>
                </div>
            </div>
            @endif

            {{-- STEP 3: Rilis Komisi --}}
            @if($s2 && !$s3)
            <div class="border rounded-3 p-3 flex-fill" style="background:#fdf4ff;border-color:#e9d5ff!important;">
                <div class="fw-semibold mb-2" style="color:#7c3aed;">
                    <iconify-icon icon="mdi:account-cash" class="me-1"></iconify-icon>Rilis Komisi
                </div>

                {{-- Badge urutan mobil hari ini --}}
                @if($isThirdCar)
                <x-ui.alert type="danger" class="mb-2 d-flex gap-2 align-items-center py-2" style="font-size:.8rem;">
                    <iconify-icon icon="mdi:car-multiple" style="font-size:16px;flex-shrink:0;"></iconify-icon>
                    <span><strong>Mobil ke-3</strong> hari ini — rate otomatis naik ke <strong>Fullset Ekstra Plus</strong></span>
                </x-ui.alert>
                @elseif($isSecondCar)
                <x-ui.alert type="warning" class="mb-2 d-flex gap-2 align-items-center py-2" style="font-size:.8rem;">
                    <iconify-icon icon="mdi:car-multiple" style="font-size:16px;flex-shrink:0;"></iconify-icon>
                    <span><strong>Mobil ke-2</strong> hari ini — rate otomatis naik ke <strong>Fullset Ekstra</strong></span>
                </x-ui.alert>
                @endif

                @if(count($commissionPreview))
                <div class="mb-3">
                    @foreach($commissionPreview as $cp)
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom small">
                        <div>
                            <span class="badge me-1" style="font-size:.65rem;
                                background:{{ $cp['role']==='installer' ? '#eff6ff' : ($cp['role']==='marketing' ? '#fdf4ff' : '#fff7ed') }};
                                color:{{ $cp['role']==='installer' ? '#1d4ed8' : ($cp['role']==='marketing' ? '#7c3aed' : '#c2410c') }};">
                                {{ ucfirst(str_replace('_',' ',$cp['role'])) }}
                            </span>
                            {{ $cp['employee_name'] }}
                            @if(!empty($cp['note']))
                            <div class="text-muted" style="font-size:.65rem;padding-left:4px;">{{ $cp['note'] }}</div>
                            @endif
                        </div>
                        <span class="fw-semibold">Rp{{ number_format($cp['amount'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-muted small mb-3">Tidak ada komisi yang akan dirilis.</div>
                @endif

                <form method="POST" action="{{ route('invoice.release-commission', $invoice->id) }}"
                      onsubmit="return confirm('Rilis komisi untuk invoice ini?\nTindakan ini tidak dapat dibatalkan.')">
                    @csrf

                    {{-- Checkbox Lebaran --}}
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="lebaran" value="1" id="chkLebaran"
                               onchange="updateLebaranPreview(this.checked)">
                        <label class="form-check-label small fw-semibold" for="chkLebaran" style="color:#c2410c;">
                            <iconify-icon icon="mdi:star" class="me-1" style="color:#f59e0b;"></iconify-icon>
                            Bonus Lebaran (2× rate)
                        </label>
                    </div>

                    <x-ui.button type="submit" size="sm" class="w-100" style="background:#7c3aed;color:#fff;">
                        <iconify-icon icon="mdi:send-check" class="me-1"></iconify-icon>Rilis Komisi Sekarang
                    </x-ui.button>
                </form>
            </div>
            @elseif($s3)
            <div class="border rounded-3 p-3" style="background:#f0fdf4;border-color:#bbf7d0!important;min-width:200px;">
                <div class="fw-semibold text-success small">
                    <iconify-icon icon="mdi:check-circle" class="me-1"></iconify-icon>Komisi Telah Dirilis
                </div>
                <div class="text-muted" style="font-size:.75rem;">
                    {{ $invoice->commission_released_at?->isoFormat('D MMM Y, HH:mm') ?? '-' }}
                </div>
                @if(auth()->user()->hasRole('superadmin'))
                <form method="POST" action="{{ route('invoice.rerelease-commission', $invoice->id) }}"
                      class="mt-2"
                      onsubmit="return confirm('Rilis ULANG komisi?\nKomisi lama akan dihapus dan dihitung ulang.\nGunakan hanya jika komisi sebelumnya salah.')">
                    @csrf
                    <x-ui.button type="submit" variant="outline" size="sm" class="w-100" style="font-size:.72rem;">
                        <iconify-icon icon="mdi:refresh" class="me-1"></iconify-icon>Rilis Ulang (Koreksi)
                    </x-ui.button>
                </form>
                @endif
            </div>
            @endif

        </div>
    </div>
</x-ui.card>
{{-- ═══ END STEP TRACKER ═════════════════════════════════════════════════════ --}}

{{-- ═══ TEKNISI & KENEK BAR ═══════════════════════════════════════════════════ --}}
<x-ui.card class="mb-3">
    <div class="d-flex flex-wrap gap-4 align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="d-flex align-items-center justify-content-center rounded-circle"
                  style="width:34px;height:34px;background:#eff6ff;flex-shrink:0;">
                <iconify-icon icon="mdi:account-hard-hat" style="color:#1d4ed8;font-size:18px;"></iconify-icon>
            </span>
            <div>
                <div style="font-size:.7rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Teknisi</div>
                <div class="fw-semibold" style="font-size:.9rem;">
                    {{ $customer->tukang?->name ?? '<span class="text-muted fst-italic" style="font-size:.8rem;">Belum ditentukan</span>' }}
                </div>
            </div>
        </div>
        <div style="width:1px;height:36px;background:#e5e7eb;"></div>
        <div class="d-flex align-items-center gap-2">
            <span class="d-flex align-items-center justify-content-center rounded-circle"
                  style="width:34px;height:34px;background:#f0fdf4;flex-shrink:0;">
                <iconify-icon icon="mdi:account-wrench" style="color:#16a34a;font-size:18px;"></iconify-icon>
            </span>
            <div>
                <div style="font-size:.7rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Kenek</div>
                <div class="fw-semibold" style="font-size:.9rem;">
                    {{ $customer->kenek?->name ?? '<span class="text-muted fst-italic" style="font-size:.8rem;">—</span>' }}
                </div>
            </div>
        </div>
        @if($customer->install_date)
        <div style="width:1px;height:36px;background:#e5e7eb;"></div>
        <div class="d-flex align-items-center gap-2">
            <span class="d-flex align-items-center justify-content-center rounded-circle"
                  style="width:34px;height:34px;background:#fdf4ff;flex-shrink:0;">
                <iconify-icon icon="mdi:calendar-check" style="color:#7c3aed;font-size:18px;"></iconify-icon>
            </span>
            <div>
                <div style="font-size:.7rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Tgl Pemasangan</div>
                <div class="fw-semibold" style="font-size:.9rem;">
                    {{ \Carbon\Carbon::parse($customer->install_date)->isoFormat('D MMM Y') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</x-ui.card>
{{-- ═══ END TEKNISI & KENEK BAR ════════════════════════════════════════════════ --}}

<x-ui.card>
    @slot('header')
        <div class="ui-card-title">Detail Invoice #{{ $invoice->invoice_number }}</div>
        <div class="d-flex gap-2">

            {{-- Download PDF --}}
            <a href="{{ route('invoice.download', $invoice->id) }}"
                class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                title="Download PDF">
                <iconify-icon icon="ic:baseline-download"></iconify-icon>
            </a>

            {{-- Edit --}}
            <a href="{{ route('invoice.edit', $invoice->id) }}"
                class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                title="Edit Invoice">
                <iconify-icon icon="lucide:edit"></iconify-icon>
            </a>

            {{-- Delete --}}
            <form action="{{ route('invoice.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Hapus invoice ini?')" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                    title="Hapus Invoice">
                    <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                </button>
            </form>

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
                    @foreach ($customer->customerProducts as $product)
                        <tr style="border-style: none !important;">
                            <td style="width: 15rem; text-align: left; border-style: none !important;">{{ $product->part->name }}</td>
                            <td style="border-style: none !important;">: {{ $product->categoryProduct->name }} | {{ $product->product->name }} | {{ $product->product->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Vehicle Description Section -->
            <h6 class="bg-gray-100 dark:bg-gray-950 text-center text-gray-800 dark:text-white font-bold text-xl my-4" style="background-color:black; color:white; padding: 5px 0;">
                VEHICLE DESCRIPTION
            </h6>
            <table class="table">
                <tbody style="border-style: none !important;">
                    <tr style="border-style: none !important;">
                        <td style="width: 15rem; border-style: none !important;">Brand</td>
                        <td style="border-style: none !important;">: {{ $customer->vehicle_brand }}</td>
                    </tr>
                    <tr style="border-style: none !important;">
                        <td style="width: 15rem; border-style: none !important;">Model</td>
                        <td style="border-style: none !important;">: {{ $customer->vehicle_model }}</td>
                    </tr>
                    <tr style="border-style: none !important;">
                        <td style="width: 15rem; border-style: none !important;">Police Number</td>
                        <td style="border-style: none !important;">: {{ $customer->plat_number }}</td>
                    </tr>
                    <tr style="border-style: none !important;">
                        <td style="width: 15rem; border-style: none !important;">Year</td>
                        <td style="border-style: none !important;">: {{ $customer->vehicle_year }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Installer Section -->
            <h6 class="text-center font-bold text-xl my-4" style="background-color:black; color:white; padding: 5px 0;">
                INSTALLER
            </h6>
            <table class="table">
                <tbody style="border-style: none !important;">
                    <tr style="border-style: none !important;">
                        <td style="width: 15rem; border-style: none !important;">Teknisi</td>
                        <td style="border-style: none !important;">: {{ $customer->tukang?->name ?? '-' }}</td>
                    </tr>
                    <tr style="border-style: none !important;">
                        <td style="width: 15rem; border-style: none !important;">Kenek</td>
                        <td style="border-style: none !important;">: {{ $customer->kenek?->name ?? '-' }}</td>
                    </tr>
                    @if($customer->install_date)
                    <tr style="border-style: none !important;">
                        <td style="width: 15rem; border-style: none !important;">Tanggal Pemasangan</td>
                        <td style="border-style: none !important;">: {{ \Carbon\Carbon::parse($customer->install_date)->isoFormat('D MMMM Y') }}</td>
                    </tr>
                    @endif
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
                                <p class="text-center fw-bold text-uppercase mb-0">TYPE OF TRANSACTION : {{ $invoice->payment_method }}</p>
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
                                        <td>: Rp. {{ number_format($invoice->downpayment, 0, ',', '.') }}</td>
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
