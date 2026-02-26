@props(['title', 'subtitle' => null])

<div class="text-center mb-4">
  <h2 class="fw-bold">{{ $title }}</h2>
  @if($subtitle)
    <p class="text-secondary mb-0">{{ $subtitle }}</p>
  @endif
</div>
