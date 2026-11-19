@extends('frontend.layout.index')

@section('title', 'Artikel - Acegard Indonesia')
@section('meta_description', 'Artikel dan tips seputar kaca film mobil, kaca film gedung, dan Paint Protection Film dari ACEGARD Indonesia.')
@section('body-class', 'ag-page')

@section('content')

@php
  $date = fn ($a) => optional($a->published_at)->locale('id')->translatedFormat('d F Y');
@endphp

{{-- ───────── Hero ───────── --}}
<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-art-title">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Artikel</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">Articles</span>
    <h1 class="ag-display" id="ag-art-title">Tips dan panduan <b>kaca film dan PPF.</b></h1>
    <p class="ag-hero-sub">Informasi tepercaya seputar kaca film, PPF, serta perlindungan kendaraan dan bangunan.</p>
  </div>
</section>

<section class="ag-section" style="padding-top:24px" aria-label="Daftar artikel">
  <div class="ag-container">

    @if ($featured)
      <article class="ag-art-feature">
        <div class="media">
          @if ($featured->cover_image)
            <img src="{{ asset('storage/' . $featured->cover_image) }}" alt="{{ $featured->title }}" width="800" height="500" loading="lazy">
          @endif
        </div>
        <div class="body">
          <span class="ag-eyebrow" style="align-self:flex-start">{{ $categories[$featured->category] ?? $featured->category }}</span>
          <h2>{{ $featured->title }}</h2>
          <p style="margin:0;color:var(--ag-muted-strong)">{{ $featured->excerpt }}</p>
          <span class="ag-art-meta">{{ $date($featured) }}</span>
          <a class="ag-btn ag-btn-primary ag-btn-sm" style="align-self:flex-start" href="{{ route('frontend.artikel.show', $featured) }}">Baca Selengkapnya</a>
        </div>
      </article>
    @endif

    <nav class="ag-chips is-live" style="justify-content:center;margin:0 0 32px;padding:0;border:0" aria-label="Kategori artikel">
      <a class="ag-chip {{ ! $activeCategory ? 'is-on' : '' }}" href="{{ route('frontend.artikel') }}"@if (! $activeCategory) aria-current="page"@endif>Semua</a>
      @foreach ($categories as $value => $label)
        <a class="ag-chip {{ $activeCategory === $value ? 'is-on' : '' }}" href="{{ route('frontend.artikel', ['category' => $value]) }}"@if ($activeCategory === $value) aria-current="page"@endif>{{ $label }}</a>
      @endforeach
    </nav>

    @if ($articles->count())
      <div class="ag-grid c3">
        @foreach ($articles as $article)
          <a class="ag-card ag-art-card is-hover" href="{{ route('frontend.artikel.show', $article) }}" style="padding:24px">
            <span class="ag-art-meta">{{ $categories[$article->category] ?? $article->category }} · {{ $date($article) }}</span>
            <h3>{{ $article->title }}</h3>
            <p>{{ $article->excerpt }}</p>
            <span class="ag-link">Baca selengkapnya <iconify-icon icon="mdi:arrow-right" aria-hidden="true"></iconify-icon></span>
          </a>
        @endforeach
      </div>
    @elseif (! $featured)
      <div class="ag-fempty">
        <p style="margin:0">Belum ada artikel untuk kategori ini.</p>
      </div>
    @endif

    <div class="ag-pager">{{ $articles->links() }}</div>
  </div>
</section>

@endsection

@section('jsonld')
  {!! \App\Support\Seo::breadcrumbs([['Home', route('frontend.home')], ['Artikel', route('frontend.artikel')]]) !!}
@endsection
