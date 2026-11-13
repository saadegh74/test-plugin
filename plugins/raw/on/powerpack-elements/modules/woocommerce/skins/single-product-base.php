<?php
/**
 * PowerPack WooCommerce Skin Single Product - Default.
 *
 * @package PowerPack
 */

namespace PowerpackElements\Modules\Woocommerce\Skins;

use Elementor\Controls_Manager;
use Elementor\Skin_Base;
use Elementor\Widget_Base;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Single_Product_Base
 *
 * @property Products $parent
 */
abstract class Single_Product_Base extends Skin_Base {

	/**
	 * Settings
	 *
	 * @since 2.7.0
	 * @var object $settings
	 */
	public static $settings;

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

			include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/single-product-skin-1.php';

			do_action( 'pp_after_product' );
			wp_reset_postdata();
		endif;
	}

		/**
	 * Render wrapper start.
	 *
	 * @since 2.7.0
	 */
	public function render_wrapper_start() {

		$settings = $this->parent->get_settings();

		$this->parent->add_render_attribute(
			'wrapper', [
				'class' => [
					'woocommerce',
					'pp-woocommerce',
					'pp-single-product',
					'pp-single-product-' . $this->get_id(),
				],
			]
		);

		echo '<div ' . wp_kses_post( $this->parent->get_render_attribute_string( 'wrapper' ) ) . '>';
			echo '<div class="summary entry-summary clearfix">';
	}

	/**
	 * Render wrapper end.
	 *
	 * @since 2.7.0
	 */
	public function render_wrapper_end() {
		echo '</div></div>';
	}

	/**
	 * Render Content.
	 *
	 * @since 2.7.0
	 * @access protected
	 */
	public function render() {
		if ( ! empty( self::$settings ) ) {
			$settings = self::$settings;
		} else {
			$settings = $this->parent->get_settings();
		}

		if ( ! $settings['product_id'] ) {
			$placeholder = sprintf( 'Click here to edit the "%1$s" settings and choose a product from the dropdown list.', esc_attr( $this->parent->get_title() ) );

			echo esc_attr( $this->parent->render_editor_placeholder(
				[
					'title' => esc_attr( $this->parent->get_title() ),
					'body' => $placeholder,
				]
			) );
		}

		$this->render_wrapper_start();
			$this->render_woo_single_product_template();
		$this->render_wrapper_end();
	}
}
