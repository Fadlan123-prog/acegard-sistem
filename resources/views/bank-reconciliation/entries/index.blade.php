@extends('layout.index')

@section('title', 'Rekonsiliasi — '.$account->account_name)
@section('route-breadcrumb', route('bank-accounts.index'))
@section('breadcrumb', 'Rekonsiliasi Bank')

@section('content')

@php
    $fmtRp = fn($v) => 'Rp '.number_format(abs($v), 0, ',', '.');
@endphp

{{-- Account Header --}}
<x-ui.card class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;border-radius:12px;background:rgba(99,102,241,.1);display:flex;align-items:center;justify-content:center;color:#6366f1;font-size:22px;flex-shrink:0">
                <iconify-icon icon="mdi:bank-outline"></iconify-icon>
            </div>
            <div>
                <div class="fw-bold" style="font-size:16px">{{ $account->account_name }}</div>
                <div class="text-muted small">{{ $account->bank_name }} · {{ $account->account_number }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div class="text-center">
                <div class="text-muted" style="font-size:11px">Saldo Berjalan</div>
                <div class="fw-bold" style="color:#6366f1;font-size:18px">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</div>
            </div>
            <div class="text-center">
                <div class="text-muted" style="font-size:11px">Belum Rekonsiliasi</div>
                <div class="fw-bold" style="color:{{ $summary['unreconciled'] > 0 ? '#f59e0b' : '#10b981' }};font-size:18px">
                    {{ $summary['unreconciled'] }}
                </div>
            </div>
            <x-ui.button :href="route('bank-accounts.entries.export', [$account->id, 'month'=>$filters['month']??'', 'status'=>$filters['status']??''])"
                variant="success" size="sm">
                <iconify-icon icon="mdi:file-excel-outline" class="me-1"></iconify-icon>Export
            </x-ui.button>
            <x-ui.button :href="route('bank-accounts.entries.create', $account->id)" variant="primary" size="sm">
                <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon>Tambah Mutasi
            </x-ui.button>
        </div>
    </div>
</x-ui.card>

{{-- Summary Tiles --}}
<div class="row g-3 mb-4">
    @php
        $mdIcon = fn (string $name) => '<iconify-icon icon="'.$name.'"></iconify-icon>';
        $tiles = [
            ['label'=>'Total Masuk',         'val'=>$fmtRp($summary['total_credit']), 'color'=>'green', 'icon'=>$mdIcon('mdi:arrow-down-circle-outline')],
            ['label'=>'Total Keluar',         'val'=>$fmtRp($summary['total_debit']),  'color'=>'red',   'icon'=>$mdIcon('mdi:arrow-up-circle-outline')],
            ['label'=>'Sudah Rekonsiliasi',   'val'=>$summary['reconciled'].' item',   'color'=>'green', 'icon'=>$mdIcon('mdi:checkbox-marked-circle-outline')],
            ['label'=>'Belum Rekonsiliasi',   'val'=>$summary['unreconciled'].' item', 'color'=>'amber', 'icon'=>$mdIcon('mdi:clock-outline')],
        ];
    @endphp
    @foreach ($tiles as $t)
    <div class="col-6 col-lg-3">
        <x-ui.stats-card :label="$t['label']" :value="$t['val']" :color="$t['color']" :icon="$t['icon']" accent />
    </div>
    @endforeach
</div>

{{-- Filter + Batch Reconcile --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('bank-accounts.entries', $account->id) }}"
          class="d-flex flex-wrap align-items-end gap-2" id="filterForm">

        <div style="min-width:150px">
            <input type="month" name="month" class="form-control form-control-sm"
                   value="{{ $filters['month'] ?? '' }}" onchange="this.form.submit()">
        </div>
        <div style="min-width:160px">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="unreconciled" {{ ($filters['status'] ?? '') === 'unreconciled' ? 'selected' : '' }}>Belum Rekonsiliasi</option>
                <option value="reconciled"   {{ ($filters['status'] ?? '') === 'reconciled'   ? 'selected' : '' }}>Sudah Rekonsiliasi</option>
            </select>
        </div>
        <x-ui.button :href="route('bank-accounts.entries', $account->id)" variant="outline" size="sm">Reset</x-ui.button>

        <div class="ms-auto d-flex gap-2">
            <x-ui.button type="button" id="selectAllBtn" variant="outline" size="sm">Pilih Semua</x-ui.button>
            <x-ui.button type="button" id="reconcileSelectedBtn" variant="success" size="sm" disabled>
                <iconify-icon icon="mdi:check-all" class="me-1"></iconify-icon>Rekonsiliasi Terpilih
            </x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Entries Table --}}
