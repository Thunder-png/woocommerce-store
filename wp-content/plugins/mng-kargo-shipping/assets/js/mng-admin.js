(function ($) {
    'use strict';

    function showMessage($box, text, isError) {
        var $msg = $box.find('.mng-ajax-message');
        $msg.removeClass('notice-success notice-error').addClass(isError ? 'notice notice-error' : 'notice notice-success');
        $msg.text(text).show();
        setTimeout(function () { $msg.fadeOut(); }, 4000);
    }

    $(document).on('click', '.mng-create-shipment', function () {
        var $btn = $(this);
        var orderId = $btn.data('order-id');
        var $box = $btn.closest('#mng_kargo_shipment');
        $btn.prop('disabled', true);
        $.post(mngKargoAdmin.ajaxUrl, {
            action: 'mng_kargo_create_shipment',
            nonce: mngKargoAdmin.nonce,
            order_id: orderId
        }).done(function (r) {
            if (r.success) {
                showMessage($box, r.data.message, false);
                location.reload();
            } else {
                showMessage($box, r.data && r.data.message ? r.data.message : 'Hata', true);
                $btn.prop('disabled', false);
            }
        }).fail(function () {
            showMessage($box, 'İstek başarısız.', true);
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.mng-refresh-tracking', function () {
        var $btn = $(this);
        var orderId = $btn.data('order-id');
        var $box = $btn.closest('#mng_kargo_shipment');
        $btn.prop('disabled', true);
        $.post(mngKargoAdmin.ajaxUrl, {
            action: 'mng_kargo_refresh_tracking',
            nonce: mngKargoAdmin.nonce,
            order_id: orderId
        }).done(function (r) {
            if (r.success) {
                showMessage($box, r.data.message, false);
                location.reload();
            } else {
                showMessage($box, r.data && r.data.message ? r.data.message : 'Hata', true);
            }
            $btn.prop('disabled', false);
        }).fail(function () {
            showMessage($box, 'İstek başarısız.', true);
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.mng-cancel-shipment', function () {
        if (!confirm('Kargo gönderisini iptal etmek istediğinize emin misiniz?')) return;
        var $btn = $(this);
        var orderId = $btn.data('order-id');
        var $box = $btn.closest('#mng_kargo_shipment');
        $btn.prop('disabled', true);
        $.post(mngKargoAdmin.ajaxUrl, {
            action: 'mng_kargo_cancel_shipment',
            nonce: mngKargoAdmin.nonce,
            order_id: orderId
        }).done(function (r) {
            if (r.success) {
                showMessage($box, r.data.message, false);
                location.reload();
            } else {
                showMessage($box, r.data && r.data.message ? r.data.message : 'Hata', true);
            }
            $btn.prop('disabled', false);
        }).fail(function () {
            showMessage($box, 'İstek başarısız.', true);
            $btn.prop('disabled', false);
        });
    });
})(jQuery);
