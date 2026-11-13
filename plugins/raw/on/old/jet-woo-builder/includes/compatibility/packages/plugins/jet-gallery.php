<?php
/**
 * JetGallery compatibility package
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Builder_Gallery_Package' ) ) {


	class Jet_Woo_Builder_Gallery_Package {


		public function __construct() {
			add_filter( 'jet-woo-builder/frontend/localize-data', [ $this, 'add_quick_view_gallery_assets_data' ] );
		}

		/**
		 * Add quick view gallery assets data.
		 *
		 * Returns list of JetProductGallery assets for JetPopup product quick view.
		 *
		 * @since  2.1.1
		 * @access public
		 *
		 * @param array $data Localized frontend data.
		 *
		 * @return array
		 */
		public function add_quick_view_gallery_assets_data( $data ) {

			$suffix     = jet_woo_product_gallery_assets()->suffix();
			$plugin_url = jet_woo_product_gallery()->plugin_url();

			$data['quick_view_gallery_assets'] = [
				'data'    => apply_filters( 'jet-woo-product-gallery/frontend/localize-data', [
					'product_types'       => jet_woo_product_gallery_tools()->get_compatible_product_types(),
					'assets_path'         => $plugin_url . 'assets',
					'photoswipe_template' => jet_woo_product_gallery()->get_photoswipe_template_html(),
				] ),
				'scripts' => [
					'jet-woo-product-gallery' => $plugin_url . 'assets/js/jet-woo-product-gallery' . $suffix . '.js',
				],
				'styles'  => [
					'jet-gallery-frontend'                  => $plugin_url . 'assets/css/frontend.css',
					'jet-gallery-widget-gallery-anchor-nav' => $plugin_url . 'assets/css/widgets/gallery-anchor-nav.css',
					'jet-gallery-widget-gallery-grid'       => $plugin_url . 'assets/css/widgets/gallery-grid.css',
					'jet-gallery-widget-gallery-modern'     => $plugin_url . 'assets/css/widgets/gallery-modern.css',
					'jet-gallery-widget-gallery-slider'     => $plugin_url . 'assets/css/widgets/gallery-slider.css',
				],
			];

			return $data;

		}

	}

}

new Jet_Woo_Builder_Gallery_Package();