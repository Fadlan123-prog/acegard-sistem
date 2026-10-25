@extends('dashboard.index')

@section('title', 'Dealers')
@section('route-breadcrumb', 'dealers.index')
@section('breadcrumb', 'Dealers')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Dealer (Website)</div>
            <x-ui.button :href="route('dealers.create')" variant="primary" size="sm">+ Tambah Dealer</x-ui.button>
        </div>
    @endslot

    

    

    {{-- Search --}}
    <form method="GET" action="{{ route('dealers.index') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Cari Dealer</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="Nama atau lokasi...">
        </div>

        <div class="col-auto d-flex gap-2">
            <x-ui.button type="submit" variant="outline">Filter</x-ui.button>

            @if(request('search'))
                <x-ui.button :href="route('dealers.index')" variant="secondary">Reset</x-ui.button>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th style="width:60px;">Foto</th>
                    <th>Nama Dealer</th>
                    <th>Tipe</th>
                    <th>Lokasi</th>
                    <th class="text-center">Status</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($dealers as $dealer)
                    <tr>
                        <td>{{ $dealers->firstItem() + $loop->index }}</td>

                        <td>
                            @if($dealer->image)
                                <img src="{{ asset('storage/' . $dealer->image) }}" alt="{{ $dealer->name }}"
                                     class="rounded" style="width:40px; height:40px; object-fit:cover;">
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <td class="fw-semibold">{{ $dealer->name }}</td>

                        <td>
                            <x-ui.badge variant="{{ $dealer->type === 'cabang_utama' ? 'primary' : 'neutral' }}" size="sm">
                                {{ $dealer->type === 'cabang_utama' ? 'Cabang Utama' : 'Dealer' }}
                            </x-ui.badge>
                        </td>

                        <td>{{ $dealer->location ?: '-' }}</td>

                        <td class="text-center">
                            <x-ui.badge variant="{{ $dealer->is_active ? 'success' : 'neutral' }}" size="sm">
                                {{ $dealer->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-ui.badge>
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('dealers.edit', $dealer->id) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                   title="Edit">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                <form action="{{ route('dealers.destroy', $dealer->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus dealer {{ addslashes($dealer->name) }}?')"
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

    @slot('footer'){{ $dealers->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
