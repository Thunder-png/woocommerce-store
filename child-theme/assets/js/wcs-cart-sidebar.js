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

    // AJAX ile sepete ekleme olduğunda sidebari aç.
    $(document.body).on('added_to_cart wc_fragments_refreshed', function () {
      openSidebar($sidebar, $overlay);
    });

    // Sayfa yenilendikten sonra WooCommerce mesajı varsa (non-AJAX add to cart)
    // sidebari otomatik aç.
    var $notice = $('.woocommerce-message');
    if ($notice.length) {
      openSidebar($sidebar, $overlay);
    }
  });
})(window.jQuery);

