@extends('frontend.layout.index')

@section('title', 'Artikel - Acegard Indonesia')

@section('content')

<section class="ace-section-sm text-center">
    <div class="container">
        <x-frontend.section-heading title="Artikel"
            subtitle="Temukan tips, panduan, dan informasi terpercaya seputar kaca film, PPF, serta solusi perlindungan kendaraan dan bangunan." />
    </div>
</section>

<section class="ace-section-sm pt-0">
    <div class="container">
        @if($featured)
            <div class="ace-card overflow-hidden mb-5 row g-0">
                <div class="col-md-6">
                    @if($featured->cover_image)
                        <img src="{{ asset('storage/' . $featured->cover_image) }}" alt="{{ $featured->title }}"
                            class="w-100 h-100" style="object-fit: cover; min-height: 260px;">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-uppercase fw-semibold ace-muted" style="min-height: 260px; background: var(--ace-bg-card);">
                            Image
                        </div>
                    @endif
                </div>
                <div class="col-md-6 p-4 d-flex flex-column ace-card-soft">
                    <h4 class="ace-navy-text mb-2">{{ $featured->title }}</h4>
                    <p class="ace-muted small flex-grow-1">{{ $featured->excerpt }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('frontend.artikel.show', $featured) }}" class="btn-ace-outline">Baca Selengkapnya</a>
                        <span class="small ace-muted">{{ optional($featured->published_at)->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
            <a href="{{ route('frontend.artikel') }}" class="ace-pill {{ !$activeCategory ? 'ace-pill-dark' : '' }}">All Post</a>
            @foreach($categories as $value => $label)
                <a href="{{ route('frontend.artikel', ['category' => $value]) }}" class="ace-pill {{ $activeCategory === $value ? 'ace-pill-dark' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="row g-4">
            @forelse($articles as $article)
                <div class="col-md-4">
                    <div class="ace-card-soft p-4 h-100 d-flex flex-column">
                        <a href="{{ route('frontend.artikel.show', $article) }}" class="text-decoration-none fw-semibold ace-navy-text mb-2">
                            {{ $article->title }}
                        </a>
                        <p class="ace-muted small flex-grow-1">{{ $article->excerpt }}</p>
                        <span class="small ace-muted">{{ optional($article->published_at)->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="ace-muted text-center mb-0">Belum ada artikel untuk kategori ini.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $articles->links() }}
        </div>
    </div>
</section>

@endsection
