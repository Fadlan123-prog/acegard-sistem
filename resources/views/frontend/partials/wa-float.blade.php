@php
  use App\Support\Whatsapp;

  // Nama halaman dan kategori untuk konteks pesan.
  [$waPage, $waCat] = match (true) {
    request()->routeIs('frontend.home')            => ['Beranda', null],
    request()->routeIs('frontend.service.car')     => ['Kaca Film Mobil', 'car'],
    request()->routeIs('frontend.simulator')       => ['Simulator VLT Mobil', 'car'],
    request()->routeIs('frontend.service.building') => ['Kaca Film Gedung', 'building'],
    request()->routeIs('frontend.ppf.catalog')     => ['PPF', 'ppf'],
    request()->routeIs('frontend.about')           => ['Tentang ACEGARD', null],
    request()->routeIs('frontend.contact')         => ['Kontak', null],
    request()->routeIs('frontend.faq')             => ['FAQ', null],
    default => [trim(str_replace(['- Acegard Indonesia', '– Acegard Indonesia'], '', View::yieldContent('title'))) ?: 'Situs ACEGARD', null],
  };
@endphp
<div id="agWa" data-wa="{{ Whatsapp::url() }}" data-page="{{ $waPage }}" data-cat="{{ $waCat }}">
  <div class="ag-wa-panel" id="agWaPanel" role="dialog" aria-labelledby="agWaTitle" hidden>
    <div class="ag-wa-head">
      <div>
        <strong id="agWaTitle">Konsultasi via WhatsApp</strong>
        <span>Isi singkat agar tim kami langsung paham kebutuhan Anda. Semua kolom opsional.</span>
      </div>
      <button type="button" class="ag-wa-close" data-wa-close aria-label="Tutup panel konsultasi"><iconify-icon icon="mdi:close" aria-hidden="true"></iconify-icon></button>
    </div>

    <p class="ag-wa-context" data-wa-context></p>

    <div class="ag-wa-form">
      <label>Nama<input type="text" id="agWaName" autocomplete="name" placeholder="Nama Anda"></label>
      <label>Kebutuhan
        <select id="agWaNeed">
          <option value="car">Kaca film mobil</option>
          <option value="building">Kaca film gedung atau rumah</option>
          <option value="ppf">PPF (Paint Protection Film)</option>
          <option value="other">Belum tahu, ingin konsultasi</option>
        </select>
      </label>
      <label><span data-wa-type-label>Tipe mobil</span><input type="text" id="agWaType" placeholder="" autocomplete="off"></label>
      <label>Kota<input type="text" id="agWaCity" autocomplete="address-level2" placeholder="Contoh: Tangerang"></label>
    </div>

    <a class="ag-btn ag-btn-primary whatsapp-btn" id="agWaSend" data-gtm-label="floating-whatsapp-send" target="_blank" rel="noopener"
       href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}">
      <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Kirim lewat WhatsApp
    </a>
  </div>

  {{-- Tanpa JavaScript, tombol ini tetap menjadi tautan WhatsApp biasa. --}}
  <a class="ag-wa whatsapp-btn" id="agWaBtn" data-gtm-label="floating-whatsapp" target="_blank" rel="noopener"
     href="{{ Whatsapp::url('Halo ACEGARD, saya ingin berkonsultasi.') }}" aria-label="Chat WhatsApp ACEGARD" aria-controls="agWaPanel" aria-expanded="false">
    <iconify-icon icon="mdi:whatsapp" aria-hidden="true"></iconify-icon>
    <span>WhatsApp</span>
  </a>
</div>

