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
      var sizeKeys = ['attribute_pa_olcu', 'attribute_pa_en-boy-orani', 'attribute_pa_mukavemet'];

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
          // Sadece ölçü ve mukavemet alanlarını otomatik doldur.
          if (sizeKeys.indexOf(key) === -1) {
            return;
          }

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
    }

    // Ölçü ve mukavemet satırlarını gizle, diğerleri (İp Kalınlığı, Göz Aralığı, Renk)
    // kullanıcıya dropdown olarak görünsün.
    var variationRows = form.querySelectorAll('table.variations tr');

    variationRows.forEach(function (row) {
      var select = row.querySelector('select[name^="attribute_pa_"]');

      if (!select) {
        return;
      }

      var name = select.name;

      if (name === 'attribute_pa_olcu' || name === 'attribute_pa_en-boy-orani' || name === 'attribute_pa_mukavemet') {
        row.style.display = 'none';
      }
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

