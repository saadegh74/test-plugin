<?php
/**
 * PowerPack WooCommerce Category - Template.
 *
 * @package PowerPack
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 1 === $i || 2 === $i || 3 === $i || 4 === $i ) {
	$pp_grid_class = '';
} else {
	$pp_grid_class = 'pp-grid-item-wrap';
}

if ( 1 === $i ) {
	echo '<div class="pp-woo-cat-tiles pp-woo-cat-tiles-1">';
	echo '<div class="pp-woo-cat-tiles-left">';
}
if ( 2 === $i ) {
	echo '<div class="pp-woo-cat-tiles-center">';
}
if ( 4 === $i ) {
	echo '<div class="pp-woo-cat-tiles-right">';
}
?>
<div <?php wc_product_cat_class( 'product ' . $pp_grid_class . ' pp-woo-cat-' . $i, $category ); ?>>
	<div class="pp-grid-item">
	<?php
	/**
	 * Link Open
	 * woocommerce_before_subcategory hook.
	 *
	 * @hooked woocommerce_template_loop_category_link_open - 10
	 */
	do_action( 'woocommerce_before_subcategory', $category );

	/**
	 * Subcategory Title
	 * woocommerce_before_subcategory_title hook.
	 *
	 * @hooked woocommerce_subcategory_thumbnail - 10
	 */
	do_action( 'woocommerce_before_subcategory_title', $category );

	/**
	 * Subcategory Title
	 * woocommerce_shop_loop_subcategory_title hook.
	 *
	 * @hooked woocommerce_template_loop_category_title - 10
	 */
	do_action( 'woocommerce_shop_loop_subcategory_title', $category );

	/**
	 * Subcategory Title
	 * woocommerce_after_subcategory_title hook.
	 */
	do_action( 'woocommerce_after_subcategory_title', $category );

	/**
	 * Link CLose
	 * woocommerce_after_subcategory hook.
	 *
	 * @hooked woocommerce_template_loop_category_link_close - 10
	 */
	do_action( 'woocommerce_after_subcategory', $category );
	?>
	</div>
</div>
<?php
if ( 1 === $i || 3 === $i || 4 === $i ) {
	echo '</div>';
}
if ( 4 === $i ) {
	echo '</div>';
}
