@extends('dashboard.index')

@section('title', 'Jaringan Sales')
@section('route-breadcrumb', 'sales-network.admin-index')
@section('breadcrumb', 'Jaringan Sales')

@section('content')

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="ui-card-title">Semua Anggota Jaringan</div>
            <div class="d-flex gap-2">
                <x-ui.button :href="route('sales-network.pending')" variant="outline" size="sm">Menunggu Approval</x-ui.button>
                <x-ui.button :href="route('sales-network.admin-create')" variant="primary" size="sm">+ Tambah Anggota Langsung</x-ui.button>
            </div>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama</th>
                    <th>Kode Referral</th>
                    <th>Sponsor</th>
                    <th>Cabang</th>
                    <th>Status</th>
                    <th style="width:170px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $profile)
                <tr>
                    <td>{{ $profiles->firstItem() + $loop->index }}</td>
                    <td class="fw-semibold">{{ $profile->employee?->name ?? '-' }}</td>
                    <td class="font-monospace small">{{ $profile->referral_code }}</td>
                    <td class="small">{{ $profile->sponsor?->name ?? '— (akar jaringan)' }}</td>
                    <td class="small text-muted">{{ $profile->employee?->branch?->name ?? '-' }}</td>
                    <td>
                        <x-ui.badge :variant="$profile->isActive() ? 'success' : 'warning'">
                            {{ \App\Models\SalesNetworkProfile::$statusLabels[$profile->status] ?? $profile->status }}
                        </x-ui.badge>
                    </td>
                    <td>
                        @if($profile->isActive() && isset($needsInvite[$profile->employee_id]))
                            <form method="POST" action="{{ route('sales-network.resend-invite', $profile) }}">
                                @csrf
                                <x-ui.button type="submit" variant="outline" size="sm">
                                    <iconify-icon icon="mdi:email-fast-outline"></iconify-icon> Kirim ulang undangan
                                </x-ui.button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-5"><x-ui.empty-state title="Belum ada anggota jaringan." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $profiles->links() }}@endslot
</x-ui.card>

@endsection
