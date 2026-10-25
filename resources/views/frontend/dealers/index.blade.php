@extends('frontend.layout.index')

@section('title', 'Dealer Resmi Acegard Indonesia')

@section('content')

<section class="ace-section-sm text-center">
    <div class="container">
        <x-frontend.section-heading title="Dealer Resmi" highlight="ACEGARD"
            subtitle="Temukan dealer resmi ACEGARD terdekat untuk mendapatkan produk original, pemasangan profesional, dan garansi resmi." />
    </div>
</section>

@if($cabangUtama->isNotEmpty())
    <section class="ace-section-sm pt-0">
        <div class="container text-center">
            <x-frontend.section-heading highlight="Cabang Utama" />

            <div class="row justify-content-center">
                @foreach($cabangUtama as $dealer)
                    <div class="col-md-6 col-lg-4 text-start">
                        <x-frontend.dealer-card :dealer="$dealer" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section class="ace-section-sm pt-0">
    <div class="container text-center">
        <x-frontend.section-heading highlight="Dealers" />

        <div class="row g-4 text-start">
            @forelse($dealers as $dealer)
                <div class="col-md-6 col-lg-4">
                    <x-frontend.dealer-card :dealer="$dealer" />
                </div>
            @empty
                <div class="col-12">
                    <p class="ace-muted text-center mb-0">Belum ada data dealer.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@endsection
