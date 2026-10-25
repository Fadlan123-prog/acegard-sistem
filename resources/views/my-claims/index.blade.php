@extends('dashboard.index')

@section('title', 'Klaim Saya')
@section('route-breadcrumb', 'my-claims.index')
@section('breadcrumb', 'Klaim Saya')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Klaim Pembelian, Bensin & Kesehatan</h5>
    <x-ui.button :href="route('my-claims.create')" variant="primary">
        <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Ajukan Klaim
    </x-ui.button>
</div>

<x-ui.card>
    @if($claims->isEmpty())
        <x-ui.empty-state
            icon='<iconify-icon icon="mdi:receipt-text-check-outline" style="font-size:26px;opacity:.5"></iconify-icon>'
            title="Belum ada klaim"
            description="Klaim pembelian, bensin, atau biaya kesehatan yang Anda ajukan akan muncul di sini." />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($claims as $claim)
                        <tr>
                            <td>{{ \App\Models\EmployeeClaim::$typeLabels[$claim->type] }}</td>
                            <td class="tab-nums">Rp {{ number_format($claim->amount, 0, ',', '.') }}</td>
                            <td class="small text-muted">{{ $claim->description ?: '-' }}</td>
                            <td>
                                @php
                                    $statusVariant = match($claim->status) {
                                        'pending_approval' => 'warning',
                                        'approved'          => 'info',
                                        'paid'              => 'success',
                                        'rejected'          => 'danger',
                                        default             => 'secondary',
                                    };
                                    $statusLabel = match($claim->status) {
                                        'pending_approval' => 'Menunggu Approval',
                                        'approved'          => 'Disetujui — Menunggu Pencairan',
                                        'paid'              => 'Sudah Dicairkan',
                                        'rejected'          => 'Ditolak',
                                        default             => $claim->status,
                                    };
                                @endphp
                                <x-ui.badge :variant="$statusVariant">{{ $statusLabel }}</x-ui.badge>
                            </td>
                            <td class="small text-muted">{{ $claim->created_at->translatedFormat('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $claims->links() }}</div>
    @endif
</x-ui.card>

@endsection
