@extends('dashboard.index')

@section('title', 'Pencairan Komisi')
@section('route-breadcrumb', 'commission.withdrawals.index')
@section('breadcrumb', 'Pencairan Komisi')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

@php
    $statusMap = [
        ''          => ['label' => 'Semua',     'badge' => 'secondary'],
        'draft'     => ['label' => 'Menunggu',  'badge' => 'warning'],
        'paid'      => ['label' => 'Disetujui', 'badge' => 'success'],
        'cancelled' => ['label' => 'Ditolak',   'badge' => 'danger'],
    ];
    $activeStatus = $filters['status'] ?? '';
@endphp

@if(isset($branch) && $branch)
<div class="d-flex align-items-center gap-2 mb-3">
    <iconify-icon icon="proicons-branch" style="font-size:16px;color:#64748b;"></iconify-icon>
    <span class="text-muted small">Menampilkan data cabang:</span>
    <span class="badge px-3 py-2 fw-semibold" style="background:#0f172a;color:#fff;font-size:.8rem;">{{ $branch->name }}</span>
</div>
@endif

{{-- STATUS TABS --}}
<x-ui.card class="mb-3">
    <div class="d-flex gap-2 flex-wrap align-items-center py-1">
        <span class="text-muted small fw-semibold me-1">Filter:</span>
        @foreach($statusMap as $val => $cfg)
            <a href="{{ route('commission.withdrawals.index', ['status' => $val]) }}"
               class="btn btn-sm {{ $activeStatus === $val ? 'btn-'.$cfg['badge'] : 'btn-outline-'.$cfg['badge'] }}">
                {{ $cfg['label'] }}
            </a>
        @endforeach
        <span class="ms-auto text-muted small">{{ $batches->total() }} pengajuan</span>
    </div>
</x-ui.card>

