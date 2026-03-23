(function ($) {
  "use strict";

  if (typeof $ === "undefined") {
    return;
  }

  $(document).on("submit", "[data-wcs-cart-coupon-form]", function (event) {
    event.preventDefault();

    var $form = $(this);
    var $input = $form.find("[data-wcs-cart-coupon-input]");
    var $button = $form.find("[data-wcs-cart-coupon-submit]");
    var code = $.trim($input.val() || "");

    if (!code) {
      if (window.wcs_toast) {
        window.wcs_toast.show((window.wcsCartCoupon && window.wcsCartCoupon.missingText) || "Lutfen kupon kodu girin.", "error");
      }
      return;
    }

    $button.prop("disabled", true);

    $.ajax({
      url: (window.wcsCartCoupon && window.wcsCartCoupon.ajaxUrl) || "/?wc-ajax=apply_coupon",
      method: "POST",
      data: {
        coupon_code: code,
        security: (window.wcsCartCoupon && window.wcsCartCoupon.nonce) || ""
      }
    })
      .done(function () {
        if (window.wcs_toast) {
          window.wcs_toast.show((window.wcsCartCoupon && window.wcsCartCoupon.successText) || "Kupon uygulandi.", "success");
        }
        $(document.body).trigger("wc_fragment_refresh");
      })
      .fail(function () {
        if (window.wcs_toast) {
          window.wcs_toast.show((window.wcsCartCoupon && window.wcsCartCoupon.errorText) || "Kupon uygulanamadi.", "error");
        }
      })
      .always(function () {
        $button.prop("disabled", false);
      });
  });
})(window.jQuery);
