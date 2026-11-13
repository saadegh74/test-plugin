<?php
/**
 * Wishlist loop item remove button template
 */

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the SVG inside button needs raw output
echo jet_cw_functions()->get_wishlist_remove_button( $_product, $widget_settings );