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
 * Class Single_Product_Skin_1
 *
 * @property Products $parent
 */
class Single_Product_Skin_1 extends Single_Product_Base {

	/**
	 * Get ID.
	 *
	 * @since 2.7.0
	 * @access public
	 */
	public function get_id() {
		return 'skin-1';
	}

	/**
	 * Get title.
	 *
	 * @since 2.7.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Skin 1', 'powerpack' );
	}

}
