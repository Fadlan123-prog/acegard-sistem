@extends('layout.index')

@section('title', 'Target KPI')
@section('route-breadcrumb', route('kpi.targets.index'))
@section('breadcrumb', 'Target KPI per Divisi')

@php
    $period    = \Carbon\Carbon::create($year, $month, 1);
    $prev      = $period->copy()->subMonth();
    $next      = $period->copy()->addMonth();
    $totalKpi  = $targets->flatten(1)->count();
    $incomplete = $targets->filter(fn ($rows) => abs($rows->sum('weight') - 100) > 0.01)->count();
    $months    = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
@endphp

@section('content')

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <div class="ui-card-title">Target KPI per Divisi</div>
        <small class="text-muted">Target dan bobot KPI untuk periode yang dipilih. Total bobot tiap divisi idealnya 100%.</small>
    </div>
    <x-ui.button :href="route('kpi.targets.create', ['month' => $month, 'year' => $year])" variant="primary" size="sm">
        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Tambah Target
    </x-ui.button>
</div>

@include('kpi._period-filter', ['action' => route('kpi.targets.index')])

@if ($totalKpi > 0)
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><x-ui.stats-card label="Periode" :value="$months[$month].' '.$year" color="blue" accent /></div>
    <div class="col-6 col-xl-3"><x-ui.stats-card label="Divisi Punya Target" :value="(string) $targets->count()" color="purple" accent /></div>
    <div class="col-6 col-xl-3"><x-ui.stats-card label="Total KPI" :value="(string) $totalKpi" color="green" accent /></div>
    <div class="col-6 col-xl-3"><x-ui.stats-card label="Bobot Belum 100%" :value="$incomplete.' divisi'" :color="$incomplete ? 'red' : 'green'" accent /></div>
</div>
@endif

@forelse ($targets as $division => $rows)
@php
    $sum   = (float) $rows->sum('weight');
    $state = abs($sum - 100) <= 0.01 ? 'ok' : ($sum > 100 ? 'over' : 'warn');
@endphp
<x-ui.card :title="\App\Models\KpiTarget::DIVISIONS[$division] ?? $division" :subtitle="$rows->count().' KPI'" class="mb-3">
    <div class="d-flex align-items-center flex-wrap gap-2 mb-3" style="font-size:13px">
        <span class="fw-medium">Total bobot {{ rtrim(rtrim(number_format($sum, 2, '.', ''), '0'), '.') }}%</span>
        @if ($state === 'ok')
            <x-ui.badge variant="success">Lengkap</x-ui.badge>
        @elseif ($state === 'over')
            <x-ui.badge variant="danger">Lebih {{ rtrim(rtrim(number_format($sum - 100, 2, '.', ''), '0'), '.') }}%</x-ui.badge>
        @else
            <x-ui.badge variant="warning">Kurang {{ rtrim(rtrim(number_format(100 - $sum, 2, '.', ''), '0'), '.') }}%</x-ui.badge>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table bordered-table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-0 py-2 text-muted fw-medium border-bottom">Metric</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Target</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Bobot</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Arah</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom"><span class="visually-hidden">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $t)
                <tr>
                    <td class="ps-0 py-2 fw-medium">{{ $t->metric_label }}<div class="text-muted" style="font-size:11px">{{ $t->metric_key }}</div></td>
                    <td class="py-2 text-end">{{ rtrim(rtrim($t->target_value, '0'), '.') }}</td>
                    <td class="py-2 text-end">{{ rtrim(rtrim(number_format($t->weight, 2, '.', ''), '0'), '.') }}%</td>
                    <td class="py-2">
                        @if ($t->direction === 'lower_better')
                            <x-ui.badge variant="info" size="sm"><iconify-icon icon="mdi:arrow-down"></iconify-icon> Makin kecil makin baik</x-ui.badge>
                        @else
                            <x-ui.badge variant="primary" size="sm"><iconify-icon icon="mdi:arrow-up"></iconify-icon> Makin besar makin baik</x-ui.badge>
                        @endif
                    </td>
                    <td class="py-2 text-end">
                        <form action="{{ route('kpi.targets.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus target {{ e($t->metric_label) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-btn ui-btn-ghost ui-btn-sm" title="Hapus" aria-label="Hapus {{ $t->metric_label }}">
                                <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
@empty
<x-ui.empty-state title="Belum ada target KPI untuk {{ $months[$month] }} {{ $year }}" description="Tambahkan target per divisi agar skor KPI bulan ini bisa dihitung.">
    <x-ui.button :href="route('kpi.targets.create', ['month' => $month, 'year' => $year])" variant="primary" size="sm">
        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Tambah Target
    </x-ui.button>
</x-ui.empty-state>
@endforelse

@endsection
