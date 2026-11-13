( function( $, JetWooBuilder ) {

	"use strict";

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetSingleAddToCart = function( $scope ) {

		// Do not re-init variation form on native single product pages.
		if ( $( 'body' ).hasClass( 'single-product' ) ) {
			return;
		}

		// Init WooCommerce variation form inside widget scope.
		if ( 'undefined' !== typeof wc_add_to_cart_variation_params ) {
			$scope.find( '.variations_form' ).each( function() {
				$( this ).wc_variation_form();
			} );
		}

	};

	JetWooBuilder.registerWidgetHandler(
		'jet-single-add-to-cart.default',
		widgetSingleAddToCart
	);

}( jQuery, window.JetWooBuilder ) );
