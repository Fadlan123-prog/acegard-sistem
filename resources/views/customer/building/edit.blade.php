@extends('dashboard.index')

@section('title', 'Edit Customer Gedung — ' . $customer->name)
@section('route-breadcrumb', 'customer.building.index')
@section('breadcrumb', 'Customer Gedung')

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
.cust-nav li a.is-active { background: var(--primary-light); color: var(--primary-600); }
.cust-nav li a:hover .cn-icon,
.cust-nav li a.is-active .cn-icon { background: var(--primary-600); color: #fff; }

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
  font-size: 18px; flex-shrink: 0;
}
.cust-section-head .sec-icon.sec-icon-danger { background: rgba(239,68,68,.12); color: #ef4444; }
.cust-section-head .sec-icon.sec-icon-store  { background: rgba(139,92,246,.12); color: #7c3aed; }
.cust-section-head .sec-title { font-size: 14px; font-weight: 600; margin: 0; color: var(--neutral-900); }
.cust-section-head .sec-desc  { font-size: 12px; color: var(--neutral-500); margin: 0; }
.cust-section-body { padding: 20px; }

.fi-wrap {
  display: flex; align-items: stretch;
  border: 1px solid var(--input-stroke, var(--neutral-300));
  border-radius: 8px; overflow: hidden;
  background: var(--input-bg, var(--neutral-50));
  transition: border-color .15s, box-shadow .15s;
}
.fi-wrap:focus-within { border-color: var(--primary-600); box-shadow: 0 0 0 3px rgba(72,127,255,.12); }
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
.fi-wrap input[type="tel"] {
  flex: 1; border: none; outline: none; background: transparent;
  padding: 8px 12px; font-size: 14px; color: var(--neutral-900); min-width: 0;
}
.fi-wrap input[type="hidden"] { display: none; }

.cust-action-bar {
  position: sticky; bottom: 0; z-index: 100;
  background: var(--white);
  border-top: 1px solid var(--border-color);
  padding: 14px 20px;
  display: flex; align-items: center; justify-content: flex-end; gap: 10px;
  margin-top: -1px;
}
.prod-row-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--neutral-500); margin-bottom: 4px; }

[data-theme="dark"] .cust-section,
[data-theme="dark"] .cust-nav,
[data-theme="dark"] .cust-action-bar { background: var(--dark-2); }
[data-theme="dark"] .cust-section-head { background: var(--dark-3); }
[data-theme="dark"] .cust-nav li a    { color: var(--neutral-400); }
[data-theme="dark"] .cust-nav li a .cn-icon { background: var(--dark-3); color: var(--neutral-400); }
[data-theme="dark"] .fi-wrap   { background: var(--dark-3); border-color: var(--dark-1); }
[data-theme="dark"] .fi-badge  { background: var(--dark-1); border-color: var(--dark-1); color: var(--neutral-300); }
[data-theme="dark"] .fi-wrap input { color: var(--neutral-100); }
[data-theme="dark"] .cust-section-head .sec-title { color: var(--neutral-100); }
</style>
@endpush

@section('content')

@if ($errors->any())
  <x-ui.alert type="danger" class="mb-3">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
  </x-ui.alert>
@endif
@if (session('error'))
  <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

@php
  // Prefill: prioritaskan old() flash (validasi gagal), fallback ke DB
  if (!empty(old('products'))) {
    $prefillProducts = collect(old('products'))->values()->map(fn($p) => [
      'id'                           => $p['id'] ?? null,
      'category_product_building_id' => $p['category_product_building_id'] ?? '',
      'product_building_id'          => $p['product_building_id'] ?? '',
      'warantee_duration'            => $p['warantee_duration'] ?? '',
      'meters'                       => $p['meters'] ?? '',
    ])->toArray();
  } else {
    $prefillProducts = $customer->products->map(fn($p) => [
      'id'                           => $p->id,
      'category_product_building_id' => $p->category_product_building_id,
      'product_building_id'          => $p->product_building_id,
      'warantee_duration'            => $p->warantee_duration,
      'meters'                       => $p->meters,
    ])->values()->toArray();
  }
  if (empty($prefillProducts)) {
    $prefillProducts = [['id' => null, 'category_product_building_id' => null, 'product_building_id' => null, 'warantee_duration' => null, 'meters' => null]];
  }
@endphp

