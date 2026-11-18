@props([
    'id',
    'title',
    'subtitle' => null,
    'labels' => [],
    'values' => [],
    'format' => 'number', // number | rp
    'height' => 260,
])
@php
    $labels = array_values($labels);
    $values = array_values(array_map('floatval', $values));
    $hasData = array_sum($values) > 0;
@endphp

<x-ui.card :title="$title" :subtitle="$subtitle" class="h-100">
    @if ($hasData)
        <div id="{{ $id }}" style="min-height:{{ $height }}px" role="img" aria-label="Diagram lingkaran {{ $title }}"></div>
    @else
        <x-ui.empty-state title="Belum ada data" />
    @endif
</x-ui.card>

@if ($hasData)
@push('page-scripts')
<script>
(function () {
    const fmt = {!! $format === 'rp'
        ? "v => 'Rp ' + Number(v).toLocaleString('id-ID')"
        : "v => Number(v).toLocaleString('id-ID')" !!};
    new ApexCharts(document.getElementById(@json($id)), {
        chart: { type: 'donut', height: {{ $height }}, fontFamily: 'inherit' },
        series: @json($values),
        labels: @json($labels),
        colors: ['#2563eb', '#16a34a', '#f59e0b', '#8b5cf6', '#ef4444', '#0891b2', '#64748b', '#db2777'],
        legend: { position: 'bottom', fontSize: '12px' },
        dataLabels: { enabled: true, formatter: v => Math.round(v) + '%' },
        tooltip: { y: { formatter: fmt } },
        stroke: { width: 2 },
        plotOptions: { pie: { donut: { size: '62%', labels: { show: true,
            total: { show: true, label: 'Total', formatter: w => fmt(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) }
        } } } },
    }).render();
})();
</script>
@endpush
@endif
