document.addEventListener('DOMContentLoaded', function () {
  var productForms = document.querySelectorAll('.single-product form.variations_form');

  if (!productForms.length) {
    return;
  }

  productForms.forEach(function (form) {
    var cardRoot = form.closest('.wcs-product-card') || document.querySelector('.wcs-product-card');

    if (!cardRoot) {
      return;
    }

    var cards = Array.from(cardRoot.querySelectorAll('.wcs-product-card__variation'));
    var mainPrice = cardRoot.querySelector('.wcs-product-card__price-current');

    function setCardState(activeCard) {
      cards.forEach(function (card) {
        var isActive = card === activeCard;
        card.classList.toggle('is-active', isActive);
        card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }

    function syncAttributesToForm(attrs) {
      if (!attrs || typeof attrs !== 'object') {
        return;
      }

      Object.keys(attrs).forEach(function (selectName) {
        var select = form.querySelector('select[name="' + selectName + '"]');

        if (!select) {
          return;
        }

        var nextValue = attrs[selectName];

        if (typeof nextValue !== 'string' || !nextValue.length) {
          return;
        }

        if (select.value !== nextValue) {
          select.value = nextValue;
          select.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    }

    function activateCard(card) {
      var raw = card.getAttribute('data-attributes');
      var attrs = null;

      if (raw) {
        try {
          attrs = JSON.parse(raw);
        } catch (error) {
          attrs = null;
        }
      }

      setCardState(card);
      syncAttributesToForm(attrs);

      var cardPrice = card.querySelector('.wcs-product-card__variation-price');
      if (mainPrice && cardPrice && cardPrice.innerHTML.trim()) {
        mainPrice.innerHTML = cardPrice.innerHTML;
      }
    }

    cards.forEach(function (card) {
      card.addEventListener('click', function () {
        activateCard(card);
      });
    });

    if (cards.length) {
      activateCard(cards[0]);
    }

    form.addEventListener('found_variation', function (event) {
      var variation = event.detail && event.detail[0] ? event.detail[0] : null;

      if (!variation || !variation.variation_id) {
        return;
      }

      cards.forEach(function (card) {
        var id = Number(card.getAttribute('data-variation-id'));
        if (id === Number(variation.variation_id)) {
          setCardState(card);
        }
      });

      if (mainPrice && variation.price_html) {
        mainPrice.innerHTML = variation.price_html;
      }
    });

    var variationRows = form.querySelectorAll('table.variations tr');
    variationRows.forEach(function (row) {
      var select = row.querySelector('select[name^="attribute_"]');
      if (select) {
        row.style.display = 'none';
      }
    });

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
