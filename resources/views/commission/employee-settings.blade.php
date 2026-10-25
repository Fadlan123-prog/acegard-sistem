@extends('dashboard.index')

@section('title', 'Setting Komisi Karyawan')
@section('route-breadcrumb', 'commission.settings.index')
@section('breadcrumb', 'Setting Komisi Karyawan')

@section('content')

@if(isset($branch) && $branch)
<div class="d-flex align-items-center gap-2 mb-3">
    <iconify-icon icon="proicons-branch" style="font-size:16px;color:#64748b;"></iconify-icon>
    <span class="text-muted small">Menampilkan data cabang:</span>
    <x-ui.badge variant="neutral" class="px-3 py-2 fw-semibold" style="font-size:.8rem;">{{ $branch->name }}</x-ui.badge>
</div>
@endif

<x-ui.alert type="info" class="mb-4">
    <x-slot name="title">Cara Kerja Setting Komisi</x-slot>
    <span class="small">
        Berlaku untuk komisi installer (teknisi/kenek pada job mobil &amp; PPF) dan marketing.
        <strong>Sistem Saat Ini</strong> memakai perhitungan komisi yang sudah ada (nominal tetap per jenis layanan untuk mobil, persentase untuk PPF).
        <strong>Persentase Kustom</strong> menimpa dengan persentase pilihan Anda dari harga invoice.
        <strong>Tidak Dapat Komisi</strong> tetap membuat baris komisi (supaya keterlibatan karyawan tercatat) tapi nominalnya dipaksa Rp0.
        Setting ini berlaku saat komisi invoice berikutnya dirilis — tidak mengubah komisi yang sudah terlanjur dirilis sebelumnya.
    </span>
</x-ui.alert>

<x-ui.card class="basic-data-table">
    @slot('header')
        <div>
            <div class="ui-card-title">Setting Komisi per Karyawan</div>
            <small class="text-muted">{{ $employees->count() }} karyawan aktif.</small>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Karyawan</th>
                    <th>Posisi</th>
                    <th style="width:220px;">Mode Komisi</th>
                    <th style="width:160px;">Persentase</th>
                    <th style="width:90px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                @php $formId = 'commission-form-' . $employee->id; @endphp
                <tr>
                    <td class="fw-semibold">{{ $employee->name }}</td>
                    <td class="text-muted small">{{ $employee->job_position ?? '-' }}</td>
                    <td>
                        <select name="commission_mode" form="{{ $formId }}" class="form-select form-select-sm commission-mode-select">
                            @foreach(\App\Models\Employee::$commissionModeLabels as $val => $label)
                                <option value="{{ $val }}" @selected($employee->commission_mode === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div class="input-group input-group-sm commission-percentage-wrap" style="{{ $employee->commission_mode !== 'percentage' ? 'visibility:hidden' : '' }}">
                            <input type="number" name="commission_percentage" form="{{ $formId }}" step="0.01" min="0.01" max="100"
                                   value="{{ $employee->commission_mode === 'percentage' ? rtrim(rtrim(number_format($employee->commission_percentage * 100, 4, '.', ''), '0'), '.') : '' }}"
                                   class="form-control form-control-sm text-center">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td>
                        <x-ui.button type="submit" form="{{ $formId }}" variant="outline" size="sm" title="Simpan">
                            <iconify-icon icon="mdi:check" style="font-size:14px;"></iconify-icon>
                        </x-ui.button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-5"><x-ui.empty-state title="Tidak ada karyawan aktif." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

{{-- Satu <form> kosong per baris, diasosiasikan ke input/select/tombol lewat atribut
     form="..." — supaya markup <table> tetap valid (form tidak boleh membungkus <td>
     langsung di dalam <tr>) sambil tiap baris tetap submit independen. --}}
@foreach($employees as $employee)
<form id="commission-form-{{ $employee->id }}" method="POST" action="{{ route('commission.settings.update', $employee) }}" class="d-none">
    @csrf
    @method('PUT')
</form>
@endforeach

<script>
document.querySelectorAll('.commission-mode-select').forEach(function (select) {
    function syncVisibility() {
        var wrap = select.closest('tr').querySelector('.commission-percentage-wrap');
        wrap.style.visibility = select.value === 'percentage' ? 'visible' : 'hidden';
    }
    select.addEventListener('change', syncVisibility);
});
</script>

@endsection
