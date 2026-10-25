@extends('dashboard.index')

@section('title', 'Team Creative')
@section('route-breadcrumb', 'creative-tasks.index')
@section('breadcrumb', 'Team Creative')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Papan Tugas</h5>
    @if(auth()->user()->hasAnyRole(['strategi_pengembangan', 'superadmin']))
        <x-ui.button :href="route('creative-tasks.create')" variant="primary">
            <iconify-icon icon="mdi:plus-box-outline" class="me-1"></iconify-icon>Buat Tugas Baru
        </x-ui.button>
    @endif
</div>

@php
    $columns = [
        'draft'       => 'Draft',
        'in_progress' => 'In Progress',
        'review'      => 'Menunggu Review',
        'revision'    => 'Revisi',
        'done'        => 'Selesai',
    ];
@endphp

<div class="row g-3">
    @foreach($columns as $key => $label)
        <div class="col-md">
            <x-ui.card>
                <h6 class="mb-3 d-flex justify-content-between align-items-center">
                    {{ $label }}
                    <span class="badge bg-secondary">{{ ($tasksByStatus[$key] ?? collect())->count() }}</span>
                </h6>

                @forelse(($tasksByStatus[$key] ?? collect()) as $task)
                    <a href="{{ route('creative-tasks.show', $task) }}" class="d-block text-decoration-none mb-2">
                        <div class="border rounded-3 p-2 {{ $task->isOverdue() ? 'border-danger' : '' }}">
                            <div class="fw-semibold small text-dark">{{ $task->title }}</div>
                            <div class="small text-muted">{{ $task->assignee->name ?? '-' }}</div>
                            @if($task->due_date)
                                <div class="small {{ $task->isOverdue() ? 'text-danger' : 'text-muted' }}">
                                    <iconify-icon icon="mdi:calendar-outline"></iconify-icon>
                                    {{ $task->due_date->translatedFormat('d M Y') }}
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="small text-muted mb-0">Tidak ada tugas.</p>
                @endforelse
            </x-ui.card>
        </div>
    @endforeach
</div>

<div class="mt-3">{{ $tasks->links() }}</div>

@endsection
