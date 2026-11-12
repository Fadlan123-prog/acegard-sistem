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

<style>
    .kt-toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin-bottom:16px}
    .kt-period{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
    .kt-nav{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border:1px solid var(--bs-border-color,#dee2e6);border-radius:8px;color:inherit;text-decoration:none;background:var(--bs-body-bg,#fff)}
    .kt-nav:hover{background:rgba(0,0,0,.04)}
    .kt-nav:focus-visible,.kt-del:focus-visible{outline:2px solid #2563eb;outline-offset:2px}
    .kt-sum{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:16px}
    .kt-sum>div{border:1px solid var(--bs-border-color,#dee2e6);border-radius:10px;padding:12px 14px}
    .kt-sum small{display:block;color:#6c757d;font-size:12px}
    .kt-sum strong{font-size:22px}
    .kt-weight{display:flex;align-items:center;gap:10px;margin-bottom:12px;font-size:12px}
    .kt-bar{flex:1;height:8px;background:rgba(0,0,0,.08);border-radius:99px;overflow:hidden;max-width:280px}
    .kt-bar>span{display:block;height:100%;background:#16a34a}
    .kt-bar.warn>span{background:#d97706}
    .kt-bar.over>span{background:#dc2626}
    .kt-dir{display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#6c757d}
    .kt-del{min-width:44px;min-height:44px}
    @media (max-width:576px){.kt-hide-sm{display:none}}
</style>

<div class="kt-toolbar">
    <form method="GET" action="{{ route('kpi.targets.index') }}" class="kt-period">
        <a class="kt-nav" href="{{ route('kpi.targets.index', ['month' => $prev->month, 'year' => $prev->year]) }}" aria-label="Bulan sebelumnya">
            <iconify-icon icon="mdi:chevron-left" width="20"></iconify-icon>
        </a>
        <label for="kt-month" class="visually-hidden">Bulan</label>
        <select id="kt-month" name="month" class="form-select" style="width:auto" onchange="this.form.submit()">
            @foreach ($months as $n => $label)
                <option value="{{ $n }}" @selected($n === $month)>{{ $label }}</option>
            @endforeach
        </select>
        <label for="kt-year" class="visually-hidden">Tahun</label>
        <input id="kt-year" type="number" name="year" min="2026" max="2100" value="{{ $year }}" class="form-control" style="width:96px" onchange="this.form.submit()">
        <a class="kt-nav" href="{{ route('kpi.targets.index', ['month' => $next->month, 'year' => $next->year]) }}" aria-label="Bulan berikutnya">
            <iconify-icon icon="mdi:chevron-right" width="20"></iconify-icon>
        </a>
        <noscript><button class="ui-btn ui-btn-secondary ui-btn-sm" type="submit">Tampilkan</button></noscript>
    </form>

    <x-ui.button :href="route('kpi.targets.create', ['month' => $month, 'year' => $year])" variant="primary" size="sm">
        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Tambah Target
    </x-ui.button>
</div>

@if ($totalKpi > 0)
<div class="kt-sum">
    <div><small>Periode</small><strong>{{ $months[$month] }} {{ $year }}</strong></div>
    <div><small>Divisi punya target</small><strong>{{ $targets->count() }}</strong></div>
    <div><small>Total KPI</small><strong>{{ $totalKpi }}</strong></div>
    <div><small>Bobot belum 100%</small><strong class="{{ $incomplete ? 'text-warning' : 'text-success' }}">{{ $incomplete }} divisi</strong></div>
</div>
@endif

@forelse ($targets as $division => $rows)
@php
    $sum   = (float) $rows->sum('weight');
    $state = abs($sum - 100) <= 0.01 ? 'ok' : ($sum > 100 ? 'over' : 'warn');
@endphp
<x-ui.card :title="\App\Models\KpiTarget::DIVISIONS[$division] ?? $division" :subtitle="$rows->count().' KPI'" class="mb-3">
    <div class="kt-weight">
        <span class="fw-medium">Total bobot {{ rtrim(rtrim(number_format($sum, 2, '.', ''), '0'), '.') }}%</span>
        <div class="kt-bar {{ $state === 'ok' ? '' : $state }}" role="progressbar" aria-valuenow="{{ min($sum, 100) }}" aria-valuemin="0" aria-valuemax="100" aria-label="Total bobot {{ $division }}">
            <span style="width:{{ min($sum, 100) }}%"></span>
        </div>
        @if ($state === 'ok')
            <span class="text-success"><iconify-icon icon="mdi:check-circle-outline"></iconify-icon> Lengkap</span>
        @elseif ($state === 'over')
            <span class="text-danger"><iconify-icon icon="mdi:alert-circle-outline"></iconify-icon> Lebih {{ rtrim(rtrim(number_format($sum - 100, 2, '.', ''), '0'), '.') }}%</span>
        @else
            <span class="text-warning"><iconify-icon icon="mdi:alert-outline"></iconify-icon> Kurang {{ rtrim(rtrim(number_format(100 - $sum, 2, '.', ''), '0'), '.') }}%</span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead>
                <tr>
                    <th class="ps-0 py-2 text-muted fw-medium border-bottom">Metric</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Target</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Bobot</th>
                    <th class="py-2 text-muted fw-medium border-bottom kt-hide-sm">Arah</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom"><span class="visually-hidden">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $t)
                <tr>
                    <td class="ps-0 py-2 fw-medium">{{ $t->metric_label }}<div class="text-muted" style="font-size:11px">{{ $t->metric_key }}</div></td>
                    <td class="py-2 text-end">{{ rtrim(rtrim($t->target_value, '0'), '.') }}</td>
                    <td class="py-2 text-end">{{ rtrim(rtrim(number_format($t->weight, 2, '.', ''), '0'), '.') }}%</td>
                    <td class="py-2 kt-hide-sm">
                        @if ($t->direction === 'lower_better')
                            <span class="kt-dir"><iconify-icon icon="mdi:arrow-down"></iconify-icon> Makin kecil makin baik</span>
                        @else
                            <span class="kt-dir"><iconify-icon icon="mdi:arrow-up"></iconify-icon> Makin besar makin baik</span>
                        @endif
                    </td>
                    <td class="py-2 text-end">
                        <form action="{{ route('kpi.targets.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus target {{ e($t->metric_label) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-btn ui-btn-ghost ui-btn-sm kt-del" title="Hapus" aria-label="Hapus {{ $t->metric_label }}">
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
