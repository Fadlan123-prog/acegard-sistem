@extends('layout.index')

@section('title', 'Detail Pengajuan')
@section('route-breadcrumb', route('my-leave-requests.index'))
@section('breadcrumb', 'Detail Pengajuan')

@section('content')

@php
    $statusMap = [
        'pending'   => ['label' => 'Menunggu Persetujuan', 'variant' => 'warning'],
        'approved'  => ['label' => 'Disetujui',            'variant' => 'success'],
        'rejected'  => ['label' => 'Ditolak',              'variant' => 'danger'],
        'cancelled' => ['label' => 'Dibatalkan',           'variant' => 'neutral'],
    ];
    $sm = $statusMap[$leaveRequest->status] ?? ['label' => ucfirst($leaveRequest->status), 'variant' => 'neutral'];
    $typeLabel = $typeLabels[$leaveRequest->leave_type] ?? ucfirst($leaveRequest->leave_type);
@endphp

{{-- Flash --}}
@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if (session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

{{-- Back --}}
<div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
    <div class="d-flex align-items-center gap-3">
        <x-ui.button href="{{ route('my-leave-requests.index') }}" variant="outline" size="sm">
            <x-slot name="icon"><svg viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></x-slot>
            Kembali
        </x-ui.button>
        <h5 class="mb-0 fw-semibold">Detail Pengajuan</h5>
    </div>
    <x-ui.badge :variant="$sm['variant']">{{ $sm['label'] }}</x-ui.badge>
</div>

<div class="row g-3">
    <div class="col-lg-7">

        <x-ui.card class="mb-3">
            @slot('header')
                <div class="ui-card-title">Informasi Pengajuan</div>
            @endslot

            <div class="row g-3" style="font-size:13px">
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Jenis</div>
                    <div class="fw-semibold">{{ $typeLabel }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Status</div>
                    <x-ui.badge :variant="$sm['variant']">{{ $sm['label'] }}</x-ui.badge>
                </div>
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Tanggal Mulai</div>
                    <div class="fw-semibold">{{ $leaveRequest->from_date->format('d M Y') }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Tanggal Selesai</div>
                    <div class="fw-semibold">{{ $leaveRequest->to_date->format('d M Y') }}</div>
                </div>
                <div class="col-12">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Total Hari</div>
                    <div class="fw-bold" style="font-size:20px">{{ $leaveRequest->days }} <span class="fw-normal ui-text-muted" style="font-size:13px">hari</span></div>
                </div>
                <div class="col-12">
                    <div class="ui-text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Alasan</div>
                    <div>{{ $leaveRequest->reason }}</div>
                </div>
            </div>
        </x-ui.card>

        {{-- Timeline --}}
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title">Riwayat Status</div>
            @endslot

            <div style="font-size:13px">

                {{-- Diajukan --}}
                <div class="d-flex gap-3 align-items-start mb-3">
                    <div class="flex-shrink-0 d-flex flex-column align-items-center" style="width:28px">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:28px;height:28px;background:var(--ui-primary,#3b82f6);color:white;font-size:12px">
                            <iconify-icon icon="mdi:send-outline"></iconify-icon>
                        </div>
                        <div style="width:2px;height:32px;background:var(--ui-border);margin-top:4px"></div>
                    </div>
                    <div>
                        <div class="fw-semibold">Pengajuan Dibuat</div>
                        <div class="ui-text-muted" style="font-size:12px">
                            {{ $leaveRequest->created_at->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>

                {{-- Diproses --}}
                <div class="d-flex gap-3 align-items-start">
                    <div class="flex-shrink-0" style="width:28px">
                        @if ($leaveRequest->status === 'approved')
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;background:var(--ui-green,#10b981);color:white;font-size:12px">
                                <iconify-icon icon="mdi:check-all"></iconify-icon>
                            </div>
                        @elseif ($leaveRequest->status === 'rejected')
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;background:var(--ui-red,#ef4444);color:white;font-size:12px">
                                <iconify-icon icon="mdi:close"></iconify-icon>
                            </div>
                        @elseif ($leaveRequest->status === 'cancelled')
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;background:var(--ui-border);color:var(--ui-text-muted);font-size:12px">
                                <iconify-icon icon="mdi:block-helper"></iconify-icon>
                            </div>
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;background:var(--ui-amber,#f59e0b);color:white;font-size:12px">
                                <iconify-icon icon="mdi:clock-outline"></iconify-icon>
                            </div>
                        @endif
                    </div>
                    <div>
                        @if ($leaveRequest->status === 'pending')
                            <div class="fw-semibold" style="color:var(--ui-amber,#f59e0b)">Menunggu Persetujuan Admin</div>
                            <div class="ui-text-muted" style="font-size:12px">Pengajuan Anda sedang diproses</div>
                        @elseif ($leaveRequest->status === 'approved')
                            <div class="fw-semibold" style="color:var(--ui-green,#10b981)">Disetujui</div>
                            <div class="ui-text-muted" style="font-size:12px">
                                @if ($leaveRequest->approver)
                                    Oleh {{ $leaveRequest->approver->name }}
                                @endif
                                @if ($leaveRequest->approved_at)
                                    pada {{ $leaveRequest->approved_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        @elseif ($leaveRequest->status === 'rejected')
                            <div class="fw-semibold" style="color:var(--ui-red,#ef4444)">Ditolak</div>
                            <div class="ui-text-muted" style="font-size:12px">
                                @if ($leaveRequest->approver)
                                    Oleh {{ $leaveRequest->approver->name }}
                                @endif
                                @if ($leaveRequest->approved_at)
                                    pada {{ $leaveRequest->approved_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        @elseif ($leaveRequest->status === 'cancelled')
                            <div class="fw-semibold ui-text-muted">Dibatalkan oleh Anda</div>
                        @endif

                        @if ($leaveRequest->approval_notes)
                            <div class="mt-2 p-2 rounded" style="background:var(--ui-surface-alt,#f8fafc);border:1px solid var(--ui-border);font-size:12px">
                                <iconify-icon icon="mdi:chat-outline" class="me-1 ui-text-muted"></iconify-icon>{{ $leaveRequest->approval_notes }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($leaveRequest->isPending())
                @slot('footer')
                    <form action="{{ route('my-leave-requests.cancel', $leaveRequest->id) }}" method="POST">
                        @csrf @method('PUT')
                        <x-ui.button type="submit" variant="danger-outline" size="sm"
                                onclick="return confirm('Batalkan pengajuan ini?')">
                            <iconify-icon icon="mdi:close" class="me-1"></iconify-icon> Batalkan Pengajuan
                        </x-ui.button>
                    </form>
                @endslot
            @endif
        </x-ui.card>

    </div>

    {{-- Info sidebar --}}
    <div class="col-lg-5">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title">Keterangan Jenis</div>
            @endslot
            <div style="font-size:13px">
                @foreach ($typeLabels as $key => $label)
                @php
                    $icons = [
                        'annual'     => 'mdi:airplane',
                        'sick'       => 'mdi:heart-pulse',
                        'permission' => 'mdi:clock-outline',
                        'emergency'  => 'mdi:alarm-light-outline',
                        'libur'      => 'mdi:coffee-outline',
                    ];
                    $active = $leaveRequest->leave_type === $key;
                @endphp
                <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                     style="border-color:var(--ui-border);{{ $active ? 'font-weight:600' : '' }}">
                    <iconify-icon icon="{{ $icons[$key] ?? 'mdi:calendar-outline' }}"
                       style="font-size:16px;color:{{ $active ? 'var(--ui-primary,#3b82f6)' : 'var(--ui-text-muted)' }}"></iconify-icon>
                    <span style="{{ $active ? 'color:var(--ui-primary,#3b82f6)' : '' }}">{{ $label }}</span>
                    @if ($active)
                        <x-ui.badge variant="primary" size="sm" class="ms-auto">Dipilih</x-ui.badge>
                    @endif
                </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</div>

@endsection
