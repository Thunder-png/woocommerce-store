(function () {
  "use strict";

  var input = document.getElementById("wcs-search-input");
  var form = input ? input.closest("form") : null;
  var wrap = document.getElementById("wcs-search-wrap");
  if (!input || !form || !wrap || typeof wcsLiveSearch === "undefined") {
    return;
  }

  var box = document.createElement("div");
  box.className = "wcs-live-search-results";
  box.setAttribute("hidden", "hidden");
  wrap.appendChild(box);

  var timer = null;
  var abortCtrl = null;

  function escapeHTML(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function closeResults() {
    box.innerHTML = "";
    box.setAttribute("hidden", "hidden");
  }

  function render(items) {
    if (!items || !items.length) {
      box.innerHTML = '<div class="wcs-live-search-results__empty">' + (wcsLiveSearch.noResultsText || "Sonuc bulunamadi") + "</div>";
      box.removeAttribute("hidden");
      return;
    }

    var html = ['<ul class="wcs-live-search-results__list">'];
    items.forEach(function (item) {
      var imageHtml = item.image ? '<img src="' + escapeHTML(item.image) + '" alt="' + escapeHTML(item.name) + '">' : '<span class="wcs-live-search-results__thumb-ph"></span>';
      html.push(
        '<li class="wcs-live-search-results__item">' +
          '<a href="' + escapeHTML(item.url) + '" class="wcs-live-search-results__link">' +
            '<span class="wcs-live-search-results__thumb">' + imageHtml + "</span>" +
            '<span class="wcs-live-search-results__meta">' +
              '<strong class="wcs-live-search-results__title">' + escapeHTML(item.name) + "</strong>" +
              '<span class="wcs-live-search-results__price">' + escapeHTML(item.price || "") + "</span>" +
            "</span>" +
          "</a>" +
        "</li>"
      );
    });
    html.push("</ul>");
    box.innerHTML = html.join("");
    box.removeAttribute("hidden");
  }

  function fetchResults(term) {
    if (abortCtrl) {
      abortCtrl.abort();
    }
    abortCtrl = typeof AbortController !== "undefined" ? new AbortController() : null;

    var params = new URLSearchParams();
    params.set("action", "wcs_live_search");
    params.set("nonce", wcsLiveSearch.nonce || "");
    params.set("term", term);

    fetch((wcsLiveSearch.ajaxUrl || "") + "?" + params.toString(), {
      signal: abortCtrl ? abortCtrl.signal : undefined,
      credentials: "same-origin"
    })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (!json || !json.success) {
          render([]);
          return;
        }
        render(json.data.items || []);
      })
      .catch(function (error) {
        if (error && error.name === "AbortError") {
          return;
        }
        render([]);
      });
  }

  input.addEventListener("input", function () {
    var term = (input.value || "").trim();
    if (term.length < (wcsLiveSearch.minChars || 2)) {
      closeResults();
      return;
    }
    clearTimeout(timer);
    timer = setTimeout(function () {
      fetchResults(term);
    }, 300);
  });

  document.addEventListener("click", function (event) {
    if (!wrap.contains(event.target)) {
      closeResults();
    }
  });

  input.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeResults();
    }
  });
})();
