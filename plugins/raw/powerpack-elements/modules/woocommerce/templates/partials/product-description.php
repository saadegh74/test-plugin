<?php do_action( 'pp_single_product_before_desc_wrap', $settings, $product, $product_data ); ?>

<div class="woocommerce-product-details__short-description">
	<?php echo apply_filters( 'woocommerce_short_description', $product_data['short_description'] ); ?>
</div>

<?php do_action( 'pp_single_product_after_desc_wrap', $settings, $product, $product_data ); ?>
