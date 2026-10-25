@props([
    'icon' => 'mdi:check-circle-outline',
    'title',
    'desc' => null,
    'href' => null,
    'arrow' => false,       // tampilkan panah dekoratif top-right walau tanpa href
    'titleClass' => 'ace-navy-text',
])

<div {{ $attributes->class(['ace-card-soft p-4 h-100 position-relative']) }}>
    @if($href)
        <a href="{{ $href }}" class="position-absolute top-0 end-0 m-3 ace-muted" aria-label="Selengkapnya">
            <iconify-icon icon="mdi:arrow-top-right"></iconify-icon>
        </a>
    @elseif($arrow)
        <span class="position-absolute top-0 end-0 m-3 ace-muted">
            <iconify-icon icon="mdi:arrow-top-right"></iconify-icon>
        </span>
    @endif

    <div class="fs-3 mb-3 ace-navy-text">
        <iconify-icon icon="{{ $icon }}"></iconify-icon>
    </div>

    <div @class(['fw-semibold mb-2', $titleClass])>{{ $title }}</div>

    @if($desc)
        <p class="ace-muted small mb-0">{{ $desc }}</p>
    @endif
</div>
