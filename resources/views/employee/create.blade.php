@extends('dashboard.index')

@section('title', 'Tambah Karyawan')
@section('route-breadcrumb', 'employee.index')
@section('breadcrumb', 'Tambah Karyawan')

@push('page-styles')
<style>
/* ── Base ─────────────────────────────────────────────────────────── */
.emp-wrap { display: grid; grid-template-columns: 220px 1fr; gap: 24px; align-items: start; }
@media (max-width: 991px) { .emp-wrap { grid-template-columns: 1fr; } }

/* ── Side Nav ─────────────────────────────────────────────────────── */
.emp-nav {
    position: sticky;
    top: 80px;
    background: var(--white);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 8px;
    overflow: hidden;
}
@media (max-width: 991px) { .emp-nav { display: none; } }

.emp-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--neutral-500);
    text-decoration: none;
    transition: background .15s, color .15s;
    cursor: pointer;
}
.emp-nav-item i { font-size: 16px; flex-shrink: 0; }
.emp-nav-item:hover { background: var(--neutral-100); color: var(--neutral-700); }
.emp-nav-item.active {
    background: var(--primary-light);
    color: var(--primary-600);
    font-weight: 600;
}
.emp-nav-divider {
    height: 1px;
    background: var(--border-color);
    margin: 6px 4px;
}

/* ── Sections ─────────────────────────────────────────────────────── */
.emp-sections { display: flex; flex-direction: column; gap: 16px; }

.emp-section {
    background: var(--white);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}
.emp-section-head {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color);
    background: var(--neutral-50);
}
.emp-section-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: var(--primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.emp-section-icon i { font-size: 18px; color: var(--primary-600); }
.emp-section-meta { flex: 1; min-width: 0; }
.emp-section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--neutral-800);
    margin: 0 0 1px;
}
.emp-section-desc {
    font-size: 12px;
    color: var(--neutral-400);
    margin: 0;
}
.emp-section-body { padding: 24px; }

/* ── Fields ───────────────────────────────────────────────────────── */
.emp-field { display: flex; flex-direction: column; gap: 6px; }
.emp-label {
    font-size: 11.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .055em;
    color: var(--neutral-500);
}
.emp-label .req { color: #ef4444; margin-left: 2px; font-style: normal; }
.emp-hint {
    font-size: 11.5px;
    color: var(--neutral-400);
    line-height: 1.45;
    margin: 0;
}
.emp-form .form-control,
.emp-form .form-select {
    font-size: 14px;
    border: 1px solid var(--input-stroke);
    background: var(--input-bg);
    border-radius: 8px;
    padding: 9px 13px;
    color: var(--neutral-800);
    line-height: 1.5;
    width: 100%;
    transition: border-color .15s, box-shadow .15s;
}
.emp-form .form-control::placeholder { color: var(--neutral-400); }
.emp-form .form-control:focus,
.emp-form .form-select:focus {
    border-color: var(--primary-600);
    box-shadow: 0 0 0 3px var(--primary-light);
    outline: none;
}
.emp-form .input-group .input-group-text {
    border: 1px solid var(--input-stroke);
    border-right: none;
    background: var(--neutral-100);
    color: var(--neutral-500);
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px 0 0 8px;
    padding: 9px 13px;
}
.emp-form .input-group .form-control { border-radius: 0 8px 8px 0; }

/* ── Toggle ───────────────────────────────────────────────────────── */
.emp-toggle {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    background: var(--neutral-50);
    border: 1px solid var(--border-color);
    border-radius: 10px;
}
.emp-toggle .form-check-input {
    width: 2.4em;
    height: 1.3em;
    margin-top: 1px;
    flex-shrink: 0;
    cursor: pointer;
}
.emp-toggle .form-check-input:checked {
    background-color: var(--primary-600);
    border-color: var(--primary-600);
}
.emp-toggle-body { flex: 1; }
.emp-toggle-label {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--neutral-700);
    cursor: pointer;
    display: block;
    margin-bottom: 2px;
}
.emp-toggle-hint {
    font-size: 12px;
    color: var(--neutral-400);
    margin: 0;
}