<form method="POST" action="{{ route('bank-accounts.reconcile-many', $account->id) }}" id="batchForm">
    @csrf
    <x-ui.card>
        @slot('footer')
            @if ($entries->hasPages())
                {{ $entries->links() }}
            @endif
        @endslot
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13px">
                <thead style="background:#fafafa">
                    <tr>
                        <th class="ps-4 py-3 border-bottom" style="width:36px">
                            <input type="checkbox" id="checkAll" class="form-check-input">
                        </th>
                        <th class="py-3 text-muted fw-medium border-bottom">Tanggal</th>
                        <th class="py-3 text-muted fw-medium border-bottom">Keterangan</th>
                        <th class="py-3 text-muted fw-medium border-bottom">Ref</th>
                        <th class="py-3 text-end text-muted fw-medium border-bottom" style="color:#10b981">Masuk (Cr)</th>
                        <th class="py-3 text-end text-muted fw-medium border-bottom" style="color:#ef4444">Keluar (Dr)</th>
                        <th class="py-3 text-end text-muted fw-medium border-bottom">Saldo</th>
                        <th class="py-3 text-center text-muted fw-medium border-bottom">Status</th>
                        <th class="py-3 pe-4 text-end text-muted fw-medium border-bottom">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                    <tr style="{{ $entry->is_reconciled ? 'background:rgba(16,185,129,.02)' : '' }}">
                        <td class="ps-4 py-2">
                            @if (!$entry->is_reconciled)
                            <input type="checkbox" name="ids[]" value="{{ $entry->id }}"
                                   class="form-check-input entry-check">
                            @endif
                        </td>
                        <td class="py-2">{{ $entry->entry_date->format('d/m/Y') }}</td>
                        <td class="py-2">
                            <div>{{ $entry->description }}</div>
                            @if ($entry->notes)
                                <div class="text-muted" style="font-size:11px">{{ $entry->notes }}</div>
                            @endif
                        </td>
                        <td class="py-2 text-muted" style="font-size:11px">{{ $entry->reference_number ?? '—' }}</td>
                        <td class="py-2 text-end fw-medium" style="color:{{ $entry->credit > 0 ? '#10b981' : '#9ca3af' }}">
                            {{ $entry->credit > 0 ? $fmtRp($entry->credit) : '—' }}
                        </td>
                        <td class="py-2 text-end fw-medium" style="color:{{ $entry->debit > 0 ? '#ef4444' : '#9ca3af' }}">
                            {{ $entry->debit > 0 ? $fmtRp($entry->debit) : '—' }}
                        </td>
                        <td class="py-2 text-end text-muted" style="font-size:12px">
                            {{ $entry->balance_after !== null ? 'Rp '.number_format($entry->balance_after, 0, ',', '.') : '—' }}
                        </td>
                        <td class="py-2 text-center">
                            @if ($entry->is_reconciled)
                                <x-ui.badge variant="success" size="sm">
                                    <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Rekonsiliasi
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="warning" size="sm">Menunggu</x-ui.badge>
                            @endif
                        </td>
                        <td class="py-2 pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <form method="POST" action="{{ route('bank-entries.reconcile', $entry->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $entry->is_reconciled ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            style="padding:3px 8px" title="{{ $entry->is_reconciled ? 'Batalkan' : 'Rekonsiliasi' }}">
                                        <iconify-icon icon="{{ $entry->is_reconciled ? 'mdi:close' : 'mdi:check' }}"></iconify-icon>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('bank-entries.destroy', $entry->id) }}">
                                    @csrf @method('DELETE')
                                    <x-ui.button type="submit" variant="danger-outline" size="sm"
                                            style="padding:3px 8px"
                                            onclick="return confirm('Hapus mutasi ini?')">
                                        <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                    </x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="py-5"><x-ui.empty-state title="Belum ada mutasi pada periode ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</form>

@push('page-scripts')
<script>
(function () {
    const checkAll     = document.getElementById('checkAll');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const reconcileBtn = document.getElementById('reconcileSelectedBtn');
    const batchForm    = document.getElementById('batchForm');

    function updateReconcileBtn() {
        const checked = document.querySelectorAll('.entry-check:checked').length;
        reconcileBtn.disabled = checked === 0;
        reconcileBtn.textContent = checked > 0
            ? `Rekonsiliasi ${checked} Item`
            : 'Rekonsiliasi Terpilih';
    }

    document.querySelectorAll('.entry-check').forEach(cb => {
        cb.addEventListener('change', updateReconcileBtn);
    });

    checkAll?.addEventListener('change', function () {
        document.querySelectorAll('.entry-check').forEach(cb => cb.checked = this.checked);
        updateReconcileBtn();
    });

    selectAllBtn?.addEventListener('click', function () {
        const checks = document.querySelectorAll('.entry-check');
        const allChecked = [...checks].every(cb => cb.checked);
        checks.forEach(cb => cb.checked = !allChecked);
        if (checkAll) checkAll.checked = !allChecked;
        updateReconcileBtn();
    });

    reconcileBtn?.addEventListener('click', function () {
        if (document.querySelectorAll('.entry-check:checked').length === 0) return;
        if (confirm('Rekonsiliasi item terpilih?')) batchForm.submit();
    });
})();
</script>
@endpush

@endsection
