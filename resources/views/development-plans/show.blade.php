@extends('dashboard.index')

@section('title', 'Rencana Tahunan ' . $plan->year)
@section('route-breadcrumb', 'development-plans.show')
@section('breadcrumb', 'Rencana Tahunan ' . $plan->year)

@section('content')

<x-ui.card class="mb-3">
    <h5 class="mb-1">Rencana Tahunan {{ $plan->year }}</h5>
    <p class="small text-muted mb-2">Dibuat oleh {{ $plan->author->name ?? '-' }}</p>
    <div style="white-space: pre-line;">{{ $plan->summary }}</div>
</x-ui.card>

<x-ui.card class="mb-3">
    <h6 class="mb-3">Inisiatif</h6>

    @forelse($plan->initiatives as $initiative)
        @php
            $variant = match($initiative->status) {
                'planned' => 'secondary', 'in_progress' => 'info', 'done' => 'success', 'cancelled' => 'danger', default => 'secondary',
            };
        @endphp
        <div class="border rounded-3 p-3 mb-2 d-flex justify-content-between align-items-start">
            <div>
                <div class="fw-semibold">{{ $initiative->title }}</div>
                @if($initiative->description)<p class="small text-muted mb-1">{{ $initiative->description }}</p>@endif
                @if($initiative->target_date)<p class="small text-muted mb-0">Target: {{ $initiative->target_date->translatedFormat('d M Y') }}</p>@endif
            </div>
            <div class="text-end">
                <x-ui.badge :variant="$variant" class="mb-2">{{ \App\Models\DevelopmentAnnualPlanInitiative::$statusLabels[$initiative->status] }}</x-ui.badge>
                @if(auth()->user()->hasAnyRole(['development', 'superadmin']))
                <form method="POST" action="{{ route('development-plans.initiatives.status', [$plan, $initiative]) }}" class="d-flex gap-1">
                    @csrf @method('PUT')
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(\App\Models\DevelopmentAnnualPlanInitiative::$statusLabels as $key => $label)
                            <option value="{{ $key }}" @selected($initiative->status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
            </div>
        </div>
    @empty
        <p class="text-muted small mb-0">Belum ada inisiatif ditambahkan.</p>
    @endforelse
</x-ui.card>

@if(auth()->user()->hasAnyRole(['development', 'superadmin']))
<x-ui.card>
    <h6 class="mb-3">Tambah Inisiatif</h6>
    <form method="POST" action="{{ route('development-plans.initiatives.store', $plan) }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Judul Inisiatif" name="title" :required="true">
                    <input type="text" name="title" class="form-control" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3">
                <x-ui.form-field label="Target Tanggal" name="target_date">
                    <input type="date" name="target_date" class="form-control">
                </x-ui.form-field>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <x-ui.button type="submit" variant="primary" class="w-100 mb-3">Tambah</x-ui.button>
            </div>
        </div>
        <x-ui.form-field label="Deskripsi (opsional)" name="description">
            <textarea name="description" rows="2" class="form-control"></textarea>
        </x-ui.form-field>
    </form>
</x-ui.card>
@endif

@endsection
