@extends('dashboard.index')

@section('title', 'Catat Pemakaian Motor')
@section('route-breadcrumb', 'petty-cash.index')
@section('breadcrumb', 'Catat Pemakaian Motor')

@section('content')

{{-- Flash errors --}}
@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <strong>Terjadi kesalahan.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif

{{-- Same card/header structure as petty-cash/create-purchase --}}
<x-ui.card>

    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">
                <iconify-icon icon="mdi:motorbike" class="me-2 text-primary"></iconify-icon>
                Catat Pemakaian Motor
            </div>
        </div>
        <small class="text-muted">Catat satu atau lebih perjalanan sekaligus — mendukung tanggal lampau</small>
    @endslot

    <form method="POST" action="{{ route('petty-cash.store-bulk-out') }}" id="formOut">
    @csrf

    {{-- Cabang --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <x-ui.form-field label="Cabang" name="branch_id" :required="true">
                <select name="branch_id" id="branch_id"
                        class="form-select @error('branch_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected(old('branch_id', $branchId) == $b->id)>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </x-ui.form-field>
        </div>
    </div>

    {{-- Section heading — same pattern as purchases/receipts/create --}}
    <h6 class="fw-semibold mb-3">Detail Perjalanan</h6>

    {{-- Trips Table — same component as petty-cash/index, rows rendered via loop --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @foreach([
                        ['#',            'ps-3 text-center', 'width:44px'],
                        ['Tanggal',      '',                 'min-width:130px'],
                        ['Pengemudi',    '',                 'min-width:170px'],
                        ['Motor',        '',                 'min-width:155px'],
                        ['Tujuan',       '',                 'min-width:185px'],
                        ['Jarak (km)',   '',                 'min-width:115px'],
                        ['Biaya Bensin', 'text-end',         'min-width:130px'],
                        ['',             '',                 'width:52px'],
                    ] as [$col, $cls, $sty])
                    <th class="{{ $cls }}" style="{{ $sty }}">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody id="rowsTbody">
                {{-- Rows injected by JavaScript --}}
            </tbody>
        </table>
    </div>

    {{-- Add Row --}}
    <div class="mt-2">
        <x-ui.button type="button" id="addRowBtn" variant="outline" class="w-100">
            <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Tambah Perjalanan
        </x-ui.button>
    </div>

    {{-- Info — same as create-purchase --}}
    <x-ui.alert type="info" class="mt-3 mb-0">
        Biaya bensin dihitung otomatis: (jarak × Rp425) × 2 (pulang-pergi), dibulatkan ke ribuan terdekat.
    </x-ui.alert>

    {{-- Submit — same pattern as create-purchase / create-in --}}
    <div class="d-flex gap-2 mt-4">
        <x-ui.button variant="primary" type="submit" id="submitBtn">
            <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Simpan
        </x-ui.button>
        <x-ui.button variant="secondary" href="{{ route('petty-cash.index') }}">Batal</x-ui.button>
    </div>

    </form>

</x-ui.card>

{{--
    Hidden employee options source.
    JS reads innerHTML and clones into each row's <select>.
    Using a hidden <select> (no name attr) avoids template/iconify issues entirely.
--}}
<select id="empOptsSrc" style="display:none" aria-hidden="true" tabindex="-1">
    <option value="">-- Pilih --</option>
    @foreach($employees as $emp)
        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
    @endforeach
</select>

@push('page-scripts')
<script>
(function () {
    const RATE   = 425;
    let   idx    = 0;
    const TODAY  = '{{ now()->format("Y-m-d") }}';

    const tbody     = document.getElementById('rowsTbody');
    const addBtn    = document.getElementById('addRowBtn');
    const form      = document.getElementById('formOut');
    const empSrc    = document.getElementById('empOptsSrc');
    const submitBtn = document.getElementById('submitBtn');

    const calc  = km => Math.round(((km * RATE) * 2) / 1000) * 1000;
    const rpFmt = n  => 'Rp' + n.toLocaleString('id-ID');

    function setFuelSpan(span, amount) {
        if (amount > 0) {
            span.textContent = rpFmt(amount);
            span.className   = 'row-fuel fw-semibold text-success';
        } else {
            span.textContent = '—';
            span.className   = 'row-fuel fw-semibold text-muted';
        }
    }

    function renum() {
        tbody.querySelectorAll('.trip-row').forEach(function (row, i) {
            row.querySelector('.row-num').textContent = i + 1;
        });
    }

    function addRow(vals) {
        var i = idx++;

        var tr = document.createElement('tr');
        tr.className = 'trip-row';

        // Tanggal cell
        var tdNum = document.createElement('td');
        tdNum.className = 'ps-3 text-center text-muted fw-semibold';
        tdNum.innerHTML = '<span class="row-num"></span>';

        var tdDate = document.createElement('td');
        var inpDate = document.createElement('input');
        inpDate.type = 'date';
        inpDate.name = 'rows[' + i + '][date]';
        inpDate.className = 'form-control form-control-sm row-date';
        inpDate.value = vals ? (vals.date || TODAY) : TODAY;
        inpDate.required = true;
        tdDate.appendChild(inpDate);

        // Pengemudi cell
        var tdEmp = document.createElement('td');
        var selEmp = document.createElement('select');
        selEmp.name = 'rows[' + i + '][employee_id]';
        selEmp.className = 'form-select form-select-sm row-emp';
        selEmp.required = true;
        selEmp.innerHTML = empSrc.innerHTML;
        if (vals && vals.emp) selEmp.value = vals.emp;
        tdEmp.appendChild(selEmp);

        // Motor cell
        var tdMotor = document.createElement('td');
        var inpMotor = document.createElement('input');
        inpMotor.type = 'text';
        inpMotor.name = 'rows[' + i + '][motor_name]';
        inpMotor.className = 'form-control form-control-sm row-motor';
        inpMotor.placeholder = 'Honda Beat B 1234 XYZ...';
        inpMotor.required = true;
        if (vals && vals.motor) inpMotor.value = vals.motor;
        tdMotor.appendChild(inpMotor);

        // Tujuan cell
        var tdDest = document.createElement('td');
        var inpDest = document.createElement('input');
        inpDest.type = 'text';
        inpDest.name = 'rows[' + i + '][destination]';
        inpDest.className = 'form-control form-control-sm row-dest';
        inpDest.placeholder = 'Masukkan tujuan...';
        inpDest.required = true;
        if (vals && vals.dest) inpDest.value = vals.dest;
        tdDest.appendChild(inpDest);

        // Jarak cell
        var tdKm = document.createElement('td');
        var ig = document.createElement('div');
        ig.className = 'input-group input-group-sm';
        var inpKm = document.createElement('input');
        inpKm.type = 'number';
        inpKm.name = 'rows[' + i + '][distance_km]';
        inpKm.className = 'form-control row-km';
        inpKm.setAttribute('min', '0.1');
        inpKm.setAttribute('step', '0.1');
        inpKm.placeholder = '0.0';
        inpKm.required = true;
        if (vals && vals.km > 0) inpKm.value = vals.km;
        var igText = document.createElement('span');
        igText.className = 'input-group-text';
        igText.textContent = 'km';
        ig.appendChild(inpKm);
        ig.appendChild(igText);
        tdKm.appendChild(ig);

        // Biaya cell
        var tdFuel = document.createElement('td');
        tdFuel.className = 'text-end pe-3';
        var spanFuel = document.createElement('span');
        setFuelSpan(spanFuel, vals && vals.km > 0 ? calc(vals.km) : 0);
        tdFuel.appendChild(spanFuel);

        // Delete cell
        var tdDel = document.createElement('td');
        tdDel.className = 'pe-2';
        var btnDel = document.createElement('button');
        btnDel.type = 'button';
        btnDel.className = 'ui-btn ui-btn-danger-outline ui-btn-sm del-row';
        btnDel.title = 'Hapus baris';
        btnDel.innerHTML = '<span class="btn-text"><iconify-icon icon="mdi:trash-can-outline"></iconify-icon></span>';
        tdDel.appendChild(btnDel);

        tr.appendChild(tdNum);
        tr.appendChild(tdDate);
        tr.appendChild(tdEmp);
        tr.appendChild(tdMotor);
        tr.appendChild(tdDest);
        tr.appendChild(tdKm);
        tr.appendChild(tdFuel);
        tr.appendChild(tdDel);

        // Events
        inpKm.addEventListener('input', function () {
            var v = parseFloat(this.value) || 0;
            setFuelSpan(spanFuel, v > 0 ? calc(v) : 0);
        });

        btnDel.addEventListener('click', function () {
            if (tbody.querySelectorAll('.trip-row').length <= 1) return;
            tr.remove();
            renum();
        });

        tbody.appendChild(tr);
        renum();
        return tr;
    }

    addBtn.addEventListener('click', function () {
        var tr = addRow(null);
        tr.querySelector('.row-date').focus();
        tr.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    form.addEventListener('submit', function (e) {
        if (!tbody.querySelector('.trip-row')) {
            e.preventDefault();
            alert('Tambahkan minimal 1 baris perjalanan.');
            return;
        }
        if (submitBtn && !submitBtn.classList.contains('ui-btn-loading')) {
            submitBtn.classList.add('ui-btn-loading');
            submitBtn.setAttribute('aria-busy', 'true');
            var spinner = document.createElement('span');
            spinner.className = 'btn-spinner';
            spinner.setAttribute('aria-hidden', 'true');
            submitBtn.prepend(spinner);
        }
    });

    // Restore old() values after validation failure
    @if(old('rows'))
        @foreach(old('rows', []) as $i => $row)
        addRow({
            date:  '{{ $row["date"]        ?? now()->format("Y-m-d") }}',
            emp:   '{{ $row["employee_id"] ?? "" }}',
            motor: '{{ addslashes($row["motor_name"]  ?? "") }}',
            dest:  '{{ addslashes($row["destination"] ?? "") }}',
            km:    {{ (float)($row["distance_km"] ?? 0) }},
        });
        @endforeach
    @else
        addRow(null);
    @endif

})();
</script>
@endpush

@endsection
