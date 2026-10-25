@extends('dashboard.index')

@section('title', 'Tambah Complain')
@section('route-breadcrumb', 'film-complaints.index')
@section('breadcrumb', 'Tambah Complain')

@section('content')

@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <strong>Terjadi kesalahan.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Tambah Complain / Replacement</div>
                <small class="text-muted">
                    Input pergantian kaca film karena complain. Stok roll akan berkurang otomatis.
                </small>
            </div>

            <x-ui.button :href="route('film-complaints.index')" variant="secondary" size="sm">
                Kembali
            </x-ui.button>
        </div>
    @endslot

    <form action="{{ route('film-complaints.store') }}" method="POST" id="complaintForm">
        @csrf

        <div class="row">

            <div class="col-md-4 mb-3">
                <label class="form-label">Cabang <span class="text-danger">*</span></label>
                <select name="branch_id" class="form-select" required>
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ old('branch_id', session('active_branch_id')) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} - {{ $branch->city ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Jenis Complain <span class="text-danger">*</span></label>
                <select name="complaint_type" id="complaintType" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="car" {{ old('complaint_type') == 'car' ? 'selected' : '' }}>
                        Mobil
                    </option>
                    <option value="building" {{ old('complaint_type') == 'building' ? 'selected' : '' }}>
                        Gedung / Building
                    </option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Complain <span class="text-danger">*</span></label>
                <input type="date"
                       name="complaint_date"
                       class="form-control"
                       value="{{ old('complaint_date', now()->format('Y-m-d')) }}"
                       required>
            </div>

        </div>

        <div id="customerCarWrapper" class="mb-3 d-none">
            <label class="form-label">Customer Mobil <span class="text-danger">*</span></label>

            <select name="customer_id"
                    id="customerSelect"
                    class="form-control"
                    style="width: 100%;">
            </select>

            <small class="text-muted">
                Ketik nama customer, nomor kartu, plat nomor, atau nomor HP.
            </small>
        </div>

        <div id="customerBuildingWrapper" class="mb-3 d-none">
            <label class="form-label">Customer Gedung <span class="text-danger">*</span></label>
            <select name="customer_building_id" class="form-select">
                <option value="">-- Pilih Customer Gedung --</option>
                @foreach($customerBuildings as $customerBuilding)
                    <option value="{{ $customerBuilding->id }}"
                        {{ old('customer_building_id') == $customerBuilding->id ? 'selected' : '' }}>
                        {{ $customerBuilding->name }}
                        -
                        {{ $customerBuilding->address ?? '-' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-5 mb-3">
                <label class="form-label">Alasan Complain</label>
                <input type="text"
                       name="reason"
                       value="{{ old('reason') }}"
                       class="form-control"
                       placeholder="Contoh: baret, bubble, salah pasang, kaca pecah, dll">
            </div>

            <div class="col-md-7 mb-3">
                <label class="form-label">Catatan</label>
                <input type="text"
                       name="notes"
                       value="{{ old('notes') }}"
                       class="form-control"
                       placeholder="Catatan tambahan complain...">
            </div>
        </div>

        <hr>

        {{-- ITEM MOBIL --}}
        <div id="carItemsWrapper" class="d-none">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-0">Item Pergantian Mobil</h6>
                    <small class="text-muted">
                        Pilih produk, part, sisi kanan/kiri, dan scope pergantian.
                    </small>
                </div>

                <x-ui.button type="button" variant="outline" size="sm" id="addCarItem">
                    + Tambah Item Mobil
                </x-ui.button>
            </div>

            <div id="carItemsContainer">
                <div class="border rounded p-3 mb-3 car-item" data-index="0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Item Mobil</h6>
                        <x-ui.button type="button" variant="danger-outline" size="sm" class="remove-item">
                            Hapus
                        </x-ui.button>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori Produk</label>
                            <select name="items[0][category_product_id]" class="form-select car-category">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categoryProducts as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Produk / Kode</label>
                            <select name="items[0][product_id]" class="form-select car-product">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-category-id="{{ $product->category_product_id ?? $product->category_id ?? '' }}">
                                        {{ $product->name }} - {{ $product->description ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Part Kaca</label>
                            <select name="items[0][part_id]" class="form-select">
                                <option value="">-- Pilih Part --</option>
                                @foreach($parts as $part)
                                    <option value="{{ $part->id }}">
                                        {{ $part->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sisi</label>
                            <select name="items[0][replacement_side]" class="form-select">
                                <option value="not_applicable">Tidak Ada</option>
                                <option value="left">Kiri</option>
                                <option value="right">Kanan</option>
                                <option value="both">Kanan & Kiri</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Scope Pergantian</label>
                            <select name="items[0][replacement_scope]" class="form-select replacement-scope">
                                <option value="full">Full</option>
                                <option value="partial">Sebagian</option>
                                <option value="manual">Manual Meter</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Coverage %</label>
                            <input type="number"
                                   name="items[0][coverage_percent]"
                                   class="form-control coverage-percent"
                                   value="100"
                                   min="1"
                                   max="100"
                                   step="1">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Manual Meter</label>
                            <input type="number"
                                   name="items[0][used_length_meter]"
                                   class="form-control manual-meter"
                                   step="0.01"
                                   min="0"
                                   placeholder="Opsional">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Catatan Item</label>
                            <input type="text"
                                   name="items[0][notes]"
                                   class="form-control"
                                   placeholder="Opsional">
                        </div>
                    </div>

                    <small class="text-muted">
                        Jika scope = Full/Sebagian, sistem memakai estimasi part. Jika scope = Manual Meter, sistem memakai nilai manual meter.
                    </small>
                </div>
            </div>
        </div>

        {{-- ITEM GEDUNG --}}
        <div id="buildingItemsWrapper" class="d-none">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-0">Item Pergantian Gedung</h6>
                    <small class="text-muted">
                        Isi area m² atau manual meter untuk pengurangan stok.
                    </small>
                </div>

                <x-ui.button type="button" variant="outline" size="sm" id="addBuildingItem">
                    + Tambah Item Gedung
                </x-ui.button>
            </div>

            <div id="buildingItemsContainer">
                <div class="border rounded p-3 mb-3 building-item" data-index="0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Item Gedung</h6>
                        <x-ui.button type="button" variant="danger-outline" size="sm" class="remove-item">
                            Hapus
                        </x-ui.button>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategori Produk Gedung</label>
                            <select name="items[0][category_product_building_id]" class="form-select building-category">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categoryProductBuildings as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Produk Gedung / Kode</label>
                            <select name="items[0][product_building_id]" class="form-select building-product">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($productBuildings as $product)
                                    <option value="{{ $product->id }}"
                                            data-category-id="{{ $product->category_product_building_id ?? '' }}">
                                        {{ $product->name }} - {{ $product->description ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Area m²</label>
                            <input type="number"
                                   name="items[0][area_meter]"
                                   class="form-control"
                                   step="0.01"
                                   min="0"
                                   placeholder="Contoh: 5.5">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Waste %</label>
                            <input type="number"
                                   name="items[0][waste_percent]"
                                   class="form-control"
                                   value="10"
                                   step="0.01"
                                   min="0"
                                   max="100">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Manual Meter</label>
                            <input type="number"
                                   name="items[0][used_length_meter]"
                                   class="form-control"
                                   step="0.01"
                                   min="0"
                                   placeholder="Kosongkan jika pakai m²">
                        </div>

                        <div class="col-md-9 mb-3">
                            <label class="form-label">Catatan Item</label>
                            <input type="text"
                                   name="items[0][notes]"
                                   class="form-control"
                                   placeholder="Opsional">
                        </div>
                    </div>

                    <small class="text-muted">
                        Jika manual meter diisi, sistem memakai manual meter. Jika kosong, sistem menghitung dari area m² / 1.5 + waste.
                    </small>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary"
                    onclick="return confirm('Simpan complain dan kurangi stok roll?')">
                Simpan Complain
            </x-ui.button>

            <x-ui.button :href="route('film-complaints.index')" variant="ghost">
                Batal
            </x-ui.button>
        </div>

    </form>

</x-ui.card>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const complaintType = document.getElementById('complaintType');

    const customerCarWrapper = document.getElementById('customerCarWrapper');
    const customerBuildingWrapper = document.getElementById('customerBuildingWrapper');

    const customerSelect = document.getElementById('customerSelect');
    const customerBuildingSelect = document.querySelector('select[name="customer_building_id"]');

    const carItemsWrapper = document.getElementById('carItemsWrapper');
    const buildingItemsWrapper = document.getElementById('buildingItemsWrapper');

    const carItemsContainer = document.getElementById('carItemsContainer');
    const buildingItemsContainer = document.getElementById('buildingItemsContainer');

    const addCarItemBtn = document.getElementById('addCarItem');
    const addBuildingItemBtn = document.getElementById('addBuildingItem');

    function toggleComplaintType() {
        const type = complaintType.value;

        customerCarWrapper.classList.add('d-none');
        customerBuildingWrapper.classList.add('d-none');
        carItemsWrapper.classList.add('d-none');
        buildingItemsWrapper.classList.add('d-none');

        customerSelect.removeAttribute('required');
        if (customerBuildingSelect) {
            customerBuildingSelect.removeAttribute('required');
        }

        if (type === 'car') {
            customerCarWrapper.classList.remove('d-none');
            carItemsWrapper.classList.remove('d-none');
            customerSelect.setAttribute('required', 'required');
        }

        if (type === 'building') {
            customerBuildingWrapper.classList.remove('d-none');
            buildingItemsWrapper.classList.remove('d-none');

            if (customerBuildingSelect) {
                customerBuildingSelect.setAttribute('required', 'required');
            }
        }
    }

    function reindexItems(container, itemClass) {
        const items = container.querySelectorAll('.' + itemClass);

        items.forEach((item, index) => {
            item.dataset.index = index;

            item.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/items\[\d+\]/g, `items[${index}]`);
                }
            });
        });
    }

    function resetItemFields(item) {
        item.querySelectorAll('input').forEach(input => {
            if (input.type === 'number') {
                if (input.classList.contains('coverage-percent')) {
                    input.value = 100;
                } else {
                    input.value = '';
                }
            } else {
                input.value = '';
            }
        });

        item.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });
    }

    function cloneItem(container, itemClass) {
        const firstItem = container.querySelector('.' + itemClass);
        const clone = firstItem.cloneNode(true);

        resetItemFields(clone);
        container.appendChild(clone);

        reindexItems(container, itemClass);
        refreshProductFilters();
    }

    function filterCarProduct(item) {
        const category = item.querySelector('.car-category');
        const product = item.querySelector('.car-product');

        if (!category || !product) return;

        const categoryId = category.value;

        Array.from(product.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = option.getAttribute('data-category-id') !== categoryId;
        });

        if (product.selectedOptions.length && product.selectedOptions[0].hidden) {
            product.value = '';
        }
    }

    function filterBuildingProduct(item) {
        const category = item.querySelector('.building-category');
        const product = item.querySelector('.building-product');

        if (!category || !product) return;

        const categoryId = category.value;

        Array.from(product.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = option.getAttribute('data-category-id') !== categoryId;
        });

        if (product.selectedOptions.length && product.selectedOptions[0].hidden) {
            product.value = '';
        }
    }

    function refreshProductFilters() {
        document.querySelectorAll('.car-item').forEach(filterCarProduct);
        document.querySelectorAll('.building-item').forEach(filterBuildingProduct);
    }

    complaintType.addEventListener('change', function () {
        toggleComplaintType();

        if (window.jQuery && $('#customerSelect').data('select2')) {
            $('#customerSelect').val(null).trigger('change');
        }
    });

    addCarItemBtn.addEventListener('click', function () {
        cloneItem(carItemsContainer, 'car-item');
    });

    addBuildingItemBtn.addEventListener('click', function () {
        cloneItem(buildingItemsContainer, 'building-item');
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-item')) {
            const item = event.target.closest('.car-item, .building-item');
            const container = item.parentElement;

            if (container.children.length <= 1) {
                alert('Minimal harus ada 1 item.');
                return;
            }

            const isCar = item.classList.contains('car-item');
            item.remove();

            reindexItems(container, isCar ? 'car-item' : 'building-item');
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('car-category')) {
            filterCarProduct(event.target.closest('.car-item'));
        }

        if (event.target.classList.contains('building-category')) {
            filterBuildingProduct(event.target.closest('.building-item'));
        }

        if (event.target.classList.contains('replacement-scope')) {
            const item = event.target.closest('.car-item');
            const coverage = item.querySelector('.coverage-percent');
            const manual = item.querySelector('.manual-meter');

            if (event.target.value === 'full') {
                coverage.value = 100;
                manual.value = '';
            }

            if (event.target.value === 'manual') {
                coverage.value = 100;
            }
        }
    });

    toggleComplaintType();
    refreshProductFilters();

    $(document).ready(function () {
        function escapeHtml(value) {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function extractValue(text, label) {
            const regex = new RegExp(label + ':\\s*([^|]+)', 'i');
            const match = String(text || '').match(regex);

            return match ? match[1].trim() : '-';
        }

        function normalizeCustomer(customer) {
            const rawText = customer.text || '';

            let name = customer.name;
            let card = customer.card_number;
            let plat = customer.plat_number;
            let phone = customer.phone_number;
            let branch = customer.branch_name;

            /*
            |--------------------------------------------------------------------------
            | Fallback kalau backend masih mengirim text panjang:
            | Orla Benton | Kartu: 20260001 | Plat: 296 | HP: xxx | Cabang: xxx
            |--------------------------------------------------------------------------
            */
            if (!name) {
                name = rawText.split('|')[0]?.trim() || '-';
            }

            if (!card) {
                card = extractValue(rawText, 'Kartu');
            }

            if (!plat) {
                plat = extractValue(rawText, 'Plat');
            }

            if (!phone) {
                phone = extractValue(rawText, 'HP');
            }

            if (!branch) {
                branch = extractValue(rawText, 'Cabang');
            }

            return {
                id: customer.id,
                text: name,
                name: name,
                card_number: card,
                plat_number: plat,
                phone_number: phone,
                branch_name: branch,
            };
        }

        function formatCustomerResult(customer) {
            if (customer.loading) {
                return customer.text;
            }

            customer = normalizeCustomer(customer);

            const name = escapeHtml(customer.name);
            const card = escapeHtml(customer.card_number);
            const plat = escapeHtml(customer.plat_number);
            const phone = escapeHtml(customer.phone_number);
            const branch = escapeHtml(customer.branch_name);
            const avatar = name.charAt(0).toUpperCase();

            return $(`
                <div class="customer-option">
                    <div class="customer-option-top">
                        <div class="customer-avatar">${avatar}</div>

                        <div class="customer-main">
                            <div class="customer-name">${name}</div>

                            <div class="customer-meta">
                                <span>Kartu: ${card}</span>
                                <span>Plat: ${plat}</span>
                                <span>HP: ${phone}</span>
                            </div>
                        </div>
                    </div>

                    <div class="customer-branch">
                        Cabang: ${branch}
                    </div>
                </div>
            `);
        }

        function formatCustomerSelection(customer) {
            if (!customer.id) {
                return customer.text;
            }

            customer = normalizeCustomer(customer);

            const name = escapeHtml(customer.name);
            const card = escapeHtml(customer.card_number);
            const plat = escapeHtml(customer.plat_number);
            const phone = escapeHtml(customer.phone_number);
            const branch = escapeHtml(customer.branch_name);

            return $(`
                <div class="customer-selected">
                    <div class="customer-selected-name">${name}</div>

                    <div class="customer-selected-meta">
                        <span>Kartu: ${card}</span>
                        <span>Plat: ${plat}</span>
                        <span>HP: ${phone}</span>
                        <span>Cabang: ${branch}</span>
                    </div>
                </div>
            `);
        }

        if ($.fn.select2) {
            $('#customerSelect').select2({
                placeholder: '-- Ketik nama / kartu / plat / HP customer --',
                allowClear: true,
                width: '100%',
                minimumInputLength: 2,

                templateResult: formatCustomerResult,
                templateSelection: formatCustomerSelection,

                escapeMarkup: function (markup) {
                    return markup;
                },

                ajax: {
                    url: "{{ route('film-complaints.search-customers') }}",
                    dataType: 'json',
                    delay: 300,
                    cache: false,

                    data: function (params) {
                        return {
                            q: params.term,
                            branch_id: $('select[name="branch_id"]').val(),
                            complaint_type: $('select[name="complaint_type"]').val()
                        };
                    },

                    processResults: function (data) {
                        return {
                            results: (data.results || []).map(function (customer) {
                                return normalizeCustomer(customer);
                            })
                        };
                    }
                },

                language: {
                    inputTooShort: function () {
                        return 'Ketik minimal 2 karakter untuk mencari customer';
                    },
                    searching: function () {
                        return 'Mencari customer...';
                    },
                    noResults: function () {
                        return 'Customer tidak ditemukan';
                    },
                    errorLoading: function () {
                        return 'Data customer gagal dimuat';
                    }
                }
            });

            $('select[name="branch_id"], select[name="complaint_type"]').on('change', function () {
                $('#customerSelect').val(null).empty().trigger('change');
            });
        } else {
            console.error('Select2 belum ter-load. Cek jQuery dan Select2 JS.');
        }
    });
});
</script>
@endsection
