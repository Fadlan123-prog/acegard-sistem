@extends('layout.index')

@section('title', 'Dashboard Finance')
@section('route-breadcrumb', route('finance.dashboard'))
@section('breadcrumb', 'Dashboard Finance')

@section('content')

@php $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Invoice Belum Lunas"
            :value="number_format($outstandingInvoices)"
            color="red"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Total: {{ $idr($outstandingAmount) }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Komisi Siap Bayar"
            :value="$idr($commissionEligible)"
            color="amber"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">{{ number_format($commissionEligibleCount) }} transaksi eligible</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Total Hutang"
            :value="$idr($totalHutang)"
            color="purple"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Belum terbayar</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Total Piutang"
            :value="$idr($totalPiutang)"
            color="green"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Belum diterima</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">

    {{-- Pending Invoices --}}
    <div class="col-xl-7">
        <x-ui.card>
            @slot('header')
            <div class="d-flex justify-content-between align-items-center">
                <div class="ui-card-title">Invoice Belum Lunas</div>
                <x-ui.button variant="outline" size="sm" href="{{ route('customer.index') }}" style="font-size:11px">Semua Invoice</x-ui.button>
            </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Customer</th>
                            <th class="py-2 text-muted fw-medium border-bottom">No. Invoice</th>
                            <th class="py-2 text-center text-muted fw-medium border-bottom">Status</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Sisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingInvoices as $inv)
                        @php $vm = ['unpaid'=>'danger','partial'=>'warning']; @endphp
                        <tr>
                            <td class="ps-0 py-2">
                                <div style="font-size:12px;font-weight:500">{{ $inv->customer->name ?? '—' }}</div>
                            </td>
                            <td class="py-2 font-monospace text-muted" style="font-size:11px">{{ $inv->invoice_number ?? '—' }}</td>
                            <td class="py-2 text-center">
                                <x-ui.badge :variant="$vm[$inv->status] ?? 'neutral'" size="sm">
                                    {{ ucfirst($inv->status) }}
                                </x-ui.badge>
                            </td>
                            <td class="py-2 text-end fw-semibold" style="color:#ef4444;font-size:12px">
                                {{ $idr($inv->remaining_payment) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">
                                <x-ui.empty-state title="Semua invoice sudah lunas" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    {{-- Hutang Jatuh Tempo --}}
    <div class="col-xl-5">
        <x-ui.card title="Hutang Jatuh Tempo" subtitle="30 hari ke depan">
            @forelse ($hutangDue as $h)
            @php $isUrgent = $h->due_date && \Carbon\Carbon::parse($h->due_date)->diffInDays() <= 7; @endphp
            <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--ui-border)">
                <div>
                    <div style="font-size:12px;font-weight:500">{{ $h->vendor_name ?? $h->description ?? '—' }}</div>
                    <div class="text-muted" style="font-size:10px">{{ $idr($h->remaining_amount) }}</div>
                </div>
                <x-ui.badge :variant="$isUrgent ? 'danger' : 'warning'" size="sm">
                    {{ $h->due_date ? \Carbon\Carbon::parse($h->due_date)->format('d/m/Y') : '—' }}
                </x-ui.badge>
            </div>
            @empty
            <x-ui.empty-state title="Tidak ada hutang jatuh tempo dalam 30 hari" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Commissions to Pay --}}
    <div class="col-12">
        <x-ui.card title="Komisi Siap Dibayar per Karyawan">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Karyawan</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Jabatan</th>
                            <th class="py-2 text-center text-muted fw-medium border-bottom">Jml. Transaksi</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Total Komisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($commissionsToPay as $emp)
                        <tr>
                            <td class="ps-0 py-2 fw-medium" style="font-size:12px">{{ $emp->name }}</td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ $emp->job_position ?? '—' }}</td>
                            <td class="py-2 text-center">
                                <x-ui.badge variant="neutral" size="sm">{{ $emp->count }}</x-ui.badge>
                            </td>
                            <td class="py-2 text-end fw-semibold" style="color:#10b981;font-size:12px">
                                {{ $idr($emp->total) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">
                                <x-ui.empty-state title="Tidak ada komisi yang siap dibayar" />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

</div>

@endsection
