@extends('layout.index')

@section('title','Dashboard')

@section('route-breadcrumb', 'dashboard')

@section('breadcrumb', 'Dashboard')

@section('content')


@if (isset($branch))
    <h3>Cabang Aktif: {{ $branch->name }}</h3>
@endif

@if (isset($customers))
    <ul>
        @foreach ($customers as $customer)
            <li>{{ $customer->name }}</li>
        @endforeach
    </ul>
@endif
<div class="row gy-4">
        <div class="col-xxl-12">
            <div class="row gy-4">

                <div class="col-xxl-4 col-sm-6">
                    <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-1">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                                <div class="d-flex align-items-center gap-2">
                                    <span class="mb-0 w-48-px h-48-px bg-primary-600 flex-shrink-0 text-white d-flex justify-content-center align-items-center rounded-circle h6 mb-0">
                                        <iconify-icon icon="mingcute:user-follow-fill" class="icon"></iconify-icon>
                                    </span>
                                    <div>
                                        <span class="mb-2 fw-medium text-secondary-light text-sm">New Users</span>
                                        @if (isset($totalUsers))
                                        <h6 class="fw-semibold">{{ $totalUsers }}</h6>
                                        @endif
                                    </div>
                                </div>

                                <div id="new-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                            </div>
                            <p class="text-sm mb-0">Increase by <span class="bg-success-focus px-1 rounded-2 fw-medium text-success-main text-sm">+200</span> this week</p>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4 col-sm-6">
                    <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-2">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                                <div class="d-flex align-items-center gap-2">
                                    <span class="mb-0 w-48-px h-48-px bg-success-main flex-shrink-0 text-white d-flex justify-content-center align-items-center rounded-circle h6">
                                        <iconify-icon icon="mingcute:user-follow-fill" class="icon"></iconify-icon>
                                    </span>
                                    <div>
                                        <span class="mb-2 fw-medium text-secondary-light text-sm">Cabang Aktif</span>
                                        @if (isset($totalBranches))
                                        <h6 class="fw-semibold">{{ $totalBranches }}</h6>
                                        @endif
                                    </div>
                                </div>

                                <div id="active-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                            </div>
                            <p class="text-sm mb-0">Increase by <span class="bg-success-focus px-1 rounded-2 fw-medium text-success-main text-sm">+200</span> this week</p>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4 col-sm-6">
                    <div class="card p-3 shadow-2 radius-8 border input-form-light h-100 bg-gradient-end-3">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">

                                <div class="d-flex align-items-center gap-2">
                                    <span class="mb-0 w-48-px h-48-px bg-yellow text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle h6">
                                        <iconify-icon icon="iconamoon:discount-fill" class="icon"></iconify-icon>
                                    </span>
                                    <div>
                                        <span class="mb-2 fw-medium text-secondary-light text-sm">Total Customers</span>
                                        @if (isset($totalCustomers))
                                        <h6 class="fw-semibold">{{ $totalCustomers }}</h6>
                                        @endif
                                    </div>
                                </div>

                                <div id="total-sales-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                            </div>
                            <p class="text-sm mb-0">Increase by <span class="bg-danger-focus px-1 rounded-2 fw-medium text-danger-main text-sm">-$10k</span> this week</p>
                        </div>
                    </div>
                </div>

                @if(!empty($rows))
                    <div class="row gy-4 mt-1">
                        @foreach ($rows as $row)
                        <div class="col-xxl-3 col-sm-6">
                            <div class="card p-3 shadow-2 radius-8 h-100">
                            <div class="card-body p-0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">{{ $row->name }}</h6>
                                <span class="badge bg-secondary">{{ number_format($row->total_rows) }} item</span>
                                </div>
                                <div class="fs-5 fw-semibold">{{ number_format($row->total_meters, 2) }} m²</div>
                                <small class="text-muted">Total meter terpasang</small>
                            </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-2"><strong>Grand Total:</strong> {{ number_format($grandTotal, 2) }} m²</p>
                    @endif

                    {{-- Rekap per PRODUK BUILDING --}}
                    @if(!empty($products) && count($products))
                    <h5 class="mt-4 mb-3">Rekap per Produk Building</h5>
                    <div class="row gy-3">
                        @foreach ($products as $p)
                        <div class="col-xxl-3 col-lg-4 col-md-6">
                            <div class="card p-3 shadow-1 radius-8 h-100">
                            <div class="card-body p-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-info">{{ $p->category_name }}</span>
                                <span class="text-muted small">#{{ $p->id }}</span>
                                </div>
                                <h6 class="mb-1">{{ $p->name }}</h6>
                                <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div class="fw-semibold">{{ number_format($p->total_meters, 2) }} m²</div>
                                    <small class="text-muted">Total meter</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">{{ number_format($p->total_rows) }}</div>
                                    <small class="text-muted">Transaksi</small>
                                </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mt-3">Belum ada data pemasangan gedung.</p>
                    @endif

            </div>
        </div>
        <!-- Revenue Growth start -->
    </div>
@endsection
