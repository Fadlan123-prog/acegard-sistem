@extends('frontend.layout.index')

@section('title', 'Paint Protection Film (PPF) - Acegard Indonesia')

@section('content')

@php
    $waLink = fn ($text) => 'https://wa.me/6282121212093?text=' . urlencode($text);

    $tiers = [
        ['name' => 'ARMOR', 'rowClass' => 'ace-tier-navy'],
        ['name' => 'ARMOR PLUS', 'rowClass' => 'ace-tier-navy'],
        ['name' => 'AEGIS', 'rowClass' => 'ace-tier-gold'],
        ['name' => 'AEGIS MAX +', 'rowClass' => 'ace-tier-gold'],
        ['name' => 'MATTE', 'rowClass' => 'ace-tier-dark'],
    ];

    $specCells = ['Heat Self Healing', '5 Tahun', 'Lubrizol Mix Grade', '8.5 Mils'];
@endphp

{{-- Hero --}}
<section class="ace-section-sm text-center">
    <div class="container" style="max-width: 760px;">
        <h1 class="fw-normal mb-3">
            <span class="ace-gradient-text">ACEGARD</span> Paint Protection Film
        </h1>
        <p class="ace-muted mb-4">
            Mobil lebih aman, berkendara lebih tenang. Lindungi cat kendaraan Anda dengan teknologi terdepan ACEGARD.
        </p>
        <x-frontend.button variant="gradient" :href="$waLink('Halo Acegard, saya ingin konsultasi PPF')" class="whatsapp-btn" target="_blank">
            Konsultasi Dengan Kami Gratis di Whatsapp
        </x-frontend.button>
    </div>
</section>

{{-- Tier tables --}}
@foreach($tiers as $tier)
    <section class="ace-section-sm py-4">
        <div class="container" style="max-width: 900px;">
            <x-frontend.section-heading :highlight="$tier['name']" />

            <x-frontend.spec-table
                :headers="['Self Healing Rate', 'Garansi', 'Urethane Original', 'Produck Thickness']"
                :rows="[['cells' => $specCells, 'class' => $tier['rowClass']]]"
                :cta="['label' => 'Hubungi Kami', 'href' => $waLink('Halo Acegard, saya tertarik dengan PPF ' . $tier['name']), 'whatsapp' => true]"
            />
        </div>
    </section>
@endforeach

@endsection
