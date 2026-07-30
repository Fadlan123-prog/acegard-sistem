<?php

namespace App\View\Components\Ui;

use Illuminate\View\Component;

class EmptyState extends Component
{
    public function __construct(
        public string  $title       = 'Belum ada data',
        public string  $description = '',
        public ?string $icon        = null,
    ) {}

    public function render()
    {
        return view('components.ui.empty-state');
    }
}
