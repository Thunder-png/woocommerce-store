/**
 * WCS Filter AJAX
 * Filtre değişimlerini sayfa yenilenmeden uygular.
 * Ürün grid'ini (#wcs-products-wrap) ve filtre barı aktif pilleri günceller.
 */
(function () {
  'use strict';

  function init() {
    var bar  = document.getElementById('wcs-filter-bar');
    var wrap = document.getElementById('wcs-products-wrap');
    if (!bar || !wrap) return;

    var form   = bar.querySelector('.wcs-filter-panel__form');
    var toggle = document.getElementById('wcs-filter-toggle');
    if (!form) return;

    var debounceTimer = null;
    var abortCtrl     = null;

    /* ─── Loading state ─────────────────────────────────────────── */
    function setLoading(on) {
      wrap.style.transition   = 'opacity 0.2s';
      wrap.style.opacity      = on ? '0.45' : '1';
      wrap.style.pointerEvents = on ? 'none' : '';
    }

    /* ─── URL oluştur ───────────────────────────────────────────── */
    function buildUrl() {
      var action = form.getAttribute('action').split('?')[0];
      var fd = new FormData(form);
      var p  = new URLSearchParams();
      fd.forEach(function (v, k) { if (v !== '') p.append(k, v); });
      return action + (p.toString() ? '?' + p.toString() : '');
    }

    /* ─── Radio durumunu URL'den senkronize et ──────────────────── */
    function syncRadiosFromUrl(url) {
      var search = url.indexOf('?') > -1 ? url.split('?')[1] : '';
      var params = new URLSearchParams(search);
      form.querySelectorAll('input[type="radio"]').forEach(function (r) {
        var v = params.get(r.name) || '';
        r.checked = (r.value === v);
        if (r.checked) {
          var grp = r.closest('.wcs-filter-panel__options');
          if (grp) {
            grp.querySelectorAll('.wcs-filter-option')
               .forEach(function (l) { l.classList.remove('wcs-filter-option--active'); });
            r.closest('.wcs-filter-option').classList.add('wcs-filter-option--active');
          }
        }
      });
    }

    /* ─── Filtre barındaki aktif pill'leri güncelle ─────────────── */
    function updateFilterBar(doc) {
      var newBar = doc.getElementById('wcs-filter-bar');
      if (!newBar) return;

      /* Sol taraf: toggle butonunu koru, pill'leri + clear-all'ı yenile */
      var curLeft = bar.querySelector('.wcs-filter-bar__left');
      var newLeft = newBar.querySelector('.wcs-filter-bar__left');
      if (curLeft && newLeft) {
        var savedToggle = curLeft.querySelector('.wcs-filter-bar__toggle');
        curLeft.innerHTML = '';
        if (savedToggle) curLeft.appendChild(savedToggle);

        /* Toggle içindeki aktif sayaç rozetini güncelle */
        var newBadge = newLeft.querySelector('.wcs-filter-bar__active-count');
        var curBadge = savedToggle ? savedToggle.querySelector('.wcs-filter-bar__active-count') : null;
        if (newBadge && savedToggle) {
          if (curBadge) {
            curBadge.textContent = newBadge.textContent;
          } else {
            savedToggle.insertBefore(
              newBadge.cloneNode(true),
              savedToggle.querySelector('.wcs-filter-bar__chevron')
            );
          }
        } else if (curBadge) {
          curBadge.remove();
        }

        /* Yeni pill'leri ve Temizle linkini ekle */
        newLeft.querySelectorAll(
          '.wcs-filter-bar__active-pill, .wcs-filter-bar__clear-all'
        ).forEach(function (el) {
          curLeft.appendChild(el.cloneNode(true));
        });
      }

      /* Sağ taraf: ürün sayısını güncelle */
      var newCount = newBar.querySelector('.wcs-filter-bar__count');
      var curCount = bar.querySelector('.wcs-filter-bar__count');
      if (newCount && curCount) {
        curCount.textContent = newCount.textContent;
      }
    }

    /* ─── Kart animasyonunu yeniden tetikle ─────────────────────── */
    function triggerCardAnimations() {
      wrap.querySelectorAll('.wcs-card').forEach(function (c, i) {
        c.style.opacity        = '0';
        c.style.animation      = 'none';
        c.style.animationDelay = (Math.min(i, 6) * 0.07) + 's';
        void c.offsetWidth; /* reflow */
        c.style.animation = '';
      });
    }

    /* ─── AJAX fetch + DOM güncelle ─────────────────────────────── */
    function doFetch(url) {
      if (abortCtrl) abortCtrl.abort();
      abortCtrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
      setLoading(true);

      fetch(url, { signal: abortCtrl ? abortCtrl.signal : undefined })
        .then(function (r) { return r.text(); })
        .then(function (html) {
          var doc     = new DOMParser().parseFromString(html, 'text/html');
          var newWrap = doc.getElementById('wcs-products-wrap');

          if (newWrap) {
            wrap.innerHTML = newWrap.innerHTML;
            triggerCardAnimations();
          }

          updateFilterBar(doc);
          syncRadiosFromUrl(url);
          history.pushState(null, '', url);
          setLoading(false);

          /* Ürünler görünür değilse yukarı kaydır */
          var rect = wrap.getBoundingClientRect();
          if (rect.top < 0) {
            var top = wrap.getBoundingClientRect().top + window.scrollY - 90;
            window.scrollTo({ top: top, behavior: 'smooth' });
          }
        })
        .catch(function (err) {
          if (err && err.name === 'AbortError') return;
          setLoading(false);
          window.location.href = url; /* fallback */
        });
    }

    /* ─── Radio değişimi → otomatik fetch (debounced) ───────────── */
    form.addEventListener('change', function (e) {
      if (e.target.type !== 'radio') return;

      /* Aktif class'ı anında güncelle */
      var grp = e.target.closest('.wcs-filter-panel__options');
      if (grp) {
        grp.querySelectorAll('.wcs-filter-option')
           .forEach(function (l) { l.classList.remove('wcs-filter-option--active'); });
        e.target.closest('.wcs-filter-option').classList.add('wcs-filter-option--active');
      }

      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () { doFetch(buildUrl()); }, 350);
    });

    /* ─── Filtrele butonu → fetch ───────────────────────────────── */
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearTimeout(debounceTimer);
      doFetch(buildUrl());
    });

    /* ─── Aktif pill kaldırma + Temizle (event delegation) ──────── */
    bar.addEventListener('click', function (e) {
      var removeBtn = e.target.closest('.wcs-filter-bar__active-pill-remove');
      if (removeBtn) {
        e.preventDefault();
        doFetch(removeBtn.getAttribute('href'));
        return;
      }
      var clearAll = e.target.closest('.wcs-filter-bar__clear-all');
      if (clearAll) {
        e.preventDefault();
        doFetch(clearAll.getAttribute('href'));
      }
    });

    /* ─── Sayfalama linkleri (event delegation) ─────────────────── */
    document.addEventListener('click', function (e) {
      var link = e.target.closest('#wcs-products-wrap a.page-numbers');
      if (link) {
        e.preventDefault();
        doFetch(link.href);
      }
    });

    /* ─── Tarayıcı geri/ileri ───────────────────────────────────── */
    window.addEventListener('popstate', function () {
      syncRadiosFromUrl(window.location.href);
      doFetch(window.location.href);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
