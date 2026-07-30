@php
    $panelOptions = [
        'hood'          => 'Kap Mesin',
        'front_bumper'  => 'Bemper Depan',
        'rear_bumper'   => 'Bemper Belakang',
        'front_fenders' => 'Spatbor Depan',
        'rear_fenders'  => 'Spatbor Belakang',
        'mirrors'       => 'Spion',
        'door_edges'    => 'Tepi Pintu',
        'full_doors'    => 'Pintu Penuh',
        'roof'          => 'Atap',
        'trunk'         => 'Bagasi / Trunk',
        'rocker_panels' => 'Bawah Pintu',
        'headlights'    => 'Lampu Depan',
        'taillights'    => 'Lampu Belakang',
    ];

    $selectedPanels = old('panels', $installation->panels ?? []);
@endphp

@include('partials.form-errors')

<div class="row g-3">

    {{-- Customer & Product --}}
    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">
            Customer PPF <span class="text-danger">*</span>
        </label>
        <select name="customer_ppf_id" class="form-select form-select-sm @error('customer_ppf_id') is-invalid @enderror" required>
            <option value="">— Pilih Customer —</option>
            @foreach ($customers as $c)
                <option value="{{ $c->id }}"
                    {{ old('customer_ppf_id', $installation->customer_ppf_id ?? '') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                    @if($c->wsn) (WSN: {{ $c->wsn }}) @endif
                </option>
            @endforeach
        </select>
        @error('customer_ppf_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">
            Produk PPF <span class="text-danger">*</span>
        </label>
        <select name="ppf_product_id" class="form-select form-select-sm @error('ppf_product_id') is-invalid @enderror" required>
            <option value="">— Pilih Produk —</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}"
                    {{ old('ppf_product_id', $installation->ppf_product_id ?? '') == $p->id ? 'selected' : '' }}>
                    {{ $p->brand }} {{ $p->name }}
                    @if($p->series) — {{ $p->series }} @endif
                </option>
            @endforeach
        </select>
        @error('ppf_product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Branch & Install Date --}}
    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">
            Cabang <span class="text-danger">*</span>
        </label>
        <select name="branch_id" class="form-select form-select-sm @error('branch_id') is-invalid @enderror" required>
            <option value="">— Pilih Cabang —</option>
            @foreach ($branches as $b)
                <option value="{{ $b->id }}"
                    {{ old('branch_id', $installation->branch_id ?? '') == $b->id ? 'selected' : '' }}>
                    {{ $b->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-medium" style="font-size:13px">
            Tanggal Pasang <span class="text-danger">*</span>
        </label>
        <input type="date" name="install_date" class="form-control form-control-sm @error('install_date') is-invalid @enderror"
               value="{{ old('install_date', optional($installation->install_date ?? null)->format('Y-m-d')) }}" required>
        @error('install_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-medium" style="font-size:13px">
            Tipe Pemasangan <span class="text-danger">*</span>
        </label>
        <select name="install_type" class="form-select form-select-sm @error('install_type') is-invalid @enderror" required>
            <option value="fullbody" {{ old('install_type', $installation->install_type ?? '') === 'fullbody' ? 'selected' : '' }}>Fullbody</option>
            <option value="partial"  {{ old('install_type', $installation->install_type ?? '') === 'partial'  ? 'selected' : '' }}>Partial</option>
        </select>
        @error('install_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Panels (checkboxes) --}}
    <div class="col-12">
        <label class="form-label fw-medium" style="font-size:13px">Panel yang Dipasang</label>
        <div class="row g-2">
            @foreach ($panelOptions as $key => $label)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="form-check" style="font-size:13px">
                    <input class="form-check-input" type="checkbox" name="panels[]" value="{{ $key }}"
                           id="panel_{{ $key }}"
                           {{ in_array($key, $selectedPanels) ? 'checked' : '' }}>
                    <label class="form-check-label" for="panel_{{ $key }}">{{ $label }}</label>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Teknisi --}}
    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">Tukang (Teknisi Utama)</label>
        <select name="tukang_id" class="form-select form-select-sm @error('tukang_id') is-invalid @enderror">
            <option value="">— Tidak Ditugaskan —</option>
            @foreach ($employees as $emp)
                <option value="{{ $emp->id }}"
                    {{ old('tukang_id', $installation->tukang_id ?? '') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>
        @error('tukang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">Kenek (Asisten)</label>
        <select name="kenek_id" class="form-select form-select-sm @error('kenek_id') is-invalid @enderror">
            <option value="">— Tidak Ditugaskan —</option>
            @foreach ($employees as $emp)
                <option value="{{ $emp->id }}"
                    {{ old('kenek_id', $installation->kenek_id ?? '') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>
        @error('kenek_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Teknisi Tambahan (pivot) --}}
    @php
        $selectedTechs = old('technicians', $installation->exists
            ? $installation->technicians->pluck('id')->toArray()
            : []);
    @endphp
    <div class="col-12">
        <label class="form-label fw-medium" style="font-size:13px">Teknisi Tambahan</label>
        <select name="technicians[]" class="form-select form-select-sm" multiple size="4" style="font-size:13px">
            @foreach ($employees as $emp)
                <option value="{{ $emp->id }}"
                    {{ in_array($emp->id, $selectedTechs) ? 'selected' : '' }}>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text" style="font-size:10px">Tahan Ctrl/⌘ untuk pilih beberapa. Berbeda dengan Tukang/Kenek di atas.</div>
    </div>

    {{-- Divider: Garansi --}}
    <div class="col-12">
        <hr class="my-1">
        <p class="fw-semibold mb-0" style="font-size:12px;color:#6366f1;text-transform:uppercase;letter-spacing:.5px">
            Informasi Garansi
        </p>
    </div>

    <div class="col-md-5">
        <label class="form-label fw-medium" style="font-size:13px">
            Kode Garansi <span class="text-danger">*</span>
        </label>
        <input type="text" name="warranty_code" class="form-control form-control-sm @error('warranty_code') is-invalid @enderror font-monospace"
               value="{{ old('warranty_code', $installation->warranty_code ?? '') }}"
               placeholder="cth. PPF-2024-00001" required>
        @error('warranty_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-medium" style="font-size:13px">
            Mulai Garansi <span class="text-danger">*</span>
        </label>
        <input type="date" name="warranty_start" class="form-control form-control-sm @error('warranty_start') is-invalid @enderror"
               value="{{ old('warranty_start', optional($installation->warranty_start ?? null)->format('Y-m-d')) }}" required>
        @error('warranty_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-2">
        <label class="form-label fw-medium" style="font-size:13px">Lama Garansi (thn)</label>
        <input type="number" name="warranty_years" class="form-control form-control-sm @error('warranty_years') is-invalid @enderror"
               value="{{ old('warranty_years', $installation->warranty_years ?? '') }}"
               placeholder="Dari produk" min="1" max="20">
        <div class="form-text" style="font-size:10px">Kosongkan = pakai default produk</div>
        @error('warranty_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-2">
        <label class="form-label fw-medium" style="font-size:13px">Status Garansi</label>
        <select name="warranty_status" class="form-select form-select-sm @error('warranty_status') is-invalid @enderror">
            <option value="active"  {{ old('warranty_status', $installation->warranty_status ?? 'active') === 'active'  ? 'selected' : '' }}>Aktif</option>
            <option value="expired" {{ old('warranty_status', $installation->warranty_status ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
            <option value="void"    {{ old('warranty_status', $installation->warranty_status ?? '') === 'void'    ? 'selected' : '' }}>Void</option>
        </select>
        @error('warranty_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Divider: Dokumen --}}
    <div class="col-12">
        <hr class="my-1">
        <p class="fw-semibold mb-0" style="font-size:12px;color:#6366f1;text-transform:uppercase;letter-spacing:.5px">
            Dokumen
        </p>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">Foto Checklist</label>
        @if (!empty($installation->checklist_image))
            <div class="mb-2">
                <img src="{{ Storage::url($installation->checklist_image) }}"
                     alt="Checklist" style="max-height:80px;border-radius:6px;border:1px solid #e2e8f0">
            </div>
        @endif
        <input type="file" name="checklist_image" class="form-control form-control-sm @error('checklist_image') is-invalid @enderror"
               accept="image/jpeg,image/png,image/webp">
        @error('checklist_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium" style="font-size:13px">Foto Job Order</label>
        @if (!empty($installation->job_order_image))
            <div class="mb-2">
                <img src="{{ Storage::url($installation->job_order_image) }}"
                     alt="Job Order" style="max-height:80px;border-radius:6px;border:1px solid #e2e8f0">
            </div>
        @endif
        <input type="file" name="job_order_image" class="form-control form-control-sm @error('job_order_image') is-invalid @enderror"
               accept="image/jpeg,image/png,image/webp">
        @error('job_order_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-medium" style="font-size:13px">Catatan</label>
        <textarea name="notes" rows="2" class="form-control form-control-sm @error('notes') is-invalid @enderror"
                  placeholder="Catatan tambahan…">{{ old('notes', $installation->notes ?? '') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

</div>
