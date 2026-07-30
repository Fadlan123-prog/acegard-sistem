@extends('layout.index')

@section('title', 'Jadwal Pemasangan')
@section('route-breadcrumb', 'jadwal.index')
@section('breadcrumb', 'Jadwal Pemasangan')

@push('page-styles')
<style>
/* ── Calendar Grid ──────────────────────────────────────────── */
.jp-wrap { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }
@media(max-width:1100px){ .jp-wrap{grid-template-columns:1fr;} }

.jp-cal { background:var(--white); border:1px solid var(--border-color); border-radius:12px; overflow:hidden; }
.jp-cal-header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid var(--border-color); background:var(--neutral-50); }
.jp-cal-title { font-size:16px; font-weight:700; color:var(--neutral-900); margin:0; }
.jp-cal-nav { display:flex; gap:6px; }

.jp-cal-grid { display:grid; grid-template-columns:repeat(7, 1fr); }
.jp-cal-dow { padding:8px 4px; text-align:center; font-size:11px; font-weight:700; color:var(--neutral-400); letter-spacing:.5px; border-bottom:1px solid var(--border-color); }
.jp-cal-dow:first-child { color:var(--danger-main); }

.jp-day { min-height:80px; padding:6px; border-right:1px solid var(--border-color); border-bottom:1px solid var(--border-color); cursor:pointer; transition:background .15s; position:relative; }
.jp-day:nth-child(7n) { border-right:none; }
.jp-day.empty { background:var(--neutral-50); cursor:default; }
.jp-day.other-month { opacity:.4; }
.jp-day:hover:not(.empty) { background:var(--primary-light); }
.jp-day.active { background:var(--primary-light); outline:2px solid var(--primary-600); outline-offset:-2px; }
.jp-day.today .jp-day-num { background:var(--primary-600); color:#fff; }
.jp-day-num { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--neutral-700); }
.jp-day-dots { display:flex; flex-wrap:wrap; gap:3px; }
.jp-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.jp-dot-success { background:var(--success-main); }
.jp-dot-primary { background:var(--primary-600); }
.jp-dot-warning { background:var(--warning-main); }
.jp-day-count { font-size:10px; color:var(--neutral-400); margin-top:3px; }

/* ── Sidebar Panel ──────────────────────────────────────────── */
.jp-panel { background:var(--white); border:1px solid var(--border-color); border-radius:12px; overflow:hidden; position:sticky; top:80px; }
.jp-panel-head { padding:14px 18px; border-bottom:1px solid var(--border-color); background:var(--neutral-50); }
.jp-panel-title { font-size:13px; font-weight:700; margin:0; color:var(--neutral-900); }
.jp-panel-body { max-height:70vh; overflow-y:auto; }

.jp-event { display:flex; gap:10px; padding:12px 16px; border-bottom:1px solid var(--border-color); transition:background .12s; }
.jp-event:last-child { border-bottom:none; }
.jp-event:hover { background:var(--neutral-50); }
.jp-event-dot { width:10px; height:10px; border-radius:50%; margin-top:4px; flex-shrink:0; }
.jp-event-label { font-size:13px; font-weight:600; color:var(--neutral-900); line-height:1.3; }
.jp-event-sub { font-size:11px; color:var(--neutral-500); margin-top:1px; }
.jp-event-staff { font-size:11px; color:var(--neutral-400); margin-top:2px; display:flex; align-items:center; gap:4px; }
.jp-event-date { font-size:11px; color:var(--neutral-400); white-space:nowrap; }
.jp-event-link { text-decoration:none; color:inherit; display:block; }

/* ── List below calendar (mobile / all-day view) ─────────────── */
.jp-list-section { background:var(--white); border:1px solid var(--border-color); border-radius:12px; overflow:hidden; }
.jp-list-head { padding:14px 18px; border-bottom:1px solid var(--border-color); background:var(--neutral-50); display:flex; align-items:center; justify-content:space-between; }

/* ── Legend ──────────────────────────────────────────────────── */
.jp-legend { display:flex; gap:14px; flex-wrap:wrap; }
.jp-legend-item { display:flex; align-items:center; gap:5px; font-size:12px; color:var(--neutral-600); }

