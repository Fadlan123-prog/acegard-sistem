<div {{ $attributes->merge(['class' => 'ui-empty']) }}>

    <div class="ui-empty-icon" aria-hidden="true">
        @if($icon)
            {!! $icon !!}
        @else
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        @endif
    </div>

    <div class="ui-empty-title">{{ $title }}</div>

    @if($description)
        <div class="ui-empty-desc">{{ $description }}</div>
    @endif

    @if($slot->isNotEmpty())
        <div class="ui-empty-action">
            {{ $slot }}
        </div>
    @endif

</div>
