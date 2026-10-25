@extends('dashboard.index')

@section('title', 'Tambah Customer PPF')
@section('route-breadcrumb', 'ppf-customers.index')
@section('breadcrumb', 'Customers PPF')

@push('page-styles')
<style>
.cust-layout {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 24px;
  align-items: start;
}
@media (max-width: 768px) {
  .cust-layout { grid-template-columns: 1fr; }
  .cust-nav    { display: none; }
}
.cust-nav {
  position: sticky; top: 80px;
  background: var(--white);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 8px; list-style: none; margin: 0;
}
.cust-nav li a {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px; border-radius: 8px;
  font-size: 13px; font-weight: 500;
  color: var(--neutral-600); text-decoration: none;
  transition: background .15s, color .15s;
}
.cust-nav li a .cn-icon {
  width: 28px; height: 28px; border-radius: 6px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; background: var(--neutral-100); color: var(--neutral-500);
  flex-shrink: 0; transition: background .15s, color .15s;
}
.cust-nav li a:hover, .cust-nav li a.is-active { background: var(--primary-light); color: var(--primary-600); }
.cust-nav li a:hover .cn-icon, .cust-nav li a.is-active .cn-icon { background: var(--primary-600); color: #fff; }

.cust-section {
  background: var(--white); border: 1px solid var(--border-color);
  border-radius: 12px; overflow: hidden; margin-bottom: 20px;
}
.cust-section-head {
  display: flex; align-items: center; gap: 14px;
  padding: 16px 20px; background: var(--neutral-50);
  border-bottom: 1px solid var(--border-color);
}
.cust-section-head .sec-icon {
  width: 38px; height: 38px; border-radius: 9px;
  background: var(--primary-light); color: var(--primary-600);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; flex-shrink: 0;
}
.cust-section-head .sec-icon.sec-icon-danger  { background: rgba(239,68,68,.12); color: #ef4444; }
.cust-section-head .sec-icon.sec-icon-ppf     { background: rgba(139,92,246,.12); color: #7c3aed; }
.cust-section-head .sec-title { font-size: 14px; font-weight: 600; margin: 0; color: var(--neutral-900); }
.cust-section-head .sec-desc  { font-size: 12px; color: var(--neutral-500); margin: 0; }
.cust-section-body { padding: 20px; }

/* Phone unified input */
.fi-wrap {
  display: flex; align-items: stretch;
  border: 1px solid var(--input-stroke, var(--neutral-300));
  border-radius: 8px; overflow: hidden;
  background: var(--input-bg, var(--neutral-50));
  transition: border-color .15s, box-shadow .15s;
}
.fi-wrap:focus-within { border-color: var(--primary-600); box-shadow: 0 0 0 3px rgba(72,127,255,.12); }
.fi-badge {
  display: flex; align-items: center; gap: 5px; padding: 0 12px;
  background: var(--neutral-100);
  border-right: 1px solid var(--input-stroke, var(--neutral-300));
  font-size: 12px; font-weight: 600; white-space: nowrap;
  color: var(--neutral-600); flex-shrink: 0; user-select: none;
}
.fi-badge .wa-dot { width: 8px; height: 8px; border-radius: 50%; background: #25d366; flex-shrink: 0; }
.fi-wrap input[type="tel"] {
  flex: 1; border: none; outline: none; background: transparent;
  padding: 8px 12px; font-size: 14px; color: var(--neutral-900); min-width: 0;
}
.fi-wrap input[type="hidden"] { display: none; }

/* Installation card */
.install-card {
  border: 1px solid var(--border-color);
  border-radius: 10px; overflow: hidden; margin-bottom: 16px;
}
.install-card-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 16px;
  background: var(--neutral-50);
  border-bottom: 1px solid var(--border-color);
}
.install-card-num {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; font-weight: 600;
}
.install-card-num .num-badge {
  width: 24px; height: 24px; border-radius: 6px;
  background: var(--primary-600); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.install-card-body { padding: 16px; }

/* Tech picker */
.tech-picker {
  border: 1px solid var(--border-color);
  border-radius: 10px; padding: 14px;
}
.tech-list {
  border: 1px solid var(--border-color);
  border-radius: 8px; padding: 8px;
  max-height: 200px; overflow-y: auto;
}

/* Doc upload box */
.doc-upload-box {
  border: 1px dashed var(--border-color);
  border-radius: 10px; padding: 14px;
  background: var(--neutral-50);
  transition: border-color .15s;
}
.doc-upload-box:hover { border-color: var(--primary-600); }

/* Action bar */
.cust-action-bar {
  position: sticky; bottom: 0; z-index: 100;
  background: var(--white);
  border-top: 1px solid var(--border-color);
  padding: 14px 20px;
  display: flex; align-items: center; justify-content: flex-end; gap: 10px;
  margin-top: -1px;
}

/* Dark mode */
[data-theme="dark"] .cust-section,
[data-theme="dark"] .cust-nav,
[data-theme="dark"] .cust-action-bar  { background: var(--dark-2); }
[data-theme="dark"] .cust-section-head,
[data-theme="dark"] .install-card-head,
[data-theme="dark"] .doc-upload-box   { background: var(--dark-3); }
[data-theme="dark"] .install-card     { border-color: var(--dark-1); }
[data-theme="dark"] .tech-picker,
[data-theme="dark"] .tech-list        { border-color: var(--dark-1); background: var(--dark-3); }
[data-theme="dark"] .cust-nav li a   { color: var(--neutral-400); }
[data-theme="dark"] .cust-nav li a .cn-icon { background: var(--dark-3); color: var(--neutral-400); }
[data-theme="dark"] .fi-wrap  { background: var(--dark-3); border-color: var(--dark-1); }
[data-theme="dark"] .fi-badge { background: var(--dark-1); border-color: var(--dark-1); color: var(--neutral-300); }
[data-theme="dark"] .fi-wrap input { color: var(--neutral-100); }
[data-theme="dark"] .cust-section-head .sec-title { color: var(--neutral-100); }
</style>
@endpush

@section('content')

@if ($errors->any())
  <x-ui.alert type="danger" class="mb-3">
    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </x-ui.alert>
@endif

@php
  $lockBranch      = $lockBranch ?? false;
  $defaultBranchId = $defaultBranchId ?? null;
  $selectedBranch  = old('branch_id', $defaultBranchId);

  $panelOptions = [
    'hood'            => 'Kap Mesin',
    'front_bumper'    => 'Bumper Depan',
    'rear_bumper'     => 'Bumper Belakang',
    'fender_left'     => 'Fender Kiri',
    'fender_right'    => 'Fender Kanan',
    'door_fl'         => 'Pintu Depan Kiri',
    'door_fr'         => 'Pintu Depan Kanan',
    'door_rl'         => 'Pintu Belakang Kiri',
    'door_rr'         => 'Pintu Belakang Kanan',
    'mirror_left'     => 'Spion Kiri',
    'mirror_right'    => 'Spion Kanan',
    'roof'            => 'Atap',
    'trunk'           => 'Bagasi',
    'side_skirt_left' => 'Side Skirt Kiri',
    'side_skirt_right'=> 'Side Skirt Kanan',
  ];

  $oldInstalls = old('installations', [[
    'ppf_product_id' => '', 'install_type' => '',
    'install_date'   => date('Y-m-d'), 'warranty_years' => 5,
    'technicians'    => [], 'panels' => [], 'notes' => '',
  ]]);
@endphp

<div class="cust-layout">

  {{-- Sidebar --}}
  <ul class="cust-nav" id="custNav">
    <li><a href="#sec-identitas" class="is-active">
      <span class="cn-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></span> Identitas
    </a></li>
    <li><a href="#sec-kendaraan">
      <span class="cn-icon"><iconify-icon icon="mdi:car-outline"></iconify-icon></span> Kendaraan
    </a></li>
    <li><a href="#sec-instalasi">
      <span class="cn-icon"><iconify-icon icon="mdi:shield-check-outline"></iconify-icon></span> Instalasi
    </a></li>
    <li><a href="#sec-keamanan">
      <span class="cn-icon"><iconify-icon icon="mdi:lock-outline"></iconify-icon></span> Keamanan
    </a></li>
  </ul>

  <div>

    @if(isset($fromSo) && $fromSo)
    <x-ui.alert type="success" class="mb-3">
        <div class="d-flex align-items-start gap-2">
            <iconify-icon icon="mdi:link-variant" class="fs-5 mt-1" style="flex-shrink:0"></iconify-icon>
            <div>
                <div class="fw-semibold small">Diisi dari Sales Order <span class="font-monospace">{{ $fromSo->so_number }}</span></div>
                <div class="text-muted" style="font-size:12px">Data prospek sudah diisi otomatis. Lengkapi data instalasi PPF, produk, dan konfirmasi admin.</div>
            </div>
        </div>
    </x-ui.alert>
    @endif

    <form action="{{ route('ppf-customers.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if(isset($fromSo) && $fromSo)
      <input type="hidden" name="from_so" value="{{ $fromSo->id }}">
      @endif

      {{-- ── Section 1: Identitas ── --}}
      <div id="sec-identitas" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Identitas & Kontak</p>
            <p class="sec-desc">Data customer, cabang, dan informasi kontak</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row g-3">

            {{-- Cabang --}}
            <div class="col-md-6">
              <label class="form-label">Cabang <span class="text-danger">*</span></label>
              @if($lockBranch)
                <input type="hidden" name="branch_id" value="{{ $selectedBranch }}">
                <select class="form-select" disabled>
                  @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ (string)$selectedBranch===(string)$b->id ? 'selected':'' }}>{{ $b->name }}</option>
                  @endforeach
                </select>
                <div class="form-text"><iconify-icon icon="mdi:information-outline" class="me-1"></iconify-icon>Cabang otomatis sesuai akun Anda.</div>
              @else
                <select name="branch_id" class="form-select" required>
                  <option value="">— Pilih Cabang —</option>
                  @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ (string)$selectedBranch===(string)$b->id ? 'selected':'' }}>{{ $b->name }}</option>
                  @endforeach
                </select>
              @endif
            </div>

            {{-- WSN --}}
            <div class="col-md-6">
              <label class="form-label">WSN <span class="text-danger">*</span></label>
              <input type="text" name="wsn" class="form-control" required
                     value="{{ old('wsn') }}" placeholder="WSN-XXXX">
            </div>

            <div class="col-12">
              <x-ui.alert type="info" class="mb-0">
                <iconify-icon icon="mdi:information-outline" class="me-1"></iconify-icon>
                <strong>Card Number</strong> & <strong>Warranty Code</strong> dibuat otomatis saat disimpan.
              </x-ui.alert>
            </div>

            {{-- Nama --}}
            <div class="col-md-6">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="{{ old('name', $fromSo?->prospect_name) }}" placeholder="Nama pemilik kendaraan">
            </div>

            {{-- Phone --}}
            <div class="col-md-6">
              <label class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
              <div class="fi-wrap">
                <span class="fi-badge"><span class="wa-dot"></span>+62</span>
                <input type="tel" id="phone_display" placeholder="812 3456 7890" autocomplete="off">
                <input type="hidden" name="phone" id="phone_hidden" value="{{ old('phone', $fromSo?->prospect_phone) }}">
              </div>
            </div>

            {{-- Email --}}
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control"
                     value="{{ old('email', $fromSo?->prospect_email) }}" placeholder="email@contoh.com">
            </div>

            {{-- Marketing --}}
            <div class="col-md-6">
              <label class="form-label">Marketing <span class="text-muted fw-normal small">opsional</span></label>
              <select name="marketing_id" class="form-select">
                <option value="">— Tidak ada —</option>
                @foreach($employees as $e)
                  @if(($e->job_position ?? '') === 'Marketing')
                    <option value="{{ $e->id }}" {{ old('marketing_id')==$e->id ? 'selected':'' }}>{{ $e->name }}</option>
                  @endif
                @endforeach
              </select>
            </div>

            {{-- Alamat --}}
            <div class="col-md-12">
              <label class="form-label">Alamat <span class="text-danger">*</span></label>
              <input type="text" name="address" class="form-control" required
                     value="{{ old('address', $fromSo?->prospect_address) }}" placeholder="Alamat lengkap">
            </div>

            <div class="col-md-6">
              <label class="form-label">Kota <span class="text-danger">*</span></label>
              <input type="text" name="city" class="form-control" required
                     value="{{ old('city') }}" placeholder="Jakarta">
            </div>

            <div class="col-md-6">
              <label class="form-label">Negara</label>
              <input type="text" name="country" class="form-control"
                     value="{{ old('country','Indonesia') }}">
            </div>

          </div>
        </div>
      </div>

      {{-- ── Section 2: Kendaraan ── --}}
      <div id="sec-kendaraan" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:car-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Data Kendaraan</p>
            <p class="sec-desc">Informasi kendaraan yang akan dipasang PPF</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Merek (Brand) <span class="text-danger">*</span></label>
              <input type="text" name="vehicle_brand" class="form-control" required
                     value="{{ old('vehicle_brand', $fromSo?->vehicle_brand) }}" placeholder="Toyota">
            </div>
            <div class="col-md-6">
              <label class="form-label">Model <span class="text-danger">*</span></label>
              <input type="text" name="vehicle_model" class="form-control" required
                     value="{{ old('vehicle_model', $fromSo?->vehicle_model) }}" placeholder="Fortuner">
            </div>
            <div class="col-md-4">
              <label class="form-label">Tahun <span class="text-danger">*</span></label>
              <input type="text" name="vehicle_year" class="form-control" required
                     value="{{ old('vehicle_year', $fromSo?->vehicle_year) }}" placeholder="{{ date('Y') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Warna <span class="text-danger">*</span></label>
              <input type="text" name="vehicle_color" class="form-control" required
                     value="{{ old('vehicle_color') }}" placeholder="Putih">
            </div>
            <div class="col-md-4">
              <label class="form-label">Plat Nomor <span class="text-danger">*</span></label>
              <input type="text" name="vehicle_plate" class="form-control" required
                     value="{{ old('vehicle_plate', $fromSo?->vehicle_plate) }}" placeholder="B 1234 XYZ">
            </div>
          </div>
        </div>
      </div>

      {{-- ── Section 3: Instalasi PPF ── --}}
      <div id="sec-instalasi" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon sec-icon-ppf"><iconify-icon icon="mdi:shield-check-outline"></iconify-icon></div>
          <div class="flex-grow-1">
            <p class="sec-title">Instalasi PPF</p>
            <p class="sec-desc">Detail setiap sesi pemasangan film PPF</p>
          </div>
          <x-ui.button type="button" variant="outline" size="sm" class="flex-shrink-0" id="add-install">
            <iconify-icon icon="mdi:plus"></iconify-icon> Tambah Instalasi
          </x-ui.button>
        </div>
        <div class="cust-section-body">

          <div id="install-container">
            @foreach($oldInstalls as $i => $ins)
              @php
                $oldTech   = old("installations.$i.technicians", $ins['technicians'] ?? []);
                if (!is_array($oldTech)) $oldTech = [$oldTech];
                $oldPanels = old("installations.$i.panels", $ins['panels'] ?? []);
                if (!is_array($oldPanels)) $oldPanels = [$oldPanels];
              @endphp

              <div class="install-card install-item" data-index="{{ $i }}">
                <div class="install-card-head">
                  <div class="install-card-num">
                    <span class="num-badge">{{ $i+1 }}</span>
                    <span>Instalasi #{{ $i+1 }}</span>
                  </div>
                  <x-ui.button type="button" variant="danger-outline" size="sm" class="remove-install">
                    <iconify-icon icon="mdi:trash-can-outline"></iconify-icon> Hapus
                  </x-ui.button>
                </div>

                <div class="install-card-body">
                  <div class="row g-3">

                    {{-- Produk PPF --}}
                    <div class="col-md-6">
                      <label class="form-label">Produk PPF <span class="text-danger">*</span></label>
                      <select name="installations[{{ $i }}][ppf_product_id]" class="form-select" required>
                        <option value="">— Pilih Produk —</option>
                        @foreach($ppfProducts as $p)
                          <option value="{{ $p->id }}"
                            {{ old("installations.$i.ppf_product_id", $ins['ppf_product_id'] ?? '') == $p->id ? 'selected':'' }}>
                            {{ $p->name }} — {{ $p->brand }} {{ $p->series }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    {{-- Tanggal --}}
                    <div class="col-md-2">
                      <label class="form-label">Tanggal Pasang <span class="text-danger">*</span></label>
                      <input type="date" name="installations[{{ $i }}][install_date]" class="form-control" required
                             value="{{ old("installations.$i.install_date", $ins['install_date'] ?? date('Y-m-d')) }}">
                    </div>

                    {{-- Garansi --}}
                    <div class="col-md-2">
                      <label class="form-label">Garansi (tahun) <span class="text-danger">*</span></label>
                      <select name="installations[{{ $i }}][warranty_years]" class="form-select" required>
                        <option value="5" {{ (int)old("installations.$i.warranty_years", $ins['warranty_years'] ?? 5)===5 ? 'selected':'' }}>5 Tahun</option>
                        <option value="7" {{ (int)old("installations.$i.warranty_years", $ins['warranty_years'] ?? 5)===7 ? 'selected':'' }}>7 Tahun</option>
                        <option value="8" {{ (int)old("installations.$i.warranty_years", $ins['warranty_years'] ?? 5)===8 ? 'selected':'' }}>8 Tahun</option>
                      </select>
                    </div>

                    {{-- Tipe --}}
                    <div class="col-md-2">
                      <label class="form-label">Tipe <span class="text-danger">*</span></label>
                      <select name="installations[{{ $i }}][install_type]" class="form-select" required>
                        <option value="">— Pilih —</option>
                        <option value="fullbody" {{ old("installations.$i.install_type", $ins['install_type'] ?? '') === 'fullbody' ? 'selected':'' }}>Full Body</option>
                        <option value="partial"  {{ old("installations.$i.install_type", $ins['install_type'] ?? '') === 'partial'  ? 'selected':'' }}>Partial</option>
                      </select>
                    </div>

                    {{-- Teknisi --}}
                    <div class="col-12">
                      <div class="tech-picker" data-max="4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                          <label class="form-label mb-0 fw-semibold" style="font-size:13px;">
                            <iconify-icon icon="mdi:account-cog-outline" class="me-1 text-primary"></iconify-icon>Teknisi <span class="text-danger">*</span>
                            <span class="text-muted fw-normal">(maks 4)</span>
                          </label>
                          <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark tech-count">0/4 dipilih</span>
                            <x-ui.button type="button" variant="secondary" size="sm" class="tech-clear">Kosongkan</x-ui.button>
                          </div>
                        </div>

                        <input type="text" class="form-control form-control-sm tech-search mb-2"
                               placeholder="Cari teknisi...">

                        <div class="tech-chips d-flex flex-wrap gap-1 mb-2">
                          <span class="badge bg-light text-muted">Belum ada teknisi dipilih</span>
                        </div>

                        <div class="tech-list">
                          <div class="row g-2">
                            @foreach($employees as $e)
                              @if(in_array($e->job_position ?? '', ['Teknisi','Teknisi Kaca Film','Teknisi PPF','Detailer']))
                                @php $tid = "tech_{$i}_{$e->id}"; @endphp
                                <div class="col-md-3 col-6 tech-item" data-name="{{ strtolower($e->name) }}">
                                  <div class="form-check m-0">
                                    <input class="form-check-input tech-checkbox" type="checkbox"
                                           name="installations[{{ $i }}][technicians][]"
                                           value="{{ $e->id }}" data-emp="{{ $e->id }}"
                                           id="{{ $tid }}"
                                           {{ in_array($e->id, $oldTech) ? 'checked':'' }}>
                                    <label class="form-check-label small" for="{{ $tid }}" style="cursor:pointer;">
                                      {{ $e->name }}
                                    </label>
                                  </div>
                                </div>
                              @endif
                            @endforeach
                          </div>
                        </div>
                        <div class="form-text mt-1">Centang untuk memilih. Maksimal 4 teknisi per instalasi.</div>
                      </div>
                    </div>

                    {{-- Panel --}}
                    <div class="col-12">
                      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <label class="form-label mb-0 fw-semibold" style="font-size:13px;">
                          <iconify-icon icon="mdi:view-grid-outline" class="me-1 text-primary"></iconify-icon>Panel yang Dipasangkan
                        </label>
                        <div class="d-flex gap-2">
                          <x-ui.button type="button" variant="secondary" size="sm" class="panel-select-all">Pilih Semua</x-ui.button>
                          <x-ui.button type="button" variant="secondary" size="sm" class="panel-clear">Kosongkan</x-ui.button>
                        </div>
                      </div>

                      <div class="row g-2 panel-wrap">
                        @foreach($panelOptions as $key => $label)
                          @php $cid = "panel_{$i}_{$key}"; @endphp
                          <div class="col-md-3 col-6">
                            <input class="btn-check panel-checkbox" type="checkbox"
                                   name="installations[{{ $i }}][panels][]" value="{{ $key }}"
                                   id="{{ $cid }}" {{ in_array($key, $oldPanels) ? 'checked':'' }} autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $cid }}">
                              {{ $label }}
                            </label>
                          </div>
                        @endforeach
                      </div>
                      <div class="form-text mt-1">
                        Jika <strong>partial</strong>, pilih panel yang dipasang. Jika <strong>full body</strong>, semua panel dianggap terpilih.
                      </div>
                    </div>

                    {{-- Dokumen --}}
                    <div class="col-md-6">
                      @php $cid = "doc_checklist_{$i}"; @endphp
                      <div class="doc-upload-box">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                          <div>
                            <div class="fw-semibold" style="font-size:13px;"><iconify-icon icon="mdi:file-check-outline" class="me-1 text-primary"></iconify-icon>Checklist</div>
                            <div class="text-muted" style="font-size:11px;">JPG / PNG / WEBP • Maks 2MB</div>
                          </div>
                          <div class="d-flex gap-1">
                            <label for="{{ $cid }}" class="btn btn-sm btn-outline-primary mb-0 doc-label" data-doc="checklist">
                              <iconify-icon icon="mdi:upload-outline"></iconify-icon> Upload
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary doc-clear" data-doc="checklist">Reset</button>
                          </div>
                        </div>
                        <input id="{{ $cid }}" type="file"
                               name="installations[{{ $i }}][checklist_image]"
                               class="d-none doc-input" data-doc="checklist" accept="image/*">
                        <div class="text-muted doc-filename" style="font-size:12px;" data-doc="checklist">Belum ada file.</div>
                        <img class="img-fluid rounded border mt-2 d-none doc-preview" data-doc="checklist"
                             style="max-height:160px; object-fit:contain;" alt="Checklist preview">
                      </div>
                    </div>

                    <div class="col-md-6">
                      @php $jid = "doc_joborder_{$i}"; @endphp
                      <div class="doc-upload-box">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                          <div>
                            <div class="fw-semibold" style="font-size:13px;"><iconify-icon icon="mdi:clipboard-list-outline" class="me-1 text-primary"></iconify-icon>Job Order</div>
                            <div class="text-muted" style="font-size:11px;">JPG / PNG / WEBP • Maks 2MB</div>
                          </div>
                          <div class="d-flex gap-1">
                            <label for="{{ $jid }}" class="btn btn-sm btn-outline-primary mb-0 doc-label" data-doc="joborder">
                              <iconify-icon icon="mdi:upload-outline"></iconify-icon> Upload
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary doc-clear" data-doc="joborder">Reset</button>
                          </div>
                        </div>
                        <input id="{{ $jid }}" type="file"
                               name="installations[{{ $i }}][job_order_image]"
                               class="d-none doc-input" data-doc="joborder" accept="image/*">
                        <div class="text-muted doc-filename" style="font-size:12px;" data-doc="joborder">Belum ada file.</div>
                        <img class="img-fluid rounded border mt-2 d-none doc-preview" data-doc="joborder"
                             style="max-height:160px; object-fit:contain;" alt="Job order preview">
                      </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="col-12">
                      <label class="form-label small">Catatan <span class="text-muted fw-normal">opsional</span></label>
                      <textarea name="installations[{{ $i }}][notes]" class="form-control" rows="2"
                                placeholder="Catatan tambahan untuk instalasi ini...">{{ old("installations.$i.notes", $ins['notes'] ?? '') }}</textarea>
                    </div>

                  </div>
                </div>
              </div>
            @endforeach
          </div>{{-- /install-container --}}

        </div>
      </div>

      {{-- ── Section 4: Keamanan + Action Bar ── --}}
      <div id="sec-keamanan" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon sec-icon-danger"><iconify-icon icon="mdi:lock-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Konfirmasi Keamanan</p>
            <p class="sec-desc">Masukkan password Anda untuk menyimpan data customer PPF</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Password Anda <span class="text-danger">*</span></label>
              <input type="password" name="admin_password"
                     class="form-control @error('admin_password') is-invalid @enderror" required>
              @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
        <div class="cust-action-bar">
          <x-ui.button :href="route('ppf-customers.index')" variant="secondary">Batal</x-ui.button>
          <x-ui.button type="submit" variant="primary" class="px-4">
            <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon> Simpan Customer PPF
          </x-ui.button>
        </div>
      </div>

    </form>
  </div>
