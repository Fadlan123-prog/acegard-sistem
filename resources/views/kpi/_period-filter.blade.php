{{-- Filter periode KPI (pola sama dengan filter payroll/purchases: label + select + tombol outline) --}}
@php
    $monthNames = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
@endphp
<form method="GET" action="{{ $action }}" class="row g-2 mb-4 align-items-end">
    <div class="col-6 col-md-3">
        <label for="kpi-month" class="form-label">Bulan</label>
        <select id="kpi-month" name="month" class="form-select">
            @foreach ($monthNames as $n => $label)
                <option value="{{ $n }}" @selected($n === $month)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-2">
        <label for="kpi-year" class="form-label">Tahun</label>
        <input id="kpi-year" type="number" name="year" min="2026" max="2100" value="{{ $year }}" class="form-control">
    </div>
    @if (!empty($withDivision))
    <div class="col-12 col-md-3">
        <label for="kpi-division" class="form-label">Divisi</label>
        <select id="kpi-division" name="division" class="form-select">
            <option value="">-- Semua Divisi --</option>
            @foreach (\App\Models\KpiTarget::DIVISIONS as $key => $label)
                <option value="{{ $key }}" @selected(($division ?? null) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="col-12 col-md-4 d-flex gap-2">
        <x-ui.button type="submit" variant="outline">Filter</x-ui.button>
        <x-ui.button :href="$action" variant="outline">Reset</x-ui.button>
    </div>
</form>
