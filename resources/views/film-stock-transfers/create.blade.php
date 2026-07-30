@extends('dashboard.index')

@section('title', 'Transfer Roll Ke Cabang')
@section('route-breadcrumb', 'film-stock-transfers.create')
@section('breadcrumb', 'Transfer Roll Ke Cabang')

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

@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Transfer Roll Ke Cabang</div>
                <small class="text-muted">
                    Pilih roll dari gudang/cabang asal untuk dikirim ke cabang tujuan.
                </small>
            </div>

            <x-ui.button href="{{ route('film-stock-transfers.index') }}" variant="secondary" size="sm">
                History Transfer
            </x-ui.button>
        </div>
    @endslot

    <form method="GET" action="{{ route('film-stock-transfers.create') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-4">
            <x-ui.form-field label="Cabang / Gudang Asal" name="from_branch_id">
                <select name="from_branch_id" class="form-select" required>
                    <option value="">-- Pilih Asal --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (int)$fromBranchId === (int)$branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} - {{ $branch->city ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </x-ui.form-field>
        </div>

        <div class="col-md-2">
            <x-ui.button type="submit" variant="outline">
                Tampilkan Roll
            </x-ui.button>
        </div>
    </form>

    <form method="POST" action="{{ route('film-stock-transfers.store') }}">
        @csrf

        <input type="hidden" name="from_branch_id" value="{{ $fromBranchId }}">

        <div class="row mb-3">
            <div class="col-md-4">
                <x-ui.form-field label="Cabang Tujuan *" name="to_branch_id">
                    <select name="to_branch_id" class="form-select" required>
                        <option value="">-- Pilih Cabang Tujuan --</option>
                        @foreach($branches as $branch)
                            @if((int)$fromBranchId !== (int)$branch->id)
                                <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} - {{ $branch->city ?? '-' }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </x-ui.form-field>
            </div>

            <div class="col-md-3">
                <x-ui.form-field label="Tanggal Transfer *" name="transfer_date">
                    <input type="date" name="transfer_date" class="form-control"
                           value="{{ old('transfer_date', now()->format('Y-m-d')) }}" required>
                </x-ui.form-field>
            </div>

            <div class="col-md-5">
                <x-ui.form-field label="Catatan" name="notes">
                    <input type="text" name="notes" class="form-control"
                           value="{{ old('notes') }}" placeholder="Catatan transfer...">
                </x-ui.form-field>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">Daftar Roll Tersedia</h6>
            <small class="text-muted">
                Pilih roll yang akan dikirim.
            </small>
        </div>

        <div class="table-responsive border rounded mb-3" style="max-height: 520px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="checkAll" class="form-check-input">
                        </th>
                        <th>Kode Roll</th>
                        <th>Type</th>
                        <th>Kode / Kegelapan</th>
                        <th>Lebar</th>
                        <th>Panjang</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th>Tanggal Masuk</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($availableRolls as $roll)
                        <tr>
                            <td>
                                <input type="checkbox"
                                       name="roll_entry_ids[]"
                                       value="{{ $roll->id }}"
                                       class="form-check-input check-item">
                            </td>
                            <td class="fw-semibold">{{ $roll->roll_code }}</td>
                            <td>{{ $roll->film_type ?? '-' }}</td>
                            <td>
                                <x-ui.badge variant="neutral">
                                    {{ $roll->darkness_code ?? '-' }}
                                </x-ui.badge>
                            </td>
                            <td>{{ number_format($roll->width_meter, 2, ',', '.') }} m</td>
                            <td>{{ number_format($roll->length_meter, 2, ',', '.') }} m</td>
                            <td>{{ number_format($roll->area_meter, 2, ',', '.') }} m²</td>
                            <td>
                                @if($roll->status === 'in_warehouse')
                                    <x-ui.badge variant="primary">In Warehouse</x-ui.badge>
                                @elseif($roll->status === 'in_branch')
                                    <x-ui.badge variant="success">In Branch</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral">{{ $roll->status }}</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                {{ $roll->received_date ? $roll->received_date->format('d-m-Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-5">
                                <x-ui.empty-state title="Tidak ada roll tersedia di cabang/gudang asal ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-ui.button type="submit" variant="primary"
                onclick="return confirm('Yakin transfer roll terpilih ke cabang tujuan?')">
            Simpan Transfer
        </x-ui.button>

        <x-ui.button href="{{ route('film-stock-transfers.index') }}" variant="ghost">
            Batal
        </x-ui.button>

    </form>

</x-ui.card>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.check-item');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
});
</script>

@endsection