<div class="cust-layout">

  {{-- Sidebar --}}
  <ul class="cust-nav" id="custNav">
    <li><a href="#sec-identitas" class="is-active">
      <span class="cn-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></span> Identitas
    </a></li>
    <li><a href="#sec-dealer">
      <span class="cn-icon"><iconify-icon icon="mdi:store-outline"></iconify-icon></span> Dealer
    </a></li>
    <li><a href="#sec-produk">
      <span class="cn-icon"><iconify-icon icon="mdi:office-building-outline"></iconify-icon></span> Produk
    </a></li>
    <li><a href="#sec-keamanan">
      <span class="cn-icon"><iconify-icon icon="mdi:lock-outline"></iconify-icon></span> Keamanan
    </a></li>
  </ul>

  <div>
    <form action="{{ route('customer.building.update', $customer->id) }}" method="POST">
      @csrf
      @method('PUT')

      {{-- ── Section 1: Identitas ── --}}
      <div id="sec-identitas" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Identitas Customer</p>
            <p class="sec-desc">Data pribadi dan kontak pemilik / pengelola gedung</p>
          </div>
        </div>
        <div class="cust-section-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Warranty Serial Number <span class="text-danger">*</span></label>
              <input type="text" name="wsn" class="form-control"
                     value="{{ old('wsn', $customer->wsn) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nomor Kartu <span class="text-danger">*</span></label>
              <input type="text" name="card_number" class="form-control"
                     value="{{ old('card_number', $customer->card_number) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control"
                     value="{{ old('name', $customer->name) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
              <div class="fi-wrap">
                <span class="fi-badge"><span class="wa-dot"></span>+62</span>
                <input type="tel" id="phone_display" placeholder="812 3456 7890" autocomplete="off">
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
              <label class="form-label">Alamat Gedung</label>
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

      {{-- ── Section 2: Dealer & Aplikator ── --}}
      <div id="sec-dealer" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon sec-icon-store"><iconify-icon icon="mdi:store-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Dealer & Aplikator</p>
            <p class="sec-desc">Sumber referensi dan pihak yang mengaplikasikan film</p>
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
              <label class="form-label">Aplikator</label>
              <input type="text" name="applicator" class="form-control"
                     value="{{ old('applicator', $customer->applicator) }}">
            </div>
          </div>
        </div>
      </div>

      {{-- ── Section 3: Produk ── --}}
      <div id="sec-produk" class="cust-section">
        <div class="cust-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:office-building-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Produk Gedung</p>
            <p class="sec-desc">Film yang dipasang beserta luas area dan garansi per produk</p>
          </div>
        </div>
        <div class="cust-section-body">

          <div class="row mb-1 d-none d-md-flex px-2">
            <div class="col-md-3"><span class="prod-row-label">Kategori</span></div>
            <div class="col-md-3"><span class="prod-row-label">Produk</span></div>
            <div class="col-md-3"><span class="prod-row-label">Durasi Garansi</span></div>
            <div class="col-md-2"><span class="prod-row-label">Ukuran (m²)</span></div>
          </div>

          <div id="product-container">
            @foreach($prefillProducts as $i => $row)
            <div class="row align-items-end mb-3 product-item" data-index="{{ $i }}">
              @if(!empty($row['id']))
                <input type="hidden" name="products[{{ $i }}][id]" value="{{ $row['id'] }}">
              @endif

              <div class="col-md-3">
                <label class="form-label d-md-none small">Kategori</label>
                <select name="products[{{ $i }}][category_product_building_id]"
                        class="form-select category-select" data-index="{{ $i }}" required>
                  <option value="">Pilih Kategori</option>
                  @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                      {{ (string)$cat->id === (string)($row['category_product_building_id'] ?? '') ? 'selected' : '' }}>
                      {{ $cat->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label d-md-none small">Produk</label>
                <select name="products[{{ $i }}][product_building_id]"
                        class="form-select product-select"
                        data-index="{{ $i }}"
                        data-current="{{ $row['product_building_id'] ?? '' }}" required>
                  <option value="">Pilih Produk</option>
                  {{-- Pre-populated via AJAX on load (triggered by category-select initial value) --}}
                  @if(!empty($row['product_building_id']) && !empty($row['category_product_building_id']))
                    @foreach($products->where('category_product_building_id', $row['category_product_building_id']) as $prod)
                      <option value="{{ $prod->id }}"
                        {{ (string)$prod->id === (string)$row['product_building_id'] ? 'selected' : '' }}>
                        {{ $prod->name }}
                      </option>
                    @endforeach
                  @endif
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label d-md-none small">Durasi Garansi</label>
                <select name="products[{{ $i }}][warantee_duration]" class="form-select" required>
                  <option value="">Pilih Durasi</option>
                  <option value="0" {{ (string)($row['warantee_duration'] ?? '') === '0' ? 'selected' : '' }}>Tidak Ada Garansi</option>
                  <option value="3" {{ (string)($row['warantee_duration'] ?? '') === '3' ? 'selected' : '' }}>3 Tahun</option>
                  <option value="5" {{ (string)($row['warantee_duration'] ?? '') === '5' ? 'selected' : '' }}>5 Tahun</option>
                  <option value="7" {{ (string)($row['warantee_duration'] ?? '') === '7' ? 'selected' : '' }}>7 Tahun</option>
                </select>
              </div>

              <div class="col-md-2">
                <label class="form-label d-md-none small">Ukuran (m²)</label>
                <input type="number" step="0.01" min="0.01"
                       name="products[{{ $i }}][meters]" class="form-control"
                       value="{{ $row['meters'] ?? '' }}" placeholder="0.00" required>
              </div>

              <div class="col-md-1">
                <x-ui.button type="button" variant="danger-outline" size="sm" class="remove-product w-100">
                  <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                </x-ui.button>
              </div>
            </div>
            @endforeach
          </div>

          <x-ui.button type="button" variant="outline" size="sm" id="add-product">
            <iconify-icon icon="mdi:plus"></iconify-icon> Tambah Produk
          </x-ui.button>

        </div>
      </div>

      {{-- ── Section 4: Keamanan + Action Bar ── --}}
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
              @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
        <div class="cust-action-bar">
          <x-ui.button href="{{ route('customer.building.index') }}" variant="outline">Batal</x-ui.button>
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
<script>
(function () {

  // ── Phone formatting (normalise +62 / 62 / 0 prefix dari DB) ──
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
      const a = d.slice(0,3), b = d.slice(3,7), c = d.slice(7,11);
      return [a,b,c].filter(Boolean).join(' ');
    }
    const init = strip(hidden.value || '');
    display.value = fmt(init);
    if (init) hidden.value = '+62' + init;
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

  // ── Product AJAX ──
  let productIndex = {{ count($prefillProducts) }};

  // Kategori berubah → reload produk (reset data-current supaya tidak auto-select lama)
  $(document).on('change', '.category-select', function () {
    const catId = $(this).val();
    const idx   = $(this).data('index');
    const $prod = $(`select[name="products[${idx}][product_building_id]"]`);
    $prod.attr('data-current', '').html('<option value="">Memuat...</option>');
    if (!catId) { $prod.html('<option value="">Pilih Produk</option>'); return; }
    $.get(`{{ url('products-by-category-building') }}/${catId}`)
      .done(function (data) {
        let opts = '<option value="">Pilih Produk</option>';
        data.forEach(p => opts += `<option value="${p.id}">${p.name}</option>`);
        $prod.html(opts);
      })
      .fail(function () { $prod.html('<option value="">Pilih Produk</option>'); });
  });

  // Tambah baris produk baru (clone dari baris pertama, bersihkan semua)
  document.getElementById('add-product')?.addEventListener('click', function () {
    const container = document.getElementById('product-container');
    const clone     = container.querySelector('.product-item').cloneNode(true);
    clone.setAttribute('data-index', productIndex);
    // hapus hidden id jika ada
    clone.querySelector('input[type="hidden"][name$="[id]"]')?.remove();
    clone.querySelectorAll('select, input').forEach(el => {
      const name = el.getAttribute('name');
      if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${productIndex}]`));
      if (el.classList.contains('category-select') || el.classList.contains('product-select')) {
        el.setAttribute('data-index', productIndex);
      }
      if (el.tagName === 'SELECT') {
        el.selectedIndex = 0;
        if (el.classList.contains('product-select')) {
          el.setAttribute('data-current', '');
          el.innerHTML = '<option value="">Pilih Produk</option>';
        }
      } else {
        el.value = '';
      }
    });
    container.appendChild(clone);
    productIndex++;
  });

  // Hapus baris produk
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
