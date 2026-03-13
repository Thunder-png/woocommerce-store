(function () {
  document.addEventListener("DOMContentLoaded", function () {
    var widthInput = document.getElementById("cmpc-width");
    var heightInput = document.getElementById("cmpc-height");
    var areaDisplay = document.getElementById("cmpc-area-display");
    var totalDisplay = document.getElementById("cmpc-total-display");
    var areaHidden = document.getElementById("cmpc-area-hidden");

    if (!widthInput || !heightInput || !areaDisplay || !totalDisplay) {
      return;
    }

    var settings = window.CMPC_Settings || {};
    var baseM2Price = Number(settings.baseM2Price || 0);
    var currency = settings.currency || "";
    var minW = settings.minWidth != null ? Number(settings.minWidth) : null;
    var maxW = settings.maxWidth != null ? Number(settings.maxWidth) : null;
    var minH = settings.minHeight != null ? Number(settings.minHeight) : null;
    var maxH = settings.maxHeight != null ? Number(settings.maxHeight) : null;

    function formatMoney(value) {
      return value.toFixed(2) + " " + currency;
    }

    function validateRange(value, min, max) {
      if (!Number.isFinite(value) || value <= 0) {
        return false;
      }
      if (min != null && value < min) {
        return false;
      }
      if (max != null && value > max) {
        return false;
      }
      return true;
    }

    function update() {
      var w = Number(widthInput.value);
      var h = Number(heightInput.value);

      if (!validateRange(w, minW, maxW) || !validateRange(h, minH, maxH)) {
        areaDisplay.textContent = "0.00 m²";
        totalDisplay.textContent = formatMoney(0);
        if (areaHidden) {
          areaHidden.value = "";
        }
        return;
      }

      var area = w * h;
      var total = area * baseM2Price;

      areaDisplay.textContent = area.toFixed(2) + " m²";
      totalDisplay.textContent = formatMoney(total);
      if (areaHidden) {
        areaHidden.value = area.toFixed(4);
      }
    }

    widthInput.addEventListener("input", update);
    heightInput.addEventListener("input", update);

    // Basic client-side validation on form submit.
    var form = widthInput.closest("form");
    if (form) {
      form.addEventListener("submit", function (e) {
        var w = Number(widthInput.value);
        var h = Number(heightInput.value);

        if (!validateRange(w, minW, maxW) || !validateRange(h, minH, maxH)) {
          e.preventDefault();
          alert("Lütfen geçerli bir en/boy değeri girin.");
        }
      });
    }

    update();
  });
})();

