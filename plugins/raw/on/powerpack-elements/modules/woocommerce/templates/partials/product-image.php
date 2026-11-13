<?php do_action( 'pp_single_product_before_image_wrap', $settings, $product ); ?>

<div class="single-product-image">
	<?php if ( method_exists( $product, 'get_image' ) ) { ?>
		<?php echo $product->get_image( $settings['image_size_size'], array( 'data-no-lazy' => '1', 'class' => 'pp-product-featured-image' ) ); ?>
	<?php } else { ?>
		<img src="<?php echo $image[0]; ?>" title="<?php echo get_the_title( $product_id ); ?>" alt="<?php echo get_the_title( $product_id ); ?>" class="pp-product-featured-image">
	<?php } ?>
	<?php if ( 'yes' == $settings['show_sale_badge'] && $product->is_on_sale() ) : ?>
		<?php
			$sale_badge = sprintf( '<span class="onsale">%s</span>', esc_html__('Sale!', 'powerpack') );
			echo apply_filters( 'woocommerce_sale_flash', $sale_badge, $post, $product );
		?>
	<?php endif; ?>
</div>

<?php do_action( 'pp_single_product_after_image_wrap', $settings, $product ); ?>