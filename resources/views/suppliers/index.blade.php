@extends('dashboard.index')

@section('title', 'Supplier')
@section('route-breadcrumb', 'suppliers.index')
@section('breadcrumb', 'Supplier')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Supplier</div>
            <x-ui.button href="{{ route('suppliers.create') }}" variant="primary" size="sm">+ Tambah Supplier</x-ui.button>
        </div>
    @endslot

    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Cari Supplier</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="Nama, kota, atau telepon...">
        </div>

        <div class="col-auto d-flex gap-2">
            <x-ui.button type="submit" variant="outline">Filter</x-ui.button>

            @if(request('search'))
                <x-ui.button href="{{ route('suppliers.index') }}" variant="secondary">Reset</x-ui.button>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama Supplier</th>
                    <th>Kota</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th class="text-center">Purchasing</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>{{ $suppliers->firstItem() + $loop->index }}</td>

                        <td class="fw-semibold">{{ $supplier->name }}</td>

                        <td>{{ $supplier->city ?: '-' }}</td>

                        <td>{{ $supplier->phone_number ?: '-' }}</td>

                        <td>{{ $supplier->email ?: '-' }}</td>

                        <td class="text-center">
                            @if($supplier->purchases_count > 0)
                                <a href="{{ route('purchases.index', ['supplier_id' => $supplier->id]) }}" class="text-decoration-none">
                                    <x-ui.badge variant="neutral" size="sm">{{ $supplier->purchases_count }} PO</x-ui.badge>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                   title="Edit">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                <form action="{{ route('suppliers.destroy', $supplier->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus supplier {{ addslashes($supplier->name) }}?')"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                            title="Hapus">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-5"><x-ui.empty-state title="Belum ada data" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $suppliers->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
