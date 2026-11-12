@extends('layout.index')

@section('title', 'Skor KPI')
@section('route-breadcrumb', route('kpi.scores.index'))
@section('breadcrumb', 'Skor KPI per Karyawan')

@section('content')

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

<div class="mb-3">
    <div class="ui-card-title">Skor KPI per Karyawan</div>
    <small class="text-muted">Input nilai aktual tiap KPI; pencapaian dan nilai berbobot dihitung otomatis.</small>
</div>

@include('kpi._period-filter', ['action' => route('kpi.scores.index'), 'withDivision' => true])

@if ($targets->isEmpty())
<x-ui.empty-state title="Belum ada target KPI untuk periode/divisi ini" description="Buat target KPI dulu agar skor bisa diinput.">
    <x-ui.button :href="route('kpi.targets.create', ['month' => $month, 'year' => $year])" variant="primary" size="sm">Tambah Target</x-ui.button>
</x-ui.empty-state>
@else
<x-ui.card title="Input Skor KPI">
    <form action="{{ route('kpi.scores.store') }}" method="POST" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4">
            <x-ui.form-field label="Karyawan" name="employee_id" :required="true">
                <select name="employee_id" class="form-select" required>
                    <option value="">Pilih karyawan</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </x-ui.form-field>
        </div>
        <div class="col-md-4">
            <x-ui.form-field label="KPI" name="kpi_target_id" :required="true">
                <select name="kpi_target_id" class="form-select" required>
                    <option value="">Pilih KPI</option>
                    @foreach ($targets->reject->isAuto() as $t)
                        <option value="{{ $t->id }}">{{ $t->division_label }} — {{ $t->metric_label }} (target {{ rtrim(rtrim($t->target_value, '0'), '.') }})</option>
                    @endforeach
                </select>
            </x-ui.form-field>
        </div>
        <div class="col-md-2">
            <x-ui.form-field label="Aktual" name="actual_value" :required="true">
                <input type="number" step="0.01" min="0" name="actual_value" class="form-control" required>
            </x-ui.form-field>
        </div>
        <div class="col-md-2">
            <x-ui.button type="submit" variant="primary" class="w-100">Simpan</x-ui.button>
        </div>
    </form>
</x-ui.card>

<x-ui.card title="Skor per Karyawan" class="mt-3">
    <div class="table-responsive">
        <table class="table bordered-table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-0 py-2 text-muted fw-medium border-bottom">Karyawan</th>
                    <th class="py-2 text-muted fw-medium border-bottom">KPI</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Aktual</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Pencapaian</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Nilai (bobot)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($scores as $employeeId => $rows)
                    @php $total = $rows->sum(fn($s) => $s->weightedScore()); @endphp
                    @foreach ($rows as $i => $s)
                    <tr>
                        <td class="ps-0 py-2 fw-medium">@if($i === 0){{ $s->employee->name }}@endif</td>
                        <td class="py-2">{{ $s->target->metric_label }} @if($s->is_auto)<x-ui.badge variant="info" size="sm">Otomatis</x-ui.badge>@endif</td>
                        <td class="py-2 text-end">{{ rtrim(rtrim($s->actual_value, '0'), '.') }}</td>
                        <td class="py-2 text-end">@include('kpi._achievement-badge', ['value' => $s->achievementPercent()])</td>
                        <td class="py-2 text-end">{{ $s->weightedScore() }}</td>
                    </tr>
                    @endforeach
                    <tr style="background:var(--ui-surface-2)">
                        <td colspan="4" class="py-2 text-end fw-semibold">Skor total</td>
                        <td class="py-2 text-end fw-semibold">@include('kpi._achievement-badge', ['value' => round($total, 1)])</td>
                    </tr>
                @empty
                <tr><td colspan="5" class="py-4 text-center"><x-ui.empty-state title="Belum ada skor tercatat" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>
@endif

@endsection
