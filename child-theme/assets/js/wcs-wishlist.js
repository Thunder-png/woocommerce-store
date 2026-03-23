(function () {
  "use strict";

  var KEY = "wcs_wishlist_products";

  function getList() {
    try {
      var raw = localStorage.getItem(KEY);
      var list = raw ? JSON.parse(raw) : [];
      return Array.isArray(list) ? list : [];
    } catch (error) {
      return [];
    }
  }

  function setList(list) {
    localStorage.setItem(KEY, JSON.stringify(list));
  }

  function syncButtons() {
    var list = getList();
    document.querySelectorAll("[data-wcs-wishlist]").forEach(function (btn) {
      var productId = btn.getAttribute("data-product-id");
      var active = list.indexOf(productId) !== -1;
      btn.classList.toggle("wcs-wishlist-btn--active", active);
      btn.setAttribute("aria-pressed", active ? "true" : "false");
      var icon = btn.querySelector("i");
      if (icon) {
        icon.className = active ? "bi bi-heart-fill" : "bi bi-heart";
      }
    });
  }

  function toggle(productId) {
    var list = getList();
    var index = list.indexOf(productId);
    var isAdded = false;
    if (index === -1) {
      list.push(productId);
      isAdded = true;
    } else {
      list.splice(index, 1);
    }
    setList(list);
    syncButtons();
    if (window.wcs_toast) {
      window.wcs_toast.show(isAdded ? "Urun favorilere eklendi." : "Urun favorilerden kaldirildi.", "info");
    }
  }

  document.addEventListener("click", function (event) {
    var btn = event.target.closest("[data-wcs-wishlist]");
    if (!btn) {
      return;
    }
    event.preventDefault();
    var productId = btn.getAttribute("data-product-id");
    if (!productId) {
      return;
    }
    toggle(productId);
  });

  document.addEventListener("DOMContentLoaded", syncButtons);
  document.addEventListener("wc_fragments_loaded", syncButtons);
})();
