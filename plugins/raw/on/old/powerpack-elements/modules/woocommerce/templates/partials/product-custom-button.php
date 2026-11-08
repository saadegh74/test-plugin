<?php do_action( 'pp_single_product_before_button_wrap', $settings, $product ); ?>

<div class="pp-product-action  woocommerce-product-add-to-cart">
	<?php
		if ( ! empty( $settings['button_link']['url'] ) ) {
			$this->parent->add_link_attributes( 'button_url', $settings['button_link'] );
		} else {
			$this->parent->add_render_attribute( 'button_url', 'href', '#' );
		}
		?>
	<a <?php echo wp_kses_post( $this->parent->get_render_attribute_string( 'button_url' ) ); ?> class="button pp-product-button pp-product-button-custom">
		<?php
		if ( $settings['button_text'] != '' ) {
			echo $settings['button_text'];
		} else {
			esc_html_e('Add to cart', 'powerpack');
		}
		?>
	</a>
</div>

<?php do_action( 'pp_single_product_after_button_wrap', $settings, $product ); ?>