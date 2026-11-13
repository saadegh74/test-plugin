<?php
/**
 * JetWooBuilder Single Product Attributes Table widget template.
 */

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
	return;
}

ob_start();

wc_display_product_attributes( $product );

$content = ob_get_clean();

if ( empty( $content ) ) {
	return;
}

$settings = $this->get_settings_for_display();

$tag   = isset( $settings['block_title_tag'] ) ? jet_woo_builder_tools()->sanitize_html_tag( $settings['block_title_tag'] ) : 'h3';
$title = isset( $settings['block_title'] ) ? esc_html__( $settings['block_title'], 'jet-woo-builder' ) : '';

if ( ! empty( $title ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped via esc_html__()
	printf( '<%1$s class="jet-single-attrs__title">%2$s</%1$s>', esc_attr( $tag ), $title );
}

echo wp_kses_post( $content );
