@props(['logos' => []])

<div class="bg-white rounded-4 p-4 shadow-sm">
  <div class="d-flex flex-wrap justify-content-center gap-4 align-items-center">
    @foreach($logos as $logo)
      <img class="partner-logo" src="{{ asset($logo['src']) }}" alt="{{ $logo['alt'] }}">
    @endforeach
  </div>
</div>
