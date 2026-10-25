@extends('dashboard.index')

@section('title', 'Mitra')
@section('route-breadcrumb', 'mitras.index')
@section('breadcrumb', 'Mitra')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="ui-card-title">Daftar Mitra / Non-Franchise</div>
            <x-ui.button :href="route('mitras.create')" variant="primary" size="sm">+ Tambah Mitra</x-ui.button>
        </div>
    @endslot

    {{-- Search & filter --}}
    <form method="GET" action="{{ route('mitras.index') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Cari Mitra</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="Nama, kota, atau telepon...">
        </div>

        <div class="col-md-3">
            <label class="form-label">Tipe</label>
            <select name="type" class="form-select">
                <option value="">Semua Tipe</option>
                <option value="mitra" @selected(request('type') == 'mitra')>Mitra</option>
                <option value="non_franchise" @selected(request('type') == 'non_franchise')>Non-Franchise</option>
            </select>
        </div>

        <div class="col-auto d-flex gap-2">
            <x-ui.button type="submit" variant="outline">Filter</x-ui.button>

            @if(request('search') || request('type'))
                <x-ui.button :href="route('mitras.index')" variant="secondary">Reset</x-ui.button>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>PIC</th>
                    <th>Telepon</th>
                    <th>Kota</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Invoice</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($mitras as $mitra)
                    <tr>
                        <td>{{ $mitras->firstItem() + $loop->index }}</td>

                        <td class="fw-semibold">{{ $mitra->name }}</td>

                        <td>
                            <x-ui.badge variant="{{ $mitra->type === 'mitra' ? 'info' : 'neutral' }}" size="sm">
                                {{ $mitra->type_label }}
                            </x-ui.badge>
                        </td>

                        <td>{{ $mitra->pic_name ?: '-' }}</td>

                        <td>{{ $mitra->phone ?: '-' }}</td>

                        <td>{{ $mitra->city ?: '-' }}</td>

                        <td class="text-center">
                            <x-ui.badge variant="{{ $mitra->status === 'active' ? 'success' : 'neutral' }}" size="sm">
                                {{ $mitra->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </x-ui.badge>
                        </td>

                        <td class="text-center">
                            @if($mitra->invoices_count > 0)
                                <a href="{{ route('mitra-invoices.index', ['mitra_id' => $mitra->id]) }}" class="text-decoration-none">
                                    <x-ui.badge variant="neutral" size="sm">{{ $mitra->invoices_count }} Invoice</x-ui.badge>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('mitras.edit', $mitra->id) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                   title="Edit">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                <form action="{{ route('mitras.destroy', $mitra->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus mitra {{ addslashes($mitra->name) }}?')"
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
                        <td colspan="9" class="py-5"><x-ui.empty-state title="Belum ada data mitra" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $mitras->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
