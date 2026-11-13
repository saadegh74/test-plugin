( function( $, JetWooBuilder ) {

	'use strict';

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetCartTable = function( $scope ) {

		$scope.find( '.cart-collaterals' ).filter( function() {
			return $( this ).children().length === 0;
		} ).hide();

		let settings = JetWooBuilder.getElementorElementSettings( $scope );

		if ( 'yes' === settings.cart_update_automatically ) {
			let timeout;

			$( '.woocommerce' ).on( 'change', 'input.qty', function() {
				if ( timeout !== undefined ) {
					clearTimeout( timeout );
				}

				timeout = setTimeout( function() {
					$( '[name="update_cart"]' ).trigger( 'click' );
				}, 300 );
			} );
		}

	};

	JetWooBuilder.registerWidgetHandler(
		'jet-cart-table.default',
		widgetCartTable
	);

}( jQuery, window.JetWooBuilder ) );
