@extends('dashboard.index')

@section('title', 'Laporan Absensi Bulanan')
@section('route-breadcrumb', 'attendances.report')
@section('breadcrumb', 'Laporan Absensi Bulanan')

@push('page-styles')
<style>
/* Matrix cells */
.s-badge {
    display:inline-flex; align-items:center; justify-content:center;
    width:22px; height:20px; border-radius:4px;
    font-size:11px; font-weight:700; line-height:1;
}
.s-present    { background:#d1fae5; color:#065f46; }
.s-off        { background:#f3f4f6; color:#6b7280; }
.s-sick       { background:#dbeafe; color:#1e40af; }
.s-permission { background:#fef3c7; color:#92400e; }
.s-absent     { background:#fee2e2; color:#991b1b; }

/* Matrix table */
.matrix-table { border-collapse: collapse; width: 100%; }
.matrix-table th, .matrix-table td {
    padding: 7px 5px; text-align: center; vertical-align: middle;
    white-space: nowrap; border-right: 1px solid #e2e8f0;
}
.matrix-table thead th {
    background: #1e293b; color: #e2e8f0;
    font-size: 11px; font-weight: 600;
    position: sticky; top: 0; z-index: 3;
    border-right: 1px solid #334155; border-bottom: none;
}
.matrix-table tbody tr { border-bottom: 1px solid #f1f5f9; }
.matrix-table tbody tr:hover td { background: #f8fafc; }
.matrix-table tfoot td {
    background: #f8fafc; font-weight: 700; font-size: 12px;
    border-top: 2px solid #cbd5e1;
}

/* Sticky columns */
.col-no   { width: 36px; position: sticky; left: 0; z-index: 2; background: white; }
.col-name { min-width: 160px; text-align: left !important; position: sticky; left: 36px; z-index: 2; background: white; }
thead .col-no, thead .col-name { background: #1e293b !important; z-index: 4; }
tfoot .col-no, tfoot .col-name { background: #f8fafc !important; }
tbody tr:hover .col-no, tbody tr:hover .col-name { background: #f8fafc !important; }

/* Weekend */
.we-col { background: #f8fafc; color: #94a3b8; }
thead .we-col { background: #253347 !important; color: #64748b !important; }
tbody tr:hover .we-col { background: #f1f5f9 !important; }

/* Total columns separator */
.col-total-sep { border-left: 2px solid #475569 !important; }
thead .col-total-sep { border-left: 2px solid #475569 !important; }

/* Total column header — softer colors */
.th-present    { background: #14532d !important; color: #bbf7d0 !important; }
.th-off        { background: #1e293b !important; color: #cbd5e1 !important; }
.th-sick       { background: #1e3a8a !important; color: #bfdbfe !important; }
.th-permission { background: #78350f !important; color: #fde68a !important; }
.th-absent     { background: #7f1d1d !important; color: #fecaca !important; }

/* Total data cells */
.td-present    { color: #166534; font-weight: 700; }
.td-off        { color: #374151; font-weight: 700; }
.td-sick       { color: #1e40af; font-weight: 700; }
.td-permission { color: #92400e; font-weight: 700; }
.td-absent     { color: #991b1b; font-weight: 700; }
</style>
@endpush

@section('content')

@include('partials.form-errors')

@php
    $totalPresent=$totalOff=$totalSick=$totalPermission=$totalAbsent=0;
    foreach ($employees as $emp) {
        $r = $attendanceMap->get($emp->id, collect());
        $totalPresent    += $r->where('status','present')->count();
        $totalOff        += $r->where('status','off')->count();
        $totalSick       += $r->where('status','sick')->count();
        $totalPermission += $r->where('status','permission')->count();
        $totalAbsent     += $r->where('status','absent')->count();
    }
    $statsColorMap = ['secondary'=>'blue','success'=>'green','info'=>'blue','warning'=>'amber','danger'=>'red'];
@endphp

{{-- Page Title --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Laporan Absensi Bulanan</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            {{ $startDate->isoFormat('D MMMM Y') }} — {{ $endDate->isoFormat('D MMMM Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <x-ui.button variant="outline" size="sm" href="{{ route('attendances.daily') }}">Input Harian</x-ui.button>
        <x-ui.button variant="outline" size="sm" href="{{ route('attendances.monthlyReport', request()->query()) }}">Lap. Gaji</x-ui.button>
    </div>
</div>

{{-- Filter --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('attendances.report') }}" class="row g-3 align-items-end">
        <div class="col-12 col-sm-6 col-md-3">
            <x-ui.form-field label="Bulan" name="month">
                <input type="month" name="month" value="{{ $month }}" class="form-control">
            </x-ui.form-field>
        </div>
        <div class="col-12 col-sm-6 col-md-6">
            <x-ui.form-field label="Cabang" name="branch_id">
                <select name="branch_id" class="form-select">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (int)$branchId === (int)$branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}{{ $branch->city ? ' — ' . $branch->city : '' }}
                        </option>
                    @endforeach
                </select>
            </x-ui.form-field>
        </div>
        <div class="col-12 col-sm-6 col-md-3 d-flex gap-2">
            <x-ui.button variant="primary" type="submit">Filter</x-ui.button>
            <x-ui.button variant="outline" href="{{ route('attendances.report') }}">Reset</x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Karyawan','val'=>$employees->count(),'color'=>'secondary'],
        ['label'=>'Masuk',   'val'=>$totalPresent,      'color'=>'success'],
        ['label'=>'Libur',   'val'=>$totalOff,           'color'=>'secondary'],
        ['label'=>'Sakit',   'val'=>$totalSick,          'color'=>'info'],
        ['label'=>'Izin',    'val'=>$totalPermission,    'color'=>'warning'],
        ['label'=>'Alpha',   'val'=>$totalAbsent,        'color'=>'danger'],
    ] as $s)
    <div class="col-6 col-sm-4 col-md-2">
        <x-ui.stats-card :label="$s['label']" :value="$s['val']" :color="$statsColorMap[$s['color']] ?? 'blue'" class="h-100" />
    </div>
    @endforeach
</div>

{{-- Legend --}}
<div class="d-flex gap-3 flex-wrap mb-3 align-items-center" style="font-size:12.5px;">
    <span class="text-muted">Keterangan:</span>
    <span class="d-flex align-items-center gap-1"><span class="s-badge s-present">M</span> Masuk</span>
    <span class="d-flex align-items-center gap-1"><span class="s-badge s-off">L</span> Libur</span>
    <span class="d-flex align-items-center gap-1"><span class="s-badge s-sick">S</span> Sakit</span>
    <span class="d-flex align-items-center gap-1"><span class="s-badge s-permission">I</span> Izin</span>
    <span class="d-flex align-items-center gap-1"><span class="s-badge s-absent">A</span> Alpha</span>
    <span class="text-muted">· <span style="background:#f8fafc; border:1px solid #e2e8f0; padding:1px 6px; border-radius:3px; font-size:11px;">Sa/Mi</span> = Weekend</span>
</div>

{{-- Matrix --}}
<x-ui.card>
    @if($employees->isEmpty())
        <x-ui.empty-state title="Tidak ada karyawan aktif di cabang ini." />
    @else
    <div style="overflow:auto; max-height:620px; -webkit-overflow-scrolling:touch;">
        <table class="table matrix-table mb-0">
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th class="col-name ps-2">Nama Karyawan</th>
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php $dayDate = $startDate->copy()->day($d); @endphp
                        <th class="{{ $dayDate->isWeekend() ? 'we-col' : '' }}" style="min-width:28px;">
                            {{ $d }}<br><span style="font-size:8px;opacity:.6;">{{ $dayDate->format('D') }}</span>
                        </th>
                    @endfor
                    <th class="th-present col-total-sep" style="min-width:34px;">M</th>
                    <th class="th-off" style="min-width:34px;">L</th>
                    <th class="th-sick" style="min-width:34px;">S</th>
                    <th class="th-permission" style="min-width:34px;">I</th>
                    <th class="th-absent" style="min-width:34px;">A</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $idx => $emp)
                    @php
                        $rows = $attendanceMap->get($emp->id, collect());
                        $cnt  = ['present'=>0,'off'=>0,'sick'=>0,'permission'=>0,'absent'=>0];
                    @endphp
                    <tr>
                        <td class="col-no text-muted" style="font-size:11px;">{{ $idx+1 }}</td>
                        <td class="col-name ps-2">
                            <div class="fw-semibold" style="font-size:13px;">{{ $emp->name }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $emp->job_position ?? '-' }}</div>
                        </td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $key    = $startDate->copy()->day($d)->format('Y-m-d');
                                $record = $rows->get($key);
                                $status = $record?->status;
                                if ($status && isset($cnt[$status])) $cnt[$status]++;
                                $isWe = \Carbon\Carbon::parse($key)->isWeekend();
                            @endphp
                            <td class="{{ $isWe ? 'we-col' : '' }}">
                                @if($status)
                                    <span class="s-badge s-{{ $status }}"
                                          @if($record->notes) title="{{ $record->notes }}" style="cursor:help;" @endif>
                                        {{ $statusLabel[$status] }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:13px;">—</span>
                                @endif
                            </td>
                        @endfor
                        <td class="td-present col-total-sep">{{ $cnt['present'] }}</td>
                        <td class="td-off">{{ $cnt['off'] }}</td>
                        <td class="td-sick">{{ $cnt['sick'] }}</td>
                        <td class="td-permission">{{ $cnt['permission'] }}</td>
                        <td class="td-absent">{{ $cnt['absent'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="col-no"></td>
                    <td class="col-name ps-2 fw-bold" style="font-size:12px; text-align:left!important;">Total</td>
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $key = $startDate->copy()->day($d)->format('Y-m-d');
                            $cnt2 = $employees->filter(fn($e) => $attendanceMap->get($e->id, collect())->has($key))->count();
                            $isWe = \Carbon\Carbon::parse($key)->isWeekend();
                        @endphp
                        <td class="{{ $isWe ? 'we-col' : '' }}" style="font-size:11px; color:#94a3b8;">
                            {{ $cnt2 ?: '—' }}
                        </td>
                    @endfor
                    <td class="td-present col-total-sep">{{ $totalPresent }}</td>
                    <td class="td-off">{{ $totalOff }}</td>
                    <td class="td-sick">{{ $totalSick }}</td>
                    <td class="td-permission">{{ $totalPermission }}</td>
                    <td class="td-absent">{{ $totalAbsent }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</x-ui.card>

@endsection
