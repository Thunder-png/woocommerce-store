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
      .trim();
  }

  function parseThicknessMm(text) {
    var t = normalize(text);
    var match = t.match(/([\d.,]+)/);
    if (!match) {
      return null;
    }
    var num = parseFloat(match[1].replace(",", "."));
    return Number.isFinite(num) ? num : null;
  }

  function parseMesh(text) {
    var t = normalize(text);
    var match = t.match(/(\d+)\s*[x×*]\s*(\d+)/i);
    if (!match) {
      return null;
    }
    return match[1] + "x" + match[2];
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

    const thicknessMm = parseThicknessMm(thicknessText); // örn: 1.5, 2, 4
    const mesh = parseMesh(meshText); // örn: "2x2", "4x4", "5x5"
    const c = normalize(colorText); // örn: "beyaz", "siyah", "gri", "standart renk"

    if (!thicknessMm || !mesh) {
      return fallbackPricePerM2;
    }

    // Renk grubu tespiti.
    var colorGroup = "standard";
    if (c.indexOf("siyah") !== -1 || c.indexOf("gri") !== -1) {
      colorGroup = "black";
    } else if (c.indexOf("renkli") !== -1) {
      colorGroup = "colored";
    }

    // Temel tablo (renkten bağımsız fiyatlar).
    var basePrice = null;
    var key = thicknessMm + "|" + mesh;

    // Temel tablo (renkten bağımsız fiyatlar).
    switch (key) {
      case "1.5|2x2":
        basePrice = 45;
        break;
      case "2|4x4":
        basePrice = 50;
        break;
      case "2.5|4x4":
        basePrice = 55;
        break;
      case "3|4x4":
        basePrice = 60;
        break;
      case "4|4x4":
        basePrice = 70;
        break;
      case "6|10x10":
        basePrice = 70;
        break;
      case "2|13x13":
        basePrice = 52;
        break;
      case "4|12x12":
        basePrice = 60;
        break;
      case "4|5x5":
        basePrice = 100;
        break;
      default:
        basePrice = null;
    }

    // Renkli ve siyah/gri için override'lar.
    if (thicknessMm === 4 && mesh === "5x5" && colorGroup === "colored") {
      // 4 mm 5x5 = 105 - Renkli
      basePrice = 105;
    }

    if (thicknessMm === 1.5 && mesh === "2x2" && colorGroup === "black") {
      // 1.5 mm 2x2 = 50 - Siyah/Gri
      basePrice = 50;
    }

    if (thicknessMm === 2 && mesh === "4x4" && colorGroup === "black") {
      // 2 mm 4x4 = 70 - Siyah
      basePrice = 70;
    }

    if (thicknessMm === 3 && mesh === "4x4" && colorGroup === "black") {
      // 3 mm 4x4 = 75 - Siyah
      basePrice = 75;
    }

    if (thicknessMm === 6 && mesh === "10x10" && colorGroup === "black") {
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
