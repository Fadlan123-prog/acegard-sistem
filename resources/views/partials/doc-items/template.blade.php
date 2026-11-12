{{-- Row template --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td class="desc-td">
            {{-- Mobil: satu paket = daftar bagian kaca (Part / Kategori / Produk) dengan satu harga --}}
            <div class="car-pack pack-card" hidden>
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="pack-label">Kendaraan <span class="text-muted fw-normal">(merek / model, opsional)</span></label>
                        <input type="text" name="items[__IDX__][vehicle_label]" class="form-control form-control-sm pack-vehicle" placeholder="Contoh: Toyota Avanza" maxlength="120" disabled>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="pack-label">Plat nomor</label>
                        <input type="text" name="items[__IDX__][vehicle_plate]" class="form-control form-control-sm pack-plate" placeholder="B 1234 XYZ" maxlength="20" disabled>
                    </div>
                    <div class="col-md-4 col-6">
                        <label class="pack-label">Nama paket</label>
                        <input type="text" name="items[__IDX__][description]" class="form-control form-control-sm pack-title" placeholder="Fullset / D-S-P" disabled>
                    </div>
                </div>

                <div class="pack-head">Bagian kaca &amp; film</div>
                <div class="pack-cols" aria-hidden="true"><span>Part</span><span>Kategori</span><span>Produk</span><span></span></div>
                <div class="pack-lines"></div>
                <button type="button" class="btn btn-outline-primary btn-sm add-line">
                    <iconify-icon icon="mdi:plus" class="me-1" aria-hidden="true"></iconify-icon>Tambah bagian
                </button>
                <div class="pack-chips car-chips" aria-live="polite"></div>
                <div class="pack-legacy car-legacy" hidden></div>
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
        <td class="unit-td">
            <select name="items[__IDX__][unit]" class="form-select form-select-sm item-unit">
                <option value="unit">unit</option>
                <option value="m2">m²</option>
                <option value="pcs">pcs</option>
                <option value="paket">paket</option>
                <option value="meter">meter</option>
            </select>
        </td>
        <td class="qty-td"><input type="number" name="items[__IDX__][quantity]" class="form-control form-control-sm item-qty" value="1" min="0.01" step="0.01" required></td>
        <td><div class="price-wrap"><span aria-hidden="true">Rp</span><input type="number" name="items[__IDX__][unit_price]" class="form-control form-control-sm item-price" value="0" min="0" step="1" required inputmode="numeric" aria-label="Harga"></div></td>
        <td class="disc-td"><input type="number" name="items[__IDX__][discount_percent]" class="form-control form-control-sm item-disc" value="0" min="0" max="100" step="0.5"></td>
        <td class="text-end item-subtotal-cell align-middle"><span class="item-subtotal">Rp 0</span></td>
        <td class="text-center align-middle">
            <button type="button" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0 remove-row-btn" title="Hapus">
                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
            </button>
        </td>
    </tr>
</template>