/* ── BPJS Collapsible ────────────────────────────────────────────── */
.emp-bpjs-panel {
    border: 1px solid var(--border-color);
    border-radius: 10px;
    overflow: hidden;
}
.emp-bpjs-panel + .emp-bpjs-panel { margin-top: 12px; }
.emp-bpjs-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: var(--neutral-50);
    cursor: pointer;
}
.emp-bpjs-head-left { display: flex; align-items: center; gap: 10px; }
.emp-bpjs-badge {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--info-surface);
    display: flex;
    align-items: center;
    justify-content: center;
}
.emp-bpjs-badge i { font-size: 14px; color: #3b82f6; }
.emp-bpjs-name { font-size: 13.5px; font-weight: 600; color: var(--neutral-700); }
.emp-bpjs-status { font-size: 11px; color: var(--neutral-400); font-weight: 400; margin-top: 1px; }
.emp-bpjs-fields {
    padding: 16px;
    border-top: 1px solid var(--border-color);
    background: var(--white);
    display: none;
}
.emp-bpjs-fields.open { display: block; }

/* ── Action Bar ───────────────────────────────────────────────────── */
.emp-action-bar {
    position: sticky;
    bottom: 0;
    z-index: 100;
    background: var(--white);
    border-top: 1px solid var(--border-color);
    padding: 14px 24px;
    margin: 16px -24px -14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    box-shadow: 0 -4px 12px rgba(0,0,0,.06);
}
.emp-action-hint {
    font-size: 12px;
    color: var(--neutral-400);
    display: flex;
    align-items: center;
    gap: 5px;
}
.emp-action-hint i { font-size: 14px; color: var(--neutral-300); }

/* ── Dark mode ────────────────────────────────────────────────────── */
[data-theme="dark"] .emp-section-head,
[data-theme="dark"] .emp-toggle,
[data-theme="dark"] .emp-bpjs-head { background: var(--neutral-100); }
[data-theme="dark"] .emp-bpjs-fields,
[data-theme="dark"] .emp-section,
[data-theme="dark"] .emp-nav,
[data-theme="dark"] .emp-action-bar { background: var(--dark-2); }
[data-theme="dark"] .emp-section-title { color: var(--neutral-700); }
[data-theme="dark"] .emp-form .form-control,
[data-theme="dark"] .emp-form .form-select { background: var(--dark-3); border-color: var(--neutral-300); }
[data-theme="dark"] .emp-form .input-group .input-group-text { background: var(--neutral-200); border-color: var(--neutral-300); }

/* ── Unified Field Components ─────────────────────────────────────── */
.fi-wrap {
    display: flex;
    align-items: stretch;
    border: 1px solid var(--input-stroke);
    border-radius: 8px;
    overflow: hidden;
    background: var(--input-bg);
    transition: border-color .15s, box-shadow .15s;
}
.fi-wrap:focus-within {
    border-color: var(--primary-600);
    box-shadow: 0 0 0 3px var(--primary-light);
}
.fi-wrap.is-invalid { border-color: #ef4444; }
.fi-wrap.is-invalid:focus-within { box-shadow: 0 0 0 3px rgba(239,68,68,.15); }
.fi-wrap.is-valid { border-color: #22c55e; }
.fi-wrap.is-valid:focus-within { box-shadow: 0 0 0 3px rgba(34,197,94,.15); }

.fi-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 12px;
    border-right: 1px solid var(--input-stroke);
    background: var(--neutral-100);
    flex-shrink: 0;
    white-space: nowrap;
    transition: border-color .15s;
}
.fi-badge-wa i { font-size: 15px; color: #25D366; }
.fi-badge-cc {
    font-size: 13px;
    font-weight: 700;
    color: var(--neutral-500);
    letter-spacing: -.01em;
}
.fi-badge-rp {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--neutral-500);
    letter-spacing: .02em;
    transition: color .15s;
}
.fi-wrap:focus-within .fi-badge-rp { color: var(--primary-600); }

.emp-form .fi-inner {
    flex: 1;
    min-width: 0;
    border: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    padding-left: 12px !important;
}
.emp-form .fi-inner:focus { outline: none; border: none !important; box-shadow: none !important; }
.emp-form .fi-inner[type="number"]::-webkit-inner-spin-button,
.emp-form .fi-inner[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; }
.emp-form .fi-inner[type="number"] { -moz-appearance: textfield; }

.fi-msg {
    display: none;
    font-size: 11.5px;
    margin-top: 5px;
    align-items: center;
    gap: 4px;
    line-height: 1.4;
}
.fi-msg.show { display: flex; }
.fi-msg i { font-size: 13px; flex-shrink: 0; }
.fi-msg.error { color: #ef4444; }
.fi-msg.success { color: #22c55e; }

[data-theme="dark"] .fi-badge { background: var(--neutral-200); border-right-color: var(--neutral-300); }
[data-theme="dark"] .fi-wrap { background: var(--dark-3); border-color: var(--neutral-300); }
</style>
@endpush

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="ui-card-title">Tambah Karyawan</div>
                <small class="text-muted">Data ini akan digunakan untuk absensi, komisi, dan laporan gaji.</small>
            </div>
            <x-ui.button href="{{ route('employee.index') }}" variant="secondary" size="sm">
                <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon> Kembali
            </x-ui.button>
        </div>
    @endslot

    @include('partials.form-errors')

    <form method="POST" action="{{ route('employee.store') }}" class="emp-form" id="empForm">
        @csrf

        <div class="emp-wrap">

            {{-- ── Sidebar Navigation ───────────────────────────────── --}}
            <aside class="emp-nav" role="navigation" aria-label="Navigasi bagian form">
                <a class="emp-nav-item active" href="#sec-identity" data-section="sec-identity">
                    <iconify-icon icon="mdi:account-outline"></iconify-icon> Identitas & Penugasan
                </a>
                <div class="emp-nav-divider"></div>
                <a class="emp-nav-item" href="#sec-salary" data-section="sec-salary">
                    <iconify-icon icon="mdi:currency-usd-circle-outline"></iconify-icon> Gaji & Kompensasi
                </a>
                <div class="emp-nav-divider"></div>
                <a class="emp-nav-item" href="#sec-bpjs" data-section="sec-bpjs">
                    <iconify-icon icon="mdi:shield-account-outline"></iconify-icon> Pajak & BPJS
                </a>
            </aside>

            {{-- ── Form Sections ────────────────────────────────────── --}}
            <div class="emp-sections">

                {{-- Section 1: Identitas & Penugasan --}}
                <section class="emp-section" id="sec-identity">
                    <div class="emp-section-head">
                        <div class="emp-section-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></div>
                        <div class="emp-section-meta">
                            <p class="emp-section-title">Identitas & Penugasan</p>
                            <p class="emp-section-desc">Nama, kontak, posisi, dan cabang tempat karyawan bekerja.</p>
                        </div>
                    </div>
                    <div class="emp-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="emp-field">
                                    <label class="emp-label" for="name">Nama Lengkap <em class="req">*</em></label>
                                    <input type="text" id="name" name="name" class="form-control"
                                           value="{{ old('name') }}"
                                           placeholder="Nama lengkap karyawan" required
                                           aria-required="true">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="emp-field">
                                    <label class="emp-label" for="phone_display">Nomor WhatsApp</label>
                                    <div class="fi-wrap" id="waWrap">
                                        <div class="fi-badge fi-badge-wa" aria-hidden="true">
                                            <iconify-icon icon="mdi:whatsapp"></iconify-icon>
                                            <span class="fi-badge-cc">+62</span>
                                        </div>
                                        <input type="tel" id="phone_display"
                                               class="form-control fi-inner"
                                               placeholder="812 3456 7890"
                                               inputmode="numeric"
                                               autocomplete="tel-national"
                                               maxlength="15"
                                               aria-label="Nomor WhatsApp tanpa kode negara"
                                               aria-describedby="waHint waMsg">
                                        <input type="hidden" name="phone_number" id="phone_number"
                                               value="{{ old('phone_number') }}">
                                    </div>
                                    <div class="fi-msg" id="waMsg" role="alert" aria-live="polite"></div>
                                    <p class="emp-hint" id="waHint">Ketik tanpa 0 di depan. Contoh: 812 3456 7890</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="emp-field">
                                    <label class="emp-label" for="job_position">Posisi / Jabatan</label>
                                    <select id="job_position" name="job_position" class="form-select">
                                        <option value="">— Pilih jabatan —</option>
                                        @foreach (['Teknisi','Teknisi Kaca Film','Teknisi PPF','Detailer','Kenek','Marketing','Admin','Lainnya'] as $pos)
                                            <option value="{{ $pos }}" @selected(old('job_position') == $pos)>{{ $pos }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="emp-field">
                                    <label class="emp-label" for="branch_id">Cabang <em class="req">*</em></label>
                                    <select id="branch_id" name="branch_id" class="form-select" required aria-required="true">
                                        <option value="">— Pilih cabang —</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                @selected(old('branch_id', session('active_branch_id')) == $branch->id)>
                                                {{ $branch->name }} — {{ $branch->city ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="emp-field">
                                    <label class="emp-label" for="is_active">Status</label>
                                    <select id="is_active" name="is_active" class="form-select">
                                        <option value="1" @selected(old('is_active', 1) == 1)>Aktif</option>
                                        <option value="0" @selected(old('is_active') === '0')>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="emp-field">
                                    <label class="emp-label" for="hire_date">Tanggal Bergabung</label>
                                    <input type="date" id="hire_date" name="hire_date" class="form-control"
                                           value="{{ old('hire_date', '') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="emp-field">
                                    <label class="emp-label" for="employment_status">Status Kepegawaian</label>
                                    <select id="employment_status" name="employment_status" class="form-select">
                                        @php $currentStatus = old('employment_status', 'permanent'); @endphp
                                        <option value="permanent" @selected($currentStatus === 'permanent')>Tetap</option>
                                        <option value="contract"  @selected($currentStatus === 'contract')>Kontrak</option>
                                        <option value="probation" @selected($currentStatus === 'probation')>Percobaan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="emp-field">
                                    <label class="emp-label" for="npwp">NPWP</label>
                                    <input type="text" id="npwp" name="npwp" class="form-control"
                                           value="{{ old('npwp', '') }}"
                                           placeholder="xx.xxx.xxx.x-xxx.xxx" maxlength="20">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Section 2: Gaji & Kompensasi --}}
                <section class="emp-section" id="sec-salary">
                    <div class="emp-section-head">
                        <div class="emp-section-icon"><iconify-icon icon="mdi:currency-usd-circle-outline"></iconify-icon></div>
                        <div class="emp-section-meta">
                            <p class="emp-section-title">Gaji & Kompensasi</p>
                            <p class="emp-section-desc">Gaji pokok, uang makan, dan pengaturan komisi.</p>
                        </div>
                    </div>
                    <div class="emp-section-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="emp-field">
                                    <label class="emp-label" for="salary_display">Gaji Bulanan</label>
                                    <div class="fi-wrap">
                                        <div class="fi-badge" aria-hidden="true">
                                            <span class="fi-badge-rp">Rp</span>
                                        </div>
                                        <input type="text" id="salary_display"
                                               class="form-control fi-inner"
                                               placeholder="4.000.000"
                                               inputmode="numeric"
                                               autocomplete="off"
                                               aria-label="Gaji bulanan dalam Rupiah"
                                               aria-describedby="salaryHint">
                                        <input type="hidden" name="monthly_salary" id="monthly_salary"
                                               value="{{ old('monthly_salary', 0) }}">
                                    </div>
                                    <p class="emp-hint" id="salaryHint">Gaji harian dihitung prorate dari nilai ini.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="emp-field">
                                    <label class="emp-label" for="meal_display">Uang Makan Khusus</label>
                                    <div class="fi-wrap">
                                        <div class="fi-badge" aria-hidden="true">
                                            <span class="fi-badge-rp">Rp</span>
                                        </div>
                                        <input type="text" id="meal_display"
                                               class="form-control fi-inner"
                                               placeholder="Default jabatan"
                                               inputmode="numeric"
                                               autocomplete="off"
                                               aria-label="Nominal uang makan khusus dalam Rupiah"
                                               aria-describedby="mealHint">
                                        <input type="hidden" name="meal_allowance_rate" id="meal_allowance_rate"
                                               value="{{ old('meal_allowance_rate') }}">
                                    </div>
                                    <p class="emp-hint" id="mealHint">Default: Teknisi Rp50.000 · Kenek Rp30.000 · Lainnya Rp0/hari.</p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="hidden" name="meal_allowance_enabled" value="0">
                                <label class="emp-toggle" for="mealAllowanceEnabled">
                                    <input type="checkbox" id="mealAllowanceEnabled"
                                           name="meal_allowance_enabled" value="1"
                                           class="form-check-input"
                                           {{ old('meal_allowance_enabled', $employee->meal_allowance_enabled ?? true) ? 'checked' : '' }}>
                                    <div class="emp-toggle-body">
                                        <span class="emp-toggle-label">Berhak Uang Makan</span>
                                        <p class="emp-toggle-hint">Karyawan mendapat uang makan di setiap hari hadir.</p>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="ppf_admin_commission" value="0">
                                <label class="emp-toggle" for="ppfAdminCommission">
                                    <input type="checkbox" id="ppfAdminCommission"
                                           name="ppf_admin_commission" value="1"
                                           class="form-check-input"
                                           {{ old('ppf_admin_commission') ? 'checked' : '' }}>
                                    <div class="emp-toggle-body">
                                        <span class="emp-toggle-label">Komisi Admin PPF</span>
                                        <p class="emp-toggle-hint">Mendapat komisi 2% dari setiap invoice PPF yang masuk.</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Section 3: BPJS --}}
                <section class="emp-section" id="sec-bpjs">
                    <div class="emp-section-head">
                        <div class="emp-section-icon"><iconify-icon icon="mdi:shield-account-outline"></iconify-icon></div>
                        <div class="emp-section-meta">
                            <p class="emp-section-title">Asuransi BPJS</p>
                            <p class="emp-section-desc">Pendaftaran BPJS Ketenagakerjaan dan BPJS Kesehatan.</p>
                        </div>
                    </div>
                    <div class="emp-section-body">

                        {{-- BPJS Ketenagakerjaan --}}
                        <div class="emp-bpjs-panel">
                            <div class="emp-bpjs-head" data-bpjs="kt">
                                <div class="emp-bpjs-head-left">
                                    <div class="emp-bpjs-badge"><iconify-icon icon="mdi:briefcase-outline"></iconify-icon></div>
                                    <div>
                                        <div class="emp-bpjs-name">BPJS Ketenagakerjaan</div>
                                        <div class="emp-bpjs-status" id="ktStatus">Belum terdaftar</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="bpjs_ketenagakerjaan_enrolled" value="0">
                                    <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                        <input type="checkbox" id="bpjsKtEnrolled"
                                               name="bpjs_ketenagakerjaan_enrolled" value="1"
                                               class="form-check-input"
                                               {{ old('bpjs_ketenagakerjaan_enrolled', $employee->bpjs_ketenagakerjaan_enrolled ?? false) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="emp-bpjs-fields {{ old('bpjs_ketenagakerjaan_enrolled', $employee->bpjs_ketenagakerjaan_enrolled ?? false) ? 'open' : '' }}" id="bpjsKtFields">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <div class="emp-field">
                                            <label class="emp-label" for="bpjs_kt_no">Nomor BPJS Ketenagakerjaan</label>
                                            <input type="text" id="bpjs_kt_no" name="bpjs_ketenagakerjaan_no"
                                                   class="form-control"
                                                   value="{{ old('bpjs_ketenagakerjaan_no', $employee->bpjs_ketenagakerjaan_no ?? '') }}"
                                                   placeholder="Masukkan nomor kepesertaan" maxlength="30">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="emp-field">
                                            <label class="emp-label" for="jkk_risk_group">Kelompok Risiko JKK</label>
                                            <select id="jkk_risk_group" name="jkk_risk_group" class="form-select">
                                                @php $cg = old('jkk_risk_group', $employee->jkk_risk_group ?? 1) @endphp
                                                <option value="1" @selected($cg==1)>1 — 0.24% (Rendah)</option>
                                                <option value="2" @selected($cg==2)>2 — 0.54%</option>
                                                <option value="3" @selected($cg==3)>3 — 0.89%</option>
                                                <option value="4" @selected($cg==4)>4 — 1.27%</option>
                                                <option value="5" @selected($cg==5)>5 — 1.74% (Tinggi)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BPJS Kesehatan --}}
                        <div class="emp-bpjs-panel">
                            <div class="emp-bpjs-head" data-bpjs="kes">
                                <div class="emp-bpjs-head-left">
                                    <div class="emp-bpjs-badge"><iconify-icon icon="mdi:heart-pulse"></iconify-icon></div>
                                    <div>
                                        <div class="emp-bpjs-name">BPJS Kesehatan</div>
                                        <div class="emp-bpjs-status" id="kesStatus">Belum terdaftar</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="bpjs_kesehatan_enrolled" value="0">
                                    <div class="form-check form-switch mb-0" onclick="event.stopPropagation()">
                                        <input type="checkbox" id="bpjsKesEnrolled"
                                               name="bpjs_kesehatan_enrolled" value="1"
                                               class="form-check-input"
                                               {{ old('bpjs_kesehatan_enrolled', $employee->bpjs_kesehatan_enrolled ?? false) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="emp-bpjs-fields {{ old('bpjs_kesehatan_enrolled', $employee->bpjs_kesehatan_enrolled ?? false) ? 'open' : '' }}" id="bpjsKesFields">
                                <div class="emp-field" style="max-width: 400px;">
                                    <label class="emp-label" for="bpjs_kes_no">Nomor BPJS Kesehatan</label>
                                    <input type="text" id="bpjs_kes_no" name="bpjs_kesehatan_no"
                                           class="form-control"
                                           value="{{ old('bpjs_kesehatan_no', $employee->bpjs_kesehatan_no ?? '') }}"
                                           placeholder="Masukkan nomor kepesertaan" maxlength="30">
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                {{-- Action Bar --}}
                <div class="emp-action-bar">
                    <p class="emp-action-hint">
                        <iconify-icon icon="mdi:alert-circle-outline"></iconify-icon>
                        Field bertanda <strong style="color:#ef4444;">*</strong> wajib diisi
                    </p>
                    <div class="d-flex gap-2">
                        <x-ui.button href="{{ route('employee.index') }}" variant="secondary">Batal</x-ui.button>
                        <x-ui.button type="submit" variant="primary" class="px-4">
                            <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon> Simpan Karyawan
                        </x-ui.button>
                    </div>
                </div>

            </div>{{-- end emp-sections --}}
        </div>{{-- end emp-wrap --}}
    </form>
</x-ui.card>

@push('page-scripts')
<script>
(function () {
    /* ── Rupiah formatter ─────────────────────────────────────────── */
    function fmtRp(raw) {
        return (raw || '').toString().replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function initRupiah(displayId, hiddenId) {
        const d = document.getElementById(displayId);
        const h = document.getElementById(hiddenId);
        if (!d || !h) return;

        const init = (h.value || '').replace(/\D/g, '');
        if (init && init !== '0') d.value = fmtRp(init);

        d.addEventListener('input', function () {
            const raw = this.value.replace(/\D/g, '');
            const cur = this.selectionStart;
            const dotsBefore = (this.value.slice(0, cur).match(/\./g) || []).length;
            this.value = fmtRp(raw);
            const dotsAfter = (this.value.slice(0, cur).match(/\./g) || []).length;
            try { this.setSelectionRange(cur + dotsAfter - dotsBefore, cur + dotsAfter - dotsBefore); } catch {}
            h.value = raw || '0';
        });

        d.addEventListener('keydown', function (e) {
            const pass = ['Backspace','Delete','Tab','Escape','Enter','ArrowLeft','ArrowRight','Home','End'];
            if (pass.includes(e.key) || (e.ctrlKey || e.metaKey)) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        });

        d.addEventListener('paste', function (e) {
            e.preventDefault();
            const raw = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            this.value = fmtRp(raw);
            h.value = raw || '0';
        });

        d.addEventListener('blur', function () {
            if (!this.value.trim()) { this.value = ''; h.value = '0'; }
        });
    }

    initRupiah('salary_display', 'monthly_salary');
    initRupiah('meal_display',   'meal_allowance_rate');

    /* ── Phone formatter ──────────────────────────────────────────── */
    const pDisp = document.getElementById('phone_display');
    const pHide = document.getElementById('phone_number');
    const waWrap = document.getElementById('waWrap');
    const waMsg  = document.getElementById('waMsg');

    if (pDisp && pHide) {
        function fmtPhone(digits) {
            digits = digits.replace(/\D/g, '').slice(0, 12);
            return digits.replace(/^(\d{0,3})(\d{0,4})(\d{0,4})$/, (_, a, b, c) =>
                [a, b, c].filter(Boolean).join(' ')
            );
        }

        function validatePhone(digits) {
            if (!digits) {
                waWrap.classList.remove('is-invalid', 'is-valid');
                waMsg.className = 'fi-msg'; waMsg.innerHTML = ''; return;
            }
            const ok = /^[89]\d{8,11}$/.test(digits);
            waWrap.classList.toggle('is-valid',   ok);
            waWrap.classList.toggle('is-invalid', !ok);
            waMsg.className = 'fi-msg show ' + (ok ? 'success' : 'error');
            waMsg.innerHTML = ok
                ? '<iconify-icon icon="mdi:check-circle-outline"></iconify-icon> Nomor valid'
                : '<iconify-icon icon="mdi:alert-circle-outline"></iconify-icon> Format tidak valid — mulai 8/9, 9–12 digit';
        }

        function initPhone() {
            let v = (pHide.value || '').replace(/\D/g, '');
            if (v.startsWith('62'))      v = v.slice(2);
            else if (v.startsWith('0')) v = v.slice(1);
            if (v) { pDisp.value = fmtPhone(v); validatePhone(v); }
        }

        pDisp.addEventListener('input', function () {
            let raw = this.value.replace(/\D/g, '');
            if (raw.startsWith('0')) raw = raw.slice(1);
            if (raw.startsWith('62')) raw = raw.slice(2);
            this.value = fmtPhone(raw);
            pHide.value = raw ? '+62' + raw : '';
            validatePhone(raw);
        });

        pDisp.addEventListener('keydown', function (e) {
            const pass = ['Backspace','Delete','Tab','Escape','ArrowLeft','ArrowRight','Home','End',' '];
            if (pass.includes(e.key) || e.ctrlKey || e.metaKey) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        });

        pDisp.addEventListener('paste', function (e) {
            e.preventDefault();
            let raw = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (raw.startsWith('62'))     raw = raw.slice(2);
            else if (raw.startsWith('0')) raw = raw.slice(1);
            this.value = fmtPhone(raw);
            pHide.value = raw ? '+62' + raw : '';
            validatePhone(raw);
        });

        initPhone();
    }

    /* ── BPJS toggle ───────────────────────────────────────────────── */
    function initBpjs(checkboxId, fieldsId, statusId) {
        const cb     = document.getElementById(checkboxId);
        const fields = document.getElementById(fieldsId);
        const status = document.getElementById(statusId);
        if (!cb) return;

        function update() {
            fields.classList.toggle('open', cb.checked);
            status.textContent = cb.checked ? 'Terdaftar' : 'Belum terdaftar';
        }
        cb.addEventListener('change', update);
        update();
    }
    initBpjs('bpjsKtEnrolled',  'bpjsKtFields',  'ktStatus');
    initBpjs('bpjsKesEnrolled', 'bpjsKesFields', 'kesStatus');

    /* Click on BPJS head row toggles checkbox */
    document.querySelectorAll('.emp-bpjs-head').forEach(head => {
        head.addEventListener('click', function () {
            const panel = this.closest('.emp-bpjs-panel');
            const cb    = panel.querySelector('input[type="checkbox"]');
            cb.checked  = !cb.checked;
            cb.dispatchEvent(new Event('change'));
        });
    });

    /* ── Sidebar scroll spy ────────────────────────────────────────── */
    const sections = document.querySelectorAll('.emp-section[id]');
    const navItems = document.querySelectorAll('.emp-nav-item[data-section]');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                navItems.forEach(n => n.classList.remove('active'));
                const active = document.querySelector(`.emp-nav-item[data-section="${entry.target.id}"]`);
                if (active) active.classList.add('active');
            }
        });
    }, { rootMargin: '-30% 0px -60% 0px', threshold: 0 });

    sections.forEach(s => observer.observe(s));

    /* Smooth scroll on nav click */
    navItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.getElementById(this.dataset.section);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
})();
</script>
@endpush

@endsection
