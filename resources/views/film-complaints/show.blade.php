@extends('dashboard.index')

@section('title', 'Detail Complain')
@section('route-breadcrumb', 'film-complaints.index')
@section('breadcrumb', 'Detail Complain')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Detail Complain / Replacement</div>
                <small class="text-muted">
                    {{ $complaint->complaint_number }}
                </small>
            </div>

            <div class="d-flex gap-2">
                <x-ui.button :href="route('film-complaints.edit', $complaint->id)" variant="outline" size="sm">
                    <iconify-icon icon="mdi:pencil-outline" class="me-1"></iconify-icon>Edit
                </x-ui.button>
                <x-ui.button :href="route('film-complaints.index')" variant="secondary" size="sm">
                    Kembali
                </x-ui.button>
            </div>
        </div>
    @endslot

    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">No Complain</small>
                <h6 class="mb-0">{{ $complaint->complaint_number }}</h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Tanggal</small>
                <h6 class="mb-0">
                    {{ $complaint->complaint_date ? $complaint->complaint_date->format('d-m-Y') : '-' }}
                </h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Jenis</small>
                <h6 class="mb-0">
                    @if($complaint->complaint_type === 'car')
                        <x-ui.badge variant="primary">Mobil</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">Gedung</x-ui.badge>
                    @endif
                </h6>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="border rounded p-3 h-100">
                <small class="text-muted d-block">Status</small>
                <h6 class="mb-0">
                    @if($complaint->status === 'completed')
                        <x-ui.badge variant="success">Completed</x-ui.badge>
                    @elseif($complaint->status === 'open')
                        <x-ui.badge variant="primary">Open</x-ui.badge>
                    @elseif($complaint->status === 'process')
                        <x-ui.badge variant="warning">Process</x-ui.badge>
                    @elseif($complaint->status === 'cancelled')
                        <x-ui.badge variant="danger">Cancelled</x-ui.badge>
                    @else
                        <x-ui.badge variant="neutral">{{ ucfirst($complaint->status) }}</x-ui.badge>
                    @endif
                </h6>
            </div>
        </div>
    </div>

    <form action="{{ route('film-complaints.update-status', $complaint->id) }}" method="POST"
          class="d-flex align-items-end gap-2 mb-4" style="max-width:360px">
        @csrf
        @method('PATCH')
        <div class="flex-fill">
            <label class="form-label small fw-medium mb-1">Ubah Status</label>
            <select name="status" class="form-select form-select-sm">
                @foreach (['open' => 'Open', 'process' => 'Process', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                    <option value="{{ $value }}" {{ $complaint->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="outline" size="sm">Simpan</x-ui.button>
    </form>

    <dl class="row mb-4">
        <dt class="col-sm-3">Cabang</dt>
        <dd class="col-sm-9">{{ $complaint->branch->name ?? '-' }}</dd>

        <dt class="col-sm-3">Customer</dt>
        <dd class="col-sm-9">
            @if($complaint->complaint_type === 'car')
                {{ $complaint->customer->name ?? '-' }}
                <br>
                <small class="text-muted">
                    {{ $complaint->customer->vehicle_brand ?? '' }}
                    {{ $complaint->customer->vehicle_model ?? '' }}
                    -
                    {{ $complaint->customer->plat_number ?? '' }}
                </small>
            @else
                {{ $complaint->customerBuilding->name ?? '-' }}
                <br>
                <small class="text-muted">
                    {{ $complaint->customerBuilding->address ?? '-' }}
                </small>
            @endif
        </dd>

        <dt class="col-sm-3">Alasan</dt>
        <dd class="col-sm-9">{{ $complaint->reason ?? '-' }}</dd>

        <dt class="col-sm-3">Catatan</dt>
        <dd class="col-sm-9">{{ $complaint->notes ?? '-' }}</dd>

        <dt class="col-sm-3">Dibuat Oleh</dt>
        <dd class="col-sm-9">{{ $complaint->creator->name ?? '-' }}</dd>
    </dl>

    <h6 class="fw-bold mb-3">Item Pergantian</h6>

    <div class="table-responsive border rounded mb-4">
        <table class="table table-hover align-middle mb-0" style="min-width: 1100px;">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>Part / Area</th>
                    <th>Sisi</th>
                    <th>Scope</th>
                    <th>Coverage</th>
                    <th>Meter Terpakai</th>
                    <th>Waste</th>
                    <th>Source</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>
                @forelse($complaint->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            @if($complaint->complaint_type === 'car')
                                {{ $item->categoryProduct->name ?? '-' }}
                                <br>
                                <x-ui.badge variant="neutral">
                                    {{ $item->product->name ?? '-' }}
                                </x-ui.badge>
                            @else
                                {{ $item->categoryProductBuilding->name ?? '-' }}
                                <br>
                                <x-ui.badge variant="neutral">
                                    {{ $item->productBuilding->name ?? '-' }}
                                </x-ui.badge>
                            @endif
                        </td>

                        <td>
                            @if($complaint->complaint_type === 'car')
                                {{ $item->part->name ?? '-' }}
                            @else
                                {{ number_format($item->area_meter, 2, ',', '.') }} m²
                            @endif
                        </td>

                        <td>
                            @if($complaint->complaint_type === 'car')
                                @if($item->replacement_side === 'left')
                                    Kiri
                                @elseif($item->replacement_side === 'right')
                                    Kanan
                                @elseif($item->replacement_side === 'both')
                                    Kanan & Kiri
                                @else
                                    Tidak Ada
                                @endif
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if($complaint->complaint_type === 'car')
                                {{ ucfirst($item->replacement_scope) }}
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            @if($complaint->complaint_type === 'car')
                                {{ number_format($item->coverage_percent, 0, ',', '.') }}%
                            @else
                                -
                            @endif
                        </td>

                        <td class="fw-semibold">
                            {{ number_format($item->used_length_meter, 2, ',', '.') }} m
                        </td>

                        <td>
                            {{ number_format($item->waste_percent, 2, ',', '.') }}%
                        </td>

                        <td>
                            @if($item->usage_source === 'manual')
                                <x-ui.badge variant="warning">Manual</x-ui.badge>
                            @else
                                <x-ui.badge variant="success">Estimasi</x-ui.badge>
                            @endif
                        </td>

                        <td>{{ $item->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="py-5"><x-ui.empty-state title="Tidak ada item pergantian." /></td></tr>
                @endforelse
            </tbody>

            @if($complaint->items->count())
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Total Meter Terpakai</th>
                        <th>
                            {{ number_format($complaint->items->sum('used_length_meter'), 2, ',', '.') }} m
                        </th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <h6 class="fw-bold mb-3">Histori Pengurangan Stock Roll</h6>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0" style="min-width: 1000px;">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Type</th>
                    <th>Kode</th>
                    <th>Meter Terpakai</th>
                    <th>Stock Sebelum</th>
                    <th>Stock Sesudah</th>
                    <th>Reason</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>
                @forelse($complaint->stockUsages as $usage)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            {{ $usage->usage_date ? \Carbon\Carbon::parse($usage->usage_date)->format('d-m-Y') : '-' }}
                        </td>

                        <td>{{ $usage->film_type ?? '-' }}</td>

                        <td>
                            <x-ui.badge variant="neutral">
                                {{ $usage->darkness_code ?? '-' }}
                            </x-ui.badge>
                        </td>

                        <td class="fw-semibold">
                            {{ number_format($usage->used_length_meter, 2, ',', '.') }} m
                        </td>

                        <td>
                            {{ number_format($usage->stock_before, 2, ',', '.') }} m
                        </td>

                        <td>
                            {{ number_format($usage->stock_after, 2, ',', '.') }} m
                        </td>

                        <td>
                            <x-ui.badge variant="danger">
                                {{ ucfirst($usage->usage_reason ?? 'complaint') }}
                            </x-ui.badge>
                        </td>

                        <td>{{ $usage->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-5"><x-ui.empty-state title="Belum ada histori pengurangan stok." /></td></tr>
                @endforelse
            </tbody>

            @if($complaint->stockUsages->count())
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total Pengurangan</th>
                        <th>
                            {{ number_format($complaint->stockUsages->sum('used_length_meter'), 2, ',', '.') }} m
                        </th>
                        <th colspan="4"></th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</x-ui.card>

@endsection
