@extends('frontend.layout.index')

@section('title', 'Kaca Film Otomotif - Acegard Indonesia')

@section('content')

@php
    $waLink = fn ($text) => 'https://wa.me/6282121212093?text=' . urlencode($text);

    $depan = [
        ['image' => asset('assets/frontend/figma/otomotif/depan-1-notch-uv400.png'), 'caption' => 'Lorem Ipsum', 'name' => 'NOTCH UV400', 'tagline' => 'Jernih & Tetap Sejuk', 'desc' => 'Kejernihan maksimal dengan perlindungan panas yang efektif. Tampilan tetap natural, kenyamanan tetap terjaga.', 'irr' => '00%', 'uvr' => '00%', 'tser' => '00%'],
        ['image' => asset('assets/frontend/figma/otomotif/depan-2-4k-alpha-pro.png'), 'caption' => 'Lorem Ipsum', 'name' => '4K ALPHA PRO', 'tagline' => 'Nyaman dengan Privasi yang Pas', 'desc' => 'Perpaduan ideal antara visibilitas, kenyamanan, dan privasi. Tetap jernih dari dalam, stylish dari luar.', 'irr' => '00%', 'uvr' => '00%', 'tser' => '00%'],
    ];

    $samping = [
        ['image' => asset('assets/frontend/figma/otomotif/samping-flex-one-way.png'), 'caption' => 'FLEX ONE WAY', 'name' => 'FLEX ONE WAY', 'irr' => '73%', 'uvr' => '99%', 'tser' => '73%'],
        ['image' => asset('assets/frontend/figma/otomotif/samping-black-master.png'), 'caption' => 'BLACK MASTER', 'name' => 'BLACK MASTER', 'irr' => '90%', 'uvr' => '99%', 'tser' => '75%'],
        ['image' => asset('assets/frontend/figma/otomotif/samping-notch-uv400.png'), 'caption' => 'NOTCH UV400', 'name' => 'NOTCH UV400', 'irr' => '98%', 'uvr' => '99%', 'tser' => '83%'],
        ['image' => asset('assets/frontend/figma/otomotif/samping-flex-one-way.png'), 'caption' => '4K ALPHA PRO', 'name' => '4K ALPHA PRO', 'irr' => '90%', 'uvr' => '99%', 'tser' => '75%'],
    ];

    $sunroof = [
        ['image' => asset('assets/frontend/figma/otomotif/sunroof-1.png'), 'caption' => 'NOTCH UV400', 'name' => 'NOTCH UV400', 'irr' => '73%', 'uvr' => '99%', 'tser' => '73%'],
        ['image' => asset('assets/frontend/figma/otomotif/sunroof-2.png'), 'caption' => 'NOTCH UV400', 'name' => 'NOTCH UV400', 'irr' => '90%', 'uvr' => '99%', 'tser' => '75%'],
        ['image' => asset('assets/frontend/figma/otomotif/sunroof-3.png'), 'caption' => 'NOTCH UV400', 'name' => 'NOTCH UV400', 'irr' => '98%', 'uvr' => '99%', 'tser' => '83%'],
    ];

    $seri = [
        ['image' => asset('assets/frontend/figma/otomotif/seri-1.png'), 'caption' => '4K ALPHA PRO', 'name' => '4K ALPHA PRO', 'irr' => '00%', 'uvr' => '00%', 'tser' => '00%'],
        ['image' => asset('assets/frontend/figma/otomotif/seri-2.png'), 'caption' => 'NOTCH UV400', 'name' => 'NOTCH UV400', 'irr' => '00%', 'uvr' => '00%', 'tser' => '00%'],
        ['image' => asset('assets/frontend/figma/otomotif/seri-3.png'), 'caption' => 'NOTCH UV400', 'name' => 'NOTCH UV400', 'irr' => '00%', 'uvr' => '00%', 'tser' => '00%'],
    ];

    $genericDesc = 'Kejernihan maksimal dengan perlindungan panas yang efektif. Tampilan tetap natural, kenyamanan tetap terjaga.';
@endphp

