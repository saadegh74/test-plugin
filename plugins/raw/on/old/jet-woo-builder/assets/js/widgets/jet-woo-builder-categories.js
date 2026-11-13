( function( $, JetWooBuilder ) {

	"use strict";

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetCategories = function( $scope ) {

		let $carousel     = $scope.find( '.jet-woo-carousel' ),
			$wrapper      = $scope.find( '.jet-woo-categories' ),
			mobileHover   = $wrapper.data( 'mobile-hover' ),
			$categoryItem = $wrapper.find( '.jet-woo-categories__item' ),
			$count        = $categoryItem.find( '.jet-woo-category-count' );

		if (
			(
				$wrapper.hasClass( 'jet-woo-categories--preset-2' ) && $count.length > 0 ||
				$wrapper.hasClass( 'jet-woo-categories--preset-3' )
			) && mobileHover
		) {
			JetWooBuilder.mobileHoverOnTouch(
				$categoryItem,
				'.jet-woo-category-thumbnail'
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
		'jet-woo-categories.default',
		widgetCategories
	);

}( jQuery, window.JetWooBuilder ) );
