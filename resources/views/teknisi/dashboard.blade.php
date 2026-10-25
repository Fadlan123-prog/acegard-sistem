@extends('layout.index')

@section('title', $title)
@section('route-breadcrumb', route('teknisi.dashboard'))
@section('breadcrumb', $isTeknisi ? 'Teknisi' : 'Kenek')

@section('content')

{{-- Flash Messages --}}

{{-- Job Assignment Notifications --}}
@if ($jobNotifications->isNotEmpty())
    <x-ui.card class="border-warning border-2 mb-3" id="jobNotifBanner">
        <div class="d-flex align-items-start gap-3">
            <div class="text-warning fs-3 flex-shrink-0">
                <iconify-icon icon="mdi:bell"></iconify-icon>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-warning fs-6 mb-1">
                    Ada {{ $jobNotifications->count() }} Penugasan Baru! Segera Terima Orderan
                </div>
                @foreach ($jobNotifications as $notif)
                    @php $d = $notif->data; @endphp
                    <div class="border rounded p-2 mb-2 bg-white small">
                        <div class="fw-semibold">{{ $d['customer_name'] ?? '-' }}</div>
                        <div class="text-muted">
                            {{ $d['vehicle'] ?? '' }}
                            @if (!empty($d['plat'])) &bull; {{ $d['plat'] }} @endif
                            @if (!empty($d['branch'])) &bull; <iconify-icon icon="mdi:map-marker-outline"></iconify-icon> {{ $d['branch'] }} @endif
                            @if (!empty($d['install_type'])) &bull; {{ strtoupper($d['install_type']) }} @endif
                        </div>
                    </div>
                @endforeach
                <x-ui.button variant="warning" size="sm" href="#pesanan-aktif" class="mt-1">
                    <iconify-icon icon="mdi:arrow-down" class="me-1"></iconify-icon> Lihat & Terima Order
                </x-ui.button>
            </div>
            <button type="button" class="btn-close flex-shrink-0" aria-label="Tutup"
                    onclick="document.getElementById('jobNotifBanner').remove()"></button>
        </div>
    </x-ui.card>
@endif

<x-dashboard.birthday-widget :birthdays="$upcomingBirthdays" />

@include('teknisi._incoming-sales-orders')

{{-- Header Info --}}
<x-ui.card class="mb-3">
    <div class="d-flex align-items-center gap-3">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:48px;height:48px;font-size:18px;font-weight:700">
            {{ strtoupper(substr($employee->name, 0, 1)) }}
        </div>
        <div class="flex-grow-1 min-width-0">
            <h6 class="mb-0 fw-semibold text-truncate">{{ $employee->name }}</h6>
            <span class="text-muted small">{{ ucfirst($employee->job_position ?? ($isTeknisi ? 'Teknisi' : 'Kenek')) }}</span>
        </div>
        <x-ui.badge variant="{{ $isTeknisi ? 'primary' : 'success' }}" class="px-3 py-2 flex-shrink-0">
            {{ $isTeknisi ? 'Teknisi' : 'Kenek' }}
        </x-ui.badge>
    </div>
</x-ui.card>

