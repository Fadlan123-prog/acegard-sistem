@extends('layout.index')

@section('title', 'Daftar Konten')
@section('route-breadcrumb', route('content.posts.index'))
@section('breadcrumb', 'Daftar Konten')

@section('content')

@if (session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
    <div class="d-flex justify-content-between align-items-center">
        <div class="ui-card-title">Daftar Konten</div>
        <x-ui.button :href="route('content.posts.create')" variant="primary" size="sm">
            <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Catat Konten
        </x-ui.button>
    </div>
    @endslot

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:12px">
            <thead>
                <tr>
                    <th class="ps-0 py-2 text-muted fw-medium border-bottom">Tanggal</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Judul</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Platform</th>
                    <th class="py-2 text-muted fw-medium border-bottom">Creator</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Views</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Leads</th>
                    <th class="py-2 text-end text-muted fw-medium border-bottom">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                <tr>
                    <td class="ps-0 py-2">{{ $post->published_at->format('d/m/Y') }}</td>
                    <td class="py-2">
                        {{ $post->title }}
                        @if($post->is_fyp)<x-ui.badge variant="info" size="sm" class="ms-1">FYP</x-ui.badge>@endif
                    </td>
                    <td class="py-2"><x-ui.badge variant="neutral" size="sm">{{ $post->platform_label }}</x-ui.badge></td>
                    <td class="py-2 text-muted" style="font-size:11px">{{ $post->employee->name ?? '—' }}</td>
                    <td class="py-2 text-end">{{ number_format($post->views) }}</td>
                    <td class="py-2 text-end">{{ $post->leads_generated }}</td>
                    <td class="py-2 text-end">
                        <form action="{{ route('content.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Hapus konten ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-btn ui-btn-ghost ui-btn-sm" title="Hapus">
                                <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-4 text-center"><x-ui.empty-state title="Belum ada konten tercatat" /></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $posts->links() }}</div>
</x-ui.card>

@endsection
