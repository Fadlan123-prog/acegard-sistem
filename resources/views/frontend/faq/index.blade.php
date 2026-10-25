@extends('frontend.layout.index')

@section('title', 'FAQ - Acegard Indonesia')

@section('content')

@php
    $waLink = fn ($text) => 'https://wa.me/6282121212093?text=' . urlencode($text);

    $lipsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';

    $makeItems = fn () => [
        ['q' => 'Kenapa harus pasang kaca film ACEGARD?', 'a' => $lipsum, 'open' => true],
        ['q' => 'Lorem ipsum dolor sit amet', 'a' => $lipsum, 'open' => false],
        ['q' => 'Lorem ipsum dolor sit amet', 'a' => $lipsum, 'open' => false],
        ['q' => 'Lorem ipsum dolor sit amet', 'a' => $lipsum, 'open' => false],
        ['q' => 'Lorem ipsum dolor sit amet', 'a' => $lipsum, 'open' => false],
        ['q' => 'Lorem ipsum dolor sit amet', 'a' => $lipsum, 'open' => false],
    ];

    $categories = [
        ['key' => 'otomotif', 'label' => 'Kaca Film Otomotif', 'items' => $makeItems()],
        ['key' => 'bangunan', 'label' => 'Kaca Film Bangunan', 'items' => $makeItems()],
        ['key' => 'ppf', 'label' => 'PPF', 'items' => $makeItems()],
    ];
@endphp

<section class="ace-section-sm text-center">
    <div class="container">
        <x-frontend.section-heading eyebrow="FAQ" highlight="Frequently Asked Questions"
            subtitle="Temukan jawaban atas pertanyaan yang paling sering diajukan seputar produk dan layanan Acegard." />
    </div>
</section>

<section class="ace-section-sm pt-0 ace-faq">
    <div class="container">
        <div class="row g-4 mb-4">
            @foreach(array_slice($categories, 0, 2) as $cat)
                <div class="col-md-6">
                    <h5 class="mb-3">{{ $cat['label'] }}</h5>
                    <div class="accordion" id="acc-{{ $cat['key'] }}">
                        @foreach($cat['items'] as $i => $item)
                            <x-frontend.faq-item
                                :id="$cat['key'] . '-' . $i"
                                parent="acc-{{ $cat['key'] }}"
                                :question="$item['q']"
                                :open="$item['open']"
                            >{{ $item['a'] }}</x-frontend.faq-item>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <h5 class="mb-3">{{ $categories[2]['label'] }}</h5>
                <div class="accordion" id="acc-{{ $categories[2]['key'] }}">
                    @foreach($categories[2]['items'] as $i => $item)
                        <x-frontend.faq-item
                            :id="$categories[2]['key'] . '-' . $i"
                            parent="acc-{{ $categories[2]['key'] }}"
                            :question="$item['q']"
                            :open="$item['open']"
                        >{{ $item['a'] }}</x-frontend.faq-item>
                    @endforeach
                </div>
            </div>

            <div class="col-md-6">
                <div class="ace-card-soft p-4">
                    <h5 class="mb-1">Butuh Bantuan?</h5>
                    <p class="ace-muted small mb-3">Lorem Ipsum dolor sit amet</p>

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

                    <div class="text-center my-3 ace-muted small">Atau</div>

                    <x-frontend.button variant="gradient" :href="$waLink('Halo Acegard, saya ingin konsultasi')" class="whatsapp-btn w-100" target="_blank">
                        Konsultasi Dengan Kami Gratis di Whatsapp
                    </x-frontend.button>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
