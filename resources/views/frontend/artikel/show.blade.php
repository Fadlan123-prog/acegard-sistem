@extends('frontend.layout.index')

@php use App\Support\Whatsapp; @endphp

@section('title', $article->title . ' - Acegard Indonesia')
@section('meta_description', \Illuminate\Support\Str::limit(trim((string) ($article->excerpt ?: strip_tags((string) $article->body))), 155))
@if ($article->cover_image)
  @section('og_image', asset('storage/' . $article->cover_image))
  @section('og_image_alt', $article->title)
@endif
@section('body-class', 'ag-page')

@section('content')

@php
  $categoryLabel = \App\Models\Article::categories()[$article->category] ?? $article->category;
  $date = optional($article->published_at)->locale('id')->translatedFormat('d F Y');
@endphp

<section class="ag-hero ag-hero-sub-page" aria-labelledby="ag-art-h1">
  <div class="ag-container">
    <nav class="ag-crumbs" aria-label="Breadcrumb">
      <a href="{{ route('frontend.home') }}">Home</a><span aria-hidden="true">/</span><a href="{{ route('frontend.artikel') }}">Artikel</a><span aria-hidden="true">/</span><span aria-current="page">{{ \Illuminate\Support\Str::limit($article->title, 40) }}</span>
    </nav>
    <span class="ag-eyebrow ag-hero-tag">{{ $categoryLabel }}</span>
    <h1 class="ag-display" id="ag-art-h1" style="font-size:clamp(2rem,5vw,3.5rem)">{{ $article->title }}</h1>
    @if ($date)<p class="ag-hero-sub">{{ $date }}</p>@endif
  </div>
</section>

<section class="ag-section" style="padding-top:8px">
  <div class="ag-container">
    @if ($article->cover_image)
      <figure class="ag-shot ag-art-cover" style="aspect-ratio:16/9"><img src="{{ asset('storage/' . $article->cover_image) }}" alt="{{ $article->title }}" width="960" height="540"></figure>
    @endif

    <div class="ag-art-body">{{ $article->body }}</div>

    <div class="ag-cta-panel" style="margin-top:56px">
      <h2>Ingin konsultasi <b>soal artikel ini?</b></h2>
      <p>Tim ACEGARD siap menjawab pertanyaan Anda lewat WhatsApp.</p>
      <div class="ag-cta-actions">
        <a class="ag-btn ag-btn-light whatsapp-btn" data-gtm-label="article-whatsapp" target="_blank" rel="noopener"
           href="{{ Whatsapp::url('Halo ACEGARD, saya membaca artikel "' . $article->title . '" dan ingin berkonsultasi.') }}">
          <iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Konsultasi via WhatsApp
        </a>
        <a class="ag-btn ag-btn-ghost" href="{{ route('frontend.artikel') }}">Kembali ke Artikel</a>
      </div>
    </div>
  </div>
</section>

@endsection

@section('jsonld')
  {!! \App\Support\Seo::breadcrumbs([['Home', route('frontend.home')], ['Artikel', route('frontend.artikel')], [$article->title, route('frontend.artikel.show', $article)]]) !!}
  {!! \App\Support\Seo::article($article) !!}
@endsection
