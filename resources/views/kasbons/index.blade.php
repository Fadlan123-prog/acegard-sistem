@extends('dashboard.index')

@section('title', 'Data Kasbon')
@section('route-breadcrumb', 'kasbons.index')
@section('breadcrumb', 'Data Kasbon')

@section('content')

@php
    $statusMap = [
        ''                  => ['label' => 'Semua',            'badge' => 'secondary'],
        'pending_approval'  => ['label' => 'Menunggu Approval', 'badge' => 'warning'],
        'active'            => ['label' => 'Berjalan',          'badge' => 'info'],
        'completed'         => ['label' => 'Lunas',             'badge' => 'success'],
        'rejected'          => ['label' => 'Ditolak',           'badge' => 'danger'],
        'cancelled'         => ['label' => 'Dibatalkan',        'badge' => 'danger'],
    ];
    $activeStatus = $filters['status'] ?? '';
@endphp

<x-ui.card class="mb-3">
    <div class="d-flex gap-2 flex-wrap align-items-center py-1">
        <span class="text-muted small fw-semibold me-1">Filter:</span>
        @foreach($statusMap as $val => $cfg)
            <a href="{{ route('kasbons.index', ['status' => $val]) }}"
               class="btn btn-sm {{ $activeStatus === $val ? 'btn-'.$cfg['badge'] : 'btn-outline-'.$cfg['badge'] }}">
                {{ $cfg['label'] }}
            </a>
        @endforeach
        <span class="ms-auto text-muted small">{{ $kasbons->total() }} pengajuan</span>
    </div>
</x-ui.card>

<x-ui.card class="basic-data-table">
    @slot('header')
        <div>
            <div class="ui-card-title">Data Kasbon</div>
            <small class="text-muted">Audit seluruh pengajuan kasbon karyawan. Persetujuan dilakukan di menu Approval.</small>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Karyawan</th>
                    <th>Cabang</th>
                    <th class="text-end">Nominal</th>
                    <th>Cicilan</th>
                    <th class="text-end">Sisa</th>
                    <th>Sumber Potongan</th>
                    <th>Status</th>
                    <th>Diajukan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kasbons as $kasbon)
                @php
                    $statusVariant = match($kasbon->status) {
                        'pending_approval' => 'warning',
                        'active'            => 'info',
                        'completed'         => 'success',
                        'rejected', 'cancelled' => 'danger',
                        default             => 'secondary',
                    };
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $kasbon->employee?->name ?? '-' }}</td>
                    <td>{{ $kasbon->branch?->name ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</td>
                    <td>{{ $kasbon->installments_paid }} / {{ $kasbon->installments }}x</td>
                    <td class="text-end fw-semibold">Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}</td>
                    <td>{{ \App\Models\Kasbon::$sourceLabels[$kasbon->deduction_source] ?? $kasbon->deduction_source }}</td>
                    <td><x-ui.badge :variant="$statusVariant">{{ \App\Models\Kasbon::$statusLabels[$kasbon->status] ?? $kasbon->status }}</x-ui.badge></td>
                    <td class="small text-muted">{{ $kasbon->created_at->format('d/m/Y') }}</td>
                    <td>
                        <x-ui.button :href="route('my-kasbon.show', $kasbon)" variant="secondary" size="sm">Detail</x-ui.button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="py-5"><x-ui.empty-state title="Belum ada pengajuan kasbon." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer')
        @if($kasbons->hasPages())
            {{ $kasbons->links('pagination::bootstrap-5') }}
        @endif
    @endslot
</x-ui.card>

@endsection
