@extends('dashboard.index')

@section('title', 'Harga Roll')
@section('route-breadcrumb', 'branch-roll-invoices.index')
@section('breadcrumb', 'Harga Roll')

@section('content')

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</x-ui.alert>
@endif

<form action="{{ route('branch-roll-prices.update') }}" method="POST">
    @csrf @method('PUT')

    <x-ui.card>
        @slot('header')
            <div class="ui-card-title">Harga Jual Roll (per roll)</div>
            <x-ui.button type="submit" variant="primary" size="sm">Simpan Harga</x-ui.button>
        @endslot

        <x-ui.alert type="info" class="mb-4">
            Satu daftar harga untuk semua penjualan roll: Invoice Mitra (pusat ke mitra) dan Invoice Roll Antar Cabang.
            Harga per roll utuh, per nama film (berlaku untuk mobil dan gedung), menurut kategori pembeli: Franchise atau
            Non-Franchise. Untuk antar cabang, kategori diambil dari pengaturan cabang pembeli di menu Cabang.
            Harga kosong atau 0 berarti belum diatur dan film itu tidak bisa dijual. Invoice yang sudah dibuat tidak
            berubah kalau harga diganti.
        </x-ui.alert>

        <div class="table-responsive">
            <table class="table bordered-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Film</th>
                        <th style="min-width:200px;">{{ $ownerships['mitra'] }} (Rp)</th>
                        <th style="min-width:200px;">{{ $ownerships['non_franchise'] }} (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($films as $f)
                        @php $row = $prices[$f->id] ?? null; @endphp
                        <tr>
                            <td>
                                {{ $f->name }}
                                @if(!in_array(strtoupper(trim($f->name)), $carNames, true))<x-ui.badge variant="info" size="sm" class="ms-1">Hanya gedung</x-ui.badge>@endif
                            </td>
                            <td><input type="number" min="0" step="1000" name="prices[{{ $f->id }}][mitra]" class="form-control"
                                       value="{{ $row && $row->price_mitra > 0 ? (int) $row->price_mitra : '' }}" placeholder="Belum diatur"></td>
                            <td><input type="number" min="0" step="1000" name="prices[{{ $f->id }}][non_franchise]" class="form-control"
                                       value="{{ $row && $row->price_non_franchise > 0 ? (int) $row->price_non_franchise : '' }}" placeholder="Belum diatur"></td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-ui.empty-state title="Belum ada kategori film" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</form>

@endsection
