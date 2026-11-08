<?php do_action( 'pp_single_product_before_price_wrap', $settings, $product ); ?>

<p class="price"><?php echo $product->get_price_html(); ?></p>

<?php do_action( 'pp_single_product_after_price_wrap', $settings, $product ); ?>