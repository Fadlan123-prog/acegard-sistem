@extends('dashboard.index')

@section('title', 'Produk Sales')
@section('route-breadcrumb', 'sales-products.index')
@section('breadcrumb', 'Produk Sales')

@section('content')

<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Katalog Produk Sales</div>
            <x-ui.button :href="route('sales-products.create')" variant="primary" size="sm">+ Tambah Produk</x-ui.button>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th class="text-end">Harga</th>
                    <th>Status</th>
                    <th style="width:160px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td><x-ui.badge variant="info">{{ $product->category }}</x-ui.badge></td>
                    <td class="text-end fw-semibold">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td>
                        <x-ui.badge :variant="$product->is_active ? 'success' : 'neutral'">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-ui.badge>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('sales-products.edit', $product) }}"
                               class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                               title="Edit">
                                <iconify-icon icon="lucide:edit"></iconify-icon>
                            </a>
                            <form method="POST" action="{{ route('sales-products.toggle', $product) }}">
                                @csrf
                                <x-ui.button type="submit" :variant="$product->is_active ? 'warning' : 'success'" size="sm">
                                    {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-5"><x-ui.empty-state title="Belum ada produk." description="Produk yang ditambahkan di sini bisa langsung dijual lewat jaringan sales." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

@endsection
