<?php do_action( 'pp_single_product_before_title_wrap', $settings, $product ); ?>

<<?php echo $settings['product_title_heading_tag']; ?> class="pp-product-title"><?php echo get_the_title( $product_id ); ?></<?php echo $settings['product_title_heading_tag']; ?>>

<?php do_action( 'pp_single_product_after_title_wrap', $settings, $product ); ?>