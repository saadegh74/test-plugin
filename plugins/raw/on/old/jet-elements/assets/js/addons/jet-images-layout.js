(function ($) {

    "use strict";

    function widgetImagesLayout( $scope ) {

        var $target = $scope.find( '.jet-images-layout' ),
        instance = null,
        settings = {};

        if ( ! $target.length ) {
            return;
        }

        settings = $target.data( 'settings' );
        instance = new jetImagesLayout( $target, settings );
        instance.init();
    }

    /**
	 * Jet Images Layout Class
	 *
	 * @return {void}
	 */

	class jetImagesLayout {

		constructor( $selector, settings ) {
	
			var self          = this,
				$instance     = $selector,
				$instanceList = $( '.jet-images-layout__list', $instance ),
				$itemsList    = $( '.jet-images-layout__item', $instance ),
				editMode      = Boolean( elementorFrontend.isEditMode() ),
				defaultSettings = {
					layoutType: 'masonry',
					justifyHeight: 300
				};
	
			settings = settings || {};
	
			/**
			 * Merge settings
			 */
			settings = $.extend( defaultSettings, settings );
	
			/**
			 * Layout build
			 */
			self.layoutBuild = function () {
	
				switch ( settings.layoutType ) {
	
					case 'masonry':
						salvattore.init();
						break;
	
					case 'justify':
						$itemsList.each( function () {
	
							var $this         = $( this ),
								$image        = $( '.jet-images-layout__image-instance', $this ),
								imageWidth    = $image.data( 'width' ),
								imageHeight   = $image.data( 'height' ),
								imageRatio    = +imageWidth / +imageHeight,
								flexValue     = imageRatio * 100,
								newWidth      = settings.justifyHeight * imageRatio;
	
							$this.css( {
								'flex-grow': flexValue,
								'flex-basis': newWidth
							} );
						} );
						break;
				}
	
				if ( $.isFunction( $.fn.imagesLoaded ) ) {
	
					$( '.jet-images-layout__image', $itemsList )
						.imagesLoaded()
						.progress( function ( instance, image ) {
	
							var $image      = $( image.img ),
								$parentItem = $image.closest( '.jet-images-layout__item' ),
								$loader     = $( '.jet-images-layout__image-loader', $parentItem );
	
							$parentItem.addClass( 'image-loaded' );
	
							$loader.fadeTo( 500, 0, function () {
								$( this ).remove();
							} );
						} );
	
				} else {
	
					var $loader = $( '.jet-images-layout__image-loader', $itemsList );
	
					$itemsList.addClass( 'image-loaded' );
	
					$loader.fadeTo( 500, 0, function () {
						$( this ).remove();
					} );
				}
			};
	
			/**
			 * Init
			 */
			self.init = function () {
				self.layoutBuild();
			};
		}
	}
	
    window.widgetImagesLayout = widgetImagesLayout;

})( jQuery );