@extends('dashboard.index')

@section('title', 'Tambah Cabang')
@section('route-breadcrumb', 'branches.index')
@section('breadcrumb', 'Tambah Cabang')

@push('page-styles')
<style>
/* ── Layout ─────────────────────────────────────────────────── */
.br-layout {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 24px;
  align-items: start;
}
@media (max-width: 768px) {
  .br-layout { grid-template-columns: 1fr; }
  .br-nav    { display: none; }
}

/* ── Sidebar nav ─────────────────────────────────────────────── */
.br-nav {
  position: sticky; top: 80px;
  background: var(--white);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 8px; list-style: none; margin: 0;
}
.br-nav li a {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px; border-radius: 8px;
  font-size: 13px; font-weight: 500;
  color: var(--neutral-600); text-decoration: none;
  transition: background .15s, color .15s;
}
.br-nav li a .bn-icon {
  width: 28px; height: 28px; border-radius: 6px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; background: var(--neutral-100); color: var(--neutral-500);
  flex-shrink: 0; transition: background .15s, color .15s;
}
.br-nav li a:hover,
.br-nav li a.is-active               { background: var(--primary-light); color: var(--primary-600); }
.br-nav li a:hover .bn-icon,
.br-nav li a.is-active .bn-icon      { background: var(--primary-600); color: #fff; }

/* ── Section cards ───────────────────────────────────────────── */
.br-section {
  background: var(--white);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 20px;
}
.br-section-head {
  display: flex; align-items: center; gap: 14px;
  padding: 16px 20px;
  background: var(--neutral-50);
  border-bottom: 1px solid var(--border-color);
}
.br-section-head .sec-icon {
  width: 38px; height: 38px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; flex-shrink: 0;
  background: var(--primary-light); color: var(--primary-600);
}
.br-section-head .sec-icon.si-green  { background: rgba(34,197,94,.12);  color: #16a34a; }
.br-section-head .sec-icon.si-orange { background: rgba(249,115,22,.12); color: #ea580c; }
.br-section-head .sec-icon.si-violet { background: rgba(139,92,246,.12); color: #7c3aed; }
.br-section-head .sec-icon.si-red    { background: rgba(239,68,68,.12);  color: #ef4444; }
.br-section-head .sec-icon.si-teal   { background: rgba(20,184,166,.12); color: #0d9488; }
.br-section-head .sec-title { font-size: 14px; font-weight: 600; margin: 0; color: var(--neutral-900); }
.br-section-head .sec-desc  { font-size: 12px; color: var(--neutral-500); margin: 0; }
.br-section-body { padding: 20px; }

/* ── Day-picker toggle buttons ───────────────────────────────── */
.day-wrap { display: flex; flex-wrap: wrap; gap: 6px; }
.day-wrap .btn-check + label {
  min-width: 76px; text-align: center;
  font-size: 12px; font-weight: 500;
  padding: 6px 10px; border-radius: 6px;
}

/* ── Status toggle ───────────────────────────────────────────── */
.status-wrap { display: flex; gap: 8px; }
.status-wrap input[type="radio"] { display: none; }
.status-wrap label {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 16px; border-radius: 8px; cursor: pointer;
  font-size: 13px; font-weight: 500;
  border: 1px solid var(--border-color);
  color: var(--neutral-600); background: var(--white);
  transition: background .15s, color .15s, border-color .15s;
}
.status-wrap input[value="active"]:checked   + label { background: rgba(34,197,94,.12);  border-color: #16a34a; color: #16a34a; }
.status-wrap input[value="inactive"]:checked + label { background: rgba(239,68,68,.12);  border-color: #ef4444; color: #ef4444; }

/* ── Action bar ──────────────────────────────────────────────── */
.br-action-bar {
  position: sticky; bottom: 0; z-index: 100;
  background: var(--white);
  border-top: 1px solid var(--border-color);
  padding: 14px 20px;
  display: flex; align-items: center; justify-content: flex-end; gap: 10px;
  margin-top: -1px;
}

/* ── Form helper ─────────────────────────────────────────────── */
.field-hint { font-size: 11.5px; color: var(--neutral-500); margin-top: 4px; }

/* ── Dark mode ───────────────────────────────────────────────── */
[data-theme="dark"] .br-section,
[data-theme="dark"] .br-nav,
[data-theme="dark"] .br-action-bar    { background: var(--dark-2); }
[data-theme="dark"] .br-section-head  { background: var(--dark-3); }
[data-theme="dark"] .br-nav li a      { color: var(--neutral-400); }
[data-theme="dark"] .br-nav li a .bn-icon { background: var(--dark-3); color: var(--neutral-400); }
[data-theme="dark"] .status-wrap label { background: var(--dark-3); border-color: var(--dark-1); }
[data-theme="dark"] .br-section-head .sec-title { color: var(--neutral-100); }
</style>
@endpush

@section('content')

@if ($errors->any())
  <x-ui.alert type="danger" class="mb-3">
    <strong>Mohon periksa kembali form berikut:</strong>
    <ul class="mb-0 mt-1">
      @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </x-ui.alert>
@endif

<div class="br-layout">

  {{-- ── Sidebar nav ── --}}
  <ul class="br-nav" id="brNav">
    <li><a href="#sec-identitas" class="is-active">
      <span class="bn-icon"><iconify-icon icon="mdi:office-building-outline"></iconify-icon></span> Identitas
    </a></li>
    <li><a href="#sec-kontak">
      <span class="bn-icon"><iconify-icon icon="mdi:phone-outline"></iconify-icon></span> Kontak
    </a></li>
    <li><a href="#sec-alamat">
      <span class="bn-icon"><iconify-icon icon="mdi:map-marker-outline"></iconify-icon></span> Alamat
    </a></li>
    <li><a href="#sec-manajemen">
      <span class="bn-icon"><iconify-icon icon="mdi:account-star-outline"></iconify-icon></span> Manajemen
    </a></li>
    <li><a href="#sec-operasional">
      <span class="bn-icon"><iconify-icon icon="mdi:clock-outline"></iconify-icon></span> Operasional
    </a></li>
    <li><a href="#sec-catatan">
      <span class="bn-icon"><iconify-icon icon="mdi:note-text-outline"></iconify-icon></span> Catatan
    </a></li>
  </ul>

  <div>
    <form action="{{ route('branches.store') }}" method="POST" novalidate>
      @csrf

      {{-- ── Section 1: Identitas ── --}}
      <div id="sec-identitas" class="br-section">
        <div class="br-section-head">
          <div class="sec-icon"><iconify-icon icon="mdi:office-building-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Informasi Dasar</p>
            <p class="sec-desc">Nama, kode unik, tipe, dan status operasional cabang</p>
          </div>
        </div>
        <div class="br-section-body">
          <div class="row g-3">

            {{-- Status (top — most impactful decision) --}}
            <div class="col-12">
              <label class="form-label">Status Cabang <span class="text-danger">*</span></label>
              <div class="status-wrap">
                <input type="radio" name="status" id="status_active" value="active"
                       {{ old('status', 'active') === 'active' ? 'checked' : '' }}>
                <label for="status_active"><iconify-icon icon="mdi:checkbox-marked-circle-outline"></iconify-icon> Aktif</label>

                <input type="radio" name="status" id="status_inactive" value="inactive"
                       {{ old('status') === 'inactive' ? 'checked' : '' }}>
                <label for="status_inactive"><iconify-icon icon="mdi:cancel"></iconify-icon> Nonaktif</label>
              </div>
              @error('status')<div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>@enderror
            </div>

            {{-- Name --}}
            <div class="col-md-8">
              <label for="name" class="form-label">Nama Cabang <span class="text-danger">*</span></label>
              <input type="text" name="name" id="name"
                     class="form-control @error('name') is-invalid @enderror"
                     value="{{ old('name') }}"
                     placeholder="cth. Acegard Tangerang">
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Code --}}
            <div class="col-md-4">
              <label for="code" class="form-label">Kode Cabang</label>
              <input type="text" name="code" id="code"
                     class="form-control text-uppercase @error('code') is-invalid @enderror"
                     value="{{ old('code') }}"
                     placeholder="cth. ACG-TNG"
                     maxlength="30">
              @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div class="field-hint">Unik, digunakan sebagai prefix di dokumen & laporan.</div>
            </div>

            {{-- Type --}}
            <div class="col-md-6">
              <label for="type" class="form-label">Tipe Cabang</label>
              <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                <option value="">— Pilih Tipe —</option>
                <option value="head_office"    {{ old('type') === 'head_office'    ? 'selected' : '' }}>Head Office</option>
                <option value="branch"         {{ old('type') === 'branch'         ? 'selected' : '' }}>Cabang</option>
                <option value="warehouse"      {{ old('type') === 'warehouse'      ? 'selected' : '' }}>Gudang</option>
                <option value="service_center" {{ old('type') === 'service_center' ? 'selected' : '' }}>Service Center</option>
              </select>
              @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Opening date --}}
            <div class="col-md-6">
              <label for="opening_date" class="form-label">Tanggal Berdiri</label>
              <input type="date" name="opening_date" id="opening_date"
                     class="form-control @error('opening_date') is-invalid @enderror"
                     value="{{ old('opening_date') }}">
              @error('opening_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

          </div>
        </div>
      </div>

      {{-- ── Section 2: Kontak ── --}}
      <div id="sec-kontak" class="br-section">
        <div class="br-section-head">
          <div class="sec-icon si-green"><iconify-icon icon="mdi:phone-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Informasi Kontak</p>
            <p class="sec-desc">Nomor telepon, WhatsApp, dan email cabang</p>
          </div>
        </div>
        <div class="br-section-body">
          <div class="row g-3">

            <div class="col-md-4">
              <label for="phone" class="form-label">Telepon</label>
              <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">
                  <iconify-icon icon="mdi:phone-outline"></iconify-icon>
                </span>
                <input type="text" name="phone" id="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" placeholder="021-5556677">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="col-md-4">
              <label for="whatsapp" class="form-label">WhatsApp</label>
              <div class="input-group">
                <span class="input-group-text" style="font-size:13px; color:#25d366;">
                  <iconify-icon icon="mdi:whatsapp"></iconify-icon>
                </span>
                <input type="text" name="whatsapp" id="whatsapp"
                       class="form-control @error('whatsapp') is-invalid @enderror"
                       value="{{ old('whatsapp') }}" placeholder="+628xxxxxxxxxx">
                @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="col-md-4">
              <label for="email" class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text" style="font-size:13px;">
                  <iconify-icon icon="mdi:email-outline"></iconify-icon>
                </span>
                <input type="email" name="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="cabang@acegard.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- ── Section 3: Alamat ── --}}
      <div id="sec-alamat" class="br-section">
        <div class="br-section-head">
          <div class="sec-icon si-orange"><iconify-icon icon="mdi:map-marker-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Alamat Cabang</p>
            <p class="sec-desc">Lokasi fisik cabang secara lengkap</p>
          </div>
        </div>
        <div class="br-section-body">
          <div class="row g-3">

            <div class="col-md-6">
              <label for="city" class="form-label">Kota / Kabupaten <span class="text-danger">*</span></label>
              <input type="text" name="city" id="city"
                     class="form-control @error('city') is-invalid @enderror"
                     value="{{ old('city') }}" placeholder="Tangerang">
              @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label for="province" class="form-label">Provinsi</label>
              <input type="text" name="province" id="province"
                     class="form-control @error('province') is-invalid @enderror"
                     value="{{ old('province') }}" placeholder="Banten">
              @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label for="district" class="form-label">Kecamatan</label>
              <input type="text" name="district" id="district"
                     class="form-control @error('district') is-invalid @enderror"
                     value="{{ old('district') }}" placeholder="Cipondoh">
              @error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
              <label for="postal_code" class="form-label">Kode Pos</label>
              <input type="text" name="postal_code" id="postal_code"
                     class="form-control @error('postal_code') is-invalid @enderror"
                     value="{{ old('postal_code') }}" placeholder="15146" maxlength="10">
              @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
              <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
              <textarea name="address" id="address" rows="3"
                        class="form-control @error('address') is-invalid @enderror"
                        placeholder="Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog...">{{ old('address') }}</textarea>
              @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

          </div>
        </div>
      </div>

      {{-- ── Section 4: Manajemen ── --}}
      <div id="sec-manajemen" class="br-section">
        <div class="br-section-head">
          <div class="sec-icon si-violet"><iconify-icon icon="mdi:account-star-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Manajemen Cabang</p>
            <p class="sec-desc">Penanggungjawab operasional dan administrasi cabang</p>
          </div>
        </div>
        <div class="br-section-body">
          <div class="row g-3">

            <div class="col-md-6">
              <label for="manager_id" class="form-label">Branch Manager</label>
              <select name="manager_id" id="manager_id"
                      class="form-select @error('manager_id') is-invalid @enderror">
                <option value="">— Pilih Karyawan —</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}" {{ old('manager_id') == $e->id ? 'selected' : '' }}>
                    {{ $e->name }}@if($e->job_position) · {{ $e->job_position }}@endif
                  </option>
                @endforeach
              </select>
              @error('manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div class="field-hint">Bertanggung jawab atas seluruh operasional cabang.</div>
            </div>

            <div class="col-md-6">
              <label for="pic_id" class="form-label">PIC Administrasi</label>
              <select name="pic_id" id="pic_id"
                      class="form-select @error('pic_id') is-invalid @enderror">
                <option value="">— Pilih Karyawan —</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}" {{ old('pic_id') == $e->id ? 'selected' : '' }}>
                    {{ $e->name }}@if($e->job_position) · {{ $e->job_position }}@endif
                  </option>
                @endforeach
              </select>
              @error('pic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div class="field-hint">Person in Charge untuk administrasi dan koordinasi.</div>
            </div>

          </div>
        </div>
      </div>

      {{-- ── Section 5: Operasional ── --}}
      <div id="sec-operasional" class="br-section">
        <div class="br-section-head">
          <div class="sec-icon si-teal"><iconify-icon icon="mdi:clock-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Jam & Hari Operasional</p>
            <p class="sec-desc">Jadwal buka/tutup dan hari kerja cabang</p>
          </div>
        </div>
        <div class="br-section-body">
          <div class="row g-3">

            <div class="col-md-4">
              <label for="opening_time" class="form-label">Jam Buka</label>
              <input type="time" name="opening_time" id="opening_time"
                     class="form-control @error('opening_time') is-invalid @enderror"
                     value="{{ old('opening_time', '08:00') }}">
              @error('opening_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label for="closing_time" class="form-label">Jam Tutup</label>
              <input type="time" name="closing_time" id="closing_time"
                     class="form-control @error('closing_time') is-invalid @enderror"
                     value="{{ old('closing_time', '17:00') }}">
              @error('closing_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
              <label class="form-label">Hari Kerja</label>
              @php
                $days        = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                $selectedDays = old('working_days', ['Senin','Selasa','Rabu','Kamis','Jumat']);
              @endphp
              <div class="day-wrap">
                @foreach($days as $day)
                  <input class="btn-check" type="checkbox" name="working_days[]"
                         value="{{ $day }}" id="day_{{ $day }}"
                         {{ in_array($day, $selectedDays) ? 'checked' : '' }}
                         autocomplete="off">
                  <label class="btn btn-outline-primary" for="day_{{ $day }}">{{ $day }}</label>
                @endforeach
              </div>
              @error('working_days')<div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>@enderror
              <div class="field-hint mt-1">Centang hari-hari yang menjadi hari operasional cabang ini.</div>
            </div>

          </div>
        </div>
      </div>

      {{-- ── Section 6: Catatan + Action Bar ── --}}
      <div id="sec-catatan" class="br-section">
        <div class="br-section-head">
          <div class="sec-icon si-red"><iconify-icon icon="mdi:note-text-outline"></iconify-icon></div>
          <div>
            <p class="sec-title">Catatan Internal</p>
            <p class="sec-desc">Informasi tambahan yang tidak ditampilkan ke publik</p>
          </div>
        </div>
        <div class="br-section-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="notes" class="form-label">Catatan</label>
              <textarea name="notes" id="notes" rows="4"
                        class="form-control @error('notes') is-invalid @enderror"
                        placeholder="Catatan internal tentang cabang ini...">{{ old('notes') }}</textarea>
              @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
        <div class="br-action-bar">
          <x-ui.button :href="route('branches.index')" variant="secondary">Batal</x-ui.button>
          <x-ui.button type="submit" variant="primary" class="px-4">
            <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon> Simpan &amp; Tambah User
          </x-ui.button>
        </div>
      </div>

    </form>
  </div>
</div>
@endsection

@push('page-scripts')
<script>
(function () {
  // Auto-uppercase kode cabang
  const code = document.getElementById('code');
  code?.addEventListener('input', function () { this.value = this.value.toUpperCase(); });

  // Scroll spy
  const navLinks = document.querySelectorAll('#brNav a');
  const sections = document.querySelectorAll('.br-section[id]');
  const obs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        navLinks.forEach(a => a.classList.remove('is-active'));
        document.querySelector(`#brNav a[href="#${entry.target.id}"]`)?.classList.add('is-active');
      }
    });
  }, { threshold: 0.3 });
  sections.forEach(s => obs.observe(s));
})();
</script>
@endpush
