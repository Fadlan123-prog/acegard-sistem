<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acegard PPF – Teknologi Paint Protection Film terbaik. Goresan hilang hanya dalam 5 detik. Garansi resmi 8 tahun. Bahan Premium TPU Lubrizol USA.">
    <meta property="og:title" content="Acegard PPF – Goresan Hilang Hanya Dalam 5 Detik">
    <meta property="og:description" content="Paint Protection Film premium dengan teknologi Instant Self-Healing. Garansi resmi 8 tahun.">
    <meta name="robots" content="noindex">
    <title>Acegard PPF – Goresan Hilang Hanya Dalam 5 Detik</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fonts/remixicon.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <!-- Event snippet for Konversi PPF conversion page
    In your html page, add the snippet and call gtag_report_conversion when someone clicks on the chosen link or button. -->


    <style>
    :root {
        --blue       : #1E6FE0;
        --blue-dark  : #1558C0;
        --blue-light : #EEF4FF;
        --dark       : #0B1330;
        --red        : #E63946;
        --gray       : #64748B;
        --light-bg   : #F8FAFF;
        --green      : #16A34A;
        --border     : #E2E8F0;
        --gold       : #F59E0B;
    }
    :root {
        --bs-font-sans-serif: 'Poppins', sans-serif;
        --bs-body-font-family: 'Poppins', sans-serif;
    }
    *, *::before, *::after { font-family: 'Poppins', sans-serif !important; box-sizing: border-box; }
    body { background: #fff; overflow-x: hidden; margin: 0; font-family: 'Poppins', sans-serif !important; }

    /* ── NAVBAR ── */
    .lp-nav {
        position: absolute;
        top: 0; left: 0; right: 0;
        z-index: 100;
        padding: 20px 48px;
        background: transparent;
    }
    .lp-nav-logo { height: 22px; object-fit: contain; display: block; }

    /* ── HERO ── */
    .hero-section {
        background: url("{{ asset('assets/frontend/ppf/1.webp') }}") center center / cover no-repeat;
        overflow: hidden;
        position: relative;
    }
    /* Custom flex layout — no Bootstrap col constraints */
    .hero-wrap {
        display: flex;
        align-items: center;
        min-height: 75vh;
        max-width: 1440px;
        margin: 0 auto;
        padding: 100px 48px 60px;
        gap: 0;
    }
    .hero-car-side {
        flex: 0 0 54%;
        position: relative;
        padding-right: 12px;
    }
    .hero-text-side {
        flex: 1;
        min-width: 0;
        padding-left: 40px;
    }

    /* Car wrapper + callout annotations */
    .car-callout-wrapper {
        position: relative;
        width: 100%;
        display: block;
    }
    .hero-car-img {
        width: 100%;
        display: block;
        position: relative;
        z-index: 1;
        filter: drop-shadow(0 20px 50px rgba(30,70,200,.14));
        animation: floatCar 5s ease-in-out infinite;
    }
    @keyframes floatCar { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

    /* SVG connecting lines overlay */
    .callout-svg {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        z-index: 2;
        pointer-events: none;
        overflow: visible;
    }

    /* Callout pill badges */
    .callout-badge {
        position: absolute;
        z-index: 3;
        background: transparent;
        border: 1.8px solid var(--blue);
        border-radius: 14px;
        padding: 10px 22px;
        font-size: 13px;
        font-weight: 600;
        color: var(--blue);
        white-space: nowrap;
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 20px rgba(30,111,224,.12);
        line-height: 1;
    }

    /* Hero text */
    .hero-h1 {
        font-size: clamp(1.8rem, 2.6vw, 3rem);
        font-weight: 600;
        line-height: 1.18;
        color: var(--dark);
        margin-bottom: 20px;
        letter-spacing: -0.5px;
        text-align: right;
        white-space: nowrap;
    }
    .hero-h1 .blue-line {
        display: block;
        background: #0166FF;
        background: linear-gradient(90deg, rgba(1,102,255,1) 0%, rgba(2,33,79,1) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-desc {
        color: var(--gray);
        font-size: 15px;
        line-height: 1.75;
        margin-bottom: 30px;
        max-width: 460px;
        text-align: right;
        margin-left: auto;
    }
    .btn-konsul {
        background: linear-gradient(90deg, rgba(1,102,255,1) 0%, rgba(2,33,79,1) 100%);
        color: #fff; border: none;
        border-radius: 10px; padding: 13px 26px;
        font-size: 15px; font-weight: 400;
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: transform .15s, box-shadow .15s, opacity .15s;
        white-space: nowrap;
    }
    .btn-konsul:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(1,102,255,.4); opacity: .9; }
    .btn-produk {
        background: transparent;
        color: #0166FF;
        border: 1.5px solid #0166FF;
        border-radius: 10px; padding: 12px 26px; font-size: 15px; font-weight: 400;
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: all .15s; white-space: nowrap;
    }
    .btn-produk:hover { background: linear-gradient(90deg, rgba(1,102,255,1) 0%, rgba(2,33,79,1) 100%); color: #fff; border-color: transparent; }
    /* blue CTA — used in promo section (on dark bg) */
    .btn-primary-cta {
        background: var(--blue); color: #fff; border: none;
        border-radius: 10px; padding: 13px 26px;
        font-size: 15px; font-weight: 700;
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: transform .15s, box-shadow .15s; white-space: nowrap;
    }
    .btn-primary-cta:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(30,111,224,.4); }

    /* ── TRUST BAR ── */
    .trust-bar {
        background: #F6F7FB;
        padding: 32px 0;
        margin-top: -80px;
        position: relative;
        z-index: 10;
    }
    .trust-bar .container {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
    }
    .trust-row {
        display: flex;
        align-items: center;
        justify-content: space-around;
        flex-wrap: nowrap;
    }
    .trust-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 20px;
        flex-shrink: 0;
    }
    .trust-circle {
        width: 44px; height: 44px; border-radius: 50%;
        background: #02214F;
        background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 50%, rgba(2,33,79,1) 100%);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 19px; flex-shrink: 0;
    }
    .trust-circle.count-badge { font-size: 11px; font-weight: 800; letter-spacing: -.5px; }
    .trust-item .t-label { font-size: 14px; font-weight: 500; color: var(--dark); margin: 0; white-space: nowrap; }
    .trust-sep { width: 1px; height: 32px; background: var(--border); flex-shrink: 0; }

    /* ── SECTION TITLES ── */
    .sec-title { font-size: clamp(1.5rem, 3.5vw, 2.2rem); font-weight: 800; color: var(--dark); line-height: 1; }
    .sec-title span { color: var(--blue); }
    .sec-sub { color: var(--gray); font-size: 15px; }

    /* ── WHY SECTION ── */
    .why-section { padding: 80px 0 40px; background: url("{{ asset('assets/frontend/ppf/2.webp') }}") center center / cover no-repeat; }
    .why-section .sec-title { font-weight: 500; font-size: clamp(1.1rem, 2vw, 1.5rem); }
    .why-title-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(8px);
        border-radius: 14px;
        padding: 16px 28px;
        min-height: 64px;
    }
    .why-title-wrap .sec-title { margin: 0; }

    /* Image grid */
    .adv-grid {
        display: flex;
        gap: 10px;
        height: 480px;
    }
    .adv-main {
        flex: 0 0 54%;
        position: relative;
        border-radius: 16px;
        overflow: hidden;
    }
    .adv-stack {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .adv-stack-item {
        flex: 1;
        position: relative;
        border-radius: 16px;
        overflow: hidden;
    }
    .adv-grid-img {
        width: 100%; height: 100%;
        object-fit: cover; display: block;
        transition: transform .4s;
    }
    .adv-main:hover .adv-grid-img,
    .adv-stack-item:hover .adv-grid-img { transform: scale(1.04); }
    .adv-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.6) 0%, transparent 55%);
        z-index: 1;
    }
    .adv-grid-label {
        position: absolute; bottom: 16px; left: 16px;
        display: flex; align-items: center; gap: 10px;
        z-index: 2;
    }
    .adv-grid-icon {
        width: 36px; height: 36px; border-radius: 50%;
        border: 1.5px solid rgba(255,255,255,.65);
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 16px; flex-shrink: 0;
    }
    .adv-grid-text {
        color: #fff; font-size: 14px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .5px;
        text-shadow: 0 1px 4px rgba(0,0,0,.5); line-height: 1.2;
    }

    /* Right text features */
    .why-feature { margin-bottom: 26px; padding-bottom: 26px; border-bottom: 1px solid rgba(0,0,0,.08); }
    .why-feature:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .why-feature-tag {
        font-size: 11px; font-weight: 700; color: #0166FF;
        text-transform: uppercase; letter-spacing: 1px;
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
    }
    .why-feature-tag::after {
        content: ''; display: block;
        width: 20px; height: 2px;
        background: #0166FF; border-radius: 2px;
    }
    .why-feature-q { font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .why-feature-desc { font-size: 13.5px; color: var(--gray); line-height: 1.7; margin: 0; }
    .feat-card {
        background: #fff; border-radius: 14px; padding: 24px;
        box-shadow: 0 2px 16px rgba(0,0,0,.06); height: 100%;
        border: 1px solid var(--border);
    }
    .feat-card .fc-icon {
        width: 48px; height: 48px; border-radius: 12px;
        background: var(--blue-light); color: var(--blue);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; margin-bottom: 14px;
    }
    .feat-card h5 { font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .feat-card p  { font-size: 13px; color: var(--gray); line-height: 1.6; margin: 0; }

    /* ── BENEFIT CARDS ── */
    .benefits-section { padding: 40px 0 80px; background: url("{{ asset('assets/frontend/ppf/3.webp') }}") center center / cover no-repeat; }
    .benefits-section .sec-title { font-weight: 500; font-size: clamp(1.1rem, 2vw, 1.5rem); }
    .bc-card {
        background: #fff;
        border-radius: 20px;
        padding: 56px 0px 8px;
        text-align: center;
        position: relative;
        box-shadow: 0 4px 24px rgba(0,0,0,.07);
        height: 100%;
        transition: transform .2s, box-shadow .2s;
    }
    .bc-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px rgba(1,102,255,.14); }
    .bc-num {
        position: absolute; top: 0; left: 0;
        background: var(--dark); color: #fff;
        width: 36px; height: 36px;
        border-radius: 20px 0 12px 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; font-weight: 800;
        z-index: 1;
    }
    .bc-icon-wrap {
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, #1E90FF 0%, #0047C0 100%);
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        box-shadow: 0 8px 20px rgba(1,102,255,.3);
        z-index: 2;
    }
    .bc-title { font-size: 14px; font-weight: 700; color: var(--dark); margin-bottom: 10px; }
    .bc-desc  { font-size: 12.5px; color: var(--gray); line-height: 1.6; margin-bottom: 16px; }
    .bc-line  { width: 30px; height: 2px; background: linear-gradient(90deg, rgba(1,102,255,1) 0%, rgba(2,33,79,1) 100%); margin: 0 auto; border-radius: 2px; }

    /* ── GALLERY ── */
    .gallery-section { padding: 80px 0; background: url("{{ asset('assets/frontend/ppf/4.webp') }}") center center / cover no-repeat; }
    .gallery-section .sec-title { color: var(--dark); font-weight: 500; font-size: clamp(1.1rem, 2vw, 1.5rem); }
    .gallery-section .sec-title span { color: var(--blue); }
    .gallery-item {
        position: relative; border-radius: 16px;
        overflow: hidden; cursor: pointer;
        box-shadow: 0 4px 20px rgba(0,0,0,.1);
        background: #fff;
    }
    .gallery-item img { width: 100%; height: auto; display: block; transition: transform .5s; }
    .gallery-item:hover img { transform: scale(1.05); }
    .g-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.88) 0%, rgba(0,0,0,.3) 55%, transparent 100%);
        z-index: 1;
    }
    .g-info {
        position: absolute; bottom: 0; left: 0; right: 0;
        padding: 10px 14px 14px;
        z-index: 2;
    }
    .g-car-name { line-height: 1.1; margin-bottom: 6px; }
    .g-brand { font-size: 24px; font-weight: 900; font-style: italic; color: #fff; text-transform: uppercase; letter-spacing: .5px; }
    .g-model { font-size: 14px; font-weight: 700; font-style: italic; color: #60AAFF; text-transform: uppercase; margin-left: 4px; }
    .g-badge { display: flex; align-items: center; gap: 5px; margin-bottom: 3px; flex-wrap: wrap; }
    .g-badge-pre { font-size: 8.5px; color: rgba(255,255,255,.8); font-weight: 500; white-space: nowrap; }
    .g-badge-pill {
        background: linear-gradient(90deg, #C0392B 0%, #E74C3C 100%);
        color: #fff; font-size: 8px; font-weight: 800;
        padding: 2px 7px; border-radius: 4px; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap;
    }
    .g-base { font-size: 8.5px; color: rgba(255,255,255,.65); font-style: italic; }

    /* ── TESTIMONIALS ── */
    .testimonials-section { padding: 80px 0; background: url("{{ asset('assets/frontend/ppf/5.webp') }}") center center / cover no-repeat; }
    .testi-main-title {
        font-size: clamp(1.4rem, 2.5vw, 2rem); font-weight: 600; margin: 0;
        background: #02214F;
        background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 50%, rgba(2,33,79,1) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .testi-card-new {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 24px 28px;
        height: 100%;
    }
    .testi-quote-text { font-size: 14px; color: var(--gray); line-height: 1.7; margin-bottom: 24px; }
    .testi-footer-new { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .testi-avatar-img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid var(--border); }
    .testi-person-name { font-size: 14px; font-weight: 700; color: var(--dark); margin: 0; line-height: 1.3; }
    .testi-person-car  { font-size: 12px; color: var(--gray); margin: 0; }
    .testi-stars-new { color: var(--gold); font-size: 17px; white-space: nowrap; letter-spacing: 2px; }
    /* Nav buttons */
    .testi-nav { display: flex; justify-content: center; gap: 10px; margin-top: 36px; }
    .testi-btn {
        width: 44px; height: 44px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all .2s;
        outline: none; flex-shrink: 0;
    }
    .testi-btn-prev { background: #fff; color: var(--dark); border: 1.5px solid var(--border); }
    .testi-btn-prev:hover { border-color: var(--dark); }
    .testi-btn-next { background: #0166FF; color: #fff; border: none; }
    .testi-btn-next:hover { background: #0052CC; }

    /* ── PRICING ── */
    .pricing-section { padding: 80px 0; background: linear-gradient(160deg, #EEF2FF 0%, #F8FAFF 100%); }
    .pricing-main-title {
        font-size: clamp(1.8rem, 3.5vw, 2.5rem); font-weight: 700; margin: 0 0 8px;
        background: #02214F;
        background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 50%, rgba(2,33,79,1) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    /* Card wrapper — allows badge to overflow above */
    .pc-wrap { position: relative; padding-top: 44px; height: 100%; }
    .pc-wrap.no-badge { padding-top: 0; }
    /* Best Seller badge */
    .pc-bestseller {
        position: absolute; top: 0; z-index: 5;
        display: flex; flex-direction: column; align-items: center;
    }
    .pc-bestseller.left  { left: 8px; }
    .pc-bestseller.right { right: 8px; }
    .pc-fire { font-size: 26px; line-height: 1; margin-bottom: 2px; }
    .pc-bs-circle {
        width: 54px; height: 54px; border-radius: 50%;
        background: linear-gradient(135deg, #0B1A5E 0%, #1E3A8A 100%);
        color: #fff; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        font-size: 9px; font-weight: 900; line-height: 1.3;
        text-transform: uppercase; letter-spacing: .5px;
        box-shadow: 0 4px 14px rgba(11,26,94,.4);
    }
    /* Card */
    .pc-card { border-radius: 14px; overflow: hidden; box-shadow: 0 6px 24px rgba(0,0,0,.1); display: flex; flex-direction: column; height: 100%; }
    .pc-head-dark {
        background: #171F32;
        background: linear-gradient(160deg, rgba(23,31,50,1) 0%, rgba(54,73,109,1) 50%, rgba(23,31,50,1) 100%);
        color: #fff; padding: 14px 20px; text-align: center; font-size: 28px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;
    }
    .pc-head-gold { background: #D5AA38; background: linear-gradient(160deg, rgba(213,170,56,1) 0%, rgba(248,226,143,1) 50%, rgba(213,170,56,1) 100%); color: #fff; padding: 14px 20px; text-align: center; font-size: 28px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; text-shadow: 0 1px 2px rgba(0,0,0,.3); }
    /* Body */
    .pc-body { background: #fff; padding: 16px 20px 20px; flex: 1; }
    .pc-glossy-pill { display: inline-block; border: 1px solid #D0D5DD; border-radius: 50px; padding: 2px 12px; font-size: 10.5px; color: #888; margin-bottom: 10px; }
    .pc-price-num {
        font-size: 2.6rem; font-weight: 600; line-height: 1.1; margin: 0; text-align: center;
        background: #02214F;
        background: linear-gradient(160deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 50%, rgba(2,33,79,1) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .pc-price-num sup { font-size: 13px; font-weight: 600; vertical-align: super; }
    .pc-normal { font-size: 14px; color: var(--gray); margin: 4px 0 16px; text-align: center; }
    .pc-normal del { color: #E63946; }
    /* Feature table */
    .pc-feat-row { display: flex; align-items: stretch; border-bottom: 1px solid #F0F2F5; padding: 10px 0; }
    .pc-feat-row:last-child { border-bottom: none; }
    .pc-feat-lbl { flex: 0 0 52%; font-size: 11.5px; color: #888; display: flex; align-items: center; padding: 32px 8px 32px 0; line-height: 1.3; gap: 8px; }
    .pc-feat-div { width: 1px; background: #E0E4EA; margin: 0 10px; flex-shrink: 0; }
    .pc-feat-val { flex: 1; font-size: 11.5px; font-weight: 700; color: #0166FF; display: flex; align-items: center; justify-content: flex-end; text-align: right; line-height: 1.3; }
    /* Footer */
    .pc-foot-dark {
        background: #171F32;
        background: linear-gradient(160deg, rgba(23,31,50,1) 0%, rgba(54,73,109,1) 50%, rgba(23,31,50,1) 100%);
        color: #fff; padding: 11px 20px; text-align: center; font-size: 20px; font-weight: 400; letter-spacing: .5px;
    }
    .pc-foot-gold { background: #D5AA38; background: linear-gradient(160deg, rgba(213,170,56,1) 0%, rgba(248,226,143,1) 50%, rgba(213,170,56,1) 100%); color: #fff; padding: 11px 20px; text-align: center; font-size: 20px; font-weight: 400; letter-spacing: .5px; text-shadow: 0 1px 2px rgba(0,0,0,.3); }

    /* ── PROMO ── */
    .promo-section { background: linear-gradient(160deg, #EEF2FF 0%, #F8FAFF 100%); padding: 60px 0; }
    .promo-card { background: #fff; border-radius: 20px; box-shadow: 0 4px 32px rgba(0,0,0,.08); padding: 40px 36px; }
    .promo-tag { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
    .promo-tag-icon {
        width: 40px; height: 40px; border-radius: 50%;
        background: #02214F;
        background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 50%, rgba(2,33,79,1) 100%);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .promo-tag-label {
        font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;
        background: #02214F;
        background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .promo-heading { font-size: clamp(1.8rem, 3vw, 2.8rem); font-weight: 400; color: #0B1120; line-height: 1.15; margin-bottom: 14px; }
    .promo-sub { font-size: 14px; color: var(--gray); line-height: 1.6; margin: 0; }
    .promo-sub .accent { color: #0166FF; font-weight: 500; }
    .promo-num {
        font-size: 6rem; font-weight: 600; line-height: 1;
        background: #02214F;
        background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .promo-slot-label {
        font-size: 15px; font-weight: 600; margin: 6px 0 16px;
        background: #02214F;
        background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .promo-free-btn {
        display: inline-block; border-radius: 50px;
        padding: 9px 24px; font-size: 13px; font-weight: 600;
        text-decoration: none; transition: all .15s;
        background: #02214F;
        background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        border: 1.5px solid #0166FF;
    }
    .promo-free-btn:hover { background: linear-gradient(90deg, rgba(2,33,79,1) 0%, rgba(1,102,255,1) 100%); -webkit-text-fill-color: #fff; color: #fff; }
    .promo-right-text { font-size: 15px; color: var(--gray); text-align: left; line-height: 1.6; margin-bottom: 16px; }
    .btn-wa-promo {
        display: flex; align-items: center; gap: 16px; width: 100%;
        background: #0066FF;
        background: linear-gradient(156deg, rgba(0,102,255,1) 0%, rgba(2,33,78,1) 100%);
        color: #fff; border: none; border-radius: 50px;
        padding: 8px 28px 8px 8px;
        font-size: 15px; font-weight: 700;
        text-decoration: none; transition: opacity .15s; margin-bottom: 14px; cursor: pointer;
        box-shadow: 0 4px 20px rgba(10,22,40,.35);
    }
    .btn-wa-promo:hover { opacity: .88; color: #fff; }
    .btn-wa-promo .wa-circle {
        width: 46px; height: 46px; border-radius: 50%;
        background: #fff;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; overflow: hidden;
    }
    .promo-trust { display: flex; align-items: center; border: 1px solid var(--border); border-radius: 50px; overflow: hidden; }
    .promo-trust-item { display: flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 12px; color: var(--gray); flex: 1; justify-content: center; white-space: nowrap; }
    .promo-trust-sep { width: 1px; height: 20px; background: var(--border); flex-shrink: 0; }

    /* ── FLOATING WHATSAPP ── */
    .float-wa {
        position: fixed;
        bottom: 28px;
        right: 24px;
        z-index: 999;
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 24px rgba(0,0,0,.15);
        text-decoration: none;
        animation: waPulse 2.4s ease-in-out infinite;
        transition: transform .2s;
    }
    .float-wa:hover { transform: scale(1.1); }
    .float-wa img   { width: 32px; height: 32px; object-fit: contain; }
    @keyframes waPulse {
        0%, 100% { box-shadow: 0 6px 24px rgba(30,111,224,.25); }
        50%       { box-shadow: 0 6px 32px rgba(30,111,224,.4), 0 0 0 10px rgba(30,111,224,.12); }
    }

    /* ── SCROLL REVEAL ── */
    .reveal {
        opacity: 0;
        transform: translateY(36px);
        transition: opacity .65s cubic-bezier(.22,.61,.36,1), transform .65s cubic-bezier(.22,.61,.36,1);
    }
    .reveal.reveal-left  { transform: translateX(-40px); }
    .reveal.reveal-right { transform: translateX(40px); }
    .reveal.reveal-fade  { transform: none; }
    .reveal.delay-1 { transition-delay: .1s; }
    .reveal.delay-2 { transition-delay: .2s; }
    .reveal.delay-3 { transition-delay: .3s; }
    .reveal.delay-4 { transition-delay: .4s; }
    .reveal.is-visible { opacity: 1; transform: none; }

    /* ── GALLERY SWIPER (mobile) ── */
    .gallery-swiper-wrap { overflow: hidden; }
    .gallery-swiper { overflow: visible; padding-bottom: 36px !important; }
    .gallery-swiper .swiper-slide { height: auto; }
    .gallery-swiper .gallery-item img { height: auto; }
    .gallery-pagination { bottom: 0 !important; }
    .gallery-pagination .swiper-pagination-bullet { background: var(--dark); opacity: .25; width: 8px; height: 8px; }
    .gallery-pagination .swiper-pagination-bullet-active { background: var(--blue); opacity: 1; width: 24px; border-radius: 4px; }

    /* ── PRICING SWIPER (mobile) ── */
    .pricing-swiper-wrap { overflow: visible; }
    .pricing-swiper { overflow: visible; padding-bottom: 40px !important; }
    .pricing-swiper .swiper-slide { height: auto; }
    .pricing-swiper .pc-wrap { height: 100%; }
    .pricing-swiper .pc-card { height: 100%; }
    .pricing-pagination { bottom: 0 !important; }
    .pricing-pagination .swiper-pagination-bullet { background: var(--dark); opacity: .25; width: 8px; height: 8px; }
    .pricing-pagination .swiper-pagination-bullet-active { background: var(--blue); opacity: 1; width: 24px; border-radius: 4px; }

    /* ── FAQ ── */
    .faq-section { padding: 80px 0; background: var(--light-bg); }
    .accordion-button:not(.collapsed) { background: var(--blue-light); color: var(--blue); box-shadow: none; }
    .accordion-button:focus { box-shadow: none; }
    .accordion-item { border: 1px solid var(--border); border-radius: 10px !important; margin-bottom: 8px; overflow: hidden; }
    .accordion-button { font-size: 14px; font-weight: 600; color: var(--dark); }
    .accordion-body  { font-size: 13.5px; color: var(--gray); line-height: 1.7; }

    /* ── FOOTER ── */
    .lp-footer { background: #edf0f5; padding: 50px 0 24px; }
    .lp-footer .f-logo { height: 32px; }
    .lp-footer p  { color: #4a5568; font-size: 13px; }
    .lp-footer a  { color: #4a5568; font-size: 13px; text-decoration: none; }
    .lp-footer a:hover { color: var(--dark); }
    .lp-footer h6 { color: var(--dark); font-weight: 700; font-size: 32px; margin-bottom: 14px; }
    .f-divider { border-color: rgba(0,0,0,.1); margin: 30px 0 20px; }
    .soc-icon {
        width: 48px; height: 48px; border-radius: 10px;
        background: #fff; color: var(--dark);
        border: 1px solid #d1d9e6;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 22px; text-decoration: none !important;
        transition: background .15s, color .15s;
    }
    .soc-icon:hover { background: var(--blue); color: #fff !important; border-color: var(--blue); }
    .copyright { color: #9aa3b2; font-size: 12px; text-align: center; margin: 0; }

    /* ── RESPONSIVE ── */

    /* ── Large desktop: ≥1400px ── */
    @media (min-width: 1400px) {
        .hero-wrap      { padding: 110px 64px 64px; min-height: 72vh; }
        .hero-h1        { font-size: 3.6rem; }
        .hero-desc      { font-size: 16px; max-width: 500px; }
        .promo-card     { padding: 48px 48px; }
        .promo-heading  { font-size: 2.6rem; }
    }

    /* ── Laptop: 992px – 1199px ── */
    @media (max-width: 1199px) {
        .lp-nav         { padding: 20px 32px; }
        .hero-wrap      { padding: 96px 32px 44px; }
        .hero-text-side { padding-left: 20px; }
        .callout-badge  { font-size: 11.5px; padding: 6px 14px; }
        .adv-grid       { height: 400px; }
        .promo-num      { font-size: 5rem; }
    }

    /* ── Tablet: 768px – 991px ── */
    @media (max-width: 991px) {
        /* Navbar */
        .lp-nav { padding: 16px 24px; }

        /* Hero */
        .hero-wrap {
            flex-direction: column;
            min-height: auto;
            padding: 88px 24px 36px;
            gap: 20px;
        }
        .hero-car-side  { width: 100%; max-width: 560px; margin: 0 auto; padding-right: 0; flex: none; }
        .hero-text-side { width: 100%; padding-left: 0; text-align: center; }
        .hero-h1        { font-size: clamp(1.6rem, 2.8vw, 3rem); }
        .hero-desc      { max-width: 540px; }
        .hero-text-side .d-flex { justify-content: center; }
        .callout-badge  { font-size: 11px; padding: 5px 12px; }

        /* Why / Adv grid */
        .adv-grid       { height: auto; flex-direction: column; }
        .adv-main       { flex: none; height: 260px; }
        .adv-stack      { flex-direction: row; height: 180px; }
        .adv-stack-item { flex: 1; }

        /* Trust bar */
        .trust-row  { flex-wrap: wrap; justify-content: center; gap: 0; }
        .trust-item { padding: 12px 16px; }
        .trust-sep  { display: none !important; }

        /* Promo */
        .promo-right-text { text-align: left; }
        .promo-num  { font-size: 4.5rem; }
        .promo-card { padding: 32px 24px; }

        /* Footer */
        .lp-footer h6 { font-size: 24px; }
    }

    /* ── Phone landscape / small tablet: 576px – 767px ── */
    @media (max-width: 767px) {
        /* Trust bar — layout 2x2 grid, icon di atas teks */
        .trust-bar          { margin-top: 0; background: transparent; padding: 16px 16px 32px; }
        .trust-bar .container { border-radius: 20px; padding: 8px 0; }
        .trust-row          { display: grid; grid-template-columns: 1fr 1fr; gap: 0; flex-wrap: unset; }
        .trust-item         { flex-direction: column; align-items: center; text-align: center; padding: 20px 12px; gap: 8px; flex-shrink: unset; }
        .trust-item .t-label { font-size: 13px; white-space: normal; line-height: 1.3; }
        .trust-sep          { display: none !important; }

        /* Center align semua section (kecuali FAQ) */
        .hero-h1        { text-align: center; }
        .hero-desc      { text-align: center; margin-left: auto; margin-right: auto; }
        .why-feature-tag { justify-content: center; }
        .why-feature-q, .why-feature-desc { text-align: center; }
        .promo-right-text { text-align: center; }
        .promo-tag        { justify-content: center; }
        .promo-heading    { text-align: center; }
        .promo-sub        { text-align: center; }
        .promo-slot-label { text-align: center; }
        .lp-footer      { text-align: center; }
        .lp-footer .d-flex { justify-content: center; }

        /* Hero */
        .hero-h1   { font-size: clamp(1.6rem, 5vw, 2.4rem); white-space: normal; }
        .hero-desc { font-size: 14px; }
        .btn-konsul, .btn-produk { font-size: 13px; padding: 11px 18px; }

        /* Trust bar */
        .trust-circle { width: 40px; height: 40px; font-size: 17px; }
        .trust-item .t-label { font-size: 13px; }

        /* Why section */
        .adv-stack { flex-direction: column; height: auto; }
        .adv-stack-item { height: 160px; }
        .why-feature:first-child { margin-top: 24px; }

        /* Benefits — 2 kolom di phone */
        .benefits-section .row { row-gap: 64px !important; padding-top: 36px !important; }
        .bc-card  { padding: 52px 8px 12px; border-radius: 16px; }
        .bc-title { font-size: 13px; }
        .bc-desc  { font-size: 11.5px; }
        .bc-icon-wrap { width: 72px; height: 72px; top: -36px; left: 50%; transform: translateX(-50%); display: flex; align-items: center; justify-content: center; }
        .bc-icon-wrap iconify-icon { font-size: 32px; width: auto !important; height: auto !important; }
        .benefits-section .text-center { margin-bottom: 80px !important; }

        /* Pricing */
        .pc-head-dark, .pc-head-gold { font-size: 22px; }
        .pc-price-num { font-size: 2rem; }
        .pc-feat-lbl, .pc-feat-val { font-size: 11px; }

        /* Promo */
        .promo-heading { font-size: clamp(1.4rem, 4vw, 2rem); }
        .promo-num { font-size: 3.5rem; }
        .btn-wa-promo { font-size: 13px; padding: 8px 16px 8px 8px; }
        .promo-trust-item { font-size: 11px; padding: 6px 10px; }

        /* FAQ */
        .accordion-button { font-size: 13px; }

        /* Footer */
        .lp-footer h6 { font-size: 20px; }
        .lp-footer { padding: 40px 0 20px; }
    }

    /* ── Phone portrait: < 576px ── */
    @media (max-width: 575px) {
        /* Navbar */
        .lp-nav      { padding: 14px 16px; }
        .lp-nav-logo { height: 18px; }

        /* Hero */
        .hero-wrap   { padding: 76px 16px 28px; gap: 16px; }
        .hero-h1     { font-size: clamp(1.5rem, 6vw, 2rem); white-space: normal; letter-spacing: 0; }
        .hero-desc   { font-size: 13px; line-height: 1.7; }
        .btn-konsul, .btn-produk { font-size: 12.5px; padding: 10px 14px; }
        .callout-badge { font-size: 10px; padding: 5px 10px; }

        /* Trust bar */
        .trust-circle { width: 40px; height: 40px; font-size: 17px; }

        /* Sections padding */
        .why-section, .gallery-section, .testimonials-section,
        .pricing-section, .faq-section { padding: 48px 0; }
        .benefits-section { padding: 32px 0 48px; }

        /* Why / adv grid */
        .adv-grid       { flex-direction: column; height: auto; gap: 8px; }
        .adv-main       { flex: none; height: 200px; }
        .adv-stack      { flex-direction: row; }
        .adv-stack-item { height: 130px; }
        .why-title-wrap { padding: 12px 16px; }
        .sec-title      { font-size: clamp(1.1rem, 4.5vw, 1.5rem); }

        /* Benefits */
        .benefits-section .row { row-gap: 56px !important; padding-top: 32px !important; }
        .bc-card   { padding: 48px 6px 10px; }
        .bc-icon-wrap { width: 64px; height: 64px; top: -32px; left: 50%; transform: translateX(-50%); display: flex; align-items: center; justify-content: center; }
        .bc-icon-wrap iconify-icon { font-size: 28px; width: auto !important; height: auto !important; }
        .bc-title  { font-size: 12px; }
        .bc-desc   { font-size: 11px; }

        /* Gallery */
        .gallery-section .col-6 { padding: 4px; }

        /* Testimonials */
        .testi-card-new { padding: 18px 16px; }
        .testi-quote-text { font-size: 13px; }

        /* Pricing */
        .pricing-main-title { font-size: 1.6rem; }
        .pc-head-dark, .pc-head-gold { font-size: 20px; letter-spacing: 1px; }
        .pc-price-num { font-size: 1.75rem; }
        .pc-normal    { font-size: 12px; }
        .pc-feat-lbl, .pc-feat-val { font-size: 10.5px; padding: 24px 4px 24px 0; }

        /* Promo */
        .promo-card    { padding: 24px 16px; border-radius: 14px; }
        .promo-heading { font-size: 1.4rem; }
        .promo-num     { font-size: 3rem; }
        .promo-slot-label { font-size: 13px; }
        .btn-wa-promo  { font-size: 12.5px; gap: 10px; }
        .promo-trust   { flex-wrap: nowrap; border-radius: 12px; }
        .promo-trust-item { flex: 1; font-size: 10.5px; padding: 8px 6px; }
        .promo-trust-sep  { display: block; }

        /* FAQ */
        .accordion-button { font-size: 12.5px; }
        .accordion-body   { font-size: 12.5px; }

        /* Footer */
        .lp-footer { padding: 32px 0 16px; }
        .lp-footer h6 { font-size: 18px; margin-bottom: 10px; }
        .lp-footer p, .lp-footer a { font-size: 12.5px; }
        .soc-icon { width: 42px; height: 42px; }
        .f-logo   { height: 26px; }
    }
    </style>
    <script>
    function gtag_report_conversion(url) {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ 'event': 'whatsapp_click' });
      var callback = function () {
        if (typeof(url) != 'undefined') {
          window.location = url;
        }
      };
      gtag('event', 'conversion', {
          'send_to': 'AW-11332274683/1dNiCKjQwsccEPuT05sq',
          'event_callback': callback
      });
      return false;
    }
    </script>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5DH3T7FD');</script>
    <!-- End Google Tag Manager -->
</head>
<body>

@php
    $waNumber = '6282121212093';
    $waText   = urlencode('Halo Acegard, saya ingin konsultasi PPF untuk kendaraan saya.');
    $waLink   = "https://wa.me/{$waNumber}?text={$waText}";
@endphp

{{-- ─── HERO ──────────────────────────────────────────────────── --}}
<section class="hero-section">
    <nav class="lp-nav">
        <img src="{{ asset('assets/frontend/ppf/logo.webp') }}" alt="Acegard" class="lp-nav-logo">
    </nav>
    <div class="hero-wrap">

        {{-- LEFT: Car + callout annotations --}}
        <div class="hero-car-side">
            <div class="car-callout-wrapper">
                <img src="{{ asset('assets/frontend/ppf/car-hero.webp') }}"
                     alt="Mobil dengan Acegard PPF" class="hero-car-img">

                {{-- SVG connecting lines --}}
                <svg class="callout-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                    {{-- Badge "Best Quality" → bodi kiri bawah --}}
                    <line x1="21" y1="68" x2="40" y2="55"
                          stroke="#1E6FE0" stroke-width="0.6" vector-effect="non-scaling-stroke"/>
                    <circle cx="21" cy="68" r="0.9" fill="#1E6FE0" vector-effect="non-scaling-stroke"/>
                    <circle cx="40" cy="55" r="0.9" fill="#1E6FE0" vector-effect="non-scaling-stroke"/>
                    {{-- Badge "TPU by Lubrizol USA" → atap --}}
                    <line x1="46" y1="12" x2="45" y2="29"
                          stroke="#1E6FE0" stroke-width="0.6" vector-effect="non-scaling-stroke"/>
                    <circle cx="46" cy="12" r="0.9" fill="#1E6FE0" vector-effect="non-scaling-stroke"/>
                    <circle cx="45" cy="29" r="0.9" fill="#1E6FE0" vector-effect="non-scaling-stroke"/>
                    {{-- Badge "Anti Yellowing" → kanan tengah --}}
                    <line x1="70" y1="33" x2="61" y2="44"
                          stroke="#1E6FE0" stroke-width="0.6" vector-effect="non-scaling-stroke"/>
                    <circle cx="70" cy="33" r="0.9" fill="#1E6FE0" vector-effect="non-scaling-stroke"/>
                    <circle cx="61" cy="44" r="0.9" fill="#1E6FE0" vector-effect="non-scaling-stroke"/>
                </svg>

                {{-- Callout badges --}}
                <div class="callout-badge" style="left:4%;top:62%">Best Quality</div>
                <div class="callout-badge" style="left:29%;top:4%">TPU by Lubrizol USA</div>
                <div class="callout-badge" style="left:57%;top:26%">Anti Yellowing</div>
            </div>
        </div>

        {{-- RIGHT: Text — bebas dari col Bootstrap --}}
        <div class="hero-text-side">
            <h1 class="hero-h1">
                <span style="display:block">Goresan Hilang Hanya</span>
                <span class="blue-line">Dalam 5 Detik</span>
            </h1>
            <p class="hero-desc">
                Acegard PPF menghadirkan teknologi Self-Healing eksklusif yang membantu
                menyamarkan goresan halus secara otomatis, sekaligus memberikan perlindungan
                cat mobil premium dengan salah satu teknologi paling inovatif yang tersedia di Indonesia.
            </p>
            <div class="d-flex justify-content-between align-items-center gap-3">
                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn-konsul flex-grow-1 justify-content-center" onclick="return gtag_report_conversion('{{ $waLink }}');">
                    Konsultasi Gratis
                </a>
                <a href="#pricing" class="btn-produk flex-grow-1 justify-content-center">
                    Lihat Produk
                </a>
            </div>
        </div>

    </div>
</section>

{{-- ─── TRUST BAR ─────────────────────────────────────────────── --}}
<section class="trust-bar">
    <div class="container">
        <div class="trust-row">

            <div class="trust-item">
                <div class="trust-circle">
                    <iconify-icon icon="ph:star-fill" width="22" height="22"></iconify-icon>
                </div>
                <span class="t-label">4,9 Rating di Tokopedia</span>
            </div>

            <div class="trust-sep d-none d-sm-block"></div>

            <div class="trust-item">
                <div class="trust-circle">
                    <iconify-icon icon="ph:certificate-bold" width="22" height="22"></iconify-icon>
                </div>
                <span class="t-label">Garansi Resmi 8 Tahun</span>
            </div>

            <div class="trust-sep d-none d-sm-block"></div>

            <div class="trust-item">
                <div class="trust-circle">
                    <iconify-icon icon="ph:shield-check-bold" width="22" height="22"></iconify-icon>
                </div>
                <span class="t-label">Perlindungan Maksimal</span>
            </div>

            <div class="trust-sep d-none d-sm-block"></div>

            <div class="trust-item">
                <div class="trust-circle">
                    <iconify-icon icon="ph:users-bold" width="22" height="22"></iconify-icon>
                </div>
                <span class="t-label">500+ Pelanggan Puas</span>
            </div>

        </div>
    </div>
</section>

{{-- ─── WHY PPF ────────────────────────────────────────────────── --}}
<section class="why-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="why-title-wrap">
                <h3 class="sec-title">Mengapa Acegard PPF Menjadi <span>Pilihan Pemilik Mobil Premium?</span></h3>
            </div>
        </div>

        {{-- Feature highlight row --}}
        <div class="row align-items-center gy-4 mb-5">

            {{-- LEFT: Image grid --}}
            <div class="col-lg-7">
                <div class="adv-grid">
                    {{-- Large main image --}}
                    <div class="adv-main">
                        <img src="{{ asset('assets/frontend/ppf/why-1.webp') }}"
                             alt="Hydrophobic" class="adv-grid-img">
                        <div class="adv-overlay"></div>
                        <div class="adv-grid-label">
                            <div class="adv-grid-icon"><i class="ri-drop-line"></i></div>
                            <span class="adv-grid-text">Hydrophobic</span>
                        </div>
                    </div>
                    {{-- Two stacked images --}}
                    <div class="adv-stack">
                        <div class="adv-stack-item">
                            <img src="{{ asset('assets/frontend/ppf/why-2.webp') }}"
                                 alt="Instan Self Healing" class="adv-grid-img">
                            <div class="adv-overlay"></div>
                            <div class="adv-grid-label">
                                <div class="adv-grid-icon"><i class="ri-refresh-line"></i></div>
                                <span class="adv-grid-text">Instan Self<br>Healing</span>
                            </div>
                        </div>
                        <div class="adv-stack-item">
                            <img src="{{ asset('assets/frontend/ppf/why-3.webp') }}"
                                 alt="Tear Strength" class="adv-grid-img">
                            <div class="adv-overlay"></div>
                            <div class="adv-grid-label">
                                <div class="adv-grid-icon"><i class="ri-shield-line"></i></div>
                                <span class="adv-grid-text">Tear Strength</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Text features --}}
            <div class="col-lg-5 ps-lg-5">
                <div class="why-feature">
                    <div class="why-feature-tag">Hydrophobic</div>
                    <h4 class="why-feature-q">Mobil Cepat Kotor dan Sulit Dibersihkan?</h4>
                    <p class="why-feature-desc">Teknologi Hydrophobic Acegard PPF membantu menolak air, debu, dan kotoran agar tidak mudah menempel pada permukaan kendaraan. Hasilnya, mobil lebih mudah dibersihkan dan tetap terlihat bersih lebih lama.</p>
                </div>
                <div class="why-feature">
                    <div class="why-feature-tag">Instan Self Healing</div>
                    <h4 class="why-feature-q">Khawatir Baret Halus Saat Pemakaian Harian?</h4>
                    <p class="why-feature-desc">Teknologi Self-Healing eksklusif Acegard PPF membantu menyamarkan goresan ringan secara otomatis. Mobil tetap terlihat mulus meskipun digunakan setiap hari.</p>
                </div>
                <div class="why-feature">
                    <div class="why-feature-tag">Tear Strength</div>
                    <h4 class="why-feature-q">Takut Body Mobil Terkena Gesekan dan Kerikil Jalan?</h4>
                    <p class="why-feature-desc">Material Acegard PPF memiliki daya tahan tinggi terhadap benturan ringan, gesekan, dan serpihan kerikil di jalan. Memberikan lapisan perlindungan ekstra untuk menjaga cat mobil tetap aman.</p>
                </div>
            </div>

        </div>

    </div>
</section>

{{-- ─── BENEFITS ───────────────────────────────────────────────── --}}
<section class="benefits-section">
    <div class="container">
        <div class="text-center" style="margin-bottom:120px">
            <div class="why-title-wrap">
                <h2 class="sec-title">Mengapa Acegard Memberikan <span>Perlindungan Lebih Maksimal?</span></h2>
            </div>
        </div>
        <div class="row gx-4" style="padding-top:40px; row-gap:90px;">
            @php
                $benefits = [
                    ['num'=>1,'icon'=>'ph:arrows-clockwise-bold',
                     'title'=>'Instant Self-Healing',
                     'desc'=>'Goresan ringan dapat tersamarkan secara otomatis'],
                    ['num'=>2,'icon'=>'ph:waves-bold',
                     'title'=>'Hydrophobic Technology',
                     'desc'=>'Air, debu, dan kotoran tidak mudah menempel'],
                    ['num'=>3,'icon'=>'ph:cpu-bold',
                     'title'=>'Stone Chip Protection',
                     'desc'=>'Material premium dengan daya tahan tinggi'],
                    ['num'=>4,'icon'=>'ph:eye-bold',
                     'title'=>'Crystal Clear Finish',
                     'desc'=>'Hasil transparan, menjaga warna kendaraan tetap sempurna'],
                    ['num'=>5,'icon'=>'ph:seal-check-bold',
                     'title'=>'Garansi 6 - 8 Tahun',
                     'desc'=>'Garansi resmi hingga 6 - 8 tahun untuk ketenangan anda'],
                    ['num'=>6,'icon'=>'ph:squares-four-bold',
                     'title'=>'Anti Yellowing',
                     'desc'=>'Teknologi Anti Yellowing membantu menjaga film tetap jernih'],
                    ['num'=>7,'icon'=>'ph:sun-bold',
                     'title'=>'UV Protection',
                     'desc'=>'Melindungi cat kendaraan dari paparan sinar UV'],
                    ['num'=>8,'icon'=>'ph:flag-bold',
                     'title'=>'Premium TPU by Lubrizol USA',
                     'desc'=>'Menggunakan material TPU premium dari Lubrizol USA'],
                ];
            @endphp
            @foreach ($benefits as $b)
            <div class="col-6 col-lg-3">
                <div class="bc-card">
                    <div class="bc-num">{{ $b['num'] }}</div>
                    <div class="bc-icon-wrap">
                        <iconify-icon icon="{{ $b['icon'] }}" width="38" height="38"></iconify-icon>
                    </div>
                    <h6 class="bc-title">{{ $b['title'] }}</h6>
                    <p class="bc-desc">{{ $b['desc'] }}</p>
                    <div class="bc-line"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ─── GALLERY ────────────────────────────────────────────────── --}}
<section class="gallery-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="why-title-wrap">
                <h2 class="sec-title">Galeri Instalasi <span>Acegard PPF</span></h2>
            </div>
        </div>
        @php
            $gallery = [
                ['img'=>'assets/frontend/ppf/bmw-ix-drive.webp','brand'=>'BMW IX','model'=>'XDRIVE'],
                ['img'=>'assets/frontend/ppf/mazda-3.webp','brand'=>'MAZDA','model'=>'3'],
                ['img'=>'assets/frontend/ppf/denza-d-9.webp','brand'=>'DENZA','model'=>'D9'],
                ['img'=>'assets/frontend/ppf/byd-atto-1.webp','brand'=>'BYD','model'=>'ATTO1'],
                ['img'=>'assets/frontend/ppf/jaecoo-j5.webp','brand'=>'JAECOO','model'=>'J5'],
                ['img'=>'assets/frontend/ppf/mini-cooper.webp','brand'=>'MINI','model'=>'COOPER'],
                ['img'=>'assets/frontend/ppf/vinfast.webp','brand'=>'VINFAST','model'=>''],
                ['img'=>'assets/frontend/ppf/aion-hyptec.webp','brand'=>'AION','model'=>'HYPTEC'],
            ];
        @endphp

        {{-- Desktop / Tablet: Grid --}}
        <div class="row g-3 d-none d-md-flex">
            @foreach ($gallery as $g)
            <div class="col-6 col-lg-3">
                <div class="gallery-item">
                    <img src="{{ asset($g['img']) }}"
                         alt="{{ $g['brand'] }} {{ $g['model'] }}" loading="lazy">
                </div>
            </div>
            @endforeach
        </div>

        {{-- Mobile: Swiper --}}
        <div class="d-block d-md-none gallery-swiper-wrap">
            <div class="swiper gallery-swiper">
                <div class="swiper-wrapper">
                    @foreach ($gallery as $g)
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="{{ asset($g['img']) }}"
                                 alt="{{ $g['brand'] }} {{ $g['model'] }}" loading="lazy">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="swiper-pagination gallery-pagination"></div>
            </div>
        </div>

    </div>
</section>

{{-- ─── TESTIMONIALS ───────────────────────────────────────────── --}}
<section class="testimonials-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="testi-main-title">Apa Kata Mereka?</h2>
        </div>
        <div class="swiper testi-swiper">
            <div class="swiper-wrapper">
                @php
                    $testimonials = [
                        ['name'=>'Rizky Firmansyah','car'=>'BMW IX xDrive','stars'=>5,'avatar'=>'assets/images/user.png',
                         'text'=>'Hasil pemasangan PPF Acegard di BMW IX saya luar biasa rapi dan bersih. Teknisi sangat profesional, teliti di setiap sudut mobil. Sudah 6 bulan dipakai, cat tetap mulus seperti baru keluar dari showroom. Sangat rekomendasikan!'],
                        ['name'=>'Andika Prasetyo','car'=>'Denza D9','stars'=>5,'avatar'=>'assets/images/user.png',
                         'text'=>'Awalnya ragu pasang PPF, tapi setelah coba Acegard langsung keliatan bedanya. Goresan halus akibat car wash hilang sendiri dalam beberapa menit! Garansi 8 tahun juga bikin tenang. Worth it banget buat mobil premium.'],
                        ['name'=>'Hendra Kurniawan','car'=>'Mini Cooper S','stars'=>5,'avatar'=>'assets/images/user.png',
                         'text'=>'Pelayanan Acegard top banget, dari konsultasi sampai pemasangan selesai dalam 1 hari. Cat Mini Cooper saya sekarang terlindungi sempurna dari kerikil dan debu jalan. Hydrophobic-nya juga kerasa banget, air langsung ngalir sendiri.'],
                        ['name'=>'Budi Santoso','car'=>'BYD Atto 1','stars'=>5,'avatar'=>'assets/images/user.png',
                         'text'=>'Sudah pakai PPF Acegard seri AEGIS hampir setahun, hasilnya konsisten jernih tanpa yellowing sama sekali. Teknisinya juga sabar menjawab semua pertanyaan saya. Definitely akan repeat order untuk mobil kedua.'],
                        ['name'=>'Fajar Nugroho','car'=>'Jaecoo J5','stars'=>5,'avatar'=>'assets/images/user.png',
                         'text'=>'Workshop Acegard bersih dan nyaman, proses pemasangannya bisa dipantau langsung. Self-healing PPF-nya benar-benar bekerja — baret kecil dari kunci hilang sendiri setelah kena panas matahari. Kualitas terbaik di kelasnya!'],
                        ['name'=>'Dimas Ardiansyah','car'=>'Mazda 3','stars'=>5,'avatar'=>'assets/images/user.png',
                         'text'=>'Harga kompetitif dengan kualitas film TPU Lubrizol USA yang premium. Pemasangan presisi, tidak ada gelembung atau lipatan. Mazda 3 saya terasa seperti baru terus setelah pasang PPF Acegard. Terima kasih tim Acegard!'],
                    ];
                @endphp
                @foreach ($testimonials as $t)
                <div class="swiper-slide h-auto">
                    <div class="testi-card-new">
                        <p class="testi-quote-text">{{ $t['text'] }}</p>
                        <div class="testi-footer-new">
                            <div>
                                <p class="testi-person-name">{{ $t['name'] }}</p>
                                <p class="testi-person-car">{{ $t['car'] }}</p>
                            </div>
                            <div class="testi-stars-new">
                                @for($i = 0; $i < $t['stars']; $i++)★@endfor
                                @for($i = $t['stars']; $i < 5; $i++)<span style="color:#E2E8F0">★</span>@endfor
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="testi-nav">
            <button class="testi-btn testi-btn-prev" id="testiPrev">
                <iconify-icon icon="ph:arrow-left-bold" width="20" height="20"></iconify-icon>
            </button>
            <button class="testi-btn testi-btn-next" id="testiNext">
                <iconify-icon icon="ph:arrow-right-bold" width="20" height="20"></iconify-icon>
            </button>
        </div>
    </div>
</section>

{{-- ─── PRICING ────────────────────────────────────────────────── --}}
<section class="pricing-section" id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="pricing-main-title">Pilih Perlindungan Mobil Anda!</h2>
        </div>

        {{-- ── DESKTOP / TABLET: Grid biasa ── --}}
        <div class="d-none d-md-block">
            {{-- Row 1: ARMOR + ARMOR PLUS --}}
            <div class="row g-4 mb-4 justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="pc-wrap">
                        <div class="pc-bestseller left">
                            <img src="{{ asset('assets/frontend/ppf/best-seller.webp') }}" alt="Best Seller" style="width:130px;height:auto">
                        </div>
                        <div class="pc-card">
                            <div class="pc-head-dark">ARMOR</div>
                            <div class="pc-body">
                                <div class="text-center"><span class="pc-glossy-pill">Glossy</span></div>
                                <p class="pc-price-num">Rp 11.900.000<sup>/M</sup></p>
                                <p class="pc-normal">Harga Normal <del>Rp 17.000.000</del></p>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Self Healing Rate</div><div class="pc-feat-div"></div><div class="pc-feat-val">Heat Self Healing</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Garansi</div><div class="pc-feat-div"></div><div class="pc-feat-val">5 TAHUN</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Urethane Original</div><div class="pc-feat-div"></div><div class="pc-feat-val">Lubrizol Mix Grade</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Product Thickness</div><div class="pc-feat-div"></div><div class="pc-feat-val">7.5 Mils</div></div>
                            </div>
                            <div class="pc-foot-dark">Best For Daily Protection</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5">
                    <div class="pc-wrap">
                        <div class="pc-bestseller right">
                            <img src="{{ asset('assets/frontend/ppf/best-seller.webp') }}" alt="Best Seller" style="width:130px;height:auto">
                        </div>
                        <div class="pc-card">
                            <div class="pc-head-dark">ARMOR PLUS</div>
                            <div class="pc-body">
                                <div class="text-center"><span class="pc-glossy-pill">Glossy</span></div>
                                <p class="pc-price-num">Rp 15.400.000<sup>/M</sup></p>
                                <p class="pc-normal">Harga Normal <del>Rp 22.000.000</del></p>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Self Healing Rate</div><div class="pc-feat-div"></div><div class="pc-feat-val">Heat Self Healing</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Garansi</div><div class="pc-feat-div"></div><div class="pc-feat-val">7 TAHUN</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Urethane Original</div><div class="pc-feat-div"></div><div class="pc-feat-val">Lubrizol Mix Grade</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Product Thickness</div><div class="pc-feat-div"></div><div class="pc-feat-val">8.5 Mils</div></div>
                            </div>
                            <div class="pc-foot-dark">Best For Daily Protection</div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Row 2: AEGIS, MATTE, AEGIS MAX+ --}}
            <div class="row g-4 justify-content-center">
                @php
                    $premiums = [
                        ['name'=>'AEGIS','price'=>'Rp 19.600.000','orig'=>'Rp 28.000.000',
                         'heal'=>'Instan Self Healing','garansi'=>'8 TAHUN','urethane'=>'Lubrizol Estane Grade','tebal'=>'7.5 Mils',
                         'head'=>'gold','foot'=>'gold','footer'=>'Best For Maximum Protection'],
                        ['name'=>'MATTE','price'=>'Rp 13.900.000','orig'=>'Rp 20.000.000',
                         'heal'=>'Heat Self Healing','garansi'=>'5 TAHUN','urethane'=>'Lubrizol Mix Grade','tebal'=>'7.5 Mils',
                         'head'=>'dark','foot'=>'dark','footer'=>'Best For Daily Protection'],
                        ['name'=>'AEGIS MAX +','price'=>'Rp 24.500.000','orig'=>'Rp 35.000.000',
                         'heal'=>'Instan Self Healing','garansi'=>'8 TAHUN','urethane'=>'Lubrizol Estane Grade','tebal'=>'8.5 Mils',
                         'head'=>'gold','foot'=>'gold','footer'=>'Best For Maximum Protection'],
                    ];
                @endphp
                @foreach ($premiums as $pp)
                <div class="col-md-6 col-lg-4">
                    <div class="pc-wrap no-badge">
                        <div class="pc-card">
                            <div class="pc-head-{{ $pp['head'] }}">{{ $pp['name'] }}</div>
                            <div class="pc-body">
                                <div class="text-center"><span class="pc-glossy-pill">Glossy</span></div>
                                <p class="pc-price-num">{{ $pp['price'] }}<sup>/M</sup></p>
                                <p class="pc-normal">Harga Normal <del>{{ $pp['orig'] }}</del></p>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Self Healing Rate</div><div class="pc-feat-div"></div><div class="pc-feat-val">{{ $pp['heal'] }}</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Garansi</div><div class="pc-feat-div"></div><div class="pc-feat-val">{{ $pp['garansi'] }}</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Urethane Original</div><div class="pc-feat-div"></div><div class="pc-feat-val">{{ $pp['urethane'] }}</div></div>
                                <div class="pc-feat-row"><div class="pc-feat-lbl">Product Thickness</div><div class="pc-feat-div"></div><div class="pc-feat-val">{{ $pp['tebal'] }}</div></div>
                            </div>
                            <div class="pc-foot-{{ $pp['foot'] }}">{{ $pp['footer'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── MOBILE: Swiper ── --}}
        <div class="d-block d-md-none pricing-swiper-wrap">
            <div class="swiper pricing-swiper">
                <div class="swiper-wrapper">

                    {{-- ARMOR --}}
                    <div class="swiper-slide">
                        <div class="pc-wrap">
                            <div class="pc-bestseller left">
                                <img src="{{ asset('assets/frontend/ppf/best-seller.webp') }}" alt="Best Seller" style="width:110px;height:auto">
                            </div>
                            <div class="pc-card">
                                <div class="pc-head-dark">ARMOR</div>
                                <div class="pc-body">
                                    <div class="text-center"><span class="pc-glossy-pill">Glossy</span></div>
                                    <p class="pc-price-num">Rp 11.900.000<sup>/M</sup></p>
                                    <p class="pc-normal">Harga Normal <del>Rp 17.000.000</del></p>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Self Healing Rate</div><div class="pc-feat-div"></div><div class="pc-feat-val">Heat Self Healing</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Garansi</div><div class="pc-feat-div"></div><div class="pc-feat-val">5 TAHUN</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Urethane Original</div><div class="pc-feat-div"></div><div class="pc-feat-val">Lubrizol Mix Grade</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Product Thickness</div><div class="pc-feat-div"></div><div class="pc-feat-val">7.5 Mils</div></div>
                                </div>
                                <div class="pc-foot-dark">Best For Daily Protection</div>
                            </div>
                        </div>
                    </div>

                    {{-- ARMOR PLUS --}}
                    <div class="swiper-slide">
                        <div class="pc-wrap">
                            <div class="pc-bestseller right">
                                <img src="{{ asset('assets/frontend/ppf/best-seller.webp') }}" alt="Best Seller" style="width:110px;height:auto">
                            </div>
                            <div class="pc-card">
                                <div class="pc-head-dark">ARMOR PLUS</div>
                                <div class="pc-body">
                                    <div class="text-center"><span class="pc-glossy-pill">Glossy</span></div>
                                    <p class="pc-price-num">Rp 15.400.000<sup>/M</sup></p>
                                    <p class="pc-normal">Harga Normal <del>Rp 22.000.000</del></p>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Self Healing Rate</div><div class="pc-feat-div"></div><div class="pc-feat-val">Heat Self Healing</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Garansi</div><div class="pc-feat-div"></div><div class="pc-feat-val">7 TAHUN</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Urethane Original</div><div class="pc-feat-div"></div><div class="pc-feat-val">Lubrizol Mix Grade</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Product Thickness</div><div class="pc-feat-div"></div><div class="pc-feat-val">8.5 Mils</div></div>
                                </div>
                                <div class="pc-foot-dark">Best For Daily Protection</div>
                            </div>
                        </div>
                    </div>

                    {{-- AEGIS, MATTE, AEGIS MAX+ --}}
                    @foreach ($premiums as $pp)
                    <div class="swiper-slide">
                        <div class="pc-wrap no-badge">
                            <div class="pc-card">
                                <div class="pc-head-{{ $pp['head'] }}">{{ $pp['name'] }}</div>
                                <div class="pc-body">
                                    <div class="text-center"><span class="pc-glossy-pill">Glossy</span></div>
                                    <p class="pc-price-num">{{ $pp['price'] }}<sup>/M</sup></p>
                                    <p class="pc-normal">Harga Normal <del>{{ $pp['orig'] }}</del></p>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Self Healing Rate</div><div class="pc-feat-div"></div><div class="pc-feat-val">{{ $pp['heal'] }}</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Garansi</div><div class="pc-feat-div"></div><div class="pc-feat-val">{{ $pp['garansi'] }}</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Urethane Original</div><div class="pc-feat-div"></div><div class="pc-feat-val">{{ $pp['urethane'] }}</div></div>
                                    <div class="pc-feat-row"><div class="pc-feat-lbl">Product Thickness</div><div class="pc-feat-div"></div><div class="pc-feat-val">{{ $pp['tebal'] }}</div></div>
                                </div>
                                <div class="pc-foot-{{ $pp['foot'] }}">{{ $pp['footer'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
                <div class="swiper-pagination pricing-pagination"></div>
            </div>
        </div>

    </div>
</section>

{{-- ─── PROMO SECTION ──────────────────────────────────────────── --}}
<section class="promo-section">
    <div class="container">
        <div class="promo-card">
            <div class="row align-items-center gy-4">

                {{-- LEFT --}}
                <div class="col-lg-5">
                    <div class="promo-tag">
                        <div class="promo-tag-icon">
                            <iconify-icon icon="ph:tag-bold" width="18" height="18"></iconify-icon>
                        </div>
                        <span class="promo-tag-label">Promo Terbatas</span>
                    </div>
                    <h2 class="promo-heading">Dapatkan penawaran terbaik khusus hari ini!</h2>
                    <p class="promo-sub">Slot terbatas setiap harinya dan dapatkan <span class="accent">promo spesial</span> khusus bulan ini</p>
                </div>

                {{-- CENTER --}}
                <div class="col-lg-2 text-center">
                    <div class="promo-num">21</div>
                    <p class="promo-slot-label">Slot Promo Spesial Tersedia</p>
                    <span class="promo-free-btn">
                        Free Kaca Film Depan Acegard
                    </span>
                </div>

                {{-- RIGHT --}}
                <div class="col-lg-5 ps-lg-5">
                    <p class="promo-right-text">Kami siap membantu anda kapan saja dengan pelayanan terbaik</p>
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn-wa-promo" onclick="return gtag_report_conversion('{{ $waLink }}');">
                        <div class="wa-circle">
                            <img src="{{ asset('assets/frontend/ppf/whatsapp.webp') }}" alt="WhatsApp" style="width:32px;height:32px;object-fit:contain">
                        </div>
                        <span>Hubungi Admin Kami Lewat Whatsapp</span>
                    </a>
                    <div class="promo-trust">
                        <div class="promo-trust-item">
                            <iconify-icon icon="ph:lightning-bold" width="14" height="14" style="color:#0166FF"></iconify-icon>
                            <span>Fast Respon</span>
                        </div>
                        <div class="promo-trust-sep"></div>
                        <div class="promo-trust-item">
                            <iconify-icon icon="ph:shield-check-bold" width="14" height="14" style="color:#0166FF"></iconify-icon>
                            <span>Aman & Terpercaya</span>
                        </div>
                        <div class="promo-trust-sep"></div>
                        <div class="promo-trust-item">
                            <iconify-icon icon="ph:clock-bold" width="14" height="14" style="color:#0166FF"></iconify-icon>
                            <span>Siap 24/7</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ─── FAQ ────────────────────────────────────────────────────── --}}
<section class="faq-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <div style="font-size:11px;font-weight:700;color:var(--blue);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px">TRUSTED BY THOUSANDS</div>
                    <h2 class="sec-title">Frequently <span>Asked Questions</span></h2>
                </div>
                <div class="accordion" id="faqAccordion">
                    @php
                        $faqs = [
                            ['q'=>'Workshop Acegard beralamat di mana?',
                             'a'=>'Workshop Acegard beralamat di Jl. H. Kesayihan Astral, RT.007/RW.003, Nanggeng, Kec. Cipondoh, Kota Tangerang, Banten 15141. Lokasi kami mudah dijangkau dari Jakarta, Tangerang, dan sekitarnya.'],
                            ['q'=>'Apakah PPF membuat warna mobil berubah?',
                             'a'=>'Tidak sama sekali. PPF Acegard menggunakan film ultra-bening dengan teknologi Crystal Clear yang menjaga warna asli cat mobil Anda. Tidak ada perubahan warna atau efek menguning selama masa garansi berlaku.'],
                            ['q'=>'Apakah PPF aman untuk cat mobil saya?',
                             'a'=>'Sangat aman. Lem yang digunakan adalah pressure-sensitive adhesive berkualitas tinggi yang tidak merusak cat saat dilepas. Setelah masa garansi berakhir (6–8 tahun), film dapat diganti atau dilepas tanpa merusak cat asli.'],
                            ['q'=>'Berapa lama proses pemasangan PPF?',
                             'a'=>'Tergantung paket yang dipilih. Untuk full body biasanya memerlukan 2–3 hari kerja. Partial (hood + bumper) dapat selesai dalam 1 hari. Kami memastikan pemasangan rapi dan presisi sebelum kendaraan diserahkan kembali.'],
                            ['q'=>'Apa bedanya ARMOR, ARMOR PLUS, AEGIS, dan AEGIS MAX+?',
                             'a'=>'Perbedaan utama ada pada ketebalan film, durasi garansi, dan tingkat perlindungan. ARMOR cocok untuk penggunaan harian biasa. ARMOR PLUS memberikan perlindungan lebih tebal dengan garansi 6 tahun. AEGIS dan AEGIS MAX+ adalah pilihan premium dengan garansi hingga 8 tahun dan ketebalan film terbesar — ideal untuk kendaraan mewah dan koleksi.'],
                        ];
                    @endphp
                    @foreach ($faqs as $fi => $fq)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $fi > 0 ? 'collapsed' : '' }}"
                                    type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq{{ $fi }}" aria-expanded="{{ $fi === 0 ? 'true' : 'false' }}">
                                {{ $fq['q'] }}
                            </button>
                        </h2>
                        <div id="faq{{ $fi }}"
                             class="accordion-collapse collapse {{ $fi === 0 ? 'show' : '' }}"
                             data-bs-parent="#faqAccordion">
                            <div class="accordion-body">{{ $fq['a'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ─── FOOTER ─────────────────────────────────────────────────── --}}
<footer class="lp-footer">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-6">
                <img src="{{ asset('assets/frontend/ppf/logo.webp') }}" alt="Acegard" class="f-logo mb-3">
                <p style="max-width:380px">Bergabunglah bersama kami dalam menghadirkan solusi ppf terbaik untuk kendaraan anda. Hubungi Acegard Sekarang juga.</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="soc-icon"><iconify-icon icon="ic:baseline-facebook" width="24" height="24"></iconify-icon></a>
                    <a href="#" class="soc-icon"><iconify-icon icon="mdi:instagram" width="24" height="24"></iconify-icon></a>
                </div>
            </div>
            <div class="col-lg-6">
                <h6>Contact Us</h6>
                <p>Jl. KH. Hasyim Ashari, RT.007/RW.002, Nerogtog, Kec. Cipondoh, Kota Tangerang, Banten 15146</p>
                <p><a href="mailto:acegardindonesia@gmail.com">acegardindonesia@gmail.com</a></p>
                <p><a href="{{ $waLink }}" target="_blank" rel="noopener">+6282121212093</a></p>
            </div>
        </div>
        <hr class="f-divider">
        <p class="copyright">Copyright &copy; {{ date('Y') }} Acegard Indonesia. All Rights Reserved.</p>
    </div>
</footer>

{{-- Floating WhatsApp --}}
<a href="{{ $waLink }}" target="_blank" rel="noopener" class="float-wa" aria-label="Chat WhatsApp" onclick="return gtag_report_conversion('{{ $waLink }}');">
    <img src="{{ asset('assets/frontend/ppf/whatsapp.webp') }}" alt="WhatsApp">
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
const testiSwiper = new Swiper('.testi-swiper', {
    slidesPerView: 1,
    spaceBetween: 24,
    breakpoints: {
        640:  { slidesPerView: 2 },
        1024: { slidesPerView: 3 },
    },
});
document.getElementById('testiPrev').addEventListener('click', () => testiSwiper.slidePrev());
document.getElementById('testiNext').addEventListener('click', () => testiSwiper.slideNext());

new Swiper('.gallery-swiper', {
    slidesPerView: 1.2,
    spaceBetween: 12,
    centeredSlides: true,
    loop: true,
    autoplay: { delay: 3000, disableOnInteraction: false },
    pagination: { el: '.gallery-pagination', clickable: true },
});

new Swiper('.pricing-swiper', {
    slidesPerView: 1.12,
    spaceBetween: 16,
    centeredSlides: true,
    pagination: { el: '.pricing-pagination', clickable: true },
});

(function () {
    function tick() {
        const now  = new Date();
        const end  = new Date();
        end.setHours(23, 59, 59, 999);
        const diff = Math.max(0, end - now);
        const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
        const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
        const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
        document.getElementById('cd-h').textContent = h;
        document.getElementById('cd-m').textContent = m;
        document.getElementById('cd-s').textContent = s;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>

<script>
(function () {
    const GROUPS = [
        { sel: '.trust-item',           stagger: true  },
        { sel: '.adv-main',             stagger: false },
        { sel: '.adv-stack-item',       stagger: true  },
        { sel: '.why-feature',          stagger: true  },
        { sel: '.bc-card',              stagger: true  },
        { sel: '.gallery-item',         stagger: true  },
        { sel: '.testi-card-new',       stagger: true  },
        { sel: '.pc-card',              stagger: true  },
        { sel: '.promo-tag',            stagger: false },
        { sel: '.promo-heading',        stagger: false },
        { sel: '.promo-sub',            stagger: false },
        { sel: '.promo-num',            stagger: false },
        { sel: '.btn-wa-promo',         stagger: false },
        { sel: '.promo-trust',          stagger: false },
        { sel: '.accordion-item',       stagger: true  },
        { sel: '.sec-title',            stagger: false },
        { sel: '.testi-main-title',     stagger: false },
        { sel: '.pricing-main-title',   stagger: false },
        { sel: '.lp-footer .col-lg-6',  stagger: true  },
    ];

    GROUPS.forEach(({ sel, stagger }) => {
        document.querySelectorAll(sel).forEach((el) => {
            if (el.classList.contains('reveal')) return;
            el.classList.add('reveal');
            if (stagger) {
                const siblings = Array.from(el.parentElement.children).filter(c => c.matches(sel));
                const idx = siblings.indexOf(el);
                if (idx >= 1 && idx <= 4) el.classList.add('delay-' + idx);
            }
        });
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
})();
</script>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DH3T7FD"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
</body>
</html>
