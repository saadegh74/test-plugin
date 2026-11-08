<?php do_action( 'pp_single_product_before_meta_wrap', $settings, $product ); ?>

<div class="product_meta">
	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php if ( 'yes' == $settings['show_sku'] && wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
		<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'powerpack' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'powerpack' ); ?></span></span>
	<?php endif; ?>

	<?php if ( 'yes' == $settings['show_taxonomy'] ) : ?>
		<span class="posted_in">
		<?php
			$product_tax 	= $settings['select_taxonomy'];
			$product_terms	= wp_get_post_terms( $product->get_id(), $product_tax, array('fields' => 'all') );
			$terms 			= '';

			if ( $settings['show_taxonomy_custom_text'] == 'yes' ) {
				echo $settings['taxonomy_custom_text'];
			} else {
				if ( 'product_cat' == $product_tax ) {
					echo __('Category: ', 'powerpack');
				} elseif ( 'product_tag' == $product_tax ) {
					echo __('Tags: ', 'powerpack');
				} else {
					$tax_obj = get_taxonomy( $product_tax );
					if ( $tax_obj ) {
						echo $tax_obj->label . ': ';
					}
				}
			}

			if ( !empty( $product_terms ) && $product_tax != 'none' ) {
				foreach ($product_terms as $term) {

					$term_link = get_term_link( $term );
					$terms .= '<a href="'.$term_link.'">'. $term->name .'</a>, ';

				}
				$terms = rtrim( $terms, ', ' );
				echo $terms;
			}
		?>
		</span>
	<?php endif; ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>
</div>

<?php do_action( 'pp_single_product_after_meta_wrap', $settings, $product ); ?>