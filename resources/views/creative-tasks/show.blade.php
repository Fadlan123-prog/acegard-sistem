@extends('dashboard.index')

@section('title', $task->title)
@section('route-breadcrumb', 'creative-tasks.show')
@section('breadcrumb', $task->title)

@section('content')

@if($errors->any())
    <x-ui.alert type="danger" class="mb-3">{{ $errors->first() }}</x-ui.alert>
@endif

@php
    $statusVariant = match($task->status) {
        'draft'       => 'secondary',
        'in_progress' => 'info',
        'review'      => 'warning',
        'revision'    => 'danger',
        'done'        => 'success',
        default       => 'secondary',
    };
    $isAssigner = auth()->id() === $task->assigned_by || auth()->user()->hasRole('superadmin');
    $isAssignee = auth()->id() === $task->assigned_to || auth()->user()->hasRole('superadmin');
@endphp

<div class="row g-3">
    <div class="col-lg-8">
        <x-ui.card>
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h5 class="mb-0">{{ $task->title }}</h5>
                <x-ui.badge :variant="$statusVariant">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</x-ui.badge>
            </div>

            <p class="text-muted mb-1"><strong>Brief:</strong></p>
            <p style="white-space: pre-line;">{{ $task->brief }}</p>

            @if($task->department_context)
                <p class="small text-muted mb-0">Kebutuhan dari: {{ $task->department_context }}</p>
            @endif
        </x-ui.card>

        <x-ui.card class="mt-3">
            <h6 class="mb-3">Deliverable</h6>

            @forelse($task->deliverables as $deliverable)
                <div class="border rounded-3 p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Versi {{ $deliverable->version }}</strong>
                        <span class="small text-muted">{{ $deliverable->created_at->translatedFormat('d M Y, H:i') }} — {{ $deliverable->uploader->name ?? '-' }}</span>
                    </div>
                    @if($deliverable->note)
                        <p class="small mb-1 mt-1">{{ $deliverable->note }}</p>
                    @endif
                    <a href="{{ Storage::url($deliverable->file_path) }}" target="_blank" class="small">
                        <iconify-icon icon="mdi:open-in-new"></iconify-icon> Lihat File
                    </a>
                </div>
            @empty
                <p class="text-muted small mb-0">Belum ada deliverable diunggah.</p>
            @endforelse

            @if($isAssignee && in_array($task->status, ['in_progress', 'revision']))
                <hr>
                <form method="POST" action="{{ route('creative-tasks.deliverables.store', $task) }}" enctype="multipart/form-data">
                    @csrf
                    <x-ui.form-field label="Upload Deliverable Baru" name="file" :required="true">
                        <input type="file" name="file" accept="image/*,video/*" class="form-control" required>
                    </x-ui.form-field>
                    <x-ui.form-field label="Catatan (opsional)" name="note">
                        <textarea name="note" rows="2" class="form-control"></textarea>
                    </x-ui.form-field>
                    <x-ui.button type="submit" variant="primary">Kirim untuk Review</x-ui.button>
                </form>
            @endif
        </x-ui.card>
    </div>

    <div class="col-lg-4">
        <x-ui.card>
            <dl class="row mb-0 small">
                <dt class="col-5">Diberikan Oleh</dt>
                <dd class="col-7">{{ $task->assigner->name ?? '-' }}</dd>
                <dt class="col-5">Ditugaskan Ke</dt>
                <dd class="col-7">{{ $task->assignee->name ?? '-' }}</dd>
                <dt class="col-5">Prioritas</dt>
                <dd class="col-7 text-capitalize">{{ $task->priority }}</dd>
                <dt class="col-5">Deadline</dt>
                <dd class="col-7">{{ $task->due_date?->translatedFormat('d M Y') ?? '-' }}</dd>
            </dl>
        </x-ui.card>

        @if($isAssigner && $task->status === 'review')
        <x-ui.card class="mt-3">
            <h6 class="mb-2">Review Deliverable</h6>
            <form method="POST" action="{{ route('creative-tasks.approve', $task) }}" class="mb-2">
                @csrf
                <x-ui.button type="submit" variant="success" class="w-100">
                    <iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>Setujui — Selesai
                </x-ui.button>
            </form>
            <form method="POST" action="{{ route('creative-tasks.revision', $task) }}">
                @csrf
                <textarea name="note" rows="2" class="form-control form-control-sm mb-2" placeholder="Catatan revisi..." required></textarea>
                <x-ui.button type="submit" variant="danger" class="w-100">
                    <iconify-icon icon="mdi:refresh" class="me-1"></iconify-icon>Minta Revisi
                </x-ui.button>
            </form>
        </x-ui.card>
        @endif
    </div>
</div>

@endsection
