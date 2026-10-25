@php
    $isEdit        = isset($salesOrder);
    $existingItems = $isEdit ? $salesOrder->items : collect();
    $quotation     = $quotation ?? ($salesOrder->quotation ?? null);
    $lead          = $lead ?? ($salesOrder->lead ?? $quotation?->lead ?? null);
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

/* Picker multi-teknisi (kategori Gedung) */
.tech-picker { position:relative; }
.tech-picker-trigger { width:100%; min-height:42px; display:flex; align-items:center; cursor:pointer; }
.tech-picker-trigger[aria-expanded="true"] { border-color:var(--primary-600); box-shadow:0 0 0 .2rem var(--primary-light); }
.tech-chip-row { display:flex; flex-wrap:wrap; align-items:center; gap:6px; width:100%; }
.tech-placeholder { color:var(--neutral-500); font-size:14px; }
.tech-chip {
    display:inline-flex; align-items:center; gap:4px; max-width:100%;
    background:var(--primary-light); color:var(--primary-600);
    border-radius:999px; padding:3px 4px 3px 12px; font-size:13px; font-weight:600; line-height:1.5;
}
.tech-chip span { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tech-chip-remove {
    display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
    width:22px; height:22px; border:none; border-radius:50%; background:transparent;
    color:var(--primary-600); font-size:15px; cursor:pointer; padding:0;
}
.tech-chip-remove:hover,.tech-chip-remove:focus-visible { background:rgba(0,0,0,.08); outline:none; }
.tech-picker-panel {
    position:absolute; top:calc(100% + 6px); left:0; right:0; z-index:30;
    background:var(--white); border:1px solid var(--border-color); border-radius:10px;
    box-shadow:0 12px 28px rgba(20,20,43,.14); max-height:280px; overflow-y:auto; padding:6px;
}
.tech-picker-option {
    display:flex; align-items:center; gap:12px; min-height:44px; padding:8px 10px;
    border-radius:8px; cursor:pointer; font-size:14px; color:var(--neutral-900);
}
.tech-picker-option:hover,.tech-picker-option:focus-within { background:var(--neutral-50); }
.tech-picker-checkbox { width:18px; height:18px; flex-shrink:0; cursor:pointer; accent-color:var(--primary-600); }

[data-theme="dark"] .tech-placeholder { color:var(--neutral-400); }
[data-theme="dark"] .tech-picker-panel { background:var(--dark-2); border-color:var(--border-color); }
[data-theme="dark"] .tech-picker-option { color:var(--neutral-100); }
[data-theme="dark"] .tech-picker-option:hover,[data-theme="dark"] .tech-picker-option:focus-within { background:var(--dark-3); }
[data-theme="dark"] .tech-chip-remove:hover { background:rgba(255,255,255,.12); }
</style>
@endpush

@include('partials.form-errors')

<div class="cust-layout">

    {{-- Sidebar Nav --}}
    <ul class="cust-nav" id="soNav">
        <li><a href="#sec-info" class="is-active">
            <span class="cn-icon"><iconify-icon icon="mdi:clipboard-list-outline"></iconify-icon></span> Info SO
        </a></li>
        <li><a href="#sec-prospek">
            <span class="cn-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></span> Data Prospek
        </a></li>
        <li><a href="#sec-penugasan">
            <span class="cn-icon"><iconify-icon icon="mdi:account-multiple-outline"></iconify-icon></span> Penugasan Tim
        </a></li>
        <li><a href="#sec-items">
            <span class="cn-icon"><iconify-icon icon="mdi:format-list-checks"></iconify-icon></span> Rincian Harga
        </a></li>
        <li><a href="#sec-catatan">
            <span class="cn-icon"><iconify-icon icon="mdi:note-text-outline"></iconify-icon></span> Catatan
        </a></li>
    </ul>

    <div>

        {{-- Source banners --}}
        @if($quotation)
        <x-ui.alert type="success" class="mb-3">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="mdi:file-document-outline" class="fs-5"></iconify-icon>
                <span>Dari Quotation: <strong>{{ $quotation->quotation_number }}</strong>
                    — {{ $quotation->prospect_name }}
                </span>
            </div>
            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
            @if($quotation->lead_id)
                <input type="hidden" name="lead_id" value="{{ $quotation->lead_id }}">
            @endif
        </x-ui.alert>
        @elseif($lead)
        <x-ui.alert type="info" class="mb-3">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="mdi:target" class="fs-5"></iconify-icon>
                <span>Dari Lead: <strong>{{ $lead->name }}</strong></span>
            </div>
            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
        </x-ui.alert>
        @endif

        {{-- Section 1: Info SO --}}
        <div id="sec-info" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:clipboard-list-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Informasi Sales Order</p>
                    <p class="sec-desc">Tanggal, jadwal pemasangan, dan kategori layanan</p>
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
                        <x-ui.form-field label="Tanggal SO" name="date" :required="true" :error="$errors->first('date')">
                            <input type="date" name="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', $isEdit ? $salesOrder->date->format('Y-m-d') : now()->toDateString()) }}" required>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Jadwal Pemasangan" name="scheduled_date"
                                         :error="$errors->first('scheduled_date')"
                                         help="Rencana tanggal pekerjaan dilakukan">
                            <input type="date" name="scheduled_date"
                                   class="form-control @error('scheduled_date') is-invalid @enderror"
                                   value="{{ old('scheduled_date', $isEdit ? $salesOrder->scheduled_date?->format('Y-m-d') : '') }}">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Kategori" name="category" :required="true" :error="$errors->first('category')">
                            <select name="category" id="categorySelect"
                                    class="form-select @error('category') is-invalid @enderror" required>
                                @foreach(\App\Models\SalesOrder::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('category', $isEdit ? $salesOrder->category : ($quotation->category ?? $lead->category ?? 'car_film')) == $key ? 'selected' : '' }}>
                                        {{ $label }}
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
                    <p class="sec-title">Data Prospek / Customer</p>
                    <p class="sec-desc">Informasi pemilik kendaraan atau bangunan</p>
                </div>
            </div>
            <div class="cust-section-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <x-ui.form-field label="Nama" name="prospect_name" :required="true" :error="$errors->first('prospect_name')">
                            <input type="text" name="prospect_name"
                                   class="form-control @error('prospect_name') is-invalid @enderror"
                                   value="{{ old('prospect_name', $isEdit ? $salesOrder->prospect_name : ($quotation->prospect_name ?? $lead->name ?? '')) }}"
                                   placeholder="Nama lengkap" required>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Telepon" name="prospect_phone">
                            <input type="text" name="prospect_phone" class="form-control"
                                   value="{{ old('prospect_phone', $isEdit ? $salesOrder->prospect_phone : ($quotation->prospect_phone ?? $lead->phone ?? '')) }}"
                                   placeholder="08xx-xxxx-xxxx">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-4">
                        <x-ui.form-field label="Email" name="prospect_email">
                            <input type="email" name="prospect_email" class="form-control"
                                   value="{{ old('prospect_email', $isEdit ? $salesOrder->prospect_email : ($quotation->prospect_email ?? $lead->email ?? '')) }}"
                                   placeholder="email@contoh.com">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-6">
                        <x-ui.form-field label="Alamat" name="prospect_address">
                            <input type="text" name="prospect_address" class="form-control"
                                   value="{{ old('prospect_address', $isEdit ? $salesOrder->prospect_address : ($quotation->prospect_address ?? $lead->address ?? '')) }}"
                                   placeholder="Alamat lengkap">
                        </x-ui.form-field>
                    </div>

                    {{-- Vehicle fields --}}
                    <div id="vehicleFields" class="col-12">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <x-ui.form-field label="Merek Kendaraan" name="vehicle_brand">
                                    <input type="text" name="vehicle_brand" class="form-control"
                                           value="{{ old('vehicle_brand', $isEdit ? $salesOrder->vehicle_brand : ($quotation->vehicle_brand ?? $lead->vehicle_brand ?? '')) }}"
                                           placeholder="Toyota...">
                                </x-ui.form-field>
                            </div>
                            <div class="col-md-3">
                                <x-ui.form-field label="Model" name="vehicle_model">
                                    <input type="text" name="vehicle_model" class="form-control"
                                           value="{{ old('vehicle_model', $isEdit ? $salesOrder->vehicle_model : ($quotation->vehicle_model ?? $lead->vehicle_model ?? '')) }}"
                                           placeholder="Avanza...">
                                </x-ui.form-field>
                            </div>
                            <div class="col-md-2">
                                <x-ui.form-field label="Tahun" name="vehicle_year">
                                    <input type="text" name="vehicle_year" class="form-control"
                                           value="{{ old('vehicle_year', $isEdit ? $salesOrder->vehicle_year : ($quotation->vehicle_year ?? $lead->vehicle_year ?? '')) }}"
                                           placeholder="{{ date('Y') }}">
                                </x-ui.form-field>
                            </div>
                            <div class="col-md-2">
                                <x-ui.form-field label="Plat Nomor" name="vehicle_plate">
                                    <input type="text" name="vehicle_plate" class="form-control"
                                           value="{{ old('vehicle_plate', $isEdit ? $salesOrder->vehicle_plate : ($quotation->vehicle_plate ?? '')) }}"
                                           placeholder="BH 1234 AB">
                                </x-ui.form-field>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 3: Penugasan Tim --}}
        <div id="sec-penugasan" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:account-multiple-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Penugasan Tim</p>
                    <p class="sec-desc">Marketing/PIC yang menangani pekerjaan ini</p>
                </div>
            </div>
            <div class="cust-section-body">
                {{-- Teknisi/kenek (mobil/PPF) atau daftar teknisi (gedung) SENGAJA tidak
                     diisi di sini — baru bisa ditugaskan setelah SO dikonfirmasi, lewat
                     tombol "Tugaskan Teknisi/Kenek" di halaman detail SO. Ini menjamin
                     urutan konfirmasi-dulu-baru-assign dan jadi satu-satunya jalur
                     penugasan (termasuk untuk reassign). --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-ui.form-field label="Marketing / PIC" name="marketing_id">
                            <select name="marketing_id" class="form-select">
                                <option value="">— Tidak ditugaskan —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ old('marketing_id', $isEdit ? $salesOrder->marketing_id : '') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Rincian Harga --}}
        <div id="sec-items" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:format-list-checks"></iconify-icon></div>
                <div>
                    <p class="sec-title">Rincian Harga</p>
                    <p class="sec-desc">Daftar produk/layanan beserta harga</p>
                </div>
            </div>
            <div class="cust-section-body">

                <div class="table-responsive mb-3">
                    <table class="table table-bordered items-tbl mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="min-width:200px">Deskripsi / Produk</th>
                                <th style="min-width:140px">Spesifikasi</th>
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

                <x-ui.button variant="outline" size="sm" type="button" id="addRowBtn" class="mb-4">
                    <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Tambah Baris
                </x-ui.button>

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
                                           value="{{ old('discount_amount', $isEdit ? $salesOrder->discount_amount : 0) }}"
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

        {{-- Section 5: Catatan --}}
        <div id="sec-catatan" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:note-text-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Catatan</p>
                    <p class="sec-desc">Instruksi tambahan atau catatan pekerjaan</p>
                </div>
            </div>
            <div class="cust-section-body">
                <x-ui.form-field label="Catatan" name="notes">
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Instruksi khusus, catatan teknisi, dll...">{{ old('notes', $isEdit ? $salesOrder->notes : '') }}</textarea>
                </x-ui.form-field>
            </div>
            <div class="cust-action-bar">
                <x-ui.button variant="ghost" href="{{ $isEdit ? route('sales-orders.show', $salesOrder) : route('sales-orders.index') }}">Batal</x-ui.button>
                <x-ui.button variant="primary" type="submit" class="px-4">
                    <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>
                    {{ $isEdit ? 'Perbarui Sales Order' : 'Simpan Sales Order' }}
                </x-ui.button>
            </div>
        </div>

    </div>
