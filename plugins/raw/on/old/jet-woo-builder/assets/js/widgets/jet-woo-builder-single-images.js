( function( $, JetWooBuilder ) {

	"use strict";

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetSingleImages = function( $scope ) {

		// Do not re-init native single product gallery on default product pages.
		if ( $( 'body' ).hasClass( 'single-product' ) ) {
			return;
		}

		// Initialize WooCommerce product gallery inside widget scope.
		$scope.find( '.woocommerce-product-gallery' ).each( function() {
			$( this ).wc_product_gallery();
		} );

	};

	JetWooBuilder.registerWidgetHandler(
		'jet-single-images.default',
		widgetSingleImages
	);

}( jQuery, window.JetWooBuilder ) );
