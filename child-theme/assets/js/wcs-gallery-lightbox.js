(function () {
  "use strict";

  function bootstrapGalleryAnchors() {
    document
      .querySelectorAll(".woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image a")
      .forEach(function (anchor) {
        anchor.setAttribute("data-gallery", "product-gallery");
        anchor.classList.add("wcs-glightbox-link");
      });
  }

  function init() {
    if (typeof window.GLightbox === "undefined") {
      return;
    }
    bootstrapGalleryAnchors();
    window.GLightbox({
      selector: ".wcs-glightbox-link",
      touchNavigation: true,
      loop: true
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
