<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$add_pp_product_class = '';
if ( 'slider' === $settings['products_layout_type'] ) {
	$add_pp_product_class = 'swiper-wrapper';
} else {
	$add_pp_product_class = 'elementor-grid';
}
?>
<ul class="products <?php echo esc_attr( $add_pp_product_class ); ?> columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?>">
