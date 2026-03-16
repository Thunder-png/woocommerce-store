<?php
/**
 * My Account — Ana wrapper template override.
 * WooCommerce core: woocommerce/templates/myaccount/my-account.php
 *
 * Astra'nın layout sistemi .woocommerce div'ini bozuyor.
 * Kendi .wcs-ma-layout wrapper'ımızla grid kontrolünü elimize alıyoruz.
 *
 * @package WooCommerce_Store_Child
 */
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_account_navigation' );  // navigation.php → .wcs-ma-nav
?>

<div class="woocommerce-MyAccount-content wcs-ma-content">
    <?php
    do_action( 'woocommerce_account_content' );  // dashboard, orders, vb.
    ?>
</div>
