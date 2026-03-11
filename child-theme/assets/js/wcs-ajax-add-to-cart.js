(function ($) {
  if (typeof $ === 'undefined' || typeof window.wc_add_to_cart_params === 'undefined') {
    return;
  }

  $(document).on('submit.wcsAjax', '.single-product form.cart', function (event) {
    event.preventDefault();

    var $form = $(this);
    var $button = $form.find('.single_add_to_cart_button');

    var url = window.wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart');
    var formData = $form.serialize();

    $button.prop('disabled', true).addClass('loading');

    $.post(url, formData)
      .done(function (response) {
        if (!response) {
          return;
        }

        if (response.error && response.product_url) {
          window.location = response.product_url;
          return;
        }

        if (response.fragments) {
          $.each(response.fragments, function (key, value) {
            $(key).replaceWith(value);
          });
        }

        if (response.cart_hash) {
          $(document.body).trigger('wc_fragments_refreshed');
        }

        $(document.body).trigger('added_to_cart', [response.fragments || {}, response.cart_hash, $form]);
      })
      .fail(function () {
        // Basit fallback uyarısı.
        window.alert('Ürün sepete eklenirken bir hata oluştu. Lütfen tekrar deneyin.');
      })
      .always(function () {
        $button.prop('disabled', false).removeClass('loading');
      });
  });
})(window.jQuery);

