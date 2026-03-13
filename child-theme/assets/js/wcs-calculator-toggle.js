(function () {
  document.addEventListener("DOMContentLoaded", function () {
    var card = document.querySelector(".single-product .wcs-product-card") || document;
    var toggle = card.querySelector(".wcs-calculator-toggle");
    var calculator = document.querySelector(".wcs-calculator");

    if (!toggle || !calculator) {
      return;
    }

    // Başlangıçta kapalı dursun.
    calculator.classList.remove("is-open");
    toggle.setAttribute("aria-expanded", "false");

    toggle.addEventListener("click", function () {
      var isOpen = calculator.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
  });
})();

