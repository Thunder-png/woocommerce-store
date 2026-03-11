(function ($) {
  if (typeof $ === 'undefined') {
    return;
  }

  function initAttributeButtons(form) {
    var $form = $(form);

    if (!$form.length || $form.data('wcsAttrButtonsInit')) {
      return;
    }

    var $rows = $form.find('table.variations tr');
    if (!$rows.length) {
      return;
    }

    $rows.each(function () {
      var $row = $(this);
      var $select = $row.find('select[name^="attribute_"]');

      if (!$select.length || $select.hasClass('wcs-attr-select-original')) {
        return;
      }

      var $buttonsWrap = $('<div class="wcs-attr-buttons" />');

      $select.find('option').each(function () {
        var $opt = $(this);
        var value = $opt.val();
        var label = $.trim($opt.text());

        // Boş / placeholder option'u atla.
        if (!value || !label) {
          return;
        }

        var $btn = $('<button type="button" class="wcs-attr-button" />')
          .attr('data-value', value)
          .text(label);

        if ($select.val() === value) {
          $btn.addClass('is-selected');
        }

        $buttonsWrap.append($btn);
      });

      if (!$buttonsWrap.children().length) {
        return;
      }

      // Butonlara click davranışı: select.value yaz + change tetikle.
      $buttonsWrap.on('click', '.wcs-attr-button', function (event) {
        event.preventDefault();

        var $btn = $(this);
        var value = $btn.data('value');

        if (!value) {
          return;
        }

        // Aynı seçili butona tekrar tıklamada da aynı değeri yazıp change tetikleyelim.
        $select.val(value).trigger('change');

        $btn
          .addClass('is-selected')
          .siblings('.wcs-attr-button')
          .removeClass('is-selected');
      });

      // Select'i saklamak için sınıf ekle ve butonları hemen arkasına yerleştir.
      $select.addClass('wcs-attr-select-original').after($buttonsWrap);
    });

    // "Tümünü sıfırla" linki tıklandığında butonların seçili durumu temizlensin.
    $form.on('click', '.reset_variations', function () {
      $form.find('.wcs-attr-button.is-selected').removeClass('is-selected');
    });

    $form.data('wcsAttrButtonsInit', true);
  }

  // WooCommerce variations_form hazır olduğunda attribute butonlarını başlat.
  $(document).on('woocommerce_variation_form', '.single-product form.variations_form', function () {
    initAttributeButtons(this);
  });
})(window.jQuery);

