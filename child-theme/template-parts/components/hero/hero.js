(function () {
  function initHeroLottie() {
    var containers = document.querySelectorAll('[data-wcs-lottie]');

    if (!containers.length || !window.lottie) {
      return;
    }

    containers.forEach(function (container) {
      if (container.getAttribute('data-wcs-lottie-ready') === '1') {
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

      container.setAttribute('data-wcs-lottie-ready', '1');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeroLottie);
    return;
  }

  initHeroLottie();
})();
