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

  const pricePerM2 = Number((window.wcsCalculator && window.wcsCalculator.pricePerM2) || 0);
  const vatRate = Number((window.wcsCalculator && window.wcsCalculator.vatRate) || 0.20);
  const currency = (window.wcsCalculator && window.wcsCalculator.currency) || "";

  const format = (value) => `${value.toFixed(2)} ${currency}`.trim();

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
})();
