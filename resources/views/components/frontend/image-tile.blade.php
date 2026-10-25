@props([
    'image',
    'imageAlt' => '',
    'title',
    'href',
    'ctaLabel' => 'Selengkapnya',
    'height' => '480px',
])

<div {{ $attributes->class(['position-relative rounded-4 overflow-hidden']) }} style="min-height: {{ $height }};">
    <img src="{{ $image }}" alt="{{ $imageAlt }}" class="w-100 h-100 position-absolute top-0 start-0" style="object-fit: cover;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(2,33,78,0) 45%, rgba(2,33,78,.8) 100%);"></div>

    <div class="position-relative h-100 d-flex flex-column justify-content-end p-4" style="min-height: {{ $height }};">
        <h3 class="text-white fw-normal mb-3">{{ $title }}</h3>
        <x-frontend.button variant="outline" :href="$href" class="bg-white" style="width: fit-content;">
            {{ $ctaLabel }}
        </x-frontend.button>
    </div>
</div>
