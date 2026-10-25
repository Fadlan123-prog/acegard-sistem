@extends('frontend.layout.index')

@section('title', 'Tentang Kami - Acegard Indonesia')

@section('content')

@php
    $waLink = fn ($text) => 'https://wa.me/6282121212093?text=' . urlencode($text);

    $whyUs = [
        ['icon' => 'mdi:certificate-outline', 'title' => 'Made In Korea', 'desc' => 'Dirancang dengan standar kualitas tinggi untuk penggunaan jangka panjang.'],
        ['icon' => 'mdi:white-balance-sunny', 'title' => '99% Heat Rejection', 'desc' => 'Mengurangi panas matahari agar ruangan tetap sejuk dan nyaman.'],
        ['icon' => 'mdi:shield-sun-outline', 'title' => 'UV Protection', 'desc' => 'Melindungi kulit dan interior dari paparan sinar UV.'],
        ['icon' => 'mdi:flash-outline', 'title' => 'Energy Efficiency', 'desc' => 'Membantu mengurangi konsumsi listrik AC.'],
        ['icon' => 'mdi:shield-outline', 'title' => 'Color Stability', 'desc' => 'Warna tetap konsisten meski digunakan bertahun-tahun.'],
        ['icon' => 'mdi:signal', 'title' => 'Signal Friendly', 'desc' => 'Tetap nyaman tanpa mengganggu sinyal seluler, WiFi, maupun GPS.'],
    ];

    $timeline = [
        'High TSER Performence', 'Tungsten Thermal Layer', 'Night Vision Optical Clarity',
        'UV400 Full Spectrum Protection', 'Non-Metal Signal Friendly',
        'Color Fade Resistant', 'Energy Efficient Color System',
    ];

    $clients = ['BTN', 'Jasamarga', 'Kementerian Koordinator Bidang Kemaritiman dan Investasi', 'Mercure Hotels', 'Cengkareng Heliport'];

    $carBrands = ['nissan', 'toyota', 'bmw', 'hyundai', 'mercy', 'honda', 'mazda', 'vw'];

    $testimonials = [
        ['name' => 'Jessica Iskandar', 'photo' => asset('assets/frontend/building/img/faces/1.jpg'), 'quote' => 'Suka banget sama kaca film nya bikin tampilan kaca rumah lebih elegan dan ruangan jadi lebih adem'],
        ['name' => 'Presdir Bank INA', 'photo' => null, 'quote' => 'Ac jadi lebih cepat dingin dan kinerja listrik berkurang drastis, kualitas Acegard memang bukan kaleng-kaleng'],
        ['name' => 'Bos Daikin', 'photo' => null, 'quote' => 'Awalnya hanya coba-coba, ternyata adem banget ac central saya jadi lebih dingin, siang pun tidak silau'],
    ];
@endphp

{{-- Intro banner --}}
<section class="ace-section-sm pb-0">
    <div class="container">
        <div class="rounded-4 p-5 mb-5" style="background: linear-gradient(120deg, #eef4ff, #ffffff 60%);">
            <h2 class="fw-normal mb-4" style="max-width: 420px;">ACEGARD NANO CERAMIC WINDOW FILM</h2>
            <iconify-icon icon="mdi:arrow-down" class="fs-2 ace-navy-text"></iconify-icon>
        </div>
    </div>
</section>

