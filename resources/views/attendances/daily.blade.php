@extends('dashboard.index')

@section('title', 'Absensi Harian')
@section('route-breadcrumb', 'attendances.daily')
@section('breadcrumb', 'Absensi Harian')

@push('page-styles')
<style>
.status-select option { font-weight: 600; }
.select-present    { background:#d1fae5; color:#065f46; border-color:#6ee7b7; font-weight:600; }
.select-off        { background:#f3f4f6; color:#374151; border-color:#d1d5db; font-weight:600; }
.select-sick       { background:#dbeafe; color:#1e40af; border-color:#93c5fd; font-weight:600; }
.select-permission { background:#fef3c7; color:#92400e; border-color:#fcd34d; font-weight:600; }
.select-absent     { background:#fee2e2; color:#991b1b; border-color:#fca5a5; font-weight:600; }
</style>
@endpush

@section('content')

@include('partials.form-errors')

{{-- Page Title --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Absensi Harian</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Input kehadiran karyawan per tanggal</p>
    </div>
    <div class="d-flex gap-2">
        <x-ui.button :href="route('attendances.report')" variant="outline" size="sm">Lap. Absensi</x-ui.button>
        <x-ui.button :href="route('attendances.monthlyReport')" variant="outline" size="sm">Lap. Gaji</x-ui.button>
    </div>
</div>

{{-- Filter --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('attendances.daily') }}" class="row g-3 align-items-end">
        <div class="col-12 col-sm-6 col-md-3">
            <label class="form-label fw-semibold">Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control">
        </div>
        <div class="col-12 col-sm-6 col-md-6">
            <label class="form-label fw-semibold">Cabang</label>
            <select name="branch_id" class="form-select">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (int)$branchId === (int)$branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}{{ $branch->city ? ' — ' . $branch->city : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <x-ui.button variant="primary" type="submit" class="w-100">Tampilkan</x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Stats --}}
@php
    $cntPresent    = $attendanceMap->where('status','present')->count();
    $cntOff        = $attendanceMap->where('status','off')->count();
    $cntSick       = $attendanceMap->where('status','sick')->count();
    $cntPermission = $attendanceMap->where('status','permission')->count();
    $cntAbsent     = $attendanceMap->where('status','absent')->count();
    $cntBelum      = max(0, $employees->count() - $attendanceMap->count());
@endphp

@php
    $statsColorMap = ['secondary'=>'blue','success'=>'green','info'=>'blue','warning'=>'amber','danger'=>'red'];
    $mdIcon = fn (string $name) => '<iconify-icon icon="'.$name.'"></iconify-icon>';
@endphp
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Total',   'val'=>$employees->count(), 'icon'=>'mdi:account-group',   'color'=>'secondary'],
        ['label'=>'Masuk',   'val'=>$cntPresent,         'icon'=>'mdi:account-check',   'color'=>'success'],
        ['label'=>'Libur',   'val'=>$cntOff,             'icon'=>'mdi:home-account',     'color'=>'secondary'],
        ['label'=>'Sakit',   'val'=>$cntSick,            'icon'=>'mdi:medical-bag',      'color'=>'info'],
        ['label'=>'Izin',    'val'=>$cntPermission,      'icon'=>'mdi:clipboard-text',   'color'=>'warning'],
        ['label'=>'Alpha',   'val'=>$cntAbsent,          'icon'=>'mdi:account-remove',   'color'=>'danger'],
    ] as $s)
    <div class="col-6 col-sm-4 col-md-2">
        <x-ui.stats-card
            :label="$s['label']"
            :value="$s['val']"
            :color="$statsColorMap[$s['color']] ?? 'blue'"
            :icon="$mdIcon($s['icon'])"
            accent
        />
    </div>
    @endforeach
</div>

{{-- Form --}}
<x-ui.card>
    @slot('header')
        <div class="d-flex align-items-center justify-content-between">
            <span class="fw-semibold">
                Daftar Karyawan —
                <span class="text-muted fw-normal" style="font-size:13px;">
                    {{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y') }}
                </span>
            </span>
            @if($cntBelum > 0)
                <x-ui.badge variant="warning">{{ $cntBelum }} belum diisi</x-ui.badge>
            @else
                <x-ui.badge variant="success">Semua sudah diisi</x-ui.badge>
            @endif
        </div>
    @endslot

    <form method="POST" action="{{ route('attendances.saveDaily') }}">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $branchId }}">
        <input type="hidden" name="attendance_date" value="{{ $date }}">

        @if($employees->isEmpty())
            <div class="py-5">
                <x-ui.empty-state title="Tidak ada karyawan aktif di cabang ini." />
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width:700px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;" class="ps-3">#</th>
                            <th>Nama</th>
                            <th class="d-none d-md-table-cell">Posisi</th>
                            <th class="d-none d-lg-table-cell">Gaji</th>
                            <th style="min-width:180px;">Status</th>
                            <th style="min-width:180px;" class="d-none d-sm-table-cell">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            @php
                                $att    = $attendanceMap->get($employee->id);
                                $status = old("attendances.$loop->index.status", $att?->status ?? 'present');
                                $notes  = old("attendances.$loop->index.notes",  $att?->notes  ?? '');
                            @endphp
                            <tr>
                                <td class="ps-3 text-muted" style="font-size:12px;">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $employee->name }}</span>
                                    <input type="hidden" name="attendances[{{ $loop->index }}][employee_id]" value="{{ $employee->id }}">
                                    <div class="d-sm-none text-muted" style="font-size:11px;">
                                        {{ $employee->job_position ?? '-' }}
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <x-ui.badge variant="neutral">{{ $employee->job_position ?? '-' }}</x-ui.badge>
                                </td>
                                <td class="text-muted d-none d-lg-table-cell" style="font-size:13px;">
                                    Rp{{ number_format($employee->monthly_salary ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    <select name="attendances[{{ $loop->index }}][status]"
                                            class="form-select form-select-sm select-{{ $status }}"
                                            onchange="styleSelect(this)">
                                        <option value="present"    {{ $status==='present'    ? 'selected':'' }}>✅ Masuk</option>
                                        <option value="off"        {{ $status==='off'        ? 'selected':'' }}>🏠 Libur</option>
                                        <option value="sick"       {{ $status==='sick'       ? 'selected':'' }}>🤒 Sakit</option>
                                        <option value="permission" {{ $status==='permission' ? 'selected':'' }}>📋 Izin</option>
                                        <option value="absent"     {{ $status==='absent'     ? 'selected':'' }}>❌ Alpha</option>
                                    </select>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <input type="text"
                                           name="attendances[{{ $loop->index }}][notes]"
                                           class="form-control form-control-sm"
                                           value="{{ $notes }}"
                                           placeholder="Catatan...">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex gap-2">
                <x-ui.button variant="primary" type="submit">Simpan Absensi</x-ui.button>
                <x-ui.button :href="route('attendances.daily')" variant="outline">Reset</x-ui.button>
            </div>
        @endif
    </form>
</x-ui.card>

@push('page-scripts')
<script>
function styleSelect(el) {
    el.className = el.className.replace(/select-\w+/, '');
    el.classList.add('form-select', 'form-select-sm', 'select-' + el.value);
}
document.querySelectorAll('select[name*="[status]"]').forEach(styleSelect);
</script>
@endpush

@endsection
