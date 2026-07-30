@extends('dashboard.index')

@section('title', 'Uang Makan Mingguan')
@section('route-breadcrumb', 'meal-allowances.weekly')
@section('breadcrumb', 'Uang Makan Mingguan')

@push('page-styles')
<style>
.day-col { min-width: 72px; text-align: center; }
.badge-present { background: #d1fae5; color: #065f46; font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 5px; display: inline-block; }
.badge-absent  { background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 5px; display: inline-block; }
.badge-status  { font-size: 10px; color: #9ca3af; display: block; margin-top: 2px; }
.day-weekend   { background: #f9fafb; }
thead .day-weekend { background: #374151 !important; }
@media (max-width: 767px) {
    .card-header { flex-direction: column; align-items: flex-start !important; gap: 10px !important; }
    .card-header .d-flex { width: 100%; }
}
</style>
@endpush

@section('content')

{{-- Page Title --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Uang Makan Mingguan</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Periode dihitung otomatis Senin – Minggu berdasarkan absensi
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <x-ui.button href="{{ route('meal-allowances.weekly.pdf', ['branch_id' => $branchId, 'week_date' => $selectedDate]) }}" variant="danger" size="sm" target="_blank">
            <iconify-icon icon="mdi:file-pdf-box" class="me-1"></iconify-icon> Export PDF
        </x-ui.button>
        <x-ui.button type="button" onclick="window.print()" variant="outline" size="sm">
            <iconify-icon icon="mdi:printer" class="me-1"></iconify-icon> Print
        </x-ui.button>
    </div>
</div>

{{-- Filter --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('meal-allowances.weekly') }}" class="row g-3 align-items-end">
        <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label fw-semibold">Cabang</label>
            <select name="branch_id" class="form-select">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (int)$branchId === (int)$branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}{{ $branch->city ? ' — ' . $branch->city : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label fw-semibold">Tanggal dalam Minggu</label>
            <input type="date" name="week_date" class="form-control" value="{{ $selectedDate }}">
            <div class="form-text">Otomatis membaca Senin – Minggu</div>
        </div>
        <div class="col-12 col-sm-6 col-md-2">
            <x-ui.button type="submit" variant="primary" class="w-100">Tampilkan</x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <iconify-icon icon="mdi:calendar-week" class="text-primary" style="font-size:18px;"></iconify-icon>
                    <span class="text-muted" style="font-size:12px;">Periode</span>
                </div>
                <p class="fw-bold mb-0" style="font-size:14px;">
                    {{ $startDate->isoFormat('D MMM') }} – {{ $endDate->isoFormat('D MMM Y') }}
                </p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <iconify-icon icon="mdi:account-check" class="text-success" style="font-size:18px;"></iconify-icon>
                    <span class="text-muted" style="font-size:12px;">Total Hari Masuk</span>
                </div>
                <p class="fw-bold mb-0 text-success fs-4">{{ $totalPresentDays }} <small class="fs-6 fw-normal">hari</small></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <iconify-icon icon="mdi:cash" class="text-warning" style="font-size:18px;"></iconify-icon>
                    <span class="text-muted" style="font-size:12px;">Total Uang Makan</span>
                </div>
                <p class="fw-bold mb-0 text-warning fs-4">Rp{{ number_format($grandTotal, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Info --}}
<x-ui.alert type="info" class="mb-4" style="font-size:13px;">
    <div class="d-flex gap-2">
        <iconify-icon icon="mdi:information" style="font-size:18px; flex-shrink:0;"></iconify-icon>
        <span>
            <strong>Ketentuan:</strong>
            Teknisi <strong>Rp50.000/hari</strong> · Kenek <strong>Rp35.000/hari</strong> ·
            Marketing & posisi lain <strong>Rp0</strong> (tidak mendapat uang makan).
        </span>
    </div>
</x-ui.alert>

{{-- Table --}}
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Detail Uang Makan</span>
            <x-ui.badge variant="neutral">{{ count($mealRows) }} karyawan</x-ui.badge>
        </div>
    @endslot
    <div class="table-responsive" style="margin: -1rem -1.25rem; padding: 0;">
        <table class="table table-hover align-middle mb-0" style="min-width: 900px; font-size: 13px;">
            <thead class="table-dark sticky-top">
                <tr>
                    <th style="width:40px;" class="ps-3">#</th>
                    <th style="min-width:140px;">Nama</th>
                    <th>Posisi</th>
                    @foreach($weekDays as $day)
                        @php $isWe = in_array(\Carbon\Carbon::parse($day['date'])->dayOfWeek, [0,6]); @endphp
                        <th class="text-center day-col {{ $isWe ? 'day-weekend' : '' }}">
                            {{ $day['short'] }}<br>
                            <small style="font-size:9px; opacity:.7;">{{ $day['day'] }}</small>
                        </th>
                    @endforeach
                    <th class="text-center" style="min-width:60px;">Masuk</th>
                    <th class="text-end" style="min-width:90px;">Rate/Hari</th>
                    <th class="text-end pe-3" style="min-width:110px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mealRows as $index => $row)
                    @php $job = strtolower($row['job_position'] ?? ''); @endphp
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:12px;">{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $row['employee']->name }}</td>
                        <td>
                            @php
                                $badgeMap = ['teknisi'=>'primary','kenek'=>'success','marketing'=>'neutral'];
                                $badgeVariant = $badgeMap[$job] ?? 'neutral';
                            @endphp
                            <x-ui.badge :variant="$badgeVariant" style="font-weight:500;">
                                {{ $row['job_position'] ?? '-' }}
                            </x-ui.badge>
                        </td>
                        @foreach($weekDays as $day)
                            @php
                                $daily = $row['daily'][$day['date']] ?? null;
                                $isPresent = $daily['is_present'] ?? false;
                                $status    = $daily['status'] ?? null;
                                $isWe = in_array(\Carbon\Carbon::parse($day['date'])->dayOfWeek, [0,6]);
                            @endphp
                            <td class="text-center {{ $isWe ? 'day-weekend' : '' }}">
                                @if($isPresent)
                                    <span class="badge-present">✓</span>
                                @elseif($status)
                                    <span class="badge-absent">{{ strtoupper(substr($status,0,1)) }}</span>
                                    <span class="badge-status">{{ ucfirst($status) }}</span>
                                @else
                                    <span class="text-muted" style="font-size:13px;">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center fw-bold text-success">{{ $row['present_days'] }}</td>
                        <td class="text-end text-muted" style="font-size:12px;">
                            @if(!$row['employee']->meal_allowance_enabled)
                                <span class="badge bg-light text-danger border">Tidak dapat</span>
                            @else
                                Rp{{ number_format($row['daily_rate'], 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="text-end pe-3 fw-bold {{ $row['total_allowance'] > 0 ? 'text-success' : 'text-muted' }}">
                            Rp{{ number_format($row['total_allowance'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="py-5">
                            <x-ui.empty-state title="Tidak ada karyawan pada cabang ini." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($mealRows))
            <tfoot class="table-secondary fw-bold">
                <tr>
                    <td colspan="3" class="text-end ps-3">Grand Total</td>
                    @foreach($weekDays as $day)
                        @php $isWe = in_array(\Carbon\Carbon::parse($day['date'])->dayOfWeek, [0,6]); @endphp
                        <td class="{{ $isWe ? 'day-weekend' : '' }}"></td>
                    @endforeach
                    <td class="text-center text-success">{{ $totalPresentDays }}</td>
                    <td></td>
                    <td class="text-end pe-3 text-success">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-ui.card>

@push('page-styles')
<style>
@media print {
    .sidebar, .navbar, .breadcrumb, form, .btn, .alert { display: none !important; }
    body { background: #fff !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    table { font-size: 11px; }
}
</style>
@endpush

@endsection
