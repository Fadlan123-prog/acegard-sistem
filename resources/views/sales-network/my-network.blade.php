@extends('dashboard.index')

@section('title', 'Jaringan Sales Saya')
@section('route-breadcrumb', 'sales-network.my-network')
@section('breadcrumb', 'Jaringan Sales Saya')

@section('content')

<x-ui.card class="mb-3">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Kode Referral Saya</div>
            <x-ui.badge :variant="$profile->isActive() ? 'success' : 'warning'">
                {{ \App\Models\SalesNetworkProfile::$statusLabels[$profile->status] ?? $profile->status }}
            </x-ui.badge>
        </div>
    @endslot

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-3" style="background: var(--neutral-50); border: 1px dashed var(--neutral-300);">
            <iconify-icon icon="mdi:tag-outline" style="font-size:18px;color:var(--text-secondary-light);"></iconify-icon>
            <span class="fs-5 fw-bold font-monospace" id="referralCode">{{ $profile->referral_code }}</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('referralCode').textContent); this.innerHTML='<iconify-icon icon=\'mdi:check\'></iconify-icon> Disalin'; setTimeout(() => this.innerHTML='<iconify-icon icon=\'mdi:content-copy\'></iconify-icon> Salin', 1500)">
            <iconify-icon icon="mdi:content-copy"></iconify-icon> Salin
        </button>
    </div>
    <p class="text-muted small mt-3 mb-0">Bagikan kode ini ke calon sales yang ingin Anda rekrut — kode ini otomatis dipakai saat mereka didaftarkan lewat form "Rekrut Sales Baru".</p>
    @if($profile->isActive())
        <x-ui.button :href="route('sales-network.create')" variant="primary" size="sm" class="mt-3">
            <iconify-icon icon="mdi:account-plus-outline" class="me-1"></iconify-icon>Rekrut Sales Baru
        </x-ui.button>
    @endif
</x-ui.card>

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="ui-card-title">Rekrutan Saya ({{ $recruits->count() }})</div>
    @endslot

    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Tanggal Direkrut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recruits as $recruit)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $recruit->employee?->name ?? '-' }}</td>
                    <td>
                        <x-ui.badge :variant="$recruit->isActive() ? 'success' : 'warning'">
                            {{ \App\Models\SalesNetworkProfile::$statusLabels[$recruit->status] ?? $recruit->status }}
                        </x-ui.badge>
                    </td>
                    <td class="small text-muted">{{ $recruit->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-5"><x-ui.empty-state title="Belum ada rekrutan." description="Bagikan kode referral Anda untuk mulai membangun jaringan." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

@endsection
