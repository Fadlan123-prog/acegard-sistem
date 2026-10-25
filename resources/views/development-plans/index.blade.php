@extends('dashboard.index')

@section('title', 'Rencana Tahunan')
@section('route-breadcrumb', 'development-plans.index')
@section('breadcrumb', 'Rencana Tahunan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Rencana Tahunan</h5>
    @if(auth()->user()->hasAnyRole(['development', 'superadmin']))
        <x-ui.button :href="route('development-plans.create')" variant="primary">
            <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Buat Rencana Tahun Baru
        </x-ui.button>
    @endif
</div>

<div class="row g-3">
    @forelse($plans as $plan)
        <div class="col-md-4">
            <x-ui.card>
                <h5 class="mb-2">{{ $plan->year }}</h5>
                <p class="small text-muted mb-2">{{ Str::limit($plan->summary, 120) }}</p>
                <p class="small mb-3">{{ $plan->initiatives->count() }} inisiatif — {{ $plan->initiatives->where('status', 'done')->count() }} selesai</p>
                <a href="{{ route('development-plans.show', $plan) }}" class="ui-btn ui-btn-sm ui-btn-secondary">Lihat Detail</a>
            </x-ui.card>
        </div>
    @empty
        <div class="col-12">
            <x-ui.card>
                <x-ui.empty-state icon='<iconify-icon icon="mdi:calendar-star" style="font-size:26px;opacity:.5"></iconify-icon>' title="Belum ada rencana tahunan" description="Buat rencana tahunan pertama untuk mulai melacak inisiatif pengembangan perusahaan." />
            </x-ui.card>
        </div>
    @endforelse
</div>

<div class="mt-3">{{ $plans->links() }}</div>

@endsection
