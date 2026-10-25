@extends('dashboard.index')

@section('title', 'Monitoring Semua Role')
@section('route-breadcrumb', 'owner-monitoring.index')
@section('breadcrumb', 'Monitoring Semua Role')

@section('content')

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <x-ui.stats-card label="Menunggu Approval Saya" :value="(string) $pendingByFlow->sum()" color="blue" accent>
            <iconify-icon icon="mdi:clipboard-check-outline"></iconify-icon>
        </x-ui.stats-card>
    </div>
    <div class="col-md-3">
        <x-ui.stats-card label="Klaim Karyawan Aktif" :value="(string) ($claimsByStatus->get('pending_approval', 0) + $claimsByStatus->get('approved', 0))" color="amber" accent>
            <iconify-icon icon="mdi:receipt-text-check-outline"></iconify-icon>
        </x-ui.stats-card>
    </div>
    <div class="col-md-3">
        <x-ui.stats-card label="Tugas Creative Berjalan" :value="(string) ($creativeByStatus->except('done')->sum())" color="purple" accent>
            <iconify-icon icon="mdi:palette-outline"></iconify-icon>
        </x-ui.stats-card>
    </div>
    <div class="col-md-3">
        <x-ui.stats-card label="Tugas Creative Overdue" :value="(string) $overdueCreativeTasks" color="red" accent>
            <iconify-icon icon="mdi:calendar-alert-outline"></iconify-icon>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <x-ui.card class="mb-3">
            <h6 class="mb-3"><iconify-icon icon="mdi:clipboard-check-outline" class="me-1"></iconify-icon>Antrian Approval</h6>
            @forelse($pendingByFlow as $flowName => $count)
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="small">{{ $flowName }}</span>
                    <x-ui.badge variant="warning">{{ $count }}</x-ui.badge>
                </div>
            @empty
                <p class="small text-muted mb-0">Tidak ada yang menunggu approval.</p>
            @endforelse
            <a href="{{ route('approvals.index') }}" class="small d-inline-block mt-2">Lihat semua →</a>
        </x-ui.card>

        <x-ui.card class="mb-3">
            <h6 class="mb-3"><iconify-icon icon="mdi:receipt-text-check-outline" class="me-1"></iconify-icon>Klaim Karyawan</h6>
            @php
                $claimLabels = ['pending_approval' => 'Menunggu Approval', 'approved' => 'Disetujui — Belum Cair', 'paid' => 'Sudah Dicairkan', 'rejected' => 'Ditolak'];
            @endphp
            @foreach($claimLabels as $key => $label)
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="small">{{ $label }}</span>
                    <span class="tab-nums fw-semibold">{{ $claimsByStatus->get($key, 0) }}</span>
                </div>
            @endforeach
        </x-ui.card>

        <x-ui.card>
            <h6 class="mb-3"><iconify-icon icon="mdi:palette-outline" class="me-1"></iconify-icon>Team Creative</h6>
            @php
                $creativeLabels = ['draft' => 'Draft', 'in_progress' => 'In Progress', 'review' => 'Menunggu Review', 'revision' => 'Revisi', 'done' => 'Selesai'];
            @endphp
            @foreach($creativeLabels as $key => $label)
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="small">{{ $label }}</span>
                    <span class="tab-nums fw-semibold">{{ $creativeByStatus->get($key, 0) }}</span>
                </div>
            @endforeach
            <a href="{{ route('creative-tasks.index') }}" class="small d-inline-block mt-2">Lihat papan tugas →</a>
        </x-ui.card>
    </div>

    <div class="col-lg-6">
        <x-ui.card class="mb-3">
            <h6 class="mb-3"><iconify-icon icon="mdi:earth" class="me-1"></iconify-icon>Export & Import</h6>
            <p class="small fw-semibold mb-1">Import (Bahan Baku)</p>
            @php $importLabels = ['ordered' => 'Dipesan', 'shipped' => 'Dikirim', 'customs' => 'Bea Cukai', 'received' => 'Diterima']; @endphp
            @foreach($importLabels as $key => $label)
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="small">{{ $label }}</span>
                    <span class="tab-nums fw-semibold">{{ $importByStatus->get($key, 0) }}</span>
                </div>
            @endforeach

            <p class="small fw-semibold mb-1 mt-3">Export (Produk Jadi)</p>
            @php $exportLabels = ['confirmed' => 'Dikonfirmasi', 'shipped' => 'Dikirim', 'customs' => 'Bea Cukai', 'delivered' => 'Diterima Buyer']; @endphp
            @foreach($exportLabels as $key => $label)
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="small">{{ $label }}</span>
                    <span class="tab-nums fw-semibold">{{ $exportByStatus->get($key, 0) }}</span>
                </div>
            @endforeach
            <div class="d-flex gap-3 mt-2">
                <a href="{{ route('import-orders.index') }}" class="small">Lihat Import →</a>
                <a href="{{ route('export-orders.index') }}" class="small">Lihat Export →</a>
            </div>
        </x-ui.card>

        <x-ui.card>
            <h6 class="mb-3"><iconify-icon icon="mdi:chart-line" class="me-1"></iconify-icon>Development</h6>

            @if($latestReport)
                <p class="small fw-semibold mb-1">Laporan Terbaru</p>
                <p class="small mb-1">{{ $latestReport->title }}</p>
                <p class="small text-muted mb-3">{{ $latestReport->period_start->translatedFormat('d M Y') }} — {{ $latestReport->period_end->translatedFormat('d M Y') }} oleh {{ $latestReport->author->name ?? '-' }}</p>
            @else
                <p class="small text-muted mb-3">Belum ada laporan berkala.</p>
            @endif

            @if($currentYearPlan)
                <p class="small fw-semibold mb-1">Rencana Tahun {{ now()->year }}</p>
                <p class="small mb-0">{{ $currentYearPlan->initiatives->count() }} inisiatif —
                    {{ $currentYearPlan->initiatives->where('status', 'done')->count() }} selesai,
                    {{ $currentYearPlan->initiatives->where('status', 'in_progress')->count() }} berjalan
                </p>
            @else
                <p class="small text-muted mb-0">Belum ada rencana tahunan untuk {{ now()->year }}.</p>
            @endif

            <div class="d-flex gap-3 mt-2">
                <a href="{{ route('development-reports.index') }}" class="small">Lihat Laporan →</a>
                <a href="{{ route('development-plans.index') }}" class="small">Lihat Rencana →</a>
            </div>
        </x-ui.card>
    </div>
</div>

@endsection
