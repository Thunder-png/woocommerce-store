<?php
/**
 * Order metabox view.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_class = ! empty( $meta['last_error'] ) ? 'demw-badge demw-badge-error' : 'demw-badge demw-badge-ok';
$last_response_preview = function_exists( 'mb_substr' ) ? mb_substr( (string) $meta['last_response'], 0, 400 ) : substr( (string) $meta['last_response'], 0, 400 );
$current_stage = ( ! empty( $meta['tracking_number'] ) || ! empty( $meta['shipment_id'] ) ) ? __( 'Stage 3 completed (barcode created)', 'dhl-ecommerce-mng-woocommerce' ) : ( ! empty( $meta['order_created'] ) ? __( 'Stage 2 completed (recipient + order synced)', 'dhl-ecommerce-mng-woocommerce' ) : __( 'Stage 1 pending', 'dhl-ecommerce-mng-woocommerce' ) );
?>
<div class="demw-metabox">
	<?php if ( is_array( $notice ) && ! empty( $notice['message'] ) ) : ?>
		<div class="demw-inline-notice demw-inline-notice-<?php echo esc_attr( 'error' === $notice['type'] ? 'error' : 'success' ); ?>">
			<?php echo esc_html( $notice['message'] ); ?>
		</div>
	<?php endif; ?>

	<p>
		<strong><?php echo esc_html__( 'Shipment Status', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong>
		<span class="<?php echo esc_attr( $status_class ); ?>">
			<?php echo esc_html( $meta['last_status'] ? $meta['last_status'] : __( 'N/A', 'dhl-ecommerce-mng-woocommerce' ) ); ?>
		</span>
	</p>
	<p><strong><?php echo esc_html__( 'Reference ID', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong> <?php echo esc_html( $meta['reference_id'] ? $meta['reference_id'] : '-' ); ?></p>
	<p><strong><?php echo esc_html__( 'Shipment Stage', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong> <?php echo esc_html( $current_stage ); ?></p>
	<p><strong><?php echo esc_html__( 'Shipment ID', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong> <?php echo esc_html( $meta['shipment_id'] ? $meta['shipment_id'] : '-' ); ?></p>
	<p><strong><?php echo esc_html__( 'Tracking Number', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong> <?php echo esc_html( $meta['tracking_number'] ? $meta['tracking_number'] : '-' ); ?></p>
	<?php if ( ! empty( $meta['order_created'] ) && empty( $meta['tracking_number'] ) && ! empty( $meta['order_synced_at'] ) ) : ?>
		<p><em><?php echo esc_html__( 'Stage 1-2 were completed. Use Create Shipment again to run barcode generation step.', 'dhl-ecommerce-mng-woocommerce' ); ?></em></p>
	<?php endif; ?>
	<p>
		<strong><?php echo esc_html__( 'Label', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong>
		<?php if ( ! empty( $meta['label_url'] ) ) : ?>
			<a href="<?php echo esc_url( $meta['label_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Open', 'dhl-ecommerce-mng-woocommerce' ); ?></a>
		<?php else : ?>
			<?php echo esc_html__( 'N/A', 'dhl-ecommerce-mng-woocommerce' ); ?>
		<?php endif; ?>
	</p>
	<p><strong><?php echo esc_html__( 'Last Synced', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong> <?php echo esc_html( $meta['last_synced_at'] ? $meta['last_synced_at'] : '-' ); ?></p>

	<?php if ( ! empty( $meta['last_error'] ) ) : ?>
		<p class="demw-error-text"><strong><?php echo esc_html__( 'Last Error', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong> <?php echo esc_html( $meta['last_error'] ); ?></p>
	<?php endif; ?>

	<p><strong><?php echo esc_html__( 'Last API Response Summary', 'dhl-ecommerce-mng-woocommerce' ); ?>:</strong></p>
	<div class="demw-response-preview"><?php echo esc_html( $last_response_preview ); ?></div>

	<div class="demw-action-grid">
		<a class="button" href="<?php echo esc_url( $actions['test_connection'] ); ?>"><?php echo esc_html__( 'Test Connection', 'dhl-ecommerce-mng-woocommerce' ); ?></a>
		<a class="button button-primary" href="<?php echo esc_url( $actions['create_shipment'] ); ?>"><?php echo esc_html__( 'Create Shipment', 'dhl-ecommerce-mng-woocommerce' ); ?></a>
		<a class="button" href="<?php echo esc_url( $actions['query_status'] ); ?>"><?php echo esc_html__( 'Query Shipment Status', 'dhl-ecommerce-mng-woocommerce' ); ?></a>
		<a class="button" href="<?php echo esc_url( $actions['get_label'] ); ?>"><?php echo esc_html__( 'Get Label', 'dhl-ecommerce-mng-woocommerce' ); ?></a>
	</div>
</div>
