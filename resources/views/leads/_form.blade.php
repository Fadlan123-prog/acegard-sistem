@php $isEdit = isset($lead); @endphp

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

.cust-action-bar { position:sticky; bottom:0; z-index:100; background:var(--white); border-top:1px solid var(--border-color); padding:14px 20px; display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-top:-1px; }

[data-theme="dark"] .cust-section,.cust-action-bar { background:var(--dark-2); }
[data-theme="dark"] .cust-section-head { background:var(--dark-3); }
[data-theme="dark"] .cust-nav { background:var(--dark-2); }
[data-theme="dark"] .cust-nav li a { color:var(--neutral-400); }
[data-theme="dark"] .cust-nav li a .cn-icon { background:var(--dark-3); color:var(--neutral-400); }
[data-theme="dark"] .cust-section-head .sec-title { color:var(--neutral-100); }
</style>
@endpush

@include('partials.form-errors')

<div class="cust-layout">

    {{-- Sidebar Nav --}}
    <ul class="cust-nav" id="leadNav">
        <li><a href="#sec-identitas" class="is-active">
            <span class="cn-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></span> Identitas
        </a></li>
        <li><a href="#sec-kebutuhan">
            <span class="cn-icon"><iconify-icon icon="mdi:car-outline"></iconify-icon></span> Kebutuhan
        </a></li>
        <li><a href="#sec-penugasan">
            <span class="cn-icon"><iconify-icon icon="mdi:account-multiple-outline"></iconify-icon></span> Penugasan
        </a></li>
        @if($isEdit)
        <li><a href="#sec-status">
            <span class="cn-icon"><iconify-icon icon="mdi:flag-outline"></iconify-icon></span> Status
        </a></li>
        @endif
    </ul>

    {{-- Main Content --}}
    <div>

        {{-- Section 1: Identitas --}}
        <div id="sec-identitas" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:account-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Identitas Prospek</p>
                    <p class="sec-desc">Data pribadi dan kontak calon customer</p>
                </div>
            </div>
            <div class="cust-section-body">

                {{-- Cabang (superadmin, create only) --}}
                @if(!$isEdit && auth()->user()->hasRole('superadmin') && isset($branches) && $branches->isNotEmpty())
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <x-ui.form-field label="Cabang" name="branch_id" :required="true" :error="$errors->first('branch_id')">
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
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
                    <div class="col-md-6">
                        <x-ui.form-field label="Nama Lengkap" name="name" :required="true" :error="$errors->first('name')">
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $lead->name ?? '') }}"
                                   placeholder="Nama lengkap prospek" required>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Telepon / WhatsApp" name="phone" :error="$errors->first('phone')">
                            <input type="text" name="phone" id="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $lead->phone ?? '') }}"
                                   placeholder="08xx-xxxx-xxxx">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Email" name="email" :error="$errors->first('email')">
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $lead->email ?? '') }}"
                                   placeholder="email@contoh.com">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-6">
                        <x-ui.form-field label="Alamat" name="address">
                            <input type="text" name="address" class="form-control"
                                   value="{{ old('address', $lead->address ?? '') }}"
                                   placeholder="Alamat lengkap">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Kota" name="city">
                            <input type="text" name="city" class="form-control"
                                   value="{{ old('city', $lead->city ?? '') }}"
                                   placeholder="Jakarta">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Sumber Lead" name="source" :required="true" :error="$errors->first('source')">
                            <select name="source" class="form-select @error('source') is-invalid @enderror" required>
                                @foreach(\App\Models\Lead::SOURCES as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('source', $lead->source ?? 'walk_in') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                </div>

            </div>
        </div>

        {{-- Section 2: Kebutuhan --}}
        <div id="sec-kebutuhan" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:car-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Kebutuhan Layanan</p>
                    <p class="sec-desc">Kategori layanan dan informasi kendaraan (jika relevan)</p>
                </div>
            </div>
            <div class="cust-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-ui.form-field label="Kategori" name="category" :required="true" :error="$errors->first('category')">
                            <select name="category" id="categorySelect"
                                    class="form-select @error('category') is-invalid @enderror" required>
                                @foreach(\App\Models\Lead::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('category', $lead->category ?? 'car_film') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-4">
                        <x-ui.form-field label="Estimasi Nilai (Rp)" name="estimated_value">
                            <input type="number" name="estimated_value" class="form-control" min="0" step="1000"
                                   value="{{ old('estimated_value', $lead->estimated_value ?? '') }}"
                                   placeholder="0">
                        </x-ui.form-field>
                    </div>
                </div>

                <div id="vehicleFields" class="row g-3 mt-1">
                    <div class="col-md-4">
                        <x-ui.form-field label="Merek Kendaraan" name="vehicle_brand">
                            <input type="text" name="vehicle_brand" class="form-control"
                                   value="{{ old('vehicle_brand', $lead->vehicle_brand ?? '') }}"
                                   placeholder="Toyota, Honda...">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-4">
                        <x-ui.form-field label="Model" name="vehicle_model">
                            <input type="text" name="vehicle_model" class="form-control"
                                   value="{{ old('vehicle_model', $lead->vehicle_model ?? '') }}"
                                   placeholder="Avanza, Brio...">
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-2">
                        <x-ui.form-field label="Tahun" name="vehicle_year">
                            <input type="text" name="vehicle_year" class="form-control"
                                   value="{{ old('vehicle_year', $lead->vehicle_year ?? '') }}"
                                   placeholder="{{ date('Y') }}">
                        </x-ui.form-field>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <x-ui.form-field label="Catatan" name="notes">
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Catatan tambahan tentang prospek ini...">{{ old('notes', $lead->notes ?? '') }}</textarea>
                        </x-ui.form-field>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Penugasan --}}
        <div id="sec-penugasan" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:account-multiple-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Penugasan</p>
                    <p class="sec-desc">Marketing yang menangani dan jadwal follow up</p>
                </div>
            </div>
            <div class="cust-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-ui.form-field label="Marketing / PIC" name="assigned_to">
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Tidak ditugaskan --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ old('assigned_to', $lead->assigned_to ?? '') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                    <div class="col-md-3">
                        <x-ui.form-field label="Follow Up Tanggal" name="follow_up_date">
                            <input type="date" name="follow_up_date" class="form-control"
                                   value="{{ old('follow_up_date', isset($lead) ? $lead->follow_up_date?->format('Y-m-d') : '') }}">
                        </x-ui.form-field>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Status (edit only) --}}
        @if($isEdit)
        <div id="sec-status" class="cust-section">
            <div class="cust-section-head">
                <div class="sec-icon"><iconify-icon icon="mdi:flag-outline"></iconify-icon></div>
                <div>
                    <p class="sec-title">Status Lead</p>
                    <p class="sec-desc">Perubahan status akan otomatis dicatat di riwayat aktivitas</p>
                </div>
            </div>
            <div class="cust-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-ui.form-field label="Status" name="status" :required="true" :error="$errors->first('status')">
                            <select name="status" id="statusSelect"
                                    class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(\App\Models\Lead::STATUSES as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('status', $lead->status) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                    <div class="col-12" id="lostReasonField"
                         style="{{ old('status', $lead->status) === 'lost' ? '' : 'display:none' }}">
                        <x-ui.form-field label="Alasan Gagal" name="lost_reason" :error="$errors->first('lost_reason')">
                            <textarea name="lost_reason"
                                      class="form-control @error('lost_reason') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Jelaskan mengapa lead ini gagal...">{{ old('lost_reason', $lead->lost_reason ?? '') }}</textarea>
                        </x-ui.form-field>
                    </div>
                </div>
            </div>
            <div class="cust-action-bar">
                <x-ui.button :href="route('leads.show', $lead)" variant="outline">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" class="px-4">
                    <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon> Perbarui Lead
                </x-ui.button>
            </div>
        </div>
        @else
        <div class="cust-action-bar" style="border-radius:12px; border:1px solid var(--border-color); margin-bottom:20px;">
            <x-ui.button :href="route('leads.index')" variant="outline">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary" class="px-4">
                <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon> Simpan Lead
            </x-ui.button>
        </div>
        @endif

    </div>
</div>

@push('page-scripts')
<script>
(function(){
    // Show/hide vehicle fields
    function toggleVehicle() {
        const cat = document.getElementById('categorySelect')?.value;
        const vf  = document.getElementById('vehicleFields');
        if (vf) vf.style.display = cat === 'building_film' ? 'none' : '';
    }
    document.getElementById('categorySelect')?.addEventListener('change', toggleVehicle);
    toggleVehicle();

    // Show/hide lost reason (edit mode)
    document.getElementById('statusSelect')?.addEventListener('change', function(){
        const el = document.getElementById('lostReasonField');
        if (el) el.style.display = this.value === 'lost' ? '' : 'none';
    });

    // Sidebar scroll spy
    const navLinks = document.querySelectorAll('#leadNav a');
    const sections = document.querySelectorAll('.cust-section[id]');
    if (sections.length) {
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    navLinks.forEach(a => a.classList.remove('is-active'));
                    const link = document.querySelector(`#leadNav a[href="#${e.target.id}"]`);
                    if (link) link.classList.add('is-active');
                }
            });
        }, { threshold: 0.4 });
        sections.forEach(s => obs.observe(s));
    }
})();
</script>
@endpush
