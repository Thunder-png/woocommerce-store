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
    var variationIdInput = form.querySelector('input[name="variation_id"]');
    var jQueryInstance = window.jQuery;

    function getVariationSelects() {
      return Array.from(form.querySelectorAll('select[name^="attribute_"]'));
    }

    function getProductVariations() {
      var raw = form.getAttribute('data-product_variations');

      if (!raw || raw === 'false') {
        return [];
      }

      try {
        var parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        return [];
      }
    }

    function getVariationDataById(variationId) {
      var targetId = Number(variationId);

      return getProductVariations().find(function (variation) {
        return Number(variation.variation_id) === targetId;
      }) || null;
    }

    function triggerSelectChange(select) {
      // Native event for vanilla listeners.
      select.dispatchEvent(new Event('change', { bubbles: true }));

      // jQuery trigger for WooCommerce variation form handlers.
      if (jQueryInstance) {
        jQueryInstance(select).trigger('change');
      }
    }

    function setCardState(activeCard) {
      cards.forEach(function (card) {
        var isActive = card === activeCard;
        card.classList.toggle('is-active', isActive);
        card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }

    function fillMissingAttributes() {
      getVariationSelects().forEach(function (select) {
        if (select.value) {
          return;
        }

        var fallbackOption = Array.from(select.options).find(function (option) {
          return option.value && !option.disabled;
        });

        if (fallbackOption) {
          select.value = fallbackOption.value;
          triggerSelectChange(select);
        }
      });
    }


    function syncInlineVariationMeta(variation) {
      if (!variation || typeof variation !== 'object') {
        return;
      }

      var variationPriceWrap = form.querySelector('.woocommerce-variation-price');
      if (variationPriceWrap && variation.price_html) {
        variationPriceWrap.innerHTML = variation.price_html;
      }

      var availabilityWrap = form.querySelector('.woocommerce-variation-availability');
      if (availabilityWrap && variation.availability_html) {
        availabilityWrap.innerHTML = variation.availability_html;
      }
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
          triggerSelectChange(select);
        }
      });

      // Visible kart seçimi dışında kalan attribute'ler için validasyon fallback.
      fillMissingAttributes();
    }

    function activateCard(card) {
      var rawCardAttrs = card.getAttribute('data-attributes');
      var cardAttrs = null;
      var variationId = card.getAttribute('data-variation-id');
      var variationData = getVariationDataById(variationId);
      var attrs = variationData && variationData.attributes ? variationData.attributes : null;

      if (rawCardAttrs) {
        try {
          cardAttrs = JSON.parse(rawCardAttrs);
        } catch (error) {
          cardAttrs = null;
        }
      }

      // Variation JSON bulunamazsa, kart üzerindeki attribute payload'u kullan.
      if (!attrs) {
        attrs = cardAttrs;
      }

      setCardState(card);
      syncAttributesToForm(attrs);

      if (variationIdInput && variationId) {
        variationIdInput.value = variationId;
      }

      if (variationData) {
        syncInlineVariationMeta(variationData);

        if (jQueryInstance) {
          jQueryInstance(form).trigger('found_variation', [variationData]);
        }
      }

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

    if (jQueryInstance) {
      jQueryInstance(form).on('found_variation.wc-variation-form', function (_, variation) {
        if (!variation || !variation.variation_id) {
          return;
        }

        cards.forEach(function (card) {
          var id = Number(card.getAttribute('data-variation-id'));
          if (id === Number(variation.variation_id)) {
            setCardState(card);
          }
        });

        if (variationIdInput) {
          variationIdInput.value = variation.variation_id;
        }

        if (mainPrice && variation.price_html) {
          mainPrice.innerHTML = variation.price_html;
        }

        syncInlineVariationMeta(variation);
      });
    }

    form.addEventListener('submit', function () {
      fillMissingAttributes();

      // Submit öncesi aktif karttan variation_id garanti et.
      var activeCard = cardRoot.querySelector('.wcs-product-card__variation.is-active');
      if (variationIdInput && activeCard) {
        var selectedId = activeCard.getAttribute('data-variation-id');
        if (selectedId) {
          variationIdInput.value = selectedId;
        }
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
