(function( $ ) {

	'use strict';

	var JetTabsEditor,
		JetTabsData = window.JetTabsData || {};

	JetTabsEditor = {

		modal: false,
		editedModel: false,
		panelObserver: null,
		syncTimer: null,

		init: function() {
			window.elementor.on( 'preview:loaded', JetTabsEditor.onPreviewLoaded );

			JetTabsEditor.initTabsResponsiveControlsSync();
		},

		initTabsResponsiveControlsSync: function() {
			JetTabsEditor.injectTabsControlsSyncStyles();

			document.addEventListener( 'change', JetTabsEditor.scheduleTabsControlsSync, true );
			document.addEventListener( 'input', JetTabsEditor.scheduleTabsControlsSync, true );
			document.addEventListener( 'click', JetTabsEditor.scheduleTabsControlsSync, true );

			if ( window.elementor.channels && window.elementor.channels.deviceMode ) {
				window.elementor.channels.deviceMode.on( 'change', JetTabsEditor.scheduleTabsControlsSync );
			}

			setInterval( JetTabsEditor.syncTabsResponsiveControls, 300 );
		},

		injectTabsControlsSyncStyles: function() {
			if ( document.getElementById( 'jet-tabs-editor-controls-sync-style' ) ) {
				return;
			}

			$( '<style id="jet-tabs-editor-controls-sync-style">.jet-tabs-force-hidden-control{display:none!important;}</style>' ).appendTo( 'head' );
		},

		scheduleTabsControlsSync: function() {
			setTimeout( JetTabsEditor.syncTabsResponsiveControls, 0 );
			setTimeout( JetTabsEditor.syncTabsResponsiveControls, 100 );
			setTimeout( JetTabsEditor.syncTabsResponsiveControls, 300 );
		},

		getCurrentTabsPositionFromPanel: function() {
			var $selects = $(
				'select[data-setting="tabs_position"],' +
				'select[data-setting="tabs_position_tablet"],' +
				'select[data-setting="tabs_position_mobile"]'
			    ),
			    $select = $selects.filter( ':visible' ).last(),
			    value = '';

			if ( ! $select.length ) {
				$select = $selects.last();
			}

			if ( ! $select.length ) {
				return '';
			}

			value = $select.val();

			return $.trim( String( value || '' ) ).toLowerCase();
		},

		getControlRow: function( controlName ) {
			return $( '.elementor-control-' + controlName + ', [class*="elementor-control-' + controlName + '"]' );
		},

		getControlValue: function( controlName ) {
			var $row = JetTabsEditor.getControlRow( controlName ),
			    $checkbox,
			    $select,
			    $input,
			    value = '';

			if ( ! $row.length ) {
				return '';
			}

			$checkbox = $row.find( 'input[type="checkbox"]' ).first();

			if ( $checkbox.length ) {
				return $checkbox.prop( 'checked' ) ? 'yes' : '';
			}

			$select = $row.find( 'select' ).first();

			if ( $select.length ) {
				return $select.val() || '';
			}

			$input = $row.find( 'input[data-setting="' + controlName + '"]' ).first();

			if ( $input.length ) {
				value = $input.val();
			}

			return value || '';
		},

		toggleControlRow: function( controlName, isVisible ) {
			var $row = JetTabsEditor.getControlRow( controlName );

			if ( ! $row.length ) {
				return;
			}

			$row.toggleClass( 'jet-tabs-force-hidden-control', ! isVisible );
		},

		syncTabsResponsiveControls: function() {
			var position = JetTabsEditor.getCurrentTabsPositionFromPanel(),
			    isHorizontalPosition,
			    isScrollingEnabled,
			    scrollType,
			    isSlider,
			    isFixedSlideWidth;

			if ( ! position ) {
				return;
			}

			isHorizontalPosition = -1 !== [ 'top', 'bottom' ].indexOf( position );

			isScrollingEnabled = 'yes' === JetTabsEditor.getControlValue( 'tab_scrolling_navigation' );
			scrollType = JetTabsEditor.getControlValue( 'tab_scroll_type' ) || 'plain';
			isSlider = 'slider' === scrollType;
			isFixedSlideWidth = 'yes' === JetTabsEditor.getControlValue( 'fixed_slide_width' );

			JetTabsEditor.toggleControlRow( 'tab_scrolling_navigation', isHorizontalPosition );
			JetTabsEditor.toggleControlRow( 'tab_scroll_type', isHorizontalPosition && isScrollingEnabled );
			JetTabsEditor.toggleControlRow( 'fixed_slide_width', isHorizontalPosition && isScrollingEnabled && isSlider );
			JetTabsEditor.toggleControlRow( 'slide_width_value', isHorizontalPosition && isScrollingEnabled && isSlider && isFixedSlideWidth );
			JetTabsEditor.toggleControlRow( 'slider_centered', isHorizontalPosition && isScrollingEnabled && isSlider );
			JetTabsEditor.toggleControlRow( 'slider_looped', isHorizontalPosition && isScrollingEnabled && isSlider );
			JetTabsEditor.toggleControlRow( 'slider_show_nav', isHorizontalPosition && isScrollingEnabled && isSlider );
		},

		onPreviewLoaded: function() {
			var $previewContents = window.elementor.$previewContents,
				elementorFrontend = $('#elementor-preview-iframe')[0].contentWindow.elementorFrontend;

			elementorFrontend.hooks.addAction( 'frontend/element_ready/jet-tabs.default', function( $scope ){
				$scope.find( '.jet-tabs__edit-cover' ).on( 'click', JetTabsEditor.showTemplatesModal );
				$scope.find( '.jet-tabs-new-template-link' ).on( 'click', function( event ) {
					window.location.href = $( this ).attr( 'href' );
				} );
			} );

			elementorFrontend.hooks.addAction( 'frontend/element_ready/jet-accordion.default', function( $scope ){
				$scope.find( '.jet-toggle__edit-cover' ).on( 'click', JetTabsEditor.showTemplatesModal );
				$scope.find( '.jet-toogle-new-template-link' ).on( 'click', function( event ) {
					window.location.href = $( this ).attr( 'href' );
				} );
			} );

			elementorFrontend.hooks.addAction( 'frontend/element_ready/jet-switcher.default', function( $scope ){
				$scope.find( '.jet-switcher__edit-cover' ).on( 'click', JetTabsEditor.showTemplatesModal );
				$scope.find( '.jet-switcher-new-template-link' ).on( 'click', function( event ) {
					window.location.href = $( this ).attr( 'href' );
				} );
			} );

			JetTabsEditor.getModal().on( 'hide', function() {
				window.elementor.reloadPreview();
			});
		},

		showTemplatesModal: function() {
			var editLink = $( this ).data( 'template-edit-link' );

			JetTabsEditor.showModal( editLink );
		},

		showModal: function( link ) {
			var $iframe,
				$loader;

			JetTabsEditor.getModal().show();

			$( '#jet-tabs-template-edit-modal .dialog-message').html( '<iframe src="' + link + '" id="jet-tabs-edit-frame" width="100%" height="100%"></iframe>' );
			$( '#jet-tabs-template-edit-modal .dialog-message').append( '<div id="jet-tabs-loading"><div class="elementor-loader-wrapper"><div class="elementor-loader"><div class="elementor-loader-boxes"><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div><div class="elementor-loader-box"></div></div></div><div class="elementor-loading-title">Loading</div></div></div>' );

			$iframe = $( '#jet-tabs-edit-frame');
			$loader = $( '#jet-tabs-loading');

			$iframe.on( 'load', function() {
				$loader.fadeOut( 300 );
			} );
		},

		getModal: function() {

			if ( ! JetTabsEditor.modal ) {
				this.modal = elementor.dialogsManager.createWidget( 'lightbox', {
					id: 'jet-tabs-template-edit-modal',
					closeButton: true,
					closeButtonClass: 'eicon-close',
					hide: {
						onBackgroundClick: false
					}
				} );
			}

			return JetTabsEditor.modal;
		}

	};

	$( window ).on( 'elementor:init', JetTabsEditor.init );

})( jQuery );
