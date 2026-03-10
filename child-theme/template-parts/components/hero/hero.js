/**
 * WCS Hero — Enhanced JS
 * Handles: canvas net animation, stat counters, mouse parallax, ticker pause.
 *
 * child-theme/template-parts/components/hero/hero.js
 */

(function () {
  'use strict';

  /* ─────────────────────────────────────────────────────────────
     CANVAS NET ANIMATION
     ───────────────────────────────────────────────────────────── */
  function initHeroCanvas() {
    var canvas = document.getElementById('wcs-hero-canvas');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    var W, H;
    var nodes = [];
    var cols  = 22;
    var rows  = 14;
    var mouse = { x: -9999, y: -9999 };
    var t     = 0;
    var raf;

    function setup() {
      var section = canvas.closest('.wcs-hero') || canvas.parentElement;
      W = canvas.width  = section ? section.offsetWidth  : window.innerWidth;
      H = canvas.height = section ? section.offsetHeight : window.innerHeight;

      nodes = [];
      for (var r = 0; r < rows; r++) {
        for (var c = 0; c < cols; c++) {
          nodes.push({
            ox: (c / (cols - 1)) * W,
            oy: (r / (rows - 1)) * H,
            x:  0,
            y:  0,
            ph: Math.random() * Math.PI * 2,
            sp: 0.18 + Math.random() * 0.28,
          });
        }
      }
    }

    function draw(ts) {
      t = ts * 0.001;
      ctx.clearRect(0, 0, W, H);

      /* Update node positions */
      for (var i = 0; i < nodes.length; i++) {
        var n  = nodes[i];
        var w  = Math.sin(t * n.sp + n.ph) * 14;
        n.x    = n.ox + Math.cos(n.ph * 1.4) * w * 0.45;
        n.y    = n.oy + w;

        /* Mouse repulsion */
        var dx = n.x - mouse.x;
        var dy = n.y - mouse.y;
        var d  = Math.sqrt(dx * dx + dy * dy);
        if (d < 180 && d > 0) {
          var f = (180 - d) / 180 * 32;
          n.x  += (dx / d) * f;
          n.y  += (dy / d) * f;
        }
      }

      /* Draw edges */
      for (var r = 0; r < rows; r++) {
        for (var c = 0; c < cols; c++) {
          var idx = r * cols + c;
          var n   = nodes[idx];

          /* Horizontal — white, brighter with pulse */
          if (c < cols - 1) {
            var nx  = nodes[idx + 1];
            var a   = 0.55 + Math.sin(t * 0.4 + r * 0.6) * 0.12;
            /* glow pass */
            ctx.beginPath();
            ctx.moveTo(n.x, n.y);
            ctx.lineTo(nx.x, nx.y);
            ctx.strokeStyle = 'rgba(255,255,255,' + (a * 0.25) + ')';
            ctx.lineWidth   = 5;
            ctx.stroke();
            /* sharp pass */
            ctx.beginPath();
            ctx.moveTo(n.x, n.y);
            ctx.lineTo(nx.x, nx.y);
            ctx.strokeStyle = 'rgba(255,255,255,' + a + ')';
            ctx.lineWidth   = 1.6;
            ctx.stroke();
          }

          /* Vertical — red, brighter with pulse */
          if (r < rows - 1) {
            var nb  = nodes[idx + cols];
            var ab  = 0.38 + Math.sin(t * 0.35 + c * 0.45) * 0.1;
            /* glow pass */
            ctx.beginPath();
            ctx.moveTo(n.x, n.y);
            ctx.lineTo(nb.x, nb.y);
            ctx.strokeStyle = 'rgba(240,80,80,' + (ab * 0.3) + ')';
            ctx.lineWidth   = 5;
            ctx.stroke();
            /* sharp pass */
            ctx.beginPath();
            ctx.moveTo(n.x, n.y);
            ctx.lineTo(nb.x, nb.y);
            ctx.strokeStyle = 'rgba(226,0,13,' + ab + ')';
            ctx.lineWidth   = 1.6;
            ctx.stroke();
          }

          /* Diagonal — visible now */
          if (c < cols - 1 && r < rows - 1 && (r + c) % 3 === 0) {
            var nd = nodes[idx + cols + 1];
            ctx.beginPath();
            ctx.moveTo(n.x, n.y);
            ctx.lineTo(nd.x, nd.y);
            ctx.strokeStyle = 'rgba(255,255,255,0.055)';
            ctx.lineWidth   = 0.5;
            ctx.stroke();
          }

          /* Nodes */
          var dist = Math.hypot(n.x - mouse.x, n.y - mouse.y);
          var near = dist < 180;

          if (near) {
            ctx.beginPath();
            ctx.arc(n.x, n.y, 10, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(226,0,13,' + ((180 - dist) / 180 * 0.12) + ')';
            ctx.fill();
          }

          ctx.beginPath();
          ctx.arc(n.x, n.y, near ? 3.0 : 1.4, 0, Math.PI * 2);
          ctx.fillStyle = near
            ? 'rgba(226,0,13,0.85)'
            : 'rgba(255,255,255,0.22)';
          ctx.fill();
        }
      }

      raf = requestAnimationFrame(draw);
    }

    setup();

    /* Resize debounce */
    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        cancelAnimationFrame(raf);
        setup();
        raf = requestAnimationFrame(draw);
      }, 200);
    });

    /* Mouse tracking relative to section */
    var section = canvas.closest('.wcs-hero');
    var trackTarget = section || window;

    if (section) {
      section.addEventListener('mousemove', function (e) {
        var rect  = section.getBoundingClientRect();
        mouse.x   = e.clientX - rect.left;
        mouse.y   = e.clientY - rect.top;
      });
      section.addEventListener('mouseleave', function () {
        mouse.x = -9999;
        mouse.y = -9999;
      });
    } else {
      window.addEventListener('mousemove', function (e) {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
      });
    }

    raf = requestAnimationFrame(draw);
  }

  /* ─────────────────────────────────────────────────────────────
     STAT COUNTER ANIMATION
     ───────────────────────────────────────────────────────────── */
  function initStatCounters() {
    var stats = document.querySelectorAll('.wcs-hero__stat-value[data-target]');
    if (!stats.length) return;

    var animated = false;

    function easeOutQuart(t) {
      return 1 - Math.pow(1 - t, 4);
    }

    function animateCounters() {
      if (animated) return;
      animated = true;

      stats.forEach(function (el) {
        var target   = parseInt(el.getAttribute('data-target'), 10);
        var duration = 1400;
        var start    = null;

        /* Find or create the leading text node (before <sup>) */
        var textNode = null;
        for (var i = 0; i < el.childNodes.length; i++) {
          if (el.childNodes[i].nodeType === 3) {
            textNode = el.childNodes[i];
            break;
          }
        }
        if (!textNode) {
          textNode = document.createTextNode('');
          el.insertBefore(textNode, el.firstChild);
        }

        function step(ts) {
          if (!start) start = ts;
          var progress = Math.min((ts - start) / duration, 1);
          var eased    = easeOutQuart(progress);
          var current  = Math.round(eased * target);

          textNode.textContent = current;

          if (progress < 1) {
            requestAnimationFrame(step);
          } else {
            textNode.textContent = target;
          }
        }

        requestAnimationFrame(step);
      });
    }

    /* Use IntersectionObserver if available */
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateCounters();
            observer.disconnect();
          }
        });
      }, { threshold: 0.4 });

      var statsSection = document.querySelector('.wcs-hero__stats');
      if (statsSection) observer.observe(statsSection);
    } else {
      /* Fallback: run after a short delay */
      setTimeout(animateCounters, 800);
    }
  }

  /* ─────────────────────────────────────────────────────────────
     NET CARD MOUSE PARALLAX
     ───────────────────────────────────────────────────────────── */
  function initCardParallax() {
    var card = document.querySelector('.wcs-hero__net-card');
    if (!card) return;

    var tiltX = 0, tiltY = 0;
    var targetX = 0, targetY = 0;
    var rafId;

    card.addEventListener('mousemove', function (e) {
      var rect   = card.getBoundingClientRect();
      var cx     = rect.left + rect.width  / 2;
      var cy     = rect.top  + rect.height / 2;
      targetX    = ((e.clientY - cy) / rect.height) *  8;
      targetY    = ((e.clientX - cx) / rect.width)  * -8;
    });

    card.addEventListener('mouseleave', function () {
      targetX = 0;
      targetY = 0;
    });

    function lerp(a, b, t) { return a + (b - a) * t; }

    function tick() {
      tiltX = lerp(tiltX, targetX, 0.08);
      tiltY = lerp(tiltY, targetY, 0.08);
      card.style.transform = 'perspective(800px) rotateX(' + tiltX + 'deg) rotateY(' + tiltY + 'deg)';
      rafId = requestAnimationFrame(tick);
    }

    rafId = requestAnimationFrame(tick);
  }

  /* ─────────────────────────────────────────────────────────────
     TICKER TOUCH PAUSE
     ───────────────────────────────────────────────────────────── */
  function initTickerPause() {
    var track = document.getElementById('wcs-hero-ticker');
    if (!track) return;

    track.addEventListener('touchstart', function () {
      track.style.animationPlayState = 'paused';
    }, { passive: true });

    track.addEventListener('touchend', function () {
      track.style.animationPlayState = 'running';
    }, { passive: true });
  }

  /* ─────────────────────────────────────────────────────────────
     LOTTIE (preserve existing integration)
     ───────────────────────────────────────────────────────────── */
  function initHeroLottie() {
    var containers = document.querySelectorAll('[data-wcs-lottie]');
    if (!containers.length || !window.lottie) return;

    containers.forEach(function (container) {
      if (container.getAttribute('data-wcs-lottie-ready') === '1') return;
      var src = container.getAttribute('data-lottie-src');
      if (!src) return;

      window.lottie.loadAnimation({
        container: container,
        renderer:  'svg',
        loop:      true,
        autoplay:  true,
        path:      src,
      });

      container.setAttribute('data-wcs-lottie-ready', '1');
    });
  }

  /* ─────────────────────────────────────────────────────────────
     BOOT
     ───────────────────────────────────────────────────────────── */
  function boot() {
    initHeroCanvas();
    initStatCounters();
    initCardParallax();
    initTickerPause();
    initHeroLottie();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

})();