@extends('dashboard.index')

@section('title', 'Dashboard Sales')
@section('route-breadcrumb', 'sales.dashboard')
@section('breadcrumb', 'Dashboard Sales')

@section('content')

@if(!$profile)
<x-ui.alert type="warning" class="mb-3">Akun Anda belum terdaftar di jaringan sales. Hubungi admin.</x-ui.alert>
@elseif(!$profile->isActive())
<x-ui.alert type="warning" class="mb-3">Akun Anda masih menunggu approval admin. Beberapa fitur belum bisa dipakai.</x-ui.alert>
@endif

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Closing (Lunas)" :value="number_format($myClosings)" color="green" accent />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Nilai Transaksi" :value="'Rp ' . number_format($myClosingValue, 0, ',', '.')" color="blue" accent />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Komisi Belum Cair" :value="'Rp ' . number_format($myCommissionEligible, 0, ',', '.')" color="purple" accent />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Komisi Sudah Cair" :value="'Rp ' . number_format($myCommissionPaid, 0, ',', '.')" color="red" accent />
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <x-ui.card title="Transaksi Terbaru">
            @if($recentTransactions->isEmpty())
                <x-ui.empty-state title="Belum ada transaksi." />
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Customer</th><th>Status</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($recentTransactions as $tx)
                            <tr>
                                <td><a href="{{ route('sales-transactions.show', $tx) }}">{{ $tx->customer_name }}</a></td>
                                <td class="small">{{ \App\Models\SalesTransaction::$statusLabels[$tx->status] ?? $tx->status }}</td>
                                <td class="text-end">Rp {{ number_format($tx->total_price, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <x-ui.button :href="route('sales-transactions.index')" variant="outline" size="sm" class="mt-3">Lihat Semua Transaksi</x-ui.button>
        </x-ui.card>
    </div>

    <div class="col-lg-5">
        <x-ui.card title="Ringkasan Komisi">
            @if($commissionBreakdown->isEmpty())
                <x-ui.empty-state title="Belum ada komisi." />
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Jenis</th><th class="text-center">Jumlah</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($commissionBreakdown as $row)
                            <tr>
                                <td class="small">{{ \App\Models\Commission::$salesRoleLabels[$row->role] ?? $row->role }}</td>
                                <td class="text-center">{{ $row->count }}</td>
                                <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>

        @if($profile)
        <x-ui.card title="Jaringan" class="mt-3">
            <p class="mb-2">Rekrutan: <strong>{{ $recruitCount }}</strong> orang</p>
            <x-ui.button :href="route('sales-network.my-network')" variant="outline" size="sm">Kelola Jaringan Saya</x-ui.button>
        </x-ui.card>
        @endif
    </div>
</div>

@endsection
