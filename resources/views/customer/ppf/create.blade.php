@extends('dashboard.index')

@section('title', 'Add Customer PPF')
@section('route-breadcrumb', 'ppf-customers.index')
@section('breadcrumb', 'Customers PPF')

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
  $lockBranch = $lockBranch ?? false;
  $defaultBranchId = $defaultBranchId ?? null;

  $panelOptions = [
    'hood' => 'Kap Mesin',
    'front_bumper' => 'Bumper Depan',
    'rear_bumper' => 'Bumper Belakang',
    'fender_left' => 'Fender Kiri',
    'fender_right' => 'Fender Kanan',
    'door_fl' => 'Pintu Depan Kiri',
    'door_fr' => 'Pintu Depan Kanan',
    'door_rl' => 'Pintu Belakang Kiri',
    'door_rr' => 'Pintu Belakang Kanan',
    'mirror_left' => 'Spion Kiri',
    'mirror_right' => 'Spion Kanan',
    'roof' => 'Atap',
    'trunk' => 'Bagasi',
    'side_skirt_left' => 'Side Skirt Kiri',
    'side_skirt_right' => 'Side Skirt Kanan',
  ];

  $oldInstalls = old('installations', [
    [
      'ppf_product_id' => '',
      'install_type' => '',
      'install_date' => date('Y-m-d'),
      'warranty_years' => 5,
      'technicians' => [],
      'panels' => [],
      'notes' => '',
    ]
  ]);

  $selectedBranch = old('branch_id', $defaultBranchId);