<script>
(function () {
  var box = document.getElementById('agWa');
  if (!box) return;
  var btn = document.getElementById('agWaBtn'), panel = document.getElementById('agWaPanel'), send = document.getElementById('agWaSend');
  var f = { name: document.getElementById('agWaName'), need: document.getElementById('agWaNeed'), type: document.getElementById('agWaType'), city: document.getElementById('agWaCity') };
  var page = box.getAttribute('data-page'), cat = box.getAttribute('data-cat');
  var KEY = 'agWaForm', SEL = 'agWaSel';

  // Pelacakan GTM hanya untuk tombol kirim. Tombol mengambang sekarang membuka panel.
  btn.classList.remove('whatsapp-btn');

  var get = function (store, k) { try { return JSON.parse(window[store].getItem(k) || 'null'); } catch (e) { return null; } };
  var set = function (store, k, v) { try { window[store].setItem(k, JSON.stringify(v)); } catch (e) {} };

  // Pilihan terakhir dari simulator atau perbandingan (dikirim lewat event dari halaman).
  var sel = get('sessionStorage', SEL);
  window.addEventListener('ag:selection', function (e) {
    sel = { page: page, text: String(e.detail || '') };
    set('sessionStorage', SEL, sel);
    update();
  });

  var needLabel = { car: 'kaca film mobil', building: 'kaca film gedung atau rumah', ppf: 'PPF (Paint Protection Film)', other: '' };
  var typeLabel = { car: 'Tipe mobil', building: 'Jenis bangunan', ppf: 'Tipe mobil', other: 'Tipe mobil atau bangunan' };
  var typePh = { car: 'Contoh: Honda Civic 2022', building: 'Contoh: rumah 2 lantai', ppf: 'Contoh: Honda Civic 2022', other: '' };

  var saved = get('localStorage', KEY) || {};
  f.name.value = saved.name || ''; f.city.value = saved.city || ''; f.type.value = saved.type || '';
  f.need.value = cat || saved.need || 'other';

  function compose() {
    var name = f.name.value.trim(), city = f.city.value.trim(), type = f.type.value.trim(), need = f.need.value;
    var m = 'Halo ACEGARD';
    if (name || city) m += ', saya ' + (name || '') + (name && city ? ' dari ' + city : (city ? 'dari ' + city : ''));
    m += '.';
    if (needLabel[need]) {
      m += ' Saya ingin berkonsultasi soal ' + needLabel[need];
      if (type) m += need === 'building' ? ' untuk ' + type : ' untuk mobil ' + type;
      m += '.';
    } else if (type) { m += ' Saya ingin berkonsultasi untuk ' + type + '.'; }
    if (sel && sel.text) m += ' Pilihan yang sedang saya lihat: ' + sel.text + (sel.page ? ' (' + sel.page + ')' : '') + '.';
    else if (page) m += ' Saya sedang melihat halaman ' + page + '.';
    return m + ' Mohon informasinya.';
  }

  function update() {
    var needV = f.need.value;
    box.querySelector('[data-wa-type-label]').textContent = typeLabel[needV];
    f.type.placeholder = typePh[needV];
    box.querySelector('[data-wa-context]').textContent = sel && sel.text
      ? 'Terakhir dilihat: ' + sel.text + (sel.page ? ' (' + sel.page + ')' : '')
      : (page ? 'Anda sedang di halaman ' + page : '');
    box.querySelector('[data-wa-context]').hidden = !(sel && sel.text) && !page;
    send.href = box.getAttribute('data-wa') + '?text=' + encodeURIComponent(compose());
    set('localStorage', KEY, { name: f.name.value.trim(), city: f.city.value.trim(), type: f.type.value.trim(), need: needV });
  }

  function open() {
    panel.hidden = false; btn.setAttribute('aria-expanded', 'true');
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event: 'whatsapp_panel_open', page_path: window.location.pathname });
    update(); setTimeout(function () { f.name.focus(); }, 30);
  }
  function close(focusBtn) {
    panel.hidden = true; btn.setAttribute('aria-expanded', 'false');
    if (focusBtn) btn.focus();
  }

  btn.addEventListener('click', function (e) { e.preventDefault(); panel.hidden ? open() : close(false); });
  box.querySelector('[data-wa-close]').addEventListener('click', function () { close(true); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) close(true); });
  document.addEventListener('click', function (e) { if (!panel.hidden && !box.contains(e.target)) close(false); });
  box.querySelectorAll('input, select').forEach(function (el) { el.addEventListener('input', update); el.addEventListener('change', update); });
  send.addEventListener('click', function () { setTimeout(function () { close(false); }, 200); });

  update();
})();
</script>
