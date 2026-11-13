( function( $, JetWooBuilder ) {

	"use strict";

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetProductsList = function( $scope ) {

		// Initialize quantity inputs inside products list widget scope.
		JetWooBuilder.handleInputQuantityValue( $scope );
		JetWooBuilder.handleVariationSwatchesAddToCart( $scope );

	};

	JetWooBuilder.registerWidgetHandler(
		'jet-woo-products-list.default',
		widgetProductsList
	);

}( jQuery, window.JetWooBuilder ) );
