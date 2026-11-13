<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/jet-woo-builder/woocommerce/cart/cart.php.
 *
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( WC()->cart ) ) {
	return;
}

$template = apply_filters( 'jet-woo-builder/current-template/template-id', jet_woo_builder()->woocommerce->get_custom_cart_template() );

jet_woo_builder()->admin_bar->register_post_item( $template );

do_action( 'woocommerce_before_cart' );
?>

<div class="jet-woo-builder-woocommerce-cart">
	<?php echo jet_woo_builder_template_functions()->get_woo_builder_content( $template, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>

<?php do_action( 'woocommerce_after_cart' );
