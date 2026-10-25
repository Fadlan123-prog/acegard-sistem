@extends('layout.index')

@section('title', 'Produk PPF')
@section('route-breadcrumb', route('ppf-products.index'))
@section('breadcrumb', 'Produk PPF')

@section('content')

{{-- Action Bar --}}
<x-ui.card class="mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <p class="mb-0 text-muted small">{{ $products->total() }} produk terdaftar</p>
        <x-ui.button :href="route('ppf-products.create')" variant="primary" size="sm">
            <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon>Tambah Produk
        </x-ui.button>
    </div>
</x-ui.card>

{{-- Table --}}
<x-ui.card>
    @slot('footer')
        @if($products->hasPages())
            {{ $products->links() }}
        @endif
    @endslot

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead style="background:#fafafa">
                <tr>
                    <th class="ps-4 py-3 text-muted fw-medium border-bottom">Nama / Brand</th>
                    <th class="py-3 text-muted fw-medium border-bottom">Seri</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Ketebalan</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Finish</th>
                    <th class="py-3 text-center text-muted fw-medium border-bottom">Garansi Default</th>
                    <th class="py-3 pe-4 text-end text-muted fw-medium border-bottom">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                <tr>
                    <td class="ps-4 py-3">
                        <div class="fw-semibold" style="color:#1e293b">{{ $p->name }}</div>
                        <div class="text-muted" style="font-size:11px">{{ $p->brand }}</div>
                    </td>
                    <td class="py-3 text-muted" style="font-size:12px">{{ $p->series ?? '—' }}</td>
                    <td class="py-3 text-center text-muted" style="font-size:12px">
                        {{ $p->thickness_microns ? $p->thickness_microns.' μm' : '—' }}
                    </td>
                    <td class="py-3 text-center">
                        @if($p->finish)
                        @php
                            $finishVariant = match(strtolower($p->finish)) {
                                'glossy'  => 'primary',
                                'matte'   => 'neutral',
                                'satin'   => 'warning',
                                'ceramic' => 'success',
                                default   => 'neutral',
                            };
                        @endphp
                        <x-ui.badge :variant="$finishVariant" size="sm" class="text-capitalize">{{ $p->finish }}</x-ui.badge>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="py-3 text-center">
                        <span class="fw-semibold" style="color:#6366f1">{{ $p->default_warranty_years }} tahun</span>
                    </td>
                    <td class="py-3 pe-4">
                        <div class="d-flex gap-1 justify-content-end">
                            <x-ui.button :href="route('ppf-products.edit', $p)" variant="outline" size="sm" title="Edit">
                                <iconify-icon icon="mdi:pencil-outline"></iconify-icon>
                            </x-ui.button>
                            <form method="POST" action="{{ route('ppf-products.destroy', $p) }}">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger-outline" size="sm" title="Hapus"
                                        onclick="return confirm('Hapus produk {{ addslashes($p->name) }}?')">
                                    <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                </x-ui.button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-5"><x-ui.empty-state title="Belum ada produk PPF." /></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

@endsection
