@extends('dashboard.index')

@section('title', 'Edit Invoice')
@section('route-breadcrumb', 'customer.index')
@section('breadcrumb', 'Customers')

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
        <h5 class="card-title mb-0">Form Edit Invoice</h5>
        <a href="{{ route('customer.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <form action="{{ route('invoice.update', $invoice->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="customer_id" value="{{ $invoice->customer_id }}">

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
                    <option value="">Pilih</option>
                    <option value="cash" {{ $invoice->type == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="credit" {{ $invoice->type == 'credit' ? 'selected' : '' }}>Credit</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Harga</label>
                <input type="number" name="price" class="form-control" value="{{ old('price', $invoice->price) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Diskon</label>
                <input type="number" name="discount" class="form-control" value="{{ old('discount', $invoice->discount ?? 0) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Uang Muka (Downpayment)</label>
                <input type="number" name="downpayment" class="form-control" value="{{ old('downpayment', $invoice->downpayment ?? 0) }}">
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
