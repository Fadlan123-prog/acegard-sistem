{{-- HR Identity & BPJS fields — included in create.blade.php and edit.blade.php --}}
<hr class="my-4">
<h6 class="text-muted fw-semibold mb-3">Data HR & BPJS</h6>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">NPWP</label>
        <input type="text" name="npwp" class="form-control"
               value="{{ old('npwp', $employee->npwp ?? '') }}"
               placeholder="xx.xxx.xxx.x-xxx.xxx" maxlength="20">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Tanggal Bergabung</label>
        <input type="date" name="hire_date" class="form-control"
               value="{{ old('hire_date', isset($employee) && $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status Kepegawaian</label>
    <select name="employment_status" class="form-select">
        @php
            $statuses = ['permanent' => 'Tetap', 'contract' => 'Kontrak', 'probation' => 'Percobaan'];
            $currentStatus = old('employment_status', $employee->employment_status ?? 'permanent');
        @endphp
        @foreach($statuses as $val => $label)
            <option value="{{ $val }}" {{ $currentStatus === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>

<hr class="my-3">
<h6 class="text-muted fw-semibold mb-3">BPJS Ketenagakerjaan</h6>

<div class="mb-3">
    <input type="hidden" name="bpjs_ketenagakerjaan_enrolled" value="0">
    <div class="form-check">
        <input type="checkbox" name="bpjs_ketenagakerjaan_enrolled" value="1"
               class="form-check-input" id="bpjsKtEnrolled"
               {{ old('bpjs_ketenagakerjaan_enrolled', $employee->bpjs_ketenagakerjaan_enrolled ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="bpjsKtEnrolled">
            Karyawan terdaftar BPJS Ketenagakerjaan
        </label>
    </div>
</div>

<div id="bpjsKtFields" style="{{ old('bpjs_ketenagakerjaan_enrolled', $employee->bpjs_ketenagakerjaan_enrolled ?? false) ? '' : 'display:none' }}">
    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">No. BPJS Ketenagakerjaan</label>
            <input type="text" name="bpjs_ketenagakerjaan_no" class="form-control"
                   value="{{ old('bpjs_ketenagakerjaan_no', $employee->bpjs_ketenagakerjaan_no ?? '') }}"
                   maxlength="30">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Kelompok Risiko JKK</label>
            <select name="jkk_risk_group" class="form-select">
                @php $currentGroup = old('jkk_risk_group', $employee->jkk_risk_group ?? 1) @endphp
                <option value="1" {{ $currentGroup == 1 ? 'selected' : '' }}>1 — 0.24% (Rendah)</option>
                <option value="2" {{ $currentGroup == 2 ? 'selected' : '' }}>2 — 0.54%</option>
                <option value="3" {{ $currentGroup == 3 ? 'selected' : '' }}>3 — 0.89%</option>
                <option value="4" {{ $currentGroup == 4 ? 'selected' : '' }}>4 — 1.27%</option>
                <option value="5" {{ $currentGroup == 5 ? 'selected' : '' }}>5 — 1.74% (Tinggi)</option>
            </select>
        </div>
    </div>
</div>

<hr class="my-3">
<h6 class="text-muted fw-semibold mb-3">BPJS Kesehatan</h6>

<div class="mb-3">
    <input type="hidden" name="bpjs_kesehatan_enrolled" value="0">
    <div class="form-check">
        <input type="checkbox" name="bpjs_kesehatan_enrolled" value="1"
               class="form-check-input" id="bpjsKesEnrolled"
               {{ old('bpjs_kesehatan_enrolled', $employee->bpjs_kesehatan_enrolled ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="bpjsKesEnrolled">
            Karyawan terdaftar BPJS Kesehatan
        </label>
    </div>
</div>

<div id="bpjsKesFields" style="{{ old('bpjs_kesehatan_enrolled', $employee->bpjs_kesehatan_enrolled ?? false) ? '' : 'display:none' }}">
    <div class="mb-3">
        <label class="form-label">No. BPJS Kesehatan</label>
        <input type="text" name="bpjs_kesehatan_no" class="form-control"
               value="{{ old('bpjs_kesehatan_no', $employee->bpjs_kesehatan_no ?? '') }}"
               maxlength="30">
    </div>
</div>

@push('page-scripts')
<script>
    document.getElementById('bpjsKtEnrolled').addEventListener('change', function () {
        document.getElementById('bpjsKtFields').style.display = this.checked ? '' : 'none';
    });
    document.getElementById('bpjsKesEnrolled').addEventListener('change', function () {
        document.getElementById('bpjsKesFields').style.display = this.checked ? '' : 'none';
    });
</script>
@endpush
