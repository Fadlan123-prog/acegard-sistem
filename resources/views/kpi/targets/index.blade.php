@extends('layout.index')

@section('title', 'Target KPI')
@section('route-breadcrumb', route('kpi.targets.index'))
@section('breadcrumb', 'Target KPI per Divisi')

@section('content')

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <x-ui.button :href="route('kpi.targets.create')" variant="primary" size="sm">
        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Tambah Target
    </x-ui.button>
</div>

@forelse ($targets as $division => $rows)
<x-ui.card :title="\App\Models\KpiTarget::DIVISIONS[$division] ?? $division" class="mb-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:12px">
            <thead>
                <tr>
                    <th class="ps-0 py-2 text-muted fw-medium border-bottom">Metric</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Target</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Bobot</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Arah</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $t)
                <tr>
                    <td class="ps-0 py-2 fw-medium">{{ $t->metric_label }}</td>
                    <td class="py-2 text-end">{{ rtrim(rtrim($t->target_value, '0'), '.') }}</td>
                    <td class="py-2 text-end">{{ $t->weight }}%</td>
                    <td class="py-2 text-muted" style="font-size:11px">{{ $t->direction === 'lower_better' ? 'Makin kecil makin baik' : 'Makin besar makin baik' }}</td>
                    <td class="py-2 text-end">
                        <form action="{{ route('kpi.targets.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus target ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-btn ui-btn-ghost ui-btn-sm" title="Hapus">
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
<x-ui.empty-state title="Belum ada target KPI untuk periode ini" />
@endforelse

@endsection
