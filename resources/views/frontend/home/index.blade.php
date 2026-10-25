@extends('frontend.layout.index')

@section('title', 'Acegard Indonesia - Kaca Film Otomotif, Bangunan & PPF')

@section('content')

{{-- Hero --}}
<section class="ace-section-sm pb-0">
    <div class="container">
        <x-frontend.image-tile
            image="{{ asset('assets/frontend/figma/home/hero-otomotif.png') }}"
            image-alt="Kaca Film Otomotif"
            title="Kaca Film Otomotif"
            :href="route('frontend.service.car')"
            height="480px"
        />
    </div>
</section>

{{-- Kaca Film Bangunan + PPF --}}
<section class="ace-section-sm">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <x-frontend.image-tile
                    image="{{ asset('assets/frontend/figma/home/tile-bangunan.png') }}"
                    image-alt="Kaca Film Bangunan"
                    title="Kaca Film Bangunan"
                    :href="route('frontend.service.building')"
                    height="360px"
                />
            </div>
            <div class="col-md-6">
                <x-frontend.image-tile
                    image="{{ asset('assets/frontend/figma/home/tile-ppf.png') }}"
                    image-alt="PPF"
                    title="PPF"
                    :href="Route::has('frontend.ppf.catalog') ? route('frontend.ppf.catalog') : '#'"
                    height="360px"
                />
            </div>
        </div>
    </div>
</section>

{{-- Kenapa memilih Acegard + Butuh bantuan --}}
<section class="ace-section-sm">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <x-frontend.feature-card
                    icon="mdi:tray-arrow-up"
                    title="Pemasangan Presisi"
                    desc="consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
                />
            </div>
            <div class="col-md-6">
                <x-frontend.feature-card
                    icon="mdi:headset"
                    title="Pelayanan Prima"
                    desc="consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
                />
            </div>
            <div class="col-md-6">
                <x-frontend.feature-card
                    icon="mdi:shield-check"
                    title="Garansi Terjamin"
                    desc="consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
                />
            </div>
            <div class="col-md-6">
                <div class="ace-card-soft p-4 h-100">
                    <h5 class="mb-1">Butuh Bantuan?</h5>
                    <p class="ace-muted small mb-3">Lorem Ipsum Lorem ipsum dolor sit amet consectetur adipiscing elit</p>

                    <form>
                        <div class="mb-3 border-bottom" style="border-color: var(--ace-border) !important;">
                            <input type="text" class="form-control border-0 bg-transparent px-0" placeholder="Full Name*">
                        </div>
                        <div class="mb-3 border-bottom" style="border-color: var(--ace-border) !important;">
                            <input type="email" class="form-control border-0 bg-transparent px-0" placeholder="Email*">
                        </div>
                        <div class="mb-3 border-bottom" style="border-color: var(--ace-border) !important;">
                            <input type="text" class="form-control border-0 bg-transparent px-0" placeholder="Phone Number*">
                        </div>
                        <div class="mb-3 border-bottom" style="border-color: var(--ace-border) !important;">
                            <textarea class="form-control border-0 bg-transparent px-0" rows="2" placeholder="Message*"></textarea>
                        </div>
                        <x-frontend.button type="submit" variant="gradient">SEND</x-frontend.button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Google Maps --}}
<section class="ace-section-sm">
    <div class="container">
        <x-frontend.section-heading
            title="Temukan Kami di"
            highlight="Google Maps"
            subtitle="Kunjungi workshop kami untuk mendapatkan layanan terbaik dengan kualitas yang tidak diragukan lagi."
        />

        <div class="position-relative rounded-4 overflow-hidden">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7932.866895562227!2d106.6881984!3d-6.2064166!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ed2a0d3377ef%3A0x49dc44beb8c49b02!2sAcegard%20Indonesia!5e0!3m2!1sid!2sid!4v1755761775295!5m2!1sid!2sid"
                width="100%" height="380" style="border:0; display:block;" allowfullscreen=""
                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

            <div class="position-absolute bottom-0 start-0 w-100 text-white text-center small fw-semibold py-2 px-3" style="background: var(--ace-navy);">
                Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146
            </div>
        </div>
    </div>
</section>

@endsection
