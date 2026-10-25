@extends('frontend.layout.index')

@section('title', 'Kaca Film Rumah - Tolak Panas 99%, Anti UV & Hemat Energi | Acegard')

{{-- Landing page Google Ads: tanpa navbar/footer situs, layout & konten mengikuti Figma 1:1 --}}
@section('hide-navbar', '1')
@section('hide-footer', '1')

@push('styles')
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5DH3T7FD');</script>
<!-- End Google Tag Manager -->
@endpush

@section('content')

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DH3T7FD"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

@php
    $waLink = fn ($text) => 'https://wa.me/628212003331?text=' . urlencode($text);

    // target = angka murni yang di-animasikan naik dari 0; decimals pakai koma ala Indonesia (mis. 4,9)
    $stats = [
        ['dot' => '#f6c344', 'target' => 2100, 'decimals' => 0, 'suffix' => '+',      'label' => 'Pelanggan Puas'],
        ['dot' => '#2f6bff', 'target' => 99,   'decimals' => 0, 'suffix' => '%',      'label' => 'Tolak Panas'],
        ['dot' => '#34a853', 'target' => 4.9,  'decimals' => 1, 'suffix' => '',       'label' => 'di Tokopedia'],
        ['dot' => '#e5484d', 'target' => 7,    'decimals' => 0, 'suffix' => ' Tahun', 'label' => 'Jaminan Garansi'],
    ];

    // Kartu "Mengapa": title+desc di atas, panah + ikon besar di baris bawah (sesuai Figma)
    $reasons = [
        [
            'icon' => 'mdi:white-balance-sunny',
            'title' => 'Tolak Panas Tinggi',
            'desc' => 'Kaca Film Acegard mampu menolak sinar inframerah hingga 99% dan menolak sinar ultraviolet hingga 99% karena menggunakan 7 Layer Chemical, 2 lapisan diantaranya menggunakan material Ceramic.',
        ],
        [
            'icon' => 'mdi:lightning-bolt',
            'title' => 'Energi Efisiensi',
            'desc' => 'Energi efisiensi pada kaca film mengacu pada kemampuannya mengurangi konsumsi energi dalam suatu ruangan. Kaca film yang efisien energi dapat mengurangi beban kerja AC atau kompressor, sehingga membantu penghematan daya.',
        ],
        [
            'icon' => 'mdi:diamond-stone',
            'title' => 'Material Kuat & Tahan Lama',
            'desc' => 'Material Ceramic umumnya menggunakan Silica, berbeda dengan Acegard, Acegard menggunakan bahan Ceramic yang di tergabung dari Mineral Tin dan Tungsten, yang mampu membuat Kaca Film ini Tahan lebih lama dan tidak akan memudar.',
        ],
        [
            'icon' => 'mdi:check-decagram',
            'title' => 'Bergaransi Resmi',
            'desc' => 'Bergaransi resmi 3 hingga 7 tahun untuk tipe tertentu, Garansi ini meliputi jika kaca film tidak menempel pada kaca dan warna yang luntur. Garansi Acegard terdaftar resmi dalam situs Acegard.id',
        ],
    ];

    // Kartu "Keunggulan": ikon+panah di atas, title (gradient)+desc di bawah
    $features = [
        ['icon' => 'circle-flags:kr', 'title' => 'Made in Korea', 'desc' => 'Dirancang dengan standar kualitas tinggi untuk penggunaan jangka panjang'],
        ['icon' => 'mdi:white-balance-sunny', 'title' => '99% Heat Rejection', 'desc' => 'Mengurangi panas matahari agar ruangan tetap sejuk dan nyaman'],
        ['icon' => 'mdi:account-hard-hat-outline', 'title' => 'UV Protection', 'desc' => 'Melindungi kulit dan interior dari paparan sinar UV'],
        ['icon' => 'mdi:chart-bar', 'title' => 'High TSER Performance', 'desc' => 'Efektivitas tinggi dalam mengurangi panas yang masuk ke ruangan'],
        ['icon' => 'mdi:lightning-bolt', 'title' => 'Energy Efficiency', 'desc' => 'Membantu mengurangi konsumsi listrik AC'],
        ['icon' => 'mdi:shield-outline', 'title' => 'Color Stability', 'desc' => 'Warna tetap konsisten meski digunakan bertahun-tahun'],
        ['icon' => 'mdi:signal', 'title' => 'Signal Friendly', 'desc' => 'Tetap nyaman tanpa mengganggu sinyal seluler, WiFi, maupun GPS'],
        ['icon' => 'mdi:check-circle', 'title' => '7-Year Warranty', 'desc' => 'Garansi resmi sebagai jaminan kualitas dan ketahanan produk'],
    ];

    $testimonials = [
        [
            'name' => 'Jessica Iskandar',
            'quote' => 'Suka banget sama kaca film nya bikin tampilan kaca rumah lebih elegan dan ruangan jadi lebih adem',
            'avatar' => 'testi-jessica.webp',
        ],
        [
            'name' => 'Presdir Bank INA',
            'quote' => 'Ac jadi lebih cepat dingin dan kinerja listrik berkurang drastis, kualitas Acegard memang bukan kaleng-kaleng',
            'avatar' => null,
        ],
        [
            'name' => 'Bos Daikin',
            'quote' => 'Awalnya hanya coba-coba, ternyata adem banget, ac central saya jadi lebih dingin, siang pun tidak silau',
            'avatar' => 'testi-daikin.webp',
        ],
    ];

    $clients = [
        'btn' => 'Bank BTN', 'topgolf' => 'Topgolf', 'jasamarga' => 'Jasa Marga',
        'kemenko' => 'Kemenko Marves', 'mercure' => 'Mercure Hotels', 'heliport' => 'Cengkareng Heliport',
    ];

    // priceNum = angka murni untuk animasi hitung naik; ser/uv/ir juga angka murni (tanpa "%").
    // ser/uv/ir diambil dari varian dengan performa terbaik di tiap keluarga produk (lihat
    // pricelist resmi: tiap % kegelapan punya TSER/IRR/UVR sendiri — di sini ditampilkan
    // sebagai "hingga X%" per keluarga, bukan dirata-rata).
    $products = [
        [
            'name' => 'Flex One Way', 'accent' => '#29b6e8',
            'priceNum' => 230000, 'normal' => '500.000',
            'darkness' => '60% / 80%',
            'warranty' => '3 Tahun',
            'ser' => 84, 'uv' => 99.9, 'ir' => 99,
        ],
        [
            'name' => 'Black Master', 'accent' => '#171f32',
            'priceNum' => 450000, 'normal' => '750.000',
            'darkness' => '40% / 60% / 80%',
            'warranty' => '5 Tahun',
            'ser' => 87, 'uv' => 99.9, 'ir' => 99,
        ],
        [
            'name' => 'Notch UV400', 'accent' => '#0066ff',
            'priceNum' => 550000, 'normal' => '900.000',
            'darkness' => '20% / 30% / 40% / 60% / 80%',
            'warranty' => '5 Tahun',
            'ser' => 89, 'uv' => 99.9, 'ir' => 99,
        ],
        [
            'name' => '4K Alpha Pro', 'accent' => '#3a3f47',
            'priceNum' => 800000, 'normal' => '1.300.000',
            'darkness' => '30% / 40% / 60% / 80% / 90%',
            'warranty' => '7 Tahun',
            'ser' => 95, 'uv' => 99.9, 'ir' => 99,
        ],
    ];
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Poppins:ital@1&display=swap" rel="stylesheet">
<style>
    :root{ --kfr-manrope: 'Manrope', var(--ace-font); --kfr-poppins: 'Poppins', var(--ace-font); }

    /* ---------- Heading override khusus halaman ini: subtitle italic Poppins, heading Manrope Bold solid navy ---------- */
    .kfr-heading h2{ font-family: var(--kfr-manrope); font-weight: 700; margin-bottom: 2rem; }
    .kfr-heading p.ace-muted{ font-family: var(--kfr-poppins); font-style: italic; }

    /* ---------- Hero ---------- */
    /* Foto latar penuh (edge-to-edge, bukan kotak terpisah) — tinggi section dikunci via
       min-height proporsional (bukan mengikuti tinggi konten) supaya cover-crop tetap
       konsisten & tidak "zoom" berlebihan walau konten sedikit bertambah. */
    .kfr-hero{
        position: relative;
        background:
            url('{{ asset('assets/frontend/landing/kaca-film-rumah/hero.webp') }}') center right / cover no-repeat,
            linear-gradient(180deg, #fff, #f4f8ff);
        overflow: hidden;
    }
    .kfr-hero .container{
        min-height: clamp(500px, 40vw, 760px);
        display: flex; flex-direction: column; justify-content: center;
        padding-block: 32px;
    }
    /* Container Bootstrap mentok di 1320px — di layar desktop lebar (1440px+) itu menyisakan
       area kosong besar di kiri karena background cover ter-anchor ke kanan (foto rumah).
       Lebarkan container & batasi section-nya sendiri supaya proporsinya tetap "penuh" seperti di laptop,
       bukan melebar jadi kosong di monitor besar. */
    @media (min-width: 1400px){
        .kfr-hero{ max-width: 1920px; margin-inline: auto; }
        .kfr-hero .container{ max-width: 1400px; }
    }
    .kfr-hero-logo{ display: inline-block; margin-bottom: 44px; } /* jarak logo -> judul */
    .kfr-hero-logo img{ height: 26px; width: auto; }
    .kfr-hero-copy{ max-width: 560px; }
    .kfr-hero-title{
        font-family: var(--kfr-manrope);
        font-weight: 800;
        color: var(--ace-ink);
        font-size: clamp(1.9rem, 3.4vw, 2.5rem);
        line-height: 1.4;
        letter-spacing: .2px;
        margin-bottom: 3.25rem; /* jarak judul -> deskripsi */
    }
    .kfr-hero-title .kfr-gradient-line{
        background: var(--ace-gradient);
        -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .kfr-hero-desc{
        font-family: var(--kfr-poppins);
        color: #3d4a5c;
        max-width: 420px;
        line-height: 1.7;
        margin-bottom: 2.25rem;
    }
    .kfr-btn-hero{
        display: inline-block;
        background: var(--ace-gradient);
        color: #fff;
        font-weight: 600;
        font-size: 1rem;
        padding: 13px 76px;
        /* Kiri-atas & kanan-bawah membulat penuh; kanan-atas & kiri-bawah sudut tajam */
        border-radius: 30px 5px 30px 5px;
        text-decoration: none;
        box-shadow: 0 12px 28px rgba(2,33,78,.28);
    }
    .kfr-btn-hero:hover{ color: #fff; opacity: .92; }

    .kfr-stats-bar{
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 16px 40px rgba(2,33,78,.10);
        padding: 18px 28px;
        margin-top: 40px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        align-items: center;
        gap: 12px 28px;
    }
    .kfr-stat{ display: flex; align-items: center; gap: 10px; color: #23303f; white-space: nowrap; }
    .kfr-stat b{ color: var(--ace-navy); }
    .kfr-dot{ width: 14px; height: 14px; border-radius: 50%; flex: none; }

    /* Foto sebagai <img> terpisah, hanya untuk breakpoint di mana background-cover tidak
       lagi cocok (potret sempit menimpa teks) — teks jadi rata tengah, foto pindah ke bawah. */
    .kfr-hero-mobile-img{ display: none; }

    @media (max-width: 991.98px){
        .kfr-hero{ background-image: none; }
        .kfr-hero .container{ min-height: 0; padding-block: 40px 32px; }
        .kfr-hero-logo{ margin-bottom: 24px; }
        .kfr-hero-copy{ max-width: 100%; text-align: center; }
        .kfr-hero-desc{ margin-inline: auto; }
        .kfr-hero-mobile-img{
            display: block; width: 100%; max-width: 480px; margin: 8px auto 0;
            border-radius: 16px;
        }
        .kfr-stats-bar{ margin-top: 32px; padding: 16px 20px; }
    }
    @media (max-width: 575.98px){
        .kfr-hero-title{ font-size: 1.65rem; }
        .kfr-btn-hero{ padding: 12px 52px; }
        .kfr-stats-bar{ display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; justify-items: start; }
        .kfr-stat{ white-space: normal; font-size: .875rem; }
        .kfr-dot{ width: 12px; height: 12px; }
    }
    @media (max-width: 359.98px){ .kfr-stats-bar{ grid-template-columns: 1fr; } }

    /* ---------- Kartu "Mengapa Kaca Film Acegard" ---------- */
    .kfr-reason-card{ display: flex; flex-direction: column; min-height: 100%; }
    .kfr-reason-card .kfr-reason-title{
        font-family: var(--kfr-manrope); font-weight: 600; font-size: 1.2rem; margin-bottom: .6rem;
    }
    .kfr-reason-card .kfr-reason-desc{ color: var(--ace-ink); font-size: .9rem; line-height: 1.45; flex: 1; }
    .kfr-reason-card .kfr-reason-foot{
        display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem;
    }
    .kfr-reason-card .kfr-reason-foot iconify-icon:first-child{ font-size: 1.15rem; color: var(--ace-navy); }
    .kfr-reason-card .kfr-reason-foot iconify-icon:last-child{ font-size: 1.6rem; color: var(--ace-ink); }

    /* ---------- Testimoni ---------- */
    .kfr-testi-card{ background: var(--ace-bg-soft); border-radius: 12px; padding: 1.5rem; box-shadow: 0 3px 3px rgba(0,0,0,.08); }
    .kfr-testi-head{ display: flex; align-items: center; gap: .75rem; margin-bottom: .9rem; }
    .kfr-testi-avatar{ width: 52px; height: 52px; border-radius: 50%; object-fit: cover; flex: none; }
    .kfr-testi-avatar-fallback{
        width: 52px; height: 52px; border-radius: 50%; background: var(--ace-navy);
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; flex: none;
    }
    .kfr-testi-name{ font-weight: 800; }
    .kfr-testi-quote{ color: var(--ace-ink); font-size: .95rem; margin-bottom: .9rem; min-height: 4.5em; }
    .kfr-testi-stars{ text-align: right; }
    .kfr-testi-stars img{ height: 20px; }

    /* ---------- Klien Kami: logo full-color, tanpa grayscale ---------- */
    .kfr-client-logo{ max-height: 52px; width: auto; object-fit: contain; }

    /* ---------- Spesifikasi Produk & Harga ---------- */
    .kfr-price-card{
        background: #fff; border: 1px solid var(--ace-border); border-radius: 12px;
        padding: 1.75rem 1.5rem; display: flex; flex-direction: column; height: 100%;
        text-align: center;
    }
    .kfr-price-name{ font-size: 1.05rem; margin-bottom: .5rem; }
    .kfr-price-amount{ font-size: 1.9rem; font-weight: 800; line-height: 1.1; }
    .kfr-price-amount .kfr-per{ font-size: .95rem; font-weight: 400; color: var(--ace-muted); }
    .kfr-price-normal{ font-size: .8rem; color: var(--ace-muted); margin-bottom: 1rem; }
    .kfr-price-normal s{ text-decoration-color: #dc3545; }
    .kfr-price-buy{
        display: block; border-radius: 999px; padding: .55rem 1rem; font-weight: 600; font-size: .9rem;
        text-decoration: none; border: 1.5px solid; margin-bottom: 1.1rem; background: #fff;
    }
    .kfr-price-buy:hover{ color: #fff !important; }
    .kfr-price-darkness{ font-size: .8rem; color: var(--ace-ink); margin-bottom: 1rem; }
    .kfr-price-divider{ display: flex; align-items: center; gap: 10px; color: var(--ace-muted); font-size: .78rem; margin-bottom: 1rem; }
    .kfr-price-divider::before, .kfr-price-divider::after{ content: ""; flex: 1; height: 1px; background: var(--ace-border); }
    .kfr-price-specs{ display: flex; text-align: left; }
    .kfr-price-specs .kfr-spec-labels{ flex: 1; display: flex; flex-direction: column; gap: .65rem; font-size: .85rem; color: var(--ace-ink); }
    .kfr-price-specs .kfr-spec-values{ border-left: 1px solid var(--ace-border); padding-left: .9rem; display: flex; flex-direction: column; gap: .65rem; font-size: .85rem; font-weight: 700; }

    /* ---------- CTA banner ---------- */
    .kfr-cta{
        background: #fff; border-radius: 20px; box-shadow: 0 20px 50px rgba(2,33,78,.10);
        padding: 2.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;
    }
    .kfr-cta-badge{
        display: inline-flex; align-items: center; gap: .6rem; border: 1px solid var(--ace-border);
        border-radius: 999px; padding: .4rem .9rem .4rem .4rem; margin-bottom: 1.1rem;
    }
    .kfr-cta-badge-icon{
        width: 34px; height: 34px; border-radius: 50%; background: var(--ace-gradient);
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; flex: none;
    }
    .kfr-cta-badge-icon iconify-icon{ color: #fff; }
    .kfr-cta-badge span{ font-weight: 700; font-size: .78rem; letter-spacing: .04em; color: var(--ace-navy); }
    .kfr-cta-left h2{ font-family: var(--kfr-manrope); font-weight: 700; font-size: 1.6rem; color: var(--ace-ink); }
    .kfr-cta-left .kfr-cta-blue{ color: var(--ace-blue); text-decoration: none; }
    .kfr-cta-right h3{ font-family: var(--kfr-manrope); font-weight: 700; font-size: 1.5rem; color: var(--ace-ink); margin-bottom: 1.25rem; }
    .kfr-cta-wa{
        display: flex; align-items: center; gap: 0; border-radius: 999px; overflow: hidden;
        text-decoration: none; box-shadow: 0 10px 24px rgba(2,33,78,.18); margin-bottom: 1.1rem;
    }
    .kfr-cta-wa-icon{ background: #fff; color: var(--ace-blue); border-radius: 100%; width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex: none; }
    .kfr-cta-wa-icon iconify-icon{ color: var(--ace-blue); }
    .kfr-cta-wa-label{ background: var(--ace-gradient); color: #fff; font-weight: 600; padding: .8rem 1.75rem; flex: 1; text-align: center; }
    .kfr-cta-perks{ display: flex; flex-wrap: wrap; align-items: center; gap: 0; border: 1px solid var(--ace-border); border-radius: 999px; padding: .5rem 1rem; }
    .kfr-cta-perk{ display: flex; align-items: center; gap: .5rem; padding: 0 .85rem; font-size: .8rem; color: var(--ace-navy); font-weight: 600; white-space: nowrap; }
    .kfr-cta-perk:not(:last-child){ border-right: 1px solid var(--ace-border); }
    .kfr-cta-perk iconify-icon{ font-size: 1.1rem; }
    @media (max-width: 767.98px){
        .kfr-cta{ grid-template-columns: 1fr; text-align: center; padding: 1.75rem; }
        .kfr-cta-badge{ margin-inline: auto; }
        .kfr-cta-left h2{ font-size: 1.35rem; }
        .kfr-cta-right{ margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--ace-border); }
        .kfr-cta-right h3{ font-size: 1rem; font-weight: 600; color: var(--ace-muted); }
        .kfr-cta-wa{ flex-direction: column; border-radius: 16px; }
        .kfr-cta-wa-icon{ width: 100%; height: 48px; }
        .kfr-cta-perks{ flex-direction: column; border-radius: 16px; align-items: stretch; }
        .kfr-cta-perk{ justify-content: center; border-right: none !important; border-bottom: 1px solid var(--ace-border); padding: .5rem 0; }
        .kfr-cta-perk:last-child{ border-bottom: none; }
    }

    /* ---------- FAQ: pengganti caret bootstrap dengan +/x bulat sesuai Figma ---------- */
    #kfrFaq .accordion-item{
        border: none; border-bottom: 1px solid var(--ace-border); background: transparent;
        transition: background-color .25s ease;
    }
    /* Background soft mengikuti item yang sedang terbuka, bukan item pertama secara statis */
    #kfrFaq .accordion-item:has(.accordion-button:not(.collapsed)){
        background: var(--ace-bg-soft); border-radius: 12px; border-bottom: none; margin-bottom: .75rem; padding: .25rem;
    }
    #kfrFaq .accordion-button{
        font-family: var(--kfr-manrope); font-weight: 600; color: var(--ace-ink);
        background: transparent; box-shadow: none; padding: 1.1rem .25rem;
    }
    #kfrFaq .accordion-button::after{ display: none; }
    #kfrFaq .accordion-button::before{
        content: "+"; flex: none; width: 30px; height: 30px; border-radius: 50%; background: var(--ace-bg-soft);
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: var(--ace-navy);
        margin-left: 1rem; transition: transform .25s ease, background-color .25s ease;
    }
    /* Satu glyph "+" yang tetap, diputar 45deg jadi terlihat "x" saat terbuka —
       supaya transisinya benar-benar animate (ganti isi content: tidak bisa di-transition). */
    #kfrFaq .accordion-button:not(.collapsed)::before{ transform: rotate(45deg); background: #fff; }
    #kfrFaq .accordion-button:focus{ box-shadow: none; }
    #kfrFaq .accordion-collapse{ transition: height .3s ease; }
    @media (prefers-reduced-motion: reduce){
        #kfrFaq .accordion-button::before{ transition: none; }
        #kfrFaq .accordion-collapse{ transition: none; }
    }

    /* ---------- Footer khusus landing page ---------- */
    .kfr-footer{ background: #fff; border-top: 1px solid var(--ace-border); padding-block: 3rem 1.5rem; }
    .kfr-footer-logo{ display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
    .kfr-footer-logo img{ height: 30px; }
    .kfr-footer-social{ display: flex; gap: .6rem; margin-top: 1.1rem; }
    .kfr-footer-social a{
        width: 34px; height: 34px; border: 1px solid var(--ace-border); border-radius: 8px;
        display: flex; align-items: center; justify-content: center; color: var(--ace-ink); font-size: 1rem;
    }
    .kfr-footer h4{ font-family: var(--kfr-manrope); font-weight: 700; font-size: 1.3rem; color: var(--ace-ink); }

    /* ---------- Animasi (scroll reveal + micro-interactions) ---------- */
    @media (prefers-reduced-motion: no-preference) {
        .kfr-reveal{
            opacity: 0; transform: translateY(24px);
            transition: opacity .6s ease, transform .6s ease; transition-delay: var(--kfr-delay, 0ms);
        }
        .kfr-reveal.kfr-visible{ opacity: 1; transform: none; }

        .kfr-hero-title, .kfr-hero-desc, .kfr-btn-hero, .kfr-stats-bar{ animation: kfrHeroIn .7s ease both; }
        .kfr-hero-desc{ animation-delay: .12s; }
        .kfr-btn-hero{ animation-delay: .24s; }
        .kfr-stats-bar{ animation-delay: .36s; }
        @keyframes kfrHeroIn { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }

        .ace-card-soft, .kfr-testi-card, .kfr-price-card{ transition: transform .25s ease, box-shadow .25s ease; }
        .ace-card-soft:hover, .kfr-testi-card:hover, .kfr-price-card:hover{ transform: translateY(-4px); box-shadow: 0 14px 34px rgba(2,33,78,.12); }

        .btn-ace-gradient, .kfr-btn-hero, .kfr-price-buy{ transition: transform .2s ease, opacity .2s ease; }
        .btn-ace-gradient:hover, .kfr-btn-hero:hover, .kfr-price-buy:hover{ transform: translateY(-2px); }

        .kfr-client-logo{ transition: transform .25s ease; }
        .kfr-client-logo:hover{ transform: scale(1.06); }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ---------- Angka hitung naik, khusus stats bar hero — replay tiap kali masuk viewport ----------
    function formatCounter(el, value) {
        var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
        var suffix = el.getAttribute('data-suffix') || '';
        if (decimals > 0) {
            return value.toFixed(decimals).replace('.', ',') + suffix;
        }
        return Math.round(value).toString() + suffix;
    }

    function animateCounter(el) {
        var target = parseFloat(el.getAttribute('data-target'));
        if (isNaN(target)) return;
        if (reduceMotion) { el.textContent = formatCounter(el, target); return; }

        // token per-elemen: membatalkan animasi lama kalau elemen re-trigger sebelum selesai
        var myToken = (el._kfrToken || 0) + 1;
        el._kfrToken = myToken;

        var duration = 1400;
        var start = null;
        function step(ts) {
            if (el._kfrToken !== myToken) return;
            if (start === null) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            el.textContent = formatCounter(el, target * eased);
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = formatCounter(el, target);
        }
        requestAnimationFrame(step);
    }

    var counters = document.querySelectorAll('.kfr-stats-bar .kfr-counter');
    if (!('IntersectionObserver' in window)) {
        counters.forEach(function (el) { animateCounter(el); });
    } else {
        // tanpa unobserve: animasi diputar ulang tiap kali stats bar masuk viewport,
        // baik discroll ke bawah maupun kembali ke atas
        var counterObs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) animateCounter(entry.target);
            });
        }, { threshold: 0.4 });
        counters.forEach(function (el) { counterObs.observe(el); });
    }

    // ---------- Scroll reveal (fade/slide-in per section & stagger antar kartu) ----------
    if (reduceMotion) return;

    var targets = document.querySelectorAll('.kfr-reveal');
    if (!('IntersectionObserver' in window)) {
        targets.forEach(function (el) { el.classList.add('kfr-visible'); });
        return;
    }

    document.querySelectorAll('.kfr-stagger').forEach(function (group) {
        var i = 0;
        group.querySelectorAll('.kfr-reveal').forEach(function (el) {
            el.style.setProperty('--kfr-delay', (i++ * 90) + 'ms');
        });
    });

    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('kfr-visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    targets.forEach(function (el) { obs.observe(el); });
})();
</script>
@endpush

{{-- Hero --}}
<section class="kfr-hero">
    <div class="container">
        <a href="{{ route('frontend.home') }}" class="kfr-hero-logo">
            <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard" height="34">
        </a>

        <div class="kfr-hero-copy">
            <h1 class="kfr-hero-title">
                Kaca Film Rumah<br>
                <span class="kfr-gradient-line">Tolak Panas 99%,<br>Anti UV &amp; Hemat Energi</span>
            </h1>
            <p class="kfr-hero-desc">
                Lindungi rumah dari panas, sinar UV, dan silau
                tanpa mengurangi cahaya alami. Cocok untuk
                rumah, kantor, apartemen, dan ruko.
            </p>
            <a href="{{ $waLink('Halo Acegard, saya ingin konsultasi gratis kaca film rumah') }}"
                class="kfr-btn-hero whatsapp-btn" target="_blank" rel="noopener">
                Konsultasi Gratis
            </a>
        </div>

        {{-- Di layar sempit background-cover diganti <img> biasa di bawah teks (lihat CSS .kfr-hero-mobile-img) --}}
        <img src="{{ asset('assets/frontend/landing/kaca-film-rumah/hero.webp') }}"
            alt="Rumah modern dengan kaca film Acegard"
            class="kfr-hero-mobile-img" fetchpriority="high">

        <div class="kfr-stats-bar">
            @foreach ($stats as $stat)
                <div class="kfr-stat">
                    <span class="kfr-dot" style="background: {{ $stat['dot'] }};"></span>
                    <span>
                        <b><span class="kfr-counter" data-target="{{ $stat['target'] }}" data-decimals="{{ $stat['decimals'] }}" data-suffix="{{ $stat['suffix'] }}">0{{ $stat['suffix'] }}</span></b>
                        {{ $stat['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Mengapa Acegard --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 1100px;">
        <div class="kfr-heading">
            <x-frontend.section-heading title="Mengapa Kaca Film Acegard?"
                subtitle="Mengurangi panas, memblokir sinar UV, dan menjaga kenyamanan setiap hari" />
        </div>

        <div class="row g-4 align-items-center">
            <div class="col-lg-5 kfr-reveal">
                <img src="{{ asset('assets/frontend/landing/kaca-film-rumah/mengapa.webp') }}"
                    alt="Gedung dengan kaca film Acegard"
                    class="w-100 rounded-4" style="aspect-ratio: 4/5; object-fit: cover;" loading="lazy">
            </div>
            <div class="col-lg-7">
                <div class="row g-4 kfr-stagger">
                    @foreach ($reasons as $reason)
                        <div class="col-md-6 kfr-reveal">
                            <div class="ace-card-soft p-4 kfr-reason-card">
                                <div class="kfr-reason-title ace-gradient-text">{{ $reason['title'] }}</div>
                                <p class="kfr-reason-desc">{{ $reason['desc'] }}</p>
                                <div class="kfr-reason-foot">
                                    <iconify-icon icon="mdi:arrow-top-right"></iconify-icon>
                                    <iconify-icon icon="{{ $reason['icon'] }}"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Keunggulan --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 1100px;">
        <div class="kfr-heading">
            <x-frontend.section-heading title="Keunggulan yang Anda Dapatkan Bersama Acegard"
                subtitle="Lebih sejuk, lebih nyaman, lebih terlindungi" />
        </div>

        <div class="row g-4 kfr-stagger">
            @foreach ($features as $feature)
                <div class="col-6 col-lg-3 kfr-reveal">
                    <x-frontend.feature-card :icon="$feature['icon']" :title="$feature['title']" :desc="$feature['desc']"
                        title-class="ace-gradient-text" :arrow="true" />
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Testimoni --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 1080px;">
        <div class="kfr-heading">
            <x-frontend.section-heading title="Apa kata mereka?"
                subtitle="Pengalaman Jujur Bersama Kaca Film Terbaik." />
        </div>

        <div class="row g-4 kfr-stagger">
            @foreach ($testimonials as $testimonial)
                <div class="col-md-4 kfr-reveal">
                    <div class="kfr-testi-card h-100 d-flex flex-column">
                        <div class="kfr-testi-head">
                            @if ($testimonial['avatar'])
                                <img src="{{ asset('assets/frontend/landing/kaca-film-rumah/' . $testimonial['avatar']) }}"
                                    alt="{{ $testimonial['name'] }}" class="kfr-testi-avatar">
                            @else
                                <span class="kfr-testi-avatar-fallback"><iconify-icon icon="mdi:account"></iconify-icon></span>
                            @endif
                            <span class="kfr-testi-name ace-gradient-text">{{ $testimonial['name'] }}</span>
                        </div>
                        <p class="kfr-testi-quote">{{ $testimonial['quote'] }}</p>
                        <div class="kfr-testi-stars mt-auto">
                            <img src="{{ asset('assets/frontend/landing/kaca-film-rumah/stars.svg') }}" alt="5 dari 5 bintang">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mt-4 kfr-stagger">
            @foreach (range(1, 5) as $i)
                <div class="col-6 col-md kfr-reveal">
                    <img src="{{ asset('assets/frontend/landing/kaca-film-rumah/gallery-' . $i . '.webp') }}"
                        alt="Proyek pemasangan kaca film Acegard {{ $i }}"
                        class="w-100 rounded-3" style="aspect-ratio: 4/5; object-fit: cover;" loading="lazy">
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Klien Kami --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 1000px;">
        <div class="kfr-heading">
            <x-frontend.section-heading title="Klien Kami"
                subtitle="Mitra Terpercaya untuk Estetika dan Kenyamanan Arsitektur." />
        </div>

        <div class="d-flex flex-wrap justify-content-center align-items-center gap-4 kfr-reveal">
            @foreach ($clients as $slug => $name)
                <img src="{{ asset('assets/frontend/landing/kaca-film-rumah/client-' . $slug . '.webp') }}"
                    alt="{{ $name }}" class="kfr-client-logo" loading="lazy">
            @endforeach
        </div>
    </div>
</section>

{{-- Spesifikasi Produk & Harga --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 1200px;">
        <div class="kfr-heading">
            <x-frontend.section-heading title="Spesifikasi Produk & Harga"
                subtitle="Investasi Terbaik untuk Kenyamanan Jangka Panjang." />
        </div>

        <div class="row g-4 align-items-stretch kfr-stagger">
            @foreach ($products as $product)
                <div class="col-md-6 col-xl-3 kfr-reveal">
                    <div class="kfr-price-card">
                        <div class="kfr-price-name" style="color: {{ $product['accent'] }};">{{ $product['name'] }}</div>

                        <div class="kfr-price-amount" style="color: {{ $product['accent'] }};">
                            Rp.{{ number_format($product['priceNum'], 0, ',', '.') }} <span class="kfr-per">/ Meter</span>
                        </div>
                        <div class="kfr-price-normal">Harga Normal <s>Rp. {{ $product['normal'] }}</s>/Meter</div>

                        <a href="{{ $waLink('Halo Acegard, saya tertarik kaca film rumah tipe ' . $product['name']) }}"
                            target="_blank" rel="noopener" class="whatsapp-btn kfr-price-buy"
                            style="border-color: {{ $product['accent'] }}; color: {{ $product['accent'] }};"
                            onmouseover="this.style.background='{{ $product['accent'] }}'"
                            onmouseout="this.style.background='#fff'">
                            Buy Now
                        </a>

                        <div class="kfr-price-darkness">Persentase Kegelapan : {{ $product['darkness'] }}</div>
                        <div class="kfr-price-darkness">Garansi : {{ $product['warranty'] }}</div>

                        <div class="kfr-price-divider">Features</div>

                        <div class="kfr-price-specs">
                            <div class="kfr-spec-labels">
                                <span>Solar Energy Rejection :</span>
                                <span>Ultraviolet Rejection :</span>
                                <span>Infrared Rejection :</span>
                            </div>
                            <div class="kfr-spec-values" style="color: {{ $product['accent'] }};">
                                <span>{{ $product['ser'] }}%</span>
                                <span>{{ $product['uv'] }}%</span>
                                <span>{{ $product['ir'] }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA Promo --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 1100px;">
        <div class="kfr-cta kfr-reveal">
            <div class="kfr-cta-left">
                <span class="kfr-cta-badge">
                    <span class="kfr-cta-badge-icon"><iconify-icon icon="mdi:headphones"></iconify-icon></span>
                    <span>LAYANAN PROFESIONAL</span>
                </span>
                <h2>
                    Siap Membuat <span class="kfr-cta-blue">Rumah Lebih Sejuk?</span><br>
                    Dapatkan Penawaran Kaca Film Rumah Hari Ini
                </h2>
                <p class="ace-muted mt-3 mb-0">
                    Dapatkan <span class="kfr-cta-blue">konsultasi dengan tim ahli, dan penawaran</span>
                    terbaik untuk pemasangan kaca film Acegard.
                </p>
            </div>

            <div class="kfr-cta-right">
                <h3>Kami siap membantu anda kapan saja dengan pelayanan terbaik</h3>

                <a href="{{ $waLink('Halo Acegard, saya ingin penawaran kaca film rumah hari ini') }}"
                    target="_blank" rel="noopener" class="kfr-cta-wa whatsapp-btn">
                    <span class="kfr-cta-wa-icon"><iconify-icon icon="mdi:whatsapp"></iconify-icon></span>
                    <span class="kfr-cta-wa-label">Konsultasi Gratis Via Whatsapp</span>
                </a>

                <div class="kfr-cta-perks">
                    <span class="kfr-cta-perk"><iconify-icon icon="mdi:lightning-bolt-outline"></iconify-icon> Fast Respon</span>
                    <span class="kfr-cta-perk"><iconify-icon icon="mdi:shield-check-outline"></iconify-icon> Aman &amp; Terpercaya</span>
                    <span class="kfr-cta-perk"><iconify-icon icon="mdi:clock-time-four-outline"></iconify-icon> Siap 24/7</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="ace-section-sm">
    <div class="container" style="max-width: 820px;">
        <div class="kfr-heading text-center mb-4">
            <div class="ace-muted small fw-semibold text-uppercase mb-1">FAQ</div>
            <h2 class="fw-normal" style="font-family: var(--kfr-manrope); font-weight: 700;">Frequently<br>Asked Question</h2>
        </div>

        <div class="accordion kfr-reveal" id="kfrFaq">
            <x-frontend.faq-item id="kfr-faq-1" parent="kfrFaq" question="Di mana lokasi workshop Anda berada?" :open="true">
                Workshop kami berlokasi di Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh,
                Kota Tangerang, Banten 15146, mudah dijangkau dan strategis dari area pusat kota.
                Lokasi ini juga tersedia di Google Maps untuk memudahkan Anda menemukan rute terbaik.
            </x-frontend.faq-item>

            <x-frontend.faq-item id="kfr-faq-2" parent="kfrFaq" question="Apa manfaat utama kaca film untuk rumah dan bangunan?">
                Kaca film mengurangi panas dan silau, memblokir hingga 99% sinar UV, membantu menghemat listrik
                karena AC bekerja lebih ringan, menambah privasi, serta melindungi perabot dan interior dari pudar.
            </x-frontend.faq-item>

            <x-frontend.faq-item id="kfr-faq-3" parent="kfrFaq" question="Apakah kaca film ramah lingkungan?">
                Ya. Dengan mengurangi beban kerja AC, kaca film membantu menurunkan konsumsi energi rumah Anda —
                lebih hemat biaya listrik sekaligus mengurangi jejak karbon.
            </x-frontend.faq-item>

            <x-frontend.faq-item id="kfr-faq-4" parent="kfrFaq" question="Apakah tersedia garansi resmi?">
                Tersedia garansi resmi 3 hingga 7 tahun sesuai tipe kaca film. Garansi meliputi kaca film yang
                tidak menempel pada kaca dan warna yang luntur, dan terdaftar resmi di situs Acegard.id.
            </x-frontend.faq-item>
        </div>
    </div>
</section>

{{-- Footer khusus landing page (2 kolom, tanpa navigasi situs, sesuai Figma) --}}
<footer class="kfr-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="kfr-footer-logo">
                    <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard">
                </div>
                <p class="ace-muted mb-0" style="max-width: 380px;">
                    Acegard sangat mementingkan kepuasan konsumen dan klien untuk menjaga integritas brand yang unggul.
                </p>
                <div class="kfr-footer-social">
                    <a href="#" aria-label="Facebook"><iconify-icon icon="mdi:facebook"></iconify-icon></a>
                    <a href="#" aria-label="Instagram"><iconify-icon icon="mdi:instagram"></iconify-icon></a>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Contact Us</h4>
                <p class="ace-muted mb-2">Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog,<br>Kec. Cipondoh, Kota Tangerang, Banten 15146</p>
                <p class="ace-muted mb-2"><a href="mailto:acegardindonesia@gmail.com" class="text-decoration-none">acegardindonesia@gmail.com</a></p>
                <p class="ace-muted mb-0">+628212003331</p>
            </div>
        </div>

        <div class="text-center ace-muted small mt-5">
            Copyright, Acegard All Rights Reserved
        </div>
    </div>
</footer>

@endsection
