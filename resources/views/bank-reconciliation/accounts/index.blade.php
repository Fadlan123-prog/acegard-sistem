@extends('layout.index')

@section('title', 'Rekening Bank')
@section('route-breadcrumb', route('bank-accounts.index'))
@section('breadcrumb', 'Rekening Bank')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        @if ($isSuperadmin)
        <form method="GET" class="d-inline-flex gap-2 align-items-center">
            <select name="branch_id" class="form-select form-select-sm" style="min-width:180px" onchange="this.form.submit()">
                <option value="">Semua Cabang</option>
                @foreach ($branches as $br)
                    <option value="{{ $br->id }}" {{ ($filters['branch_id'] ?? '') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
    </div>
    <x-ui.button :href="route('bank-accounts.create')" variant="primary" size="sm">
        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon>Tambah Rekening
    </x-ui.button>
</div>

@if ($accounts->isEmpty())
<x-ui.card class="text-center py-5">
    <x-ui.empty-state title="Belum ada rekening bank yang terdaftar." />
    <div class="mt-3">
        <x-ui.button :href="route('bank-accounts.create')" variant="primary" size="sm">Tambah Rekening</x-ui.button>
    </div>
</x-ui.card>
@else
<div class="row g-3">
    @foreach ($accounts as $acc)
    @php
        $unreconciled = $acc->unreconciledCount();
    @endphp
    <div class="col-12 col-md-6 col-xl-4">
        <x-ui.card class="h-100" style="border-color:{{ $acc->is_active ? '#f1f5f9' : '#fde8e8' }}">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(99,102,241,.1);display:flex;align-items:center;justify-content:center;color:#6366f1;font-size:20px;flex-shrink:0">
                    <iconify-icon icon="mdi:bank-outline"></iconify-icon>
                </div>
                @if (!$acc->is_active)
                    <x-ui.badge variant="neutral" size="sm">Nonaktif</x-ui.badge>
                @elseif ($unreconciled > 0)
                    <x-ui.badge variant="warning" size="sm">{{ $unreconciled }} belum rekonsiliasi</x-ui.badge>
                @else
                    <x-ui.badge variant="success" size="sm">Semua rekonsiliasi</x-ui.badge>
                @endif
            </div>

            <div class="fw-bold mb-1" style="font-size:15px">{{ $acc->account_name }}</div>
            <div class="text-muted small mb-1">{{ $acc->bank_name }} · {{ $acc->account_number }}</div>
            @if ($isSuperadmin)
                <div class="text-muted" style="font-size:11px">{{ $acc->branch?->name }}</div>
            @endif

            <div class="mt-3 pt-3" style="border-top:1px solid #f1f5f9">
                <div class="text-muted" style="font-size:11px">Saldo Berjalan</div>
                <div class="fw-bold" style="font-size:16px;color:#6366f1">
                    Rp {{ number_format($acc->current_balance, 0, ',', '.') }}
                </div>
            </div>

            @slot('footer')
                <div class="d-flex gap-2">
                    <x-ui.button :href="route('bank-accounts.entries', $acc->id)" variant="primary" size="sm">
                        <iconify-icon icon="mdi:format-list-checks" class="me-1"></iconify-icon>Mutasi & Rekonsiliasi
                    </x-ui.button>
                    <x-ui.button :href="route('bank-accounts.edit', $acc->id)" variant="outline" size="sm">
                        <iconify-icon icon="mdi:pencil-outline"></iconify-icon>
                    </x-ui.button>
                    <form method="POST" action="{{ route('bank-accounts.destroy', $acc->id) }}">
                        @csrf @method('DELETE')
                        <x-ui.button type="submit" variant="danger-outline" size="sm"
                                onclick="return confirm('Hapus rekening ini?')">
                            <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                        </x-ui.button>
                    </form>
                </div>
            @endslot
        </x-ui.card>
    </div>
    @endforeach
</div>
@endif

@endsection