</div>
@endsection

@push('page-scripts')
<script>
(function () {

  // ── Phone formatting ──
  (function () {
    const display = document.getElementById('phone_display');
    const hidden  = document.getElementById('phone_hidden');
    if (!display || !hidden) return;
    function strip(v) {
      v = v.replace(/\D/g,'');
      if (v.startsWith('62')) v = v.slice(2);
      if (v.startsWith('0'))  v = v.slice(1);
      return v;
    }
    function fmt(d) {
      const a=d.slice(0,3), b=d.slice(3,7), c=d.slice(7,11);
      return [a,b,c].filter(Boolean).join(' ');
    }
    const init = strip(hidden.value||'');
    display.value = fmt(init);
    if (init) hidden.value = '+62'+init;
    display.addEventListener('input', function(){
      const raw=strip(this.value), pos=this.selectionStart;
      const before=(this.value.slice(0,pos).match(/ /g)||[]).length;
      this.value=fmt(raw);
      const after=(this.value.slice(0,pos).match(/ /g)||[]).length;
      const np=Math.max(0,pos+after-before);
      this.setSelectionRange(np,np);
      hidden.value=raw?'+62'+raw:'';
    });
  })();

  // ── Tech picker helpers ──
  function updateTechPicker(picker) {
    const max     = parseInt(picker.dataset.max||'4',10);
    const checked = Array.from(picker.querySelectorAll('.tech-checkbox:checked'));
    const countEl = picker.querySelector('.tech-count');
    const chipsEl = picker.querySelector('.tech-chips');
    if (countEl) countEl.textContent = `${checked.length}/${max} dipilih`;
    if (chipsEl) {
      chipsEl.innerHTML = '';
      if (!checked.length) {
        const s=document.createElement('span'); s.className='badge bg-light text-muted';
        s.textContent='Belum ada teknisi dipilih'; chipsEl.appendChild(s);
      } else {
        checked.forEach(chk => {
          const lbl = picker.querySelector(`label[for="${chk.id}"]`)?.textContent?.trim()||'Teknisi';
          const b   = document.createElement('span');
          b.className='badge bg-primary-light text-primary-600 d-inline-flex align-items-center gap-2';
          b.innerHTML=`<span>${lbl}</span><button type="button" class="btn-close btn-close-sm chip-remove" aria-label="Remove" data-emp="${chk.value}"></button>`;
          chipsEl.appendChild(b);
        });
      }
    }
  }

  function enforceMax(picker, chk) {
    const max = parseInt(picker.dataset.max||'4',10);
    if (picker.querySelectorAll('.tech-checkbox:checked').length > max) {
      chk.checked = false;
      alert(`Maksimal ${max} teknisi.`);
      return false;
    }
    return true;
  }

  function filterTechList(picker, query) {
    const q=(query||'').toLowerCase().trim();
    picker.querySelectorAll('.tech-item').forEach(item=>{
      item.style.display=item.dataset.name.includes(q)?'':'none';
    });
  }

  function updateNamesAndIds(root, idx) {
    root.querySelectorAll('select,input,textarea,label').forEach(el=>{
      const name=el.getAttribute&&el.getAttribute('name');
      if (name&&name.includes('installations['))
        el.setAttribute('name', name.replace(/installations\[\d+\]/,`installations[${idx}]`));
      if (el.classList?.contains('panel-checkbox'))
        el.setAttribute('id', `panel_${idx}_${el.value}`);
      if (el.classList?.contains('tech-checkbox'))
        el.setAttribute('id', `tech_${idx}_${el.dataset.emp||el.value}`);
      if (el.classList?.contains('doc-input'))
        el.setAttribute('id', `doc_${el.dataset.doc}_${idx}`);
      if (el.classList?.contains('doc-label'))
        el.setAttribute('for', `doc_${el.dataset.doc}_${idx}`);
      if (el.tagName==='LABEL') {
        const f=el.getAttribute('for');
        if (f?.startsWith('panel_'))
          el.setAttribute('for', `panel_${idx}_${f.split('_').slice(2).join('_')}`);
        if (f?.startsWith('tech_'))
          el.setAttribute('for', `tech_${idx}_${f.split('_').slice(2).join('_')}`);
      }
    });
    // update num badge & title
    const numBadge = root.querySelector('.num-badge');
    const numLabel = root.querySelector('.install-card-num span:last-child');
    if (numBadge) numBadge.textContent = idx+1;
    if (numLabel) numLabel.textContent = `Instalasi #${idx+1}`;
  }

  const container = document.getElementById('install-container');
  const addBtn    = document.getElementById('add-install');

  // init existing pickers
  document.querySelectorAll('.tech-picker').forEach(p => updateTechPicker(p));

  // tech checkbox
  document.addEventListener('change', function(e){
    if (!e.target.classList.contains('tech-checkbox')) return;
    const picker = e.target.closest('.tech-picker'); if (!picker) return;
    enforceMax(picker, e.target);
    updateTechPicker(picker);
  });

  // tech search
  document.addEventListener('input', function(e){
    if (!e.target.classList.contains('tech-search')) return;
    const picker = e.target.closest('.tech-picker'); if (!picker) return;
    filterTechList(picker, e.target.value);
  });

  document.addEventListener('click', function(e){
    // tech clear
    if (e.target.classList.contains('tech-clear')) {
      const picker = e.target.closest('.tech-picker'); if (!picker) return;
      picker.querySelectorAll('.tech-checkbox').forEach(c=>c.checked=false);
      const s=picker.querySelector('.tech-search');
      if(s){ s.value=''; filterTechList(picker,''); }
      updateTechPicker(picker);
    }
    // chip remove
    if (e.target.classList.contains('chip-remove')) {
      const picker=e.target.closest('.tech-picker'); if(!picker) return;
      const chk=picker.querySelector(`.tech-checkbox[value="${e.target.dataset.emp}"]`);
      if(chk) chk.checked=false;
      updateTechPicker(picker);
    }
    // panel all/clear
    const card=e.target.closest('.install-item');
    if (card) {
      if (e.target.classList.contains('panel-select-all'))
        card.querySelectorAll('.panel-checkbox').forEach(c=>c.checked=true);
      if (e.target.classList.contains('panel-clear'))
        card.querySelectorAll('.panel-checkbox').forEach(c=>c.checked=false);
    }
    // doc clear
    if (e.target.classList.contains('doc-clear')) {
      const c=e.target.closest('.install-item'); if(!c) return;
      const doc=e.target.dataset.doc;
      const inp=c.querySelector(`.doc-input[data-doc="${doc}"]`);
      const fn =c.querySelector(`.doc-filename[data-doc="${doc}"]`);
      const img=c.querySelector(`.doc-preview[data-doc="${doc}"]`);
      if(inp) inp.value='';
      if(fn)  fn.textContent='Belum ada file.';
      if(img){ img.src=''; img.classList.add('d-none'); }
    }
    // remove install
    if (e.target.closest('.remove-install')) {
      const items=container.querySelectorAll('.install-item');
      if (items.length<=1) return;
      e.target.closest('.install-item')?.remove();
      Array.from(container.querySelectorAll('.install-item')).forEach((card,idx)=>{
        updateNamesAndIds(card, idx);
        const p=card.querySelector('.tech-picker'); if(p) updateTechPicker(p);
      });
    }
  });

  // doc preview + filename
  document.addEventListener('change', function(e){
    if (!e.target.classList.contains('doc-input')) return;
    const card=e.target.closest('.install-item'); if(!card) return;
    const doc=e.target.dataset.doc;
    const file=e.target.files&&e.target.files[0];
    const fn =card.querySelector(`.doc-filename[data-doc="${doc}"]`);
    const img=card.querySelector(`.doc-preview[data-doc="${doc}"]`);
    if(fn) fn.textContent=file?file.name:'Belum ada file.';
    if(!img) return;
    if(!file){ img.src=''; img.classList.add('d-none'); return; }
    img.src=URL.createObjectURL(file); img.classList.remove('d-none');
  });

  // add install
  addBtn?.addEventListener('click', function(){
    const last=container.querySelector('.install-item:last-child'); if(!last) return;
    const clone=last.cloneNode(true);
    const newIdx=container.querySelectorAll('.install-item').length;
    updateNamesAndIds(clone, newIdx);
    clone.querySelectorAll('select').forEach(s=>s.selectedIndex=0);
    clone.querySelectorAll('input').forEach(inp=>{
      if(inp.type==='date')     inp.value=new Date().toISOString().slice(0,10);
      if(inp.type==='checkbox') inp.checked=false;
      if(inp.type==='text'&&inp.classList.contains('tech-search')) inp.value='';
      if(inp.type==='file')     inp.value='';
    });
    clone.querySelectorAll('textarea').forEach(t=>t.value='');
    clone.querySelectorAll('.doc-preview').forEach(img=>{ img.src=''; img.classList.add('d-none'); });
    clone.querySelectorAll('.doc-filename').forEach(el=>el.textContent='Belum ada file.');
    const picker=clone.querySelector('.tech-picker');
    if(picker){ filterTechList(picker,''); updateTechPicker(picker); }
    container.appendChild(clone);
  });

  // ── Sidebar scroll spy ──
  const navLinks = document.querySelectorAll('#custNav a');
  const sections = document.querySelectorAll('.cust-section[id]');
  const obs = new IntersectionObserver(entries=>{
    entries.forEach(entry=>{
      if(entry.isIntersecting){
        navLinks.forEach(a=>a.classList.remove('is-active'));
        document.querySelector(`#custNav a[href="#${entry.target.id}"]`)?.classList.add('is-active');
      }
    });
  },{ threshold: 0.3 });
  sections.forEach(s=>obs.observe(s));

})();
</script>
@endpush
