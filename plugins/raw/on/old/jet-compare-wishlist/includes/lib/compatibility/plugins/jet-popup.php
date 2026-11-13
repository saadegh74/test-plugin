<?php
/**
 * Popup compatibility package.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'Jet_CW_Popup_Package' ) ) {

	class Jet_CW_Popup_Package {

		public function __construct() {
			add_filter( 'jet-popup/ajax-request/after-content-define/post-data', array( $this, 'define_popup_qw' ) );
		}

		public function define_popup_qw( $popup_data ) {

			if ( ! empty( $popup_data['isJetWooBuilder'] ) || ! empty( $popup_data['isJetEngine'] )) {
				$popup_data['jetCompareWishlistWidgets'] = jet_cw()->widgets_store->get_widgets_types();
			}

			return $popup_data;

		}

	}

}

new Jet_CW_Popup_Package();
