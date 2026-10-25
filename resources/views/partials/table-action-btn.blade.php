{{--
    Reusable circular icon action button for tables.

    Props:
    - $type     : 'link' | 'submit'  (default: 'link')
    - $href     : URL for link type
    - $icon     : iconify icon name (e.g. 'lucide:edit')
    - $color    : 'primary' | 'success' | 'danger' | 'warning' | 'info' (default: 'primary')
    - $tooltip  : optional tooltip text
    - $confirm  : optional confirm message (for submit type)
    - $modal    : optional data-bs-target value (for modal trigger)
    - $extra    : optional extra HTML attributes string
--}}

@php
    $type    = $type    ?? 'link';
    $color   = $color   ?? 'primary';
    $icon    = $icon    ?? 'lucide:edit';
    $tooltip = $tooltip ?? '';
    $confirm = $confirm ?? '';
    $modal   = $modal   ?? '';
    $extra   = $extra   ?? '';

    $colorMap = [
        'primary' => 'bg-primary-light text-primary-600',
        'success' => 'bg-success-focus text-success-main',
        'danger'  => 'bg-danger-focus text-danger-main',
        'warning' => 'bg-warning-focus text-warning-600',
        'info'    => 'bg-info-focus text-info-main',
    ];

    $classes = 'w-32-px h-32-px rounded-circle d-inline-flex align-items-center justify-content-center '
        . ($colorMap[$color] ?? $colorMap['primary']);
@endphp

@if ($type === 'submit')
    <form action="{{ $href }}" method="POST"
          @if($confirm) onsubmit="return confirm('{{ $confirm }}')" @endif
          class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="{{ $classes }} border-0"
                @if($tooltip) title="{{ $tooltip }}" @endif>
            <iconify-icon icon="{{ $icon }}"></iconify-icon>
        </button>
    </form>
@elseif ($modal)
    <a href="javascript:void(0)" class="{{ $classes }}"
       data-bs-toggle="modal" data-bs-target="{{ $modal }}"
       @if($tooltip) title="{{ $tooltip }}" @endif
       {!! $extra !!}>
        <iconify-icon icon="{{ $icon }}"></iconify-icon>
    </a>
@else
    <a href="{{ $href ?? 'javascript:void(0)' }}" class="{{ $classes }}"
       @if($tooltip) title="{{ $tooltip }}" @endif
       {!! $extra !!}>
        <iconify-icon icon="{{ $icon }}"></iconify-icon>
    </a>
@endif
