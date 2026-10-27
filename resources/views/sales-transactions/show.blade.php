@extends('dashboard.index')

@section('title', 'Detail Transaksi')
@section('route-breadcrumb', 'sales-transactions.show')
@section('breadcrumb', 'Detail Transaksi')

@section('content')

@php
    $variant = match($transaction->status) { 'paid' => 'success', 'partial' => 'warning', default => 'neutral' };
@endphp

<div class="row g-3">
    <div class="col-lg-7">
        <x-ui.card title="Rincian Transaksi">
            <dl class="row mb-0">
                <dt class="col-5 text-muted">Customer</dt>
                <dd class="col-7">{{ $transaction->customer_name }}<br><span class="text-muted small">{{ $transaction->customer_phone }}</span></dd>

                <dt class="col-5 text-muted">Sales</dt>
                <dd class="col-7">{{ $transaction->salesEmployee?->name ?? '-' }}</dd>

                <dt class="col-5 text-muted">Produk</dt>
                <dd class="col-7">{{ $transaction->product?->name ?? '-' }}</dd>

                <dt class="col-5 text-muted">Status</dt>
                <dd class="col-7"><x-ui.badge :variant="$variant">{{ \App\Models\SalesTransaction::$statusLabels[$transaction->status] ?? $transaction->status }}</x-ui.badge></dd>

                <dt class="col-5 text-muted">Harga</dt>
                <dd class="col-7">Rp {{ number_format($transaction->price, 0, ',', '.') }}</dd>

                <dt class="col-5 text-muted">Diskon</dt>
                <dd class="col-7">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</dd>

                <dt class="col-5 text-muted">Total Setelah Diskon</dt>
                <dd class="col-7 fw-semibold">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</dd>

                <dt class="col-5 text-muted">Sudah Dibayar</dt>
                <dd class="col-7">Rp {{ number_format($transaction->downpayment, 0, ',', '.') }}</dd>

                <dt class="col-5 text-muted">Sisa</dt>
                <dd class="col-7">Rp {{ number_format($transaction->remaining_payment, 0, ',', '.') }}</dd>
            </dl>

            @if(!$transaction->isPaidOff())
            <hr>
            <form method="POST" action="{{ route('sales-transactions.payment', $transaction) }}" class="d-flex gap-2 align-items-end">
                @csrf
                <x-ui.form-field label="Catat Pembayaran (Rp)" name="amount" class="mb-0 flex-grow-1">
                    <input type="number" name="amount" class="form-control" min="1" max="{{ $transaction->remaining_payment }}" required>
                </x-ui.form-field>
                <x-ui.button type="submit" variant="success">Catat</x-ui.button>
            </form>
            @endif

            @if($transaction->isPaidOff() && !$transaction->commission_released_at)
            <hr>
            <form method="POST" action="{{ route('sales-transactions.release-commission', $transaction) }}" onsubmit="return confirm('Rilis komisi untuk transaksi ini?')">
                @csrf
                <x-ui.button type="submit" variant="primary" class="w-100">Rilis Komisi</x-ui.button>
            </form>
            @endif
        </x-ui.card>
    </div>

    <div class="col-lg-5">
        <x-ui.card title="Rincian Komisi">
            @if($transaction->commissions->isEmpty())
                <x-ui.empty-state title="Belum ada komisi" description="Komisi muncul di sini setelah transaksi lunas dan tombol 'Rilis Komisi' ditekan." />
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Penerima</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->commissions as $c)
                                <tr>
                                    <td class="small">{{ \App\Models\Commission::$salesRoleLabels[$c->role] ?? $c->role }}</td>
                                    <td class="small">{{ $c->employee?->name ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>
</div>

<div class="mt-3">
    <x-ui.button :href="route('sales-transactions.index')" variant="secondary">Kembali</x-ui.button>
</div>

@endsection
