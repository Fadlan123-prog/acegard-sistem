@extends('dashboard.index')

@section('title', 'Audit Log')
@section('route-breadcrumb', 'audit-logs.index')
@section('breadcrumb', 'Audit Log')

@section('content')

{{-- Filter --}}
<x-ui.card class="mb-3">
    <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2 align-items-end">
        @if ($isSuperadmin)
            <div class="col-md-2 col-sm-6">
                <label class="form-label fw-semibold small mb-1">Cabang</label>
                <select name="branch_id" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected($filters['branch_id'] == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-2 col-sm-6">
            <label class="form-label fw-semibold small mb-1">Modul</label>
            <select name="module" class="form-select form-select-sm">
                <option value="">Semua Modul</option>
                @foreach ($modules as $m)
                    <option value="{{ $m }}" @selected($filters['module'] === $m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 col-sm-6">
            <label class="form-label fw-semibold small mb-1">Aksi</label>
            <select name="event" class="form-select form-select-sm">
                <option value="">Semua Aksi</option>
                @foreach (\App\Models\AuditLog::$eventLabels as $value => $label)
                    <option value="{{ $value }}" @selected($filters['event'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 col-sm-6">
            <label class="form-label fw-semibold small mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="form-control form-control-sm">
        </div>

        <div class="col-md-2 col-sm-6">
            <label class="form-label fw-semibold small mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="form-control form-control-sm">
        </div>

        <div class="col-md-2 col-sm-6">
            <label class="form-label fw-semibold small mb-1">Cari</label>
            <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control form-control-sm" placeholder="Nama, data, user...">
        </div>

        <div class="col-12 d-flex gap-2">
            <x-ui.button type="submit" variant="primary" size="sm">
                <iconify-icon icon="mdi:filter-outline" class="me-1"></iconify-icon>Filter
            </x-ui.button>
            <x-ui.button href="{{ route('audit-logs.index') }}" variant="outline" size="sm">Reset</x-ui.button>
        </div>
    </form>
</x-ui.card>

{{-- Log Table --}}
<x-ui.card>
    @slot('header')
        <div class="ui-card-title">Riwayat Aktivitas</div>
    @endslot

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Waktu</th>
                    <th>Aksi</th>
                    <th>Modul</th>
                    <th>Data</th>
                    <th>Oleh</th>
                    <th>Cabang</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="ps-3 text-nowrap small text-muted">{{ $log->created_at?->format('d M Y, H:i') }}</td>
                        <td>
                            <span class="badge rounded-pill" style="background:{{ $log->event_color }}22;color:{{ $log->event_color }};">
                                {{ $log->event_label }}
                            </span>
                        </td>
                        <td class="small">{{ $log->module ?? '-' }}</td>
                        <td class="small">
                            {{ $log->auditable_type }}
                            @if ($log->auditable_id)
                                <span class="text-muted">#{{ $log->auditable_id }}</span>
                            @endif
                            @if ($log->description)
                                <div class="text-muted" style="font-size:11.5px">{{ Str::limit($log->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="small">
                            {{ $log->user_name ?? 'System' }}
                            @if ($log->role)
                                <div class="text-muted" style="font-size:11.5px">{{ $log->role }}</div>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $log->branch_name ?? '-' }}</td>
                        <td class="pe-3 text-end">
                            <x-ui.button href="{{ route('audit-logs.show', $log->id) }}" variant="outline" size="sm">
                                Detail
                            </x-ui.button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-5"><x-ui.empty-state title="Belum ada aktivitas yang tercatat." /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $logs->links() }}@endslot
</x-ui.card>

@endsection
