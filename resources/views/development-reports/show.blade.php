@extends('dashboard.index')

@section('title', $report->title)
@section('route-breadcrumb', 'development-reports.show')
@section('breadcrumb', $report->title)

@section('content')

<x-ui.card>
    <h5 class="mb-1">{{ $report->title }}</h5>
    <p class="text-muted small mb-3">
        Periode {{ $report->period_start->translatedFormat('d M Y') }} — {{ $report->period_end->translatedFormat('d M Y') }}
        · Oleh {{ $report->author->name ?? '-' }}
    </p>
    <div style="white-space: pre-line;">{{ $report->content }}</div>
</x-ui.card>

@endsection
