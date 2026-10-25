@extends('layout.index')

@section('title', $title)
@section('route-breadcrumb', route('ranking.marketing'))
@section('breadcrumb', 'Ranking Marketing')

@section('content')

@php
    $idr = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret',    4=>'April',
        5=>'Mei',     6=>'Juni',     7=>'Juli',      8=>'Agustus',
        9=>'September',10=>'Oktober',11=>'November', 12=>'Desember',
    ];
@endphp

{{-- Filter --}}
<x-ui.card class="mb-4">
    <form method="GET" action="{{ route('ranking.marketing') }}" class="d-flex flex-wrap gap-2 align-items-center">
        <select name="month" class="form-select form-select-sm" style="width:140px">
            @foreach ($months as $m => $label)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="year" class="form-select form-select-sm" style="width:100px">
            @for ($y = now()->year; $y >= now()->year - 2; $y--)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <x-ui.button type="submit" variant="primary" size="sm">Filter</x-ui.button>
    </form>
</x-ui.card>

<x-ui.card>
    @slot('header')
        <div class="ui-card-title">
            Ranking Marketing — {{ $months[$month] }} {{ $year }}
        </div>
    @endslot

    <div class="table-responsive" style="margin:-1px">
        <table class="table table-hover align-middle mb-0" style="font-size:12px">
            <thead>
                <tr>
                    <th class="py-2 fw-medium border-bottom" style="color:var(--ui-text-muted);width:48px">#</th>
                    <th class="py-2 fw-medium border-bottom" style="color:var(--ui-text-muted)">Nama</th>
                    <th class="py-2 fw-medium text-center border-bottom" style="color:var(--ui-text-muted)">Total Deal</th>
                    <th class="py-2 fw-medium text-center border-bottom" style="color:var(--ui-text-muted)">Closing Rate</th>
                    <th class="py-2 fw-medium text-end border-bottom" style="color:var(--ui-text-muted)">Total Omset</th>
                    <th class="py-2 fw-medium text-end border-bottom" style="color:var(--ui-text-muted)">Komisi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ranking as $idx => $emp)
                @php
                    $medal  = match ($idx) { 0 => '#FFD700', 1 => '#C0C0C0', 2 => '#CD7F32', default => null };
                    $isTop3 = $idx < 3;

                    $empUserId = $userToEmployee->search($emp->id);
                    $ls = $empUserId ? ($leadStats[$empUserId] ?? null) : null;
                    $closingRate = ($ls && $ls->total_leads > 0)
                        ? round(($ls->won_leads / $ls->total_leads) * 100, 1)
                        : null;

                    $closingVariant = $closingRate === null ? 'neutral'
                        : ($closingRate >= 50 ? 'success' : ($closingRate >= 30 ? 'warning' : 'neutral'));
                @endphp
                <tr style="{{ $isTop3 ? 'background:rgba(var(--ui-amber-rgb,245,158,11),.06)' : '' }}">
                    <td class="py-3 text-center">
                        @if ($medal)
                            <iconify-icon icon="mdi:medal" style="font-size:22px;color:{{ $medal }}"></iconify-icon>
                        @else
                            <span class="ui-text-muted fw-bold">{{ $idx + 1 }}</span>
                        @endif
                    </td>
                    <td class="py-3">
                        <div class="fw-semibold" style="font-size:13px">{{ $emp->name }}</div>
                        @if ($emp->job_position)
                            <div class="ui-text-muted" style="font-size:10px">{{ $emp->job_position }}</div>
                        @endif
                    </td>
                    <td class="py-3 text-center">
                        <x-ui.badge variant="primary" size="sm">{{ $emp->total_deals }}</x-ui.badge>
                    </td>
                    <td class="py-3 text-center">
                        @if ($closingRate !== null)
                            <x-ui.badge :variant="$closingVariant" size="sm">{{ $closingRate }}%</x-ui.badge>
                        @else
                            <span class="ui-text-muted">—</span>
                        @endif
                    </td>
                    <td class="py-3 text-end fw-semibold" style="font-size:12px">
                        {{ $idr($emp->total_omset) }}
                    </td>
                    <td class="py-3 text-end fw-bold" style="color:var(--ui-green,#10b981);font-size:13px">
                        {{ $idr($emp->earned_commission) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-5">
                        <x-ui.empty-state title="Belum ada data marketing bulan ini"
                            description="Data akan muncul setelah invoice dengan marketing lunas diproses." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

@endsection
