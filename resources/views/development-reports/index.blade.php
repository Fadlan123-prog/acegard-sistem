@extends('dashboard.index')

@section('title', 'Laporan Berkala Development')
@section('route-breadcrumb', 'development-reports.index')
@section('breadcrumb', 'Laporan Berkala')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Laporan Berkala</h5>
    @if(auth()->user()->hasAnyRole(['development', 'superadmin']))
        <x-ui.button :href="route('development-reports.create')" variant="primary">
            <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Buat Laporan
        </x-ui.button>
    @endif
</div>

<x-ui.card>
    @if($reports->isEmpty())
        <x-ui.empty-state icon='<iconify-icon icon="mdi:chart-line" style="font-size:26px;opacity:.5"></iconify-icon>' title="Belum ada laporan" description="Laporan berkala perkembangan perusahaan akan muncul di sini." />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Judul</th><th>Periode</th><th>Dibuat Oleh</th><th></th></tr></thead>
                <tbody>
                    @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->title }}</td>
                            <td class="small">{{ $report->period_start->translatedFormat('d M Y') }} — {{ $report->period_end->translatedFormat('d M Y') }}</td>
                            <td class="small text-muted">{{ $report->author->name ?? '-' }}</td>
                            <td class="text-end"><a href="{{ route('development-reports.show', $report) }}" class="ui-btn ui-btn-sm ui-btn-secondary">Baca</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $reports->links() }}</div>
    @endif
</x-ui.card>

@endsection
