@extends('layout.index')

@section('title', $title)
@section('route-breadcrumb', route('ranking.teknisi'))
@section('breadcrumb', 'Ranking Teknisi')

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
    <form method="GET" action="{{ route('ranking.teknisi') }}" class="d-flex flex-wrap gap-2 align-items-center">
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

<div class="row g-4">

    {{-- Teknisi Ranking --}}
    <div class="col-xl-6">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title">
                    Ranking Teknisi — {{ $months[$month] }} {{ $year }}
                </div>
            @endslot

            @forelse ($ranking as $idx => $emp)
            @php
                $medal  = match ($idx) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => null };
                $isTop3 = $idx < 3;
            @endphp
            <div class="d-flex align-items-center gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}"
                 style="{{ $isTop3 ? 'background:rgba(var(--ui-amber-rgb,245,158,11),.06);margin:0 -1px;padding-left:1px;padding-right:1px;' : '' }}border-color:var(--ui-border)">
                <div class="text-center flex-shrink-0" style="width:36px">
                    @if ($medal)
                        <span style="font-size:22px;line-height:1">{{ $medal }}</span>
                    @else
                        <span class="ui-text-muted fw-bold" style="font-size:14px">{{ $idx + 1 }}</span>
                    @endif
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate" style="font-size:13px">{{ $emp->name }}</div>
                    <div class="ui-text-muted" style="font-size:11px">{{ $emp->total_jobs }} pekerjaan</div>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="fw-bold" style="font-size:13px;color:var(--ui-green,#10b981)">
                        {{ $idr($emp->earned_total) }}
                    </div>
                    <div class="ui-text-muted" style="font-size:10px">komisi</div>
                </div>
            </div>
            @empty
                <x-ui.empty-state title="Belum ada data teknisi bulan ini"
                    description="Data akan muncul setelah teknisi menyelesaikan pekerjaan dan komisi diproses." />
            @endforelse
        </x-ui.card>
    </div>

    {{-- Kenek Ranking --}}
    <div class="col-xl-6">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title">
                    Ranking Kenek — {{ $months[$month] }} {{ $year }}
                </div>
            @endslot

            @forelse ($rankingKenek as $idx => $emp)
            @php
                $medal  = match ($idx) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => null };
                $isTop3 = $idx < 3;
            @endphp
            <div class="d-flex align-items-center gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}"
                 style="{{ $isTop3 ? 'background:rgba(var(--ui-amber-rgb,245,158,11),.06);margin:0 -1px;padding-left:1px;padding-right:1px;' : '' }}border-color:var(--ui-border)">
                <div class="text-center flex-shrink-0" style="width:36px">
                    @if ($medal)
                        <span style="font-size:22px;line-height:1">{{ $medal }}</span>
                    @else
                        <span class="ui-text-muted fw-bold" style="font-size:14px">{{ $idx + 1 }}</span>
                    @endif
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate" style="font-size:13px">{{ $emp->name }}</div>
                    <div class="ui-text-muted" style="font-size:11px">{{ $emp->total_jobs }} pekerjaan</div>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="fw-bold" style="font-size:13px;color:var(--ui-green,#10b981)">
                        {{ $idr($emp->earned_total) }}
                    </div>
                    <div class="ui-text-muted" style="font-size:10px">komisi</div>
                </div>
            </div>
            @empty
                <x-ui.empty-state title="Belum ada data kenek bulan ini" />
            @endforelse
        </x-ui.card>
    </div>

</div>

@endsection
