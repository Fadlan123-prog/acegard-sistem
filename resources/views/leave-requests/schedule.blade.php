@extends('layout.index')

@section('title', 'Jadwal Libur')
@section('route-breadcrumb', 'leave-requests.schedule')
@section('breadcrumb', 'Jadwal Libur')

@push('page-styles')
<style>
/* ── Calendar Grid (same pattern as Jadwal Pemasangan) ─────────── */
.jl-wrap { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }
@media(max-width:1100px){ .jl-wrap { grid-template-columns:1fr; } }

.jl-cal { background:var(--white); border:1px solid var(--border-color); border-radius:12px; overflow:hidden; }
.jl-cal-header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid var(--border-color); background:var(--neutral-50); }
.jl-cal-title { font-size:16px; font-weight:700; color:var(--neutral-900); margin:0; }
.jl-cal-nav { display:flex; gap:6px; }

.jl-cal-grid { display:grid; grid-template-columns:repeat(7, 1fr); }
.jl-cal-dow { padding:8px 4px; text-align:center; font-size:11px; font-weight:700; color:var(--neutral-400); letter-spacing:.5px; border-bottom:1px solid var(--border-color); }
.jl-cal-dow:first-child { color:var(--danger-main); }

.jl-day { min-height:80px; padding:6px; border-right:1px solid var(--border-color); border-bottom:1px solid var(--border-color); cursor:pointer; transition:background .15s; position:relative; }
.jl-day:nth-child(7n) { border-right:none; }
.jl-day.empty { background:var(--neutral-50); cursor:default; }
.jl-day:hover:not(.empty) { background:var(--primary-light); }
.jl-day.active { background:var(--primary-light); outline:2px solid var(--primary-600); outline-offset:-2px; }
.jl-day.today .jl-day-num { background:var(--primary-600); color:#fff; }
.jl-day-num { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:600; margin-bottom:4px; color:var(--neutral-700); }
.jl-day-names { display:flex; flex-direction:column; gap:2px; }
.jl-day-name { font-size:10px; line-height:1.3; padding:1px 4px; border-radius:4px; background:var(--primary-light); color:var(--primary-700,var(--primary-600)); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.jl-day-more { font-size:10px; color:var(--neutral-400); }

/* ── Sidebar Panel ──────────────────────────────────────────── */
.jl-panel { background:var(--white); border:1px solid var(--border-color); border-radius:12px; overflow:hidden; position:sticky; top:80px; }
.jl-panel-head { padding:14px 18px; border-bottom:1px solid var(--border-color); background:var(--neutral-50); }
.jl-panel-title { font-size:13px; font-weight:700; margin:0; color:var(--neutral-900); }
.jl-panel-body { max-height:70vh; overflow-y:auto; }

.jl-event { display:flex; gap:10px; padding:12px 16px; border-bottom:1px solid var(--border-color); transition:background .12s; }
.jl-event:last-child { border-bottom:none; }
.jl-event:hover { background:var(--neutral-50); }
.jl-event-dot { width:10px; height:10px; border-radius:50%; margin-top:4px; flex-shrink:0; background:var(--primary-600); }
.jl-event-label { font-size:13px; font-weight:600; color:var(--neutral-900); line-height:1.3; }
.jl-event-sub { font-size:11px; color:var(--neutral-500); margin-top:1px; }
.jl-event-staff { font-size:11px; color:var(--neutral-400); margin-top:2px; }
.jl-event-date { font-size:11px; color:var(--neutral-400); white-space:nowrap; }
.jl-event-link { text-decoration:none; color:inherit; display:block; }

[data-theme="dark"] .jl-cal, [data-theme="dark"] .jl-panel { background:var(--dark-2); }
[data-theme="dark"] .jl-cal-header, [data-theme="dark"] .jl-panel-head { background:var(--dark-3); }
[data-theme="dark"] .jl-day.empty { background:var(--dark-3); }
[data-theme="dark"] .jl-event:hover { background:var(--dark-3); }
[data-theme="dark"] .jl-cal-title, [data-theme="dark"] .jl-panel-title, [data-theme="dark"] .jl-event-label { color:var(--neutral-100); }
[data-theme="dark"] .jl-day-num { color:var(--neutral-300); }
</style>
@endpush

@section('content')

@php
    use Carbon\Carbon;
    $today    = now()->toDateString();
    $prevMonth = Carbon::create($year, $month, 1)->subMonth();
    $nextMonth = Carbon::create($year, $month, 1)->addMonth();

    $firstDay    = Carbon::create($year, $month, 1);
    $daysInMonth = $firstDay->daysInMonth;
    $startOffset = ($firstDay->dayOfWeek === 0) ? 6 : ($firstDay->dayOfWeek - 1);
    $dayNames    = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
    $isSuperadmin = auth()->user()->hasRole('superadmin');
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h5 class="mb-0">Jadwal Libur — {{ $startOfMonth->locale('id')->isoFormat('MMMM YYYY') }}</h5>
    <x-ui.button :href="route('leave-requests.index')" variant="secondary" size="sm">Semua Pengajuan</x-ui.button>
</div>

<div class="jl-wrap">

    {{-- Calendar --}}
    <div>
        <div class="jl-cal">
            <div class="jl-cal-header">
                <h5 class="jl-cal-title">{{ Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM YYYY') }}</h5>
                <div class="jl-cal-nav">
                    <x-ui.button :href="route('leave-requests.schedule', ['year' => $prevMonth->year, 'month' => $prevMonth->month])" variant="outline" size="sm" class="px-2">
                        <iconify-icon icon="mdi:chevron-left"></iconify-icon>
                    </x-ui.button>
                    <x-ui.button :href="route('leave-requests.schedule', ['year' => now()->year, 'month' => now()->month])" variant="outline" size="sm" class="px-3">Hari ini</x-ui.button>
                    <x-ui.button :href="route('leave-requests.schedule', ['year' => $nextMonth->year, 'month' => $nextMonth->month])" variant="outline" size="sm" class="px-2">
                        <iconify-icon icon="mdi:chevron-right"></iconify-icon>
                    </x-ui.button>
                </div>
            </div>

            <div class="jl-cal-grid">
                @foreach($dayNames as $dn)
                    <div class="jl-cal-dow">{{ $dn }}</div>
                @endforeach

                @for($i = 0; $i < $startOffset; $i++)
                    <div class="jl-day empty"></div>
                @endfor

                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $dateStr  = Carbon::create($year, $month, $d)->toDateString();
                        $isToday  = $dateStr === $today;
                        $dayEvents = $events[$d] ?? [];
                    @endphp
                    <div class="jl-day {{ $isToday ? 'today' : '' }}"
                         data-day="{{ $d }}"
                         onclick="selectDay({{ $d }})">
                        <div class="jl-day-num">{{ $d }}</div>
                        @if(count($dayEvents) > 0)
                        <div class="jl-day-names">
                            @foreach(array_slice($dayEvents, 0, 2) as $ev)
                                <span class="jl-day-name">{{ \Illuminate\Support\Str::limit($ev['label'], 10) }}</span>
                            @endforeach
                            @if(count($dayEvents) > 2)
                                <span class="jl-day-more">+{{ count($dayEvents) - 2 }} lagi</span>
                            @endif
                        </div>
                        @endif
                    </div>
                @endfor

                @php
                    $totalCells = $startOffset + $daysInMonth;
                    $trailingCells = ($totalCells % 7 === 0) ? 0 : (7 - ($totalCells % 7));
                @endphp
                @for($i = 0; $i < $trailingCells; $i++)
                    <div class="jl-day empty"></div>
                @endfor
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-6 col-md-4">
                <x-ui.stats-card label="Total Pengajuan Disetujui" :value="$requests->count()" color="blue" accent />
            </div>
            <div class="col-6 col-md-4">
                <x-ui.stats-card label="Hari Libur Bulan Ini" :value="$totalEvents" color="purple" accent />
            </div>
            <div class="col-6 col-md-4">
                <x-ui.stats-card label="Karyawan Berbeda" :value="$requests->pluck('employee_id')->unique()->count()" color="amber" accent />
            </div>
        </div>
    </div>

    {{-- Right Panel: event list --}}
    <div class="jl-panel">
        <div class="jl-panel-head d-flex align-items-center justify-content-between">
            <p class="jl-panel-title mb-0" id="panelTitle">Semua Libur Bulan Ini
                <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $totalEvents }}</x-ui.badge>
            </p>
            <x-ui.button type="button" variant="outline" size="sm" id="clearDayBtn"
                    style="display:none" onclick="clearDay()">
                <iconify-icon icon="mdi:close"></iconify-icon>
            </x-ui.button>
        </div>
        <div class="jl-panel-body" id="eventList">

            @if($totalEvents === 0)
                <x-ui.empty-state title="Belum ada cuti yang disetujui" description="Tidak ada karyawan yang libur (disetujui) di bulan ini." />
            @else
                @foreach(range(1, $daysInMonth) as $d)
                    @php $dayEvs = $events[$d] ?? []; @endphp
                    @foreach($dayEvs as $ev)
                    <a href="{{ $ev['route'] }}" class="jl-event-link jl-event-row" data-day="{{ $d }}">
                        <div class="jl-event">
                            <span class="jl-event-dot" style="margin-top:5px"></span>
                            <div class="flex-1 min-w-0">
                                <div class="jl-event-label">{{ $ev['label'] }}</div>
                                <div class="jl-event-sub">{{ $ev['sub'] }}</div>
                                @if($isSuperadmin && $ev['staff'])
                                    <div class="jl-event-staff">{{ $ev['staff'] }}</div>
                                @endif
                            </div>
                            <div class="jl-event-date">
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

@endsection

@push('page-scripts')
<script>
(function(){
    let activeDay = null;
    const allRows = document.querySelectorAll('.jl-event-row');
    const panelTitle = document.getElementById('panelTitle');
    const clearBtn   = document.getElementById('clearDayBtn');
    const totalEvents = {{ $totalEvents }};

    window.selectDay = function(day) {
        if (activeDay === day) { clearDay(); return; }
        activeDay = day;

        document.querySelectorAll('.jl-day').forEach(el => el.classList.remove('active'));
        const cell = document.querySelector(`.jl-day[data-day="${day}"]`);
        if(cell) { cell.classList.add('active'); cell.scrollIntoView({block:'nearest'}); }

        let count = 0;
        allRows.forEach(row => {
            const show = parseInt(row.dataset.day) === day;
            row.style.display = show ? '' : 'none';
            if(show) count++;
        });

        const dateStr = new Date({{ $year }}, {{ $month - 1 }}, day)
            .toLocaleDateString('id-ID', {day:'numeric', month:'long'});
        panelTitle.innerHTML = dateStr + ' <span class="badge bg-neutral-500 ms-1">' + count + '</span>';
        clearBtn.style.display = '';

        showEmpty(count === 0);
    };

    window.clearDay = function() {
        activeDay = null;
        document.querySelectorAll('.jl-day').forEach(el => el.classList.remove('active'));
        allRows.forEach(row => row.style.display = '');
        panelTitle.innerHTML = 'Semua Libur Bulan Ini <span class="badge bg-neutral-500 ms-1">' + totalEvents + '</span>';
        clearBtn.style.display = 'none';
        showEmpty(totalEvents === 0);
    };

    function showEmpty(isEmpty){
        let emptyEl = document.getElementById('emptyPanel');
        if(isEmpty && !emptyEl){
            emptyEl = document.createElement('div');
            emptyEl.id = 'emptyPanel';
            emptyEl.className = 'p-4 text-center text-muted small';
            emptyEl.innerHTML = '<iconify-icon icon="mdi:calendar-outline" class="fs-3 d-block mb-2"></iconify-icon>Tidak ada yang libur di hari ini.';
            document.getElementById('eventList').appendChild(emptyEl);
        } else if(!isEmpty && emptyEl){
            emptyEl.remove();
        }
    }

    @if($year == now()->year && $month == now()->month)
    const todayDay = {{ now()->day }};
    const todayCell = document.querySelector(`.jl-day[data-day="${todayDay}"]`);
    if(todayCell && todayCell.querySelector('.jl-day-names')) {
        selectDay(todayDay);
    }
    @endif
})();
</script>
@endpush