{{-- TABEL DESKTOP --}}
<x-ui.card class="d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Batch #</th>
                    <th>Karyawan</th>
                    <th>Periode</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Tanggal Ajuan</th>
                    <th class="text-center pe-3" style="width:110px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                <tr>
                    <td class="ps-3">
                        <span class="small font-monospace text-muted">{{ $batch->batch_number }}</span>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $batch->employee?->name ?? '-' }}</div>
                        <div class="text-muted small">{{ $batch->employee?->job_position ?? '-' }}</div>
                    </td>
                    <td class="small text-muted">
                        {{ $batch->period_from?->format('d M Y') }} –
                        {{ $batch->period_to?->format('d M Y') }}
                    </td>
                    <td class="text-end fw-semibold">
                        Rp{{ number_format($batch->total_amount) }}
                    </td>
                    <td>
                        @if($batch->status === 'draft')
                            <x-ui.badge variant="warning" size="sm">
                                <iconify-icon icon="mdi:clock-outline" class="me-1"></iconify-icon>Menunggu
                            </x-ui.badge>
                        @elseif($batch->status === 'paid')
                            <x-ui.badge variant="success" size="sm">
                                <iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>Disetujui
                            </x-ui.badge>
                        @elseif($batch->status === 'cancelled')
                            <x-ui.badge variant="danger" size="sm">
                                <iconify-icon icon="mdi:close-circle-outline" class="me-1"></iconify-icon>Ditolak
                            </x-ui.badge>
                        @endif
                    </td>
                    <td class="small text-muted">
                        {{ $batch->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                    </td>
                    <td class="text-center pe-3">
                        <div class="d-flex gap-1 justify-content-center">
                            <x-ui.button href="{{ route('commission.withdrawals.show', $batch->id) }}" variant="outline" size="sm" title="Lihat Detail">
                                <iconify-icon icon="mdi:eye-outline"></iconify-icon>
                            </x-ui.button>
                            @if($batch->status === 'draft')
                                <x-ui.button variant="success" size="sm" title="Setujui"
                                        data-bs-toggle="modal" data-bs-target="#approveModal"
                                        data-batch-id="{{ $batch->id }}"
                                        data-batch-number="{{ $batch->batch_number }}"
                                        data-employee="{{ $batch->employee?->name }}"
                                        data-amount="{{ number_format($batch->total_amount) }}">
                                    <iconify-icon icon="mdi:check"></iconify-icon>
                                </x-ui.button>
                                <x-ui.button variant="danger" size="sm" title="Tolak"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal"
                                        data-batch-id="{{ $batch->id }}"
                                        data-batch-number="{{ $batch->batch_number }}"
                                        data-employee="{{ $batch->employee?->name }}">
                                    <iconify-icon icon="mdi:close"></iconify-icon>
                                </x-ui.button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-5">
                        <x-ui.empty-state title="Tidak ada pengajuan ditemukan." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @slot('footer')
        @if($batches->hasPages())
            {{ $batches->links('pagination::bootstrap-5') }}
        @endif
    @endslot
</x-ui.card>

{{-- CARD LIST MOBILE --}}
<div class="d-md-none">
    @forelse($batches as $batch)
    <x-ui.card class="mb-2">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="fw-semibold">{{ $batch->employee?->name ?? '-' }}</div>
                <div class="text-muted small">{{ $batch->employee?->job_position ?? '-' }}</div>
                <div class="text-muted small font-monospace mt-1">{{ $batch->batch_number }}</div>
            </div>
            <div class="text-end">
                @if($batch->status === 'draft')
                    <x-ui.badge variant="warning" size="sm">Menunggu</x-ui.badge>
                @elseif($batch->status === 'paid')
                    <x-ui.badge variant="success" size="sm">Disetujui</x-ui.badge>
                @elseif($batch->status === 'cancelled')
                    <x-ui.badge variant="danger" size="sm">Ditolak</x-ui.badge>
                @endif
                <div class="fw-bold mt-1">Rp{{ number_format($batch->total_amount) }}</div>
            </div>
        </div>
        <div class="text-muted small mb-3">
            <iconify-icon icon="mdi:calendar-range" class="me-1"></iconify-icon>
            {{ $batch->period_from?->format('d M Y') }} – {{ $batch->period_to?->format('d M Y') }}
        </div>
        <div class="d-flex gap-2">
            <x-ui.button href="{{ route('commission.withdrawals.show', $batch->id) }}" variant="outline" size="sm" class="flex-fill">
                <iconify-icon icon="mdi:eye-outline" class="me-1"></iconify-icon>Detail
            </x-ui.button>
            @if($batch->status === 'draft')
                <x-ui.button variant="success" size="sm" class="flex-fill"
                        data-bs-toggle="modal" data-bs-target="#approveModal"
                        data-batch-id="{{ $batch->id }}"
                        data-batch-number="{{ $batch->batch_number }}"
                        data-employee="{{ $batch->employee?->name }}"
                        data-amount="{{ number_format($batch->total_amount) }}">
                    <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Setujui
                </x-ui.button>
                <x-ui.button variant="danger" size="sm" class="flex-fill"
                        data-bs-toggle="modal" data-bs-target="#rejectModal"
                        data-batch-id="{{ $batch->id }}"
                        data-batch-number="{{ $batch->batch_number }}"
                        data-employee="{{ $batch->employee?->name }}">
                    <iconify-icon icon="mdi:close" class="me-1"></iconify-icon>Tolak
                </x-ui.button>
            @endif
        </div>
    </x-ui.card>
    @empty
    <x-ui.empty-state title="Tidak ada pengajuan ditemukan." />
    @endforelse
    @if($batches->hasPages())
        <div class="mt-3">{{ $batches->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

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
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <p class="mb-1">Setujui pencairan untuk <strong id="approveEmployee">-</strong>?</p>
                    <p class="text-muted small mb-3">
                        Batch: <code id="approveBatch">-</code> &nbsp;·&nbsp;
                        Total: <strong class="text-success" id="approveAmount">-</strong>
                    </p>
                    <div>
                        <label class="form-label fw-semibold small">Catatan (opsional)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Catatan persetujuan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 gap-2">
                    <x-ui.button variant="outline" size="sm" data-bs-dismiss="modal">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="success" size="sm">
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
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <p class="mb-3">Tolak pencairan untuk <strong id="rejectEmployee">-</strong>?
                    <span class="text-muted small d-block">Batch: <code id="rejectBatch">-</code></span></p>
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
                    <x-ui.button variant="outline" size="sm" data-bs-dismiss="modal">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="danger" size="sm">
                        <iconify-icon icon="mdi:close" class="me-1"></iconify-icon>Konfirmasi Tolak
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('approveModal')?.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('approveEmployee').textContent = btn.dataset.employee;
        document.getElementById('approveBatch').textContent    = btn.dataset.batchNumber;
        document.getElementById('approveAmount').textContent   = 'Rp ' + btn.dataset.amount;
        document.getElementById('approveForm').action =
            '{{ url("commission/withdrawals") }}/' + btn.dataset.batchId + '/approve';
    });
    document.getElementById('rejectModal')?.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('rejectEmployee').textContent = btn.dataset.employee;
        document.getElementById('rejectBatch').textContent    = btn.dataset.batchNumber;
        document.getElementById('rejectForm').action =
            '{{ url("commission/withdrawals") }}/' + btn.dataset.batchId + '/reject';
    });
});
</script>

@endsection
