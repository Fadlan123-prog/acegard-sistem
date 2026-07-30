@extends('dashboard.index')

@section('title', 'Laporan Purchasing Bulanan')
@section('route-breadcrumb', 'purchase.reports.monthly')
@section('breadcrumb', 'Laporan Purchasing')

@section('content')

<x-ui.card>

    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Laporan Purchasing Bulanan</div>
                <small class="text-muted">
                    Rekap pembelian barang berdasarkan bulan, cabang, supplier, qty, dan harga.
                </small>
            </div>
            <x-ui.button href="{{ route('purchases.index') }}" variant="outline" size="sm">
                Kembali ke Purchasing
            </x-ui.button>
        </div>
    @endslot

    {{-- Filter --}}
    <div class="border rounded p-3 mb-4 bg-light">
        <form method="GET" action="{{ route('purchase.reports.monthly') }}" class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="form-label fw-semibold">Bulan</label>
                <input type="month"
                       name="month"
                       class="form-control"
                       value="{{ $month }}">
            </div>

            @if(auth()->user()->hasRole('superadmin'))
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cabang</label>
                    <select name="branch_id" class="form-select">
                        <option value="">-- Semua Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-3">
                <label class="form-label fw-semibold">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">-- Semua Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">-- Semua --</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>
                        Confirmed
                    </option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                        Cancelled
                    </option>
                </select>
            </div>

            <div class="col-md-1 d-flex gap-2">
                <x-ui.button type="submit" variant="primary">Filter</x-ui.button>
            </div>

            <div class="col-md-12">
                @if(request('month') || request('branch_id') || request('supplier_id') || request('status'))
                    <x-ui.button href="{{ route('purchase.reports.monthly') }}" variant="outline" size="sm">
                        Reset Filter
                    </x-ui.button>
                @endif
            </div>

        </form>
    </div>


    {{-- Summary Cards --}}
    <div class="row mb-4">

        <div class="col-md-4 mb-3">
            <x-ui.stats-card
                label="Periode Laporan"
                value="{{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}"
                color="blue"
            />
        </div>

        <div class="col-md-4 mb-3">
            <x-ui.stats-card
                label="Total Qty"
                value="{{ number_format($totalQty, 2, ',', '.') }}"
                color="amber"
            />
        </div>

        <div class="col-md-4 mb-3">
            <x-ui.stats-card
                label="Grand Total Purchasing"
                value="Rp{{ number_format($grandTotal, 0, ',', '.') }}"
                color="green"
            />
        </div>

    </div>


    {{-- Ringkasan Per Barang --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="mb-0">Ringkasan Per Barang</h5>
                <small class="text-muted">
                    Total qty dan total harga berdasarkan nama barang dalam bulan terpilih.
                </small>
            </div>
        </div>

        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Barang</th>
                        <th class="text-end">Total Qty</th>
                        <th class="text-end">Rata-rata Harga</th>
                        <th class="text-end">Total Harga</th>
                        <th>Pembelian Pertama</th>
                        <th>Pembelian Terakhir</th>
                        <th class="text-center">Transaksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($summary as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <span class="fw-semibold">
                                    {{ $row->item_name }}
                                </span>
                            </td>

                            <td class="text-end">
                                {{ number_format($row->total_qty, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                Rp{{ number_format($row->average_price, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold">
                                Rp{{ number_format($row->total_amount, 0, ',', '.') }}
                            </td>

                            <td>
                                {{ $row->first_purchase_date ? \Carbon\Carbon::parse($row->first_purchase_date)->format('d-m-Y') : '-' }}
                            </td>

                            <td>
                                {{ $row->last_purchase_date ? \Carbon\Carbon::parse($row->last_purchase_date)->format('d-m-Y') : '-' }}
                            </td>

                            <td class="text-center">
                                <x-ui.badge variant="primary">{{ $row->total_transactions }}</x-ui.badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-5"><x-ui.empty-state title="Tidak ada data purchasing pada periode ini." /></td>
                        </tr>
                    @endforelse
                </tbody>

                @if($summary->count())
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total</th>
                            <th class="text-end">
                                {{ number_format($totalQty, 2, ',', '.') }}
                            </th>
                            <th></th>
                            <th class="text-end">
                                Rp{{ number_format($grandTotal, 0, ',', '.') }}
                            </th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>


    {{-- Detail Pembelian --}}
    <div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="mb-0 mt-3">Detail Pembelian</h5>
                <small class="text-muted">
                    Daftar transaksi pembelian berdasarkan tanggal dan nomor purchasing.
                </small>
            </div>
        </div>

        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Tanggal</th>
                        <th>No Purchasing</th>
                        <th>Cabang</th>
                        <th>Supplier</th>
                        <th>Barang</th>
                        <th class="text-end">Qty</th>
                        <th>Satuan</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($details as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $row->purchase_date ? \Carbon\Carbon::parse($row->purchase_date)->format('d-m-Y') : '-' }}
                            </td>

                            <td>
                                <span class="fw-semibold">
                                    {{ $row->purchase_number }}
                                </span>
                            </td>

                            <td>
                                {{ $row->branch_name ?? '-' }}
                            </td>

                            <td>
                                {{ $row->supplier_name ?? '-' }}
                            </td>

                            <td>
                                {{ $row->item_name }}
                            </td>

                            <td class="text-end">
                                {{ number_format($row->qty, 2, ',', '.') }}
                            </td>

                            <td>
                                {{ $row->unit ?? '-' }}
                            </td>

                            <td class="text-end">
                                Rp{{ number_format($row->unit_price, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold">
                                Rp{{ number_format($row->total_price, 0, ',', '.') }}
                            </td>

                            <td>
                                @if(($row->status ?? null) === 'confirmed')
                                    <x-ui.badge variant="success">Confirmed</x-ui.badge>
                                @elseif(($row->status ?? null) === 'draft')
                                    <x-ui.badge variant="warning">Draft</x-ui.badge>
                                @elseif(($row->status ?? null) === 'cancelled')
                                    <x-ui.badge variant="danger">Cancelled</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral">{{ $row->status ?? '-' }}</x-ui.badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-5"><x-ui.empty-state title="Tidak ada detail pembelian pada periode ini." /></td>
                        </tr>
                    @endforelse
                </tbody>

                @if($details->count())
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">
                                Total
                            </th>
                            <th class="text-end">
                                {{ number_format($details->sum('qty'), 2, ',', '.') }}
                            </th>
                            <th></th>
                            <th></th>
                            <th class="text-end">
                                Rp{{ number_format($details->sum('total_price'), 0, ',', '.') }}
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</x-ui.card>

@endsection
