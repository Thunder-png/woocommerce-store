(function ($) {
  if (typeof $ === 'undefined') {
    return;
  }

  function initCardPicker(form) {
    var $form = $(form);

    // WooCommerce henüz initialize edilmediyse çık.
    if (!$form.length || $form.data('wcsCardsInit')) {
      return;
    }

    var $cardRoot = $form.closest('.wcs-product-card');
    if (!$cardRoot.length) {
      $cardRoot = $('.wcs-product-card').first();
    }

    if (!$cardRoot.length) {
      return;
    }

    var $cards = $cardRoot.find('.wcs-product-card__variation');
    if (!$cards.length) {
      return;
    }

    var $mainPrice = $cardRoot.find('.wcs-product-card__price-current').first();

    function setActiveCard($active) {
      $cards.each(function () {
        var $card = $(this);
        var isActive = $card.is($active);
        $card.toggleClass('is-active', isActive);
        $card.attr('aria-pressed', isActive ? 'true' : 'false');
      });
    }

    function applyCardAttributesAndCheck($card) {
      var raw = $card.attr('data-attributes');
      if (!raw) {
        return;
      }

      var attrs;
      try {
        attrs = JSON.parse(raw);
      } catch (e) {
        return;
      }

      // 1. Tüm select değerlerini sessizce yaz.
      Object.keys(attrs).forEach(function (key) {
        var value = attrs[key];
        if (typeof value !== 'string' || !value.length) {
          return;
        }

        var $select = $form.find('select[name="' + key + '"]');
        if (!$select.length) {
          return;
        }

        if ($select.val() !== value) {
          $select.val(value);
        }
      });

      // 2. WooCommerce'e bir kez değiştiğini bildir.
      $form.trigger('woocommerce_variation_select_change');

      // 3. WooCommerce'e varyasyonları tekrar kontrol ettir.
      $form.trigger('check_variations', ['', false]);

      // 4. Kart fiyatını büyük fiyat alanına yansıt (ekstra görsel senkron).
      var $cardPrice = $card.find('.wcs-product-card__variation-price').first();
      if ($mainPrice.length && $cardPrice.length && $.trim($cardPrice.html()).length) {
        $mainPrice.html($cardPrice.html());
      }
    }

    // Kart tıklamaları.
    $cards.on('click.wcsCards', function () {
      var $card = $(this);
      setActiveCard($card);
      applyCardAttributesAndCheck($card);
    });

    // WooCommerce bir varyasyon bulduğunda kart durumunu ve fiyatı senkronize et.
    $form.on('found_variation.wcsCards', function (event, variation) {
      if (!variation || !variation.variation_id) {
        return;
      }

      var id = String(variation.variation_id);
      var $match = $cards.filter('[data-variation-id="' + id + '"]');
      if ($match.length) {
        setActiveCard($match.eq(0));
      }

      if ($mainPrice.length && variation.price_html) {
        $mainPrice.html(variation.price_html);
      }
    });

    // Başlangıç durumu: ilk kartı seç ve varyasyonları kontrol ettir.
    var $initial = $cards.eq(0);
    if ($initial.length) {
      setActiveCard($initial);
      applyCardAttributesAndCheck($initial);
    }

    // Orijinal Woo dropdown tablosunu gizle (sadece kartlar görünsün).
    $form.find('table.variations tr').each(function () {
      var $row = $(this);
      var hasSelect = $row.find('select[name^="attribute_"]').length > 0;
      if (hasSelect) {
        $row.hide();
      }
    });

    // m² hesaplayıcı toggle
    var $toggle = $cardRoot.find('.wcs-calculator-toggle').first();
    var $calculator = $cardRoot.find('.wcs-calculator').first();

    if ($toggle.length && $calculator.length) {
      $calculator.removeClass('is-open');
      $toggle.attr('aria-expanded', 'false');

      $toggle.on('click.wcsCards', function () {
        var isOpen = $calculator.toggleClass('is-open').hasClass('is-open');
        $toggle.attr('aria-expanded', isOpen ? 'true' : 'false');
      });
    }

    $form.data('wcsCardsInit', true);
  }

  // WooCommerce variations_form hazır olduğunda kart picker'ı başlat.
  $(document).on('woocommerce_variation_form', '.single-product form.variations_form', function () {
    initCardPicker(this);
  });
})(window.jQuery);

