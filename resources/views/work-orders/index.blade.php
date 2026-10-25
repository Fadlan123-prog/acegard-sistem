@extends('layout.index')

@section('title', 'Penugasan Pekerjaan')
@section('route-breadcrumb', route('work-orders.index'))
@section('breadcrumb', 'Penugasan Pekerjaan')

@section('content')

{{-- Flash Messages --}}

@if (session('info'))
    <x-ui.alert type="info" class="mb-3">{{ session('info') }}</x-ui.alert>
@endif

{{-- Filters --}}
<x-ui.card class="mb-3">
    <form method="GET" action="{{ route('work-orders.index') }}" class="row g-2 align-items-end">

        @if ($isSuperadmin)
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-medium mb-1">Cabang</label>
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} ({{ $branch->city }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-12 col-sm-6 col-md-3">
            <label class="form-label small fw-medium mb-1">Status Pekerjaan</label>
            <select name="work_status" class="form-select form-select-sm">
                <option value="active" {{ $filters['work_status'] === 'active' ? 'selected' : '' }}>
                    Aktif (Pending &amp; Diterima)
                </option>
                <option value="unassigned" {{ $filters['work_status'] === 'unassigned' ? 'selected' : '' }}>
                    Belum Ditugaskan
                </option>
                <option value="completed" {{ $filters['work_status'] === 'completed' ? 'selected' : '' }}>
                    Selesai
                </option>
                <option value="pending_qc" {{ $filters['work_status'] === 'pending_qc' ? 'selected' : '' }}>
                    Menunggu QC
                </option>
                <option value="" {{ $filters['work_status'] === '' ? 'selected' : '' }}>
                    Semua
                </option>
            </select>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <label class="form-label small fw-medium mb-1">Teknisi</label>
            <select name="tukang_id" class="form-select form-select-sm">
                <option value="">Semua Teknisi</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}"
                        {{ $filters['tukang_id'] == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-sm-3 col-md-2">
            <x-ui.button type="submit" variant="primary" size="sm" class="w-100">
                <iconify-icon icon="mdi:filter-outline" class="me-1"></iconify-icon> Filter
            </x-ui.button>
        </div>
        <div class="col-6 col-sm-3 col-md-1">
            <x-ui.button :href="route('work-orders.index')" variant="outline" size="sm" class="w-100">
                Reset
            </x-ui.button>
        </div>

    </form>
</x-ui.card>

{{-- Summary Stats --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Pending"
            :value="$customers->getCollection()->filter(fn($c) => $c->invoice?->work_status === 'pending')->count()"
            color="amber" accent />
    </div>
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Diterima"
            :value="$customers->getCollection()->filter(fn($c) => $c->invoice?->work_status === 'accepted')->count()"
            color="blue" accent />
    </div>
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Selesai"
            :value="$customers->getCollection()->filter(fn($c) => $c->invoice?->work_status === 'completed')->count()"
            color="green" accent />
    </div>
    <div class="col-6 col-md-3">
        <x-ui.stats-card label="Menunggu QC" :value="$pendingQcCount" color="red" accent />
    </div>
</div>

{{-- Work Orders --}}
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Work Order</div>
            <span class="text-muted small">{{ $customers->total() }} order</span>
        </div>
    @endslot

    {{-- Desktop table --}}
    <div class="table-responsive d-none d-lg-block">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Customer &amp; Kendaraan</th>
                    @if ($isSuperadmin)
                        <th>Cabang</th>
                    @endif
                    <th>Tipe</th>
                    <th>Teknisi</th>
                    <th>Kenek</th>
                    <th>Invoice</th>
                    <th>Status</th>
                    <th class="text-center pe-3">Tugaskan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    @php
                        $invoice     = $customer->invoice;
                        $workStatus  = $invoice?->work_status ?? 'pending';
                        $canReassign = in_array($workStatus, ['pending', 'accepted']);
                        $wsBadge = match(true) {
                            $workStatus === 'pending'                              => ['warning', 'Pending'],
                            $workStatus === 'accepted'                             => ['primary', 'Diterima'],
                            $workStatus === 'completed' && $invoice?->qc_status === 'pending' => ['warning', 'Menunggu QC'],
                            $workStatus === 'completed' && $invoice?->qc_status === 'failed'  => ['danger', 'QC Ditolak'],
                            $workStatus === 'completed'                            => ['success', 'Selesai'],
                            $workStatus === 'cancelled'                            => ['danger', 'Batal'],
                            default                                                => ['neutral', $workStatus],
                        };
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $customer->name }}</div>
                            <div class="text-muted small">
                                {{ $customer->vehicle_brand }} {{ $customer->vehicle_model }}
                                &bull; {{ $customer->plat_number }}
                            </div>
                        </td>
                        @if ($isSuperadmin)
                            <td class="small text-muted">{{ $customer->branch?->name ?? '-' }}</td>
                        @endif
                        <td>
                            <x-ui.badge variant="neutral">{{ strtoupper($customer->install_type ?? '-') }}</x-ui.badge>
                        </td>
                        <td>
                            @if ($customer->tukang)
                                <span class="fw-medium">{{ $customer->tukang->name }}</span>
                            @else
                                <span class="text-danger small">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $customer->kenek?->name ?? '-' }}</td>
                        <td>
                            @if ($invoice)
                                <x-ui.badge :variant="$invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'neutral')">
                                    {{ $invoice->status === 'paid' ? 'Lunas' : ($invoice->status === 'partial' ? 'DP' : 'Belum Lunas') }}
                                </x-ui.badge>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            <x-ui.badge :variant="$wsBadge[0]">{{ $wsBadge[1] }}</x-ui.badge>
                        </td>
                        <td class="text-center pe-3">
                            @if ($canReassign)
                                <x-ui.button type="button" variant="outline" size="sm" class="btn-assign"
                                    data-assign-url="{{ route('work-orders.assign', $customer->id) }}"
                                    data-customer-name="{{ $customer->name }}"
                                    data-tukang-id="{{ $customer->tukang_id ?? '' }}"
                                    data-kenek-id="{{ $customer->kenek_id ?? '' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignModal">
                                    <iconify-icon icon="mdi:account-plus-outline" class="me-1"></iconify-icon>Tugaskan
                                </x-ui.button>
                            @elseif ($workStatus === 'completed' && $invoice?->qc_status === 'pending' && $isSupervisor)
                                <div class="d-flex gap-1 justify-content-center">
                                    <form method="POST" action="{{ route('work-orders.qc-approve', $customer->id) }}"
                                          onsubmit="return confirm('Setujui QC untuk order {{ addslashes($customer->name) }}?')">
                                        @csrf
                                        <x-ui.button type="submit" variant="success" size="sm" title="Setujui QC">
                                            <iconify-icon icon="mdi:check"></iconify-icon>
                                        </x-ui.button>
                                    </form>
                                    <x-ui.button type="button" variant="danger-outline" size="sm" class="btn-qc-reject"
                                        data-reject-url="{{ route('work-orders.qc-reject', $customer->id) }}"
                                        data-customer-name="{{ $customer->name }}"
                                        data-bs-toggle="modal" data-bs-target="#qcRejectModal"
                                        title="Tolak QC">
                                        <iconify-icon icon="mdi:close"></iconify-icon>
                                    </x-ui.button>
                                </div>
                            @elseif ($workStatus === 'completed')
                                <span class="text-success small"><iconify-icon icon="mdi:check-all"></iconify-icon> {{ $invoice?->qc_status === 'passed' ? 'QC Lulus' : 'Selesai' }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isSuperadmin ? 9 : 8 }}" class="py-5">
                            <x-ui.empty-state title="Tidak ada work order yang ditemukan." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile card list --}}
    <div class="d-lg-none">
        @forelse ($customers as $customer)
            @php
                $invoice     = $customer->invoice;
                $workStatus  = $invoice?->work_status ?? 'pending';
                $canReassign = in_array($workStatus, ['pending', 'accepted']);
                $wsBadge = match($workStatus) {
                    'pending'   => ['warning', 'Pending'],
                    'accepted'  => ['primary', 'Diterima'],
                    'completed' => ['success', 'Selesai'],
                    'cancelled' => ['danger', 'Batal'],
                    default     => ['neutral', $workStatus],
                };
            @endphp
            <div class="border-bottom p-3 {{ $loop->last ? 'border-0' : '' }}">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="fw-semibold">{{ $customer->name }}</div>
                        <div class="text-muted small">
                            {{ $customer->vehicle_brand }} {{ $customer->vehicle_model }}
                            &bull; {{ $customer->plat_number }}
                        </div>
                        @if ($isSuperadmin && $customer->branch)
                            <div class="text-muted small">
                                <iconify-icon icon="mdi:map-marker-outline"></iconify-icon> {{ $customer->branch->name }}
                            </div>
                        @endif
                    </div>
                    <div class="d-flex flex-column align-items-end gap-1">
                        <x-ui.badge :variant="$wsBadge[0]">{{ $wsBadge[1] }}</x-ui.badge>
                        @if ($invoice)
                            <x-ui.badge size="sm" :variant="$invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'neutral')">
                                {{ $invoice->status === 'paid' ? 'Lunas' : ($invoice->status === 'partial' ? 'DP' : 'Belum Lunas') }}
                            </x-ui.badge>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-2 small text-muted">
                    @if ($customer->install_type)
                        <span><iconify-icon icon="mdi:tools"></iconify-icon> {{ strtoupper($customer->install_type) }}</span>
                    @endif
                    <span>
                        <iconify-icon icon="mdi:account-outline"></iconify-icon>
                        @if ($customer->tukang)
                            {{ $customer->tukang->name }}
                        @else
                            <span class="text-danger">Belum ditugaskan</span>
                        @endif
                    </span>
                    @if ($customer->kenek)
                        <span><iconify-icon icon="mdi:account-arrow-right-outline"></iconify-icon> {{ $customer->kenek->name }}</span>
                    @endif
                </div>

                @if ($canReassign)
                    <x-ui.button type="button" variant="outline" size="sm" class="w-100 btn-assign"
                        data-assign-url="{{ route('work-orders.assign', $customer->id) }}"
                        data-customer-name="{{ $customer->name }}"
                        data-tukang-id="{{ $customer->tukang_id ?? '' }}"
                        data-kenek-id="{{ $customer->kenek_id ?? '' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#assignModal">
                        <iconify-icon icon="mdi:account-plus-outline" class="me-1"></iconify-icon> Tugaskan Teknisi
                    </x-ui.button>
                @elseif ($workStatus === 'completed' && $invoice?->qc_status === 'pending' && $isSupervisor)
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('work-orders.qc-approve', $customer->id) }}"
                              class="flex-grow-1" onsubmit="return confirm('Setujui QC untuk order {{ addslashes($customer->name) }}?')">
                            @csrf
                            <x-ui.button type="submit" variant="success" size="sm" class="w-100">
                                <iconify-icon icon="mdi:check" class="me-1"></iconify-icon> Setujui QC
                            </x-ui.button>
                        </form>
                        <x-ui.button type="button" variant="danger-outline" size="sm" class="flex-grow-1 btn-qc-reject"
                            data-reject-url="{{ route('work-orders.qc-reject', $customer->id) }}"
                            data-customer-name="{{ $customer->name }}"
                            data-bs-toggle="modal" data-bs-target="#qcRejectModal">
                            <iconify-icon icon="mdi:close" class="me-1"></iconify-icon> Tolak
                        </x-ui.button>
                    </div>
                @elseif ($workStatus === 'completed')
                    <div class="text-success small">
                        <iconify-icon icon="mdi:check-all"></iconify-icon> {{ $invoice?->qc_status === 'passed' ? 'QC Lulus' : 'Pekerjaan selesai' }}
                    </div>
                @endif
            </div>
        @empty
            <div class="py-5">
                <x-ui.empty-state title="Tidak ada work order yang ditemukan." />
            </div>
        @endforelse
    </div>

    @slot('footer'){{ $customers->links() }}@endslot
