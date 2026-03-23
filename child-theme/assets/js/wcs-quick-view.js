(function () {
  "use strict";

  if (typeof wcsQuickView === "undefined") {
    return;
  }

  var modal = null;
  var content = null;

  function ensureModal() {
    if (modal) {
      return;
    }
    modal = document.createElement("div");
    modal.className = "wcs-quick-view-modal";
    modal.setAttribute("hidden", "hidden");
    modal.innerHTML =
      '<div class="wcs-quick-view-modal__backdrop" data-wcs-qv-close></div>' +
      '<div class="wcs-quick-view-modal__dialog" role="dialog" aria-modal="true" aria-label="Hizli goruntule">' +
      '<button class="wcs-quick-view-modal__close" type="button" data-wcs-qv-close aria-label="Kapat">&times;</button>' +
      '<div class="wcs-quick-view-modal__content" data-wcs-qv-content></div>' +
      "</div>";
    document.body.appendChild(modal);
    content = modal.querySelector("[data-wcs-qv-content]");
  }

  function openModal(html) {
    ensureModal();
    content.innerHTML = html || "";
    modal.removeAttribute("hidden");
    document.body.classList.add("wcs-no-scroll");
  }

  function closeModal() {
    if (!modal) {
      return;
    }
    modal.setAttribute("hidden", "hidden");
    document.body.classList.remove("wcs-no-scroll");
  }

  function fetchProduct(productId) {
    var params = new URLSearchParams();
    params.set("action", "wcs_quick_view");
    params.set("nonce", wcsQuickView.nonce || "");
    params.set("product_id", String(productId || ""));

    return fetch((wcsQuickView.ajaxUrl || "") + "?" + params.toString(), {
      credentials: "same-origin"
    }).then(function (r) {
      return r.json();
    });
  }

  document.addEventListener("click", function (event) {
    var closeBtn = event.target.closest("[data-wcs-qv-close]");
    if (closeBtn) {
      event.preventDefault();
      closeModal();
      return;
    }

    var trigger = event.target.closest(".wcs-quick-view-btn");
    if (!trigger) {
      return;
    }

    event.preventDefault();
    var productId = trigger.getAttribute("data-product-id");
    fetchProduct(productId).then(function (json) {
      if (!json || !json.success) {
        if (window.wcs_toast) {
          window.wcs_toast.show("Hizli goruntuleme acilamadi.", "error");
        }
        return;
      }
      openModal(json.data.html || "");
    });
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeModal();
    }
  });
})();