{{-- Hero --}}
<section class="ace-section-sm text-center">
    <div class="container">
        <h1 class="fw-normal mb-3">Rasakan Kenyamanan Berkendara<br>dalam Kemewahan</h1>

        <div class="d-flex justify-content-center gap-2 flex-wrap mb-4 ace-muted">
            <a href="#depan" class="text-decoration-none ace-muted">Depan</a> |
            <a href="#samping-belakang" class="text-decoration-none ace-muted">Samping</a> |
            <a href="#samping-belakang" class="text-decoration-none ace-muted">Belakang</a> |
            <a href="#sunroof" class="text-decoration-none ace-muted">Sunroof</a>
        </div>

        <x-frontend.button variant="gradient" :href="$waLink('Halo Acegard, saya ingin konsultasi kaca film otomotif')" class="whatsapp-btn" target="_blank">
            Konsultasi Dengan Kami Gratis di Whatsapp
        </x-frontend.button>
    </div>
</section>

{{-- Depan --}}
<section id="depan" class="ace-section-sm">
    <div class="container">
        <x-frontend.section-heading title="Rekomendasi Kaca Film" highlight="Depan" />

        <div class="row g-4">
            @foreach($depan as $item)
                <div class="col-md-6">
                    <x-frontend.product-card
                        :image="$item['image']"
                        :badge="$item['caption']"
                        :title="$item['name'] . ' — ' . $item['tagline']"
                        :desc="$item['desc']"
                        class="mb-3"
                    />
                    <x-frontend.spec-table
                        title="Spesifikasi | Lorem Ipsum"
                        :rows="[['IRR', $item['irr']], ['UVR', $item['uvr']], ['TSER', $item['tser']]]"
                        :cta="['label' => 'Hubungi Kami', 'href' => $waLink('Halo Acegard, saya tertarik dengan kaca film ' . $item['name']), 'whatsapp' => true]"
                    />
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Samping & Belakang --}}
<section id="samping-belakang" class="ace-section-sm">
    <div class="container">
        <x-frontend.section-heading title="Rekomendasi Kaca Film" highlight="Samping & Belakang" />

        <div class="row g-4">
            @foreach($samping as $item)
                <div class="col-md-6 col-lg-3">
                    <x-frontend.product-card
                        :image="$item['image']"
                        :badge="$item['caption']"
                        :title="$item['name'] . ' — Jernih & Tetap Sejuk'"
                        :desc="$genericDesc"
                        class="mb-3"
                    />
                    <x-frontend.spec-table
                        :title="'Spesifikasi | ' . ucwords(strtolower($item['name']))"
                        :rows="[['IRR', $item['irr']], ['UVR', $item['uvr']], ['TSER', $item['tser']]]"
                        :cta="['label' => 'Hubungi Kami', 'href' => $waLink('Halo Acegard, saya tertarik dengan kaca film ' . $item['name']), 'whatsapp' => true]"
                    />
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Sunroof --}}
<section id="sunroof" class="ace-section-sm">
    <div class="container">
        <x-frontend.section-heading title="Rekomendasi Kaca Film" highlight="Sunroof" />

        <div class="row g-4">
            @foreach($sunroof as $item)
                <div class="col-md-4">
                    <x-frontend.product-card
                        :image="$item['image']"
                        :badge="$item['caption']"
                        :title="$item['name'] . ' — Jernih & Tetap Sejuk'"
                        :desc="$genericDesc"
                        class="mb-3"
                    />
                    <x-frontend.spec-table
                        title="Spesifikasi | Lorem Ipsum"
                        :rows="[['IRR', $item['irr']], ['UVR', $item['uvr']], ['TSER', $item['tser']]]"
                        :cta="['label' => 'Hubungi Kami', 'href' => $waLink('Halo Acegard, saya tertarik dengan kaca film ' . $item['name']), 'whatsapp' => true]"
                    />
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Rekomendasi Seri Kaca Film ACEGARD --}}
<section class="ace-section-sm">
    <div class="container">
        <x-frontend.section-heading title="Rekomendasi Seri Kaca Film" highlight="ACEGARD" />

        <div class="row g-4">
            @foreach($seri as $item)
                <div class="col-md-4">
                    <x-frontend.product-card
                        :image="$item['image']"
                        :badge="$item['caption']"
                        :title="$item['name'] . ' — Jernih & Tetap Sejuk'"
                        :desc="$genericDesc"
                        class="mb-3"
                    />
                    <x-frontend.spec-table
                        title="Spesifikasi | Lorem Ipsum"
                        :rows="[['IRR', $item['irr']], ['UVR', $item['uvr']], ['TSER', $item['tser']]]"
                        :cta="['label' => 'Hubungi Kami', 'href' => $waLink('Halo Acegard, saya tertarik dengan kaca film ' . $item['name']), 'whatsapp' => true]"
                    />
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
