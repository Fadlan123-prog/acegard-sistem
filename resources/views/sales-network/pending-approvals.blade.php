@extends('dashboard.index')

@section('title', 'Approval Rekrutan Sales')
@section('route-breadcrumb', 'sales-network.pending')
@section('breadcrumb', 'Approval Rekrutan Sales')

@section('content')

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Menunggu Approval</div>
            <x-ui.button :href="route('sales-network.admin-index')" variant="outline" size="sm">Lihat Semua Anggota</x-ui.button>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama Calon</th>
                    <th>Cabang</th>
                    <th>Direkrut Oleh</th>
                    <th>Tanggal</th>
                    <th style="width:180px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $profile)
                <tr>
                    <td>{{ $profiles->firstItem() + $loop->index }}</td>
                    <td class="fw-semibold">{{ $profile->employee?->name ?? '-' }}</td>
                    <td class="small text-muted">{{ $profile->employee?->branch?->name ?? '-' }}</td>
                    <td class="small">{{ $profile->sponsor?->name ?? '-' }}</td>
                    <td class="small text-muted">{{ $profile->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <form method="POST" action="{{ route('sales-network.approve', $profile) }}">
                                @csrf
                                <x-ui.button type="submit" variant="success" size="sm">
                                    <iconify-icon icon="mdi:check"></iconify-icon> Setujui
                                </x-ui.button>
                            </form>
                            <form method="POST" action="{{ route('sales-network.reject', $profile) }}" onsubmit="return confirm('Tolak dan hapus pendaftaran ini?')">
                                @csrf
                                <x-ui.button type="submit" variant="danger-outline" size="sm">
                                    <iconify-icon icon="mdi:close"></iconify-icon> Tolak
                                </x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-5"><x-ui.empty-state title="Tidak ada pendaftaran yang menunggu." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $profiles->links() }}@endslot
</x-ui.card>

@endsection
