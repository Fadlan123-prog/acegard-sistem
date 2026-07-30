@extends('dashboard.index')

@section('title', 'Buat Invoice Customer')
@section('route-breadcrumb', 'customer.building.index')
@section('breadcrumb', 'Customers Building')

@section('content')

@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <strong>Terjadi kesalahan.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif

@if (session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="ui-card-title">Form Buat Invoice Building</div>
        <x-ui.button variant="secondary" size="sm" href="{{ route('customer.building.index') }}">Kembali</x-ui.button>
    @endslot

        @php
            $items = $customerProducts ?? $customer->products ?? collect();
            $selectedType = old('type');
            $selectedStatus = old('status', 'unpaid');
        @endphp

        <form action="{{ route('invoice.building.store') }}" method="POST">
            @csrf

            <input type="hidden" name="customer_building_id" value="{{ $customer->id }}">

            {{-- Informasi Customer --}}
            <div class="mb-4">
                <h6 class="fw-bold mb-2">Informasi Customer</h6>

                <div class="row">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Nama Customer</small>
                        <span class="fw-semibold">{{ $customer->name ?? '-' }}</span>
                    </div>

                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Card Number</small>
                        <span class="fw-semibold">{{ $customer->card_number ?? '-' }}</span>
                    </div>

                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Cabang</small>
                        <span class="fw-semibold">{{ $customer->branch->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Nomor Invoice & Tanggal --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No Invoice</label>

                    <input type="text"
                           name="invoice_number"
                           class="form-control @error('invoice_number') is-invalid @enderror"
                           value="{{ old('invoice_number', 'INV-' . now()->format('YmdHis')) }}">

                    @error('invoice_number')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <small class="text-muted">
                        Kosongkan juga boleh, sistem akan membuat nomor invoice otomatis.
                    </small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Invoice</label>

                    <input type="datetime-local"
                           name="invoice_date"
                           class="form-control @error('invoice_date') is-invalid @enderror"
                           value="{{ old('invoice_date', now()->format('Y-m-d\TH:i')) }}"
                           required>

                    @error('invoice_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            {{-- Tipe Pembayaran --}}
            <x-ui.form-field label="Tipe Pembayaran" name="type">
                <select name="type"
                        class="form-select @error('type') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Tipe Pembayaran --</option>

                    <option value="cash" {{ $selectedType === 'cash' ? 'selected' : '' }}>
                        Cash
                    </option>

                    <option value="credit" {{ $selectedType === 'credit' ? 'selected' : '' }}>
                        Credit
                    </option>

                    <option value="transfer" {{ $selectedType === 'transfer' ? 'selected' : '' }}>
                        Transfer
                    </option>
                </select>

                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </x-ui.form-field>

            {{-- Produk Customer --}}
            <h5 class="mt-4 mb-2">Produk Customer & Harga</h5>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Produk</th>
                            <th style="width: 25%;">Kategori</th>
                            <th style="width: 15%;">Ukuran (m²)</th>
                            <th style="width: 30%;">Harga</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    {{ $item->product->name ?? '-' }}

                                    <input type="hidden"
                                           name="products[{{ $loop->index }}][customer_building_product_id]"
                                           value="{{ $item->id }}">
                                </td>

                                <td>
                                    {{ $item->category->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->meters ?? 0 }}
                                </td>

                                <td>
                                    <input type="number"
                                           name="products[{{ $loop->index }}][price]"
                                           class="form-control @error("products.$loop->index.price") is-invalid @enderror"
                                           min="0"
                                           value="{{ old("products.$loop->index.price") }}"
                                           required>

                                    @error("products.$loop->index.price")
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-5">
                                    <x-ui.empty-state title="Belum ada produk pada customer building ini." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Diskon, DP, Status --}}
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Diskon</label>

                    <input type="number"
                           name="discount"
                           class="form-control @error('discount') is-invalid @enderror"
                           min="0"
                           value="{{ old('discount', 0) }}">

                    @error('discount')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Uang Muka (Downpayment)</label>

                    <input type="number"
                           name="down_payment"
                           class="form-control @error('down_payment') is-invalid @enderror"
                           min="0"
                           value="{{ old('down_payment', 0) }}">

                    @error('down_payment')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Status Pembayaran</label>

                    <select name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required>
                        <option value="unpaid" {{ $selectedStatus === 'unpaid' ? 'selected' : '' }}>
                            Belum Lunas
                        </option>

                        <option value="partial" {{ $selectedStatus === 'partial' ? 'selected' : '' }}>
                            Sebagian
                        </option>

                        <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>
                            Lunas
                        </option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            {{-- Button --}}
            <div class="d-flex gap-2 mt-3">
                <x-ui.button type="submit" variant="primary" :disabled="$items->count() < 1">
                    Simpan Invoice
                </x-ui.button>

                <x-ui.button variant="outline" href="{{ route('customer.building.index') }}">Batal</x-ui.button>
            </div>
        </form>
</x-ui.card>

@endsection
