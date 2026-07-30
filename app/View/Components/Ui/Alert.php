<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class Alert extends Component
{
    public string $classes;
    public string $iconSvg;

    public function __construct(
        public string  $type        = 'info',
        public ?string $title       = null,
        public bool    $dismissible = true,
    ) {
        $this->classes = match ($type) {
            'success' => 'ui-alert ui-alert-success',
            'warning' => 'ui-alert ui-alert-warning',
            'danger'  => 'ui-alert ui-alert-danger',
            default   => 'ui-alert ui-alert-info',
        };

        $this->iconSvg = match ($type) {
            'success' => '<svg class="ui-alert-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/><path d="M5.5 8l2 2 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'warning' => '<svg class="ui-alert-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 2L1.5 13h13L8 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M8 6.5v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="8" cy="11" r=".75" fill="currentColor"/></svg>',
            'danger'  => '<svg class="ui-alert-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/><path d="M5.5 5.5l5 5M10.5 5.5l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            default   => '<svg class="ui-alert-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/><path d="M8 7.5V11M8 5.5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
        };
    }

    public function render()
    {
        return view('components.ui.alert');
    }
}
