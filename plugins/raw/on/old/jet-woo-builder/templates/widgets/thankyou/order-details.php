<?php
/**
 * Thank You Page Order Details widget Template.
 */

defined( 'ABSPATH' ) || exit;

$order = jet_woo_builder_template_functions()->get_current_received_order();

$is_refund = ( $order instanceof \WC_Order_Refund )
	|| ( class_exists( '\Automattic\WooCommerce\Admin\Overrides\OrderRefund' ) && $order instanceof \Automattic\WooCommerce\Admin\Overrides\OrderRefund );

if ( $is_refund ) {
	$parent_id = method_exists( $order, 'get_parent_id' ) ? (int) $order->get_parent_id() : 0;

	if ( $parent_id > 0 ) {
		if ( ( $parent_order = wc_get_order( $parent_id ) ) && $parent_order instanceof \WC_Order ) {
			$order = $parent_order;
		}
	}
}

if ( ! ( $order instanceof \WC_Order ) ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads          = $order->get_downloadable_items();
$show_downloads     = $order->has_downloadable_item() && $order->is_download_permitted();
$actions            = function_exists( 'wc_get_account_orders_actions' ) ? wc_get_account_orders_actions( $order ) : array();

unset( $actions['view'] );

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
		<tr>
			<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
		</tr>
		</thead>

		<tbody>
		<?php
		do_action( 'woocommerce_order_details_before_order_table_items', $order );

		foreach ( $order_items as $item_id => $item ) {
			$product = $item->get_product();

			wc_get_template(
				'order/order-details-item.php',
				array(
					'order'              => $order,
					'item_id'            => $item_id,
					'item'               => $item,
					'show_purchase_note' => $show_purchase_note,
					'purchase_note'      => $product ? $product->get_purchase_note() : '',
					'product'            => $product,
				)
			);
		}

		do_action( 'woocommerce_order_details_after_order_table_items', $order );
		?>
		</tbody>

		<tfoot>
		<?php
		foreach ( $order->get_order_item_totals() as $key => $total ) {
			?>
			<tr>
				<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
				<td><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); ?></td>
			</tr>
			<?php
		}
		?>
		<?php if ( $order->get_customer_note() ) : ?>
			<tr>
				<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
				<td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( ! empty( $actions ) ) : ?>
			<tr>
				<th class="order-actions--heading"><?php esc_html_e( 'Actions', 'woocommerce' ); ?>:</th>
				<td>
					<?php
					$wp_button_class = function_exists( 'wc_wp_theme_get_element_class_name' ) && wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';

					foreach ( $actions as $key => $action ) {
						if ( empty( $action['aria-label'] ) ) {
							/* translators: %1$s Action name, %2$s Order number. */
							$action_aria_label = sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() );
						} else {
							$action_aria_label = $action['aria-label'];
						}

						echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . ' order-actions-button" aria-label="' . esc_attr( $action_aria_label ) . '">' . esc_html( $action['name'] ) . '</a>';
					}
					?>
				</td>
			</tr>
		<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php do_action( 'woocommerce_after_order_details', $order ); ?>
