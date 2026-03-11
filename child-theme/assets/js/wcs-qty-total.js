;(function () {
  if (typeof window === 'undefined') {
    return;
  }

  function initQtyTotal() {
    var priceBlock = document.querySelector('.single-product .wcs-product-card__price-block');
    if (!priceBlock) return;

    var unitPrice = parseFloat(priceBlock.getAttribute('data-wcs-unit-price') || '0');
    if (!unitPrice) return;

    var totalEl = priceBlock.querySelector('.wcs-product-card__price-total-value');
    if (!totalEl) return;

    var qtyInput = document.querySelector('.single-product form.cart input.qty');
    if (!qtyInput) return;

    function format(value) {
      if (window.wcsQtyTotal && typeof window.wcsQtyTotal.format === 'function') {
        return window.wcsQtyTotal.format(value);
      }
      // Basit fallback: 2 ondalık + para birimi sembolü.
      var currency = (window.wcsQtyTotal && window.wcsQtyTotal.currency) || '';
      return value.toFixed(2) + (currency ? ' ' + currency : '');
    }

    function updateTotal() {
      var qty = parseFloat(qtyInput.value || '1');
      if (!qty || qty < 1) qty = 1;
      var total = unitPrice * qty;
      totalEl.textContent = format(total);
    }

    qtyInput.addEventListener('input', updateTotal);
    qtyInput.addEventListener('change', updateTotal);

    // İlk yüklemede de çalıştır.
    updateTotal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQtyTotal);
  } else {
    initQtyTotal();
  }
})();

