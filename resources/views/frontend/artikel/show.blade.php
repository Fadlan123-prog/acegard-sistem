@extends('frontend.layout.index')

@section('title', $article->title . ' - Acegard Indonesia')

@section('content')

<section class="ace-section-sm">
    <div class="container" style="max-width: 820px;">
        <a href="{{ route('frontend.artikel') }}" class="small ace-muted text-decoration-none d-inline-block mb-3">
            <iconify-icon icon="mdi:arrow-left"></iconify-icon> Kembali ke Artikel
        </a>

        <div class="mb-3">
            <x-frontend.badge-pill>{{ \App\Models\Article::categories()[$article->category] ?? $article->category }}</x-frontend.badge-pill>
        </div>

        <h1 class="fw-normal mb-2">{{ $article->title }}</h1>
        <p class="small ace-muted mb-4">{{ optional($article->published_at)->locale('id')->translatedFormat('d F Y') }}</p>

        @if($article->cover_image)
            <img src="{{ asset('storage/' . $article->cover_image) }}" alt="{{ $article->title }}"
                class="w-100 rounded-4 mb-4" style="max-height: 420px; object-fit: cover;">
        @endif

        <div class="ace-muted" style="white-space: pre-line;">{{ $article->body }}</div>
    </div>
</section>

@endsection