/* dark mode */
[data-theme="dark"] .jp-cal,[data-theme="dark"] .jp-panel,[data-theme="dark"] .jp-list-section { background:var(--dark-2); }
[data-theme="dark"] .jp-cal-header,[data-theme="dark"] .jp-panel-head,[data-theme="dark"] .jp-list-head { background:var(--dark-3); }
[data-theme="dark"] .jp-day.empty { background:var(--dark-3); }
[data-theme="dark"] .jp-event:hover { background:var(--dark-3); }
[data-theme="dark"] .jp-cal-title,[data-theme="dark"] .jp-panel-title,[data-theme="dark"] .jp-event-label { color:var(--neutral-100); }
[data-theme="dark"] .jp-day-num { color:var(--neutral-300); }
</style>
@endpush

@section('content')

@php
    use Carbon\Carbon;

    $today    = now()->toDateString();
    $prevMonth = Carbon::create($year, $month, 1)->subMonth();
    $nextMonth = Carbon::create($year, $month, 1)->addMonth();

    // Build weeks array for the grid
    $firstDay  = Carbon::create($year, $month, 1);
    $daysInMonth = $firstDay->daysInMonth;
    // Week starts Monday (1=Mon, 7=Sun). Carbon dayOfWeek: 0=Sun,1=Mon,...,6=Sat
    $startOffset = ($firstDay->dayOfWeek === 0) ? 6 : ($firstDay->dayOfWeek - 1);

    $monthLabel = Carbon::create($year, $month, 1)->translatedFormat('F Y');
    Carbon::setLocale('id');

    $dayNames = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
@endphp

{{-- Filter bar --}}
<x-ui.card class="mb-3">
    <form method="GET" action="{{ route('jadwal.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
        <input type="hidden" name="year"  value="{{ $year }}">
        <input type="hidden" name="month" value="{{ $month }}">

        <label class="small fw-semibold text-muted mb-0">Filter Teknisi:</label>
        <select name="tukang_id" class="form-select form-select-sm" style="max-width:200px"
                onchange="this.form.submit()">
            <option value="">— Semua Teknisi —</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ $tukangFilter == $emp->id ? 'selected' : '' }}>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>

        @if($tukangFilter)
            <x-ui.button href="{{ route('jadwal.index', ['year' => $year, 'month' => $month]) }}" variant="outline" size="sm">Reset Filter</x-ui.button>
        @endif

        <div class="ms-auto jp-legend">
            <div class="jp-legend-item">
                <span class="jp-dot jp-dot-success"></span> Sales Order
            </div>
            <div class="jp-legend-item">
                <span class="jp-dot jp-dot-primary"></span> Kaca Film (Customer)
            </div>
            <div class="jp-legend-item">
                <span class="jp-dot jp-dot-warning"></span> PPF
            </div>
        </div>
    </form>
</x-ui.card>

