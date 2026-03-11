;(function ($) {
  if (typeof $ === "undefined") {
    return;
  }

  function getCalculatorRoot() {
    return document.querySelector(".wcs-calculator");
  }

  function ensureErrorEl() {
    var root = getCalculatorRoot();
    if (!root) {
      return null;
    }

    var existing = root.querySelector(".wcs-calculator__error");
    if (existing) {
      return existing;
    }

    var el = document.createElement("p");
    el.className = "wcs-calculator__error";
    el.setAttribute("role", "alert");
    root.appendChild(el);
    return el;
  }

  function showCalculatorError(message) {
    var el = ensureErrorEl();
    if (!el) {
      return;
    }
    el.textContent = message || "";
  }

  function clearCalculatorError() {
    var root = getCalculatorRoot();
    if (!root) {
      return;
    }
    var el = root.querySelector(".wcs-calculator__error");
    if (!el) {
      return;
    }
    el.textContent = "";
  }

  // Küçük helper: dış scriptler de isterse kullanabilsin.
  window.wcsCalculatorValidation = {
    clearError: clearCalculatorError,
  };

  // m² hesaplayıcılı ürünlerde, zorunlu alanlar boşsa form submit'ini engelle.
  $(document).on("submit", "form.cart", function (event) {
    // Hesaplayıcı olmayan simple ürünlerde varsayılan WooCommerce akışı kalsın.
    if (!getCalculatorRoot()) {
      return;
    }

    var width = parseFloat($("#wcs-width").val());
    var height = parseFloat($("#wcs-height").val());
    var thickness = $("#wcs-thickness").val();
    var mesh = $("#wcs-mesh").val();

    var missing = [];

    if (!Number.isFinite(width) || width <= 0) {
      missing.push("genişlik");
    }

    if (!Number.isFinite(height) || height <= 0) {
      missing.push("yükseklik");
    }

    if (!thickness) {
      missing.push("ip kalınlığı");
    }

    if (!mesh) {
      missing.push("göz boyutu");
    }

    if (missing.length === 0) {
      clearCalculatorError();
      return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    var message =
      "Lütfen sepet eklemeden önce " +
      missing.join(", ") +
      " alanlarını doldurun.";

    showCalculatorError(message);
  });

  // Kullanıcı alanları düzenledikçe uyarıyı kaldır.
  $(document).on(
    "input change",
    "#wcs-width, #wcs-height, #wcs-thickness, #wcs-mesh",
    function () {
      clearCalculatorError();
    }
  );
})(window.jQuery);

