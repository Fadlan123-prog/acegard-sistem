@extends('frontend.layout.index')

@section('title', 'Kaca Film Bangunan - Acegard Indonesia')

@section('content')

@php
    $waLink = fn ($text) => 'https://wa.me/6282121212093?text=' . urlencode($text);

    $filmTypes = ['Flex One Way', 'Black Master', 'Notch UV400', '4K Alpha Pro'];
    $specRows = array_map(fn ($name) => [$name, '00%', '00%', '00%'], $filmTypes);
@endphp

{{-- Hero --}}
<section class="ace-section-sm text-center">
    <div class="container" style="max-width: 760px;">
        <h1 class="fw-normal mb-3">
            Nikmati Pemandangan Dengan <span class="ace-gradient-text">Lebih Nyaman</span>
        </h1>

        <x-frontend.button variant="gradient" :href="$waLink('Halo Acegard, saya ingin konsultasi kaca film bangunan')" class="whatsapp-btn" target="_blank">
            Konsultasi Dengan Kami Gratis di Whatsapp
        </x-frontend.button>

        <h2 class="fw-normal mt-5 mb-3">
            Bagaimana Cara Menjaga <span class="ace-gradient-text">Kenyamanan Keluarga</span> dari Balik Jendela?
        </h2>
        <p class="ace-muted mb-0">
            Dengan teknologi penolak panas dan perlindungan UV tinggi, ACEGARD menciptakan ruangan yang lebih nyaman,
            melindungi keluarga maupun karyawan, serta menjaga tampilan interior tetap awet.
        </p>
    </div>
</section>

{{-- Lifestyle photos --}}
<section class="ace-section-sm pt-0">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <img src="{{ asset('assets/frontend/figma/bangunan/lifestyle-1.png') }}" alt="Kaca film pada ruang kerja"
                    class="w-100 rounded-4" style="aspect-ratio: 4/3; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <img src="{{ asset('assets/frontend/figma/bangunan/lifestyle-2.png') }}" alt="Kenyamanan keluarga di rumah"
                    class="w-100 rounded-4" style="aspect-ratio: 4/3; object-fit: cover;">
            </div>
        </div>
    </div>
</section>

{{-- Rekomendasi Kaca Film Clear --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 900px;">
        <x-frontend.section-heading title="Rekomendasi Kaca Film" highlight="Clear" />

        <x-frontend.spec-table
            :headers="['Tipe Kaca Film', 'IRR', 'UVR', 'TSER']"
            :rows="$specRows"
            :cta="['label' => 'Hubungi Kami', 'href' => $waLink('Halo Acegard, saya tertarik dengan kaca film bangunan clear'), 'whatsapp' => true]"
        />
    </div>
</section>

{{-- Rekomendasi Kaca Film Privasi --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 900px;">
        <x-frontend.section-heading title="Rekomendasi Kaca Film" highlight="Privasi" />

        <x-frontend.spec-table
            :headers="['Tipe Kaca Film', 'IRR', 'UVR', 'TSER']"
            :rows="$specRows"
            :cta="['label' => 'Hubungi Kami', 'href' => $waLink('Halo Acegard, saya tertarik dengan kaca film bangunan privasi'), 'whatsapp' => true]"
        />
    </div>
</section>

@endsection
