@extends('dashboard.index')

@section('title', 'Edit Customer')

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

@php
  // bentuk ulang baris produk untuk prarender form (old() -> model)
  $prefillProducts = collect(old('products'))->when(empty(old('products')), function($c) use ($customer){
      return $customer->products->map(function($p){
          return [
              'id'                   => $p->id,
              'part_id'              => $p->part_id,
              'category_product_id'  => $p->category_product_id,
              'product_id'           => $p->product_id,
          ];
      });
  })->values()->toArray();

  $commissionFixed = config('commission.fixed', []);
@endphp

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Edit Customer — {{ $customer->name }}</h5>
    <a href="{{ route('customer.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>

  <div class="card-body">
    <form action="{{ route('customer.update', $customer->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="row g-3">
        <div class="col-md-6">
          <label for="wsn" class="form-label">Warranty Serial Number <span class="text-danger">*</span></label>
          <input type="text" name="wsn" class="form-control" required
                 value="{{ old('wsn', $customer->wsn) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Nomor Kartu</label>
          <input type="text" class="form-control" value="{{ $customer->card_number }}" disabled>
        </div>

        <hr class="mt-5">

        <h5 class="text-center">Customer Personal Data</h5>

        <div class="col-md-6">
          <label class="form-label">Nama <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required
                 value="{{ old('name', $customer->name) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
          <input type="text" name="phone_number" class="form-control" required
                 value="{{ old('phone_number', $customer->phone_number) }}">
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

        <hr class="mt-5">

        <h5 class="text-center">Authorized Dealer</h5>

        <div class="col-md-6">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nama Dealer</label>
              <input type="text" name="dealer_name" class="form-control"
                     value="{{ old('dealer_name', $customer->dealer_name) }}">
            </div>
            <div class="col-md-6 mb-3">
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

        <div class="col-md-6">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Tukang <span class="text-danger">*</span></label>
              <select name="tukang_id" class="form-select" required>
                <option value="">Pilih</option>
                @foreach ($employees as $e)
                  @if ($e->job_position === 'Teknisi')
                    <option value="{{ $e->id }}"
                      {{ (string)old('tukang_id', $customer->tukang_id) === (string)$e->id ? 'selected' : '' }}>
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
                    <option value="{{ $e->id }}"
                      {{ (string)old('kenek_id', $customer->kenek_id) === (string)$e->id ? 'selected' : '' }}>
                      {{ $e->name }}
                    </option>
                  @endif
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Pembagian Komisi --}}
        <div class="card mt-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Pembagian Komisi Installer</h6>
            <span class="badge bg-light text-dark">Pool = Teknisi + Kenek (estimasi)</span>
          </div>
          <div class="card-body">
            {{-- Mode --}}
            <div class="btn-group w-100 mb-3" role="group">
              <input type="radio" class="btn-check" name="share_mode" id="share_auto" value="auto"
                     {{ old('share_mode','auto')==='auto' ? 'checked' : '' }}>
              <label class="btn btn-outline-primary" for="share_auto">Otomatis (70:30)</label>

              <input type="radio" class="btn-check" name="share_mode" id="share_custom" value="custom"
                     {{ old('share_mode')==='custom' ? 'checked' : '' }}>
              <label class="btn btn-outline-primary" for="share_custom">Kustom Persen</label>
            </div>

            <div id="preset-wrap" class="d-flex flex-wrap gap-2 mb-3 {{ old('share_mode')==='custom' ? '' : '' }}">
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="70" data-k="30">70 : 30</button>
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="60" data-k="40">60 : 40</button>
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="50" data-k="50">50 : 50</button>
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="100" data-k="0">100 : 0</button>
              <button type="button" class="btn btn-sm btn-outline-secondary preset-btn" data-t="0" data-k="100">0 : 100</button>
            </div>

            {{-- Custom --}}
            <div id="custom-share-wrap" class="{{ old('share_mode')==='custom' ? '' : 'd-none' }}">
              <div class="row g-3 align-items-end">
                <div class="col-md-6">
                  <label class="form-label">Tukang (%)</label>
                  <div class="input-group">
                    <input type="range" min="0" max="100" step="1" class="form-range me-3 flex-grow-1" id="range_tukang">
                    <input type="number" min="0" max="100" step="1" class="form-control w-25" id="input_tukang"
                           name="installer_share[tukang]" value="{{ old('installer_share.tukang', 70) }}">
                    <span class="input-group-text">%</span>
                  </div>
                </div>

                <div class="col-md-6 kenek-block">
                  <label class="form-label">Kenek (%)</label>
                  <div class="input-group">
                    <input type="range" min="0" max="100" step="1" class="form-range me-3 flex-grow-1" id="range_kenek">
                    <input type="number" min="0" max="100" step="1" class="form-control w-25" id="input_kenek"
                           name="installer_share[kenek]" value="{{ old('installer_share.kenek', 30) }}">
                    <span class="input-group-text">%</span>
                  </div>
                </div>

                <div class="col-12 d-flex align-items-center gap-3">
                  <div class="progress flex-grow-1" style="height:10px;">
                    <div id="bar_tukang" class="progress-bar bg-primary" style="width: 70%;"></div>
                    <div id="bar_kenek" class="progress-bar bg-success" style="width: 30%;"></div>
                  </div>
                  <span id="total_badge" class="badge bg-primary">Total: 100%</span>
                </div>

                <div class="col-12">
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="normalize_100" name="normalize_100" value="1"
                           {{ old('normalize_100', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="normalize_100">Normalisasi hingga total 100%</label>
                  </div>
                </div>
              </div>
            </div>

            {{-- Preview pool --}}
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
          <input type="text" name="city" class="form-control" value="{{ old('city', $customer->city) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Negara</label>
          <input type="text" name="country" class="form-control" value="{{ old('country', $customer->country) }}">
        </div>

        <hr class="mt-5">

        <h5 class="text-center">Vehicle Description</h5>

        <div class="col-md-6">
          <label class="form-label">Plat Nomor</label>
          <input type="text" name="plat_number" class="form-control" value="{{ old('plat_number', $customer->plat_number) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Merek Kendaraan</label>
          <input type="text" name="vehicle_brand" class="form-control" value="{{ old('vehicle_brand', $customer->vehicle_brand) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Model Kendaraan</label>
          <input type="text" name="vehicle_model" class="form-control" value="{{ old('vehicle_model', $customer->vehicle_model) }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Tahun Kendaraan</label>
          <input type="number" name="vehicle_year" class="form-control" value="{{ old('vehicle_year', $customer->vehicle_year) }}">
        </div>

      </div>

      <hr class="mt-5">

      <h5 class="mt-5 mb-4 text-center">Produk Customer</h5>

      {{-- Tipe pemasangan + panoramic --}}
      <div class="row mb-3">
        <div class="col-md-12">
          <label class="form-label">Tipe Pemasangan <span class="text-danger">*</span></label>
          <select name="install_type" id="install_type" class="form-select mb-2" required>
            <option value="">— Pilih —</option>
            <option value="fullset" {{ old('install_type', $customer->install_type)==='fullset' ? 'selected' : '' }}>Fullset</option>
            <option value="skkb"    {{ old('install_type', $customer->install_type)==='skkb' ? 'selected' : '' }}>SKKB</option>
            <option value="dsp"     {{ old('install_type', $customer->install_type)==='dsp' ? 'selected' : '' }}>D/S/P</option>
          </select>

          <div id="panoramic_wrap" class="mt-3 {{ old('install_type', $customer->install_type)==='fullset' ? '' : 'd-none' }}">
            <div class="d-flex align-items-center justify-content-between">
              <div class="mt-2 d-flex align-items-center gap-2">
                <span id="pano_status_badge"
                  class="badge {{ old('has_panoramic') ? 'bg-primary' : 'bg-secondary' }}">
                  {{ old('has_panoramic') ? 'Panoramic: AKTIF' : 'Panoramic: NONAKTIF' }}
                </span>
                <small id="pano_hint" class="text-muted">
                  {{ old('has_panoramic') ? 'Komisi Fullset mengikuti aturan panoramic.' : 'Komisi Fullset reguler (non-panoramic).' }}
                </small>
              </div>
              <div class="btn-group" role="group" aria-label="Has Panoramic toggle">
                <input type="radio" class="btn-check" name="has_panoramic" id="pano_off" value="0"
                       {{ old('has_panoramic') ? '' : 'checked' }}>
                <label class="btn btn-outline-secondary" for="pano_off">Tidak</label>

                <input type="radio" class="btn-check" name="has_panoramic" id="pano_on" value="1"
                       {{ old('has_panoramic') ? 'checked' : '' }}>
                <label class="btn btn-outline-primary" for="pano_on">Ya</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="product-container">
        @foreach($prefillProducts as $i => $row)
          <div class="row align-items-end mb-3 product-item">
            {{-- hidden id untuk update --}}
            @if(!empty($row['id']))
              <input type="hidden" name="products[{{ $i }}][id]" value="{{ $row['id'] }}">
            @endif

            <div class="col-md-4">
              <label>Part</label>
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
              <label>Kategori Produk</label>
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
              <label>Nama Produk</label>
              <select name="products[{{ $i }}][product_id]" class="form-select product-select"
                      data-index="{{ $i }}" data-current="{{ old("products.$i.product_id", $row['product_id'] ?? '') }}" required>
                <option value="">Pilih Produk</option>
                {{-- diisi via AJAX + preselect via data-current --}}
              </select>
            </div>

            <div class="col-md-1">
              <button type="button" class="btn btn-danger btn-sm remove-product">×</button>
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
          <option value="5" {{ (string)old('warantee_duration', $customer->warantee_duration)==='5' ? 'selected' : '' }}>5 Tahun</option>
          <option value="7" {{ (string)old('warantee_duration', $customer->warantee_duration)==='7' ? 'selected' : '' }}>7 Tahun</option>
        </select>
      </div>

      <div class="col-md-6 mt-3">
        <label class="form-label">Konfirmasi Password Anda <span class="text-danger">*</span></label>
        <input type="password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" required>
        @error('admin_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <button type="submit" class="btn btn-primary mt-5">Simpan Perubahan</button>
    </form>
  </div>
</div>

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>const COMMISSION_FIXED = @json($commissionFixed);</script>

<script>
  // === Panoramic visibility + labels ===
  (function(){
    const selectType = document.getElementById('install_type');
    const panoWrap   = document.getElementById('panoramic_wrap');
    const badge      = document.getElementById('pano_status_badge');
    const hint       = document.getElementById('pano_hint');
    const radioOn    = document.getElementById('pano_on');
    const radioOff   = document.getElementById('pano_off');

    function refreshWrap(){
      const isFullset = (selectType.value === 'fullset');
      panoWrap.classList.toggle('d-none', !isFullset);
      if (!isFullset && radioOff) radioOff.checked = true;
      refreshText();
      window.updatePreview && window.updatePreview();
    }
    function refreshText(){
      const active = radioOn && radioOn.checked;
      if (badge) {
        badge.className = 'badge ' + (active ? 'bg-primary' : 'bg-secondary');
        badge.textContent = active ? 'Panoramic: AKTIF' : 'Panoramic: NONAKTIF';
      }
      if (hint) {
        hint.textContent = active
          ? 'Komisi Fullset mengikuti aturan panoramic.'
          : 'Komisi Fullset reguler (non-panoramic).';
      }
    }

    if (selectType) selectType.addEventListener('change', refreshWrap);
    [radioOn, radioOff].forEach(el => el && el.addEventListener('change', refreshText));

    refreshWrap();
  })();
</script>

<script>
  // === Komisi preview & UI ===
  (function(){
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

    const installTypeEl = document.getElementById('install_type');
    const panoOn        = document.getElementById('pano_on');

    const poolNom  = document.getElementById('pool_nominal');
    const tNom     = document.getElementById('preview_t_nom');
    const kNom     = document.getElementById('preview_k_nom');

    function fmtIdr(n){ return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n||0); }
    function clamp(v){ v=parseInt(v||0,10); return isNaN(v)?0:Math.max(0,Math.min(100,v)); }
    function hasKenek(){ return !!(kenekSelect && kenekSelect.value); }
    function isCustom(){ return !!(shareCustom && shareCustom.checked); }

    function toggleMode(){
      wrapCustom.classList.toggle('d-none', !isCustom());
      updateAll();
    }
    function toggleKenekBlocks(){
      const hk = hasKenek();
      kenekBlocks.forEach(el => el.classList.toggle('d-none', !hk));
      if (!hk) {
        inputK.value = 0; rangeK.value = 0;
        inputT.value = 100; rangeT.value = 100;
      }
      updateAll();
    }

    function normalize(){
      if (!normalizeEl.checked || !hasKenek()) return;
      let t = clamp(inputT.value), k = clamp(inputK.value);
      const s = t + k;
      if (s === 0){ t=100; k=0; }
      else { t = Math.round(t*100/s); k = 100 - t; }
      inputT.value = t; rangeT.value = t;
      inputK.value = k; rangeK.value = k;
    }

    function updateBars(){
      const t = clamp(inputT.value), k = clamp(inputK.value), total = t+k;
      barT.style.width = `${t}%`; barK.style.width = `${k}%`;
      badge.textContent = `Total: ${total}%`;
      badge.classList.toggle('bg-danger', total!==100);
      badge.classList.toggle('bg-primary', total===100);
    }

    function currentKey(){
      const it = installTypeEl.value; if (!it) return null;
      if (it==='skkb' || it==='dsp') return it;
      const pano = !!(panoOn && panoOn.checked);
      return pano ? 'full_panoramic' : 'fullset';
    }
    function calcPool(){
      const row = COMMISSION_FIXED[currentKey()] || {};
      const t = parseInt(row.Teknisi ?? row.teknisi ?? row.tukang ?? 0,10);
      const k = parseInt(row.Kenek   ?? row.kenek   ?? 0,10);
      return (t+k)||0;
    }
    function updatePreview(){
      const pool = calcPool();
      poolNom.textContent = fmtIdr(pool);

      let tPct, kPct;
      if (!isCustom()){
        if (hasKenek()){ tPct=70; kPct=30; } else { tPct=100; kPct=0; }
      } else {
        tPct = clamp(inputT.value);
        kPct = clamp(inputK.value);
        if (normalizeEl.checked && hasKenek()){
          const s=tPct+kPct; if (s>0){ tPct=Math.round(tPct*100/s); kPct=100-tPct; }
        }
        if (!hasKenek()) kPct=0;
      }
      tNom.textContent = fmtIdr(Math.floor(pool*tPct/100));
      kNom.textContent = fmtIdr(Math.floor(pool*kPct/100));
    }

    function updateAll(){
      if (isCustom()) normalize();
      updateBars();
      updatePreview();
    }

    // events
    [shareAuto, shareCustom].forEach(el => el && el.addEventListener('change', toggleMode));
    if (normalizeEl) normalizeEl.addEventListener('change', updateAll);
    if (kenekSelect) kenekSelect.addEventListener('change', toggleKenekBlocks);
    [installTypeEl, panoOn].forEach(el => el && el.addEventListener('change', updatePreview));

    function bindPair(rangeEl, inputEl, otherInput){
      if (!rangeEl || !inputEl) return;
      rangeEl.addEventListener('input', ()=>{
        inputEl.value = rangeEl.value;
        if (isCustom() && normalizeEl.checked && hasKenek() && otherInput){
          const v = 100-parseInt(rangeEl.value,10);
          otherInput.value = v;
          (otherInput===inputK?rangeK:rangeT).value = v;
        }
        updateAll();
      });
      inputEl.addEventListener('input', ()=>{
        let v = clamp(inputEl.value);
        inputEl.value = v; rangeEl.value = v; updateAll();
      });
    }
    bindPair(rangeT, inputT, inputK);
    bindPair(rangeK, inputK, inputT);

    // inits
    toggleKenekBlocks();
    if (!isCustom()){
      inputT.value = 70; rangeT.value = 70;
      inputK.value = 30; rangeK.value = 30;
    }
    toggleMode();
    window.updatePreview = updatePreview; // dipakai handler lain
  })();
</script>

<script>
  // === Produk: tambah/hapus + AJAX produk by category ===
  let productIndex = {{ count($prefillProducts) }};

  document.getElementById('add-product').addEventListener('click', function () {
    const container = document.getElementById('product-container');
    const template  = container.querySelector('.product-item');
    const clone     = template.cloneNode(true);

    // bersihkan input hidden id agar menjadi row baru
    const hid = clone.querySelector('input[type="hidden"][name^="products"][name$="[id]"]');
    if (hid) hid.remove();

    clone.querySelectorAll('select').forEach(el => {
      const oldName = el.getAttribute('name'); // products[0][part_id]
      el.setAttribute('name', oldName.replace(/\[\d+\]/, `[${productIndex}]`));
      if (el.classList.contains('category-select') || el.classList.contains('product-select')) {
        el.setAttribute('data-index', productIndex);
      }
      if (el.classList.contains('product-select')) {
        el.innerHTML = '<option value="">Pilih Produk</option>';
      }
      el.selectedIndex = 0;
    });

    container.appendChild(clone);
    productIndex++;
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-product')) {
      const item = e.target.closest('.product-item');
      if (document.querySelectorAll('.product-item').length > 1) item.remove();
    }
  });

  // ajax isi produk
  $(document).on('change', '.category-select', function () {
    const categoryId = $(this).val();
    const index = $(this).data('index');
    const $product = $(`select[name="products[${index}][product_id]"]`);
    const current  = $product.data('current') || '';

    $product.html('<option value="">Memuat...</option>');
    if (!categoryId) return $product.html('<option value="">Pilih Produk</option>');

    $.get(`{{ url('products-by-category') }}/${categoryId}`)
      .done(function (data) {
        let options = '<option value="">Pilih Produk</option>';
        data.forEach(p => options += `<option value="${p.id}">${p.name}</option>`);
        $product.html(options);

        if (current) $product.val(current);
      })
      .fail(function () {
        alert('Gagal mengambil produk.');
        $product.html('<option value="">Pilih Produk</option>');
      });
  });

  // preload untuk semua baris awal
  $(function(){
    $('.category-select').each(function(){
      if ($(this).val()) $(this).trigger('change');
    });
  });
</script>
@endsection
