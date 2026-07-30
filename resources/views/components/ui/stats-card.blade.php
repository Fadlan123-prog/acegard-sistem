<div {{ $attributes->merge(['class' => 'ui-stats-card']) }}>

    @if($icon)
        <div class="ui-stats-icon {{ $iconColorClass }}" aria-hidden="true">
            {!! $icon !!}
        </div>
    @endif

    <div class="ui-stats-body">
        <div class="ui-stats-label">{{ $label }}</div>
        <div class="ui-stats-value">{{ $value }}</div>

        @if($trend)
            <span class="{{ $trendClass }}">
                @if($trendDirection === 'up')
                    <svg viewBox="0 0 10 10" fill="none" aria-hidden="true">
                        <path d="M2 7l3-3 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @elseif($trendDirection === 'down')
                    <svg viewBox="0 0 10 10" fill="none" aria-hidden="true">
                        <path d="M2 3l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @endif
                {{ $trend }}
            </span>
        @endif

        @if($slot->isNotEmpty())
            {{ $slot }}
        @endif
    </div>

</div>
