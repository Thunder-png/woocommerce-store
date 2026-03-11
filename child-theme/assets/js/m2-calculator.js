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
  let lastValues = null;

  function resolvePricePerM2() {
    const thicknessSelect = document.getElementById("wcs-thickness");
    const meshSelect = document.getElementById("wcs-mesh");
    const colorSelect = document.getElementById("wcs-color");

    if (!thicknessSelect || !meshSelect || !colorSelect) {
      return fallbackPricePerM2;
    }

    const thicknessValue = thicknessSelect.value; // "1.5", "2", "4" ...
    const mesh = meshSelect.value; // "2x2", "4x4", ...
    const rawColor = colorSelect.value || "standard"; // form değeri
    var colorGroup = "standard";

    // Renk grupları:
    // - "black": sadece siyah
    // - "colored": gri, mavi, sarı, turuncu, yeşil vb.
    var rawColorNorm = String(rawColor).toLowerCase();
    if (rawColorNorm.indexOf("siyah") !== -1) {
      colorGroup = "black";
    } else if (
      rawColorNorm.indexOf("gri") !== -1 ||
      rawColorNorm.indexOf("mavi") !== -1 ||
      rawColorNorm.indexOf("sarı") !== -1 ||
      rawColorNorm.indexOf("sari") !== -1 ||
      rawColorNorm.indexOf("turuncu") !== -1 ||
      rawColorNorm.indexOf("yeşil") !== -1 ||
      rawColorNorm.indexOf("yesil") !== -1
    ) {
      colorGroup = "colored";
    }

    const thicknessMm = thicknessValue ? parseFloat(thicknessValue) : null;

    if (!thicknessMm || !mesh) {
      return fallbackPricePerM2;
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

    // Renkli ve siyah/siyah-gri için override'lar.
    if (thicknessMm === 4 && mesh === "5x5" && colorGroup === "colored") {
      // 4 mm 5x5 = 105 - Renkli
      basePrice = 105;
    }

    if (thicknessMm === 1.5 && mesh === "2x2" && colorGroup === "colored") {
      // 1.5 mm 2x2 = 50 - Siyah/Gri (renkli gruba dahil)
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
      lastValues = null;
      areaOutput.textContent = "0.00 m²";
      priceOutput.textContent = format(0);
      vatOutput.textContent = format(0);
      totalOutput.textContent = format(0);
      return;
    }

    const thicknessSelect = document.getElementById("wcs-thickness");
    const meshSelect = document.getElementById("wcs-mesh");
    const colorSelect = document.getElementById("wcs-color");

    const thicknessValue = thicknessSelect ? thicknessSelect.value : "";
    const mesh = meshSelect ? meshSelect.value : "";
    const colorLabel =
      colorSelect && colorSelect.options[colorSelect.selectedIndex]
        ? colorSelect.options[colorSelect.selectedIndex].text
        : "";

    const pricePerM2 = resolvePricePerM2();
    const area = width * height;
    const price = area * pricePerM2;
    const vat = price * vatRate;
    const total = price + vat;

    lastValues = {
      width: width,
      height: height,
      thickness: thicknessValue,
      mesh: mesh,
      colorLabel: colorLabel,
      area: area,
      pricePerM2: pricePerM2,
      total: total,
    };

    areaOutput.textContent = `${area.toFixed(2)} m²`;
    priceOutput.textContent = format(price);
    vatOutput.textContent = format(vat);
    totalOutput.textContent = format(total);
  }

  widthInput.addEventListener("input", calculate);
  heightInput.addEventListener("input", calculate);

  // Hesaplamayı, ip kalınlığı / göz boyutu / renk değişiminde de tetikle.
  ["wcs-thickness", "wcs-mesh", "wcs-color"].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) {
      el.addEventListener("change", calculate);
    }
  });

  // Özel ölçü ile sepete ekle
  var addButton = document.querySelector(".wcs-calculator__add-to-cart");
  if (addButton) {
    addButton.addEventListener("click", function () {
      // Son hesap değerleri yoksa veya eksikse hesaplama yapmayı dene.
      if (!lastValues) {
        calculate();
      }

      if (!lastValues) {
        alert("Lütfen en, boy, ip kalınlığı, göz boyutu ve renk grubunu seçip tekrar deneyin.");
        return;
      }

      if (!lastValues.thickness || !lastValues.mesh) {
        alert("Lütfen ip kalınlığı ve göz boyutunu seçin.");
        return;
      }

      var form = document.querySelector(".single-product form.cart");
      if (!form) {
        return;
      }

      function setHidden(name, value) {
        var input = form.querySelector('input[name="' + name + '"]');
        if (!input) {
          input = document.createElement("input");
          input.type = "hidden";
          input.name = name;
          form.appendChild(input);
        }
        input.value = String(value);
      }

      setHidden("wcs_custom_order", "1");
      setHidden("wcs_width", lastValues.width);
      setHidden("wcs_height", lastValues.height);
      setHidden("wcs_thickness", lastValues.thickness);
      setHidden("wcs_mesh", lastValues.mesh);
      setHidden("wcs_color", lastValues.colorLabel);
      setHidden("wcs_area", lastValues.area.toFixed(2));
      setHidden("wcs_price_per_m2", lastValues.pricePerM2.toFixed(2));
      setHidden("wcs_total", lastValues.total.toFixed(2));

      form.submit();
    });
  }
})();
