@props(['title', 'desc', 'icon' => '✅'])

<div class="card card-feature p-4">
  <div class="d-flex gap-3 align-items-start">
    <div class="icon-pill">{{ $icon }}</div>
    <div>
      <div class="fw-bold">{{ $title }}</div>
      <div class="small text-secondary">{{ $desc }}</div>
    </div>
  </div>
</div>
