<?php do_action( 'pp_single_product_before_button_wrap', $settings, $product ); ?>

<div class="pp-product-action woocommerce-product-add-to-cart">
	<?php woocommerce_template_single_add_to_cart(); ?>
</div>

<?php do_action( 'pp_single_product_after_button_wrap', $settings, $product ); ?>