@extends('dashboard.index')

@section('title', 'Laporan Pemakaian Roll')
@section('route-breadcrumb', 'film-stock.usages')
@section('breadcrumb', 'Laporan Pemakaian Roll')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Laporan Pemakaian Roll Kaca Film</div>
                <small class="text-muted">
                    Histori pemakaian bahan dari customer mobil dan customer gedung.
                </small>
            </div>

            <x-ui.button href="{{ route('film-stock.index') }}" variant="secondary" size="sm">
                Kembali ke Stock
            </x-ui.button>
        </div>
    @endslot

    <form method="GET" action="{{ route('film-stock.usages') }}" class="row g-2 mb-4 align-items-end">

        <div class="col-md-2">
            <x-ui.form-field label="Dari Tanggal" name="from">
                <input type="date"
                       name="from"
                       value="{{ request('from') }}"
                       class="form-control">
            </x-ui.form-field>
        </div>

        <div class="col-md-2">
            <x-ui.form-field label="Sampai Tanggal" name="to">
                <input type="date"
                       name="to"
                       value="{{ request('to') }}"
                       class="form-control">
            </x-ui.form-field>
        </div>

        <div class="col-md-2">
            <x-ui.form-field label="Jenis" name="usage_type">
                <select name="usage_type" class="form-select">
                    <option value="">-- Semua --</option>
                    <option value="car" {{ request('usage_type') == 'car' ? 'selected' : '' }}>Mobil</option>
                    <option value="building" {{ request('usage_type') == 'building' ? 'selected' : '' }}>Gedung</option>
                </select>
            </x-ui.form-field>
        </div>

        <div class="col-md-2">
            <x-ui.form-field label="Type" name="film_type">
                <input type="text"
                       name="film_type"
                       value="{{ request('film_type') }}"
                       class="form-control"
                       placeholder="4K / Notch / Black">
            </x-ui.form-field>
        </div>

        <div class="col-md-2">
            <x-ui.form-field label="Kode" name="darkness_code">
                <input type="text"
                       name="darkness_code"
                       value="{{ request('darkness_code') }}"
                       class="form-control"
                       placeholder="AP50 / NV40">
            </x-ui.form-field>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <x-ui.button type="submit" variant="outline">
                Filter
            </x-ui.button>

            @if(request()->query())
                <x-ui.button href="{{ route('film-stock.usages') }}" variant="ghost">
                    Reset
                </x-ui.button>
            @endif
        </div>
    </form>

    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <x-ui.stats-card label="Total Data Halaman Ini" :value="$usages->count() . ' Pemakaian'" color="blue" />
        </div>

        <div class="col-md-4 mb-2">
            <x-ui.stats-card label="Total Meter Terpakai Halaman Ini" :value="number_format($usages->sum('used_length_meter'), 2, ',', '.') . ' m'" color="amber" />
        </div>

        <div class="col-md-4 mb-2">
            <x-ui.stats-card label="Total Area Terpakai Halaman Ini" :value="number_format($usages->sum('area_meter'), 2, ',', '.') . ' m²'" color="green" />
        </div>
    </div>

    <div class="table-responsive" style="max-height: 560px; overflow-y: auto; overflow-x: auto;">
        <table class="table bordered-table align-middle mb-0" style="min-width: 1250px; white-space: nowrap;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Cabang</th>
                    <th>Jenis</th>
                    <th>Customer</th>
                    <th>Mobil / Gedung</th>
                    <th>Type</th>
                    <th>Kode</th>
                    <th>Area</th>
                    <th>Meter Terpakai</th>
                    <th>Stok Sebelum</th>
                    <th>Stok Sesudah</th>
                    <th>Source</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>
                @forelse($usages as $usage)
                    <tr>
                        <td>{{ $usages->firstItem() + $loop->index }}</td>

                        <td>
                            {{ $usage->usage_date ? \Carbon\Carbon::parse($usage->usage_date)->format('d-m-Y') : '-' }}
                        </td>

                        <td>{{ $usage->branch->name ?? '-' }}</td>

                        <td>
                            @if($usage->usage_type === 'car')
                                <x-ui.badge variant="primary">Mobil</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">Gedung</x-ui.badge>
                            @endif
                        </td>

                        <td>
                            @if($usage->usage_type === 'car')
                                {{ $usage->customer->name ?? '-' }}
                            @else
                                {{ $usage->customerBuilding->name ?? '-' }}
                            @endif
                        </td>

                        <td>
                            @if($usage->usage_type === 'car')
                                {{ $usage->vehicle_brand }} {{ $usage->vehicle_model }}
                                <br>
                                <small class="text-muted">{{ $usage->plat_number }}</small>
                            @else
                                {{ $usage->customerBuilding->address ?? '-' }}
                            @endif
                        </td>

                        <td>{{ $usage->film_type ?? '-' }}</td>

                        <td>
                            <x-ui.badge variant="neutral">
                                {{ $usage->darkness_code ?? '-' }}
                            </x-ui.badge>
                        </td>

                        <td>{{ number_format($usage->area_meter, 2, ',', '.') }} m²</td>

                        <td class="fw-semibold">
                            {{ number_format($usage->used_length_meter, 2, ',', '.') }} m
                        </td>

                        <td>{{ number_format($usage->stock_before, 2, ',', '.') }} m</td>

                        <td>{{ number_format($usage->stock_after, 2, ',', '.') }} m</td>

                        <td>
                            @if($usage->usage_source === 'manual')
                                <x-ui.badge variant="warning">Manual</x-ui.badge>
                            @else
                                <x-ui.badge variant="success">Estimasi</x-ui.badge>
                            @endif
                        </td>

                        <td>{{ $usage->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="py-5">
                            <x-ui.empty-state title="Belum ada data pemakaian roll." />
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($usages->count())
                <tfoot>
                    <tr>
                        <th colspan="8" class="text-end">Total Halaman Ini</th>
                        <th>{{ number_format($usages->sum('area_meter'), 2, ',', '.') }} m²</th>
                        <th>{{ number_format($usages->sum('used_length_meter'), 2, ',', '.') }} m</th>
                        <th colspan="4"></th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @slot('footer')
        {{ $usages->withQueryString()->links() }}
    @endslot

</x-ui.card>

@endsection
