@extends('dashboard.index')

@section('title', 'Pengajuan Cuti')
@section('route-breadcrumb', 'leave-requests.index')
@section('breadcrumb', 'Pengajuan Cuti')

@section('content')

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Pengajuan Cuti / Izin</div>
                <small class="text-muted">Kelola cuti dan izin karyawan.</small>
            </div>
            <x-ui.button href="{{ route('leave-requests.create') }}" variant="primary" size="sm">+ Buat Pengajuan</x-ui.button>
        </div>
    @endslot

    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Karyawan</th>
                    <th>Jenis</th>
                    <th>Dari</th>
                    <th>Sampai</th>
                    <th>Hari</th>
                    <th>Status</th>
                    <th>Diajukan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $lr)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $lr->employee->name ?? '-' }}</td>
                    <td>{{ \App\Models\LeaveRequest::$typeLabels[$lr->leave_type] ?? $lr->leave_type }}</td>
                    <td>{{ $lr->from_date->format('d/m/Y') }}</td>
                    <td>{{ $lr->to_date->format('d/m/Y') }}</td>
                    <td>{{ $lr->days }}</td>
                    <td>
                        @php
                            $badgeMap = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'neutral'];
                            $badge = $badgeMap[$lr->status] ?? 'neutral';
                            $labelMap = ['pending'=>'Pending','approved'=>'Disetujui','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'];
                        @endphp
                        <x-ui.badge :variant="$badge">{{ $labelMap[$lr->status] ?? $lr->status }}</x-ui.badge>
                    </td>
                    <td>{{ $lr->created_at->format('d/m/Y') }}</td>
                    <td>
                        <x-ui.button href="{{ route('leave-requests.show', $lr) }}" variant="secondary" size="sm">Detail</x-ui.button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="py-5"><x-ui.empty-state title="Belum ada pengajuan." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $requests->links() }}@endslot
</x-ui.card>

@endsection
