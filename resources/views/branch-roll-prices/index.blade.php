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
            Satu daftar harga per nama film, berlaku untuk mobil maupun gedung. Harga per roll utuh, menurut kategori
            <strong>cabang pembeli</strong> (franchise atau non-franchise) (atur kategori tiap cabang di menu Cabang). Harga yang kosong
            berarti film itu tidak bisa dijual ke cabang dengan kategori tersebut. Invoice yang sudah dibuat tidak
            berubah kalau harga diganti.
        </x-ui.alert>

        <div class="table-responsive">
            <table class="table bordered-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Film</th>
                        @foreach($ownerships as $label)<th style="min-width:190px;">{{ $label }} (Rp)</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($films as $key => $f)
                        <tr>
                            <td>
                                {{ $f['name'] }}
                                @if(!$f['car'])<span class="ui-badge ui-badge-info ms-1">Hanya gedung</span>@endif
                            </td>
                            @foreach($ownerships as $ownKey => $label)
                                <td><input type="number" min="0" step="1000" name="prices[{{ $ownKey }}][{{ $key }}]" class="form-control"
                                           value="{{ isset($prices[$ownKey][$key]) ? (int) $prices[$ownKey][$key] : '' }}" placeholder="Belum diatur"></td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ 1 + count($ownerships) }}"><x-ui.empty-state title="Belum ada kategori film" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</form>

@endsection
