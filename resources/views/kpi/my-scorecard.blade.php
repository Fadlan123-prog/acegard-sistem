@extends('layout.index')

@section('title', 'Skor KPI Saya')
@section('route-breadcrumb', route('kpi.my-scorecard'))
@section('breadcrumb', 'Skor KPI Saya')

@section('content')

<x-ui.card title="Skor KPI Saya" :subtitle="now()->month($month)->translatedFormat('F') . ' ' . $year">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:12px">
            <thead>
                <tr>
                    <th class="ps-0 py-2 text-muted fw-medium border-bottom">KPI</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Target</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Aktual</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Bobot</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Pencapaian</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($scores as $s)
                <tr>
                    <td class="ps-0 py-2 fw-medium">{{ $s->target->metric_label }}</td>
                    <td class="py-2 text-end">{{ rtrim(rtrim($s->target->target_value, '0'), '.') }}</td>
                    <td class="py-2 text-end">{{ rtrim(rtrim($s->actual_value, '0'), '.') }}</td>
                    <td class="py-2 text-end">{{ $s->target->weight }}%</td>
                    <td class="py-2 text-end">{{ $s->achievementPercent() }}%</td>
                    <td class="py-2 text-end">{{ $s->weightedScore() }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-4 text-center"><x-ui.empty-state title="Belum ada skor untuk periode ini" /></td></tr>
                @endforelse
            </tbody>
            @if($scores->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="py-2 text-end fw-semibold">Skor Total</td>
                    <td class="py-2 text-end fw-semibold">{{ round($totalScore, 1) }}%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-ui.card>

@endsection
