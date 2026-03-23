(function ($) {
  "use strict";

  var container = null;

  function ensureContainer() {
    if (container) {
      return container;
    }
    container = document.querySelector(".wcs-toast-container");
    if (!container) {
      container = document.createElement("div");
      container.className = "wcs-toast-container";
      container.setAttribute("aria-live", "polite");
      container.setAttribute("aria-atomic", "false");
      document.body.appendChild(container);
    }
    return container;
  }

  function show(message, type) {
    if (!message) {
      return;
    }

    var wrap = ensureContainer();
    var toast = document.createElement("div");
    toast.className = "wcs-toast wcs-toast--" + (type || "info");
    toast.textContent = message;
    wrap.appendChild(toast);

    requestAnimationFrame(function () {
      toast.classList.add("is-visible");
    });

    setTimeout(function () {
      toast.classList.remove("is-visible");
      setTimeout(function () {
        if (toast && toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 240);
    }, 2800);
  }

  window.wcs_toast = { show: show };

  if (typeof $ !== "undefined") {
    $(document.body).on("added_to_cart", function () {
      show("Urun sepete eklendi.", "success");
    });
    $(document.body).on("removed_from_cart", function () {
      show("Urun sepetten cikarildi.", "info");
    });
  }
})(window.jQuery);
