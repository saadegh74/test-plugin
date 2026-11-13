<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

\Elementor\Plugin::$instance->frontend->add_body_class( 'elementor-template-full-width' );

get_header( 'shop' );

do_action( 'jet-woo-builder/full-width-page/before-content' );

$wc_data = new WC_Structured_Data;

$wc_data->generate_product_data();

$template                 = jet_woo_builder()->woocommerce->get_custom_shop_template();
$taxonomy_template = get_term_meta( get_queried_object_id(), 'jet_woo_builder_template', true );

if ( is_product_taxonomy() && ! empty( $taxonomy_template ) ) {
	$template = jet_woo_builder()->woocommerce->get_custom_product_taxonomy_template();
}

$template = apply_filters( 'jet-woo-builder/current-template/template-id', $template );

jet_woo_builder()->admin_bar->register_post_item( $template );

if ( \Elementor\Plugin::$instance->editor->is_edit_mode()
  || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
	the_content();
} else {
	echo jet_woo_builder_template_functions()->get_woo_builder_content( $template ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

do_action( 'jet-woo-builder/full-width-page/after-content' );

get_footer( 'shop' );
