@extends('layout.index')

@section('title', 'Dashboard Pemilik')
@section('route-breadcrumb', route('owner.dashboard'))
@section('breadcrumb', 'Dashboard Pemilik')

@section('content')

@php $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Omset Hari Ini"
            :value="$idr($revenueToday)"
            color="green"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Invoice terbayar lunas</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Omset Bulan Ini"
            :value="$idr($revenueMonth)"
            color="blue"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">{{ now()->translatedFormat('F Y') }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Invoice Belum Lunas"
            :value="number_format($outstandingCount)"
            color="red"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">Sisa: {{ $idr($outstandingAmount) }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card
            label="Total Customer"
            :value="number_format($totalCustomers)"
            color="purple"
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        >
            <span class="text-muted" style="font-size:11px">{{ $totalBranches }} cabang aktif</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">

    {{-- Revenue by Branch --}}
    <div class="col-xl-4">
        <x-ui.card title="Omset per Cabang" subtitle="bulan ini" class="h-100">
            @forelse ($revenueByBranch as $rb)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:13px">{{ $rb->name }}</span>
                <span class="fw-semibold" style="font-size:13px">{{ $idr($rb->total) }}</span>
            </div>
            @empty
            <x-ui.empty-state title="Belum ada data" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Top Marketing --}}
    <div class="col-xl-4">
        <x-ui.card title="Top Marketing" subtitle="komisi bulan ini" class="h-100">
            @php $rankColors = ['#10b981','#6366f1','#f59e0b','#0ea5e9','#8b5cf6']; @endphp
            @forelse ($topMarketing as $i => $m)
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge rounded-circle d-flex align-items-center justify-content-center fw-bold"
                      style="width:24px;height:24px;background:{{ $rankColors[$i] ?? '#64748b' }};color:#fff;font-size:10px;flex-shrink:0">
                    {{ $i+1 }}
                </span>
                <div class="flex-grow-1 min-width-0">
                    <div style="font-size:13px;font-weight:500" class="text-truncate">{{ $m->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $m->job_count }} job</div>
                </div>
                <span class="fw-semibold" style="font-size:12px">{{ $idr($m->total_commission) }}</span>
            </div>
            @empty
            <x-ui.empty-state title="Belum ada data" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Top Teknisi --}}
    <div class="col-xl-4">
        <x-ui.card title="Top Teknisi" subtitle="komisi bulan ini" class="h-100">
            @forelse ($topTeknisi as $i => $t)
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge rounded-circle d-flex align-items-center justify-content-center fw-bold"
                      style="width:24px;height:24px;background:{{ $rankColors[$i] ?? '#64748b' }};color:#fff;font-size:10px;flex-shrink:0">
                    {{ $i+1 }}
                </span>
                <div class="flex-grow-1 min-width-0">
                    <div style="font-size:13px;font-weight:500" class="text-truncate">{{ $t->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $t->job_count }} job</div>
                </div>
                <span class="fw-semibold" style="font-size:12px">{{ $idr($t->total_commission) }}</span>
            </div>
            @empty
            <x-ui.empty-state title="Belum ada data" />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Recent Invoices --}}
    <div class="col-12">
        <x-ui.card title="Invoice Terbaru">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">No. Invoice</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Customer</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Total</th>
                            <th class="py-2 text-center text-muted fw-medium border-bottom">Status</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentInvoices as $inv)
                        <tr>
                            <td class="ps-0 py-2 font-monospace" style="font-size:11px">{{ $inv->invoice_number ?? '—' }}</td>
                            <td class="py-2">{{ $inv->customer->name ?? '—' }}</td>
                            <td class="py-2 text-end fw-semibold">{{ $idr($inv->total_price) }}</td>
                            <td class="py-2 text-center">
                                @php $vm = ['paid'=>'success','unpaid'=>'danger','partial'=>'warning','void'=>'neutral']; @endphp
                                <x-ui.badge :variant="$vm[$inv->status] ?? 'neutral'" size="sm">{{ ucfirst($inv->status) }}</x-ui.badge>
                            </td>
                            <td class="py-2 text-end text-muted" style="font-size:11px">{{ $inv->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center">
                                <x-ui.empty-state title="Belum ada invoice" />
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