{{-- Nano ceramic tech story --}}
<section class="ace-section-sm pt-0 text-center">
    <div class="container" style="max-width: 820px;">
        <h2 class="fw-normal mb-3">KACA FILM NANO DENGAN <span class="ace-gradient-text">SOLAR REJECT TERBAIK</span></h2>
        <p class="ace-muted">
            Acegard mengadaptasi teknologi <span class="ace-gradient-text fw-semibold">NANO CERAMIC MATRIX COMPOSITE</span>
            lapisan dasar dual-layer Nano Ceramic yang diproses dengan teknologi sputtering dan diperkuat dengan elemen
            logam tingkat tinggi (Tin, Titanium &amp; Tungsten). Kombinasi ini menghasilkan 7 multi layer yang stabil
            secara optik, tahan lama, dan mampu menolak gelombang energi matahari hingga ke spektrum 2500 nanometer
            tanpa mengganggu sinyal elektronik.
        </p>

        <x-frontend.button variant="gradient" :href="$waLink('Halo Acegard, saya ingin tahu lebih lanjut tentang teknologi Nano Ceramic')" class="whatsapp-btn mb-5" target="_blank">
            More Information
        </x-frontend.button>

        <div class="ace-card-soft p-4 row g-3 text-start align-items-center">
            <div class="col-md-5">
                <div class="fw-semibold small text-uppercase ace-navy-text mb-1"><iconify-icon icon="mdi:map-marker"></iconify-icon> Location</div>
                <div class="small ace-muted">Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146</div>
            </div>
            <div class="col-md-1 d-none d-md-block text-center">
                <div style="border-left: 1px solid var(--ace-border); height: 40px;"></div>
            </div>
            <div class="col-md-3">
                <a href="{{ $waLink('Halo Acegard, saya ingin konsultasi') }}" target="_blank" class="whatsapp-btn text-decoration-none fw-semibold ace-navy-text">
                    <iconify-icon icon="mdi:whatsapp"></iconify-icon> Whatsapp
                </a>
            </div>
            <div class="col-md-3">
                <a href="mailto:acegardindonesia@gmail.com" class="text-decoration-none fw-semibold ace-navy-text">
                    <iconify-icon icon="mdi:email-outline"></iconify-icon> Email Address
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Night vision --}}
<section class="ace-section-sm">
    <div class="container">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <img src="{{ asset('assets/frontend/car/images/audi-car.webp') }}" alt="Interior kendaraan"
                    class="w-100 rounded-4" style="aspect-ratio: 4/3; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <img src="{{ asset('assets/frontend/building/img/covers/building-15.png') }}" alt="Pemandangan dari gedung"
                    class="w-100 rounded-4" style="aspect-ratio: 4/3; object-fit: cover;">
            </div>
        </div>

        <div style="max-width: 820px;" class="mx-auto text-center">
            <h2 class="fw-normal mb-3">Teknologi <span class="ace-gradient-text">Night Vision</span> Dengan Daya Tolak Panas 99,9%</h2>
            <p class="ace-muted">
                Night Vision Pada Acegard Dirancang Melalui Optical-Grade Nano Ceramic Engineering Yang Bekerja Secara
                Presisi Pada Spektrum Cahaya Tampak.
            </p>
            <p class="ace-muted">
                Struktur teknologi Nano Ceramic Matrix Composite kami dikalibrasi untuk menjaga Visible Light
                Transmission (VLT) tetap optimal, sehingga mata dapat menangkap lebih banyak detail permukaan jalan di
                kondisi cahaya rendah. Setiap partikel nano didesain untuk mengontrol bias optik, mengurangi distorsi,
                dan mempertahankan tingkat kontras visual yang stabil.
            </p>
            <p class="ace-muted">
                Lapisan Nano Ceramic Non-Metallic kami juga mengurangi efek glare dari lampu kendaraan lain dan sinar
                lampu jalan yang berlebihan. Hal ini menekan efek halo pada pupil, meminimalkan kelelahan mata, dan
                menjaga respon visual pengemudi tetap tajam dalam durasi perjalanan yang lebih panjang.
            </p>
            <p class="ace-muted mb-4">
                Ketika digabungkan dengan kemampuan filtrasi spektrum inframerah dan ultraviolet, Anda mendapatkan
                peningkatan visibilitas malam yang tidak hanya terlihat lebih jelas, tetapi juga terasa lebih ringan
                pada mata.
            </p>

            <x-frontend.button variant="gradient" :href="$waLink('Halo Acegard, saya ingin tahu lebih lanjut tentang teknologi Night Vision')" class="whatsapp-btn">
                More Information
            </x-frontend.button>
        </div>
    </div>
</section>

{{-- Kenapa memilih Acegard --}}
<section class="ace-section-sm">
    <div class="container">
        <x-frontend.section-heading title="Kenapa Memilih Kaca Film Dari" highlight="Acegard ?"
            subtitle="Setiap produk diproduksi di pabrik bersertifikat internasional di Korea dan melewati kontrol kualitas berlapis sebelum didistribusikan resmi di Indonesia. Konsumen mendapatkan kepastian garansi, layanan after sales yang jelas, teknisi bersertifikat, serta kualitas optik yang konsisten di seluruh varian produk." />

        <div class="row g-4">
            @foreach($whyUs as $item)
                <div class="col-md-4">
                    <x-frontend.feature-card :icon="$item['icon']" :title="$item['title']" :desc="$item['desc']" href="#" />
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Energy efficiency detail --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 720px;">
        <h3 class="fw-normal mb-2">Energy Efficiency</h3>
        <p class="ace-navy-text fw-semibold mb-3">Teknologi Cerdas yang Teruji Menjaga Suhu Tetap Stabil.</p>
        <p class="ace-muted mb-0">
            Acegard secara signifikan mengurangi panas yang masuk ke kabin mobil atau ruangan rumah Anda, menurunkan
            suhu interior hingga ±5°C, serta membuat sistem AC bekerja lebih ringan dan stabil. Hasilnya, konsumsi
            energi kendaraan dapat berkurang hingga ±25% dalam penggunaan harian, menghadirkan kenyamanan yang terasa
            sejuk sejak Anda masuk ke mobil/rumah, sekaligus memberikan efisiensi bahan bakar dan umur kompresor yang
            lebih panjang.
        </p>
    </div>
</section>

{{-- Keunggulan timeline --}}
<section class="ace-section-sm">
    <div class="container text-center">
        <x-frontend.section-heading title="Keunggulan Kaca Film" highlight="Acegard"
            subtitle="Acegard satu-satunya di Indonesia yang berteknologi Nano Ceramic Matrix Composite yang diperkuat lapisan Titanium dan Tungsten, mampu menolak panas inframerah hingga 99,9% dengan TSER maksimal 88% untuk menjaga suhu tetap sejuk." />

        <div class="row g-4 justify-content-center">
            @foreach($timeline as $i => $label)
                <div class="col-6 col-md-3 col-lg-auto">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold text-white mb-2"
                        style="width: 32px; height: 32px; background: var(--ace-gradient);">
                        {{ $i + 1 }}
                    </div>
                    <div class="small ace-navy-text">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Layanan kami --}}
