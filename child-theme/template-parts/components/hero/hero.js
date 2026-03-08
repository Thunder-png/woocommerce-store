(function () {
  function initHeroLottie() {
    var container = document.querySelector('[data-wcs-lottie]');

    if (!container || !window.lottie) {
      return;
    }

    var src = container.getAttribute('data-lottie-src');

    if (!src) {
      return;
    }

    window.lottie.loadAnimation({
      container: container,
      renderer: 'svg',
      loop: true,
      autoplay: true,
      path: src,
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeroLottie);
    return;
  }

  initHeroLottie();
})();
