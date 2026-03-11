document.addEventListener('DOMContentLoaded', function () {
  var productForms = document.querySelectorAll('.single-product form.variations_form');

  if (!productForms.length) {
    return;
  }

  productForms.forEach(function (form) {
    var cardRoot = form.closest('.wcs-product-card');

    if (!cardRoot) {
      cardRoot = document.querySelector('.wcs-product-card');
    }

    if (!cardRoot) {
      return;
    }

    var cards = cardRoot.querySelectorAll('.wcs-product-card__variation');

    if (cards.length) {
      var activeCard = null;

      function activateCard(card) {
        cards.forEach(function (c) {
          c.classList.remove('is-active');
        });

        card.classList.add('is-active');
        activeCard = card;

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

        // Update main price block to selected variation price.
        var mainPrice = cardRoot.querySelector('.wcs-product-card__price-current');
        var cardPrice = card.querySelector('.wcs-product-card__variation-price');

        if (mainPrice && cardPrice) {
          mainPrice.innerHTML = cardPrice.innerHTML;
        }
      }

      cards.forEach(function (card) {
        card.addEventListener('click', function () {
          activateCard(card);
        });
      });

      // Başlangıçta ilk kartı seçili yap.
      activateCard(cards[0]);
    }

    // Varyasyon tablosunu tamamen gizle; seçim sadece kartlardan yapılacak.
    var variationRows = form.querySelectorAll('table.variations tr');

    variationRows.forEach(function (row) {
      var select = row.querySelector('select[name^="attribute_pa_"]');

      if (!select) {
        return;
      }

      row.style.display = 'none';
    });

    // m² hesaplayıcıyı isteğe bağlı aç/kapat.
    var toggle = cardRoot.querySelector('.wcs-calculator-toggle');
    var calculator = cardRoot.querySelector('.wcs-calculator');

    if (toggle && calculator) {
      // Başlangıçta gizli kalsın.
      calculator.classList.remove('is-open');

      toggle.addEventListener('click', function () {
        var isOpen = calculator.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    }
  });
});

