/**
 * Karaca Ürün Spec Kartı — canvas ağ animasyonu + varyasyon değişince kart güncelleme.
 * @package WooCommerce_Store_Child
 */

(function ($) {
  'use strict';

  var GAP = 38;

  function initNetCanvas(canvas) {
    if (!canvas || !canvas.getContext) return;
    var ctx = canvas.getContext('2d');
    var W, H, cols, rows, pts;
    var mouse = { x: -999, y: -999 };

    function resize() {
      var rect = canvas.parentElement.getBoundingClientRect();
      W = canvas.width = rect.width;
      H = canvas.height = rect.height;
      build();
    }

    function build() {
      cols = Math.ceil(W / GAP) + 2;
      rows = Math.ceil(H / GAP) + 2;
      pts = [];
      for (var r = 0; r < rows; r++) {
        for (var c = 0; c < cols; c++) {
          pts.push({ bx: (c - 1) * GAP, by: (r - 1) * GAP, ox: 0, oy: 0, vx: 0, vy: 0 });
        }
      }
    }

    function tick() {
      if (!ctx || !pts) return;
      ctx.clearRect(0, 0, W, H);

      pts.forEach(function (p) {
        var dx = p.bx + p.ox - mouse.x;
        var dy = p.by + p.oy - mouse.y;
        var d = Math.sqrt(dx * dx + dy * dy);
        var R = 70, strength = 18;
        if (d < R) {
          var f = (1 - d / R) * strength;
          p.vx += (dx / d) * f;
          p.vy += (dy / d) * f;
        }
        p.vx *= 0.82;
        p.vy *= 0.82;
        p.ox += p.vx;
        p.oy += p.vy;
        p.ox *= 0.88;
        p.oy *= 0.88;
      });

      ctx.strokeStyle = 'rgba(255,255,255,0.13)';
      ctx.lineWidth = 1.4;
      ctx.lineCap = 'round';

      for (var r = 0; r < rows; r++) {
        ctx.beginPath();
        for (var c = 0; c < cols; c++) {
          var p = pts[r * cols + c];
          var x = p.bx + p.ox, y = p.by + p.oy;
          if (c === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        }
        ctx.stroke();
      }
      for (var c = 0; c < cols; c++) {
        ctx.beginPath();
        for (var r = 0; r < rows; r++) {
          var p = pts[r * cols + c];
          var x = p.bx + p.ox, y = p.by + p.oy;
          if (r === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        }
        ctx.stroke();
      }

      ctx.fillStyle = 'rgba(255,255,255,0.22)';
      pts.forEach(function (p) {
        ctx.beginPath();
        ctx.arc(p.bx + p.ox, p.by + p.oy, 2, 0, Math.PI * 2);
        ctx.fill();
      });

      requestAnimationFrame(tick);
    }

    canvas.addEventListener('mousemove', function (e) {
      var r = canvas.getBoundingClientRect();
      mouse.x = e.clientX - r.left;
      mouse.y = e.clientY - r.top;
    });
    canvas.addEventListener('mouseleave', function () {
      mouse.x = mouse.y = -999;
    });

    resize();
    window.addEventListener('resize', resize);
    tick();
  }

  function updateSpecCard($card, spec) {
    if (!$card || !$card.length) return;
    var goz = (spec && spec.wcs_spec_goz) ? spec.wcs_spec_goz : $card.data('spec-goz') || '—';
    var ip = (spec && spec.wcs_spec_ip) ? spec.wcs_spec_ip : $card.data('spec-ip') || '—';
    var renk = (spec && spec.wcs_spec_renk) ? spec.wcs_spec_renk : $card.data('spec-renk') || '';

    $card.attr('data-spec-goz', goz);
    $card.attr('data-spec-ip', ip);
    $card.attr('data-spec-renk', renk);

    $card.find('.kfc-center-value').text(goz);
    $card.find('.kfc-badge-ip-value').each(function () { $(this).text(ip); });
    $card.find('.kfc-spec-line--goz').contents().first().replaceWith(document.createTextNode(goz));
    $card.find('.kfc-spec-line--ip').contents().first().replaceWith(document.createTextNode(ip));

    var $colorWrap = $card.find('.kfc-color-dot-wrap');
    if ($colorWrap.length) {
      $colorWrap.find('.kfc-color-label').text(renk);
      $colorWrap.toggle(!!renk);
    }
  }

  function initSpecCards() {
    var $cards = $('.wcs-spec-card.kfc-card');
    $cards.each(function () {
      var $card = $(this);
      var canvas = $card.find('.kfc-net')[0];
      if (canvas) initNetCanvas(canvas);
    });
  }

  $(function () {
    initSpecCards();

    $(document.body).on('found_variation', 'form.variations_form', function (event, variation) {
      var $card = $('.wcs-spec-card.kfc-card').first();
      updateSpecCard($card, variation);
    });
  });
})(window.jQuery);
