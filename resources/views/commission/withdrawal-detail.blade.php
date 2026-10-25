@extends('dashboard.index')

@section('title', 'Detail Pencairan')
@section('route-breadcrumb', 'commission.withdrawals.index')
@section('breadcrumb', 'Detail Pencairan')

@section('content')

@php
    $kasbonDeducted = \App\Models\KasbonDeduction::where('commission_payment_batch_id', $batch->id)->sum('amount');
@endphp

@if($kasbonDeducted > 0)
<x-ui.alert type="warning" class="mb-3">
    <iconify-icon icon="mdi:cash-minus" class="me-1"></iconify-icon>
    Dipotong kasbon <strong>Rp{{ number_format($kasbonDeducted) }}</strong> dari pencairan ini —
    diterima bersih <strong>Rp{{ number_format($batch->total_amount - $kasbonDeducted) }}</strong>.
</x-ui.alert>
@endif

<div class="row g-3 mb-3">

    {{-- INFO KARYAWAN --}}
    <div class="col-md-4">
        <x-ui.card class="h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:50px;height:50px;background:#eff6ff;">
                    <iconify-icon icon="mdi:account-tie" style="font-size:26px;color:#1d4ed8;"></iconify-icon>
                </div>
                <div>
                    <div class="text-muted small">Karyawan</div>
                    <div class="fw-bold">{{ $batch->employee?->name ?? '-' }}</div>
                    <div class="text-muted small">{{ $batch->employee?->job_position ?? '-' }}</div>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- TOTAL --}}
    <div class="col-md-4">
        <x-ui.stats-card
            label="Total Pencairan"
            :value="'Rp' . number_format($batch->total_amount)"
            color="blue" accent
            class="h-100">
            <span>{{ $batch->commissions->count() }} komisi</span>
        </x-ui.stats-card>
    </div>

    {{-- STATUS + PERIODE --}}
    <div class="col-md-4">
        <x-ui.card class="h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-1">Status</div>
                    @if($batch->status === 'draft')
                        <x-ui.badge variant="warning" class="fs-6 px-3 py-2">
                            <iconify-icon icon="mdi:clock-outline" class="me-1"></iconify-icon>Menunggu
                        </x-ui.badge>
                    @elseif($batch->status === 'paid')
                        <x-ui.badge variant="success" class="fs-6 px-3 py-2">
                            <iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>Disetujui
                        </x-ui.badge>
                    @elseif($batch->status === 'cancelled')
                        <x-ui.badge variant="danger" class="fs-6 px-3 py-2">
                            <iconify-icon icon="mdi:close-circle-outline" class="me-1"></iconify-icon>Ditolak
                        </x-ui.badge>
                    @endif
                </div>
                <x-ui.button :href="route('commission.withdrawals.index')" variant="secondary" size="sm">
                    <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Kembali
                </x-ui.button>
            </div>
            <hr class="my-2">
            <div class="text-muted small">Periode</div>
            <div class="fw-semibold small">
                {{ $batch->period_from?->format('d M Y') }} – {{ $batch->period_to?->format('d M Y') }}
            </div>
            <div class="text-muted small mt-1">Ajuan: {{ $batch->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
            @if($batch->status === 'paid')
                <div class="text-muted small mt-1">Disetujui: {{ $batch->paidByUser?->name ?? '-' }}</div>
            @endif
        </x-ui.card>
    </div>
</div>

{{-- CATATAN --}}
@if($batch->requester_notes || $batch->notes)
<div class="row g-3 mb-3">
    @if($batch->requester_notes)
    <div class="col-md-6">
        <x-ui.alert type="info" class="mb-0">
            <x-slot name="title">Catatan dari Karyawan</x-slot>
            {{ $batch->requester_notes }}
        </x-ui.alert>
    </div>
    @endif
    @if($batch->notes)
    <div class="col-md-6">
        <x-ui.alert type="warning" class="mb-0">
            <x-slot name="title">Catatan Superadmin</x-slot>
            {{ $batch->notes }}
        </x-ui.alert>
    </div>
    @endif
</div>
@endif

{{-- AKSI (jika masih menunggu) --}}
@if($batch->isPending())
<div class="d-flex gap-2 mb-3">
    <x-ui.button variant="success" type="button" data-bs-toggle="modal" data-bs-target="#approveModal">
        <iconify-icon icon="mdi:check-circle-outline" class="me-2"></iconify-icon>Setujui Pencairan
    </x-ui.button>
    <x-ui.button variant="danger-outline" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal">
        <iconify-icon icon="mdi:close-circle-outline" class="me-2"></iconify-icon>Tolak Pencairan
    </x-ui.button>
</div>
@endif

{{-- RINCIAN KOMISI --}}
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">
                <iconify-icon icon="mdi:list-box-outline" class="me-2"></iconify-icon>
                Rincian Komisi
            </div>
            <x-ui.badge variant="neutral">{{ $batch->commissions->count() }} item</x-ui.badge>
        </div>
    @endslot
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="width:40px;">#</th>
                    <th>Customer</th>
                    <th>Invoice</th>
                    <th>Tipe Install</th>
                    <th class="text-end pe-3">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batch->commissions as $com)
                <tr>
                    <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-semibold small">{{ $com->customer?->name ?? '-' }}</div>
                        <div class="text-muted" style="font-size:.72rem;">
                            {{ $com->customer?->vehicle_brand }}
                            {{ $com->customer?->vehicle_model }}
                            @if($com->customer?->plat_number)
                                &nbsp;&bull;&nbsp;{{ $com->customer->plat_number }}
                            @endif
                        </div>
                    </td>
                    <td class="small text-muted font-monospace">{{ $com->invoice?->invoice_number ?? '-' }}</td>
                    <td>
                        <x-ui.badge variant="neutral" class="small border">
                            {{ strtoupper($com->install_type ?? $com->role ?? '-') }}
                        </x-ui.badge>
                    </td>
                    <td class="text-end pe-3 fw-semibold">
                        Rp{{ number_format($com->amount) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="4" class="ps-3 fw-semibold">Total</td>
                    <td class="text-end pe-3 fw-bold text-success fs-6">
                        Rp{{ number_format($batch->commissions->sum('amount')) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-ui.card>

@if($batch->isPending())
{{-- APPROVE MODAL --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:38px;height:38px;background:#dcfce7;">
                        <iconify-icon icon="mdi:check-circle-outline" style="font-size:22px;color:#16a34a;"></iconify-icon>
                    </div>
                    <h6 class="modal-title mb-0 fw-semibold">Setujui Pencairan</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('commission.withdrawals.approve', $batch->id) }}" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <p class="mb-3">Setujui pencairan <strong>Rp{{ number_format($batch->total_amount) }}</strong>
                    untuk <strong>{{ $batch->employee?->name }}</strong>?</p>
                    <div>
                        <label class="form-label fw-semibold small">Catatan (opsional)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Catatan persetujuan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 gap-2">
                    <x-ui.button variant="secondary" size="sm" type="button" data-bs-dismiss="modal">Batal</x-ui.button>
                    <x-ui.button variant="success" size="sm" type="submit">
                        <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Konfirmasi Setujui
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- REJECT MODAL --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:38px;height:38px;background:#fee2e2;">
                        <iconify-icon icon="mdi:close-circle-outline" style="font-size:22px;color:#dc2626;"></iconify-icon>
                    </div>
                    <h6 class="modal-title mb-0 fw-semibold">Tolak Pencairan</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('commission.withdrawals.reject', $batch->id) }}" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <p class="mb-3">Tolak pencairan untuk <strong>{{ $batch->employee?->name }}</strong>?</p>
                    <div>
                        <label class="form-label fw-semibold small">
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Tulis alasan penolakan..." required></textarea>
                        <div class="form-text text-muted">Komisi akan dikembalikan ke status eligible.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 gap-2">
                    <x-ui.button variant="secondary" size="sm" type="button" data-bs-dismiss="modal">Batal</x-ui.button>
                    <x-ui.button variant="danger" size="sm" type="submit">
                        <iconify-icon icon="mdi:close" class="me-1"></iconify-icon>Konfirmasi Tolak
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
