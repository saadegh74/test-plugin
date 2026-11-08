( function ( $, elementorFrontend ) {

	"use strict";

	var xhr = null;
	var JetCWSettings = window.JetCWSettings;
	var compareMaxItems = JetCWSettings.compareMaxItems;
	var compareItemsCount = JetCWSettings.compareItemsCount;

	var JetCW = {

		init: function () {

			var widgets = {
					'jet-compare.default' : JetCW.compareWidget,
				};

			$.each( widgets, function( widget, callback ) {
				elementorFrontend.hooks.addAction( 'frontend/element_ready/' + widget, callback );
			});

			JetCW.commonInit();

			$( window ).on( 'jet-popup/render-content/ajax/success', JetCW.prepareJetPopup );

		},

		commonInit: function () {
			$(document)
				.on('click.JetCW', '.jet-compare-button__link[href="#"]', JetCW.addToCompare)
				.on('click.JetCW', '.jet-wishlist-button__link[href="#"]', JetCW.addToWishlist)
				.on('click.JetCW', '.jet-compare-item-remove-button', JetCW.removeFromCompare)
				.on('click.JetCW', '.jet-wishlist-item-remove-button', JetCW.removeFromWishlist)
				.on('jet-cw-load', JetCW.addLoader)
				.on('jet-cw-loaded', JetCW.removeLoader)
				.on( 'jet-engine/listing-grid/after-lazy-load', JetCW.prepareCWSettingsAfterAjaxLoad )
				.on( 'jet-woo-builder-content-rendered', JetCW.prepareCWSettingsAfterAjaxLoad );
		},

		prepareCWSettingsAfterAjaxLoad: function ( event, scope, response ) {

			let cwData = response.data.jetCompareWishlistWidgets;

			JetCW.setProperWidgetsSettings( cwData );

		},

		setProperWidgetsSettings: function ( data ) {

			if ( ! data ) {
				return;
			}

			let compareWidgetsData = {},
				wishlistWidgetsData = {},
				loadedCompareWidgets = data.compare,
				loadedWishlistWidgets = data.wishlist;

			if ( null === JetCWSettings.widgets ) {
				JetCWSettings.widgets = {};
			} else {
				compareWidgetsData = JetCWSettings.widgets.compare;
				wishlistWidgetsData = JetCWSettings.widgets.wishlist;
			}

			JetCWSettings.widgets.compare = $.extend( compareWidgetsData, loadedCompareWidgets );
			JetCWSettings.widgets.wishlist = $.extend( wishlistWidgetsData, loadedWishlistWidgets );

		},

		compareWidget: function( $scope ) {

			let $tableWrapper = $scope.find( '.jet-compare-table__wrapper' ),
				$actionControls = $tableWrapper.find( '.jet-compare-table-action-control' );

			$actionControls.each( function () {
				const $actionControl = $( this );

				if ( $actionControl.hasClass( 'jet-compare-table-action-control__clear' ) ) {
					$actionControl.on( 'click.JetCW', JetCW.removeFromCompare );
				} else {
					$actionControl.on( 'click.JetCW', function ( event ) {

						event.preventDefault();

						let $control = $( this );

						if ( $control.hasClass( 'jet-compare-table-action-control__highlight' ) ) {
							! $tableWrapper.hasClass( 'jet-compare-table-highlight' ) ? $tableWrapper.addClass( 'jet-compare-table-highlight' ) : $tableWrapper.removeClass( 'jet-compare-table-highlight' );
						}

						if ( $control.hasClass( 'jet-compare-table-action-control__differences' ) ) {
							! $tableWrapper.hasClass( 'jet-compare-table-differences' ) ? $tableWrapper.addClass( 'jet-compare-table-differences' ) : $tableWrapper.removeClass( 'jet-compare-table-differences' );
						}

						if ( $actionControls.length > 1 ) {
							if ( ! $control.hasClass( 'active') ) {
								$control.addClass( 'active' );
								$control.siblings( '.jet-compare-table-action-control:not(.jet-compare-table-action-control__clear)' ).addClass( 'disable' );
							} else {
								$control.removeClass( 'active' );
								$control.siblings( '.jet-compare-table-action-control:not(.jet-compare-table-action-control__clear)' ).removeClass( 'disable' );
							}
						} else {
							! $control.hasClass( 'active' ) ? $control.addClass( 'active' ) : $control.removeClass( 'active' );
						}

						JetCW.compareTableDifference( $tableWrapper );

					} );
				}
			} );

		},

		compareTableDifference: function( table ) {

			let $tableRows = table.find( '.jet-compare-table-row' ),
				highlight = table.hasClass( 'jet-compare-table-highlight' ),
				onlyDifferent = table.hasClass( 'jet-compare-table-differences' );

			$tableRows.each( function () {

				let $currentRow = $( this ),
					$rowCells = $currentRow.find( '.jet-compare-table-cell' );

				if ( JetCW.exceptionsDifference( $currentRow ) ) {
					return;
				}

				let existingValues = [];

				$rowCells.each( function () {

					let $currentCell = $( this );

					if ( existingValues.indexOf( $currentCell.text().trim() ) !== -1 ) {
						return true;
					}

					existingValues.push( $currentCell.text().trim() );

				} );

				if ( highlight && existingValues.length > 1) {
						$currentRow.addClass( 'highlighted' );
				} else if ( ! highlight && $currentRow.hasClass( 'highlighted' ) ) {
					$currentRow.removeClass( 'highlighted' );
				}

				if ( onlyDifferent && existingValues.length <= 1 ) {
					$currentRow.hide();
				} else if ( ! onlyDifferent && $currentRow .is( ":hidden" ) ) {
					$currentRow.show();
				}

			} );
		},

		exceptionsDifference: function( row ) {

			let exceptions = [ 'remove-button', 'add-to-cart', 'product-title', 'stock-status', 'thumbnail', 'price', 'rating-stars' ],
				result = false;

			$.each( exceptions, function ( index, value ) {
				if ( row.children().find( '.jet-cw-' + value ).length > 0 ) {
					result = true;
				}
			});

			return result;

		},

		prepareJetPopup: function( event, popupData ) {

			let requestData = popupData.request.data;

			if ( requestData['isJetWooBuilder'] ) {
				JetCW.setProperWidgetsSettings( requestData.jetCompareWishlistWidgets );
			}

		},

		removeFromCompare: function (e) {

			e.preventDefault();

			var $scope = $(this),
				productID = $scope.data('product-id');

			if (xhr) {
				xhr.abort();
			}

			$(document).trigger(
				'jet-cw-load',
				[$scope, productID, 'jet_update_compare_list']
			);

			xhr = JetCW.ajaxRequest($scope, 'jet_update_compare_list', 'remove', productID);

		},

		removeFromWishlist: function (e) {

			e.preventDefault();

			var $scope = $(this),
				productID = $scope.data('product-id');

			if (xhr) {
				xhr.abort();
			}

			$(document).trigger(
				'jet-cw-load',
				[$scope, productID, 'jet_update_wish_list']
			);

			xhr = JetCW.ajaxRequest($scope, 'jet_update_wish_list', 'remove', productID);

		},

		addToWishlist: function (e) {

			e.preventDefault();

			var $scope = $(this),
				productID = $scope.data('product-id');

			if (xhr) {
				xhr.abort();
			}

			$(document).trigger(
				'jet-cw-load',
				[$scope, productID, 'jet_update_wish_list']
			);

			if ( ! $scope.hasClass( 'jet-wishlist-item-remove-button') ) {
				xhr = JetCW.ajaxRequest($scope, 'jet_update_wish_list', 'add', productID);
			}

		},

		addToCompare: function (e) {

			e.preventDefault();

			if ( compareItemsCount >= compareMaxItems && ! $( this ).hasClass( 'jet-compare-item-remove-button') ) {
				JetCW.showMessages( 'compare_max_items' );
				return;
			}

			var $scope = $(this),
				productID = $scope.data('product-id');

			if (xhr) {
				xhr.abort();
			}

			$(document).trigger(
				'jet-cw-load',
				[$scope, productID, 'jet_update_compare_list']
			);
			if ( ! $scope.hasClass( 'jet-compare-item-remove-button') ) {
				xhr = JetCW.ajaxRequest($scope, 'jet_update_compare_list', 'add', productID);
			}

		},

		ajaxRequest: function ($scope, action, context, productID) {

			$.ajax({
				url: JetCWSettings.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: action,
					pid: productID,
					context: context,
					widgets_data: JetCWSettings.widgets,
				},
			}).done(function (response) {
				compareItemsCount = response.compareItemsCount;

				JetCW.renderResult(response);

				$(document).trigger(
					'jet-cw-loaded',
					[$scope, productID, action]
				);

			});

		},

		renderResult: function ( response ) {

			let content = response.content;

			$.each( content, function ( selector, html ) {
				$( selector ).replaceWith( html );

				JetCW.elementorFrontendInit( $( selector ) );
				JetCW.compareWidget( $( selector ).offsetParent() );
			} );

		},

		addLoader: function (event, $scope, productID, action) {

			if( 'jet_update_compare_list' === action ){
				$('a.jet-compare-button__link[data-product-id="' + productID + '"]').addClass('jet-cw-loading');
				$('div.jet-compare-table__wrapper').addClass('jet-cw-loading');
				$('a.jet-compare-count-button__link').addClass('jet-cw-loading');
			}

			if( 'jet_update_wish_list' === action ){
				$('a.jet-wishlist-button__link[data-product-id="' + productID + '"]').addClass('jet-cw-loading');
				$('a.jet-wishlist-count-button__link').addClass('jet-cw-loading');
				$('div.jet-wishlist__content').addClass('jet-cw-loading');
			}

		},

		removeLoader: function (event, $scope, productID, action) {

			if( 'jet_update_compare_list' === action ){
				$('a.jet-compare-button__link[data-product-id="' + productID + '"]').removeClass('jet-cw-loading');
				$('div.jet-compare-table__wrapper').removeClass('jet-cw-loading');
				$('a.jet-compare-count-button__link').removeClass('jet-cw-loading');
			}

			if( 'jet_update_wish_list' === action ){
				$('a.jet-wishlist-button__link[data-product-id="' + productID + '"]').removeClass('jet-cw-loading');
				$('a.jet-wishlist-count-button__link').removeClass('jet-cw-loading');
				$('div.jet-wishlist__content').removeClass('jet-cw-loading');
			}

		},

		showMessages: function (message) {

			var compareMessage = $('.jet-compare-message--max-items');

			if ('compare_max_items' === message) {
				compareMessage.addClass('show');

				setTimeout(function () {
					compareMessage.removeClass('show');
				}, 4000);
			}

		},

		elementorFrontendInit: function( $content ) {
			$content.find( '[data-element_type]' ).each( function() {
				let $this       = $( this ),
					elementType = $this.data( 'element_type' );

				if ( ! elementType ) {
					return;
				}

				if ( 'widget' === elementType ) {
					elementType = $this.data( 'widget_type' );
					window.elementorFrontend.hooks.doAction( 'frontend/element_ready/widget', $this, $ );
				}

				window.elementorFrontend.hooks.doAction( 'frontend/element_ready/global', $this, $ );
				window.elementorFrontend.hooks.doAction( 'frontend/element_ready/' + elementType, $this, $ );
			} );
		}

	};

	if ( elementorFrontend ) {
		$( window ).on( 'elementor/frontend/init', JetCW.init );
	} else {
		JetCW.commonInit();
	}


}( jQuery, window.elementorFrontend ));