@extends('layout.index')

@section('title', 'Dashboard Content')
@section('route-breadcrumb', route('content.dashboard'))
@section('breadcrumb', 'Dashboard Content')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <x-ui.button :href="route('content.posts.create')" variant="primary" size="sm">
        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Catat Konten
    </x-ui.button>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Video Published" :value="number_format($totalPosts)" color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Bulan ini</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Total Views" :value="number_format($totalViews)" color="blue" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Seluruh platform</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="FYP Videos" :value="number_format($fypCount)" color="purple" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">≥ 100 rb views</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Organic Leads" :value="number_format($organicLeads)" color="green" accent
            icon='<svg viewBox="0 0 24 24" fill="none"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'>
            <span class="text-muted" style="font-size:11px">Dicatat manual per konten</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <x-ui.card title="Performa Konten" subtitle="10 teratas berdasarkan views">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Video</th>
                            <th class="py-2 text-muted fw-medium border-bottom">Creator</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Views</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Engagement</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Leads</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topPosts as $post)
                        <tr>
                            <td class="ps-0 py-2">
                                {{ $post->title }}
                                @if($post->is_fyp)<x-ui.badge variant="info" size="sm" class="ms-1">FYP</x-ui.badge>@endif
                            </td>
                            <td class="py-2 text-muted" style="font-size:11px">{{ $post->employee->name ?? '—' }}</td>
                            <td class="py-2 text-end">{{ number_format($post->views) }}</td>
                            <td class="py-2 text-end">{{ $post->engagement_rate !== null ? $post->engagement_rate.'%' : '—' }}</td>
                            <td class="py-2 text-end">{{ $post->leads_generated }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center"><x-ui.empty-state title="Belum ada konten bulan ini" /></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
    <div class="col-xl-5">
        <x-ui.card title="Performa per Creator">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:12px">
                    <thead>
                        <tr>
                            <th class="ps-0 py-2 text-muted fw-medium border-bottom">Creator</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Posting</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">Views</th>
                            <th class="py-2 text-end text-muted fw-medium border-bottom">FYP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byCreator as $c)
                        <tr>
                            <td class="ps-0 py-2 fw-medium">{{ $c->name }}</td>
                            <td class="py-2 text-end">{{ $c->total_posts }}</td>
                            <td class="py-2 text-end">{{ number_format($c->total_views) }}</td>
                            <td class="py-2 text-end">{{ $c->fyp_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center"><x-ui.empty-state title="Belum ada data" /></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</div>


<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <x-dashboard.donut id="donutContentViews" title="Porsi Views per Creator"
            :labels="$byCreator->pluck('name')->all()" :values="$byCreator->pluck('total_views')->all()" />
    </div>
    <div class="col-lg-6">
        <x-dashboard.donut id="donutContentPlatform" title="Porsi Video per Platform"
            :labels="array_map('ucfirst', array_keys($byPlatform))" :values="array_values($byPlatform)" />
    </div>
</div>

@endsection
