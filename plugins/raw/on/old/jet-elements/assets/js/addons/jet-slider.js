(function ($) {

    "use strict";

    function widgetSlider( $scope ) {

        var $target         = $scope.find( '.jet-slider' ),
        $imagesTagList  = $( '.sp-image', $target ),
        item            = $( '.jet-slider__item', $target ),
        instance        = null,
        item_url        = '',
        item_url_target = '',
        defaultSettings = {
            imageScaleMode: 'cover',
            slideDistance: { size: 10, unit: 'px' },
            slideDuration: 500,
            sliderAutoplay: true,
            sliderAutoplayDelay: 2000,
            sliderAutoplayOnHover: 'pause',
            sliderFadeMode: false,
            sliderFullScreen: true,
            sliderFullscreenIcon: '',
            sliderHeight: { size: 600, unit: 'px' },
            sliderLoop: true,
            sliderNaviOnHover: false,
            sliderNavigation: true,
            sliderNavigationIcon: '',
            sliderPagination: false,
            sliderShuffle: false,
            sliderWidth: { size: 100, unit: '%' },
            thumbnailWidth: 120,
            thumbnailHeight: 80,
            thumbnails: true,
            rightToLeft: false,
        },
        instanceSettings    = $target.data( 'settings' ) || {},
        breakpoints         = JetElementsTools.getElementorElementSettings( $scope ),
        breakpointsSettings = {},
        defaultHeight,
        defaultThumbHeight,
        defaultThumbWidth,
        activeBreakpoints   = elementorFrontend.config.responsive.activeBreakpoints || {},
        settings            = $.extend( {}, defaultSettings, instanceSettings ),
        fraction_nav        = $target.find( '.jet-slider__fraction-pagination' ),
        editMode            = Boolean( elementorFrontend.isEditMode() );

        if ( ! $target.length ) {
            return;
        }

        function updateFractionPagination( slider ) {
            if ( true !== settings['fractionPag'] ) {
                return;
            }

            var current = ( slider.getSelectedSlide() || 0 ) + 1,
                total = slider.getTotalSlides();

            if ( ! fraction_nav.find( '.fraction-wrapper' ).length ) {
                fraction_nav.empty().append(
                    $( '<span class="fraction-wrapper">' ).append(
                        $( '<span class="current">' ),
                        $( '<span class="separator">' ),
                        $( '<span class="total">' )
                    )
                );
            }

            fraction_nav.find( '.current' ).text( ( settings['fractionPrefix'] || '' ) + ' ' + current );
            fraction_nav.find( '.separator' ).text( settings['fractionSeparator'] || '/' );
            fraction_nav.find( '.total' ).text( ( settings['fractionSuffix'] || '' ) + ' ' + total );
        }

        if ( !editMode ) {
            $target.on( 'mousedown touchstart', '.jet-slider__content[data-slide-url]', function( e ) {
                window.XPos = e.pageX || e.originalEvent.changedTouches[0].pageX;
                window.YPos = e.pageY || e.originalEvent.changedTouches[0].pageY;
            });

            $target.on( 'mouseup touchend', '.jet-slider__content[data-slide-url]', function( e ) {
                var $this = $( this ),
                    item_url = $this.data( 'slide-url' ),
                    item_url_target = $this.data( 'slide-url-target' ),
                    clickXPos = e.pageX || e.originalEvent.changedTouches[0].pageX,
                    clickYPos = e.pageY || e.originalEvent.changedTouches[0].pageY;

                if ( window.XPos === clickXPos && window.YPos === clickYPos ) {
                    e.preventDefault();
                    if ( '_blank' === item_url_target ) {
                        window.open( item_url );
                    } else {
                        window.location = item_url;
                    }
                }
            } );
        }

        defaultHeight = ( breakpoints['slider_height'] && 'custom' === breakpoints['slider_height']['unit'] ) ? breakpoints['slider_height']['size'] : ( '' != breakpoints['slider_height']['size'] ) ? breakpoints['slider_height']['size'] + breakpoints['slider_height']['unit'] : '600px';


        defaultThumbHeight = ( 'thumbnail_height' in breakpoints && '' != breakpoints['thumbnail_height'] ) ? breakpoints['thumbnail_height'] : 80;

        defaultThumbWidth  = ( 'thumbnail_width' in breakpoints && '' != breakpoints['thumbnail_width'] ) ? breakpoints['thumbnail_width'] : 120;

        var wHeight   = $( window ).height(),
            dHeight   = $( document ).height(),
            offsetfix = ( editMode && wHeight < dHeight ) ? 18 : 1;

        Object.keys( activeBreakpoints ).forEach( function( breakpointName ) {

            if ( 'widescreen' === breakpointName ) {

                var breakpoint = activeBreakpoints[breakpointName].value - offsetfix,

                breakpointHeight = ( breakpoints['slider_height_' + breakpointName] && 'custom' === breakpoints['slider_height_' + breakpointName]['unit'] ) ? breakpoints['slider_height']['size'] : ( '' != breakpoints['slider_height_' + breakpointName]['size'] ) ? breakpoints['slider_height_' + breakpointName]['size'] + breakpoints['slider_height_' + breakpointName]['unit'] : defaultHeight,

                    breakpointThumbHeight = '' != breakpoints['thumbnail_height_' + breakpointName] ? breakpoints['thumbnail_height_' + breakpointName] : defaultThumbHeight,

                    breakpointThumbWidth  = '' != breakpoints['thumbnail_width_' + breakpointName] ? breakpoints['thumbnail_width_' + breakpointName] : defaultThumbWidth,

                    desktopHeight      = '' != breakpoints['slider_height']['size'] ? breakpoints['slider_height']['size'] + breakpoints['slider_height']['unit'] : settings['sliderHeight']['size'] + settings['sliderHeight']['unit'],

                    desktopThumbHeight = '' != breakpoints['thumbnail_height'] ? breakpoints['thumbnail_height'] : settings['thumbnailHeight'],

                    desktopThumbWidth  = '' != breakpoints['thumbnail_width'] ? breakpoints['thumbnail_width'] : settings['thumbnailWidth'];

                if ( breakpointHeight || breakpointThumbHeight || breakpointThumbWidth ) {
                    breakpointsSettings[breakpoint] = {};
                } else {
                    return;
                }

                if ( breakpointHeight ) {
                    defaultHeight = breakpointHeight;

                    breakpointsSettings[breakpoint]['height'] = desktopHeight;
                }

                if ( breakpointThumbHeight ) {
                    defaultThumbHeight = breakpointThumbHeight;

                    breakpointsSettings[breakpoint]['thumbnailHeight'] = desktopThumbHeight;
                }

                if ( breakpointThumbWidth ) {
                    defaultThumbWidth = breakpointThumbWidth;

                    breakpointsSettings[breakpoint]['thumbnailWidth'] = desktopThumbWidth;
                }

            } else {
                var breakpoint = activeBreakpoints[breakpointName].value - offsetfix,

                    breakpointThumbHeight = breakpoints['thumbnail_height_' + breakpointName] ? breakpoints['thumbnail_height_' + breakpointName] : false,
                    breakpointThumbWidth  = breakpoints['thumbnail_width_' + breakpointName] ? breakpoints['thumbnail_width_' + breakpointName] : false;

                    breakpointHeight = ( 'custom' === breakpoints['slider_height_' + breakpointName]['unit'] ) ? breakpoints['slider_height_' + breakpointName]['size'] : ( '' != breakpoints['slider_height_' + breakpointName]['size'] ) ? breakpoints['slider_height_' + breakpointName]['size'] + breakpoints['slider_height_' + breakpointName]['unit'] : false;

                if ( breakpointHeight || breakpointThumbHeight || breakpointThumbWidth ) {
                    breakpointsSettings[breakpoint] = {};
                } else {
                    return;
                }

                if ( breakpointHeight ) {
                    breakpointsSettings[breakpoint]['height'] = breakpointHeight;
                }

                if ( breakpointThumbHeight ) {
                    breakpointsSettings[breakpoint]['thumbnailHeight'] = breakpointThumbHeight;
                }

                if ( breakpointThumbWidth ) {
                    breakpointsSettings[breakpoint]['thumbnailWidth'] = breakpointThumbWidth;
                }
            }
        } );

        $( '.slider-pro', $target ).sliderPro( {
            width: settings['sliderWidth']['size'] + settings['sliderWidth']['unit'],
            height: defaultHeight,
            arrows: settings['sliderNavigation'],
            fadeArrows: settings['sliderNaviOnHover'],
            buttons: settings['sliderPagination'],
            autoplay: settings['sliderAutoplay'],
            autoplayDelay: settings['sliderAutoplayDelay'],
            autoplayOnHover: settings['sliderAutoplayOnHover'],
            fullScreen: settings['sliderFullScreen'],
            shuffle: settings['sliderShuffle'],
            loop: settings['sliderLoop'],
            fade: settings['sliderFadeMode'],
            slideDistance: ( 'string' !== typeof settings['slideDistance']['size'] ) ? settings['slideDistance']['size'] : 0,
            slideAnimationDuration: +settings['slideDuration'],
            imageScaleMode: 'exact',
            waitForLayers: false,
            grabCursor: false,
            thumbnailWidth: defaultThumbWidth,
            thumbnailHeight: defaultThumbHeight,
            rightToLeft: settings['rightToLeft'],
            touchSwipe: settings['touchswipe'],
            init: function() {
                var fullscreenIconHtml = $( '.' + settings['sliderFullscreenIcon'] ).html(),
                    arrowIconHtml      = $( '.' + settings['sliderNavigationIcon'] ).html();

                $( '.sp-full-screen-button', $target ).html( fullscreenIconHtml );
                $( '.sp-previous-arrow', $target ).html( arrowIconHtml );
                $( '.sp-next-arrow', $target ).html( arrowIconHtml );
                $( '.slider-pro', $target ).addClass( 'slider-loaded' );

                if ( settings.autoSliderHeight ) {

                    var $slider = $('.slider-pro', $target);
                                
                    // Update mask, imageContainer and slide height
                    function updateHeight() {
                        var $slider = $( '.slider-pro', $target ),
                            $activeSlide = $slider.find( '.jet-slider__item.sp-slide.sp-selected' ),
                            $inner = $activeSlide.find( '.jet-slider__content-inner' ),
                            $mask = $slider.find( '.sp-mask.sp-grab' ),
                            $imageContainer = $activeSlide.find( '.sp-image-container' ),
                            isFullscreen = $slider.hasClass( 'sp-full-screen' );
            
                            if ( $activeSlide.find( '.elementor' ).length > 0 ) {
                                var $innerHeight = $inner.outerHeight();
                                if ( isFullscreen ) {
                                    // In fullscreen mode, use the window height
                                    var windowHeight = $( window ).height();
                                    $mask.css( 'height', windowHeight + 'px' );
                                    $imageContainer.css( 'height', windowHeight + 'px' );
                                    $activeSlide.css( 'height', windowHeight + 'px' );
                                } else {
                                    $mask.css( 'height', $innerHeight + 'px' );
                                    $imageContainer.css( 'height', $innerHeight + 'px' );
                                    $activeSlide.css( 'height', $innerHeight + 'px' );
                                }
                            } else {
                                if ( isFullscreen ) {
                                    var windowHeight = $( window ).height();
                                    $mask.css( 'height', windowHeight + 'px' );
                                    $imageContainer.css( 'height', windowHeight + 'px' );
                                    $activeSlide.css( 'height', windowHeight + 'px' );
                                } else {
                                    $mask.css( 'height', defaultHeight );
                                    $imageContainer.css( 'height', defaultHeight );
                                    $activeSlide.css( 'height', defaultHeight );
                                }
                            }
                    }
            
                    // Initialize ResizeObserver
                    var resizeObserver = new ResizeObserver( function( entries ) {
                        for ( let entry of entries ) {
                            updateHeight();
                        }
                    });
        
                    function observeActiveSlide() {
                        var $activeSlide = $slider.find( '.jet-slider__item.sp-slide.sp-selected' ),
                            $inner = $activeSlide.find( '.jet-slider__content-inner' );
        
                        if ( $inner.length ) {
                            resizeObserver.observe( $inner.get( 0 ) );
                        }
                    }
        
                    // Initialize all slides
                    $slider.find( '.jet-slider__item.sp-slide' ).each( function() {
                        var $this = $( this ),
                            $inner = $this.find( '.jet-slider__content-inner' ),
                            $imageContainer = $this.find( '.sp-image-container' ),
                            $innerHeight = $inner.outerHeight();
        
                        if ($this.find( '.elementor' ).length > 0 ) {
                            $this.css( 'height', $innerHeight + 'px' );
                            $imageContainer.css( 'height', $innerHeight + 'px' );
                        }
                    });
        
                    updateHeight();
                    observeActiveSlide();
        
                    // On slide change
                    $slider.on( 'gotoSlide', function() {
                        resizeObserver.disconnect();
                        updateHeight();
                        observeActiveSlide();
                    });
                }
        
                this.resize();
                setTimeout( function() {
                    $( '.slider-pro', $target ).sliderPro( 'resize' );
                }, 250 );
            },
            gotoSlideComplete: function() {
                updateFractionPagination( this );

                elementorFrontend.elements.$window.trigger("elementor/bg-video/recalc");
            },
            update: function() {
                updateFractionPagination( this );
            },
            breakpoints: breakpointsSettings
        } );

        $( '.slider-pro', $target ).on( 'gotoSlide', function() {
            $target.find( '[data-element_type]' ).each( function() {
                window.elementorFrontend.hooks.doAction( 'frontend/element_ready/global', $( this ), $ );
            } );
        } );

        $( '.jet-mega-menu-item' ).each(function () {

            var target = this;
        
            var observer = new MutationObserver( function ( mutations ) {
        
                mutations.forEach( function ( mutation ) {
        
                    if ( mutation.attributeName === 'class' ) {
        
                        var $el = $( mutation.target );
        
                        if ( $el.hasClass( 'jet-mega-menu-item--hover' )) {
        
                            setTimeout( function () {
        
                                $el.find( '.slider-pro' ).each( function () {
        
                                    if ($( this ).data( 'sliderPro' )) {
                                        $( this ).sliderPro( 'update' );
                                        $( this ).sliderPro( 'resize' );
                                    }
        
                                });
        
                            }, 80 );
        
                        }
                    }
                });
        
            });
        
            observer.observe(target, {
                attributes: true
            });
        
        });
    }

    window.widgetSlider = widgetSlider;

})( jQuery );
