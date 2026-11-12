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
        div.className = 'pack-line';
        div.innerHTML =
            '<select name="items['+idx+'][film_lines]['+k+'][part_id]" class="form-select form-select-sm line-part" aria-label="Part">' + opts(CAR.parts, '— Part —') + '</select>' +
            '<select name="items['+idx+'][film_lines]['+k+'][category_id]" class="form-select form-select-sm line-type" aria-label="Kategori">' + opts(CAR.types, '— Kategori —', 'name') + '</select>' +
            '<select name="items['+idx+'][film_lines]['+k+'][product_id]" class="form-select form-select-sm line-product" aria-label="Produk"><option value="">— Produk —</option></select>' +
            '<button type="button" class="btn btn-outline-danger btn-sm remove-line" aria-label="Hapus bagian"><iconify-icon icon="mdi:trash-can-outline" aria-hidden="true"></iconify-icon></button>';
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
        if (data) {
            row.querySelector('.pack-vehicle').value = data.vehicle_label || '';
            row.querySelector('.pack-plate').value = data.vehicle_plate || '';
        }
        const lines = (data && data.film_lines) ? data.film_lines : [];
        if (lines.length) { lines.forEach(l => addLine(row, l)); } else { addLine(row); }
        // Quotation mobil lama (sebelum ada rincian bagian): tampilkan isi sebelumnya sebagai acuan.
        if (data && !(data.film_lines && data.film_lines.length) && data.description) {
            row.querySelector('.pack-title').value = data.description;
            row.querySelector('.car-legacy').dataset.note = 'Sebelumnya: ' + data.description + (data.specifications ? ' · ' + data.specifications : '') + ' — rinci ulang bagian kaca di atas.';
        } else if (data && data.description) {
            row.querySelector('.pack-title').value = data.description;
        }
        updateCarSummary(row);
    }

    function updateCarSummary(row){
        const chips = row.querySelector('.car-chips'), legacy = row.querySelector('.car-legacy');
        if (!chips) return;
        // Gabungkan bagian dengan tipe+produk sama (sama seperti teks dokumen di server).
        const groups = new Map();
        row.querySelectorAll('.pack-line').forEach(l => {
            const part = l.querySelector('.line-part'), type = l.querySelector('.line-type'), prod = l.querySelector('.line-product');
            if (!part.value) return;
            const film = (type.value ? type.selectedOptions[0].text : '') + (prod.value ? ' ' + prod.selectedOptions[0].text : '');
            const key = film || '—';
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(part.selectedOptions[0].text);
        });
        chips.innerHTML = '';
        groups.forEach((parts, film) => {
            const c = document.createElement('span'); c.className = 'pack-chip';
            c.appendChild(document.createTextNode([...new Set(parts)].join(', ')));
            if (film !== '—') { const s = document.createElement('small'); s.textContent = film; c.appendChild(s); }
            chips.appendChild(c);
        });
        const note = legacy.dataset.note || '';
        legacy.hidden = !(note && groups.size === 0);
        legacy.textContent = note;
    }

    function applyMode(row){
        const car = isCar();
        row.querySelector('.car-pack').hidden = !car;
        row.querySelector('.pack-title').disabled = !car;
        row.querySelector('.pack-vehicle').disabled = !car;
        row.querySelector('.pack-plate').disabled = !car;
        row.querySelector('.generic-desc').hidden = car;
        const gspec = row.querySelector('.generic-spec'); gspec.disabled = car;
        row.querySelector('.spec-td').hidden = car;
        // Paket mobil: satu harga paket — satuan/qty/diskon baris tidak dipakai (selalu 1 paket, tanpa diskon baris).
        ['.unit-td', '.qty-td', '.disc-td'].forEach(sel => { row.querySelector(sel).hidden = car; });
        if (car) {
            row.querySelector('.item-unit').value = 'paket';
            row.querySelector('.item-qty').value = 1;
            row.querySelector('.item-disc').value = 0;
        }
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
        ['thUnit', 'thQty', 'thDisc'].forEach(id => { document.getElementById(id).hidden = car; });
        document.getElementById('thPrice').textContent = car ? 'Harga Paket (Rp)' : 'Harga Satuan (Rp)';
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
            row.querySelector('.item-qty').value                  = Number(data.quantity) || 1;   // '12.00' -> 12
            row.querySelector('.item-price').value                = Math.round(Number(data.unit_price) || 0);   // harga rupiah bulat: '2500000.00' -> 2500000
            row.querySelector('.item-disc').value                 = Number(data.discount_percent) || 0;
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
    var seedItems = {!! \Illuminate\Support\Js::from($seedItems ?? []) !!};
    if (seedItems.length) { seedItems.forEach(addRow); } else { addRow(); }
    applyModeAll();

    document.getElementById('addRowBtn').addEventListener('click', () => addRow());
    discIn.addEventListener('input', recalc);

    // Kategori berubah: kolom & katalog mengikuti kategori (mobil = paket, lainnya = pilih produk).
    document.getElementById('categorySelect')?.addEventListener('change', function(){
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
    });
})();
</script>
