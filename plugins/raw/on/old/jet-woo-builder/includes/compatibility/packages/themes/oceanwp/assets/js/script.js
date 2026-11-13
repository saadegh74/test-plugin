(function () {
	'use strict';

	function resetOceanWpCustomSelect( select ) {
		if ( ! select ) {
			return;
		}

		if ( select.nextElementSibling && select.nextElementSibling.classList.contains( 'theme-select' ) ) {
			select.nextElementSibling.remove();
		}

		select.classList.remove( 'hasCustomSelect' );
		select.classList.remove( 'theme-selectHover' );

		select.style.opacity    = '';
		select.style.position   = '';
		select.style.height     = '';
		select.style.fontSize   = '';
		select.style.appearance = '';
		select.style.width      = '';
	}

	function resetJetWooBuilderSelects( context ) {
		var scope   = context || document;
		var selects = scope.querySelectorAll( '.elementor-jet-single-add-to-cart .variations_form .variations select' );

		if ( ! selects.length ) {
			return;
		}

		selects.forEach( function( select ) {
			resetOceanWpCustomSelect( select );
		} );
	}

	function initJetWooBuilderOceanWpFix() {
		resetJetWooBuilderSelects();

		if ( 'undefined' !== typeof jQuery ) {
			jQuery( document ).on( 'wc_variation_form', function( event ) {
				resetJetWooBuilderSelects( event.target );
			} );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initJetWooBuilderOceanWpFix );
	} else {
		initJetWooBuilderOceanWpFix();
	}

	if ( 'undefined' !== typeof elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', function() {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/jet-single-add-to-cart.default',
				function( $scope ) {
					resetJetWooBuilderSelects( $scope[0] );
				}
			);
		} );
	}
})();
