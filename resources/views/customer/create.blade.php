@extends('dashboard.index')

@section('title', 'Add Customers')

@section('route-breadcrumb', 'customer.index')

@section('breadcrumb', 'Customers')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Form Tambah Customer</h5>
        <a href="{{ route('customer.index') }}" class="btn btn-sm btn-secondary">
            Kembali
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('customer.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="wsn" class="form-label">Warranty Serial Number <span class="text-danger">*</span></label>
                    <input type="text" name="wsn" class="form-control" required value="{{ old('wsn') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Kartu <span class="text-danger">*</span></label>
                    <input type="number" name="card_number" class="form-control" disabled >
                </div>

                <hr class="mt-5">

                <h5 class="text-center">Customer Personal Data</h5>

                <div class="col-md-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                    <input type="text" name="phone_number" class="form-control" required value="{{ old('phone_number') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                </div>

                <hr class="mt-5">

                <h5 class="text-center">Authorized Dealer</h5>

                <div class="col-md-6">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Dealer</label>
                            <input type="text" name="dealer_name" class="form-control" value="{{ old('dealer_name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marketing</label>
                            <select name="marketing_id" class="form-select">
                                <option value="">— Tidak ada —</option>
                                @foreach ($employees as $e)
                                    @if ($e->job_position === 'Marketing')
                                        <option value="{{ $e->id }}" {{ old('marketing_id')==$e->id ? 'selected' : '' }}>
                                        {{ $e->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                        <label class="form-label">Tukang <span class="text-danger">*</span></label>
                        <select name="tukang_id" class="form-select" required>
                            <option value="">Pilih</option>
                            @foreach ($employees as $e)
                            @if ($e->job_position === 'Teknisi')
                            <option value="{{ $e->id }}" {{ old('tukang_id')==$e->id ? 'selected' : '' }}>
                                {{ $e->name }}
                            </option>
                            @endif
                            @endforeach
                        </select>

                        </div>
                        <div class="col-md-6 mb-3">
                        <label class="form-label">Kenek (opsional)</label>
                        <select name="kenek_id" class="form-select">
                            <option value="">— Tidak ada —</option>
                            @foreach ($employees as $e)
                            @if ($e->job_position === 'Kenek')

                            <option value="{{ $e->id }}" {{ old('kenek_id')==$e->id ? 'selected' : '' }}>
                                {{ $e->name }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                        </div>
                    </div>
                </div>

                @php
                // kirim config komisi ke JS agar bisa hitung "pool" (estimasi)
                $commissionFixed = config('commission.fixed', []);
                @endphp

                <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Pembagian Komisi Installer</h6>
                    <span class="badge bg-light text-dark">Pool = Teknisi + Kenek (estimasi)</span>
                </div>
                <div class="card-body">

                    {{-- Mode pilih --}}
                    <div class="btn-group w-100 mb-3" role="group" aria-label="Mode Pembagian">
                <input type="radio" class="btn-check" name="share_mode" id="share_auto" value="auto"
                        autocomplete="off" {{ old('share_mode','auto')==='auto' ? 'checked' : '' }}>
                <label class="btn btn-outline-primary" for="share_auto">Otomatis (70:30)</label>

                <input type="radio" class="btn-check" name="share_mode" id="share_custom" value="custom"
                        autocomplete="off" {{ old('share_mode')==='custom' ? 'checked' : '' }}>
                <label class="btn btn-outline-primary" for="share_custom">Kustom Persen</label>
                </div>

                <div id="preset-wrap" class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="70" data-k="30">70 : 30</button>
                <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="60" data-k="40">60 : 40</button>
                <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="50" data-k="50">50 : 50</button>
                <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="100" data-k="0">100 : 0</button>
                <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="0" data-k="100">0 : 100</button>
                </div>


                    {{-- Custom share --}}
                    <div id="custom-share-wrap" class="{{ old('share_mode')==='custom' ? '' : 'd-none' }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-6">
                        <label class="form-label">Tukang (%)</label>
                        <div class="input-group">
                            <input type="range" min="0" max="100" step="1" class="form-range me-3 flex-grow-1" id="range_tukang">
                            <input type="number" min="0" max="100" step="1" class="form-control w-25" id="input_tukang"
                                name="installer_share[tukang]" value="{{ old('installer_share.tukang', 50) }}">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Persentase bagian Tukang dari pool.</div>
                        </div>

                        <div class="col-md-6 kenek-block">
                        <label class="form-label">Kenek (%)</label>
                        <div class="input-group">
                            <input type="range" min="0" max="100" step="1" class="form-range me-3 flex-grow-1" id="range_kenek">
                            <input type="number" min="0" max="100" step="1" class="form-control w-25" id="input_kenek"
                                name="installer_share[kenek]" value="{{ old('installer_share.kenek', 50) }}">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Persentase bagian Kenek dari pool.</div>
                        </div>

                        <div class="col-12 d-flex align-items-center gap-3">
                        <div class="progress flex-grow-1" style="height: 10px;">
                            <div id="bar_tukang" class="progress-bar bg-primary" role="progressbar" style="width: 50%;"></div>
                            <div id="bar_kenek" class="progress-bar bg-success" role="progressbar" style="width: 50%;"></div>
                        </div>
                        <span id="total_badge" class="badge bg-{{ old('share_mode')==='custom' ? 'primary' : 'secondary' }}">Total: 100%</span>
                        </div>

                        <div class="col-12">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="normalize_100" name="normalize_100" value="1"
                                {{ old('normalize_100', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="normalize_100">
                            Normalisasi hingga total 100% (disarankan)
                            </label>
                        </div>
                        </div>

                    </div>
                    </div>

                    {{-- Preview nominal (estimasi) --}}
                    <hr class="my-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                    <div>
                        <span class="text-muted">Pool (estimasi):</span>
                        <span id="pool_nominal" class="fw-bold">Rp0</span>
                    </div>
                    <div class="vr" style="height: 20px;"></div>
                    <div>
                        <span class="badge bg-primary">Tukang: <span id="preview_t_nom">Rp0</span></span>
                    </div>
                    <div class="kenek-block">
                        <span class="badge bg-success">Kenek: <span id="preview_k_nom">Rp0</span></span>
                    </div>
                    <div class="ms-auto">
                        <small class="text-muted">Estimasi dihitung dari pilihan <em>Tipe Pemasangan</em> &amp; <em>Panoramic</em>.</small>
                    </div>
                    </div>

                </div>
                </div>


                <div class="col-md-6">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Negara</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country') }}">
                </div>

                <hr class="mt-5">

                <h5 class="text-center">Vehicle Description</h5>

                <div class="col-md-6">
                    <label class="form-label">Plat Nomor</label>
                    <input type="text" name="plat_number" class="form-control" value="{{ old('plat_number') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Merek Kendaraan</label>
                    <input type="text" name="vehicle_brand" class="form-control" value="{{ old('vehicle_brand') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Model Kendaraan</label>
                    <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tahun Kendaraan</label>
                    <input type="number" name="vehicle_year" class="form-control" value="{{ old('vehicle_year') }}">
                </div>



            </div>

            <hr class="mt-5">

            <h5 class="mt-5 mb-5 text-center">Produk Customer</h5>
            <div id="product-container">
                @php
                    $oldProducts = old('products', [['part_id'=>'','category_product_id'=>'','product_id'=>'']]);
                @endphp
                <div class="row">
                   <div class="col-md-12">
                    <label class="form-label">Tipe Pemasangan <span class="text-danger">*</span></label>
                        <select name="install_type" id="install_type" class="form-select mb-2" required>
                            <option value="">— Pilih —</option>
                            <option value="fullset" {{ old('install_type')==='fullset' ? 'selected' : '' }}>Fullset</option>
                            <option value="skkb"    {{ old('install_type')==='skkb' ? 'selected' : '' }}>SKKB</option>
                            <option value="dsp"     {{ old('install_type')==='dsp' ? 'selected' : '' }}>D/S/P</option>
                        </select>

                        <div id="panoramic_wrap" class="mt-3 {{ old('install_type','')==='fullset' ? '' : 'd-none' }}">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <span id="pano_status_badge"
                                            class="badge {{ old('has_panoramic') ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ old('has_panoramic') ? 'Panoramic: AKTIF' : 'Panoramic: NONAKTIF' }}
                                    </span>
                                    <small id="pano_hint" class="text-muted">
                                        {{ old('has_panoramic')
                                            ? 'Komisi Fullset hari ini mengikuti aturan panoramic.'
                                            : 'Komisi mengikuti aturan Fullset reguler (non-panoramic).' }}
                                    </small>
                                </div>
                            {{-- Segmented toggle yang jelas on/off --}}
                            <div class="btn-group" role="group" aria-label="Has Panoramic toggle">
                                <input type="radio" class="btn-check" name="has_panoramic" id="pano_off" value="0"
                                    {{ old('has_panoramic') ? '' : 'checked' }}>
                                <label class="btn btn-outline-secondary" for="pano_off">Tidak</label>

                                <input type="radio" class="btn-check" name="has_panoramic" id="pano_on" value="1"
                                    {{ old('has_panoramic') ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="pano_on">Ya</label>
                            </div>
                            </div>

                            {{-- Status badge + hint dinamis --}}

                        </div>
                    </div>
                </div>

                @foreach($oldProducts as $i => $prod)
                    <div class="row align-items-end mb-3 product-item">
                    <div class="col-md-4">
                        <label>Part</label>
                        <select name="products[{{ $i }}][part_id]" class="form-select" required>
                        <option value="">Pilih Part</option>
                        @foreach($parts as $part)
                            <option value="{{ $part->id }}" {{ (old("products.$i.part_id", $prod['part_id'] ?? '') == $part->id) ? 'selected' : '' }}>
                            {{ $part->name }}
                            </option>
                        @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Kategori Produk</label>
                        <select name="products[{{ $i }}][category_product_id]"
                                class="form-select category-select" data-index="{{ $i }}" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old("products.$i.category_product_id", $prod['category_product_id'] ?? '') == $cat->id) ? 'selected' : '' }}>
                            {{ $cat->name }}
                            </option>
                        @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Nama Produk</label>
                        <select name="products[{{ $i }}][product_id]"
                                class="form-select product-select" data-index="{{ $i }}" required>
                        {{-- akan diisi via AJAX, tapi jika old() ada dan option tersedia, akan diset di JS --}}
                        <option value="">Pilih Produk</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-product text-center">×</button>
                    </div>
                    </div>
                @endforeach

            </div>

<button type="button" class="btn btn-sm btn-outline-primary" id="add-product">+ Tambah Produk</button>

<hr class="mt-5">

<div class="col-md-12 mt-3">
                    <label class="form-label">Durasi Garansi</label>
                    <select name="warantee_duration" class="form-select" required>
                        <option value="">Pilih Durasi</option>
                        <option value="5">5 Tahun</option>
                        <option value="7">7 Tahun</option>
                    </select>
                </div>



<div class="col-md-6 mt-3">
    <label class="form-label">Konfirmasi Password Anda <span class="text-danger">*</span></label>
    <input type="password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" required>
    @error('admin_password')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-primary mt-5">
    Simpan Customer
</button>
</div>

</div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- kirim config commission ke JS --}}
<script>
  const COMMISSION_FIXED = @json($commissionFixed);
</script>
<script>
  (function () {
    const selectType = document.getElementById('install_type');
    const panoWrap   = document.getElementById('panoramic_wrap');
    const panoOn     = document.getElementById('pano_on');
    const panoOff    = document.getElementById('pano_off');
    const badge      = document.getElementById('pano_status_badge');
    const hint       = document.getElementById('pano_hint');

    function updatePanoVisibility() {
      const isFullset = selectType.value === 'fullset';
      panoWrap.classList.toggle('d-none', !isFullset);

      // jika bukan fullset, paksa nonaktif (simpan nilai 0)
      if (!isFullset) {
        if (panoOff) panoOff.checked = true;
        if (badge) badge.className = 'badge bg-secondary';
        if (badge) badge.textContent = 'Panoramic: NONAKTIF';
        if (hint)  hint.textContent  = 'Komisi mengikuti aturan Fullset reguler (non-panoramic).';
      }
    }

    function updatePanoStatusText() {
      const active = panoOn && panoOn.checked;
      if (badge) {
        badge.className = 'badge ' + (active ? 'bg-primary' : 'bg-secondary');
        badge.textContent = active ? 'Panoramic: AKTIF' : 'Panoramic: NONAKTIF';
      }
      if (hint) {
        hint.textContent = active
          ? 'Komisi Fullset hari ini mengikuti aturan panoramic.'
          : 'Komisi mengikuti aturan Fullset reguler (non-panoramic).';
      }
    }

    // events
    if (selectType) selectType.addEventListener('change', updatePanoVisibility);
    [panoOn, panoOff].forEach(el => {
      if (el) el.addEventListener('change', updatePanoStatusText);
    });

    // init
    updatePanoVisibility();
    updatePanoStatusText();

    // Bootstrap tooltip init (opsional; hapus jika tidak pakai Bootstrap icons/tooltip)
    if (window.bootstrap && bootstrap.Tooltip) {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
      });
    }
  })();
</script>

<script>
(function() {
  const shareAuto   = document.getElementById('share_auto');
  const shareCustom = document.getElementById('share_custom');
  const wrapCustom  = document.getElementById('custom-share-wrap');
  const normalizeEl = document.getElementById('normalize_100');

  const kenekSelect = document.querySelector('select[name="kenek_id"]');
  const kenekBlocks = document.querySelectorAll('.kenek-block');

  const rangeT = document.getElementById('range_tukang');
  const rangeK = document.getElementById('range_kenek');
  const inputT = document.getElementById('input_tukang');
  const inputK = document.getElementById('input_kenek');

  const barT   = document.getElementById('bar_tukang');
  const barK   = document.getElementById('bar_kenek');
  const badge  = document.getElementById('total_badge');

  const installTypeEl  = document.getElementById('install_type');
  const panoEl         = document.getElementById('has_panoramic');
  const poolNom        = document.getElementById('pool_nominal');
  const previewT       = document.getElementById('preview_t_nom');
  const previewK       = document.getElementById('preview_k_nom');

  // FIX: opsional, sinkronkan hidden share_mode bila ada
  const hiddenShareMode = document.querySelector('input[type="hidden"][name="share_mode"]');

  // helpers
  function fmtIdr(n){
    return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n||0);
  }
  function clamp0100(v){ v=parseInt(v||0,10); return isNaN(v)?0:Math.max(0,Math.min(100,v)); }
  function isCustomMode(){
    // FIX: handle jika radio tidak ada → anggap custom jika wrap custom terlihat
    if (shareCustom) return !!shareCustom.checked;
    return !wrapCustom?.classList.contains('d-none');
  }
  function setShareModeHidden(){
    if (!hiddenShareMode) return;
    hiddenShareMode.value = isCustomMode() ? 'custom' : 'auto';
  }
  function hasKenekSelected(){ return !!(kenekSelect && kenekSelect.value); }
  function getPresetWrap(){ return document.getElementById('preset-wrap'); } // may be null

  function toggleMode() {
    const isCustom = isCustomMode();
    if (wrapCustom) wrapCustom.classList.toggle('d-none', !isCustom);
    if (badge) {
      badge.classList.toggle('bg-primary', isCustom);
      badge.classList.toggle('bg-secondary', !isCustom);
    }
    const presetWrap = getPresetWrap();
    if (presetWrap) presetWrap.classList.toggle('d-none', !isCustom);

    setShareModeHidden(); // FIX: pastikan nilai terkirim
    updateUI();
  }

  function toggleKenekBlocks() {
    const hasKenek = hasKenekSelected();
    kenekBlocks.forEach(el => el.classList.toggle('d-none', !hasKenek));
    if (!hasKenek) {
      // force 100% ke tukang jika tidak ada kenek
      if (inputK) inputK.value = 0;
      if (rangeK) rangeK.value = 0;
      if (isCustomMode() && inputT && rangeT) {
        const t = 100;
        inputT.value = t;
        rangeT.value = t;
      }
    }
    updateUI();
  }

  function normalize() {
    // FIX: guard normalizeEl exist
    if (!normalizeEl || !normalizeEl.checked) return;

    const hasKenek = hasKenekSelected();
    let t = clamp0100(inputT?.value);
    let k = hasKenek ? clamp0100(inputK?.value) : 0;

    if (!hasKenek) {
      if (inputK) inputK.value = 0;
      if (rangeK) rangeK.value = 0;
      if (inputT) inputT.value = t;
      if (rangeT) rangeT.value = t;
      return;
    }

    const s = t + k;
    if (s === 0) { t = 100; k = 0; }
    else {
      t = Math.round(t * 100 / s);
      k = 100 - t;
    }
    if (inputT) inputT.value = t;
    if (rangeT) rangeT.value = t;
    if (inputK) inputK.value = k;
    if (rangeK) rangeK.value = k;
  }

  function updateBarsAndBadge() {
    const t = clamp0100(inputT?.value);
    const k = clamp0100(inputK?.value);
    const total = t + k;
    if (barT) barT.style.width = `${t}%`;
    if (barK) barK.style.width = `${k}%`;
    if (badge) {
      badge.textContent = `Total: ${total}%`;
      if (total === 100) {
        badge.classList.remove('bg-danger');
        badge.classList.add('bg-primary');
      } else {
        badge.classList.remove('bg-primary');
        badge.classList.add('bg-danger');
      }
    }
  }

  // hitung pool estimasi dari config
  function currentFinalKey() {
    const it = installTypeEl?.value; // 'fullset' | 'skkb' | 'dsp'
    if (!it) return null;
    if (it === 'skkb' || it === 'dsp') return it;
    const pano = !!(panoEl && panoEl.checked);
    return pano ? 'full_panoramic' : 'fullset';
  }

  function calcPool() {
    // FIX: guard COMMISSION_FIXED
    if (typeof COMMISSION_FIXED === 'undefined') return 0;
    const key = currentFinalKey();
    if (!key) return 0;
    const row = COMMISSION_FIXED[key] || {};
    const t = parseInt(row.Teknisi ?? row.teknisi ?? row.tukang ?? 0, 10);
    const k = parseInt(row.Kenek   ?? row.kenek   ?? 0, 10);
    return (t + k) || 0;
  }

  function updatePreview() {
    const pool = calcPool();
    if (poolNom) poolNom.textContent = fmtIdr(pool);

    let tPct, kPct;
    const hasKenek = hasKenekSelected();

    if (!isCustomMode()) {
      // AUTO = 70:30 jika tukang & kenek ada; 100:0 jika hanya satu
      if (hasKenek) { tPct = 70; kPct = 30; }
      else { tPct = 100; kPct = 0; }
    } else {
      tPct = clamp0100(inputT?.value);
      kPct = clamp0100(inputK?.value);
      if (normalizeEl && normalizeEl.checked && hasKenek) {
        const s = tPct + kPct;
        if (s > 0) { tPct = Math.round(tPct * 100 / s); kPct = 100 - tPct; }
      }
      if (!hasKenek) { kPct = 0; }
    }

    if (previewT) previewT.textContent = fmtIdr(Math.floor(pool * tPct / 100));
    if (previewK) previewK.textContent = fmtIdr(Math.floor(pool * kPct / 100));
  }

  function updateUI() {
    if (isCustomMode()) normalize();
    updateBarsAndBadge();
    updatePreview();
  }

  // events (FIX: guard null sebelum addEventListener)
  [shareAuto, shareCustom].forEach(el => {
    if (el) el.addEventListener('change', toggleMode);
  });
  if (normalizeEl) normalizeEl.addEventListener('change', updateUI);
  if (kenekSelect) kenekSelect.addEventListener('change', toggleKenekBlocks);

  // sync slider <-> number
  function bindPair(rangeEl, inputEl, otherEl) {
    if (!rangeEl || !inputEl) return;
    rangeEl.addEventListener('input', () => {
      inputEl.value = rangeEl.value;
      if (isCustomMode() && normalizeEl?.checked && hasKenekSelected() && otherEl) {
        const v = 100 - parseInt(rangeEl.value,10);
        otherEl.value = v;
        const otherRange = (otherEl === inputK ? rangeK : rangeT);
        if (otherRange) otherRange.value = v;
      }
      updateUI();
    });
    inputEl.addEventListener('input', () => {
      let v = clamp0100(inputEl.value);
      inputEl.value = v;
      if (rangeEl) rangeEl.value = v;
      updateUI();
    });
  }
  bindPair(rangeT, inputT, inputK);
  bindPair(rangeK, inputK, inputT);

  // preset buttons (FIX: guard preset-wrap existence)
  const presetWrap = getPresetWrap();
  if (presetWrap) {
    presetWrap.querySelectorAll('.preset-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (!isCustomMode() && shareCustom) { shareCustom.checked = true; toggleMode(); }
        const t = parseInt(btn.dataset.t,10);
        const k = parseInt(btn.dataset.k,10);
        if (inputT && rangeT) { inputT.value = t; rangeT.value = t; }
        if (inputK && rangeK) { inputK.value = k; rangeK.value = k; }
        updateUI();
      });
    });
  }

  // update bila sumber data lain berubah
  if (installTypeEl) installTypeEl.addEventListener('change', updatePreview);
  if (panoEl) panoEl.addEventListener('change', updatePreview);

  // initial
  toggleKenekBlocks(); // juga memanggil updateUI() lewat dalam
  // set default pair
  if (isCustomMode()) {
    const tOld = clamp0100(inputT?.value ?? 50);
    const kOld = clamp0100(inputK?.value ?? 50);
    if (rangeT) rangeT.value = tOld;
    if (rangeK) rangeK.value = kOld;
  } else {
    if (rangeT && rangeK && inputT && inputK) {
      rangeT.value = 50; rangeK.value = 50; inputT.value = 50; inputK.value = 50;
    }
  }
  toggleMode(); // set badge, preset visibility, hidden share_mode
})();
</script>


