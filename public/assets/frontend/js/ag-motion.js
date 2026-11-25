/*
 * ACEGARD motion: GSAP + ScrollTrigger, progressive enhancement.
 *
 * - Tanpa JavaScript atau tanpa GSAP, semua konten tetap tampil (lihat ag-motion.css).
 * - prefers-reduced-motion dan tombol "Animasi: mati" di footer menonaktifkan semua gerak.
 * - Pin dan scroll horizontal hanya di desktop (>= 992px). Di mobile: sticky/stack dan scroll-snap native.
 *
 * Atribut:
 *   [data-sec="Judul"]   section diberi nomor dan masuk indeks navigasi samping
 *   [data-statement]     teks besar yang menyala per kata mengikuti scroll
 *   [data-ps]            layanan dengan visual sticky (desktop) dan konten yang berganti
 *   [data-hs]            expertise: pin + geser horizontal (desktop), swipe native (mobile)
 *   [data-story]         cerita produk 3 tahap dengan visual sticky (desktop)
 *   [data-count]         angka yang menghitung naik (dipasang otomatis pada .ag-stats dd)
 */
(function () {
  'use strict';

  var root = document.documentElement;
  var $ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function release() { root.classList.remove('ag-m-wait'); }

  /* ───────── Bagian ringan, tanpa GSAP: navigasi, progres, anchor, nomor ───────── */
  function initBasics() {
    var nav = document.querySelector('.ag-nav');

    // Bar progres scroll
    var bar = document.createElement('div');
    bar.className = 'ag-progress'; bar.setAttribute('aria-hidden', 'true');
    bar.innerHTML = '<i></i>';
    document.body.appendChild(bar);
    var fill = bar.firstChild, ticking = false;

    function onScroll() {
      ticking = false;
      var y = window.pageYOffset || root.scrollTop;
      var max = Math.max(1, root.scrollHeight - window.innerHeight);
      fill.style.transform = 'scaleX(' + Math.min(1, y / max).toFixed(4) + ')';
      if (nav) nav.classList.toggle('is-scrolled', y > 24);
    }
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
    onScroll();

    // Anchor halus dengan offset navbar
    document.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('a[href^="#"]');
      if (!a || a.getAttribute('href') === '#' || e.defaultPrevented) return;
      var el = null;
      try { el = document.querySelector(a.getAttribute('href')); } catch (err) { return; }
      if (!el) return;
      e.preventDefault();
      var offset = (nav ? nav.getBoundingClientRect().height : 0) + 12;
      var top = el.getBoundingClientRect().top + (window.pageYOffset || root.scrollTop) - offset;
      window.scrollTo({ top: Math.max(0, top), behavior: reduce ? 'auto' : 'smooth' });
      if (history.replaceState) history.replaceState(null, '', a.getAttribute('href'));
    });

    // Nomor section pada eyebrow
    $('[data-sec]').forEach(function (sec, i) {
      var eb = sec.querySelector('.ag-eyebrow');
      if (eb) eb.setAttribute('data-num', ('0' + (i + 1)).slice(-2));
    });
  }

  /* ───────── Indeks section di samping (desktop) ───────── */
  function initIndex() {
    var secs = $('[data-sec]');
    if (secs.length < 3) return;
    var nav = document.createElement('nav');
    nav.className = 'ag-index'; nav.setAttribute('aria-label', 'Navigasi section');
    var html = '<ol>';
    secs.forEach(function (s, i) {
      if (!s.id) s.id = 'sec-' + (i + 1);
      var n = ('0' + (i + 1)).slice(-2);
      html += '<li><a href="#' + s.id + '"><span class="n">' + n + '</span><span class="t">' + s.getAttribute('data-sec') + '</span></a></li>';
    });
    nav.innerHTML = html + '</ol>';
    document.body.appendChild(nav);
    var links = $('a', nav);

    function setActive(i) {
      links.forEach(function (l, k) { l.classList.toggle('is-active', k === i); if (k === i) l.setAttribute('aria-current', 'true'); else l.removeAttribute('aria-current'); });
      nav.classList.add('is-visible');
    }
    secs.forEach(function (s, i) {
      ScrollTrigger.create({
        trigger: s, start: 'top 55%', end: 'bottom 55%',
        onToggle: function (self) { if (self.isActive) setActive(i); }
      });
    });
    // Sembunyikan di hero dan setelah section terakhir
    ScrollTrigger.create({
      trigger: secs[0], start: 'top 80%', endTrigger: secs[secs.length - 1], end: 'bottom 40%',
      onToggle: function (self) { nav.classList.toggle('is-visible', self.isActive); }
    });
  }

  /* ───────── Pecah teks: kata atau huruf, dengan topeng (mask) ───────── */
  function split(el, mode) {
    if (el.getAttribute('data-split')) return $('.wi', el);
    var label = el.textContent.replace(/\s+/g, ' ').trim();
    el.setAttribute('data-split', mode || 'words');
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (n) {
        if (n.nodeType === 3) {
          var frag = document.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach(function (p) {
            if (!p) return;
            if (/^\s+$/.test(p)) { frag.appendChild(document.createTextNode(' ')); return; }
            var w = document.createElement('span'); w.className = 'w'; w.setAttribute('aria-hidden', 'true');
            if (mode === 'chars') {
              Array.prototype.slice.call(p).forEach(function (ch) {
                var c = document.createElement('span'); c.className = 'wi'; c.textContent = ch; w.appendChild(c);
              });
            } else {
              var wi = document.createElement('span'); wi.className = 'wi'; wi.textContent = p; w.appendChild(wi);
            }
            frag.appendChild(w);
          });
          node.replaceChild(frag, n);
        } else if (n.nodeType === 1 && !/^(BR|SCRIPT|STYLE|IMG|SVG|IFRAME)$/i.test(n.tagName)) { walk(n); }
      });
    })(el);
    el.setAttribute('aria-label', label);
    return $('.wi', el);
  }

  /* ───────── Hero ───────── */
  function initHero(mm) {
    $('.ag-hero').forEach(function (hero) {
      var title = hero.querySelector('.ag-display');
      var container = hero.querySelector('.ag-container');
      var car = hero.querySelector('.ag-hero-car');
      var parts = $('.ag-hero-tag, .ag-crumbs, .ag-hero-sub, .ag-hero-actions, .ag-marquee', hero);
      var isHome = !hero.classList.contains('ag-hero-sub-page');
      var units = title ? split(title, isHome && window.innerWidth >= 768 ? 'chars' : 'words') : [];

      var tl = gsap.timeline({ defaults: { ease: 'power3.out' }, delay: 0.1 });
      var tag = hero.querySelector('.ag-hero-tag'), crumbs = hero.querySelector('.ag-crumbs');
      if (crumbs) tl.from(crumbs, { opacity: 0, y: 12, duration: 0.6 }, 0);
      if (tag) tl.from(tag, { opacity: 0, y: 16, duration: 0.7 }, 0.05);
      if (units.length) tl.from(units, { yPercent: 118, rotate: 2, duration: 1.05, ease: 'power4.out', stagger: { each: units.length > 40 ? 0.018 : 0.07 } }, 0.2);
      var sub = hero.querySelector('.ag-hero-sub'), actions = hero.querySelector('.ag-hero-actions');
      var after = units.length ? '>-0.45' : 0.3;
      if (sub) tl.from(sub, { opacity: 0, y: 22, duration: 0.8 }, after);
      if (actions) tl.from(actions.children, { opacity: 0, y: 22, duration: 0.7, stagger: 0.1 }, '>-0.5');
      if (car) {
        var img = car.querySelector('img');
        tl.from(car, { opacity: 0, clipPath: 'inset(14% 10% 22% 10% round 48px)', duration: 1.5, ease: 'power3.inOut', clearProps: 'clipPath,opacity' }, 0.3);
        if (img) tl.from(img, { scale: 1.18, duration: 1.8, ease: 'power3.out' }, 0.3);
      }
      var marquee = hero.querySelector('.ag-marquee');
      if (marquee) tl.from(marquee, { opacity: 0, duration: 0.8 }, '>-0.6');

      // Transisi saat mulai scroll: teks naik dan memudar, mobil bergerak lebih lambat.
      // Wajib fromTo + immediateRender:false: nilai awal eksplisit, supaya scroll kembali ke atas
      // memulihkan keadaan penuh dan tidak bentrok dengan animasi pembuka di atas.
      mm.add('(min-width: 768px)', function () {
        var texts = container ? Array.prototype.slice.call(container.children).filter(function (c) { return c !== car; }) : [];
        if (texts.length) {
          gsap.fromTo(texts, { yPercent: 0, opacity: 1 }, { yPercent: -6, opacity: 0.35, ease: 'none', immediateRender: false, scrollTrigger: { trigger: hero, start: 'top top', end: '60% top', scrub: true } });
        }
        if (car) {
          var im = car.querySelector('img');
          gsap.fromTo(car, { yPercent: 0 }, { yPercent: 9, ease: 'none', immediateRender: false, scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });
          if (im) gsap.fromTo(im, { scale: 1 }, { scale: 1.07, ease: 'none', immediateRender: false, scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });
        }
      });
      // reveal marquee tidak boleh ikut memudar di mobile
      void parts;
    });
  }

  /* ───────── Judul section: kata muncul bergantian ───────── */
  function initHeadings() {
    $('.ag-head').forEach(function (head) {
      if (head.closest('.ag-hero')) return;
      head.classList.add('is-visible'); // animasi diatur di sini, bukan oleh pengamat reveal
      var h = head.querySelector('.ag-h2');
      var others = Array.prototype.slice.call(head.children).filter(function (c) { return c !== h; });
      var units = h ? split(h, 'words') : [];
      gsap.set(units, { yPercent: 118 });
      gsap.set(others, { opacity: 0, y: 18 });
      ScrollTrigger.create({
        trigger: head, start: 'top 86%', once: true,
        onEnter: function () {
          var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
          var eb = head.querySelector('.ag-eyebrow');
          if (eb) tl.to(eb, { opacity: 1, y: 0, duration: 0.6 }, 0);
          if (units.length) tl.to(units, { yPercent: 0, duration: 0.95, ease: 'power4.out', stagger: 0.055 }, 0.05);
          var rest = others.filter(function (o) { return o !== eb; });
          if (rest.length) tl.to(rest, { opacity: 1, y: 0, duration: 0.8, stagger: 0.08 }, '>-0.55');
        }
      });
    });

    // judul CTA dan judul di dalam split
    $('.ag-cta-panel h2').forEach(function (h) {
      var units = split(h, 'words');
      gsap.set(units, { yPercent: 118 });
      ScrollTrigger.create({ trigger: h, start: 'top 88%', once: true, onEnter: function () { gsap.to(units, { yPercent: 0, duration: 0.9, ease: 'power4.out', stagger: 0.05 }); } });
    });
  }

  /* ───────── Teks pernyataan besar: menyala per kata mengikuti scroll ───────── */
  function initStatements() {
    $('[data-statement]').forEach(function (el) {
      var units = split(el, 'words');
      units.forEach(function (u) { u.setAttribute('data-bold', u.closest('b') ? '1' : ''); });
      gsap.set(units, { color: '#C9D6EC' });
      var tl = gsap.timeline({ scrollTrigger: { trigger: el, start: 'top 78%', end: 'bottom 52%', scrub: 0.6 } });
      units.forEach(function (u, i) {
        tl.to(u, { color: u.getAttribute('data-bold') ? '#155EEF' : '#123B73', duration: 1, ease: 'none' }, i * 0.35);
      });
    });
  }

  /* ───────── Gambar: clip-path reveal + parallax ───────── */
  function initImages(mm) {
    $('.ag-shot, .ag-gallery figure').forEach(function (fig) {
      var img = fig.querySelector('img');
      if (!img || fig.closest('[data-ps],[data-hs],[data-story]')) return;
      gsap.set(fig, { clipPath: 'inset(12% 9% 12% 9% round 28px)' });
      gsap.set(img, { scale: 1.28 });
      ScrollTrigger.create({
        trigger: fig, start: 'top 90%', once: true,
        onEnter: function () {
          gsap.to(fig, { clipPath: 'inset(0% 0% 0% 0% round 20px)', duration: 1.25, ease: 'power3.out', clearProps: 'clipPath' });
          gsap.to(img, { scale: 1.1, duration: 1.7, ease: 'power3.out' });
        }
      });
    });
    mm.add('(min-width: 992px)', function () {
      $('.ag-shot, .ag-gallery figure').forEach(function (fig) {
        var img = fig.querySelector('img');
        if (!img || fig.closest('[data-ps],[data-hs],[data-story]')) return;
        gsap.fromTo(img, { yPercent: -5 }, { yPercent: 5, ease: 'none', scrollTrigger: { trigger: fig, start: 'top bottom', end: 'bottom top', scrub: true } });
      });
    });
  }

  /* ───────── Transisi antar-section: latar abu/biru muda tumbuh dari kartu membulat ───────── */
  function initSections(mm) {
    $('.ag-section.alt, .ag-section.tint').forEach(function (sec) {
      if (sec.hasAttribute('data-ps') || sec.hasAttribute('data-hs') || sec.hasAttribute('data-story')) return;
      var small = window.innerWidth < 768;
      gsap.set(sec, { clipPath: small ? 'inset(18px 10px 0px 10px round 28px 28px 0 0)' : 'inset(48px 3.5vw 0px 3.5vw round 48px 48px 0 0)' });
      ScrollTrigger.create({
        trigger: sec, start: 'top 94%', once: true,
        onEnter: function () { gsap.to(sec, { clipPath: 'inset(0px 0vw 0px 0vw round 0px 0px 0 0)', duration: 1.1, ease: 'power3.out', clearProps: 'clipPath' }); }
      });
    });
    void mm;
  }

  /* ───────── Parallax lembut untuk glow dan kontur latar (variabel --ag-py) ───────── */
  function initBgParallax() {
    $('.ag-hero, .ag-section, .ag-statement, .ag-hs').forEach(function (sec) {
      gsap.fromTo(sec, { '--ag-py': '-36px' }, {
        '--ag-py': '36px', ease: 'none',
        scrollTrigger: { trigger: sec, start: 'top bottom', end: 'bottom top', scrub: true, invalidateOnRefresh: true }
      });
    });
  }

  /* ───────── Angka menghitung naik ───────── */
  function initCounters() {
    $('.ag-stats dd').forEach(function (dd) {
      var node = dd.firstChild;
      if (!node || node.nodeType !== 3) return;
      var m = node.textContent.match(/([\d.,]+)/);
      if (!m) return;
      var raw = m[1], decimals = /,\d$/.test(raw) ? 1 : 0;
      var target = parseFloat(raw.replace(',', '.'));
      if (isNaN(target) || target === 0) return;
      var prefix = node.textContent.slice(0, m.index), suffix = node.textContent.slice(m.index + raw.length);
      var obj = { v: 0 };
      var fmt = function (v) { return (decimals ? v.toFixed(1).replace('.', ',') : Math.round(v)) + ''; };
      node.textContent = prefix + fmt(0) + suffix;
      ScrollTrigger.create({
        trigger: dd, start: 'top 90%', once: true,
        onEnter: function () {
          gsap.to(obj, { v: target, duration: 1.6, ease: 'power2.out', onUpdate: function () { node.textContent = prefix + fmt(obj.v) + suffix; } });
        }
      });
    });
  }

  /* ───────── Layanan: visual sticky, konten berganti (desktop) ───────── */
  function initServices(mm) {
    mm.add('(min-width: 992px)', function () {
      $('[data-ps]').forEach(function (sec) {
        var items = $('.ag-ps-item', sec), imgs = $('.ag-ps-img', sec);
        var numEl = sec.querySelector('.ag-ps-num b'), total = items.length, bar = sec.querySelector('.ag-ps-bar i');
        if (!items.length || items.length !== imgs.length) return;
        sec.classList.add('ag-ps-on');
        var cur = -1;
        gsap.set(imgs, { opacity: 0, zIndex: 1 });

        function go(i) {
          if (i === cur) return;
          var prev = cur; cur = i;
          items.forEach(function (it, k) { it.classList.toggle('is-active', k === i); });
          var next = imgs[i];
          gsap.killTweensOf([next]);
          gsap.set(next, { opacity: 1, zIndex: 3 });
          gsap.fromTo(next, { clipPath: 'inset(100% 0% 0% 0% round 28px)', scale: 1.14 }, { clipPath: 'inset(0% 0% 0% 0% round 28px)', scale: 1, duration: 1.1, ease: 'power3.inOut' });
          if (prev > -1) {
            var old = imgs[prev];
            gsap.set(old, { zIndex: 2 });
            gsap.to(old, { scale: 0.96, duration: 1.1, ease: 'power3.inOut', onComplete: function () { gsap.set(old, { opacity: 0 }); } });
          }
          if (numEl) {
            gsap.fromTo(numEl, { yPercent: 90, opacity: 0 }, { yPercent: 0, opacity: 1, duration: 0.7, ease: 'power3.out', onStart: function () { numEl.textContent = ('0' + (i + 1)).slice(-2); } });
          }
          if (bar) gsap.to(bar, { scaleY: (i + 1) / total, duration: 0.8, ease: 'power3.out' });
        }
        items.forEach(function (it, i) {
          ScrollTrigger.create({ trigger: it, start: 'top center+=36', end: 'bottom center+=36', onToggle: function (self) { if (self.isActive) go(i); } });
        });
        go(0);

        return function () {
          sec.classList.remove('ag-ps-on');
          gsap.set(imgs, { clearProps: 'all' });
          items.forEach(function (it) { it.classList.remove('is-active'); });
        };
      });
    });
  }

  /* ───────── Expertise: pin + geser horizontal (desktop) ───────── */
  function initHorizontal(mm) {
    mm.add('(min-width: 992px)', function () {
      $('[data-hs]').forEach(function (sec) {
        var track = sec.querySelector('.ag-hs-track'), vp = sec.querySelector('.ag-hs-viewport'), bar = sec.querySelector('.ag-hs-progress i');
        if (!track || !vp) return;
        sec.classList.add('ag-hs-on');
        var dist = function () { return Math.max(0, track.scrollWidth - vp.clientWidth); };
        var tween = gsap.to(track, {
          x: function () { return -dist(); }, ease: 'none',
          scrollTrigger: {
            trigger: sec, start: 'top top+=72', end: function () { return '+=' + dist(); },
            pin: true, scrub: 0.7, anticipatePin: 1, invalidateOnRefresh: true,
            onToggle: function (self) { document.body.classList.toggle('ag-hs-active', self.isActive); },
            onUpdate: function (self) { if (bar) bar.style.transform = 'scaleX(' + self.progress.toFixed(4) + ')'; }
          }
        });
        $('.ag-hs-panel', sec).forEach(function (panel) {
          var img = panel.querySelector('img');
          if (!img) return;
          gsap.fromTo(img, { xPercent: -7 }, { xPercent: 7, ease: 'none', scrollTrigger: { trigger: panel, containerAnimation: tween, start: 'left right', end: 'right left', scrub: true } });
        });
        return function () {
          sec.classList.remove('ag-hs-on');
          gsap.set(track, { clearProps: 'all' });
        };
      });
    });

    // Mobile: swipe native, hanya perbarui indikator progres
    $('[data-hs]').forEach(function (sec) {
      var vp = sec.querySelector('.ag-hs-viewport'), bar = sec.querySelector('.ag-hs-progress i');
      if (!vp || !bar) return;
      vp.addEventListener('scroll', function () {
        if (sec.classList.contains('ag-hs-on')) return;
        var max = vp.scrollWidth - vp.clientWidth;
        bar.style.transform = 'scaleX(' + (max > 0 ? vp.scrollLeft / max : 0).toFixed(4) + ')';
      }, { passive: true });
    });
  }

  /* ───────── Cerita produk: 3 tahap (sticky di desktop, bertahap di mobile) ───────── */
  function initStory(mm) {
    $('[data-story]').forEach(function (sec) {
      var items = $('.ag-story-item', sec), stage = sec.querySelector('.ag-story-stage');
      if (!items.length) return;

      mm.add('(min-width: 992px)', function () {
        if (!stage) return;
        sec.classList.add('ag-story-on');
        var cur = 0;
        stage.setAttribute('data-state', '1');
        items[0].classList.add('is-active');
        function go(i) {
          if (i === cur) return; cur = i;
          stage.setAttribute('data-state', String(i + 1));
          items.forEach(function (it, k) { it.classList.toggle('is-active', k === i); });
        }
        items.forEach(function (it, i) {
          ScrollTrigger.create({ trigger: it, start: 'top 58%', end: 'bottom 58%', onToggle: function (self) { if (self.isActive) go(i); } });
        });
        return function () { sec.classList.remove('ag-story-on'); items.forEach(function (it) { it.classList.remove('is-active'); }); };
      });

      mm.add('(max-width: 991px)', function () {
        // tiap kartu mulai dari tahap sebelumnya, lalu bergerak saat masuk layar
        items.forEach(function (it, i) {
          var svg = it.querySelector('.ag-story-art');
          if (!svg) return;
          svg.setAttribute('data-state', String(Math.max(1, i)));
          ScrollTrigger.create({ trigger: it, start: 'top 72%', once: true, onEnter: function () { svg.setAttribute('data-state', String(i + 1)); } });
        });
      });
    });
  }

  /* ───────── Simulator: kontrol muncul bertahap ───────── */
  function initSimulator() {
    $('.ag-sim, .ag-win, .ag-ppf').forEach(function (sim) {
      var view = sim.querySelector('.ag-sim-view, .ag-win-view');
      var panel = sim.querySelector('.ag-sim-panel');
      if (view) {
        gsap.set(view, { clipPath: 'inset(8% 6% 8% 6% round 28px)', opacity: 0 });
        ScrollTrigger.create({ trigger: sim, start: 'top 82%', once: true, onEnter: function () {
          gsap.to(view, { clipPath: 'inset(0% 0% 0% 0% round 28px)', opacity: 1, duration: 1.1, ease: 'power3.out', clearProps: 'clipPath' });
        } });
      }
      if (panel) {
        var kids = Array.prototype.slice.call(panel.children);
        gsap.set(kids, { opacity: 0, y: 22 });
        ScrollTrigger.create({ trigger: sim, start: 'top 72%', once: true, onEnter: function () {
          gsap.to(kids, { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out', stagger: 0.14, delay: 0.35, clearProps: 'transform,opacity' });
        } });
      }
    });
  }

  /* ───────── Susun semuanya ───────── */
  function init() {
    gsap.registerPlugin(ScrollTrigger);
    gsap.config({ nullTargetWarn: false });
    ScrollTrigger.config({ ignoreMobileResize: true });
    root.classList.add('ag-m');

    var mm = gsap.matchMedia();
    // stagger kartu dalam grid lewat CSS (reveal pengamat)
    $('.ag-grid, .ag-stats, .ag-clients').forEach(function (g) {
      Array.prototype.slice.call(g.children).forEach(function (c, i) { c.style.setProperty('--i', i % 6); });
    });

    initHero(mm);
    initHeadings();
    initStatements();
    initCounters();
    initImages(mm);
    initSections(mm);
    initBgParallax();
    initServices(mm);
    initHorizontal(mm);
    initStory(mm);
    initSimulator();
    if (window.matchMedia('(min-width: 1360px)').matches) initIndex();

    ScrollTrigger.sort();
    release();
    window.addEventListener('load', function () { ScrollTrigger.refresh(); setTimeout(function () { ScrollTrigger.refresh(); }, 600); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { ScrollTrigger.refresh(); });
  }

  function start() {
    initBasics();
    if (reduce || !window.gsap || !window.ScrollTrigger) { release(); return; }
    try { init(); } catch (err) { release(); if (window.console) console.error('ag-motion', err); }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
  // Pengaman: konten tidak pernah tersembunyi lebih dari 2,5 detik
  setTimeout(release, 2500);
})();
