@extends('dashboard.index')

@section('title', 'Detail Audit Log')
@section('route-breadcrumb', 'audit-logs.index')
@section('breadcrumb', 'Detail Audit Log')

@section('content')

@php
    $old = $log->old_values ?? [];
    $new = $log->new_values ?? [];
    $keys = collect(array_keys($old))->merge(array_keys($new))->unique()->values();
@endphp

<x-ui.card class="mb-3">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="ui-card-title">Detail Audit Log</div>
            <x-ui.button href="{{ route('audit-logs.index') }}" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <dl class="row mb-0">
        <dt class="col-sm-3">Waktu</dt>
        <dd class="col-sm-9">{{ $log->created_at?->format('d M Y, H:i:s') }}</dd>

        <dt class="col-sm-3">Aksi</dt>
        <dd class="col-sm-9">
            <span class="badge rounded-pill" style="background:{{ $log->event_color }}22;color:{{ $log->event_color }};">
                {{ $log->event_label }}
            </span>
        </dd>

        <dt class="col-sm-3">Data</dt>
        <dd class="col-sm-9">{{ $log->auditable_type }} @if($log->auditable_id) #{{ $log->auditable_id }} @endif</dd>

        @if ($log->description)
            <dt class="col-sm-3">Keterangan</dt>
            <dd class="col-sm-9">{{ $log->description }}</dd>
        @endif

        <dt class="col-sm-3">Modul</dt>
        <dd class="col-sm-9">{{ $log->module ?? '-' }}</dd>

        <dt class="col-sm-3">Oleh</dt>
        <dd class="col-sm-9">{{ $log->user_name ?? 'System' }} @if($log->role) <span class="text-muted">({{ $log->role }})</span> @endif</dd>

        <dt class="col-sm-3">Cabang</dt>
        <dd class="col-sm-9">{{ $log->branch_name ?? '-' }}</dd>

        <dt class="col-sm-3">IP Address</dt>
        <dd class="col-sm-9">{{ $log->ip_address ?? '-' }}</dd>

        <dt class="col-sm-3">URL</dt>
        <dd class="col-sm-9 text-break small text-muted">{{ $log->url ?? '-' }}</dd>
    </dl>
</x-ui.card>

@if ($keys->isNotEmpty())
<x-ui.card>
    @slot('header')
        <div class="ui-card-title">Perubahan Data</div>
    @endslot

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Field</th>
                    <th>Sebelum</th>
                    <th>Sesudah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($keys as $key)
                    @php
                        $oldVal = $old[$key] ?? null;
                        $newVal = $new[$key] ?? null;
                        $changed = $oldVal !== $newVal;
                    @endphp
                    <tr>
                        <td class="ps-3 small fw-semibold">{{ $key }}</td>
                        <td class="small {{ $changed ? 'text-danger' : 'text-muted' }}">
                            {{ is_array($oldVal) ? json_encode($oldVal) : ($oldVal ?? '—') }}
                        </td>
                        <td class="small {{ $changed ? 'text-success fw-semibold' : 'text-muted' }}">
                            {{ is_array($newVal) ? json_encode($newVal) : ($newVal ?? '—') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-ui.card>
@endif

@endsection
