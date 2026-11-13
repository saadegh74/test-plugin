( function( $, JetWooBuilder ) {

	"use strict";

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetProductsGrid = function( $scope ) {

		// Initialize quantity inputs inside products grid widget scope.
		JetWooBuilder.handleInputQuantityValue( $scope );
		JetWooBuilder.handleVariationSwatchesAddToCart( $scope );

		let $carousel       = $scope.find( '.jet-woo-carousel' ),
			$wrapper        = $scope.find( '.jet-woo-products' ),
			mobileHover     = $wrapper.data( 'mobile-hover' ),
			$productItem    = $wrapper.find( '.jet-woo-products__item' ),
			$cqwWrapper     = $productItem.find( '.jet-woo-products-cqw-wrapper' ),
			$hoveredContent = $productItem.find( '.hovered-content' ),
			cqwWrapperExist = false,
			hoveredContentExist = false;

		if ( $cqwWrapper.length > 0 && $cqwWrapper.html().trim().length > 0 ) {
			cqwWrapperExist = true;
		}

		if ( $hoveredContent.length > 0 && $hoveredContent.html().trim().length > 0 ) {
			hoveredContentExist = true;
		}

		if ( ( cqwWrapperExist || hoveredContentExist ) && mobileHover ) {
			JetWooBuilder.mobileHoverOnTouch(
				$productItem,
				'.jet-woo-product-thumbnail'
			);
		}

		if ( $carousel.length ) {
			JetWooBuilder.initCarousel(
				$carousel,
				$carousel.data( 'slider_options' )
			);
		}

	};

	JetWooBuilder.registerWidgetHandler(
		'jet-woo-products.default',
		widgetProductsGrid
	);

}( jQuery, window.JetWooBuilder ) );
