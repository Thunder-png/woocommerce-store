(function () {
  "use strict";

  var sticky = document.querySelector(".wcs-sticky-atc");
  var target = document.querySelector(".wcs-sp-card__woo-form");
  if (!sticky || !target || !("IntersectionObserver" in window)) {
    return;
  }

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        sticky.classList.toggle("is-visible", !entry.isIntersecting);
      });
    },
    { threshold: 0.2 }
  );
  observer.observe(target);

  document.addEventListener("click", function (event) {
    var button = event.target.closest("[data-wcs-sticky-atc-button]");
    if (!button) {
      return;
    }
    event.preventDefault();

    var addToCart = document.querySelector(".single_add_to_cart_button");
    if (addToCart) {
      addToCart.click();
    }
  });
})();
