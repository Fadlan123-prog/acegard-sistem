@extends('dashboard.index')

@section('title', 'Harga Roll Antar Cabang')
@section('route-breadcrumb', 'branch-roll-invoices.index')
@section('breadcrumb', 'Harga Roll Antar Cabang')

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
            <div class="ui-card-title">Harga Jual Roll Antar Cabang (per roll)</div>
            <x-ui.button type="submit" variant="primary" size="sm">Simpan Harga</x-ui.button>
        @endslot

        <x-ui.alert type="info" class="mb-4">
            Satu harga per kategori film untuk satu roll utuh. Kategori yang harganya kosong tidak bisa dijual
            ke cabang lain. Invoice yang sudah dibuat tidak berubah kalau harga diganti.
        </x-ui.alert>

        <div class="row g-4">
            <div class="col-lg-6">
                <h6 class="fw-semibold mb-3">Film Mobil</h6>
                <table class="table bordered-table align-middle mb-0">
                    <thead><tr><th>Kategori</th><th style="width:220px;">Harga / Roll (Rp)</th></tr></thead>
                    <tbody>
                        @forelse($car as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td><input type="number" min="0" step="1000" name="car[{{ $c->id }}]" class="form-control" value="{{ isset($carPrices[$c->id]) ? (int) $carPrices[$c->id] : '' }}" placeholder="Belum diatur"></td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><x-ui.empty-state title="Belum ada kategori mobil" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="col-lg-6">
                <h6 class="fw-semibold mb-3">Film Gedung</h6>
                <table class="table bordered-table align-middle mb-0">
                    <thead><tr><th>Kategori</th><th style="width:220px;">Harga / Roll (Rp)</th></tr></thead>
                    <tbody>
                        @forelse($building as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td><input type="number" min="0" step="1000" name="building[{{ $c->id }}]" class="form-control" value="{{ isset($buildingPrices[$c->id]) ? (int) $buildingPrices[$c->id] : '' }}" placeholder="Belum diatur"></td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><x-ui.empty-state title="Belum ada kategori gedung" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-ui.card>
</form>

@endsection
