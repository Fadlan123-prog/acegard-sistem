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


[data-theme="dark"] .cust-section,[data-theme="dark"] .cust-action-bar { background:var(--dark-2); }
[data-theme="dark"] .cust-section-head { background:var(--dark-3); }
[data-theme="dark"] .cust-nav { background:var(--dark-2); }
[data-theme="dark"] .cust-nav li a { color:var(--neutral-400); }
[data-theme="dark"] .cust-nav li a .cn-icon { background:var(--dark-3); color:var(--neutral-400); }
[data-theme="dark"] .cust-section-head .sec-title { color:var(--neutral-100); }
[data-theme="dark"] .items-tbl th { background:var(--dark-3); }
</style>
@include('partials.doc-items.styles')
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

                @include('partials.doc-items.table')

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
                                           value="{{ old('discount_amount', (int) round((float) ($isEdit ? $quotation->discount_amount : 0))) }}"
                                           min="0" step="1">
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

@include('partials.doc-items.template')

@push('page-scripts')
@include('partials.doc-items.script', ['seedItems' => $isEdit ? $existingItems->map(fn($i) => [
    'description' => $i->description, 'specifications' => $i->specifications, 'film_lines' => $i->film_lines,
    'vehicle_label' => $i->vehicle_label, 'vehicle_plate' => $i->vehicle_plate, 'unit' => $i->unit,
    'quantity' => (float) $i->quantity, 'unit_price' => (int) round((float) $i->unit_price), 'discount_percent' => (float) $i->discount_percent,
])->all() : []])
<script>
(function(){
    // Show/hide vehicle fields
    function toggleVehicle(){
        const cat = document.getElementById('categorySelect')?.value;
        const vf  = document.getElementById('vehicleFields');
        if(vf) vf.style.display = cat === 'building_film' ? 'none' : '';
    }
    document.getElementById('categorySelect')?.addEventListener('change', function(){
        toggleVehicle();
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
