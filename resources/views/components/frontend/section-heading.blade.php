@props([
    'title' => null,
    'highlight' => null,
    'subtitle' => null,
    'align' => 'center',
    'eyebrow' => null,
])

<div @class([
    'mb-4',
    'text-center' => $align === 'center',
    'text-start' => $align === 'left',
])>
    @if($eyebrow)
        <div class="ace-muted small fw-semibold text-uppercase mb-1">{{ $eyebrow }}</div>
    @endif

    <h2 class="fw-normal mb-2">
        @if($title){{ $title }} @endif
        @if($highlight)<span class="ace-gradient-text">{{ $highlight }}</span>@endif
    </h2>

    @if($subtitle)
        <p class="ace-muted mb-0">{{ $subtitle }}</p>
    @endif
</div>
