@extends('dashboard.index')

@section('title', 'Artikel')
@section('route-breadcrumb', 'articles.index')
@section('breadcrumb', 'Artikel')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar Artikel (Website)</div>
            <x-ui.button :href="route('articles.create')" variant="primary" size="sm">+ Tambah Artikel</x-ui.button>
        </div>
    @endslot

    

    <form method="GET" action="{{ route('articles.index') }}" class="row g-2 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Cari Artikel</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="Judul artikel...">
        </div>

        <div class="col-auto d-flex gap-2">
            <x-ui.button type="submit" variant="outline">Filter</x-ui.button>

            @if(request('search'))
                <x-ui.button :href="route('articles.index')" variant="secondary">Reset</x-ui.button>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table bordered-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th style="width:60px;">Cover</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tanggal Publish</th>
                    <th class="text-center">Status</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($articles as $article)
                    <tr>
                        <td>{{ $articles->firstItem() + $loop->index }}</td>

                        <td>
                            @if($article->cover_image)
                                <img src="{{ asset('storage/' . $article->cover_image) }}" alt="{{ $article->title }}"
                                     class="rounded" style="width:40px; height:40px; object-fit:cover;">
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <td class="fw-semibold">{{ $article->title }}</td>

                        <td><x-ui.badge variant="neutral" size="sm">{{ \App\Models\Article::categories()[$article->category] ?? $article->category }}</x-ui.badge></td>

                        <td>{{ optional($article->published_at)->format('d M Y') ?: '-' }}</td>

                        <td class="text-center">
                            <x-ui.badge variant="{{ $article->is_published ? 'success' : 'neutral' }}" size="sm">
                                {{ $article->is_published ? 'Published' : 'Draft' }}
                            </x-ui.badge>
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('articles.edit', $article->id) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center"
                                   title="Edit">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                <form action="{{ route('articles.destroy', $article->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus artikel {{ addslashes($article->title) }}?')"
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
                        <td colspan="7" class="py-5"><x-ui.empty-state title="Belum ada artikel" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $articles->withQueryString()->links() }}@endslot
</x-ui.card>

@endsection