<script>
  let productIndex = {{ count(old('products', [['part_id'=>'','category_product_id'=>'','product_id'=>'']])) }};

  document.getElementById('add-product').addEventListener('click', function () {
      const container = document.getElementById('product-container');
      const template  = container.querySelector('.product-item');
      const newItem   = template.cloneNode(true);

      // reset values & rename indexes
      newItem.querySelectorAll('select').forEach(select => {
          const name = select.getAttribute('name');
          const newName = name.replace(/\[\d+\]/, `[${productIndex}]`);
          select.setAttribute('name', newName);

          if (select.classList.contains('category-select') || select.classList.contains('product-select')) {
              select.setAttribute('data-index', productIndex);
          }

          // kosongkan pilihan
          select.selectedIndex = 0;
          if (select.classList.contains('product-select')) {
              select.innerHTML = '<option value="">Pilih Produk</option>';
          }
      });

      container.appendChild(newItem);
      productIndex++;
  });

  // Remove row
  document.addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-product')) {
          const item = e.target.closest('.product-item');
          if (document.querySelectorAll('.product-item').length > 1) {
              item.remove();
          }
      }
  });

  // AJAX isi produk sesuai kategori
  $(document).on('change', '.category-select', function () {
      let categoryId = $(this).val();
      let index = $(this).data('index');
      let $productSelect = $(`select[name="products[${index}][product_id]"]`);

      $productSelect.html('<option value="">Memuat...</option>');

      if (categoryId) {
          $.ajax({
              url: '{{ url("products-by-category") }}/' + categoryId,
              type: 'GET',
              success: function (data) {
                  let options = '<option value="">Pilih Produk</option>';
                  data.forEach(product => {
                      options += `<option value="${product.id}">${product.name}</option>`;
                  });
                  $productSelect.html(options);

                  // SET old product_id jika ada
                  const oldVal = @json(old('products', []));
                  if (oldVal[index] && oldVal[index].product_id) {
                      $productSelect.val(oldVal[index].product_id);
                  }
              },
              error: function () {
                  alert('Gagal mengambil produk.');
                  $productSelect.html('<option value="">Pilih Produk</option>');
              }
          });
      } else {
          $productSelect.html('<option value="">Pilih Produk</option>');
      }
  });

  // PRELOAD: trigger change untuk semua baris yang punya kategori lama
  $(function() {
      const oldProducts = @json(old('products', []));
      oldProducts.forEach((row, i) => {
          if (row.category_product_id) {
              $(`select[name="products[${i}][category_product_id]"]`).trigger('change');
          }
      });
  });
</script>
<script>
  const selectType = document.getElementById('install_type');
  const panoWrap   = document.getElementById('panoramic_wrap');

  function togglePano() {
    panoWrap.classList.toggle('d-none', selectType.value !== 'fullset');
  }
  selectType.addEventListener('change', togglePano);
  // onload (kalau old value bukan fullset, tetap hidden)
  togglePano();
</script>

@endsection
