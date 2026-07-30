@extends('dashboard.index')

@section('title', 'Petty Cash')
@section('route-breadcrumb', 'petty-cash.index')
@section('breadcrumb', 'Petty Cash')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

{{-- FILTER --}}
<x-ui.card class="mb-3">
    <form method="GET" action="{{ route('petty-cash.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4 col-sm-6">
            <label class="form-label fw-semibold small mb-1">Cabang</label>
            <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected($b->id == $branchId)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-6">
            <label class="form-label fw-semibold small mb-1">Bulan</label>
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" onchange="this.form.submit()">
        </div>
        <div class="col-auto ms-auto d-flex gap-2 flex-wrap">
            @if($branchId)
            <x-ui.button href="{{ route('petty-cash.pdf', ['branch_id' => $branchId, 'month' => $month]) }}"
               target="_blank" variant="secondary" size="sm">
                <iconify-icon icon="mdi:file-pdf-box" class="me-1"></iconify-icon>Print PDF
            </x-ui.button>
            @endif
            <x-ui.button href="{{ route('petty-cash.create-in') }}" variant="success" size="sm">
                <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Tambah Saldo
            </x-ui.button>
            <x-ui.button href="{{ route('petty-cash.create-out') }}" variant="primary" size="sm">
                <iconify-icon icon="mdi:motorbike" class="me-1"></iconify-icon>Catat Pemakaian Motor
            </x-ui.button>
            <x-ui.button href="{{ route('petty-cash.create-purchase') }}" variant="warning" size="sm">
                <iconify-icon icon="mdi:cart-outline" class="me-1"></iconify-icon>Klaim Pembelian
            </x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- SUMMARY CARDS --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <x-ui.stats-card label="Total Masuk (Bulan Ini)" :value="'Rp' . number_format($totalIn, 0, ',', '.')" color="green">
            <iconify-icon icon="mdi:arrow-down-circle"></iconify-icon>
        </x-ui.stats-card>
    </div>
    <div class="col-md-4">
        <x-ui.stats-card label="Total Keluar (Bulan Ini)" :value="'Rp' . number_format($totalOut, 0, ',', '.')" color="red">
            <iconify-icon icon="mdi:arrow-up-circle"></iconify-icon>
        </x-ui.stats-card>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"
             style="background: {{ $balance >= 0 ? '#0f172a' : '#7f1d1d' }};">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:rgba(255,255,255,0.15);flex-shrink:0;">
                    <iconify-icon icon="mdi:wallet" style="font-size:26px;color:#fff;"></iconify-icon>
                </div>
                <div>
                    <div class="small" style="color:#94a3b8;">Saldo s/d Akhir Bulan Ini</div>
                    <div class="fw-bold fs-5" style="color:#fff;">Rp{{ number_format($balance, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TRANSACTION TABLE --}}
<x-ui.card>

    @slot('header')
        <div class="ui-card-title">
            Riwayat Transaksi
            @if($branch)
                <span class="text-muted fw-normal">— {{ $branch->name }}</span>
            @endif
            <x-ui.badge variant="neutral" class="ms-1">{{ \Carbon\Carbon::parse($month . '-01')->isoFormat('MMMM Y') }}</x-ui.badge>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Tanggal</th>
                    <th>Tipe</th>
                    <th>Keterangan / Item</th>
                    <th>Karyawan</th>
                    <th>Motor</th>
                    <th>Tujuan / Barang</th>
                    <th class="text-end">Jarak (km)</th>
                    <th class="text-end">Biaya Bensin</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Saldo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalOut = $transactions->whereIn('type', ['out', 'purchase'])->sum('amount');
                    $running  = $balance - $transactions->where('type','in')->sum('amount') + $totalOut;
                @endphp
                @forelse($transactions as $trx)
                    @php
                        if ($trx->type === 'in') {
                            $running += $trx->amount;
                        } else {
                            $running -= $trx->amount;
                        }
                    @endphp
                    <tr>
                        <td class="ps-3 text-nowrap">{{ $trx->date->isoFormat('D MMM Y') }}</td>
                        <td>
                            @if($trx->type === 'in')
                                <x-ui.badge variant="success">
                                    <iconify-icon icon="mdi:arrow-down" class="me-1"></iconify-icon>Masuk
                                </x-ui.badge>
                            @elseif($trx->type === 'out')
                                <x-ui.badge variant="danger">
                                    <iconify-icon icon="mdi:motorbike" class="me-1"></iconify-icon>Motor
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">
                                    <iconify-icon icon="mdi:cart-outline" class="me-1"></iconify-icon>Beli
                                </x-ui.badge>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $trx->description ?? '-' }}</td>
                        <td>{{ $trx->employee?->name ?? '-' }}</td>
                        <td class="small">
                            @if($trx->type === 'out')
                                {{ $trx->motor_name ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($trx->type === 'out')
                                {{ $trx->destination ?? '-' }}
                            @elseif($trx->type === 'purchase')
                                <span class="fw-semibold text-dark">{{ $trx->item_name ?? '-' }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end">
                            @if($trx->distance_km)
                                {{ number_format($trx->distance_km, 1, ',', '.') }} km
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end small text-muted">
                            @if($trx->fuel_cost > 0)
                                Rp{{ number_format($trx->fuel_cost, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end fw-semibold text-nowrap">
                            @if($trx->type === 'in')
                                <span class="text-success">+ Rp{{ number_format($trx->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-danger">- Rp{{ number_format($trx->amount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold text-nowrap" style="color: {{ $running >= 0 ? '#0f172a' : '#dc2626' }};">
                            Rp{{ number_format($running, 0, ',', '.') }}
                        </td>
                        <td>
                            <form method="POST" action="{{ route('petty-cash.destroy', $trx->id) }}"
                                  onsubmit="return confirm('Hapus transaksi ini?')">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger-outline" size="sm" title="Hapus">
                                    <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                </x-ui.button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="py-5"><x-ui.empty-state title="Belum ada transaksi bulan ini." /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-ui.card>

@endsection
