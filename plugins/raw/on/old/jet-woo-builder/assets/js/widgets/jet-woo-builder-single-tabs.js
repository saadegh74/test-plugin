( function( $, JetWooBuilder ) {

	"use strict";

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetSingleTabs = function( $scope ) {

		// Do not re-init native single product tabs on default product pages.
		if ( $( 'body' ).hasClass( 'single-product' ) ) {
			return;
		}

		let hash  = window.location.hash,
			url   = window.location.href,
			$tabs = $scope.find( '.wc-tabs, ul.tabs' ).first();

		$tabs.find( 'a' ).addClass( 'elementor-clickable' );

		$scope.find( '.wc-tab, .woocommerce-tabs .panel:not(.panel .panel)' ).hide();

		if ( hash.toLowerCase().indexOf( 'comment-' ) >= 0 || hash === '#reviews' || hash === '#tab-reviews' ) {
			$tabs.find( 'li.reviews_tab a' ).trigger( 'click' );
		} else if ( url.indexOf( 'comment-page-' ) > 0 || url.indexOf( 'cpage=' ) > 0 ) {
			$tabs.find( 'li.reviews_tab a' ).trigger( 'click' );
		} else if ( hash === '#tab-additional_information' ) {
			$tabs.find( 'li.additional_information_tab a' ).trigger( 'click' );
		} else {
			$tabs.find( 'li:first a' ).trigger( 'click' );
		}

	};

	JetWooBuilder.registerWidgetHandler(
		'jet-single-tabs.default',
		widgetSingleTabs
	);

}( jQuery, window.JetWooBuilder ) );