<section class="ace-section-sm">
    <div class="container text-center" style="max-width: 780px;">
        <x-frontend.section-heading title="Layanan" highlight="Kami" subtitle="Layanan Premium dengan Standar Teknologi Tinggi" />

        <p class="ace-muted">
            Kami tidak hanya berfokus pada kualitas produk, tetapi juga pada pengalaman dan kepercayaan pelanggan di
            setiap langkahnya.
        </p>
        <p class="ace-muted mb-4">
            Acegard percaya bahwa produk hebat harus diiringi dengan pelayanan yang luar biasa. Karena itu, kami
            menghadirkan layanan yang cepat, profesional, dan transparan, mulai dari konsultasi hingga dukungan after
            sales. Komitmen kami sederhana, memastikan setiap pelanggan merasa puas, terlindungi, dan yakin bahwa
            mereka telah memilih kaca film dengan kualitas, pelayanan, dan integritas terbaik di kelasnya.
        </p>

        <x-frontend.button variant="gradient" :href="route('frontend.contact')" class="mb-5">
            Contact Us
        </x-frontend.button>
    </div>

    <div class="container">
        <div class="row g-4">
            @foreach(array_slice($whyUs, 2, 4) as $item)
                <div class="col-md-3 col-6">
                    <x-frontend.feature-card :icon="$item['icon']" :title="$item['title']" :desc="$item['desc']" href="#" />
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Klien --}}
<section class="ace-section-sm ace-card-soft">
    <div class="container text-center">
        <x-frontend.section-heading title="Klien Kaca Film Bangunan" highlight="Kami" subtitle="Mitra Terpercaya untuk Estetika dan Kenyamanan Arsitektur." />

        <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
            @foreach($clients as $client)
                <span class="bg-white border rounded-3 px-4 py-3 small fw-semibold ace-navy-text" style="border-color: var(--ace-border) !important;">
                    {{ $client }}
                </span>
            @endforeach
        </div>

        <x-frontend.section-heading title="We Work With Different" highlight="Partner" subtitle="Mitra brand kendaraan yang kompatibel dengan produk Acegard." />

        <div class="d-flex flex-wrap justify-content-center align-items-center gap-4">
            @foreach($carBrands as $brand)
                <img src="{{ asset("assets/frontend/images/cars-logo/{$brand}.png") }}" alt="{{ ucfirst($brand) }}" style="height: 48px; width: auto;">
            @endforeach
        </div>
    </div>
</section>

{{-- Testimoni --}}
<section class="ace-section-sm text-center">
    <div class="container">
        <x-frontend.section-heading title="Apa kata" highlight="mereka?" subtitle="Pengalaman Jujur Bersama Kaca Film Terbaik." />

        <div class="row g-4 text-start">
            @foreach($testimonials as $t)
                <div class="col-md-4">
                    <div class="ace-card-soft p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            @if($t['photo'])
                                <img src="{{ $t['photo'] }}" alt="{{ $t['name'] }}" class="rounded-circle" style="width:40px; height:40px; object-fit: cover;">
                            @else
                                <iconify-icon icon="mdi:account-circle" class="fs-1 ace-muted"></iconify-icon>
                            @endif
                            <span class="fw-semibold ace-navy-text">{{ $t['name'] }}</span>
                        </div>
                        <p class="small ace-muted mb-2">{{ $t['quote'] }}</p>
                        <div style="color: var(--ace-warning-ink, #f5b400);">
                            @for($i = 0; $i < 5; $i++)
                                <iconify-icon icon="mdi:star"></iconify-icon>
                            @endfor
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Contact form --}}
<section class="ace-section-sm text-center">
    <div class="container" style="max-width: 720px;">
        <x-frontend.section-heading title="Ketik Pesanmu" highlight="Sekarang"
            subtitle="Kami ingin mempermudah komunikasi dengan Anda. Kirimkan pesan sekarang dan kami akan segera menghubungi Anda." />

        <div class="ace-card-soft p-4 p-md-5 text-start">
            <div class="mb-2">
                <span class="fw-semibold ace-navy-text">Get in Touch</span>
            </div>
            <p class="small ace-muted mb-4">Lorem Ipsum Lorem ipsum dolor sit amet consectetur adipiscing elit</p>

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
                <div class="mb-4 border-bottom" style="border-color: var(--ace-border) !important;">
                    <textarea class="form-control border-0 bg-transparent px-0" rows="2" placeholder="Massage*"></textarea>
                </div>
                <x-frontend.button type="submit" variant="gradient">SEND</x-frontend.button>
            </form>
        </div>
    </div>
</section>

{{-- Google Maps --}}
<section class="ace-section-sm">
    <div class="container">
        <x-frontend.section-heading title="Temukan Kami di" highlight="Google Maps"
            subtitle="Kunjungi workshop kami untuk mendapatkan layanan terbaik dengan kualitas yang tidak diragukan lagi." />

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
