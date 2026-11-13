<?php
/**
 * Wishlist loop item categories template
 */

if ( 'yes' !== $widget_settings['show_item_categories'] ) {
	return;
}

echo wp_kses_post( jet_cw_functions()->get_categories( $_product ) ?? '' );
