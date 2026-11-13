<?php
/**
 * PowerPack WooCommerce Skin Grid - Default.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Modules\Woocommerce\Skins;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Single_Product_Skin_3
 *
 * @property Products $parent
 */
class Single_Product_Skin_3 extends Single_Product_Base {

	/**
	 * Get ID.
	 *
	 * @since 2.7.0
	 * @access public
	 */
	public function get_id() {
		return 'skin-3';
	}

	/**
	 * Get title.
	 *
	 * @since 2.7.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Skin 3', 'powerpack' );
	}

	/**
	 * Render woo default template.
	 *
	 * @since 2.7.0
	 */
	public function render_woo_single_product_template() {

		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}
		global $post, $product;

		$product_id 	= $settings['product_id'];
		$product 		= wc_get_product( $product_id );

		if ( $product ) :

			$product_data 	= $product->get_data();
			$image_size 	= $settings['image_size_size'];
			$image 			= wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), $image_size );
			$attachment_ids = $product_data['gallery_image_ids'];

			$post = get_post( $product_id, OBJECT );
			setup_postdata( $post );

			do_action( 'pp_before_product' );

			include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/single-product-skin-3.php';

			do_action( 'pp_after_product' );
			wp_reset_postdata();
		endif;
	}

	/**
	 * Render.
	 *
	 * @since 2.7.0
	 * @access public
	 */
	public function render() {
		parent::render();
	}

}
