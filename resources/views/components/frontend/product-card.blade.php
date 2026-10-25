@props([
    'image',
    'imageAlt' => '',
    'badge' => null,
    'title' => null,
    'desc' => null,
])

<div {{ $attributes }}>
    <div class="position-relative rounded-3 overflow-hidden mb-3" style="aspect-ratio: 16/10;">
        <img src="{{ $image }}" alt="{{ $imageAlt }}" class="w-100 h-100" style="object-fit: cover;">
        @if($badge)
            <span class="position-absolute bottom-0 start-0 m-3 text-white fw-semibold">{{ $badge }}</span>
        @endif
    </div>

    @if($title)
        <div class="fw-semibold mb-1">{{ $title }}</div>
    @endif

    @if($desc)
        <p class="ace-muted small mb-0">{{ $desc }}</p>
    @endif
</div>
