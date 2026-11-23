@php
  use App\Support\SiteContent;
  $reels = SiteContent::celebrityReels();
  $auto = $auto ?? false;
@endphp
@if (count($reels))
<section class="ag-section {{ ($alt ?? false) ? 'alt' : '' }}" id="artis" data-sec="Artis" aria-labelledby="ag-reel-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Dipercaya Para Artis</span>
      <h2 class="ag-h2" id="ag-reel-title">Hasil pemasangan <b>untuk artis</b></h2>
      <p class="ag-lead">Video pengerjaan langsung di ACEGARD, dari akun Instagram <a href="https://www.instagram.com/acegard.id/" target="_blank" rel="noopener">@acegard.id</a>.</p>
    </div>
    <div class="ag-reels" id="agReels" data-auto="{{ $auto ? '1' : '0' }}">
      @foreach ($reels as $r)
        <div class="ag-reel ag-reveal">
          <button type="button" class="ag-reel-play" data-reel="{{ $r['id'] }}" aria-label="Putar video: {{ $r['title'] }}">
            <iconify-icon icon="mdi:instagram" width="28" aria-hidden="true"></iconify-icon>
            <span class="ag-reel-btn"><iconify-icon icon="mdi:play" width="28" aria-hidden="true"></iconify-icon></span>
            <span class="ag-reel-t">{{ $r['title'] }}</span>
            <small>Tonton di Instagram</small>
          </button>
          <noscript><a href="https://www.instagram.com/reel/{{ $r['id'] }}/" target="_blank" rel="noopener">{{ $r['title'] }}</a></noscript>
        </div>
      @endforeach
    </div>
  </div>
</section>
@push('scripts')
<script>
(function () {
  var wrap = document.getElementById('agReels');
  if (!wrap) return;
  function load(b) {
    if (!b.parentNode) return;
    var f = document.createElement('iframe');
    f.src = 'https://www.instagram.com/reel/' + b.getAttribute('data-reel') + '/embed/';
    f.setAttribute('allowfullscreen', ''); f.title = b.getAttribute('aria-label');
    f.setAttribute('allow', 'autoplay; encrypted-media; fullscreen');
    b.replaceWith(f);
  }
  var btns = wrap.querySelectorAll('.ag-reel-play');
  btns.forEach(function (b) { b.addEventListener('click', function () { load(b); }); });
  var Obs = window.IntersectionObserver;
  if (wrap.getAttribute('data-auto') === '1' && Obs) {
    var io = new Obs(function (es) {
      es.forEach(function (e) {
        if (e.isIntersecting) { io.disconnect(); btns.forEach(load); }
      });
    }, { rootMargin: '0px 0px -20% 0px', threshold: 0.15 });
    io.observe(wrap);
  }
})();
</script>
@endpush
@endif
