<?php
/**
 * Compare & Wishlist compatibility class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_CW_Compatibility' ) ) {

	/**
	 * Define Jet_CW_Compatibility class
	 */
	class Jet_CW_Compatibility {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			// WPML String Translation plugin exist check
			if ( defined( 'WPML_ST_VERSION' ) ) {
				$this->load_files();

				add_filter( 'wpml_elementor_widgets_to_translate', array( $this, 'add_translatable_nodes' ) );
			}

			add_filter( 'jet-cw/dashboard/settings/compare_page', [ $this, 'set_translated_page' ] );
			add_filter( 'jet-cw/dashboard/settings/wishlist_page', [ $this, 'set_translated_page' ] );
			add_filter( 'jet-cw/wishlist/product-id', [ $this, 'set_translated_product' ] );
			add_filter( 'jet-cw/compare/product-id', [ $this, 'set_translated_product' ] );
			add_filter( 'jet-cw/wishlist/in-wishlist', [ $this, 'check_translated_products_in_lists' ], 10, 3 );
			add_filter( 'jet-cw/compare/in-compare', [ $this, 'check_translated_products_in_lists' ], 10, 3 );

			$this->include_plugin_integration_file();

		}

		/**
		 * Set translated page.
		 *
		 * Set translated page ID to show.
		 *
		 * @since  1.5.3
		 * @access public
		 *
		 * @param string $page_id List page id.
		 *
		 * @return false|int|null
		 */
		public function set_translated_page( $page_id ) {

			if ( defined( 'WPML_ST_VERSION' ) ) {
				return apply_filters( 'wpml_object_id', $page_id, 'page', true );
			}

			if ( function_exists( 'pll_get_post' ) ) {
				$translated_template_id = pll_get_post( $page_id );

				if ( null === $translated_template_id || false === $translated_template_id ) {
					return $page_id;
				} elseif ( $translated_template_id > 0 ) {
					return $translated_template_id;
				}
			}

			return $page_id;

		}

		/**
		 * Set translated product.
		 *
		 * Set translated product ID to show in appropriate widgets lists.
		 *
		 * @since  1.5.3
		 * @access public
		 *
		 * @param string|number $product_id Product ID.
		 *
		 * @return mixed|void
		 */
		public function set_translated_product( $product_id ) {
			return apply_filters( 'wpml_object_id', $product_id, 'product', true );
		}

		/**
		 * Check translated products in lists.
		 *
		 * @since 1.5.3
		 *
		 * @param boolean       $in_list    In list existence.
		 * @param string|number $product_id Product ID.
		 * @param array         $list       Products IDs list.
		 *
		 * @return bool|mixed
		 */
		public function check_translated_products_in_lists( $in_list, $product_id, $list ) {

			$type         = apply_filters( 'wpml_element_type', get_post_type( $product_id ) );
			$trid         = apply_filters( 'wpml_element_trid', false, $product_id, $type );
			$translations = apply_filters( 'wpml_get_element_translations', [], $trid, $type );

			foreach ( $translations as $lang => $translation ) {
				if ( in_array( $translation->element_id, $list ) ) {
					$in_list = true;
				}
			}

			return $in_list;

		}

		/**
		 * Load required files.
		 */
		public
		function load_files() {
			if ( class_exists( 'WPML_Elementor_Module_With_Items' ) ) {
				require jet_cw()->plugin_path( 'includes/lib/compatibility/modules/class-wpml-jet-cw-compare.php' );
			}
		}


		/**
		 * Include plugin integrations file.
		 */
		public function include_plugin_integration_file() {

			$plugins = [
				'jet-popup.php'       => [
					'cb'   => 'class_exists',
					'args' => 'Jet_Popup',
				],
				'jet-engine.php'      => [
					'cb'   => 'class_exists',
					'args' => 'Jet_Engine',
				],
				'jet-woo-builder.php' => [
					'cb'   => 'class_exists',
					'args' => 'Jet_Woo_Builder',
				],
			];

			foreach ( $plugins as $file => $condition ) {
				if ( true === call_user_func( $condition['cb'], $condition['args'] ) ) {
					require jet_cw()->plugin_path( 'includes/lib/compatibility/plugins/' . $file );
				}
			}

		}

		/**
		 * Add jet elements translation nodes
		 *
		 * @param array $nodes_to_translate
		 *
		 * @return array
		 */
		public function add_translatable_nodes( $nodes_to_translate ) {

			$nodes_to_translate['jet-compare'] = array(
				'conditions' => array( 'widgetType' => 'jet-compare' ),
				'fields'     => array(
					array(
						'field'       => 'empty_compare_text',
						'type'        => esc_html__( 'Jet Compare: You have no comparison lists. Add products to the comparison.', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
				),
			);

			$nodes_to_translate['jet-compare-button'] = array(
				'conditions' => array( 'widgetType' => 'jet-compare-button' ),
				'fields'     => array(
					array(
						'field'       => 'compare_button_label_normal',
						'type'        => esc_html__( 'Jet Compare Button: Add To Compare', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'compare_button_label_added',
						'type'        => esc_html__( 'Jet Compare Button: View Compare', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
				),
			);

			$nodes_to_translate['jet-compare-count-button'] = array(
				'conditions' => array( 'widgetType' => 'jet-compare-count-button' ),
				'fields'     => array(
					array(
						'field'       => 'button_label',
						'type'        => esc_html__( 'Jet Compare Count Button: Compare', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'count_format',
						'type'        => esc_html__( 'Jet Compare Count Button: (%s)', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
				),
			);

			$nodes_to_translate['jet-wishlist'] = array(
				'conditions' => array( 'widgetType' => 'jet-wishlist' ),
				'fields'     => array(
					array(
						'field'       => 'empty_wishlist_text',
						'type'        => esc_html__( 'Jet Wishlist: No products were added to the wishlist.', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
				),
			);

			$nodes_to_translate['jet-wishlist-button'] = array(
				'conditions' => array( 'widgetType' => 'jet-wishlist-button' ),
				'fields'     => array(
					array(
						'field'       => 'wishlist_button_label_normal',
						'type'        => esc_html__( 'Jet Wishlist Button: Add To Wishlist', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'wishlist_button_label_added',
						'type'        => esc_html__( 'Jet Wishlist Button: View Wishlist', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
				),
			);

			$nodes_to_translate['jet-wishlist-count-button'] = array(
				'conditions' => array( 'widgetType' => 'jet-wishlist-count-button' ),
				'fields'     => array(
					array(
						'field'       => 'button_label',
						'type'        => esc_html__( 'Jet Wishlist Count Button: Wishlist', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'count_format',
						'type'        => esc_html__( 'Jet Wishlist Count Button: (%s)', 'jet-cw' ),
						'editor_type' => 'LINE',
					),
				),
			);

			return $nodes_to_translate;

		}

	}

}