{{-- Stats Row --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Pesanan Aktif" :value="$jobs->count()" color="blue" accent />
    </div>
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Selesai" :value="$completedJobs->count()" color="green" accent />
    </div>
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Komisi Siap Cair" :value="$commissionSummary['eligible_count']" color="amber" accent>
            <span>Rp {{ number_format($commissionSummary['eligible_total']) }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Total Dibayar" :value="$commissionSummary['paid_count']" color="purple" accent>
            <span>Rp {{ number_format($commissionSummary['paid_total']) }}</span>
        </x-ui.stats-card>
    </div>
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Sisa Saldo Berobat {{ now()->year }}" :value="$healthBalance ? 'Rp ' . number_format($healthBalance->remaining()) : '-'" color="green" accent>
            @if($healthBalance)
                <span>dari Rp {{ number_format($healthBalance->allocated_amount) }}</span>
            @else
                <span>Belum tersedia — hubungi admin</span>
            @endif
        </x-ui.stats-card>
    </div>
</div>

{{-- Withdrawal Banner --}}
@php
    $wibDay = \Carbon\Carbon::now('Asia/Jakarta')->day;
@endphp

@if ($canRequestWithdrawal)
    <x-ui.card class="border-success border-2 mb-3">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-3">
            <div>
                <div class="fw-semibold text-success">
                    <iconify-icon icon="mdi:currency-usd-circle-outline" class="me-1"></iconify-icon>
                    Hari Pencairan — Tanggal 15
                </div>
                <div class="text-muted small mt-1">
                    Siap dicairkan: <strong>Rp {{ number_format($eligibleUnbatchedTotal) }}</strong>
                </div>
            </div>
            <x-ui.button variant="success" class="w-100 w-sm-auto"
                    data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                <iconify-icon icon="mdi:send-outline" class="me-1"></iconify-icon> Ajukan Pencairan
            </x-ui.button>
        </div>
    </x-ui.card>
@elseif ($pendingWithdrawal)
    <x-ui.alert type="warning" class="mb-3">
        <div class="d-flex align-items-start gap-2">
            <iconify-icon icon="mdi:clock-outline" class="fs-5 flex-shrink-0 mt-1"></iconify-icon>
            <div>
                <strong>Menunggu Persetujuan</strong><br>
                <span class="small">Batch <strong>{{ $pendingWithdrawal->batch_number }}</strong>
                — Rp {{ number_format($pendingWithdrawal->total_amount) }}</span><br>
                <span class="small text-muted">{{ $pendingWithdrawal->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
            </div>
        </div>
    </x-ui.alert>
@elseif ($latestWithdrawal && $latestWithdrawal->status === 'paid')
    <x-ui.alert type="success" class="mb-3">
        <div class="d-flex align-items-start gap-2">
            <iconify-icon icon="mdi:check-all" class="fs-5 flex-shrink-0 mt-1"></iconify-icon>
            <div>
                <strong>Pencairan Bulan Ini Disetujui</strong><br>
                <span class="small">Rp {{ number_format($latestWithdrawal->total_amount) }}
                — {{ $latestWithdrawal->paid_at?->timezone('Asia/Jakarta')->format('d M Y') }} WIB</span>
            </div>
        </div>
    </x-ui.alert>
@elseif ($latestWithdrawal && $latestWithdrawal->status === 'cancelled')
    <x-ui.alert type="danger" class="mb-3">
        <div class="d-flex align-items-start gap-2">
            <iconify-icon icon="mdi:close-circle-outline" class="fs-5 flex-shrink-0 mt-1"></iconify-icon>
            <div>
                <strong>Pengajuan Ditolak</strong><br>
                <span class="small">{{ $latestWithdrawal->notes ?? 'Tidak ada keterangan.' }}</span>
                @if ($commissionSummary['eligible_count'] > 0)
                    <br><span class="small">Komisi dikembalikan ke eligible. Hubungi superadmin.</span>
                @endif
            </div>
        </div>
    </x-ui.alert>
@elseif (!$isWithdrawalDay && $commissionSummary['eligible_count'] > 0)
    <x-ui.alert type="info" class="mb-3">
        <div class="d-flex align-items-start gap-2">
            <iconify-icon icon="mdi:calendar-outline" class="fs-5 flex-shrink-0 mt-1"></iconify-icon>
            <div class="small">
                <strong>{{ $commissionSummary['eligible_count'] }} komisi</strong> siap dicairkan
                (Rp {{ number_format($commissionSummary['eligible_total']) }}).
                Pencairan hanya pada <strong>tanggal 15</strong> setiap bulan (WIB).
                @if ($wibDay < 15)
                    Tersisa <strong>{{ 15 - $wibDay }} hari</strong> lagi.
                @else
                    Tanggal 15 bulan depan.
                @endif
            </div>
        </div>
    </x-ui.alert>
@endif

{{-- Active Jobs --}}
<x-ui.card id="pesanan-aktif" class="mb-3">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Pesanan Aktif
                @if ($jobs->count() > 0)
                    <x-ui.badge variant="primary" class="ms-1">{{ $jobs->count() }}</x-ui.badge>
                @endif
            </div>
        </div>
    @endslot
    @forelse ($jobs as $job)
        @php
            $invoice = $job->invoice;
            $isAccepted = $invoice && $invoice->work_status === 'accepted';
        @endphp
        <div class="border-bottom p-3 {{ $loop->last ? 'border-0' : '' }}">
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="fw-semibold">{{ $job->name }}</span>
                        @if ($invoice?->qc_status === 'failed')
                            <x-ui.badge variant="danger">QC Ditolak</x-ui.badge>
                        @elseif ($isAccepted)
                            <x-ui.badge variant="warning">Diterima</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">Menunggu</x-ui.badge>
                        @endif
                    </div>
                    @if ($invoice?->qc_status === 'failed')
                        <x-ui.alert type="danger" class="py-2 px-3 mb-2" style="font-size:.85rem;" :dismissible="false">
                            <iconify-icon icon="mdi:alert-outline" class="me-1"></iconify-icon>
                            <strong>Perlu perbaikan:</strong> {{ $invoice->qc_notes }}
                        </x-ui.alert>
                    @endif
                    <div class="text-muted small lh-lg">
                        <iconify-icon icon="mdi:car-outline"></iconify-icon>
                        {{ $job->vehicle_brand }} {{ $job->vehicle_model }} &bull; {{ $job->plat_number }}
                        @if ($job->branch)
                            <br><iconify-icon icon="mdi:map-marker-outline"></iconify-icon> {{ $job->branch->name }}
                        @endif
                        @if ($job->install_type)
                            &bull; {{ strtoupper($job->install_type) }}
                        @endif
                    </div>
                    @if ($invoice)
                        <div class="small text-muted mt-1">
                            Invoice: <strong>{{ $invoice->invoice_number ?? '-' }}</strong>
                            &bull;
                            @if ($invoice->status === 'paid')
                                <span class="text-success fw-medium">Lunas</span>
                            @elseif ($invoice->status === 'partial')
                                <span class="text-warning fw-medium">DP</span>
                            @else
                                <span class="text-muted">Belum Lunas</span>
                            @endif
                        </div>
                    @endif
                    @if ($isAccepted && $invoice?->accepted_at)
                        <div class="small text-success mt-1">
                            <iconify-icon icon="mdi:check"></iconify-icon>
                            Diterima {{ $invoice->accepted_at->format('d M Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>

            @if ($isTeknisi)
                <div class="d-flex gap-2 mt-2">
                    @if (!$isAccepted)
                        <form action="{{ route('teknisi.job.accept', $job->id) }}" method="POST" class="flex-fill">
                            @csrf
                            <x-ui.button variant="primary" size="sm" type="submit" class="w-100"
                                    onclick="return confirm('Terima order ini?')">
                                <iconify-icon icon="mdi:check"></iconify-icon> Terima Order
                            </x-ui.button>
                        </form>
                    @endif
                    <x-ui.button variant="success" size="sm" href="{{ route('teknisi.job.complete.form', $job->id) }}" class="flex-fill">
                        <iconify-icon icon="mdi:camera-outline"></iconify-icon> Selesaikan
                    </x-ui.button>
                </div>
            @endif
        </div>
    @empty
        <x-ui.empty-state title="Tidak ada pesanan aktif saat ini." />
    @endforelse
</x-ui.card>

{{-- Completed Jobs --}}
@if ($completedJobs->count())
    <x-ui.card class="mb-3">
        @slot('header')
            <div class="ui-card-title">Pekerjaan Selesai <span class="text-muted fw-normal">(10 Terakhir)</span></div>
        @endslot
        @foreach ($completedJobs as $job)
            @php $invoice = $job->invoice; @endphp
            <div class="border-bottom p-3 {{ $loop->last ? 'border-0' : '' }}">
                <div class="d-flex align-items-start gap-2">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-semibold">{{ $job->name }}</span>
                            <x-ui.badge variant="success">Selesai</x-ui.badge>
                        </div>
                        <div class="text-muted small">
                            {{ $job->vehicle_brand }} {{ $job->vehicle_model }} &bull; {{ $job->plat_number }}
                            @if ($job->branch)
                                &bull; {{ $job->branch->name }}
                            @endif
                        </div>
                        @if ($invoice?->completed_at)
                            <div class="small text-success mt-1">
                                <iconify-icon icon="mdi:check-all"></iconify-icon>
                                Selesai {{ $invoice->completed_at->format('d M Y H:i') }}
                            </div>
                        @endif
                    </div>
                    @if ($invoice?->job_photo || $invoice?->checklist_photo)
                        <div class="d-flex gap-1 flex-shrink-0">
                            @if ($invoice->job_photo)
                                <a href="{{ Storage::url($invoice->job_photo) }}" target="_blank">
                                    <img src="{{ Storage::url($invoice->job_photo) }}"
                                         class="rounded border"
                                         style="width:52px;height:52px;object-fit:cover"
                                         alt="Foto">
                                </a>
                            @endif
                            @if ($invoice->checklist_photo)
                                <a href="{{ Storage::url($invoice->checklist_photo) }}" target="_blank">
                                    <img src="{{ Storage::url($invoice->checklist_photo) }}"
                                         class="rounded border"
                                         style="width:52px;height:52px;object-fit:cover"
                                         alt="Checklist">
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </x-ui.card>
@endif

{{-- Commissions --}}
<x-ui.card class="mt-2">
    @slot('header')
        <div>
            <div class="ui-card-title mb-2">Riwayat Komisi</div>
            <div class="d-flex flex-wrap gap-1 mt-2">
                <x-ui.badge variant="warning" size="sm">
                    Pending: Rp {{ number_format($commissionSummary['pending_total']) }}
                </x-ui.badge>
                <x-ui.badge variant="success" size="sm">
                    Siap Cair: Rp {{ number_format($commissionSummary['eligible_total']) }}
                </x-ui.badge>
                <x-ui.badge variant="neutral" size="sm">
                    Dibayar: Rp {{ number_format($commissionSummary['paid_total']) }}
                </x-ui.badge>
            </div>
        </div>
    @endslot

    {{-- Desktop table --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Customer</th>
                    <th>No. Invoice</th>
                    <th>Tipe</th>
                    <th class="text-end">Nominal</th>
                    <th>Status</th>
                    <th>Pencairan</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($commissions as $commission)
                    @php
                        $badgeVariant = match($commission->status) {
                            'pending'  => 'warning',
                            'eligible' => 'success',
                            'paid'     => 'neutral',
                            default    => 'neutral',
                        };
                        $statusLabel = match($commission->status) {
                            'pending'  => 'Pending',
                            'eligible' => 'Siap Dibayar',
                            'paid'     => 'Sudah Dibayar',
                            default    => $commission->status,
                        };
                        $dateValue = $commission->paid_at ?? $commission->eligible_at ?? $commission->created_at;
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            @if ($commission->customer)
                                <div class="fw-medium">{{ $commission->customer->name }}</div>
                                <div class="text-muted small">
                                    {{ $commission->customer->vehicle_brand }}
                                    {{ $commission->customer->vehicle_model }}
                                    &bull; {{ strtoupper($commission->customer->install_type ?? '-') }}
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $commission->invoice?->invoice_number ?? '-' }}</td>
                        <td>
                            <x-ui.badge variant="neutral" size="sm">
                                {{ strtoupper($commission->install_type ?? '-') }}
                            </x-ui.badge>
                        </td>
                        <td class="text-end fw-semibold">Rp {{ number_format($commission->amount) }}</td>
                        <td><x-ui.badge :variant="$badgeVariant">{{ $statusLabel }}</x-ui.badge></td>
                        <td class="small">
                            @if ($commission->commission_payment_batch_id)
                                @php $batchStatus = $commission->batch?->status ?? 'draft'; @endphp
                                @if ($batchStatus === 'draft')
                                    <x-ui.badge variant="warning" size="sm">Menunggu</x-ui.badge>
                                @elseif ($batchStatus === 'paid')
                                    <x-ui.badge variant="success" size="sm">Disetujui</x-ui.badge>
                                @elseif ($batchStatus === 'cancelled')
                                    <x-ui.badge variant="danger" size="sm">Ditolak</x-ui.badge>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $dateValue ? \Carbon\Carbon::parse($dateValue)->format('d M Y') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data komisi.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($commissions->isNotEmpty())
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="ps-3 fw-semibold">Total Komisi Aktif</td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($commissions->whereNotIn('status', ['paid', 'cancelled'])->sum('amount')) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Mobile card list --}}
    <div class="d-md-none">
        @forelse ($commissions as $commission)
            @php
                $badgeVariant = match($commission->status) {
                    'pending'  => 'warning',
                    'eligible' => 'success',
                    'paid'     => 'neutral',
                    default    => 'neutral',
                };
                $statusLabel = match($commission->status) {
                    'pending'  => 'Pending',
                    'eligible' => 'Siap Dibayar',
                    'paid'     => 'Sudah Dibayar',
                    default    => $commission->status,
                };
                $dateValue = $commission->paid_at ?? $commission->eligible_at ?? $commission->created_at;
                $batchStatus = $commission->batch?->status ?? null;
            @endphp
            <div class="border-bottom p-3 {{ $loop->last ? 'border-0' : '' }}">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                    <div class="fw-medium">
                        {{ $commission->customer?->name ?? '-' }}
                    </div>
                    <div class="fw-semibold text-nowrap">Rp {{ number_format($commission->amount) }}</div>
                </div>
                <div class="text-muted small mb-2">
                    {{ $commission->customer?->vehicle_brand }}
                    {{ $commission->customer?->vehicle_model }}
                    @if ($commission->invoice?->invoice_number)
                        &bull; {{ $commission->invoice->invoice_number }}
                    @endif
                </div>
                <div class="d-flex flex-wrap align-items-center gap-1">
                    <x-ui.badge :variant="$badgeVariant">{{ $statusLabel }}</x-ui.badge>
                    @if ($commission->install_type)
                        <x-ui.badge variant="neutral" size="sm">{{ strtoupper($commission->install_type) }}</x-ui.badge>
                    @endif
                    @if ($commission->commission_payment_batch_id)
                        @if ($batchStatus === 'draft')
                            <x-ui.badge variant="warning" size="sm">Pencairan: Menunggu</x-ui.badge>
                        @elseif ($batchStatus === 'paid')
                            <x-ui.badge variant="success" size="sm">Pencairan: Disetujui</x-ui.badge>
                        @elseif ($batchStatus === 'cancelled')
                            <x-ui.badge variant="danger" size="sm">Pencairan: Ditolak</x-ui.badge>
                        @endif
                    @endif
                    @if ($dateValue)
                        <span class="text-muted" style="font-size:11px">
                            {{ \Carbon\Carbon::parse($dateValue)->format('d M Y') }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Belum ada data komisi." />
        @endforelse

        @if ($commissions->isNotEmpty())
            <div class="border-top bg-light px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="small fw-semibold">Total Komisi Aktif</span>
                <span class="fw-bold">
                    Rp {{ number_format($commissions->whereNotIn('status', ['paid', 'cancelled'])->sum('amount')) }}
                </span>
            </div>
        @endif
    </div>
</x-ui.card>

{{-- Withdrawal Request Modal --}}
@if ($canRequestWithdrawal)
<div class="modal fade" id="withdrawalModal" tabindex="-1" aria-labelledby="withdrawalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawalModalLabel">Ajukan Pencairan Komisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('teknisi.withdrawal.request') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="bg-light rounded p-3 mb-3 text-center">
                        <div class="text-muted small mb-1">Total yang akan dicairkan</div>
                        <div class="fs-3 fw-bold text-success">Rp {{ number_format($eligibleUnbatchedTotal) }}</div>
                        <div class="text-muted small">
                            {{ $commissions->where('status', 'eligible')->whereNull('commission_payment_batch_id')->count() }} komisi eligible
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Catatan (opsional)</label>
                        <textarea name="requester_notes" class="form-control" rows="2"
                                  placeholder="Catatan tambahan untuk superadmin..."></textarea>
                    </div>

                    <x-ui.alert type="warning" class="small mb-0">
                        <iconify-icon icon="mdi:information-outline" class="me-1"></iconify-icon>
                        Setelah diajukan, pencairan menunggu persetujuan superadmin.
                        Hanya bisa diajukan sekali per bulan pada tanggal 15 WIB.
                    </x-ui.alert>
                </div>
                <div class="modal-footer flex-column flex-sm-row gap-2">
                    <x-ui.button variant="outline" type="button" class="w-100 w-sm-auto" data-bs-dismiss="modal">Batal</x-ui.button>
                    <x-ui.button variant="success" type="submit" class="w-100 w-sm-auto"
                            onclick="return confirm('Ajukan pencairan komisi sebesar Rp {{ number_format($eligibleUnbatchedTotal) }}?')">
                        <iconify-icon icon="mdi:send-outline" class="me-1"></iconify-icon> Ajukan Sekarang
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
