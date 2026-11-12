@extends('dashboard.index')

@section('title', 'Manajemen Produk')
@section('route-breadcrumb', 'product-management.index')
@section('breadcrumb', 'Manajemen Produk')

@section('content')
@php
    $tabs = ['car' => ['Kaca Film Mobil', 'mdi:car-outline'], 'building' => ['Kaca Film Gedung', 'mdi:office-building-outline'], 'ppf' => ['PPF', 'mdi:shield-car']];
@endphp

<style>
    .pm-tabs { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1rem; }
    .pm-tab { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem .95rem; border:1px solid var(--ui-border, #e5e7eb); border-radius:999px; font-size:13.5px; font-weight:600; color:var(--ui-text-2, #4b5563); text-decoration:none; background:var(--ui-surface, #fff); }
    .pm-tab:hover { border-color:#2563EB; color:#2563EB; }
    .pm-tab.is-active { background:#2563EB; border-color:#2563EB; color:#fff; }
    .pm-cat { border:1px solid var(--ui-border, #e5e7eb); border-left:4px solid #2563EB; border-radius:12px; margin-bottom:1rem; background:var(--ui-surface, #fff); }
    .pm-cat-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; padding:.85rem 1rem; }
    .pm-cat-title { font-weight:700; font-size:15px; }
    .pm-meta { font-size:12px; color:var(--ui-text-2, #6b7280); }
    .pm-actions { display:flex; gap:.4rem; flex-wrap:wrap; }
    .pm-body { padding:0 1rem 1rem; }
    .pm-table th { font-size:12px; text-transform:uppercase; letter-spacing:.03em; color:var(--ui-text-2, #6b7280); }
    .pm-edit { background:var(--neutral-50, #f8fafc); border-radius:10px; padding:.75rem; margin-bottom:.75rem; }
    .pm-used { font-size:11.5px; color:#92400e; }
    .pm-comm { background:var(--neutral-50, #f8fafc); border:1px solid var(--ui-border, #e5e7eb); border-radius:10px; padding:.75rem .9rem; margin-bottom:.9rem; }
    .pm-comm.is-missing { border-color:#fcd34d; background:#fffbeb; }
    .pm-rate-chips { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.6rem; }
    .pm-rate-chip { display:inline-flex; gap:.4rem; align-items:center; background:#eff6ff; color:#1d4ed8; border-radius:999px; padding:3px 11px; font-size:12.5px; }
    .pm-rate-chip strong { font-variant-numeric:tabular-nums; color:#1e3a8a; }
    .pm-rate-chip.is-zero { background:#f3f4f6; color:#6b7280; }
    .pm-rate-chip.is-zero strong { color:#6b7280; }
    .pm-warn { margin-top:.6rem; font-size:12.5px; color:#92400e; display:flex; gap:.4rem; align-items:center; }
</style>

<nav class="pm-tabs" aria-label="Jenis produk">
    @foreach($tabs as $key => [$label, $icon])
        <a href="{{ route('product-management.index', ['tab' => $key]) }}" class="pm-tab {{ $tab === $key ? 'is-active' : '' }}" @if($tab === $key) aria-current="page" @endif>
            <iconify-icon icon="{{ $icon }}" aria-hidden="true"></iconify-icon>{{ $label }}
        </a>
    @endforeach
</nav>

@if($errors->any())
    <x-ui.alert type="danger" class="mb-3" :dismissible="false">{{ $errors->first() }}</x-ui.alert>
@endif

@if($tab === 'ppf')
    <x-ui.card>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <div class="fw-semibold">Produk PPF</div>
                <div class="pm-meta">Merek, seri, ketebalan, finishing, dan garansi bawaan. Dipakai di form customer PPF dan quotation.</div>
            </div>
            <x-ui.button :href="route('ppf-products.create')" variant="primary">
                <iconify-icon icon="mdi:plus" class="me-1" aria-hidden="true"></iconify-icon>Tambah Produk PPF
            </x-ui.button>
        </div>

        <div class="table-responsive">
            <table class="table pm-table align-middle mb-0">
                <thead><tr><th>Produk</th><th>Merek / Seri</th><th>Ketebalan</th><th>Finishing</th><th>Garansi</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($ppfProducts as $p)
                    @php $used = $ppfUsage[$p->id] ?? []; @endphp
                    <tr>
                        <td class="fw-semibold">{{ $p->name }}@if($used)<div class="pm-used">Dipakai: {{ \App\Services\CatalogUsage::describe($used) }}</div>@endif</td>
                        <td>{{ $p->brand }}@if($p->series) <span class="text-muted">/ {{ $p->series }}</span>@endif</td>
                        <td>{{ $p->thickness_microns ? $p->thickness_microns . ' µm' : '—' }}</td>
                        <td>{{ $p->finish ? ucfirst($p->finish) : '—' }}</td>
                        <td>{{ $p->default_warranty_years }} tahun</td>
                        <td class="text-end">
                            <div class="pm-actions justify-content-end">
                                <x-ui.button :href="route('ppf-products.edit', $p)" variant="outline" size="sm">Edit</x-ui.button>
                                <form method="POST" action="{{ route('ppf-products.destroy', $p) }}" onsubmit="return confirm('Hapus produk PPF {{ e($p->name) }}?')">
                                    @csrf @method('DELETE')
                                    <x-ui.button type="submit" variant="danger-outline" size="sm" :disabled="(bool) $used">Hapus</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4"><x-ui.empty-state title="Belum ada produk PPF" description="Tambahkan produk PPF pertama." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
@else
    @php $cfg = $types[$tab]; @endphp

    <x-ui.card class="mb-4">
        <div class="fw-semibold mb-2">Tambah Kategori {{ $cfg['label'] }}</div>
        <form method="POST" action="{{ route('product-management.categories.store', $tab) }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-6">
                <label class="ui-label" for="new-cat-name">Nama kategori (tipe film)</label>
                <input id="new-cat-name" type="text" name="name" class="form-control" placeholder="Contoh: NOTCH UV 400" required maxlength="100" value="{{ old('name') }}">
            </div>
            <div class="col-md-3 col-6">
                <label class="ui-label" for="new-cat-dur">Garansi (tahun)</label>
                <input id="new-cat-dur" type="number" name="duration" class="form-control" min="0" max="20" required value="{{ old('duration', 5) }}">
            </div>
            <div class="col-md-3 col-6"><x-ui.button type="submit" variant="primary" class="w-100">Tambah Kategori</x-ui.button></div>
        </form>
    </x-ui.card>

    @forelse($categories as $cat)
        <section class="pm-cat" aria-labelledby="cat-{{ $cat->id }}">
            <div class="pm-cat-head">
                <div>
                    <div class="pm-cat-title" id="cat-{{ $cat->id }}">{{ $cat->name }}</div>
                    <div class="pm-meta">
                        ID {{ $cat->id }} &middot; Garansi {{ $cat->duration }} tahun &middot; {{ $cat->catalogProducts->count() }} produk
                        @if($cat->usageSummary)<span class="pm-used"> &middot; Dipakai: {{ \App\Services\CatalogUsage::describe($cat->usageSummary) }}</span>@endif
                    </div>
                </div>
                <div class="pm-actions">
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#edit-cat-{{ $cat->id }}" aria-expanded="false" aria-controls="edit-cat-{{ $cat->id }}">
                        <iconify-icon icon="mdi:pencil-outline" aria-hidden="true"></iconify-icon> Edit
                    </button>
                    <form method="POST" action="{{ route('product-management.categories.destroy', [$tab, $cat->id]) }}" onsubmit="return confirm('Hapus kategori {{ e($cat->name) }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm" type="submit" @disabled($cat->catalogProducts->isNotEmpty() || $cat->usageSummary)>
                            <iconify-icon icon="mdi:trash-can-outline" aria-hidden="true"></iconify-icon> Hapus
                        </button>
                    </form>
                </div>
            </div>

            <div class="pm-body">
                @php
                    $rates = $cat->commissionRates;
                    $missing = collect($rates)->every(fn ($r) => $r['amount'] <= 0);
                    $fmt = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
                @endphp
                <div class="pm-comm {{ $missing ? 'is-missing' : '' }}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold" style="font-size:13.5px">
                                <iconify-icon icon="mdi:cash-multiple" aria-hidden="true"></iconify-icon>
                                {{ $tab === 'car' ? 'Komisi marketing per mobil' : 'Komisi per meter' }}
                            </div>
                            <div class="pm-meta">
                                @if($tab === 'car')
                                    Dibayar ke marketing saat invoice lunas. Mobil dengan panoramic: Fullset tetap sama, D/S/P menjadi 2× D/S/P.
                                @else
                                    Dibayar saat invoice gedung lunas. Teknisi: dikali meter yang dikerjakan masing-masing. Marketing: dikali total meter pekerjaan.
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#edit-comm-{{ $cat->id }}" aria-expanded="{{ $missing ? 'true' : 'false' }}" aria-controls="edit-comm-{{ $cat->id }}">
                            <iconify-icon icon="mdi:cash-edit" aria-hidden="true"></iconify-icon> Atur Tarif
                        </button>
                    </div>

                    <div class="pm-rate-chips">
                        @foreach($rates as $key => $r)
                            <span class="pm-rate-chip {{ $r['amount'] <= 0 ? 'is-zero' : '' }}">
                                {{ $r['label'] }}
                                <strong>{{ $fmt($r['amount']) }}{{ $tab === 'building' ? ' / meter' : '' }}</strong>
                            </span>
                        @endforeach
                    </div>
                    @if($missing)
                        <div class="pm-warn" role="alert">
                            <iconify-icon icon="mdi:alert-outline" aria-hidden="true"></iconify-icon>
                            Tarif komisi belum diatur — pekerjaan kategori ini belum menghasilkan komisi. Klik <strong>Atur Tarif</strong>.
                        </div>
                    @endif

                    <div class="collapse {{ $missing ? 'show' : '' }}" id="edit-comm-{{ $cat->id }}">
                        <form method="POST" action="{{ route('product-management.categories.commission', [$tab, $cat->id]) }}" class="row g-2 align-items-end mt-1">
                            @csrf @method('PUT')
                            @foreach($rates as $key => $r)
                                <div class="col-md-4 col-sm-6">
                                    <label class="ui-label" for="rate-{{ $cat->id }}-{{ $key }}">
                                        {{ $r['label'] }}{{ $tab === 'building' ? ' (Rp per meter)' : ' (Rp per mobil)' }}
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input id="rate-{{ $cat->id }}-{{ $key }}" type="text" inputmode="numeric" name="{{ $key }}" class="form-control"
                                               value="{{ old($key, $r['amount']) }}" required aria-describedby="rate-help-{{ $cat->id }}">
                                    </div>
                                </div>
                            @endforeach
                            <div class="col-md-4 col-sm-6"><x-ui.button type="submit" variant="primary" size="sm" class="w-100">Simpan Tarif</x-ui.button></div>
                            <div class="col-12 pm-meta" id="rate-help-{{ $cat->id }}">
                                Ketik angka saja, contoh <strong>120000</strong> (titik ribuan boleh). Isi <strong>0</strong> bila tidak ada komisi untuk jenis itu.
                                Perubahan berlaku untuk komisi yang dirilis setelah ini; komisi yang sudah dirilis tidak berubah.
                            </div>
                        </form>
                    </div>
                </div>

                <div class="collapse" id="edit-cat-{{ $cat->id }}">
                    <form method="POST" action="{{ route('product-management.categories.update', [$tab, $cat->id]) }}" class="pm-edit row g-2 align-items-end">
                        @csrf @method('PUT')
                        <div class="col-md-6"><label class="ui-label">Nama kategori</label><input type="text" name="name" class="form-control form-control-sm" value="{{ $cat->name }}" required maxlength="100"></div>
                        <div class="col-md-3 col-6"><label class="ui-label">Garansi (tahun)</label><input type="number" name="duration" class="form-control form-control-sm" value="{{ $cat->duration }}" min="0" max="20" required></div>
                        <div class="col-md-3 col-6"><x-ui.button type="submit" variant="primary" size="sm" class="w-100">Simpan</x-ui.button></div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table pm-table align-middle mb-2">
                        <thead><tr><th>Kode produk</th><th>Persentase kegelapan</th><th class="text-end">Aksi</th></tr></thead>
                        <tbody>
                        @forelse($cat->catalogProducts as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->name }}@if($p->usageSummary)<div class="pm-used">Dipakai: {{ \App\Services\CatalogUsage::describe($p->usageSummary) }}</div>@endif</td>
                                <td>{{ $p->description }}</td>
                                <td class="text-end">
                                    <div class="pm-actions justify-content-end">
                                        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#edit-prod-{{ $p->id }}" aria-expanded="false" aria-controls="edit-prod-{{ $p->id }}">Edit</button>
                                        <form method="POST" action="{{ route('product-management.products.destroy', [$tab, $p->id]) }}" onsubmit="return confirm('Hapus produk {{ e($p->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" type="submit" @disabled((bool) $p->usageSummary)>Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr class="collapse" id="edit-prod-{{ $p->id }}">
                                <td colspan="3">
                                    <form method="POST" action="{{ route('product-management.products.update', [$tab, $p->id]) }}" class="pm-edit row g-2 align-items-end mb-0">
                                        @csrf @method('PUT')
                                        <div class="col-md-5"><label class="ui-label">Kode produk</label><input type="text" name="name" class="form-control form-control-sm" value="{{ $p->name }}" required maxlength="100"></div>
                                        <div class="col-md-4 col-6"><label class="ui-label">Kegelapan</label><input type="text" name="description" class="form-control form-control-sm" value="{{ $p->description }}" required maxlength="50"></div>
                                        <div class="col-md-3 col-6"><x-ui.button type="submit" variant="primary" size="sm" class="w-100">Simpan</x-ui.button></div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted small py-3">Belum ada produk di kategori ini.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('product-management.products.store', [$tab, $cat->id]) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-5"><label class="ui-label" for="np-name-{{ $cat->id }}">Kode produk baru</label><input id="np-name-{{ $cat->id }}" type="text" name="name" class="form-control form-control-sm" placeholder="Contoh: NV70" required maxlength="100"></div>
                    <div class="col-md-4 col-6"><label class="ui-label" for="np-desc-{{ $cat->id }}">Kegelapan</label><input id="np-desc-{{ $cat->id }}" type="text" name="description" class="form-control form-control-sm" placeholder="Contoh: 20%" required maxlength="50"></div>
                    <div class="col-md-3 col-6"><x-ui.button type="submit" variant="outline" size="sm" class="w-100"><iconify-icon icon="mdi:plus" aria-hidden="true"></iconify-icon> Tambah Produk</x-ui.button></div>
                </form>
            </div>
        </section>
    @empty
        <x-ui.empty-state title="Belum ada kategori" description="Tambahkan kategori pertama di atas." />
    @endforelse

    <p class="text-muted small">
        Kategori/produk yang sudah dipakai data lain (customer, stok roll, quotation/SO, dll.) tidak bisa dihapus; ubah namanya saja.
        Tarif komisi diatur per kategori di kartu masing-masing. Kategori baru perlu diatur tarifnya agar menghasilkan komisi.
    </p>
@endif
@endsection
