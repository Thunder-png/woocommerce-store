document.addEventListener('DOMContentLoaded', function () {
  var forms = document.querySelectorAll('.single-product form.variations_form');

  if (!forms.length) {
    return;
  }

  var $ = window.jQuery || null;

  forms.forEach(function (form) {
    var cardRoot = form.closest('.wcs-product-card') || document.querySelector('.wcs-product-card');
    if (!cardRoot) {
      return;
    }

    var cards = Array.from(cardRoot.querySelectorAll('.wcs-product-card__variation'));
    if (!cards.length) {
      return;
    }

    var mainPrice = cardRoot.querySelector('.wcs-product-card__price-current');

    function triggerChange(select) {
      select.dispatchEvent(new Event('change', { bubbles: true }));
      if ($) {
        $(select).trigger('change');
      }
    }

    function setActiveCard(activeCard) {
      cards.forEach(function (card) {
        var isActive = card === activeCard;
        card.classList.toggle('is-active', isActive);
        card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }

    function applyCardAttributes(card) {
      var raw = card.getAttribute('data-attributes');
      if (!raw) {
        return;
      }

      var attrs;
      try {
        attrs = JSON.parse(raw);
      } catch (e) {
        return;
      }

      Object.keys(attrs).forEach(function (key) {
        var select = form.querySelector('select[name="' + key + '"]');
        if (!select) {
          return;
        }

        var value = attrs[key];
        if (typeof value !== 'string' || !value.length) {
          return;
        }

        if (select.value !== value) {
          select.value = value;
          triggerChange(select);
        }
      });

      // Seçilen kartın variation_id değerini gizli inputa yaz.
      var variationIdInput = form.querySelector('input[name="variation_id"]');
      var cardVariationId = card.getAttribute('data-variation-id');
      if (variationIdInput && cardVariationId) {
        variationIdInput.value = cardVariationId;
      }

      var cardPrice = card.querySelector('.wcs-product-card__variation-price');
      if (mainPrice && cardPrice && cardPrice.innerHTML.trim()) {
        mainPrice.innerHTML = cardPrice.innerHTML;
      }
    }

    cards.forEach(function (card) {
      card.addEventListener('click', function () {
        setActiveCard(card);
        applyCardAttributes(card);
      });
    });

    // Varsayılan olarak ilk kartı seç.
    setActiveCard(cards[0]);
    applyCardAttributes(cards[0]);

    // WooCommerce dropdown tablosunu gizle (sadece kartlar görünsün).
    var rows = form.querySelectorAll('table.variations tr');
    rows.forEach(function (row) {
      var select = row.querySelector('select[name^="attribute_"]');
      if (select) {
        row.style.display = 'none';
      }
    });

    // m² hesaplayıcı toggle
    var toggle = cardRoot.querySelector('.wcs-calculator-toggle');
    var calculator = cardRoot.querySelector('.wcs-calculator');

    if (toggle && calculator) {
      calculator.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');

      toggle.addEventListener('click', function () {
        var isOpen = calculator.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    }
  });
});

