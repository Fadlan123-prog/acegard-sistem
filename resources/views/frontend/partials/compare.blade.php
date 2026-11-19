{{--
  Alat perbandingan produk. Variabel:
  $cmp      data dari FilmCatalog::compare*() (items, metrics, defaults)
  $cmpKey   id unik section (mis. "car")
  $cmpTitle judul (boleh memuat <b>)
  $cmpNoun  kata benda: "varian" / "tingkat"
  $cmpWa    awalan pesan WhatsApp
  $cmpAlt   true untuk latar abu muda
--}}
@php
  use App\Support\Whatsapp;
  $uid = 'agCmp-' . $cmpKey;
  $groups = collect($cmp['items'])->groupBy('group');
  $maxPick = 3;
@endphp
<section class="ag-section {{ !empty($cmpAlt) ? 'alt' : '' }}" id="bandingkan-{{ $cmpKey }}" aria-labelledby="{{ $uid }}-title">
  <div class="ag-container">
    <div class="ag-head ag-reveal">
      <span class="ag-eyebrow">Compare</span>
      <h2 class="ag-h2" id="{{ $uid }}-title">{!! $cmpTitle !!}</h2>
      <p class="ag-lead">Pilih 2 sampai {{ $maxPick }} {{ $cmpNoun }} untuk dibandingkan berdampingan. Nilai terbaik pada tiap baris ditandai.</p>
    </div>

    <div class="ag-cmp" id="{{ $uid }}" data-wa="{{ Whatsapp::url() }}" data-wa-prefix="{{ $cmpWa }}" data-max="{{ $maxPick }}">
      <div class="ag-cmp-pick">
        @foreach ($groups as $group => $list)
          <fieldset class="ag-sim-group">
            @if ($groups->count() > 1)<legend>{{ $group }}</legend>@else<legend>Pilih {{ $cmpNoun }}</legend>@endif
            <div class="ag-chips is-live">
              @foreach ($list as $it)
                <label class="ag-chip"><input type="checkbox" value="{{ $it['id'] }}" class="visually-hidden" {{ in_array($it['id'], $cmp['defaults']) ? 'checked' : '' }}><span>{{ $it['name'] }}</span></label>
              @endforeach
            </div>
          </fieldset>
        @endforeach
        <p class="ag-cmp-count" aria-live="polite"></p>
      </div>

      <div class="ag-spec-wrap" tabindex="0" role="region" aria-label="Hasil perbandingan">
        <table class="ag-spec ag-cmp-table">
          <caption class="visually-hidden">Perbandingan {{ $cmpNoun }} yang dipilih</caption>
          <thead></thead>
          <tbody></tbody>
        </table>
      </div>

      <p class="ag-center" style="margin-top:24px">
        <a class="ag-btn ag-btn-primary whatsapp-btn" data-gtm-label="compare-{{ $cmpKey }}-whatsapp" target="_blank" rel="noopener" data-cmp-wa
           href="{{ Whatsapp::url($cmpWa) }}"><iconify-icon icon="mdi:whatsapp" width="20" aria-hidden="true"></iconify-icon> Tanyakan perbandingan ini</a>
      </p>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  var root = document.getElementById(@json($uid));
  if (!root) return;
  var DATA = @json($cmp);
  var byId = {}; DATA.items.forEach(function (i) { byId[i.id] = i; });
  var MAX = Number(root.getAttribute('data-max'));
  var head = root.querySelector('thead'), body = root.querySelector('tbody'), count = root.querySelector('.ag-cmp-count');
  var esc = function (t) { return String(t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };

  function selected() {
    return Array.prototype.map.call(root.querySelectorAll('.ag-cmp-pick input:checked'), function (c) { return byId[c.value]; });
  }

  function render() {
    var sel = selected();
    var boxes = root.querySelectorAll('.ag-cmp-pick input');
    boxes.forEach(function (b) {
      b.disabled = !b.checked && sel.length >= MAX;
      b.closest('.ag-chip').classList.toggle('is-on', b.checked);
      b.closest('.ag-chip').classList.toggle('is-off', b.disabled);
    });
    count.textContent = sel.length < 2
      ? 'Pilih minimal 2 untuk membandingkan.'
      : sel.length + ' dari ' + MAX + ' dipilih.';

    head.innerHTML = '<tr><th scope="col">Spesifikasi</th>' + sel.map(function (i) {
      return '<th scope="col">' + esc(i.name) + (DATA.items.some(function (x) { return x.group !== i.group; }) ? '<small>' + esc(i.group) + '</small>' : '') + '</th>';
    }).join('') + '</tr>';

    body.innerHTML = DATA.metrics.map(function (m) {
      var vals = sel.map(function (i) { return i.cells[m.key]; });
      var nums = vals.map(function (v) { return v && v.n !== null ? v.n : null; }).filter(function (n) { return n !== null; });
      var best = null;
      if (m.better && sel.length > 1 && nums.length === vals.length && Math.max.apply(null, nums) !== Math.min.apply(null, nums)) {
        best = m.better === 'high' ? Math.max.apply(null, nums) : Math.min.apply(null, nums);
      }
      var scale = m.max || Math.max.apply(null, DATA.items.map(function (i) { return (i.cells[m.key] && i.cells[m.key].n) || 0; })) || 1;
      return '<tr><th scope="row">' + esc(m.label) + '</th>' + vals.map(function (v) {
        if (!v) return '<td>-</td>';
        var isBest = best !== null && v.n === best;
        var bar = (!m.text && !m.stars && v.n !== null)
          ? '<span class="ag-bar"><i style="--w:' + Math.min(100, v.n / scale * 100).toFixed(1) + '%"></i></span>' : '';
        var txt = m.stars ? '<span class="ag-stars"><span class="on">' + esc(v.t) + '</span><span class="off">' + '★'.repeat(5 - v.n) + '</span></span>' : esc(v.t);
        return '<td class="' + (isBest ? 'is-best' : '') + '"><b>' + txt + '</b>' + (isBest ? '<em>Terbaik</em>' : '') + bar + '</td>';
      }).join('') + '</tr>';
    }).join('');

    var names = sel.map(function (i) { return i.name; }).join(', ');
    root.querySelector('[data-cmp-wa]').href = root.getAttribute('data-wa') + '?text=' +
      encodeURIComponent(root.getAttribute('data-wa-prefix') + (names ? ' Saya ingin membandingkan: ' + names + '.' : ''));
  }

  root.addEventListener('change', render);
  render();
})();
</script>
@endpush
