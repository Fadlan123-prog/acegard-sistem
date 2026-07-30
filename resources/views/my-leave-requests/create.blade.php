@extends('layout.index')

@section('title', 'Buat Pengajuan')
@section('route-breadcrumb', route('my-leave-requests.index'))
@section('breadcrumb', 'Buat Pengajuan')

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <x-ui.button href="{{ route('my-leave-requests.index') }}" variant="outline" size="sm">
        <x-slot name="icon"><svg viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot>
        Kembali
    </x-ui.button>
    <div>
        <h5 class="mb-0 fw-semibold">Buat Pengajuan</h5>
        <div class="ui-text-muted" style="font-size:12px">{{ $employee->name }}</div>
    </div>
</div>

<x-ui.card style="max-width:640px">
    @slot('header')
        <div class="ui-card-title">Form Pengajuan Cuti / Libur / Izin</div>
    @endslot

    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-3">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form action="{{ route('my-leave-requests.store') }}" method="POST">
        @csrf

        {{-- Jenis --}}
        <x-ui.form-field label="Jenis Pengajuan" name="leave_type" :required="true">
            <select name="leave_type" id="leave_type" class="form-select" required>
                <option value="">-- Pilih Jenis --</option>
                @foreach($typeLabels as $key => $label)
                    <option value="{{ $key }}" {{ old('leave_type') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </x-ui.form-field>

        {{-- Info jatah libur (tampil hanya saat "Libur" dipilih) --}}
        <div id="libur_quota_info" class="mb-3" style="display:none">
            <div class="row g-2">
                <div class="col-4">
                    <div class="text-center p-2 rounded" style="background:var(--ui-surface-alt,#f8fafc);border:1px solid var(--ui-border)">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em" class="ui-text-muted mb-1">Jatah</div>
                        <div class="fw-bold" style="font-size:18px;color:var(--ui-primary,#3b82f6)">{{ $liburQuota['quota'] }}</div>
                        <div style="font-size:11px" class="ui-text-muted">hari/bulan</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center p-2 rounded" style="background:var(--ui-surface-alt,#f8fafc);border:1px solid var(--ui-border)">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em" class="ui-text-muted mb-1">Dipakai</div>
                        <div class="fw-bold" style="font-size:18px;color:var(--ui-amber,#f59e0b)">{{ $liburQuota['used'] }}</div>
                        <div style="font-size:11px" class="ui-text-muted">{{ $liburQuota['month'] }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-center p-2 rounded" style="background:var(--ui-surface-alt,#f8fafc);border:1px solid var(--ui-border)">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em" class="ui-text-muted mb-1">Sisa</div>
                        <div class="fw-bold" style="font-size:18px;color:{{ $liburQuota['remaining'] > 0 ? 'var(--ui-green,#10b981)' : 'var(--ui-red,#ef4444)' }}">{{ $liburQuota['remaining'] }}</div>
                        <div style="font-size:11px" class="ui-text-muted">hari</div>
                    </div>
                </div>
            </div>
            @if($liburQuota['remaining'] <= 0)
            <div class="mt-2 px-3 py-2 rounded d-flex align-items-center gap-2"
                 style="background:#fef2f2;border:1px solid #fecaca;font-size:12px;color:#dc2626">
                <iconify-icon icon="mdi:alert-circle-outline"></iconify-icon>
                Jatah libur bulan ini sudah habis. Pengajuan tetap bisa dikirim untuk dipertimbangkan admin.
            </div>
            @endif
        </div>

        {{-- Tanggal --}}
        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Dari Tanggal" name="from_date" :required="true">
                    <input type="date" name="from_date" id="from_date" class="form-control"
                           value="{{ old('from_date', now()->toDateString()) }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-6">
                <x-ui.form-field label="Sampai Tanggal" name="to_date" :required="true">
                    <input type="date" name="to_date" id="to_date" class="form-control"
                           value="{{ old('to_date', now()->toDateString()) }}" required>
                </x-ui.form-field>
            </div>
        </div>

        {{-- Durasi otomatis --}}
        <div class="mb-3">
            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded" style="background:var(--ui-surface-alt,#f8fafc);border:1px solid var(--ui-border);font-size:13px">
                <iconify-icon icon="mdi:calendar-check-outline" style="color:var(--ui-primary,#3b82f6)"></iconify-icon>
                <span class="ui-text-muted">Durasi:</span>
                <span class="fw-semibold" id="duration_label">— hari</span>
            </div>
        </div>

        {{-- Alasan --}}
        <x-ui.form-field label="Alasan / Keterangan" name="reason" :required="true">
            <textarea name="reason" id="reason" class="form-control" rows="3"
                      placeholder="Tuliskan alasan pengajuan Anda..." required>{{ old('reason') }}</textarea>
        </x-ui.form-field>

        <div class="d-flex gap-2 mt-1">
            <x-ui.button type="submit" variant="primary">
                <iconify-icon icon="mdi:send-outline" class="me-1"></iconify-icon> Ajukan Sekarang
            </x-ui.button>
            <x-ui.button href="{{ route('my-leave-requests.index') }}" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@push('scripts')
<script>
(function () {
    const from     = document.getElementById('from_date');
    const to       = document.getElementById('to_date');
    const lbl      = document.getElementById('duration_label');
    const typeEl   = document.getElementById('leave_type');
    const quotaBox = document.getElementById('libur_quota_info');

    function update() {
        const f = new Date(from.value), t = new Date(to.value);
        if (!from.value || !to.value || t < f) { lbl.textContent = '— hari'; return; }
        const days = Math.round((t - f) / 86400000) + 1;
        lbl.textContent = days + ' hari';
    }

    function toggleQuota() {
        quotaBox.style.display = typeEl.value === 'libur' ? 'block' : 'none';
    }

    from.addEventListener('change', function () {
        if (to.value && to.value < this.value) to.value = this.value;
        update();
    });
    to.addEventListener('change', update);
    typeEl.addEventListener('change', toggleQuota);

    update();
    toggleQuota();
})();
</script>
@endpush

@endsection
