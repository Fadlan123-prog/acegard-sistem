@extends('dashboard.index')

@section('title', 'Detail Stock Pool')
@section('route-breadcrumb', 'film-stock.index')
@section('breadcrumb', 'Detail Stock Pool')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Detail Stock Pool</div>
                <small class="text-muted">
                    Detail stok gabungan dan histori roll masuk.
                </small>
            </div>

            <x-ui.button href="{{ route('film-stock.index') }}" variant="secondary" size="sm">
                Kembali
            </x-ui.button>
        </div>
    @endslot

    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Type" :value="$pool->film_type ?? '-'" color="blue" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Kode / Kegelapan" :value="$pool->darkness_code ?? '-'" color="blue" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Sisa Meter" :value="number_format($pool->remaining_length_meter, 2, ',', '.') . ' m'" color="amber" />
        </div>

        <div class="col-md-3 mb-2">
            <x-ui.stats-card label="Sisa Area" :value="number_format($pool->remaining_area_meter, 2, ',', '.') . ' m²'" color="amber" />
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <dl class="row">
                <dt class="col-sm-4">Cabang</dt>
                <dd class="col-sm-8">{{ $pool->branch->name ?? '-' }}</dd>

                <dt class="col-sm-4">Jenis</dt>
                <dd class="col-sm-8">
                    @if($pool->usage_for === 'car')
                        Mobil
                    @elseif($pool->usage_for === 'building')
                        Gedung
                    @else
                        Universal
                    @endif
                </dd>

                <dt class="col-sm-4">Lebar Roll</dt>
                <dd class="col-sm-8">{{ number_format($pool->width_meter, 2, ',', '.') }} m</dd>
            </dl>
        </div>

        <div class="col-md-6">
            <dl class="row">
                <dt class="col-sm-5">Total Meter Masuk</dt>
                <dd class="col-sm-7">{{ number_format($pool->total_received_length_meter, 2, ',', '.') }} m</dd>

                <dt class="col-sm-5">Total Meter Terpakai</dt>
                <dd class="col-sm-7">{{ number_format($pool->total_used_length_meter, 2, ',', '.') }} m</dd>

                <dt class="col-sm-5">Sisa Meter</dt>
                <dd class="col-sm-7">{{ number_format($pool->remaining_length_meter, 2, ',', '.') }} m</dd>
            </dl>
        </div>
    </div>

    <h5 class="mb-3">Histori Roll Masuk</h5>

    <div class="table-responsive border rounded mb-4">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode Roll</th>
                    <th>Type</th>
                    <th>Kode / Kegelapan</th>
                    <th>Lebar</th>
                    <th>Panjang</th>
                    <th>Area</th>
                    <th>Tanggal Masuk</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pool->rollEntries as $entry)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $entry->roll_code }}</td>
                        <td>{{ $entry->film_type ?? '-' }}</td>
                        <td>{{ $entry->darkness_code ?? '-' }}</td>
                        <td>{{ number_format($entry->width_meter, 2, ',', '.') }} m</td>
                        <td>{{ number_format($entry->length_meter, 2, ',', '.') }} m</td>
                        <td>{{ number_format($entry->area_meter, 2, ',', '.') }} m²</td>
                        <td>
                            {{ $entry->received_date ? \Carbon\Carbon::parse($entry->received_date)->format('d-m-Y') : '-' }}
                        </td>
                        <td>{{ $entry->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-5">
                            <x-ui.empty-state title="Belum ada roll masuk." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h5 class="mb-3">Histori Pemakaian</h5>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Customer</th>
                    <th>Mobil / Gedung</th>
                    <th>Meter Terpakai</th>
                    <th>Stok Sebelum</th>
                    <th>Stok Sesudah</th>
                    <th>Source</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pool->usages as $usage)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            {{ $usage->usage_date ? \Carbon\Carbon::parse($usage->usage_date)->format('d-m-Y') : '-' }}
                        </td>

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
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-5">
                            <x-ui.empty-state title="Belum ada pemakaian bahan." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-ui.card>

@endsection
