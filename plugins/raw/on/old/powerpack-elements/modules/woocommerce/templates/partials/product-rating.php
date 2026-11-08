<?php
$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

if ( $rating_count > 0 ) :
?>

<?php do_action( 'pp_single_product_before_rating_wrap', $settings, $product ); ?>

<div class="woocommerce-product-rating">
	<?php echo wc_get_rating_html( $average, $rating_count ); ?>
	<?php if ( comments_open( $product_id ) && isset( $settings['product_rating_count'] ) && 'yes' === $settings['product_rating_count'] ) : ?>
		<a href="#reviews" class="woocommerce-rating-count" rel="nofollow">
			<?php if ( isset( $settings['product_rating_text'] ) && ! empty( $settings['product_rating_text'] ) ) { ?>
			(<span class="count"><?php echo $review_count; ?></span> <?php echo $settings['product_rating_text']; ?>)
			<?php } else { ?>
				<?php // translators: %s is for Rating. ?>
				(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'powerpack' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)
			<?php } ?>
		</a>
	<?php endif ?>
</div>

<?php do_action( 'pp_single_product_after_rating_wrap', $settings, $product ); ?>

<?php endif; ?>