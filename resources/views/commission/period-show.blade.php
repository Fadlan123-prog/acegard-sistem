@extends('dashboard.index')

@section('title', 'Detail Periode Komisi')
@section('route-breadcrumb', 'commission.periods.index')
@section('breadcrumb', 'Detail Periode')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

{{-- HEADER --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <x-ui.card class="h-100">
            <div class="text-muted small">Periode</div>
            <div class="fw-bold fs-5 mt-1">
                {{ $start->isoFormat('D MMM Y') }} — {{ $end->isoFormat('D MMM Y') }}
            </div>
            <div class="text-muted small mt-1">
                Jatuh tempo: <strong>{{ $dueOn->isoFormat('D MMM Y') }}</strong>
                @if($isOverdue)
                    <x-ui.badge variant="danger" size="sm" class="ms-1">Terlambat</x-ui.badge>
                @endif
            </div>
            @if($branch)
            <div class="mt-2">
                <span class="badge px-2 py-1" style="background:#0f172a;color:#fff;font-size:.72rem;">{{ $branch->name }}</span>
            </div>
            @endif
        </x-ui.card>
    </div>
    <div class="col-md-4">
        <x-ui.card class="h-100" style="background:#0f172a;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:rgba(255,255,255,.1);flex-shrink:0;">
                    <iconify-icon icon="mdi:cash-remove" style="font-size:26px;color:#f87171;"></iconify-icon>
                </div>
                <div>
                    <div class="small" style="color:#94a3b8;">Belum Dibayar</div>
                    <div class="fw-bold fs-4" style="color:{{ $totalUnpaid > 0 ? '#f87171' : '#4ade80' }};">
                        Rp{{ number_format($totalUnpaid, 0, ',', '.') }}
                    </div>
                    <div class="small" style="color:#64748b;">
                        {{ $employees->where('total_unpaid', '>', 0)->count() }} karyawan
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
    <div class="col-md-4">
        <x-ui.card class="h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:#dcfce7;flex-shrink:0;">
                    <iconify-icon icon="mdi:cash-check" style="font-size:26px;color:#16a34a;"></iconify-icon>
                </div>
                <div>
                    <div class="text-muted small">Sudah Dibayar</div>
                    <div class="fw-bold fs-4 text-success">Rp{{ number_format($totalPaid, 0, ',', '.') }}</div>
                    <div class="text-muted small">
                        {{ $employees->where('total_paid', '>', 0)->count() }} karyawan
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</div>

{{-- AKSI BAYAR SEMUA --}}
@if($totalUnpaid > 0)
<div class="d-flex justify-content-between align-items-center mb-3">
    <x-ui.button href="{{ route('commission.periods.index') }}" variant="outline" size="sm">
        <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
    </x-ui.button>
    <form method="POST" action="{{ route('commission.periods.pay-all', $period) }}"
          onsubmit="return confirm('Bayar semua komisi belum lunas di periode ini?\nTotal: Rp{{ number_format($totalUnpaid, 0, ',', '.') }}')">
        @csrf
        <x-ui.button type="submit" variant="success">
            <iconify-icon icon="mdi:cash-check" class="me-2"></iconify-icon>
            Bayar Semua — Rp{{ number_format($totalUnpaid, 0, ',', '.') }}
        </x-ui.button>
    </form>
</div>
@else
<div class="mb-3">
    <x-ui.button href="{{ route('commission.periods.index') }}" variant="outline" size="sm">
        <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
    </x-ui.button>
</div>
@endif

{{-- TABEL PER KARYAWAN --}}
<x-ui.card>
    @slot('header')
        <h6 class="mb-0 fw-semibold">Komisi Per Karyawan</h6>
    @endslot
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Karyawan</th>
                    <th class="text-end">Marketing</th>
                    <th class="text-end">Installer</th>
                    <th class="text-end">Total Belum Bayar</th>
                    <th class="text-end">Total Terbayar</th>
                    <th>Status</th>
                    <th class="pe-3" style="width:160px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td class="ps-3">
                        <div class="fw-semibold">{{ $emp->employee_name ?? '-' }}</div>
                        <div class="text-muted small">{{ $emp->job_position ?? '-' }}</div>
                    </td>
                    <td class="text-end">
                        @if($emp->marketing_unpaid > 0)
                            <span class="text-danger fw-semibold">Rp{{ number_format($emp->marketing_unpaid, 0, ',', '.') }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($emp->installer_unpaid > 0)
                            <span class="text-danger fw-semibold">Rp{{ number_format($emp->installer_unpaid, 0, ',', '.') }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($emp->total_unpaid > 0)
                            <span class="fw-bold text-danger">Rp{{ number_format($emp->total_unpaid, 0, ',', '.') }}</span>
                            <div class="text-muted" style="font-size:.7rem;">{{ $emp->unpaid_cnt }} transaksi</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($emp->total_paid > 0)
                            <span class="fw-semibold text-success">Rp{{ number_format($emp->total_paid, 0, ',', '.') }}</span>
                            @if($emp->last_paid_at)
                            <div class="text-muted" style="font-size:.7rem;">
                                {{ \Carbon\Carbon::parse($emp->last_paid_at)->isoFormat('D MMM Y, HH:mm') }}
                            </div>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($emp->total_unpaid <= 0)
                            <x-ui.badge variant="success" size="sm">
                                <iconify-icon icon="mdi:check-circle" class="me-1"></iconify-icon>Lunas
                            </x-ui.badge>
                        @else
                            <x-ui.badge variant="warning" size="sm">
                                <iconify-icon icon="mdi:clock-outline" class="me-1"></iconify-icon>Belum Lunas
                            </x-ui.badge>
                        @endif
                    </td>
                    <td class="pe-3">
                        @if($emp->total_unpaid > 0)
                        <form method="POST" action="{{ route('commission.periods.pay', $period) }}"
                              onsubmit="return confirm('Bayar komisi {{ $emp->employee_name }}?\nRp{{ number_format($emp->total_unpaid, 0, ',', '.') }}')">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $emp->employee_id }}">
                            <x-ui.button type="submit" variant="success" size="sm" class="w-100">
                                <iconify-icon icon="mdi:cash-check" class="me-1"></iconify-icon>
                                Tandai Lunas
                            </x-ui.button>
                        </form>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-5">
                        <x-ui.empty-state title="Tidak ada komisi di periode ini." />
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($employees->count())
            <tfoot class="table-light">
                <tr>
                    <td class="ps-3 fw-semibold">Total</td>
                    <td class="text-end fw-semibold text-danger">Rp{{ number_format($employees->sum('marketing_unpaid'), 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold text-danger">Rp{{ number_format($employees->sum('installer_unpaid'), 0, ',', '.') }}</td>
                    <td class="text-end fw-bold text-danger">Rp{{ number_format($totalUnpaid, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold text-success">Rp{{ number_format($totalPaid, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-ui.card>

@endsection