</x-ui.card>

{{-- Assignment Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">Tugaskan Teknisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="text-muted small">Order untuk:</div>
                        <div class="fw-semibold" id="modalCustomerName">-</div>
                    </div>

                    @if ($errors->any())
                        <x-ui.alert type="danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </x-ui.alert>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-medium">Teknisi</label>
                        <select name="tukang_id" id="modalTukangId" class="form-select">
                            <option value="">-- Lepas Penugasan --</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->name }}
                                    @if ($emp->job_position)
                                        ({{ $emp->job_position }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">Kosongkan untuk melepas teknisi dari order ini.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Kenek</label>
                        <select name="kenek_id" id="modalKenekId" class="form-select">
                            <option value="">-- Tanpa Kenek --</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->name }}
                                    @if ($emp->job_position)
                                        ({{ $emp->job_position }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-ui.alert type="info" class="mb-0 small">
                        <iconify-icon icon="mdi:information-outline" class="me-1"></iconify-icon>
                        Jika teknisi <strong>diganti atau dilepas</strong>, status order kembali ke
                        <strong>Pending</strong> — teknisi baru harus menerima order sebelum mulai bekerja.
                        Komisi teknisi lama akan dibatalkan.
                    </x-ui.alert>
                </div>
                <div class="modal-footer flex-column flex-sm-row gap-2">
                    <x-ui.button type="button" variant="outline" class="w-100 w-sm-auto" data-bs-dismiss="modal">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="primary" class="w-100 w-sm-auto">
                        <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon> Simpan Penugasan
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- QC Reject Modal --}}
@if ($isSupervisor)
<div class="modal fade" id="qcRejectModal" tabindex="-1" aria-labelledby="qcRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qcRejectModalLabel">Tolak QC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="qcRejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="text-muted small">Order untuk:</div>
                        <div class="fw-semibold" id="qcRejectCustomerName">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Catatan Perbaikan <span class="text-danger">*</span></label>
                        <textarea name="qc_notes" id="qcRejectNotes" class="form-control" rows="3"
                                  placeholder="Jelaskan apa yang perlu diperbaiki teknisi..." required></textarea>
                    </div>

                    <x-ui.alert type="warning" class="mb-0 small">
                        <iconify-icon icon="mdi:alert-outline" class="me-1"></iconify-icon>
                        Status pekerjaan akan kembali ke <strong>Diterima</strong> agar teknisi bisa memperbaikinya.
                    </x-ui.alert>
                </div>
                <div class="modal-footer flex-column flex-sm-row gap-2">
                    <x-ui.button type="button" variant="outline" class="w-100 w-sm-auto" data-bs-dismiss="modal">Batal</x-ui.button>
                    <x-ui.button type="submit" variant="danger" class="w-100 w-sm-auto">
                        <iconify-icon icon="mdi:close" class="me-1"></iconify-icon> Tolak QC
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const assignModal = document.getElementById('assignModal');
        const assignForm  = document.getElementById('assignForm');
        const modalName   = document.getElementById('modalCustomerName');
        const modalTukang = document.getElementById('modalTukangId');
        const modalKenek  = document.getElementById('modalKenekId');

        assignModal.addEventListener('show.bs.modal', function (event) {
            const btn      = event.relatedTarget;
            const url      = btn.dataset.assignUrl;
            const name     = btn.dataset.customerName;
            const tukangId = btn.dataset.tukangId;
            const kenekId  = btn.dataset.kenekId;

            modalName.textContent = name;
            assignForm.action     = url;
            modalTukang.value     = tukangId || '';
            modalKenek.value      = kenekId  || '';
        });

        const qcRejectModal = document.getElementById('qcRejectModal');
        if (qcRejectModal) {
            const qcRejectForm = document.getElementById('qcRejectForm');
            const qcRejectName = document.getElementById('qcRejectCustomerName');

            qcRejectModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                qcRejectName.textContent = btn.dataset.customerName;
                qcRejectForm.action      = btn.dataset.rejectUrl;
            });
        }
    });
</script>

@endsection
