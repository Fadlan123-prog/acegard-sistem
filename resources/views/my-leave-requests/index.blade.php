@extends('layout.index')

@section('title', 'Pengajuan Cuti / Izin')
@section('route-breadcrumb', route('my-leave-requests.index'))
@section('breadcrumb', 'Pengajuan Cuti & Izin')

@section('content')

@php
    $statusMap = [
        'pending'   => ['label' => 'Menunggu',  'variant' => 'warning'],
        'approved'  => ['label' => 'Disetujui', 'variant' => 'success'],
        'rejected'  => ['label' => 'Ditolak',   'variant' => 'danger'],
        'cancelled' => ['label' => 'Dibatalkan','variant' => 'neutral'],
    ];
@endphp

{{-- Flash --}}
@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if (session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

@if ($noEmployee)
    <x-ui.alert type="warning" :dismissible="false">
        <x-slot name="title">Akun belum terhubung ke data karyawan</x-slot>
        Hubungi admin atau superadmin untuk menghubungkan akun Anda ke data karyawan.
    </x-ui.alert>
@else

{{-- Jatah Libur Bulanan --}}
<div class="row g-3 mb-3">
    @foreach ([
        ['label' => 'Jatah Libur ' . $liburQuota['month'], 'value' => $liburQuota['quota'], 'icon' => 'mdi:calendar-check-outline', 'color' => 'var(--ui-primary,#3b82f6)'],
        ['label' => 'Sudah Dipakai',                        'value' => $liburQuota['used'],  'icon' => 'mdi:checkbox-marked-circle-outline','color' => 'var(--ui-amber,#f59e0b)'],
        ['label' => 'Sisa Libur',                           'value' => $liburQuota['remaining'], 'icon' => 'mdi:clock-outline',
         'color' => $liburQuota['remaining'] > 0 ? 'var(--ui-green,#10b981)' : 'var(--ui-red,#ef4444)'],
    ] as $stat)
    <div class="col-sm-4">
        <div class="p-3 rounded d-flex align-items-center gap-3" style="background:var(--ui-surface-alt,#f8fafc);border:1px solid var(--ui-border)">
            <iconify-icon icon="{{ $stat['icon'] }}" style="font-size:22px;color:{{ $stat['color'] }};flex-shrink:0"></iconify-icon>
            <div>
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em" class="ui-text-muted">{{ $stat['label'] }}</div>
                <div class="fw-bold" style="font-size:20px;color:{{ $stat['color'] }}">{{ $stat['value'] }} <span class="fw-normal ui-text-muted" style="font-size:12px">hari</span></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="ui-card-title">Pengajuan Cuti &amp; Izin</div>
                @if ($employee)
                    <div class="ui-text-muted" style="font-size:12px">{{ $employee->name }}</div>
                @endif
            </div>
            <x-ui.button href="{{ route('my-leave-requests.create') }}" variant="primary" size="sm">
                <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Buat Pengajuan
            </x-ui.button>
        </div>
    @endslot

    @forelse ($requests as $req)
    @php
        $sm = $statusMap[$req->status] ?? ['label' => ucfirst($req->status), 'variant' => 'neutral'];
        $typeLabel = $typeLabels[$req->leave_type] ?? ucfirst($req->leave_type);
    @endphp
    <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--ui-border)">
        <div class="d-flex align-items-start justify-content-between gap-3">
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="fw-semibold" style="font-size:14px">{{ $typeLabel }}</span>
                    <x-ui.badge :variant="$sm['variant']" size="sm">{{ $sm['label'] }}</x-ui.badge>
                </div>
                <div class="ui-text-muted" style="font-size:12px">
                    <iconify-icon icon="mdi:calendar-outline" class="me-1"></iconify-icon>
                    {{ $req->from_date->format('d M Y') }}
                    @if ($req->from_date != $req->to_date)
                        — {{ $req->to_date->format('d M Y') }}
                    @endif
                    &bull; <strong>{{ $req->days }} hari</strong>
                </div>
                <div class="ui-text-muted mt-1" style="font-size:12px">
                    {{ Str::limit($req->reason, 80) }}
                </div>
                @if ($req->approval_notes)
                    <div class="mt-1" style="font-size:12px;color:{{ $req->status === 'rejected' ? 'var(--ui-red,#ef4444)' : 'inherit' }}">
                        <iconify-icon icon="mdi:chat-outline" class="me-1"></iconify-icon>{{ $req->approval_notes }}
                    </div>
                @endif
                <div class="ui-text-muted mt-1" style="font-size:11px">
                    Diajukan {{ $req->created_at->diffForHumans() }}
                    @if ($req->approver && $req->approved_at)
                        &bull; {{ $sm['label'] }} oleh {{ $req->approver->name }}
                        pada {{ $req->approved_at->format('d M Y') }}
                    @endif
                </div>
            </div>
            <div class="d-flex flex-column gap-1 flex-shrink-0">
                <x-ui.button href="{{ route('my-leave-requests.show', $req->id) }}" variant="outline" size="sm">
                    Detail
                </x-ui.button>
                @if ($req->isPending())
                    <form action="{{ route('my-leave-requests.cancel', $req->id) }}" method="POST">
                        @csrf @method('PUT')
                        <x-ui.button type="submit" variant="danger-outline" size="sm" class="w-100"
                                onclick="return confirm('Batalkan pengajuan ini?')">
                            Batalkan
                        </x-ui.button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @empty
        <x-ui.empty-state
            title="Belum ada pengajuan"
            description="Klik 'Buat Pengajuan' untuk mengajukan cuti, libur, atau izin.">
            <x-ui.button href="{{ route('my-leave-requests.create') }}" variant="primary" size="sm">
                <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Buat Pengajuan
            </x-ui.button>
        </x-ui.empty-state>
    @endforelse

    @if ($requests instanceof \Illuminate\Pagination\LengthAwarePaginator && $requests->hasPages())
        @slot('footer')
            {{ $requests->links() }}
        @endslot
    @endif
</x-ui.card>

@endif

@endsection
