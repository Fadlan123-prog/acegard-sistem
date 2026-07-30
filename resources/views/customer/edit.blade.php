@extends('dashboard.index')

@section('title', 'Edit Customer — ' . $customer->name)
@section('route-breadcrumb', 'customer.index')
@section('breadcrumb', 'Customers')

@push('page-styles')
<style>
/* ===== Layout ===== */
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

/* ===== Sidebar Nav ===== */
.cust-nav {
  position: sticky;
  top: 80px;
  background: var(--white);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 8px;
  list-style: none;
  margin: 0;
}
.cust-nav li a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: var(--neutral-600);
  text-decoration: none;
  transition: background .15s, color .15s;
}
.cust-nav li a .cn-icon {
  width: 28px; height: 28px;
  border-radius: 6px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px;
  background: var(--neutral-100);
  color: var(--neutral-500);
  flex-shrink: 0;
  transition: background .15s, color .15s;
}
.cust-nav li a:hover,
.cust-nav li a.is-active {
  background: var(--primary-light);
  color: var(--primary-600);
}
.cust-nav li a:hover .cn-icon,
.cust-nav li a.is-active .cn-icon {
  background: var(--primary-600);
  color: #fff;
}

/* ===== Section Cards ===== */
.cust-section {
  background: var(--white);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 20px;
}
.cust-section-head {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 20px;
  background: var(--neutral-50);
  border-bottom: 1px solid var(--border-color);
}
.cust-section-head .sec-icon {
  width: 38px; height: 38px;
  border-radius: 9px;
  background: var(--primary-light);
  color: var(--primary-600);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.cust-section-head .sec-icon.sec-icon-danger { background: rgba(239,68,68,.12); color: #ef4444; }
.cust-section-head .sec-title { font-size: 14px; font-weight: 600; margin: 0; color: var(--neutral-900); }
.cust-section-head .sec-desc  { font-size: 12px; color: var(--neutral-500); margin: 0; }
.cust-section-body { padding: 20px; }

/* ===== Unified phone input ===== */
.fi-wrap {
  display: flex;
  align-items: stretch;
  border: 1px solid var(--input-stroke, var(--neutral-300));
  border-radius: 8px;
  overflow: hidden;
  background: var(--input-bg, var(--neutral-50));
  transition: border-color .15s, box-shadow .15s;
}
.fi-wrap:focus-within {
  border-color: var(--primary-600);
  box-shadow: 0 0 0 3px rgba(72,127,255,.12);
}
.fi-badge {
  display: flex; align-items: center; gap: 5px;
  padding: 0 12px;
  background: var(--neutral-100);
  border-right: 1px solid var(--input-stroke, var(--neutral-300));
  font-size: 12px; font-weight: 600;
  white-space: nowrap; color: var(--neutral-600);
  flex-shrink: 0; user-select: none;
}
.fi-badge .wa-dot { width: 8px; height: 8px; border-radius: 50%; background: #25d366; flex-shrink: 0; }
.fi-wrap input[type="tel"],
.fi-wrap input[type="text"].fi-display {
  flex: 1; border: none; outline: none; background: transparent;
  padding: 8px 12px; font-size: 14px; color: var(--neutral-900); min-width: 0;
}
.fi-wrap input[type="hidden"] { display: none; }

/* ===== Commission mini-card ===== */
.comm-card {
  background: var(--neutral-50);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  padding: 16px;
  margin-top: 16px;
}

/* ===== Sticky action bar ===== */
.cust-action-bar {
  position: sticky;
  bottom: 0;
  z-index: 100;
  background: var(--white);
  border-top: 1px solid var(--border-color);
  padding: 14px 20px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: -1px;
}

/* ===== Dark mode ===== */
[data-theme="dark"] .cust-section,
[data-theme="dark"] .cust-nav,
[data-theme="dark"] .cust-action-bar { background: var(--dark-2); }
[data-theme="dark"] .cust-section-head,
[data-theme="dark"] .comm-card        { background: var(--dark-3); }
[data-theme="dark"] .cust-nav li a    { color: var(--neutral-400); }
[data-theme="dark"] .cust-nav li a .cn-icon { background: var(--dark-3); color: var(--neutral-400); }
[data-theme="dark"] .fi-wrap   { background: var(--dark-3); border-color: var(--dark-1); }
[data-theme="dark"] .fi-badge  { background: var(--dark-1); border-color: var(--dark-1); color: var(--neutral-300); }
[data-theme="dark"] .fi-wrap input { color: var(--neutral-100); }
[data-theme="dark"] .cust-section-head .sec-title { color: var(--neutral-100); }
</style>
@endpush

@section('content')
@include('partials.form-errors')

@php
  $commissionFixed = config('commission.fixed', []);

  if (!empty(old('products'))) {
      $prefillProducts = collect(old('products'))->values()->toArray();
  } else {
      $prefillProducts = $customer->customerProducts->map(fn($p) => [
          'id'                  => $p->id,
          'part_id'             => $p->part_id,
          'category_product_id' => $p->category_product_id,
          'product_id'          => $p->product_id,
      ])->values()->toArray();
  }
  if (empty($prefillProducts)) {
      $prefillProducts = [['id' => null, 'part_id' => null, 'category_product_id' => null, 'product_id' => null]];
  }

  $hasPano = old('has_panoramic') !== null ? (bool) old('has_panoramic') : $hasPano;
@endphp

<div class="cust-layout">

  {{-- ===== Sidebar Nav ===== --}}
  <ul class="cust-nav" id="custNav">
    <li><a href="#sec-identitas" class="is-active">
      <span class="cn-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></span> Identitas
    </a></li>
    <li><a href="#sec-kendaraan">
      <span class="cn-icon"><iconify-icon icon="mdi:car-outline"></iconify-icon></span> Kendaraan
    </a></li>
    <li><a href="#sec-produk">
      <span class="cn-icon"><iconify-icon icon="mdi:layers-outline"></iconify-icon></span> Produk
    </a></li>
    <li><a href="#sec-tim">
      <span class="cn-icon"><iconify-icon icon="mdi:account-multiple-outline"></iconify-icon></span> Tim Pasang
    </a></li>
    <li><a href="#sec-penjualan">
      <span class="cn-icon"><iconify-icon icon="mdi:store-outline"></iconify-icon></span> Penjualan
    </a></li>
    <li><a href="#sec-keamanan">
      <span class="cn-icon"><iconify-icon icon="mdi:lock-outline"></iconify-icon></span> Keamanan
    </a></li>
  </ul>

  {{-- ===== Main Content ===== --}}
  <div>
    <form action="{{ route('customer.update', $customer->id) }}" method="POST" id="customerForm">
      @csrf
      @method('PUT')

      {{-- ── Section 1: Identitas ── --}}
      <div id="sec-identitas" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Identitas Customer</p>
            <p class="sec-desc">Data pribadi dan kontak pemilik kendaraan</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Warranty Serial Number <span class="text-danger">*</span></label>
              <input type="text" name="wsn" class="form-control" required
                     value="{{ old('wsn', $customer->wsn) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Nomor Kartu</label>
              <input type="text" class="form-control" value="{{ $customer->card_number }}" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="{{ old('name', $customer->name) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
              <div class="fi-wrap">
                <span class="fi-badge"><span class="wa-dot"></span>+62</span>
                <input type="tel" class="fi-display" id="phone_display"
                       placeholder="812 3456 7890" autocomplete="off">
                <input type="hidden" name="phone_number" id="phone_hidden"
                       value="{{ old('phone_number', $customer->phone_number) }}">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control"
                     value="{{ old('email', $customer->email) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Alamat</label>
              <input type="text" name="address" class="form-control"
                     value="{{ old('address', $customer->address) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Kota</label>
              <input type="text" name="city" class="form-control"
                     value="{{ old('city', $customer->city) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Negara</label>
              <input type="text" name="country" class="form-control"
                     value="{{ old('country', $customer->country) }}">
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
            <p class="sec-desc">Informasi kendaraan yang dipasang film</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Plat Nomor</label>
              <input type="text" name="plat_number" class="form-control"
                     value="{{ old('plat_number', $customer->plat_number) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Merek Kendaraan</label>
              <input type="text" name="vehicle_brand" class="form-control"
                     value="{{ old('vehicle_brand', $customer->vehicle_brand) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Model Kendaraan</label>
              <input type="text" name="vehicle_model" class="form-control"
                     value="{{ old('vehicle_model', $customer->vehicle_model) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tahun Kendaraan</label>
              <input type="number" name="vehicle_year" class="form-control"
                     value="{{ old('vehicle_year', $customer->vehicle_year) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Pemasangan <span class="text-danger">*</span></label>
              <input type="date" name="install_date" class="form-control"
                     value="{{ old('install_date', optional($customer->install_date)->format('Y-m-d') ?? $customer->created_at->format('Y-m-d')) }}">
              <div class="form-text">Tanggal aktual kendaraan dipasang film (bukan tanggal DP/invoice).</div>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Section 3: Produk & Pemasangan ── --}}
      <div id="sec-produk" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:layers-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Produk & Pemasangan</p>
            <p class="sec-desc">Tipe pemasangan, produk yang dipasang, dan durasi garansi</p>
          </div>
        </div>
        <div class="cust-section-body">

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label">Tipe Pemasangan <span class="text-danger">*</span></label>
              <select name="install_type" id="install_type" class="form-select" required>
                <option value="">— Pilih —</option>
                <option value="fullset" {{ old('install_type', $customer->install_type)==='fullset' ? 'selected' : '' }}>Fullset</option>
                <option value="skkb"    {{ old('install_type', $customer->install_type)==='skkb'    ? 'selected' : '' }}>SKKB</option>
                <option value="dsp"     {{ old('install_type', $customer->install_type)==='dsp'     ? 'selected' : '' }}>D/S/P</option>
              </select>
            </div>

            <div class="col-md-6 {{ in_array(old('install_type', $customer->install_type), ['fullset','dsp']) ? '' : 'd-none' }}" id="panoramic_wrap">
              <label class="form-label">Panoramic</label>
              <div class="d-flex align-items-center gap-3 mt-1">
                <span id="pano_status_badge" class="badge {{ $hasPano ? 'bg-primary' : 'bg-secondary' }}">
                  {{ $hasPano ? 'AKTIF' : 'NONAKTIF' }}
                </span>
                <div class="btn-group" role="group">
                  <input type="radio" class="btn-check" name="has_panoramic" id="pano_off" value="0"
                         {{ $hasPano ? '' : 'checked' }}>
                  <label class="btn btn-outline-secondary btn-sm" for="pano_off">Tidak</label>
                  <input type="radio" class="btn-check" name="has_panoramic" id="pano_on" value="1"
                         {{ $hasPano ? 'checked' : '' }}>
                  <label class="btn btn-outline-primary btn-sm" for="pano_on">Ya</label>
                </div>
              </div>
              <small id="pano_hint" class="text-muted d-block mt-1">
                {{ $hasPano ? 'Komisi mengikuti aturan panoramic.' : 'Komisi reguler.' }}
              </small>
            </div>
          </div>

          {{-- Product rows --}}
          <div id="product-container" class="mb-3">
            @foreach($prefillProducts as $i => $row)
            <div class="row align-items-end mb-3 product-item">
              @if(!empty($row['id']))
                <input type="hidden" name="products[{{ $i }}][id]" value="{{ $row['id'] }}">
              @endif
              <div class="col-md-4">
                <label class="form-label small">Part</label>
                <select name="products[{{ $i }}][part_id]" class="form-select" required>
                  <option value="">Pilih Part</option>
                  @foreach($parts as $part)
                    <option value="{{ $part->id }}"
                      {{ (string)old("products.$i.part_id", $row['part_id'] ?? '') === (string)$part->id ? 'selected' : '' }}>
                      {{ $part->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small">Kategori</label>
                <select name="products[{{ $i }}][category_product_id]"
                        class="form-select category-select" data-index="{{ $i }}" required>
                  <option value="">Pilih Kategori</option>
                  @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                      {{ (string)old("products.$i.category_product_id", $row['category_product_id'] ?? '') === (string)$cat->id ? 'selected' : '' }}>
                      {{ $cat->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label small">Produk</label>
                <select name="products[{{ $i }}][product_id]"
                        class="form-select product-select" data-index="{{ $i }}"
                        data-current="{{ old("products.$i.product_id", $row['product_id'] ?? '') }}" required>
                  <option value="">Pilih Produk</option>
                </select>
              </div>
              <div class="col-md-1">
                <x-ui.button type="button" variant="danger-outline" size="sm" class="remove-product w-100">
                  <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                </x-ui.button>
              </div>
            </div>
            @endforeach
          </div>
          <x-ui.button type="button" variant="outline" size="sm" class="mb-4" id="add-product">
            <iconify-icon icon="mdi:plus"></iconify-icon> Tambah Produk
          </x-ui.button>

          <div class="row g-3 pt-3 border-top">
            <div class="col-md-6">
              <label class="form-label">Durasi Garansi <span class="text-danger">*</span></label>
              <select name="warantee_duration" class="form-select" required>
                <option value="">Pilih Durasi</option>
                <option value="5" {{ (string)old('warantee_duration', $customer->warantee_duration)==='5' ? 'selected' : '' }}>5 Tahun</option>
                <option value="7" {{ (string)old('warantee_duration', $customer->warantee_duration)==='7' ? 'selected' : '' }}>7 Tahun</option>
                <option value="8" {{ (string)old('warantee_duration', $customer->warantee_duration)==='8' ? 'selected' : '' }}>8 Tahun</option>
              </select>
            </div>
          </div>

        </div>
      </div>

      {{-- ── Section 4: Tim Pemasangan ── --}}
      <div id="sec-tim" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:account-multiple-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Tim Pemasangan</p>
            <p class="sec-desc">Teknisi, kenek, dan pembagian komisi instalasi</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Installer 1 (Teknisi) <span class="text-danger">*</span></label>
              <select name="tukang_id" class="form-select" id="tukang_select" required>
                <option value="">Pilih Teknisi</option>
                @foreach ($employees->whereIn('job_position', ['Teknisi','Kenek'])->groupBy('job_position') as $pos => $group)
                  <optgroup label="{{ $pos }}">
                    @foreach ($group as $e)
                      <option value="{{ $e->id }}" data-position="{{ $e->job_position }}"
                        {{ (string)old('tukang_id', $customer->tukang_id) === (string)$e->id ? 'selected' : '' }}>
                        {{ $e->name }}
                      </option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Installer 2 (Kenek) <span class="text-muted fw-normal small">opsional</span></label>
              <select name="kenek_id" class="form-select" id="kenek_select">
                <option value="">— Tidak ada —</option>
                @foreach ($employees->whereIn('job_position', ['Teknisi','Kenek'])->groupBy('job_position') as $pos => $group)
                  <optgroup label="{{ $pos }}">
                    @foreach ($group as $e)
                      <option value="{{ $e->id }}" data-position="{{ $e->job_position }}"
                        {{ (string)old('kenek_id', $customer->kenek_id) === (string)$e->id ? 'selected' : '' }}>
                        {{ $e->name }}
                      </option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
            </div>
          </div>

          <div id="same-pair-banner" class="alert alert-info py-2 px-3 d-none mb-3" style="font-size:.85rem;">
            <iconify-icon icon="mdi:information-outline" class="me-1"></iconify-icon>
            <strong>Pasangan Sejenis Terdeteksi</strong> — Komisi otomatis dibagi <strong>50:50</strong>
          </div>

          <div class="comm-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="fw-semibold" style="font-size:13px;">
                <iconify-icon icon="mdi:chart-pie" class="me-1 text-primary"></iconify-icon>Pembagian Komisi
              </span>
              <x-ui.badge variant="neutral">Pool = Teknisi + Kenek</x-ui.badge>
            </div>

            <div class="btn-group w-100 mb-3" role="group">
              <input type="radio" class="btn-check" name="share_mode" id="share_auto" value="auto"
                     {{ old('share_mode','auto')==='auto' ? 'checked' : '' }}>
              <label class="btn btn-outline-primary btn-sm" for="share_auto">
                Otomatis <span id="auto-ratio-label">(70:30)</span>
              </label>
              <input type="radio" class="btn-check" name="share_mode" id="share_custom" value="custom"
                     {{ old('share_mode')==='custom' ? 'checked' : '' }}>
              <label class="btn btn-outline-primary btn-sm" for="share_custom">Kustom %</label>
            </div>

            <div id="preset-wrap" class="d-flex flex-wrap gap-2 mb-3 {{ old('share_mode')==='custom' ? '' : 'd-none' }}">
              <x-ui.button type="button" variant="outline" size="sm" class="preset-btn" data-t="70" data-k="30">70 : 30</x-ui.button>
              <x-ui.button type="button" variant="outline" size="sm" class="preset-btn" data-t="60" data-k="40">60 : 40</x-ui.button>
              <x-ui.button type="button" variant="outline" size="sm" class="preset-btn" data-t="50" data-k="50">50 : 50</x-ui.button>
              <x-ui.button type="button" variant="outline" size="sm" class="preset-btn" data-t="100" data-k="0">100 : 0</x-ui.button>
              <x-ui.button type="button" variant="outline" size="sm" class="preset-btn" data-t="0" data-k="100">0 : 100</x-ui.button>
            </div>

            <div id="custom-share-wrap" class="{{ old('share_mode')==='custom' ? '' : 'd-none' }}">
              <div class="row g-3 align-items-end mb-3">
                <div class="col-md-6">
                  <label class="form-label small">Teknisi (%)</label>
                  <div class="input-group">
                    <input type="range" min="0" max="100" step="1" class="form-range me-3 flex-grow-1" id="range_tukang">
                    <input type="number" min="0" max="100" step="1" class="form-control w-25" id="input_tukang"
                           name="installer_share[tukang]" value="{{ old('installer_share.tukang', 70) }}">
                    <span class="input-group-text">%</span>
                  </div>
                </div>
                <div class="col-md-6 kenek-block">
                  <label class="form-label small">Kenek (%)</label>
                  <div class="input-group">
                    <input type="range" min="0" max="100" step="1" class="form-range me-3 flex-grow-1" id="range_kenek">
                    <input type="number" min="0" max="100" step="1" class="form-control w-25" id="input_kenek"
                           name="installer_share[kenek]" value="{{ old('installer_share.kenek', 30) }}">
                    <span class="input-group-text">%</span>
                  </div>
                </div>
                <div class="col-12 d-flex align-items-center gap-3">
                  <div class="progress flex-grow-1" style="height:8px;">
                    <div id="bar_tukang" class="progress-bar bg-primary" style="width:70%"></div>
                    <div id="bar_kenek" class="progress-bar bg-success" style="width:30%"></div>
                  </div>
                  <span id="total_badge" class="badge bg-primary">100%</span>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="normalize_100"
                           name="normalize_100" value="1" {{ old('normalize_100', 1) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="normalize_100">Normalisasi ke 100%</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-3 pt-3 border-top">
              <div>
                <span class="text-muted small">Pool estimasi:</span>
                <span id="pool_nominal" class="fw-bold ms-1">Rp0</span>
              </div>
              <div class="vr" style="height:16px;"></div>
              <span class="badge" style="background:var(--primary-light);color:var(--primary-600)">
                Teknisi: <span id="preview_t_nom">Rp0</span>
              </span>
              <span class="badge kenek-block" style="background:rgba(34,197,94,.15);color:#16a34a">
                Kenek: <span id="preview_k_nom">Rp0</span>
              </span>
            </div>
          </div>

        </div>
      </div>

      {{-- ── Section 5: Penjualan ── --}}
      <div id="sec-penjualan" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:store-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Informasi Penjualan</p>
            <p class="sec-desc">Dealer dan marketing yang mereferensikan customer</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama Dealer</label>
              <input type="text" name="dealer_name" class="form-control"
                     value="{{ old('dealer_name', $customer->dealer_name) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Marketing</label>
              <select name="marketing_id" class="form-select">
                <option value="">— Tidak ada —</option>
                @foreach ($employees as $e)
                  @if ($e->job_position === 'Marketing')
                    <option value="{{ $e->id }}"
                      {{ (string)old('marketing_id', $customer->marketing_id) === (string)$e->id ? 'selected' : '' }}>
                      {{ $e->name }}
                    </option>
                  @endif
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Section 6: Keamanan + Action Bar ── --}}
      <div id="sec-keamanan" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon sec-icon-danger"><iconify-icon icon="mdi:lock-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Konfirmasi Keamanan</p>
            <p class="sec-desc">Masukkan password Anda untuk menyimpan perubahan</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Password Anda <span class="text-danger">*</span></label>
              <input type="password" name="admin_password"
                     class="form-control @error('admin_password') is-invalid @enderror" required>
              @error('admin_password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
        <div class="cust-action-bar">
          <x-ui.button href="{{ route('customer.index') }}" variant="outline">Batal</x-ui.button>
          <x-ui.button type="submit" variant="primary" class="px-4">
            <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon> Simpan Perubahan
          </x-ui.button>
        </div>
      </div>

    </form>
  </div>
</div>
@endsection

@push('page-scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>const COMMISSION_FIXED = @json($commissionFixed);</script>
<script>
(function () {

  // ── Phone formatting (normalise stored value: +62xxx / 62xxx / 0xxx → 8xx xxxx) ──
  (function () {
    const display = document.getElementById('phone_display');
    const hidden  = document.getElementById('phone_hidden');
    if (!display || !hidden) return;

    function strip(v) {
      v = v.replace(/\D/g, '');
      if (v.startsWith('62')) v = v.slice(2);
      if (v.startsWith('0'))  v = v.slice(1);
      return v;
    }
    function fmt(d) {
      const a = d.slice(0, 3), b = d.slice(3, 7), c = d.slice(7, 11);
      return [a, b, c].filter(Boolean).join(' ');
    }
    const initDigits = strip(hidden.value || '');
    display.value = fmt(initDigits);
    if (initDigits) hidden.value = '+62' + initDigits;

    display.addEventListener('input', function () {
      const raw    = strip(this.value);
      const pos    = this.selectionStart;
      const before = (this.value.slice(0, pos).match(/ /g) || []).length;
      this.value   = fmt(raw);
      const after  = (this.value.slice(0, pos).match(/ /g) || []).length;
      const newPos = Math.max(0, pos + after - before);
      this.setSelectionRange(newPos, newPos);
      hidden.value = raw ? '+62' + raw : '';
    });
  })();

  // ── Panoramic ──
  const installTypeEl = document.getElementById('install_type');
  const panoWrap      = document.getElementById('panoramic_wrap');
  const panoOn        = document.getElementById('pano_on');
  const panoOff       = document.getElementById('pano_off');
  const panoBadge     = document.getElementById('pano_status_badge');
  const panoHint      = document.getElementById('pano_hint');

  function refreshPanoWrap() {
    const type = installTypeEl?.value;
    const showPano = type === 'fullset' || type === 'dsp';
    panoWrap?.classList.toggle('d-none', !showPano);
    if (!showPano && panoOff) panoOff.checked = true;
    refreshPanoText();
    window._custUpdatePreview?.();
  }
  function refreshPanoText() {
    const active = !!panoOn?.checked;
    const type   = installTypeEl?.value;
    if (panoBadge) {
      panoBadge.className = 'badge ' + (active ? 'bg-primary' : 'bg-secondary');
      panoBadge.textContent = active ? 'AKTIF' : 'NONAKTIF';
    }
    if (panoHint) {
      if (!active) {
        panoHint.textContent = type === 'dsp' ? 'Komisi D/S/P reguler.' : 'Komisi Fullset reguler.';
      } else {
        panoHint.textContent = type === 'dsp'
          ? 'Komisi D/S/P + Panoramic (2× rate).'
          : 'Komisi mengikuti aturan panoramic.';
      }
    }
  }
  installTypeEl?.addEventListener('change', refreshPanoWrap);
  [panoOn, panoOff].forEach(el => el?.addEventListener('change', () => { refreshPanoText(); window._custUpdatePreview?.(); }));
  refreshPanoWrap();

  // ── Commission share ──
  (function () {
    const shareAuto   = document.getElementById('share_auto');
    const shareCustom = document.getElementById('share_custom');
    const wrapCustom  = document.getElementById('custom-share-wrap');
    const presetWrap  = document.getElementById('preset-wrap');
    const normalizeEl = document.getElementById('normalize_100');
    const tukangSel   = document.getElementById('tukang_select');
    const kenekSel    = document.getElementById('kenek_select');
    const kenekBlocks = document.querySelectorAll('.kenek-block');
    const sameBanner  = document.getElementById('same-pair-banner');
    const autoLabel   = document.getElementById('auto-ratio-label');
    const rangeT = document.getElementById('range_tukang');
    const rangeK = document.getElementById('range_kenek');
    const inputT = document.getElementById('input_tukang');
    const inputK = document.getElementById('input_kenek');
    const barT   = document.getElementById('bar_tukang');
    const barK   = document.getElementById('bar_kenek');
    const badge  = document.getElementById('total_badge');
    const poolNom = document.getElementById('pool_nominal');
    const tNom    = document.getElementById('preview_t_nom');
    const kNom    = document.getElementById('preview_k_nom');

    function fmtIdr(n) { return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n||0); }
    function clamp(v)  { v = parseInt(v||0,10); return isNaN(v) ? 0 : Math.max(0, Math.min(100, v)); }
    function hasKenek(){ return !!(kenekSel?.value); }
    function isCustom(){ return !!(shareCustom?.checked); }

    function detectSamePair() {
      if (!tukangSel || !kenekSel?.value) return false;
      const tPos = tukangSel.options[tukangSel.selectedIndex]?.dataset?.position ?? '';
      const kPos = kenekSel.options[kenekSel.selectedIndex]?.dataset?.position ?? '';
      return tPos !== '' && tPos === kPos;
    }
    function updateSamePairUI() {
      const same = detectSamePair() && hasKenek();
      sameBanner?.classList.toggle('d-none', !same);
      if (autoLabel) autoLabel.textContent = same ? '(50:50)' : '(70:30)';
    }
    function toggleMode() {
      wrapCustom?.classList.toggle('d-none', !isCustom());
      presetWrap?.classList.toggle('d-none', !isCustom());
      updateAll();
    }
    function toggleKenekBlocks() {
      const hk = hasKenek();
      kenekBlocks.forEach(el => el.classList.toggle('d-none', !hk));
      if (!hk && inputK) { inputK.value = 0; if (rangeK) rangeK.value = 0; }
      updateAll();
    }
    function normalize() {
      if (!normalizeEl?.checked || !hasKenek()) return;
      let t = clamp(inputT?.value), k = clamp(inputK?.value);
      const s = t + k;
      if (s === 0) { t = 100; k = 0; }
      else { t = Math.round(t * 100 / s); k = 100 - t; }
      if (inputT) { inputT.value = t; if (rangeT) rangeT.value = t; }
      if (inputK) { inputK.value = k; if (rangeK) rangeK.value = k; }
    }
    function updateBars() {
      const t = clamp(inputT?.value), k = clamp(inputK?.value), tot = t + k;
      if (barT) barT.style.width = `${t}%`;
      if (barK) barK.style.width = `${k}%`;
      if (badge) {
        badge.textContent = `${tot}%`;
        badge.classList.toggle('bg-danger',  tot !== 100);
        badge.classList.toggle('bg-primary', tot === 100);
      }
    }
    function currentKey() {
      const it = installTypeEl?.value; if (!it) return null;
      if (it === 'skkb' || it === 'dsp') return it;
      return panoOn?.checked ? 'full_panoramic' : 'fullset';
    }
    function calcPool() {
      const row = (COMMISSION_FIXED || {})[currentKey()] || {};
      const t = parseInt(row.Teknisi ?? row.teknisi ?? row.tukang ?? 0, 10);
      const k = parseInt(row.Kenek   ?? row.kenek   ?? 0, 10);
      return (t + k) || 0;
    }
    function updatePreview() {
      const pool = calcPool();
      if (poolNom) poolNom.textContent = fmtIdr(pool);
      let tPct, kPct;
      if (!isCustom()) {
        const same = detectSamePair() && hasKenek();
        tPct = hasKenek() ? (same ? 50 : 70) : 100;
        kPct = hasKenek() ? (same ? 50 : 30) : 0;
      } else {
        tPct = clamp(inputT?.value); kPct = clamp(inputK?.value);
        if (normalizeEl?.checked && hasKenek()) {
          const s = tPct + kPct; if (s > 0) { tPct = Math.round(tPct * 100 / s); kPct = 100 - tPct; }
        }
        if (!hasKenek()) kPct = 0;
      }
      if (tNom) tNom.textContent = fmtIdr(Math.floor(pool * tPct / 100));
      if (kNom) kNom.textContent = fmtIdr(Math.floor(pool * kPct / 100));
    }
    function updateAll() { if (isCustom()) normalize(); updateBars(); updatePreview(); }

    window._custUpdatePreview = updatePreview;

    [shareAuto, shareCustom].forEach(el => el?.addEventListener('change', toggleMode));
    normalizeEl?.addEventListener('change', updateAll);
    kenekSel?.addEventListener('change', () => { toggleKenekBlocks(); updateSamePairUI(); });
    tukangSel?.addEventListener('change', () => { updateSamePairUI(); updatePreview(); });
    [installTypeEl, panoOn].forEach(el => el?.addEventListener('change', updatePreview));

    function bindPair(rangeEl, inputEl, otherInput) {
      rangeEl?.addEventListener('input', () => {
        if (inputEl) inputEl.value = rangeEl.value;
        if (isCustom() && normalizeEl?.checked && hasKenek() && otherInput) {
          const v = 100 - parseInt(rangeEl.value, 10);
          otherInput.value = v;
          (otherInput === inputK ? rangeK : rangeT).value = v;
        }
        updateAll();
      });
      inputEl?.addEventListener('input', () => {
        const v = clamp(inputEl.value);
        inputEl.value = v; if (rangeEl) rangeEl.value = v; updateAll();
      });
    }
    bindPair(rangeT, inputT, inputK);
    bindPair(rangeK, inputK, inputT);

    document.querySelectorAll('.preset-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (!isCustom() && shareCustom) { shareCustom.checked = true; toggleMode(); }
        const t = parseInt(btn.dataset.t, 10), k = parseInt(btn.dataset.k, 10);
        if (inputT && rangeT) { inputT.value = t; rangeT.value = t; }
        if (inputK && rangeK) { inputK.value = k; rangeK.value = k; }
        updateAll();
      });
    });

    const initSame = detectSamePair() && hasKenek();
    if (!isCustom() && rangeT && rangeK && inputT && inputK) {
      rangeT.value = initSame ? 50 : 70; rangeK.value = initSame ? 50 : 30;
      inputT.value = initSame ? 50 : 70; inputK.value = initSame ? 50 : 30;
    }
    toggleKenekBlocks();
    updateSamePairUI();
    toggleMode();
  })();

  // ── Product AJAX ──
  let productIndex = {{ count($prefillProducts) }};

  function loadProductsByCategory(catSelect) {
    const catId   = $(catSelect).val();
    const index   = $(catSelect).data('index');
    const $prod   = $(`select[name="products[${index}][product_id]"]`);
    const current = String($prod.attr('data-current') || '');
    $prod.html('<option value="">Memuat...</option>');
    if (!catId) { $prod.html('<option value="">Pilih Produk</option>'); return; }
    $.get('{{ url("products-by-category") }}/' + catId, function (data) {
      let opts = '<option value="">Pilih Produk</option>';
      data.forEach(p => {
        const sel = String(p.id) === current ? 'selected' : '';
        opts += `<option value="${p.id}" ${sel}>${p.name}</option>`;
      });
      $prod.html(opts);
      if (current) $prod.val(current);
    }).fail(function () { $prod.html('<option value="">Pilih Produk</option>'); });
  }

  $(document).on('change', '.category-select', function () {
    $(this).closest('.product-item').find('.product-select').attr('data-current', '');
    loadProductsByCategory(this);
  });

  $(function () {
    $('.category-select').each(function () {
      if ($(this).val()) loadProductsByCategory(this);
    });
  });

  document.getElementById('add-product')?.addEventListener('click', function () {
    const container = document.getElementById('product-container');
    const clone     = container.querySelector('.product-item').cloneNode(true);
    clone.querySelector('input[type="hidden"][name$="[id]"]')?.remove();
    clone.querySelectorAll('select').forEach(el => {
      el.setAttribute('name', el.getAttribute('name').replace(/\[\d+\]/, `[${productIndex}]`));
      if (el.classList.contains('category-select') || el.classList.contains('product-select')) {
        el.setAttribute('data-index', productIndex);
      }
      if (el.classList.contains('product-select')) {
        el.setAttribute('data-current', '');
        el.innerHTML = '<option value="">Pilih Produk</option>';
      }
      el.selectedIndex = 0;
    });
    container.appendChild(clone);
    productIndex++;
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('.remove-product')) {
      const item = e.target.closest('.product-item');
      if (document.querySelectorAll('.product-item').length > 1) item.remove();
    }
  });

  // ── Sidebar scroll spy ──
  const navLinks = document.querySelectorAll('#custNav a');
  const sections = document.querySelectorAll('.cust-section[id]');
  const obs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        navLinks.forEach(a => a.classList.remove('is-active'));
        const link = document.querySelector(`#custNav a[href="#${entry.target.id}"]`);
        if (link) link.classList.add('is-active');
      }
    });
  }, { threshold: 0.3 });
  sections.forEach(s => obs.observe(s));

})();
</script>
@endpush
