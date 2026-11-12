@extends('layout.index')

@section('title', 'TikTok Live')
@section('route-breadcrumb', route('live-sessions.index'))
@section('breadcrumb', 'Dashboard TikTok Live')

@section('content')

@php $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <x-ui.button :href="route('live-sessions.create')" variant="primary" size="sm">
        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Catat Sesi Live
    </x-ui.button>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Total Live Hours" :value="$totalHours . ' jam'" color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Bulan ini</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Live Sessions" :value="number_format($totalSessions) . ' sesi'" color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Bulan ini</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Total Viewers (peak)" :value="number_format($totalViewers)" color="purple" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Jumlah peak tiap sesi</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Live Revenue" :value="$idr($totalRevenue)" color="green" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 2v8m0 0v2m0-2c-1.11 0-2.08-.402-2.599-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Bulan ini</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <x-ui.card title="Evaluasi Anggota Tim">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Anggota</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Jam Live</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Leads</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Closing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byHost as $h)
                        <tr>
                            <td class="ps-0 py-2 fw-medium">{{ $h->name }}</td>
                            <td class="py-2 text-end">{{ round($h->total_minutes / 60, 1) }}</td>
                            <td class="py-2 text-end">{{ $h->total_leads }}</td>
                            <td class="py-2 text-end">{{ $h->total_closing }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-4 text-center"><x-ui.empty-state title="Belum ada data" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</div>

<x-ui.card title="Riwayat Sesi Live">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:12px">
            <thead>
                <tr>
                    <th class="ps-0 py-2 text-muted fw-medium border-bottom">Tanggal</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Host</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Durasi</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Peak Viewers</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Leads</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Closing</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $s)
                <tr>
                    <td class="ps-0 py-2">{{ $s->session_date->format('d/m/Y') }}</td>
                    <td class="py-2 text-muted" style="font-size:11px">{{ $s->employee->name ?? '—' }}</td>
                    <td class="py-2 text-end">{{ $s->duration_hours }} jam</td>
                    <td class="py-2 text-end">{{ number_format($s->viewers_peak) }}</td>
                    <td class="py-2 text-end">{{ $s->leads_generated }}</td>
                    <td class="py-2 text-end">{{ $s->closing_count }}</td>
                    <td class="py-2 text-end">
                        <form action="{{ route('live-sessions.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus sesi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-btn ui-btn-ghost ui-btn-sm" title="Hapus">
                                <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-4 text-center"><x-ui.empty-state title="Belum ada sesi live tercatat" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $sessions->links() }}</div>
</x-ui.card>


<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <x-dashboard.donut id="donutLiveHours" title="Porsi Jam Live per Host" format="number"
            :labels="$byHost->pluck('name')->all()" :values="$byHost->map(fn ($h) => round($h->total_minutes / 60, 1))->all()" />
    </div>
    <div class="col-lg-6">
        <x-dashboard.donut id="donutLiveClosing" title="Porsi Closing per Host"
            :labels="$byHost->pluck('name')->all()" :values="$byHost->pluck('total_closing')->all()" />
    </div>
</div>

@endsection
