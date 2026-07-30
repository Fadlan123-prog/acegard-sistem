@extends('dashboard.index')

@section('title', 'Stock Roll Kaca Film')
@section('route-breadcrumb', 'film-stock.index')
@section('breadcrumb', 'Stock Roll')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Stock Pool Roll Kaca Film</div>
                <small class="text-muted">
                    Sisa bahan berdasarkan cabang, type, dan kode/kegelapan produk.
                </small>
            </div>

            <div class="d-flex gap-2">
                <x-ui.button href="{{ route('film-stock.usages') }}" variant="outline" size="sm">
                    Laporan Pemakaian
                </x-ui.button>

                <x-ui.button href="{{ route('film-stock.create') }}" variant="primary" size="sm">
                    + Roll Masuk
                </x-ui.button>
            </div>
        </div>
    @endslot

    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    <form method="GET" action="{{ route('film-stock.index') }}" class="row g-2 mb-4 align-items-end">

        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="Cari type / kode / cabang...">
        </div>

        <div class="col-md-2">
            <label class="form-label">Jenis</label>
            <select name="usage_for" class="form-select">
                <option value="">-- Semua --</option>
                <option value="car" {{ request('usage_for') == 'car' ? 'selected' : '' }}>Mobil</option>
                <option value="building" {{ request('usage_for') == 'building' ? 'selected' : '' }}>Gedung</option>
                <option value="universal" {{ request('usage_for') == 'universal' ? 'selected' : '' }}>Universal</option>
            </select>
        </div>

        @if(auth()->user()->hasRole('superadmin'))
            <div class="col-md-3">
                <label class="form-label">Cabang</label>
                <select name="branch_id" class="form-select">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-2">
            <label class="form-label">Low Stock</label>
            <select name="low_stock" class="form-select">
                <option value="">-- Semua --</option>
                <option value="1" {{ request('low_stock') == '1' ? 'selected' : '' }}>
                    Di bawah 10 meter
                </option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <x-ui.button type="submit" variant="outline">Filter</x-ui.button>

            @if(request()->query())
                <x-ui.button href="{{ route('film-stock.index') }}" variant="outline">Reset</x-ui.button>
            @endif
        </div>
    </form>

    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Total Pool" :value="$pools->total()" color="blue" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card
                label="Meter Masuk Halaman Ini"
                :value="number_format($pools->sum('total_received_length_meter'), 2, ',', '.') . ' m'"
                color="green" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card
                label="Meter Terpakai Halaman Ini"
                :value="number_format($pools->sum('total_used_length_meter'), 2, ',', '.') . ' m'"
                color="amber" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card
                label="Sisa Meter Halaman Ini"
                :value="number_format($pools->sum('remaining_length_meter'), 2, ',', '.') . ' m'"
                color="purple" />
        </div>
    </div>

    <div class="table-responsive" style="max-height: 540px; overflow-y: auto; overflow-x: auto;">
        <table class="table bordered-table align-middle mb-0" style="min-width: 1150px; white-space: nowrap;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Cabang</th>
                    <th>Jenis</th>
                    <th>Type</th>
                    <th>Kode / Kegelapan</th>
                    <th>Lebar Roll</th>
                    <th>Total Masuk</th>
                    <th>Total Terpakai</th>
                    <th>Sisa Meter</th>
                    <th>Sisa m²</th>
                    <th>Status</th>
                    <th style="width: 100px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pools as $pool)
                    @php
                        $remaining = (float) $pool->remaining_length_meter;
                    @endphp

                    <tr>
                        <td>{{ $pools->firstItem() + $loop->index }}</td>

                        <td>{{ $pool->branch->name ?? '-' }}</td>

                        <td>
                            @if($pool->usage_for === 'car')
                                <x-ui.badge variant="primary">Mobil</x-ui.badge>
                            @elseif($pool->usage_for === 'building')
                                <x-ui.badge variant="neutral">Gedung</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">Universal</x-ui.badge>
                            @endif
                        </td>

                        <td class="fw-semibold">
                            {{ $pool->film_type ?? '-' }}
                        </td>

                        <td>
                            <x-ui.badge variant="neutral">{{ $pool->darkness_code ?? '-' }}</x-ui.badge>
                        </td>

                        <td>{{ number_format($pool->width_meter, 2, ',', '.') }} m</td>

                        <td>{{ number_format($pool->total_received_length_meter, 2, ',', '.') }} m</td>

                        <td>{{ number_format($pool->total_used_length_meter, 2, ',', '.') }} m</td>

                        <td class="fw-semibold">
                            {{ number_format($pool->remaining_length_meter, 2, ',', '.') }} m
                        </td>

                        <td>{{ number_format($pool->remaining_area_meter, 2, ',', '.') }} m²</td>

                        <td>
                            @if($remaining <= 0)
                                <x-ui.badge variant="danger">Habis</x-ui.badge>
                            @elseif($remaining <= 10)
                                <x-ui.badge variant="warning">Low Stock</x-ui.badge>
                            @else
                                <x-ui.badge variant="success">Aman</x-ui.badge>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('film-stock.show', $pool->id) }}"
                               class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center">
                                <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="py-5"><x-ui.empty-state title="Belum ada data stock roll." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $pools->withQueryString()->links() }}
    </div>

</x-ui.card>

@endsection
