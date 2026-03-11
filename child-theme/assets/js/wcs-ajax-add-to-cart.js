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
        var data = response;

        if (!data) {
          return;
        }

        // Woo bazen string dönebilir; JSON'a çevirmeyi dene.
        if (typeof data === 'string') {
          try {
            data = JSON.parse(data);
          } catch (e) {
            // Parse edilemediyse sepet sayfasına yönlendir.
            if (window.wcsAjaxAddToCart && wcsAjaxAddToCart.cartUrl) {
              window.location = wcsAjaxAddToCart.cartUrl;
            } else if (window.wc_add_to_cart_params && wc_add_to_cart_params.cart_url) {
              window.location = wc_add_to_cart_params.cart_url;
            }
            return;
          }
        }

        if (data.error && data.product_url) {
          window.location = data.product_url;
          return;
        }

        if (data.fragments) {
          $.each(data.fragments, function (key, value) {
            $(key).replaceWith(value);
          });
        }

        if (data.cart_hash) {
          $(document.body).trigger('wc_fragments_refreshed');
        }

        $(document.body).trigger('added_to_cart', [data.fragments || {}, data.cart_hash, $form]);
      })
      .fail(function (jqXHR, textStatus) {
        // Geliştirici için konsola detay yaz, kullanıcıya basit mesaj göster.
        if (window.console && console.error) {
          console.error('wcs AJAX add to cart failed:', textStatus, jqXHR);
        }
        window.alert('Ürün sepete eklenirken bir hata oluştu. Lütfen tekrar deneyin.');
      })
      .always(function () {
        $button.prop('disabled', false).removeClass('loading');
      });
  });
})(window.jQuery);

