<?php
/**
 * Wishlist loop item SKU template
 */

if ( 'yes' !== $widget_settings['show_item_sku'] ) {
	return;
}

echo wp_kses_post( jet_cw_functions()->get_sku( $_product ) ?? '' );
