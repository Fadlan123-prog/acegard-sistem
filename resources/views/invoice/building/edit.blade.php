@extends('dashboard.index')

@section('title', 'Edit Invoice Customer')
@section('route-breadcrumb', 'customer.building.index')
@section('breadcrumb', 'Customers Building')

@section('content')
@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Edit Invoice</h5>
        <a href="{{ route('customer.building.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>

    <div class="card-body">
        <form action="{{ route('invoice.building.update', $invoice->id) }}" method="POST">
            @csrf
            @method('PUT')

            <input type="hidden" name="customer_building_id" value="{{ $customer->id }}">
            <input type="hidden" name="branch_id" value="{{ $invoice->branch_id }}">
            <input type="hidden" name="user_id" value="{{ $invoice->user_id }}">

            <div class="mb-3">
                <label class="form-label">No Invoice</label>
                <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number', $invoice->invoice_number) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal Invoice</label>
                <input type="datetime-local" name="invoice_date" class="form-control" value="{{ old('invoice_date', \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d\TH:i')) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tipe Pembayaran</label>
                <select name="type" class="form-select" required>
                    <option value="cash" {{ $invoice->type == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="credit" {{ $invoice->type == 'credit' ? 'selected' : '' }}>Credit</option>
                </select>
            </div>

            <h5 class="mt-4 mb-2">Produk Customer & Harga</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Ukuran (m²)</th>
                        <th>Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customerProducts as $item)
                    <tr>
                        <td>
                            {{ $item->product->name ?? '-' }}
                            <input type="hidden" name="products[{{ $loop->index }}][customer_building_product_id]" value="{{ $item->id }}">
                        </td>
                        <td>{{ $item->category->name ?? '-' }}</td>
                        <td>{{ $item->meters }}</td>
                        <td>
                            <input type="number" name="products[{{ $loop->index }}][price]" class="form-control" value="{{ old("products.$loop->index.price", $item->pivot->price ?? '') }}" required>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mb-3 mt-4">
                <label class="form-label">Diskon</label>
                <input type="number" name="discount" class="form-control" value="{{ old('discount', $invoice->discount) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Uang Muka (Downpayment)</label>
                <input type="number" name="down_payment" class="form-control" value="{{ old('down_payment', $invoice->downpayment) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Status Pembayaran</label>
                <select name="status" class="form-select" required>
                    <option value="unpaid" {{ $invoice->status == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="partial" {{ $invoice->status == 'partial' ? 'selected' : '' }}>Sebagian</option>
                    <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Update Invoice</button>
        </form>
    </div>
</div>
@endsection
