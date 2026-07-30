@php
    $alertId = 'alert-'.uniqid();
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}
     role="alert"
     id="{{ $alertId }}"
>
    {!! $iconSvg !!}

    <div class="ui-alert-body">
        @if($title)
            <div class="ui-alert-title">{{ $title }}</div>
        @endif
        <div class="ui-alert-msg">{{ $slot }}</div>
    </div>

    @if($dismissible)
        <button
            class="ui-alert-close"
            aria-label="Tutup notifikasi"
            onclick="
                var el = document.getElementById('{{ $alertId }}');
                el.style.transition = 'opacity .2s, transform .2s';
                el.style.opacity = '0';
                el.style.transform = 'translateX(6px)';
                setTimeout(function(){ el.remove(); }, 200);
            "
        >
            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>
    @endif
</div>