</div>

{{-- Row template --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td><input type="text" name="items[__IDX__][description]" class="form-control form-control-sm" placeholder="Nama produk / layanan" required></td>
        <td><input type="text" name="items[__IDX__][specifications]" class="form-control form-control-sm" placeholder="Opsional"></td>
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
        const qty   = parseFloat(row.querySelector('.item-qty').value)  || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const disc  = parseFloat(row.querySelector('.item-disc').value)  || 0;
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

    function addRow(data){
        const html = tmpl.innerHTML.replace(/__IDX__/g, rowIdx++);
        body.insertAdjacentHTML('beforeend', html);
        const row = body.lastElementChild;

        if(data){
            row.querySelector('[name$="[description]"]').value    = data.description     || '';
            row.querySelector('[name$="[specifications]"]').value = data.specifications  || '';
            row.querySelector('[name$="[unit]"]').value           = data.unit            || 'unit';
            row.querySelector('.item-qty').value                  = data.quantity        || 1;
            row.querySelector('.item-price').value                = data.unit_price      || 0;
            row.querySelector('.item-disc').value                 = data.discount_percent|| 0;
        }

        row.querySelectorAll('input,select').forEach(el => el.addEventListener('input', recalc));
        row.querySelector('.remove-row-btn').addEventListener('click', () => { row.remove(); recalc(); });
        recalc();
    }

    // Seed items
    // Js::from() (not ->toJson() wrapped in a raw Blade echo) HEX-escapes <, >, &, ' so a
    // description/specifications value containing a closing script tag can't break
    // out of this inline script block (stored XSS — see audit finding H-10).
    var seedItems = {!! \Illuminate\Support\Js::from($isEdit && $existingItems->isNotEmpty()
        ? $existingItems->map(fn($i) => [
            'description'      => $i->description,
            'specifications'   => $i->specifications,
            'unit'             => $i->unit,
            'quantity'         => $i->quantity,
            'unit_price'       => $i->unit_price,
            'discount_percent' => $i->discount_percent,
          ])
        : ($quotation && $quotation->items->isNotEmpty()
            ? $quotation->items->map(fn($i) => [
                'description'      => $i->description,
                'specifications'   => $i->specifications,
                'unit'             => $i->unit,
                'quantity'         => $i->quantity,
                'unit_price'       => $i->unit_price,
                'discount_percent' => $i->discount_percent,
              ])
            : []))
    !!};
    if (seedItems.length) { seedItems.forEach(addRow); } else { addRow(); }

    document.getElementById('addRowBtn').addEventListener('click', () => addRow());
    discIn.addEventListener('input', recalc);

    // Show/hide vehicle fields based on category
    function toggleVehicle(){
        const cat = document.getElementById('categorySelect')?.value;
        const vf  = document.getElementById('vehicleFields');
        if(vf) vf.style.display = cat === 'building_film' ? 'none' : '';
    }
    document.getElementById('categorySelect')?.addEventListener('change', toggleVehicle);
    toggleVehicle();

    // Sidebar scroll spy
    const navLinks = document.querySelectorAll('#soNav a');
    const sections = document.querySelectorAll('.cust-section[id]');
    if(sections.length){
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if(e.isIntersecting){
                    navLinks.forEach(a => a.classList.remove('is-active'));
                    const link = document.querySelector(`#soNav a[href="#${e.target.id}"]`);
                    if(link) link.classList.add('is-active');
                }
            });
        }, { threshold:0.4 });
        sections.forEach(s => obs.observe(s));
    }
})();
</script>
@endpush
