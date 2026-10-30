@php
    $isEdit        = isset($quotation);
    $existingItems = $isEdit ? $quotation->items : collect();
    $lead          = $lead ?? ($quotation->lead ?? null);
@endphp

@push('page-styles')
<style>
.cust-layout { display:grid; grid-template-columns:220px 1fr; gap:24px; align-items:start; }
@media(max-width:768px){ .cust-layout{grid-template-columns:1fr;} .cust-nav{display:none;} }

.cust-nav { position:sticky; top:80px; background:var(--white); border:1px solid var(--border-color); border-radius:12px; padding:8px; list-style:none; margin:0; }
.cust-nav li a { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:8px; font-size:13px; font-weight:500; color:var(--neutral-600); text-decoration:none; transition:background .15s,color .15s; }
.cust-nav li a .cn-icon { width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:14px; background:var(--neutral-100); color:var(--neutral-500); flex-shrink:0; transition:background .15s,color .15s; }
.cust-nav li a:hover,.cust-nav li a.is-active { background:var(--primary-light); color:var(--primary-600); }
.cust-nav li a:hover .cn-icon,.cust-nav li a.is-active .cn-icon { background:var(--primary-600); color:#fff; }

.cust-section { background:var(--white); border:1px solid var(--border-color); border-radius:12px; overflow:hidden; margin-bottom:20px; }
.cust-section-head { display:flex; align-items:center; gap:14px; padding:16px 20px; background:var(--neutral-50); border-bottom:1px solid var(--border-color); }
.cust-section-head .sec-icon { width:38px; height:38px; border-radius:9px; background:var(--primary-light); color:var(--primary-600); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.cust-section-head .sec-title { font-size:14px; font-weight:600; margin:0; color:var(--neutral-900); }
.cust-section-head .sec-desc  { font-size:12px; color:var(--neutral-500); margin:0; }
.cust-section-body { padding:20px; }

.cust-action-bar { position:sticky; bottom:0; z-index:100; background:var(--white); border-top:1px solid var(--border-color); padding:14px 20px; display:flex; align-items:center; justify-content:flex-end; gap:10px; }

/* Items table */
.items-tbl th { font-size:12px; font-weight:600; color:var(--neutral-500); background:var(--neutral-50); }
.items-tbl td { vertical-align:middle; }
.item-subtotal-cell { font-weight:600; color:var(--primary-600); }

[data-theme="dark"] .cust-section,[data-theme="dark"] .cust-action-bar { background:var(--dark-2); }
[data-theme="dark"] .cust-section-head { background:var(--dark-3); }
[data-theme="dark"] .cust-nav { background:var(--dark-2); }
[data-theme="dark"] .cust-nav li a { color:var(--neutral-400); }
[data-theme="dark"] .cust-nav li a .cn-icon { background:var(--dark-3); color:var(--neutral-400); }
[data-theme="dark"] .cust-section-head .sec-title { color:var(--neutral-100); }
[data-theme="dark"] .items-tbl th { background:var(--dark-3); }
</style>
@endpush

@include('partials.form-errors')

<div class="cust-layout">

    {{-- Sidebar Nav --}}
    <ul class="cust-nav" id="quoteNav">
        <li><a href="#sec-info" class="is-active">
            <span class="cn-icon"><iconify-icon icon="mdi:file-document-outline"></iconify-icon></span> Info Quotation
        </a></li>
        <li><a href="#sec-prospek">
            <span class="cn-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></span> Data Prospek
        </a></li>
        <li><a href="#sec-items">
            <span class="cn-icon"><iconify-icon icon="mdi:format-list-checks"></iconify-icon></span> Rincian Harga
        </a></li>
        <li><a href="#sec-catatan">
            <span class="cn-icon"><iconify-icon icon="mdi:note-text-outline"></iconify-icon></span> Catatan & S&K
        </a></li>
    </ul>

    {{-- Main Content --}}
    <div>

        {{-- Lead Banner --}}
        @if($lead)
        <x-ui.alert type="info" class="d-flex align-items-center gap-2 mb-3 py-2">
            <iconify-icon icon="mdi:target" class="fs-5"></iconify-icon>
            <span>Dari Lead: <strong>{{ $lead->name }}</strong>
                — <x-ui.badge variant="info" size="sm">{{ $lead->category_label }}</x-ui.badge>
            </span>
            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
        </x-ui.alert>
        @endif

        {{-- Section 1: Info Quotation --}}
        <div id="sec-info" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:file-document-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Informasi Quotation</p>
                    <p class="sec-desc">Tanggal, validitas, dan kategori layanan</p>
                </div>
            </div>
            <div class="cust-section-body">

                @if(!$isEdit && auth()->user()->hasRole('superadmin') && isset($branches) && $branches->isNotEmpty())
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <x-ui.form-field label="Cabang" name="branch_id" :required="true" :error="$errors->first('branch_id')">
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-3">
                        <x-ui.form-field label="Tanggal" name="date" :required="true" :error="$errors->first('date')">
                            <input type="date" name="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', $isEdit ? $quotation->date->format('Y-m-d') : now()->toDateString()) }}" required>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Berlaku s/d" name="valid_until" :required="true" :error="$errors->first('valid_until')"
                                         help="Tanggal kedaluwarsa penawaran ini">
                            <input type="date" name="valid_until"
                                   class="form-control @error('valid_until') is-invalid @enderror"
                                   value="{{ old('valid_until', $isEdit ? $quotation->valid_until->format('Y-m-d') : now()->addDays(14)->toDateString()) }}" required>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Kategori" name="category" :required="true" :error="$errors->first('category')">
                            <select name="category" id="categorySelect"
                                    class="form-select @error('category') is-invalid @enderror" required>
                                @foreach(\App\Models\Quotation::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('category', $isEdit ? $quotation->category : ($lead->category ?? 'car_film')) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Marketing / PIC" name="assigned_to">
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Tidak ditugaskan --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ old('assigned_to', $isEdit ? $quotation->assigned_to : '') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                </div>

            </div>
        </div>

        {{-- Section 2: Data Prospek --}}
        <div id="sec-prospek" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Data Prospek</p>
                    <p class="sec-desc">Informasi calon customer yang menerima penawaran ini</p>
                </div>
            </div>
            <div class="cust-section-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <x-ui.form-field label="Nama Prospek" name="prospect_name" :required="true" :error="$errors->first('prospect_name')">
                            <input type="text" name="prospect_name"
                                   class="form-control @error('prospect_name') is-invalid @enderror"
                                   value="{{ old('prospect_name', $isEdit ? $quotation->prospect_name : ($lead->name ?? '')) }}"
                                   placeholder="Nama lengkap" required>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Telepon" name="prospect_phone">
                            <input type="text" name="prospect_phone" class="form-control"
                                   value="{{ old('prospect_phone', $isEdit ? $quotation->prospect_phone : ($lead->phone ?? '')) }}"
                                   placeholder="08xx-xxxx-xxxx">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-4">
                        <x-ui.form-field label="Email" name="prospect_email">
                            <input type="email" name="prospect_email" class="form-control"
                                   value="{{ old('prospect_email', $isEdit ? $quotation->prospect_email : ($lead->email ?? '')) }}"
                                   placeholder="email@contoh.com">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-6">
                        <x-ui.form-field label="Alamat" name="prospect_address">
                            <input type="text" name="prospect_address" class="form-control"
                                   value="{{ old('prospect_address', $isEdit ? $quotation->prospect_address : ($lead->address ?? '')) }}"
                                   placeholder="Alamat lengkap">
                        </x-ui.form-field>
                    </div>

                    {{-- Vehicle fields --}}
                    <div id="vehicleFields" class="col-12">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <x-ui.form-field label="Merek Kendaraan" name="vehicle_brand">
                                    <input type="text" name="vehicle_brand" class="form-control"
                                           value="{{ old('vehicle_brand', $isEdit ? $quotation->vehicle_brand : ($lead->vehicle_brand ?? '')) }}"
                                           placeholder="Toyota...">
                                </x-ui.form-field>
                            </div>
                            <div class="col-md-3">
                                <x-ui.form-field label="Model" name="vehicle_model">
                                    <input type="text" name="vehicle_model" class="form-control"
                                           value="{{ old('vehicle_model', $isEdit ? $quotation->vehicle_model : ($lead->vehicle_model ?? '')) }}"
                                           placeholder="Avanza...">
                                </x-ui.form-field>
                            </div>
                            <div class="col-md-2">
                                <x-ui.form-field label="Tahun" name="vehicle_year">
                                    <input type="text" name="vehicle_year" class="form-control"
                                           value="{{ old('vehicle_year', $isEdit ? $quotation->vehicle_year : ($lead->vehicle_year ?? '')) }}"
                                           placeholder="{{ date('Y') }}">
                                </x-ui.form-field>
                            </div>
                            <div class="col-md-2">
                                <x-ui.form-field label="Plat Nomor" name="vehicle_plate">
                                    <input type="text" name="vehicle_plate" class="form-control"
                                           value="{{ old('vehicle_plate', $isEdit ? $quotation->vehicle_plate : '') }}"
                                           placeholder="BH 1234 AB">
                                </x-ui.form-field>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Rincian Harga (Items) --}}
        <div id="sec-items" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:format-list-checks"></iconify-icon></div>
                <div>
                    <p class="sec-title">Rincian Harga</p>
                    <p class="sec-desc">Daftar produk/layanan beserta harga satuan</p>
                </div>
            </div>
            <div class="cust-section-body">

                <div class="table-responsive mb-3">
                    <table class="table table-bordered items-tbl mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="min-width:200px" id="thDesc">Deskripsi / Produk</th>
                                <th style="min-width:150px" id="thSpec">Spesifikasi</th>
                                <th style="width:80px">Satuan</th>
                                <th style="width:70px">Qty</th>
                                <th style="width:130px">Harga Satuan (Rp)</th>
                                <th style="width:70px">Disc%</th>
                                <th style="width:130px" class="text-end">Subtotal (Rp)</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>

                <x-ui.button type="button" variant="outline" size="sm" class="mb-4" id="addRowBtn">
                    <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Tambah Baris
                </x-ui.button>

                {{-- Totals --}}
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted border-0">Subtotal</td>
                                <td class="text-end fw-semibold border-0" id="displaySubtotal">Rp 0</td>
                            </tr>
                            <tr>
                                <td class="border-0">Diskon (Rp)</td>
                                <td class="text-end border-0" style="width:150px">
                                    <input type="number" name="discount_amount" id="discountInput"
                                           class="form-control form-control-sm text-end"
                                           value="{{ old('discount_amount', $isEdit ? $quotation->discount_amount : 0) }}"
                                           min="0" step="1000">
                                </td>
                            </tr>
                            <tr class="table-light">
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold text-success" id="displayTotal">Rp 0</td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- Section 4: Catatan & S&K --}}
        <div id="sec-catatan" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:note-text-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Catatan & Syarat Ketentuan</p>
                    <p class="sec-desc">Informasi tambahan yang akan muncul di dokumen quotation</p>
                </div>
            </div>
            <div class="cust-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-ui.form-field label="Catatan" name="notes">
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Catatan tambahan...">{{ old('notes', $isEdit ? $quotation->notes : '') }}</textarea>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-6">
                        <x-ui.form-field label="Syarat & Ketentuan" name="terms">
                            <textarea name="terms" class="form-control" rows="3"
                                      placeholder="Syarat & ketentuan...">{{ old('terms', $isEdit ? $quotation->terms : \App\Models\Quotation::DEFAULT_TERMS) }}</textarea>
                        </x-ui.form-field>
                    </div>
                </div>
            </div>
            <div class="cust-action-bar">
                <x-ui.button :href="$isEdit ? route('quotations.show', $quotation) : route('quotations.index')" variant="outline">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="px-4">
                    <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>
                    {{ $isEdit ? 'Perbarui Quotation' : 'Simpan Quotation' }}
                </x-ui.button>
            </div>
        </div>

    </div>
</div>

{{-- Row template --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td class="desc-td">
            {{-- Mobil: satu paket = daftar bagian kaca (Part / Kategori / Produk) dengan satu harga --}}
            <div class="car-pack" hidden>
                <input type="text" name="items[__IDX__][description]" class="form-control form-control-sm pack-title" placeholder="Nama paket (mis. Fullset, D/S/P, Kaca Depan)" disabled>
                <div class="pack-lines mt-2"></div>
                <button type="button" class="btn btn-outline-primary btn-sm add-line">+ Tambah Produk</button>
                <div class="form-text car-summary"></div>
            </div>
            {{-- Gedung/PPF: pilih dari katalog produk atau "Lainnya" untuk mengetik sendiri --}}
            <div class="generic-desc">
                <select class="form-select form-select-sm desc-picker" aria-label="Pilih produk"></select>
                <input type="text" name="items[__IDX__][description]" class="form-control form-control-sm mt-1 desc-input" placeholder="Nama produk / layanan" hidden>
            </div>
        </td>
        <td class="spec-td">
            <input type="text" name="items[__IDX__][specifications]" class="form-control form-control-sm spec-input generic-spec" placeholder="Otomatis dari produk, bisa diubah">
        </td>
        <td>
            <select name="items[__IDX__][unit]" class="form-select form-select-sm">
                <option value="unit">unit</option>
                <option value="m2">m²</option>
                <option value="pcs">pcs</option>
                <option value="paket">paket</option>
                <option value="meter">meter</option>
            </select>
        </td>
        <td><input type="number" name="items[__IDX__][quantity]" class="form-control form-control-sm item-qty" value="1" min="0.01" step="0.01" required></td>
        <td><input type="number" name="items[__IDX__][unit_price]" class="form-control form-control-sm item-price" value="0" min="0" step="1000" required></td>
        <td><input type="number" name="items[__IDX__][discount_percent]" class="form-control form-control-sm item-disc" value="0" min="0" max="100" step="0.5"></td>
        <td class="text-end item-subtotal-cell align-middle"><span class="item-subtotal">Rp 0</span></td>
        <td class="text-center align-middle">
            <button type="button" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0 remove-row-btn" title="Hapus">
                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
            </button>
        </td>
    </tr>
</template>

@push('page-scripts')
<script>
(function(){
    let rowIdx = 0;
    const body   = document.getElementById('itemsBody');
    const tmpl   = document.getElementById('itemRowTemplate');
    const discIn = document.getElementById('discountInput');

    function fmt(n){ return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

    function rowSub(row){
        const qty   = parseFloat(row.querySelector('.item-qty').value)   || 0;
        const price = parseFloat(row.querySelector('.item-price').value)  || 0;
        const disc  = parseFloat(row.querySelector('.item-disc').value)   || 0;
        return qty * price * (1 - disc / 100);
    }

    function recalc(){
        let sub = 0;
        body.querySelectorAll('.item-row').forEach(row => {
            const s = rowSub(row);
            sub += s;
            row.querySelector('.item-subtotal').textContent = fmt(s);
        });
        const disc = parseFloat(discIn.value) || 0;
        document.getElementById('displaySubtotal').textContent = fmt(sub);
        document.getElementById('displayTotal').textContent    = fmt(sub - disc);
    }

    // Katalog produk per kategori quotation: [{group, items:[{label, spec}]}]
    const CATALOG = {!! \Illuminate\Support\Js::from((new \App\Services\QuotationCatalog)->build()) !!};
    const MANUAL = '__manual__';

    function currentCatalog(){
        const cat = document.getElementById('categorySelect')?.value || 'car_film';
        return CATALOG[cat] || [];
    }

    function buildPicker(select, selectedLabel){
        select.innerHTML = '';
        select.add(new Option('— Pilih produk —', ''));
        currentCatalog().forEach(g => {
            const og = document.createElement('optgroup'); og.label = g.group;
            g.items.forEach(it => { const o = new Option(it.label, it.label); o.dataset.spec = it.spec || ''; og.appendChild(o); });
            select.appendChild(og);
        });
        select.add(new Option('Lainnya (ketik manual)…', MANUAL));
        if (selectedLabel) {
            const has = Array.from(select.options).some(o => o.value === selectedLabel);
            select.value = has ? selectedLabel : MANUAL;
        }
    }

    function syncPicker(row, keepSpec){
        const picker = row.querySelector('.desc-picker');
        const desc = row.querySelector('.desc-input');
        const spec = row.querySelector('.spec-input');
        picker.required = true;   // yang divalidasi browser adalah select yang terlihat
        if (picker.value === MANUAL) {
            desc.hidden = false; desc.required = true;
            if (Array.from(picker.options).some(o => o.value === desc.value)) desc.value = '';
        } else {
            desc.hidden = true; desc.required = false;
            desc.value = picker.value;
            const opt = picker.selectedOptions[0];
            if (!keepSpec && opt && opt.dataset.spec !== undefined && picker.value) spec.value = opt.dataset.spec;
        }
    }

    // ── Mobil: paket = daftar bagian kaca (Part / Kategori / Produk) seperti form customer, satu harga ──
    const CAR = {!! \Illuminate\Support\Js::from((new \App\Services\QuotationCatalog)->carStructured()) !!};
    function isCar(){ return (document.getElementById('categorySelect')?.value || 'car_film') === 'car_film'; }

    function opts(list, ph, labelKey){ return '<option value="">' + ph + '</option>' + list.map(x => '<option value="' + x.id + '">' + (x[labelKey || 'label'] || x.name) + '</option>').join(''); }

    function fillProducts(prodSel, typeId, selected){
        const t = CAR.types.find(x => String(x.id) === String(typeId));
        prodSel.innerHTML = opts(t ? t.products : [], '— Produk —');
        if (selected) prodSel.value = String(selected);
    }

    function addLine(row, line){
        const idx = row.dataset.idx, k = row._lineIdx = (row._lineIdx || 0) + 1;
        const div = document.createElement('div');
        div.className = 'row g-2 align-items-end mb-2 pack-line';
        div.innerHTML =
            '<div class="col-md-4"><label class="form-label small mb-1">Part</label><select name="items['+idx+'][film_lines]['+k+'][part_id]" class="form-select form-select-sm line-part">' + opts(CAR.parts, '— Part —') + '</select></div>' +
            '<div class="col-md-4"><label class="form-label small mb-1">Kategori</label><select name="items['+idx+'][film_lines]['+k+'][category_id]" class="form-select form-select-sm line-type">' + opts(CAR.types, '— Kategori —', 'name') + '</select></div>' +
            '<div class="col-md-3"><label class="form-label small mb-1">Produk</label><select name="items['+idx+'][film_lines]['+k+'][product_id]" class="form-select form-select-sm line-product"><option value="">— Produk —</option></select></div>' +
            '<div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-line" title="Hapus bagian">&times;</button></div>';
        row.querySelector('.pack-lines').appendChild(div);

        const part = div.querySelector('.line-part'), type = div.querySelector('.line-type'), prod = div.querySelector('.line-product');
        if (line) { part.value = String(line.part_id || ''); type.value = String(line.category_id || ''); fillProducts(prod, type.value, line.product_id); }
        type.addEventListener('change', () => { fillProducts(prod, type.value); updateCarSummary(row); });
        [part, prod].forEach(el => el.addEventListener('change', () => updateCarSummary(row)));
        div.querySelector('.remove-line').addEventListener('click', () => {
            if (row.querySelectorAll('.pack-line').length > 1) { div.remove(); updateCarSummary(row); }
        });
        applyLineMode(div);
        updateCarSummary(row);
    }

    function applyLineMode(div){
        const car = isCar();
        div.querySelectorAll('select').forEach(sel => { sel.disabled = !car; sel.required = car; });
    }

    function buildCarRow(row, data, idx){
        row.dataset.idx = idx;
        row.querySelector('.add-line').addEventListener('click', () => addLine(row));
        const lines = (data && data.film_lines) ? data.film_lines : [];
        if (lines.length) { lines.forEach(l => addLine(row, l)); } else { addLine(row); }
        // Quotation mobil lama (sebelum ada rincian bagian): tampilkan isi sebelumnya sebagai acuan.
        if (data && !(data.film_lines && data.film_lines.length) && data.description) {
            row.querySelector('.pack-title').value = data.description;
            row.querySelector('.car-summary').dataset.legacy = 'Sebelumnya: ' + data.description + (data.specifications ? ' · ' + data.specifications : '') + ' — rinci ulang bagian kaca.';
        } else if (data && data.description) {
            row.querySelector('.pack-title').value = data.description;
        }
        updateCarSummary(row);
    }

    function updateCarSummary(row){
        const el = row.querySelector('.car-summary');
        if (!el) return;
        const lines = Array.from(row.querySelectorAll('.pack-line')).map(l => {
            const part = l.querySelector('.line-part'), type = l.querySelector('.line-type'), prod = l.querySelector('.line-product');
            if (!part.value) return null;
            return part.selectedOptions[0].text + (type.value ? ' · ' + type.selectedOptions[0].text : '') + (prod.value ? ' ' + prod.selectedOptions[0].text : '');
        }).filter(Boolean);
        el.textContent = lines.length ? lines.join('  |  ') : (el.dataset.legacy || '');
    }

    function applyMode(row){
        const car = isCar();
        row.querySelector('.car-pack').hidden = !car;
        row.querySelector('.pack-title').disabled = !car;
        row.querySelector('.generic-desc').hidden = car;
        const gspec = row.querySelector('.generic-spec'); gspec.disabled = car;
        row.querySelector('.spec-td').hidden = car;
        row.querySelector('.desc-td').colSpan = car ? 2 : 1;
        row.querySelector('.desc-picker').disabled = car;
        row.querySelector('.desc-picker').required = !car;
        const desc = row.querySelector('.desc-input'); desc.disabled = car;
        if (car) { desc.required = false; } else { syncPicker(row, true); }
        row.querySelectorAll('.pack-line').forEach(applyLineMode);
    }

    function applyModeAll(){
        const car = isCar();
        const th = document.getElementById('thDesc'), ts = document.getElementById('thSpec');
        th.textContent = car ? 'Paket Kaca Film (Part / Kategori / Produk)' : 'Deskripsi / Produk';
        th.colSpan = car ? 2 : 1;
        ts.hidden = car; ts.textContent = 'Spesifikasi';
        body.querySelectorAll('.item-row').forEach(applyMode);
    }

    function addRow(data){
        const html = tmpl.innerHTML.replace(/__IDX__/g, rowIdx++);
        body.insertAdjacentHTML('beforeend', html);
        const row = body.lastElementChild;
        buildPicker(row.querySelector('.desc-picker'), data ? (data.description || '') : '');
        row.querySelector('.desc-picker').addEventListener('change', () => { syncPicker(row, false); recalc(); });
        buildCarRow(row, data, rowIdx - 1);

        if(data){
            row.querySelector('.desc-input').value                 = data.description     || '';
            row.querySelector('[name$="[specifications]"]').value = data.specifications  || '';
            row.querySelector('[name$="[unit]"]').value           = data.unit            || 'unit';
            row.querySelector('.item-qty').value                  = data.quantity        || 1;
            row.querySelector('.item-price').value                = data.unit_price      || 0;
            row.querySelector('.item-disc').value                 = data.discount_percent|| 0;
        }
        syncPicker(row, !!data);
        applyMode(row);

        row.querySelectorAll('input,select').forEach(el => el.addEventListener('input', recalc));
        row.querySelector('.remove-row-btn').addEventListener('click', () => { row.remove(); recalc(); });
        recalc();
    }

    // Seed existing items (edit mode)
    // Js::from() (not ->toJson() wrapped in a raw Blade echo) HEX-escapes <, >, &, ' so a
    // description/specifications value containing a closing script tag can't break
    // out of this inline script block (stored XSS — see audit finding H-10).
    var seedItems = {!! \Illuminate\Support\Js::from($isEdit && $existingItems->isNotEmpty()
        ? $existingItems->map(fn($i) => [
            'description'      => $i->description,
            'specifications'   => $i->specifications,
            'film_lines'       => $i->film_lines,
            'unit'             => $i->unit,
            'quantity'         => $i->quantity,
            'unit_price'       => $i->unit_price,
            'discount_percent' => $i->discount_percent,
          ])
        : []) !!};
    if (seedItems.length) { seedItems.forEach(addRow); } else { addRow(); }
    applyModeAll();

    document.getElementById('addRowBtn').addEventListener('click', () => addRow());
    discIn.addEventListener('input', recalc);

    // Show/hide vehicle fields
    function toggleVehicle(){
        const cat = document.getElementById('categorySelect')?.value;
        const vf  = document.getElementById('vehicleFields');
        if(vf) vf.style.display = cat === 'building_film' ? 'none' : '';
    }
    document.getElementById('categorySelect')?.addEventListener('change', function(){
        toggleVehicle();
        // Katalog berubah mengikuti kategori: bangun ulang pilihan produk, pertahankan baris yang diketik manual.
        applyModeAll();
        body.querySelectorAll('.item-row').forEach(row => {
            if (isCar()) return;
            const picker = row.querySelector('.desc-picker');
            const manual = picker.value === MANUAL;
            const current = row.querySelector('.desc-input').value;
            buildPicker(picker, '');
            if (manual && current) { picker.value = MANUAL; } else { picker.value = ''; row.querySelector('.desc-input').value = ''; row.querySelector('.spec-input').value = ''; }
            syncPicker(row, true);
        });
        // Kategori gedung tidak butuh data kendaraan — kosongkan supaya tidak ada
        // sisa nilai lama yang ikut ter-submit walau field-nya disembunyikan
        // (server juga menjaga hal yang sama, ini cuma agar form tidak membingungkan).
        if (this.value === 'building_film') {
            document.getElementById('vehicleFields')
                ?.querySelectorAll('input').forEach(el => el.value = '');
        }
    });
    toggleVehicle();

    // Sidebar scroll spy
    const navLinks = document.querySelectorAll('#quoteNav a');
    const sections = document.querySelectorAll('.cust-section[id]');
    if(sections.length){
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if(e.isIntersecting){
                    navLinks.forEach(a => a.classList.remove('is-active'));
                    const link = document.querySelector(`#quoteNav a[href="#${e.target.id}"]`);
                    if(link) link.classList.add('is-active');
                }
            });
        }, { threshold: 0.4 });
        sections.forEach(s => obs.observe(s));
    }
})();
</script>
@endpush
