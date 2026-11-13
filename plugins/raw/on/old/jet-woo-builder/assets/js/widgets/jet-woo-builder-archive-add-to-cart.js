( function( $, JetWooBuilder ) {

	"use strict";

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetArchiveAddToCart = function( $scope ) {

		// Initialize quantity inputs inside archive add to cart widget scope.
		JetWooBuilder.handleInputQuantityValue( $scope );

	};

	JetWooBuilder.registerWidgetHandler(
		'jet-woo-builder-archive-add-to-cart.default',
		widgetArchiveAddToCart
	);

}( jQuery, window.JetWooBuilder ) );
