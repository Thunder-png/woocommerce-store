(function () {
  const widthInput = document.getElementById("wcs-width");
  const heightInput = document.getElementById("wcs-height");
  const areaOutput = document.getElementById("wcs-area");
  const priceOutput = document.getElementById("wcs-price");
  const vatOutput = document.getElementById("wcs-vat");
  const totalOutput = document.getElementById("wcs-total");

  if (!widthInput || !heightInput || !areaOutput || !priceOutput || !vatOutput || !totalOutput) {
    return;
  }

  const fallbackPricePerM2 = Number((window.wcsCalculator && window.wcsCalculator.pricePerM2) || 0);
  const vatRate = Number((window.wcsCalculator && window.wcsCalculator.vatRate) || 0.20);
  const currency = (window.wcsCalculator && window.wcsCalculator.currency) || "";

  const format = (value) => `${value.toFixed(2)} ${currency}`.trim();

  function normalize(text) {
    return String(text || "")
      .toLowerCase()
      .replace(/\s+/g, "")
      .trim();
  }

  function resolvePricePerM2() {
    const form = document.querySelector(".single-product form.variations_form");
    if (!form) {
      return fallbackPricePerM2;
    }

    const thicknessSelect = form.querySelector('select[name^="attribute_pa_ip-kalinligi"]');
    const meshSelect = form.querySelector('select[name^="attribute_pa_goz-araligi"]');
    const colorSelect = form.querySelector('select[name^="attribute_pa_renk"]');

    const thicknessText =
      thicknessSelect && thicknessSelect.options[thicknessSelect.selectedIndex]
        ? thicknessSelect.options[thicknessSelect.selectedIndex].text
        : "";
    const meshText =
      meshSelect && meshSelect.options[meshSelect.selectedIndex]
        ? meshSelect.options[meshSelect.selectedIndex].text
        : "";
    const colorText =
      colorSelect && colorSelect.options[colorSelect.selectedIndex]
        ? colorSelect.options[colorSelect.selectedIndex].text
        : "";

    const t = normalize(thicknessText); // örn: "15mm", "2mm", "4mm"
    const m = normalize(meshText); // örn: "2x2", "4x4", "5x5"
    const c = normalize(colorText); // örn: "beyaz", "siyah", "gri", "standartrenk"

    // Renk grubu tespiti.
    var colorGroup = "standard";
    if (c.indexOf("siyah") !== -1 || c.indexOf("gri") !== -1) {
      colorGroup = "black";
    } else if (c.indexOf("renkli") !== -1) {
      colorGroup = "colored";
    }

    // Temel tablo (renkten bağımsız fiyatlar).
    var basePrice = null;

    if (t === "15mm" && m === "2x2") {
      basePrice = 45;
    } else if (t === "2mm" && m === "4x4") {
      basePrice = 50;
    } else if (t === "25mm" && m === "4x4") {
      basePrice = 55;
    } else if (t === "3mm" && m === "4x4") {
      basePrice = 60;
    } else if (t === "4mm" && m === "4x4") {
      basePrice = 70;
    } else if (t === "6mm" && m === "10x10") {
      basePrice = 70;
    } else if (t === "2mm" && m === "13x13") {
      basePrice = 52;
    } else if (t === "4mm" && m === "12x12") {
      basePrice = 60;
    } else if (t === "4mm" && m === "5x5") {
      basePrice = 100;
    }

    // Renkli ve siyah/gri için override'lar.
    if (t === "4mm" && m === "5x5" && colorGroup === "colored") {
      // 4 mm 5x5 = 105 - Renkli
      basePrice = 105;
    }

    if (t === "15mm" && m === "2x2" && colorGroup === "black") {
      // 1.5 mm 2x2 = 50 - Siyah/Gri
      basePrice = 50;
    }

    if (t === "2mm" && m === "4x4" && colorGroup === "black") {
      // 2 mm 4x4 = 70 - Siyah
      basePrice = 70;
    }

    if (t === "3mm" && m === "4x4" && colorGroup === "black") {
      // 3 mm 4x4 = 75 - Siyah
      basePrice = 75;
    }

    if (t === "6mm" && m === "10x10" && colorGroup === "black") {
      // 6 mm 10x10 = 95 - Siyah
      basePrice = 95;
    }

    if (basePrice === null) {
      return fallbackPricePerM2;
    }

    return basePrice;
  }

  function calculate() {
    const width = Number(widthInput.value);
    const height = Number(heightInput.value);

    if (!Number.isFinite(width) || !Number.isFinite(height) || width <= 0 || height <= 0) {
      areaOutput.textContent = "0.00 m²";
      priceOutput.textContent = format(0);
      vatOutput.textContent = format(0);
      totalOutput.textContent = format(0);
      return;
    }

    const pricePerM2 = resolvePricePerM2();
    const area = width * height;
    const price = area * pricePerM2;
    const vat = price * vatRate;
    const total = price + vat;

    areaOutput.textContent = `${area.toFixed(2)} m²`;
    priceOutput.textContent = format(price);
    vatOutput.textContent = format(vat);
    totalOutput.textContent = format(total);
  }

  widthInput.addEventListener("input", calculate);
  heightInput.addEventListener("input", calculate);

  // Attribute seçimi değiştiğinde de fiyatı güncelle.
  var form = document.querySelector(".single-product form.variations_form");
  if (form) {
    var attrSelects = form.querySelectorAll('select[name^="attribute_"]');
    attrSelects.forEach(function (select) {
      select.addEventListener("change", calculate);
    });
  }
})();
