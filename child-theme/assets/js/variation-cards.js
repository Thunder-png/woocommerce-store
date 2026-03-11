document.addEventListener('DOMContentLoaded', function () {
  var productForms = document.querySelectorAll('.single-product form.variations_form');

  if (!productForms.length) {
    return;
  }

  productForms.forEach(function (form) {
    var variationCards = form.closest('.wcs-product-card');

    if (!variationCards) {
      variationCards = document.querySelector('.wcs-product-card');
    }

    if (!variationCards) {
      return;
    }

    var cards = variationCards.querySelectorAll('.wcs-product-card__variation');

    if (!cards.length) {
      return;
    }

    function activateCard(card) {
      cards.forEach(function (c) {
        c.classList.remove('is-active');
      });

      card.classList.add('is-active');

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
        var value = attrs[key];
        var select = form.querySelector('select[name="' + key + '"]');

        if (!select) {
          return;
        }

        select.value = value;

        var event = new Event('change', { bubbles: true });
        select.dispatchEvent(event);
      });
    }

    cards.forEach(function (card) {
      card.addEventListener('click', function () {
        activateCard(card);
      });
    });

    // Başlangıçta ilk kartı seçili yap.
    activateCard(cards[0]);
  });
});

