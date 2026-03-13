(function () {
  document.addEventListener("DOMContentLoaded", function () {
    var widthInput  = document.getElementById("cmpc-width");
    var heightInput = document.getElementById("cmpc-height");
    var areaDisplay = document.getElementById("cmpc-area-display");
    var totalDisplay = document.getElementById("cmpc-total-display");
    var areaHidden  = document.getElementById("cmpc-area-hidden");
    var summary     = document.querySelector(".cmpc-summary");
    var areaVisual  = document.getElementById("cmpc-area-visual");
    var areaRectEl  = document.getElementById("cmpc-area-rect");
    var areaInfoEl  = document.getElementById("cmpc-area-info-text");
    var formulaEl   = document.getElementById("cmpc-formula");
    var widthHint   = document.getElementById("cmpc-width-hint");
    var heightHint  = document.getElementById("cmpc-height-hint");

    if (!widthInput || !heightInput || !areaDisplay || !totalDisplay) return;

    var settings    = window.CMPC_Settings || {};
    var baseM2Price = Number(settings.baseM2Price || 0);
    var currency    = settings.currency || "";
    var minW = settings.minWidth  != null ? Number(settings.minWidth)  : null;
    var maxW = settings.maxWidth  != null ? Number(settings.maxWidth)  : null;
    var minH = settings.minHeight != null ? Number(settings.minHeight) : null;
    var maxH = settings.maxHeight != null ? Number(settings.maxHeight) : null;

    function formatMoney(value) {
      return value.toFixed(2) + " " + currency;
    }

    function getValidationError(value, min, max, label) {
      if (!widthInput.value && !heightInput.value) return "";
      if (!Number.isFinite(value) || value <= 0) return label + " giriniz.";
      if (min != null && value < min) return "En az " + min + " m olmalı.";
      if (max != null && value > max) return "En fazla " + max + " m olabilir.";
      return null;
    }

    function updateFieldState(input, hintEl, isValid, message) {
      if (!input) return;
      input.classList.toggle("cmpc-input-valid", isValid);
      input.classList.toggle("cmpc-input-error", !isValid && message !== "");
      if (hintEl) {
        hintEl.textContent = message || "";
        hintEl.classList.toggle("cmpc-hint-error", !isValid && message !== "");
      }
    }

    function buildRangeTip(min, max) {
      if (min != null && max != null) return min + "–" + max + " m arası";
      if (min != null) return "Min " + min + " m";
      if (max != null) return "Maks " + max + " m";
      return "";
    }

    function updateAreaVisual(w, h) {
      if (!areaVisual) return;
      var maxDim = 54;
      var ratio  = w > 0 && h > 0 ? h / w : 1;
      var rw, rh;
      if (ratio >= 1) {
        rh = maxDim;
        rw = Math.max(10, Math.round(maxDim / ratio));
      } else {
        rw = maxDim;
        rh = Math.max(10, Math.round(maxDim * ratio));
      }
      if (areaRectEl) {
        areaRectEl.setAttribute("width",  rw);
        areaRectEl.setAttribute("height", rh);
      }
      if (areaInfoEl) {
        var area = (w * h).toFixed(2);
        areaInfoEl.innerHTML = "<strong>" + w.toFixed(2) + " m × " + h.toFixed(2) + " m</strong><br>" + area + " m² alan";
      }
      areaVisual.classList.add("cmpc-visible");
    }

    function hideAreaVisual() {
      if (areaVisual) areaVisual.classList.remove("cmpc-visible");
    }

    function update() {
      var w = Number(widthInput.value);
      var h = Number(heightInput.value);

      var wErr = getValidationError(w, minW, maxW, "En değeri");
      var hErr = getValidationError(h, minH, maxH, "Boy değeri");

      var wValid = wErr === null;
      var hValid = hErr === null;

      updateFieldState(widthInput,  widthHint,  wValid, wErr  || buildRangeTip(minW, maxW));
      updateFieldState(heightInput, heightHint, hValid, hErr  || buildRangeTip(minH, maxH));

      if (!wValid || !hValid) {
        areaDisplay.textContent  = "—";
        totalDisplay.textContent = "—";
        totalDisplay.classList.remove("cmpc-calculating");
        if (summary) summary.classList.remove("cmpc-has-value");
        if (areaHidden) areaHidden.value = "";
        hideAreaVisual();
        if (formulaEl) formulaEl.innerHTML = "";
        return;
      }

      var area  = w * h;
      var total = area * baseM2Price;

      areaDisplay.textContent  = area.toFixed(2) + " m²";
      totalDisplay.textContent = formatMoney(total);
      if (summary) summary.classList.add("cmpc-has-value");
      if (areaHidden) areaHidden.value = area.toFixed(4);

      if (formulaEl) {
        formulaEl.innerHTML =
          '<span class="cmpc-chip">' + w.toFixed(2) + '</span>' +
          '<span class="cmpc-chip-op">×</span>' +
          '<span class="cmpc-chip">' + h.toFixed(2) + '</span>' +
          '<span class="cmpc-chip-op">=</span>' +
          '<span class="cmpc-chip">' + area.toFixed(2) + ' m²</span>';
      }

      updateAreaVisual(w, h);
    }

    widthInput.addEventListener("input",  update);
    heightInput.addEventListener("input", update);

    // Hintlere başlangıç değerlerini yaz
    if (widthHint)  widthHint.textContent  = buildRangeTip(minW, maxW);
    if (heightHint) heightHint.textContent = buildRangeTip(minH, maxH);

    // Form submit validasyonu
    var form = widthInput.closest("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        var w = Number(widthInput.value);
        var h = Number(heightInput.value);
        var wErr = getValidationError(w, minW, maxW, "En değeri");
        var hErr = getValidationError(h, minH, maxH, "Boy değeri");
        if (wErr !== null || hErr !== null) {
          e.preventDefault();
          if (wErr !== null) widthInput.focus();
        }
      });
    }

    update();
  });
})();

