<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class StatsCard extends Component
{
    public string $iconColorClass;
    public string $trendClass;
    public string $accentClass;

    public function __construct(
        public string  $label          = '',
        public string  $value          = '',
        public ?string $trend          = null,
        public string  $trendDirection = 'neutral',
        public ?string $icon           = null,
        public string  $color          = 'blue',
        public bool    $accent         = false,
    ) {
        $this->iconColorClass = match ($color) {
            'green'  => 'si-green',
            'amber'  => 'si-amber',
            'red'    => 'si-red',
            'purple' => 'si-purple',
            default  => 'si-blue',
        };

        $this->trendClass = match ($trendDirection) {
            'up'   => 'ui-stats-trend up',
            'down' => 'ui-stats-trend down',
            default => 'ui-stats-trend',
        };

        $this->accentClass = $accent ? match ($color) {
            'green'  => 'ui-stats-accent-green',
            'amber'  => 'ui-stats-accent-amber',
            'red'    => 'ui-stats-accent-red',
            'purple' => 'ui-stats-accent-purple',
            default  => 'ui-stats-accent-blue',
        } : '';
    }

    public function render()
    {
        return view('components.ui.stats-card');
    }
}
