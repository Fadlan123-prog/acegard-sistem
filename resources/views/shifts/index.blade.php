@extends('dashboard.index')

@section('title', 'Shift')
@section('route-breadcrumb', 'shifts.index')
@section('breadcrumb', 'Shift')

@section('content')

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Daftar Shift</div>
                <small class="text-muted">Kelola jadwal shift kerja per cabang.</small>
            </div>
            @can('superadmin')
            <x-ui.button variant="primary" size="sm" href="{{ route('shifts.create') }}">+ Tambah Shift</x-ui.button>
            @endcan
        </div>
    @endslot

    
    

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nama Shift</th>
                    <th>Cabang</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Lembur (menit)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $shift->name }}</td>
                    <td>{{ $shift->branch?->name ?? 'Semua Cabang' }}</td>
                    <td>{{ $shift->start_time }}</td>
                    <td>{{ $shift->end_time }}</td>
                    <td>{{ $shift->overtime_after_minutes }} menit</td>
                    <td>
                        @if($shift->is_active)
                            <x-ui.badge variant="success" size="sm">Aktif</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral" size="sm">Nonaktif</x-ui.badge>
                        @endif
                    </td>
                    <td>
                        @can('superadmin')
                        <x-ui.button variant="warning" size="sm" href="{{ route('shifts.edit', $shift) }}">Edit</x-ui.button>
                        <form action="{{ route('shifts.destroy', $shift) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus shift ini?')">
                            @csrf @method('DELETE')
                            <x-ui.button variant="danger" size="sm" type="submit">Hapus</x-ui.button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-5"><x-ui.empty-state title="Belum ada shift" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

@endsection
