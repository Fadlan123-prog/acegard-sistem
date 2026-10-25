@extends('layout.index')

@section('title', 'Riwayat Notifikasi')
@section('route-breadcrumb', route('notification-config.index'))
@section('breadcrumb', 'Riwayat Notifikasi Otomatis')

@section('content')

<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('notification-config.logs') }}"
          class="d-flex flex-wrap gap-2 align-items-end">

        @if ($isSuperadmin)
        <select name="branch_id" class="form-select form-select-sm" style="min-width:170px" onchange="this.form.submit()">
            <option value="">Semua Cabang</option>
            @foreach ($branches as $br)
                <option value="{{ $br->id }}" {{ ($filters['branch_id'] ?? '') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
            @endforeach
        </select>
        @endif

        <select name="type" class="form-select form-select-sm" style="min-width:180px" onchange="this.form.submit()">
            <option value="">Semua Tipe</option>
            @foreach (\App\Models\NotificationConfig::$types as $val => $lbl)
                <option value="{{ $val }}" {{ ($filters['type'] ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>

        <select name="status" class="form-select form-select-sm" style="min-width:130px" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="sent"   {{ ($filters['status'] ?? '') === 'sent'   ? 'selected' : '' }}>Terkirim</option>
            <option value="failed" {{ ($filters['status'] ?? '') === 'failed' ? 'selected' : '' }}>Gagal</option>
        </select>

        <x-ui.button :href="route('notification-config.logs')" variant="outline" size="sm">Reset</x-ui.button>
        <x-ui.button :href="route('notification-config.index', $filters)" variant="outline" size="sm" class="ms-auto">
            <iconify-icon icon="mdi:cog-outline" class="me-1"></iconify-icon>Pengaturan
        </x-ui.button>
    </form>
</x-ui.card>

<x-ui.card>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-medium border-bottom">Waktu</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Tipe</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Channel</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Penerima</th>
                    <th class="py-3 text-muted fw-medium border-bottom" style="max-width:300px">Pesan</th>
                    <th class="py-3 pe-4 text-center text-muted fw-medium border-bottom">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                <tr>
                    <td class="ps-4 py-2 text-muted" style="white-space:nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="py-2">{{ \App\Models\NotificationConfig::$types[$log->type] ?? $log->type }}</td>
                    <td class="py-2">{{ \App\Models\NotificationConfig::$channels[$log->channel] ?? $log->channel }}</td>
                    <td class="py-2 text-muted" style="font-size:12px">{{ $log->recipient ?? '—' }}</td>
                    <td class="py-2 text-muted" style="font-size:12px;max-width:300px">
                        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $log->message }}">
                            {{ $log->message }}
                        </div>
                        @if ($log->error)
                            <div class="text-danger" style="font-size:11px">{{ $log->error }}</div>
                        @endif
                    </td>
                    <td class="py-2 pe-4 text-center">
                        <x-ui.badge :variant="$log->status === 'sent' ? 'success' : 'danger'" size="sm">
                            {{ $log->status === 'sent' ? 'Terkirim' : 'Gagal' }}
                        </x-ui.badge>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-4">
                        <x-ui.empty-state title="Belum ada riwayat notifikasi." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="px-4 py-3">{{ $logs->links() }}</div>
    @endif
</x-ui.card>

@endsection
