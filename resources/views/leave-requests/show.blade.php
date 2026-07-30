@extends('dashboard.index')

@section('title', 'Detail Pengajuan Cuti')
@section('route-breadcrumb', 'leave-requests.index')
@section('breadcrumb', 'Detail Pengajuan Cuti')

@section('content')

<x-ui.card style="max-width:640px">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Detail Pengajuan</div>
            <x-ui.button href="{{ route('leave-requests.index') }}" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    <table class="table table-borderless table-sm">
        <tr><th style="width:40%">Karyawan</th><td>{{ $leaveRequest->employee->name ?? '-' }}</td></tr>
        <tr><th>Cabang</th><td>{{ $leaveRequest->branch->name ?? '-' }}</td></tr>
        <tr><th>Jenis Cuti</th><td>{{ \App\Models\LeaveRequest::$typeLabels[$leaveRequest->leave_type] ?? $leaveRequest->leave_type }}</td></tr>
        <tr><th>Tanggal</th><td>{{ $leaveRequest->from_date->format('d/m/Y') }} – {{ $leaveRequest->to_date->format('d/m/Y') }} ({{ $leaveRequest->days }} hari)</td></tr>
        <tr><th>Alasan</th><td>{{ $leaveRequest->reason }}</td></tr>
        <tr><th>Status</th><td>
            @php
                $badgeMap = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'neutral'];
                $badge = $badgeMap[$leaveRequest->status] ?? 'neutral';
            @endphp
            <x-ui.badge :variant="$badge">{{ ucfirst($leaveRequest->status) }}</x-ui.badge>
        </td></tr>
        <tr><th>Diajukan oleh</th><td>{{ $leaveRequest->requester->name ?? '-' }} pada {{ $leaveRequest->created_at->format('d/m/Y H:i') }}</td></tr>
        @if($leaveRequest->approved_by)
        <tr><th>Ditangani oleh</th><td>{{ $leaveRequest->approver->name ?? '-' }} pada {{ $leaveRequest->approved_at?->format('d/m/Y H:i') }}</td></tr>
        @endif
        @if($leaveRequest->approval_notes)
        <tr><th>Catatan</th><td>{{ $leaveRequest->approval_notes }}</td></tr>
        @endif
    </table>

    @if($leaveRequest->isPending())
    <hr>
    <div class="d-flex gap-2 flex-wrap">
        {{-- Approve --}}
        <form action="{{ route('leave-requests.approve', $leaveRequest) }}" method="POST" class="d-flex gap-2 align-items-center">
            @csrf @method('PUT')
            <input type="text" name="approval_notes" class="form-control form-control-sm" placeholder="Catatan (opsional)" style="width:220px">
            <x-ui.button type="submit" variant="success" size="sm">Setujui</x-ui.button>
        </form>

        {{-- Reject --}}
        <form action="{{ route('leave-requests.reject', $leaveRequest) }}" method="POST" class="d-flex gap-2 align-items-center">
            @csrf @method('PUT')
            <input type="text" name="approval_notes" class="form-control form-control-sm" placeholder="Alasan penolakan" style="width:220px">
            <x-ui.button type="submit" variant="danger" size="sm">Tolak</x-ui.button>
        </form>

        {{-- Cancel --}}
        <form action="{{ route('leave-requests.cancel', $leaveRequest) }}" method="POST"
              onsubmit="return confirm('Batalkan pengajuan ini?')">
            @csrf @method('PUT')
            <x-ui.button type="submit" variant="outline" size="sm">Batalkan</x-ui.button>
        </form>
    </div>
    @endif
</x-ui.card>

@endsection
