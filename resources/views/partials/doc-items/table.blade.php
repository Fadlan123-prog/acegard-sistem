                <div class="table-responsive mb-3">
                    <table class="table table-bordered items-tbl mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="min-width:260px" id="thDesc">Deskripsi / Produk</th>
                                <th style="min-width:150px" id="thSpec">Spesifikasi</th>
                                <th style="width:80px" id="thUnit">Satuan</th>
                                <th style="width:70px" id="thQty">Qty</th>
                                <th style="width:130px" id="thPrice">Harga Satuan (Rp)</th>
                                <th style="width:70px" id="thDisc">Disc%</th>
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

