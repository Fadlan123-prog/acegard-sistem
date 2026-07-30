@extends('dashboard.index')

@section('title', 'Periode Komisi')
@section('route-breadcrumb', 'commission.periods.index')
@section('breadcrumb', 'Periode Komisi')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

{{-- INFO ATURAN --}}
<x-ui.alert type="info" class="mb-4" :dismissible="false">
    <x-slot name="title">Aturan Periode Komisi</x-slot>
    Komisi dibagi per periode <strong>tanggal 16 s.d. 15 bulan berikutnya</strong>.
    Pembayaran dilakukan setiap <strong>tanggal 15</strong>.
</x-ui.alert>

@if($branch)
<div class="d-flex align-items-center gap-2 mb-3">
    <iconify-icon icon="proicons-branch" style="font-size:16px;color:#64748b;"></iconify-icon>
    <span class="text-muted small">Cabang:</span>
    <span class="badge px-3 py-2 fw-semibold" style="background:#0f172a;color:#fff;font-size:.8rem;">{{ $branch->name }}</span>
</div>
@endif

<x-ui.card>
    <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Periode</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total Komisi</th>
                        <th class="text-end">Sudah Dibayar</th>
                        <th class="text-end">Belum Dibayar</th>
                        <th>Status</th>
                        <th class="text-center pe-3" style="width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $p)
                    @php
                        $isCurrent = $p->period_start === $currentPeriodStart;
                    @endphp
                    <tr class="{{ $isCurrent ? 'table-primary' : '' }}">
                        <td class="ps-3">
                            <div class="fw-semibold small">
                                {{ \Carbon\Carbon::parse($p->period_start)->isoFormat('D MMM Y') }}
                                —
                                {{ \Carbon\Carbon::parse($p->period_end)->isoFormat('D MMM Y') }}
                            </div>
                            @if($isCurrent)
                            <x-ui.badge variant="primary" size="sm">Periode Berjalan</x-ui.badge>
                            @endif
                        </td>
                        <td class="small {{ $p->is_overdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                            {{ \Carbon\Carbon::parse($p->due_on)->isoFormat('D MMM Y') }}
                            @if($p->is_overdue)
                            <div style="font-size:.7rem;">Terlambat!</div>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">
                            Rp{{ number_format($p->total_amount, 0, ',', '.') }}
                            <div class="text-muted fw-normal" style="font-size:.7rem;">{{ $p->total_cnt }} transaksi</div>
                        </td>
                        <td class="text-end text-success fw-semibold">
                            @if($p->paid_amount > 0)
                                Rp{{ number_format($p->paid_amount, 0, ',', '.') }}
                                <div class="text-muted fw-normal" style="font-size:.7rem;">{{ $p->paid_cnt }} transaksi</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold {{ $p->unpaid_amount > 0 ? 'text-danger' : 'text-muted' }}">
                            @if($p->unpaid_amount > 0)
                                Rp{{ number_format($p->unpaid_amount, 0, ',', '.') }}
                                <div class="text-muted fw-normal" style="font-size:.7rem;">{{ $p->unpaid_cnt }} transaksi</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($p->is_complete)
                                <x-ui.badge variant="success" size="sm">
                                    <iconify-icon icon="mdi:check-circle" class="me-1"></iconify-icon>Lunas
                                </x-ui.badge>
                            @elseif($p->is_overdue)
                                <x-ui.badge variant="danger" size="sm">
                                    <iconify-icon icon="mdi:alert-circle" class="me-1"></iconify-icon>Terlambat
                                </x-ui.badge>
                            @elseif($isCurrent)
                                <x-ui.badge variant="primary" size="sm">
                                    <iconify-icon icon="mdi:clock-outline" class="me-1"></iconify-icon>Berjalan
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="warning" size="sm">
                                    <iconify-icon icon="mdi:clock-alert" class="me-1"></iconify-icon>Belum Lunas
                                </x-ui.badge>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <x-ui.button href="{{ route('commission.periods.show', $p->period_start) }}" variant="outline" size="sm">
                                <iconify-icon icon="mdi:eye-outline" class="me-1"></iconify-icon>Detail
                            </x-ui.button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-5">
                            <x-ui.empty-state title="Belum ada data komisi." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
    </div>
</x-ui.card>

@endsection
