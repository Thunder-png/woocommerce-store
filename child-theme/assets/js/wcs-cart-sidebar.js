(function ($) {
  if (typeof $ === 'undefined') {
    return;
  }

  function openSidebar($sidebar, $overlay) {
    $sidebar.addClass('is-open');
    $overlay.addClass('is-visible');
    $('body').addClass('wcs-cart-sidebar-open');
  }

  function closeSidebar($sidebar, $overlay) {
    $sidebar.removeClass('is-open');
    $overlay.removeClass('is-visible');
    $('body').removeClass('wcs-cart-sidebar-open');
  }

  $(function () {
    var $sidebar = $('.wcs-cart-sidebar');
    var $overlay = $('.wcs-cart-sidebar-overlay');
    var $cartToggle = $('[data-wcs-cart-toggle=\"true\"]');

    // Header sepet ikonundan sidebar açma (sidebar varsa).
    if ($cartToggle.length) {
      $cartToggle.on('click', function (e) {
        if ($sidebar.length && $overlay.length) {
          e.preventDefault();
          openSidebar($sidebar, $overlay);
        }
      });
    }

    if (!$sidebar.length || !$overlay.length) {
      return;
    }

    // Kapatma butonu ve overlay tıklaması.
    $sidebar.on('click', '.wcs-cart-sidebar__close', function (e) {
      e.preventDefault();
      closeSidebar($sidebar, $overlay);
    });

    $overlay.on('click', function () {
      closeSidebar($sidebar, $overlay);
    });

    // ESC ile kapatma.
    $(document).on('keyup', function (e) {
      if (e.key === 'Escape') {
        closeSidebar($sidebar, $overlay);
      }
    });

    // AJAX ile gerçekten sepete ekleme olduğunda sidebari aç.
    $(document.body).on('added_to_cart', function () {
      openSidebar($sidebar, $overlay);
    });

    // Non-AJAX add to cart sonrasında URL'de added-to-cart parametresi oluşur.
    // Sadece bu durumda otomatik aç; genel woocommerce mesajlarında açma.
    var hasAddedToCartParam = window.location.search.indexOf('added-to-cart=') !== -1;
    if (hasAddedToCartParam) {
      openSidebar($sidebar, $overlay);
    }
  });
})(window.jQuery);