<div class="jp-wrap">

    {{-- Calendar --}}
    <div>
        <div class="jp-cal">
            {{-- Header: month navigation --}}
            <div class="jp-cal-header">
                <h5 class="jp-cal-title">{{ Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM YYYY') }}</h5>
                <div class="jp-cal-nav">
                    <x-ui.button href="{{ route('jadwal.index', array_merge(request()->only('tukang_id'), ['year' => $prevMonth->year, 'month' => $prevMonth->month])) }}" variant="outline" size="sm" class="px-2">
                        <iconify-icon icon="mdi:chevron-left"></iconify-icon>
                    </x-ui.button>
                    <x-ui.button href="{{ route('jadwal.index', array_merge(request()->only('tukang_id'), ['year' => now()->year, 'month' => now()->month])) }}" variant="outline" size="sm" class="px-3">Hari ini</x-ui.button>
                    <x-ui.button href="{{ route('jadwal.index', array_merge(request()->only('tukang_id'), ['year' => $nextMonth->year, 'month' => $nextMonth->month])) }}" variant="outline" size="sm" class="px-2">
                        <iconify-icon icon="mdi:chevron-right"></iconify-icon>
                    </x-ui.button>
                </div>
            </div>

            {{-- Day-of-week header --}}
            <div class="jp-cal-grid">
                @foreach($dayNames as $dn)
                    <div class="jp-cal-dow">{{ $dn }}</div>
                @endforeach

                {{-- Empty cells before first day --}}
                @for($i = 0; $i < $startOffset; $i++)
                    <div class="jp-day empty"></div>
                @endfor

                {{-- Day cells --}}
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $dateStr  = Carbon::create($year, $month, $d)->toDateString();
                        $isToday  = $dateStr === $today;
                        $dayEvents = $events[$d] ?? [];
                        $soCount   = collect($dayEvents)->where('type', 'so')->count();
                        $custCount = collect($dayEvents)->where('type', 'customer')->count();
                        $ppfCount  = collect($dayEvents)->where('type', 'ppf')->count();
                        $total     = count($dayEvents);
                    @endphp
                    <div class="jp-day {{ $isToday ? 'today' : '' }}"
                         data-day="{{ $d }}"
                         onclick="selectDay({{ $d }})">
                        <div class="jp-day-num">{{ $d }}</div>
                        @if($total > 0)
                        <div class="jp-day-dots">
                            @for($s = 0; $s < min($soCount, 3); $s++)
                                <span class="jp-dot jp-dot-success"></span>
                            @endfor
                            @for($s = 0; $s < min($custCount, 3); $s++)
                                <span class="jp-dot jp-dot-primary"></span>
                            @endfor
                            @for($s = 0; $s < min($ppfCount, 3); $s++)
                                <span class="jp-dot jp-dot-warning"></span>
                            @endfor
                        </div>
                        <div class="jp-day-count">{{ $total }} job</div>
                        @endif
                    </div>
                @endfor

                {{-- Trailing empty cells to fill last row --}}
                @php
                    $totalCells = $startOffset + $daysInMonth;
                    $trailingCells = ($totalCells % 7 === 0) ? 0 : (7 - ($totalCells % 7));
                @endphp
                @for($i = 0; $i < $trailingCells; $i++)
                    <div class="jp-day empty"></div>
                @endfor
            </div>
        </div>

        {{-- Stats row --}}
        <div class="row g-3 mt-1">
            <div class="col-4">
                <x-ui.stats-card label="Sales Order" :value="$salesOrders->count()" color="green" />
            </div>
            <div class="col-4">
                <x-ui.stats-card label="Kaca Film" :value="$customers->count()" color="blue" />
            </div>
            <div class="col-4">
                <x-ui.stats-card label="PPF" :value="$ppfInstallations->count()" color="amber" />
            </div>
        </div>
    </div>

    {{-- Right Panel: event list --}}
    <div class="jp-panel">
        <div class="jp-panel-head d-flex align-items-center justify-content-between">
            <p class="jp-panel-title mb-0" id="panelTitle">Semua Job Bulan Ini
                <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $totalEvents }}</x-ui.badge>
            </p>
            <x-ui.button type="button" variant="outline" size="sm" id="clearDayBtn"
                    style="display:none" onclick="clearDay()">
                <iconify-icon icon="mdi:close"></iconify-icon>
            </x-ui.button>
        </div>
        <div class="jp-panel-body" id="eventList">

            @if($totalEvents === 0)
                <x-ui.empty-state title="Tidak ada jadwal di bulan ini." />
            @else
                {{-- All events, keyed with data-day for JS filtering --}}
                @foreach(range(1, $startOfMonth->daysInMonth) as $d)
                    @php $dayEvs = $events[$d] ?? []; @endphp
                    @foreach($dayEvs as $ev)
                    <a href="{{ $ev['route'] }}" class="jp-event-link jp-event-row" data-day="{{ $d }}">
                        <div class="jp-event">
                            <span class="jp-dot jp-dot-{{ $ev['color'] }}" style="margin-top:5px"></span>
                            <div class="flex-1 min-w-0">
                                <div class="jp-event-label">{{ $ev['label'] }}</div>
                                @if($ev['sub'])
                                    <div class="jp-event-sub">{{ $ev['sub'] }}</div>
                                @endif
                                @if($ev['staff'])
                                    <div class="jp-event-staff">
                                        <iconify-icon icon="mdi:account-outline"></iconify-icon> {{ $ev['staff'] }}
                                    </div>
                                @endif
                            </div>
                            <div class="jp-event-date">
                                {{ Carbon::create($year, $month, $d)->locale('id')->isoFormat('D MMM') }}
                            </div>
                        </div>
                    </a>
                    @endforeach
                @endforeach
            @endif

        </div>
    </div>

