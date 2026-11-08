<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PowerpackElements\Classes\PP_Admin_Settings;

?>
	<div class="woocommerce pp-woocommerce-checkout">

		<?php 
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		?>
		<?php
			do_action( 'woocommerce_before_checkout_form', $checkout );
			// If checkout registration is disabled and not logged in, the user cannot checkout.
			if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
				echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'powerpack' ) ) );
				return;
			}
		?>
		<?php
			do_action( 'pp_checkout_top_content' );
			$templateid = get_page_template_slug( PP_Admin_Settings::get_option( 'pp_woo_template_product_checkout' ) );
		if ( empty( $templateid ) ) {
		?>
		<div class="elementor-section elementor-section-boxed">
			<div class="elementor-container elementor-column-gap-default">
				<div class="elementor-row">
					<div class="elementor-element elementor-column elementor-col-100 elementor-db">
						<?php
							woocommerce_checkout_coupon_form();
							woocommerce_checkout_login_form();
						?>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
		<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
			<?php do_action( 'pp_checkout_content' ); ?>
		</form>
		<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
	</div>
