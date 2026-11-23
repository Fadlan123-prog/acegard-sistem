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
        @php $vid = ! empty($r['video']) && file_exists(public_path('assets/frontend/ag/video/' . $r['video'])); @endphp
        <div class="ag-reel ag-reveal">
          @if ($vid)
            <video class="ag-reel-video" muted loop playsinline preload="metadata" aria-label="{{ $r['title'] }}"
              @if (! empty($r['poster'])) poster="{{ asset('assets/frontend/ag/' . $r['poster']) }}" @endif
              src="{{ asset('assets/frontend/ag/video/' . $r['video']) }}"></video>
            <button type="button" class="ag-reel-sound" aria-label="Nyalakan suara" aria-pressed="false"><iconify-icon icon="mdi:volume-off" width="20" aria-hidden="true"></iconify-icon></button>
            <a class="ag-reel-ig" href="https://www.instagram.com/reel/{{ $r['id'] }}/" target="_blank" rel="noopener"><iconify-icon icon="mdi:instagram" width="16" aria-hidden="true"></iconify-icon> Instagram</a>
          @else
          <button type="button" class="ag-reel-play" data-reel="{{ $r['id'] }}" aria-label="Putar video: {{ $r['title'] }}">
            <iconify-icon icon="mdi:instagram" width="28" aria-hidden="true"></iconify-icon>
            <span class="ag-reel-btn"><iconify-icon icon="mdi:play" width="28" aria-hidden="true"></iconify-icon></span>
            <span class="ag-reel-t">{{ $r['title'] }}</span>
            <small>Tonton di Instagram</small>
          </button>
          <noscript><a href="https://www.instagram.com/reel/{{ $r['id'] }}/" target="_blank" rel="noopener">{{ $r['title'] }}</a></noscript>
          @endif
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
  var vids = wrap.querySelectorAll('video');
  vids.forEach(function (v) {
    var snd = v.parentNode.querySelector('.ag-reel-sound');
    if (snd) snd.addEventListener('click', function () {
      v.muted = !v.muted; snd.setAttribute('aria-pressed', v.muted ? 'false' : 'true');
      snd.firstElementChild.setAttribute('icon', v.muted ? 'mdi:volume-off' : 'mdi:volume-high');
    });
  });
  var VObs = window.IntersectionObserver;
  if (vids.length && VObs) {
    var vio = new VObs(function (es) {
      es.forEach(function (e) {
        if (e.isIntersecting) { var p = e.target.play(); if (p && p.catch) p.catch(function () {}); }
        else e.target.pause();
      });
    }, { threshold: 0.35 });
    vids.forEach(function (v) { vio.observe(v); });
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