@endphp

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Tambah Customer PPF</h5>
    <a href="{{ route('ppf-customers.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>

  <div class="card-body">
    <form action="{{ route('ppf-customers.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="row g-3">

        {{-- BRANCH --}}
        <div class="col-md-6">
          <label class="form-label">Cabang <span class="text-danger">*</span></label>

          @if($lockBranch)
            <input type="hidden" name="branch_id" value="{{ $selectedBranch }}">
            <select class="form-select" disabled>
              @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ (string)$selectedBranch === (string)$b->id ? 'selected' : '' }}>
                  {{ $b->name }}
                </option>
              @endforeach
            </select>
            <div class="form-text">Cabang otomatis sesuai akun Anda.</div>
          @else
            <select name="branch_id" class="form-select" required>
              <option value="">— Pilih —</option>
              @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ (string)$selectedBranch === (string)$b->id ? 'selected' : '' }}>
                  {{ $b->name }}
                </option>
              @endforeach
            </select>
          @endif
        </div>

        <div class="col-md-6">
          <label class="form-label">WSN <span class="text-danger">*</span></label>
          <input type="text" name="wsn" class="form-control" required value="{{ old('wsn') }}">
        </div>

        <div class="col-md-12">
          <div class="alert alert-info mb-0">
            <strong>Catatan:</strong> Card Number & Warranty Code dibuat otomatis saat disimpan.
          </div>
        </div>


        {{-- CUSTOMER --}}
        <div class="col-12"><h6 class="text-center mb-0">Data Customer</h6></div>

        <div class="col-md-6">
          <label class="form-label">Nama <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">No. HP <span class="text-danger">*</span></label>
          <input type="text" name="phone" class="form-control" required value="{{ old('phone') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Marketing (opsional)</label>
          <select name="marketing_id" class="form-select">
            <option value="">— Tidak ada —</option>
            @foreach($employees as $e)
              @if(($e->job_position ?? '') === 'Marketing')
                <option value="{{ $e->id }}" {{ old('marketing_id')==$e->id ? 'selected' : '' }}>
                  {{ $e->name }}
                </option>
              @endif
            @endforeach
          </select>
        </div>

        <div class="col-md-12">
          <label class="form-label">Alamat <span class="text-danger">*</span></label>
          <input type="text" name="address" class="form-control" required value="{{ old('address') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Kota <span class="text-danger">*</span></label>
          <input type="text" name="city" class="form-control" required value="{{ old('city') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Negara</label>
          <input type="text" name="country" class="form-control" value="{{ old('country','Indonesia') }}">
        </div>

        <hr class="my-5">

        {{-- VEHICLE --}}
        <div class="col-12"><h6 class="text-center mb-0">Data Kendaraan</h6></div>

        <div class="col-md-6">
          <label class="form-label">Brand <span class="text-danger">*</span></label>
          <input type="text" name="vehicle_brand" class="form-control" required value="{{ old('vehicle_brand') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label">Model <span class="text-danger">*</span></label>
          <input type="text" name="vehicle_model" class="form-control" required value="{{ old('vehicle_model') }}">
        </div>

        <div class="col-md-4">
          <label class="form-label">Tahun <span class="text-danger">*</span></label>
          <input type="text" name="vehicle_year" class="form-control" required value="{{ old('vehicle_year') }}">
        </div>

        <div class="col-md-4">
          <label class="form-label">Warna <span class="text-danger">*</span></label>
          <input type="text" name="vehicle_color" class="form-control" required value="{{ old('vehicle_color') }}">
        </div>

        <div class="col-md-4">
          <label class="form-label">Plat <span class="text-danger">*</span></label>
          <input type="text" name="vehicle_plate" class="form-control" required value="{{ old('vehicle_plate') }}">
        </div>

        <hr class="my-5">

        {{-- INSTALLATIONS --}}
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Instalasi PPF</h6>
          <button type="button" class="btn btn-sm btn-outline-primary" id="add-install">+ Tambah Instalasi</button>
        </div>

        <div id="install-container" class="mt-2">
          @foreach($oldInstalls as $i => $ins)
            @php
              $oldTech = old("installations.$i.technicians", $ins['technicians'] ?? []);
              if (!is_array($oldTech)) $oldTech = [$oldTech];

              $oldPanels = old("installations.$i.panels", $ins['panels'] ?? []);
              if (!is_array($oldPanels)) $oldPanels = [$oldPanels];
            @endphp

            <div class="card mt-3 install-item" data-index="{{ $i }}">
              <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong>Instalasi #{{ $i+1 }}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-install">Hapus</button>
              </div>

              <div class="card-body">
                <div class="row g-3">

                  {{-- Produk / Tanggal / Garansi / Tipe --}}
                  <div class="col-md-6">
                    <label class="form-label">Produk PPF <span class="text-danger">*</span></label>
                    <select name="installations[{{ $i }}][ppf_product_id]" class="form-select" required>
                      <option value="">— Pilih —</option>
                      @foreach($ppfProducts as $p)
                        <option value="{{ $p->id }}"
                          {{ (old("installations.$i.ppf_product_id", $ins['ppf_product_id'] ?? '') == $p->id) ? 'selected' : '' }}>
                          {{ $p->name }} - {{ $p->brand }} {{ $p->series }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Tanggal Pasang <span class="text-danger">*</span></label>
                    <input type="date" name="installations[{{ $i }}][install_date]" class="form-control" required
                      value="{{ old("installations.$i.install_date", $ins['install_date'] ?? date('Y-m-d')) }}">
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Garansi (th) <span class="text-danger">*</span></label>
                    <select name="installations[{{ $i }}][warranty_years]" class="form-select" required>
                      <option value="5" {{ (int)old("installations.$i.warranty_years", $ins['warranty_years'] ?? 5)===5 ? 'selected':'' }}>5</option>
                      <option value="7" {{ (int)old("installations.$i.warranty_years", $ins['warranty_years'] ?? 5)===7 ? 'selected':'' }}>7</option>
                    </select>
                  </div>

                  <div class="col-md-2">
                    <label class="form-label">Tipe <span class="text-danger">*</span></label>
                    <select name="installations[{{ $i }}][install_type]" class="form-select" required>
                      <option value="">— Pilih —</option>
                      <option value="fullbody" {{ old("installations.$i.install_type", $ins['install_type'] ?? '') === 'fullbody' ? 'selected' : '' }}>Full Body</option>
                      <option value="partial" {{ old("installations.$i.install_type", $ins['install_type'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                  </div>

                  {{-- TEKNISI (rapih: search + checklist + chips) --}}
                  <div class="col-md-12">
                    <div class="tech-picker border rounded-3 p-3" data-max="4">
                      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <label class="form-label mb-0">Teknisi (maks 4) <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                          <span class="badge bg-light text-dark tech-count">0/4 dipilih</span>
                          <button type="button" class="btn btn-sm btn-outline-secondary tech-clear">Kosongkan</button>
                        </div>
                      </div>

                      <input type="text" class="form-control form-control-sm tech-search mb-2"
                        placeholder="Cari teknisi... (contoh: Ferdi)">

                      <div class="tech-chips d-flex flex-wrap gap-1 mb-2">
                        <span class="badge bg-light text-muted">Belum ada teknisi dipilih</span>
                      </div>

                      <div class="tech-list border rounded-3 p-2" style="max-height:220px; overflow:auto;">
                        <div class="row g-2 p-3">
                          @foreach($employees as $e)
                            @if(($e->job_position ?? '') === 'Teknisi')
                              @php $tid = "tech_{$i}_{$e->id}"; @endphp
                              <div class="col-md-3 col-6 tech-item" data-name="{{ strtolower($e->name) }}">
                                <div class="form-check m-0 d-flex align-items-center">
                                  <input class="form-check-input tech-checkbox"
                                    type="checkbox"
                                    name="installations[{{ $i }}][technicians][]"
                                    value="{{ $e->id }}"
                                    data-emp="{{ $e->id }}"
                                    id="{{ $tid }}"
                                    {{ in_array($e->id, $oldTech) ? 'checked' : '' }}>
                                  <label class="form-check-label" for="{{ $tid }}" style="cursor:pointer;">
                                    {{ $e->name }}
                                  </label>
                                </div>
                              </div>
                            @endif
                          @endforeach
                        </div>
                      </div>

                      <div class="form-text mt-2">Klik checkbox untuk memilih. Maksimal 4 teknisi.</div>
                    </div>
                  </div>

                  {{-- PANELS --}}
                  <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                      <label class="form-label mb-0">Panel yang dipasangkan</label>
                      <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary panel-select-all">Pilih Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary panel-clear">Kosongkan</button>
                      </div>
                    </div>

                    <div class="row g-2 mt-2 panel-wrap">
                      @foreach($panelOptions as $key => $label)
                        @php $cid = "panel_{$i}_{$key}"; @endphp
                        <div class="col-md-3 col-6">
                          <input class="btn-check panel-checkbox" type="checkbox"
                            name="installations[{{ $i }}][panels][]" value="{{ $key }}"
                            id="{{ $cid }}" {{ in_array($key, $oldPanels) ? 'checked' : '' }} autocomplete="off">
                          <label class="btn btn-outline-primary btn-sm w-100 text-start" for="{{ $cid }}">
                            {{ $label }}
                          </label>
                        </div>
                      @endforeach
                    </div>

                    <div class="form-text mt-1">
                      Jika <b>partial</b>, panel wajib dipilih. Jika <b>full body</b>, panel akan dianggap semua.
                    </div>
                  </div>

                  {{-- CHECKLIST & JOB ORDER (gambar) --}}
                  {{-- CHECKLIST & JOB ORDER (rapih) --}}
                    <div class="col-md-6">
                    @php $cid = "doc_checklist_{$i}"; @endphp
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">Checklist</div>
                            <div class="text-muted small">JPG/PNG/WEBP • Maks 2MB</div>
                        </div>
                        <div class="d-flex gap-2">
                            <label for="{{ $cid }}" class="btn btn-sm btn-outline-primary mb-0 doc-label" data-doc="checklist">
                            Upload
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary doc-clear" data-doc="checklist">
                            Reset
                            </button>
                        </div>
                        </div>

                        <input
                        id="{{ $cid }}"
                        type="file"
                        name="installations[{{ $i }}][checklist_image]"
                        class="d-none doc-input"
                        data-doc="checklist"
                        accept="image/*"
                        >

                        <div class="mt-2 small text-muted doc-filename" data-doc="checklist">Belum ada file.</div>

                        <img
                        class="img-fluid rounded border mt-2 d-none doc-preview"
                        data-doc="checklist"
                        style="max-height:180px; object-fit:contain;"
                        alt="Checklist preview"
                        >
                    </div>
                    </div>

                    <div class="col-md-6">
                    @php $jid = "doc_joborder_{$i}"; @endphp
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">Job Order</div>
                            <div class="text-muted small">JPG/PNG/WEBP • Maks 2MB</div>
                        </div>
                        <div class="d-flex gap-2">
                            <label for="{{ $jid }}" class="btn btn-sm btn-outline-primary mb-0 doc-label" data-doc="joborder">
                            Upload
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary doc-clear" data-doc="joborder">
                            Reset
                            </button>
                        </div>
                        </div>

                        <input
                        id="{{ $jid }}"
                        type="file"
                        name="installations[{{ $i }}][job_order_image]"
                        class="d-none doc-input"
                        data-doc="joborder"
                        accept="image/*"
                        >

                        <div class="mt-2 small text-muted doc-filename" data-doc="joborder">Belum ada file.</div>

                        <img
                        class="img-fluid rounded border mt-2 d-none doc-preview"
                        data-doc="joborder"
                        style="max-height:180px; object-fit:contain;"
                        alt="Job order preview"
                        >
                    </div>
                    </div>


                  <div class="col-md-12">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="installations[{{ $i }}][notes]" class="form-control" rows="2">{{ old("installations.$i.notes", $ins['notes'] ?? '') }}</textarea>
                  </div>

                </div>
              </div>
            </div>
          @endforeach
        </div>

        <hr class="my-5">

        <div class="col-md-6">
          <label class="form-label">Konfirmasi Password Anda <span class="text-danger">*</span></label>
          <input type="password" name="admin_password" class="form-control" required>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary mt-2">Simpan Customer PPF</button>
        </div>

      </div>
    </form>
  </div>
</div>

<script>
(function(){
  function todayISO(){ return new Date().toISOString().slice(0,10); }

  function updateTechPicker(picker){
    const max = parseInt(picker.dataset.max || '4', 10);
    const checked = Array.from(picker.querySelectorAll('.tech-checkbox:checked'));
    const countEl = picker.querySelector('.tech-count');
    const chipsEl = picker.querySelector('.tech-chips');

    if (countEl) countEl.textContent = `${checked.length}/${max} dipilih`;

    if (chipsEl) {
      chipsEl.innerHTML = '';
      if (checked.length === 0) {
        const span = document.createElement('span');
        span.className = 'badge bg-light text-muted';
        span.textContent = 'Belum ada teknisi dipilih';
        chipsEl.appendChild(span);
      } else {
        checked.forEach(chk => {
          const label = picker.querySelector(`label[for="${chk.id}"]`)?.textContent?.trim() || 'Teknisi';
          const badge = document.createElement('span');
          badge.className = 'badge bg-primary-light text-primary-600 d-inline-flex align-items-center gap-2';
          badge.innerHTML = `
            <span>${label}</span>
            <button type="button" class="btn-close btn-close-sm chip-remove" aria-label="Remove" data-emp="${chk.value}"></button>
          `;
          chipsEl.appendChild(badge);
        });
      }
    }
  }

  function enforceMax(picker, changedCheckbox){
    const max = parseInt(picker.dataset.max || '4', 10);
    const checked = picker.querySelectorAll('.tech-checkbox:checked');
    if (checked.length > max) {
      changedCheckbox.checked = false;
      alert(`Maksimal ${max} teknisi.`);
      return false;
    }
    return true;
  }

  function filterTechList(picker, query){
    const q = (query || '').toLowerCase().trim();
    picker.querySelectorAll('.tech-item').forEach(item => {
      const name = item.dataset.name || '';
      item.style.display = name.includes(q) ? '' : 'none';
    });
  }

  function updateNamesAndIds(root, newIndex){
    root.querySelectorAll('select, input, textarea, label').forEach(el => {
      const name = el.getAttribute && el.getAttribute('name');
      if (name && name.includes('installations[')) {
        el.setAttribute('name', name.replace(/installations\[\d+\]/, `installations[${newIndex}]`));
      }

      if (el.classList && el.classList.contains('panel-checkbox')) {
        const panelKey = el.value;
        el.setAttribute('id', `panel_${newIndex}_${panelKey}`);
      }

      if (el.classList && el.classList.contains('tech-checkbox')) {
        const emp = el.dataset.emp || el.value;
        el.setAttribute('id', `tech_${newIndex}_${emp}`);
      }

      if (el.classList && el.classList.contains('doc-input')) {
        const doc = el.dataset.doc; // checklist / joborder
        el.setAttribute('id', `doc_${doc}_${newIndex}`);
        }

        // label upload doc (for=) update
        if (el.classList && el.classList.contains('doc-label')) {
        const doc = el.dataset.doc;
        el.setAttribute('for', `doc_${doc}_${newIndex}`);
        }

      if (el.tagName === 'LABEL') {
        const f = el.getAttribute('for');
        if (f && f.startsWith('panel_')) {
          const panelKey = f.split('_').slice(2).join('_');
          el.setAttribute('for', `panel_${newIndex}_${panelKey}`);
        }
        if (f && f.startsWith('tech_')) {
          const emp = f.split('_').slice(2).join('_');
          el.setAttribute('for', `tech_${newIndex}_${emp}`);
        }
      }
    });
  }

  const container = document.getElementById('install-container');
  const addBtn = document.getElementById('add-install');
  if (!container || !addBtn) return;

  // init tech
  document.querySelectorAll('.tech-picker').forEach(p => updateTechPicker(p));

  // tech events
  document.addEventListener('change', function(e){
    if (!e.target.classList.contains('tech-checkbox')) return;
    const picker = e.target.closest('.tech-picker');
    if (!picker) return;

    enforceMax(picker, e.target);
    updateTechPicker(picker);
  });

  document.addEventListener('input', function(e){
    if (!e.target.classList.contains('tech-search')) return;
    const picker = e.target.closest('.tech-picker');
    if (!picker) return;
    filterTechList(picker, e.target.value);
  });

  document.addEventListener('click', function(e){
    if (e.target.classList.contains('tech-clear')) {
      const picker = e.target.closest('.tech-picker');
      if (!picker) return;
      picker.querySelectorAll('.tech-checkbox').forEach(chk => chk.checked = false);
      const search = picker.querySelector('.tech-search');
      if (search) { search.value = ''; filterTechList(picker, ''); }
      updateTechPicker(picker);
    }

    if (e.target.classList.contains('chip-remove')) {
      const picker = e.target.closest('.tech-picker');
      if (!picker) return;
      const emp = e.target.dataset.emp;
      const chk = picker.querySelector(`.tech-checkbox[value="${emp}"]`);
      if (chk) chk.checked = false;
      updateTechPicker(picker);
    }

    // panel select all / clear
    const card = e.target.closest('.install-item');
    if (card) {
      if (e.target.classList.contains('panel-select-all')) {
        card.querySelectorAll('.panel-checkbox').forEach(chk => chk.checked = true);
      }
      if (e.target.classList.contains('panel-clear')) {
        card.querySelectorAll('.panel-checkbox').forEach(chk => chk.checked = false);
      }
    }
  });

  // preview docs
  document.addEventListener('change', function(e){
    if (!e.target.classList.contains('doc-input')) return;

    const card = e.target.closest('.install-item');
    if (!card) return;

    const doc = e.target.dataset.doc;
    const img = card.querySelector(`.doc-preview[data-doc="${doc}"]`);
    const file = e.target.files && e.target.files[0];

    if (!img) return;

    if (!file) {
      img.src = '';
      img.classList.add('d-none');
      return;
    }

    const url = URL.createObjectURL(file);
    img.src = url;
    img.classList.remove('d-none');
  });

  // add install
  addBtn.addEventListener('click', function(){
    const last = container.querySelector('.install-item:last-child');
    if (!last) return;

    const clone = last.cloneNode(true);
    const newIndex = container.querySelectorAll('.install-item').length;

    updateNamesAndIds(clone, newIndex);

    // reset selects
    clone.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);

    // reset inputs
    clone.querySelectorAll('input').forEach(inp => {
      if (inp.type === 'date') inp.value = todayISO();
      if (inp.type === 'checkbox') inp.checked = false;
      if (inp.type === 'text' && inp.classList.contains('tech-search')) inp.value = '';
      if (inp.type === 'file') inp.value = '';
    });

    // reset textareas
    clone.querySelectorAll('textarea').forEach(t => t.value = '');

    // reset previews
    clone.querySelectorAll('.doc-preview').forEach(img => {
      img.src = '';
      img.classList.add('d-none');
    });

    clone.querySelectorAll('.doc-filename').forEach(el => el.textContent = 'Belum ada file.');
    clone.querySelectorAll('.doc-preview').forEach(img => { img.src=''; img.classList.add('d-none'); });


    // reset tech filter + chips
    const picker = clone.querySelector('.tech-picker');
    if (picker) {
      filterTechList(picker, '');
      updateTechPicker(picker);
    }

    container.appendChild(clone);

    // update numbering
    container.querySelectorAll('.install-item').forEach((card, i) => {
      const title = card.querySelector('.card-header strong');
      if (title) title.textContent = `Instalasi #${i+1}`;
    });
  });

  // remove install
  document.addEventListener('click', function(e){
    if (!e.target.classList.contains('remove-install')) return;

    const items = container.querySelectorAll('.install-item');
    if (items.length <= 1) return;

    e.target.closest('.install-item')?.remove();

    // reindex
    const remaining = Array.from(container.querySelectorAll('.install-item'));
    remaining.forEach((card, idx) => updateNamesAndIds(card, idx));
    remaining.forEach((card, i) => {
      const title = card.querySelector('.card-header strong');
      if (title) title.textContent = `Instalasi #${i+1}`;
      const picker = card.querySelector('.tech-picker');
      if (picker) updateTechPicker(picker);
    });
  });

  // preview + tampilkan nama file
    document.addEventListener('change', function(e){
    if (!e.target.classList.contains('doc-input')) return;

    const card = e.target.closest('.install-item');
    if (!card) return;

    const doc = e.target.dataset.doc;
    const file = e.target.files && e.target.files[0];

    const filenameEl = card.querySelector(`.doc-filename[data-doc="${doc}"]`);
    const img = card.querySelector(`.doc-preview[data-doc="${doc}"]`);

    if (filenameEl) filenameEl.textContent = file ? file.name : 'Belum ada file.';

    if (!img) return;

    if (!file) {
        img.src = '';
        img.classList.add('d-none');
        return;
    }

    const url = URL.createObjectURL(file);
    img.src = url;
    img.classList.remove('d-none');
    });

    // tombol reset dokumen
    document.addEventListener('click', function(e){
    if (!e.target.classList.contains('doc-clear')) return;

    const card = e.target.closest('.install-item');
    if (!card) return;

    const doc = e.target.dataset.doc;
    const input = card.querySelector(`.doc-input[data-doc="${doc}"]`);
    const filenameEl = card.querySelector(`.doc-filename[data-doc="${doc}"]`);
    const img = card.querySelector(`.doc-preview[data-doc="${doc}"]`);

    if (input) input.value = '';
    if (filenameEl) filenameEl.textContent = 'Belum ada file.';
    if (img) { img.src = ''; img.classList.add('d-none'); }
    });


})();
</script>

@endsection
