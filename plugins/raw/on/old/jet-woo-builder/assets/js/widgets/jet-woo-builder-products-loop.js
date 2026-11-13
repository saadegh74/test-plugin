( function( $, JetWooBuilder ) {

	'use strict';

	if ( ! JetWooBuilder || ! JetWooBuilder.registerWidgetHandler ) {
		return;
	}

	const widgetProductsLoop = function( $scope ) {

		let settings = JetWooBuilder.getElementorElementSettings( $scope );

		if ( settings && settings.switcher_enable ) {
			let $productsWrapper   = $scope.find( '.jet-woo-products-wrapper' ),
				$switcherControl   = $scope.find( '.jet-woo-switcher-controls-wrapper .jet-woo-switcher-btn' ),
				mobileQuery        = window.matchMedia( '(max-width: 767px)' ),
				currentLayout      = null,
				lastDesktopLayout  = null,
				lastMobileLayout   = null;

			const MAIN              = settings.main_layout;
			const SECONDARY         = settings.secondary_layout;
			const MOBILE_DEFAULT_ID = ( settings.layout_default_mobile === 'secondary' ) ? SECONDARY : MAIN;

			const isMobileActive = function() {
				return mobileQuery.matches && settings.switcher_hide_mobile;
			};

			const applyLayout = function( layoutId ) {
				let filterQuery = null;

				if ( window.JetSmartFilters && window.JetSmartFilters.filterGroups['woocommerce-archive/default'] ) {
					filterQuery = window.JetSmartFilters.filterGroups['woocommerce-archive/default'].query;
				}

				if ( ! layoutId || layoutId === currentLayout ) {
					return;
				}

				$productsWrapper.addClass( 'jet-layout-loading' );

				$.ajax( {
					type: 'POST',
					url: window.jetWooBuilderData.ajax_url,
					data: {
						action: 'jet_woo_builder_get_layout',
						query: window.jetWooBuilderData.products,
						layout: layoutId,
						filters: filterQuery || undefined
					},
				} ).done( function( response ) {
					$productsWrapper.removeClass( 'jet-layout-loading' );
					$productsWrapper.html( response.data.html );

					currentLayout = layoutId;

					JetWooBuilder.elementorFrontendInit( $productsWrapper );
					$( document ).trigger( 'jet-woo-builder-content-rendered', [ this, response ] );
				} );
			};

			const getSwitcherLayout = function() {
				let $activeBtn = $switcherControl.filter( '.active' );
				return getLayoutFromButton( $activeBtn );
			};

			const getLayoutFromButton = function( $btn ) {
				return $btn.hasClass( 'jet-woo-switcher-btn-main' ) ? MAIN : SECONDARY;
			};

			const setActiveButtonByLayout = function( layoutId ) {
				if ( ! layoutId ) {
					return;
				}

				$switcherControl.each( function() {
					let $btn      = $( this ),
						btnLayout = getLayoutFromButton( $btn );

					if ( btnLayout === layoutId ) {
						$switcherControl.removeClass( 'active' );
						$btn.addClass( 'active' );
					}
				} );
			};

			const handleResponsiveChange = function() {
				if ( window.elementorFrontend && window.elementorFrontend.isEditMode() ) {
					return;
				}

				if ( isMobileActive() ) {
					const targetMobile = lastMobileLayout || MOBILE_DEFAULT_ID;

					if ( targetMobile && targetMobile !== currentLayout ) {
						applyLayout( targetMobile );
					}
				} else {
					lastDesktopLayout = lastDesktopLayout || getSwitcherLayout();

					if ( lastDesktopLayout ) {
						setActiveButtonByLayout( lastDesktopLayout );

						if ( lastDesktopLayout !== currentLayout ) {
							applyLayout( lastDesktopLayout );
						}
					}
				}
			};

			const initSwitcherEvents = function() {
				mobileQuery.addEventListener( 'change', handleResponsiveChange );

				$switcherControl.on( 'click.JetWooBuilder', function( event ) {
					event.preventDefault();

					let $thisBtn     = $( this ),
						activeLayout = getLayoutFromButton( $thisBtn );

					if ( ! activeLayout || activeLayout === currentLayout ) {
						return;
					}

					$switcherControl.removeClass( 'active' );
					$thisBtn.addClass( 'active' );

					lastDesktopLayout = activeLayout;
					applyLayout( activeLayout );
				} );
			};

			if ( isMobileActive() ) {
				currentLayout = null;
			} else {
				lastDesktopLayout = getSwitcherLayout();

				if ( lastDesktopLayout ) {
					setActiveButtonByLayout( lastDesktopLayout );
					currentLayout = lastDesktopLayout;
				}
			}

			$( document ).on( 'jet-woo-builder-content-rendered', function() {
				if ( isMobileActive() && currentLayout ) {
					lastMobileLayout = currentLayout;
				}
			} );

			handleResponsiveChange();
			initSwitcherEvents();
		}

	};

	JetWooBuilder.registerWidgetHandler(
		'jet-woo-builder-products-loop.default',
		widgetProductsLoop
	);

}( jQuery, window.JetWooBuilder ) );