</div>

{{-- Mobile: full list below calendar --}}
<div class="jp-list-section mt-3 d-xl-none">
    <div class="jp-list-head">
        <h6 class="mb-0">Semua Jadwal — {{ Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM YYYY') }}</h6>
    </div>
    <div>
        @forelse(range(1, $startOfMonth->daysInMonth) as $d)
            @php $dayEvs = $events[$d] ?? []; @endphp
            @foreach($dayEvs as $ev)
            <a href="{{ $ev['route'] }}" class="jp-event-link">
                <div class="jp-event">
                    <span class="jp-dot jp-dot-{{ $ev['color'] }}" style="margin-top:5px"></span>
                    <div class="flex-1">
                        <div class="jp-event-label">{{ $ev['label'] }}</div>
                        <div class="jp-event-sub">
                            {{ $ev['sub'] }}
                            @if($ev['staff']) · {{ $ev['staff'] }} @endif
                        </div>
                    </div>
                    <div class="jp-event-date">
                        {{ Carbon::create($year, $month, $d)->locale('id')->isoFormat('D MMM') }}
                    </div>
                </div>
            </a>
            @endforeach
        @empty
            <x-ui.empty-state title="Tidak ada jadwal." />
        @endforelse
    </div>
</div>

@endsection

@push('page-scripts')
<script>
(function(){
    let activeDay = null;
    const allRows = document.querySelectorAll('.jp-event-row');
    const panelTitle = document.getElementById('panelTitle');
    const clearBtn   = document.getElementById('clearDayBtn');
    const totalEvents = {{ $totalEvents }};

    window.selectDay = function(day) {
        if (activeDay === day) { clearDay(); return; }
        activeDay = day;

        // Highlight selected day cell
        document.querySelectorAll('.jp-day').forEach(el => el.classList.remove('active'));
        const cell = document.querySelector(`.jp-day[data-day="${day}"]`);
        if(cell) { cell.classList.add('active'); cell.scrollIntoView({block:'nearest'}); }

        // Filter event list
        let count = 0;
        allRows.forEach(row => {
            const show = parseInt(row.dataset.day) === day;
            row.style.display = show ? '' : 'none';
            if(show) count++;
        });

        // Update panel header
        const dateStr = new Date({{ $year }}, {{ $month - 1 }}, day)
            .toLocaleDateString('id-ID', {day:'numeric', month:'long'});
        panelTitle.innerHTML = dateStr + ' <span class="badge bg-neutral-500 ms-1">' + count + '</span>';
        clearBtn.style.display = '';

        // Empty state
        showEmpty(count === 0);
    };

    window.clearDay = function() {
        activeDay = null;
        document.querySelectorAll('.jp-day').forEach(el => el.classList.remove('active'));
        allRows.forEach(row => row.style.display = '');
        panelTitle.innerHTML = 'Semua Job Bulan Ini <span class="badge bg-neutral-500 ms-1">' + totalEvents + '</span>';
        clearBtn.style.display = 'none';
        showEmpty(totalEvents === 0);
    };

    function showEmpty(isEmpty){
        let emptyEl = document.getElementById('emptyPanel');
        if(isEmpty && !emptyEl){
            emptyEl = document.createElement('div');
            emptyEl.id = 'emptyPanel';
            emptyEl.className = 'p-4 text-center text-muted small';
            emptyEl.innerHTML = '<iconify-icon icon="mdi:calendar-outline" class="fs-3 d-block mb-2"></iconify-icon>Tidak ada jadwal di hari ini.';
            document.getElementById('eventList').appendChild(emptyEl);
        } else if(!isEmpty && emptyEl){
            emptyEl.remove();
        }
    }

    // Auto-select today if current month
    @if($year == now()->year && $month == now()->month)
    const todayDay = {{ now()->day }};
    const todayCell = document.querySelector(`.jp-day[data-day="${todayDay}"]`);
    if(todayCell && todayCell.querySelector('.jp-day-count')) {
        selectDay(todayDay);
    }
    @endif
})();
</script>
@endpush
