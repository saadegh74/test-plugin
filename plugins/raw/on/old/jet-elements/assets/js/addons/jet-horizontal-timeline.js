(function ($) {

    "use strict";

    function widgetHorizontalTimeline( $scope ) {

        var $timeline         = $scope.find( '.jet-hor-timeline' ),
            $timelineTrack    = $scope.find( '.jet-hor-timeline-track' ),
            $items            = $scope.find( '.jet-hor-timeline-item' ),
            $arrows           = $scope.find( '.jet-arrow' ),
            $nextArrow        = $scope.find( '.jet-next-arrow' ),
            $prevArrow        = $scope.find( '.jet-prev-arrow' ),
            columns           = {},
            prevColumns,
            slidesToScroll    = {},
            prevSlidesToScroll,
            firstMouseEvent   = true,
            currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
            prevDeviceMode    = currentDeviceMode,
            eTarget           = $timeline.closest( '.elementor-widget' ),
            breakpoints       = JetElementsTools.getElementorElementSettings( eTarget ),
            activeBreakpoints = elementorFrontend.config.responsive.activeBreakpoints || {},
            itemsCount        = $scope.find( '.jet-hor-timeline-list--middle .jet-hor-timeline-item' ).length,
            isRTL             = JetElementsTools.isRTL(),
            currentTransform  = 0,
            currentPosition   = 0,
            transform         = {},
            maxPosition       = {};

            columns['desktop'] = breakpoints['columns'];
            prevColumns        = columns['desktop'];

            transform['desktop'] = 100 / columns['desktop'];

            maxPosition['desktop'] = Math.max( 0, ( itemsCount - columns['desktop'] ) );

            slidesToScroll['desktop'] = +breakpoints['slides_to_scroll'];
            prevSlidesToScroll        = slidesToScroll['desktop'];

            Object.keys( activeBreakpoints ).reverse().forEach( function( breakpointName ) {

                if ( 'widescreen' === breakpointName ) {
                    columns[breakpointName] = ( "columns_widescreen" in breakpoints ) && '' != breakpoints.columns_widescreen ? breakpoints['columns_' + breakpointName] : columns['desktop'];

                    slidesToScroll[breakpointName] = ( "slides_to_scroll_widescreen" in breakpoints ) && '' != breakpoints.slides_to_scroll_widescreen ? +breakpoints['slides_to_scroll_' + breakpointName] : +slidesToScroll['desktop'];
                } else {
                    columns[breakpointName] = ( "" != breakpoints['columns_' + breakpointName] && undefined != breakpoints['columns_' + breakpointName] ) ? breakpoints['columns_' + breakpointName] : prevColumns;

                    prevColumns = columns[breakpointName];

                    slidesToScroll[breakpointName] = ( "" != breakpoints['slides_to_scroll_' + breakpointName] && undefined != breakpoints['slides_to_scroll_' + breakpointName] ) ? +breakpoints['slides_to_scroll_' + breakpointName] : +prevSlidesToScroll;

                    prevSlidesToScroll = slidesToScroll[breakpointName];
                }

                transform[breakpointName] = 100 / columns[breakpointName];

                maxPosition[breakpointName] = Math.max( 0, ( itemsCount - columns[breakpointName] ) );
            } );

        if ( 'ontouchstart' in window || 'ontouchend' in window ) {
            $items.on( 'touchend.jetHorTimeline', function( event ) {
                var itemId = $( this ).data( 'item-id' );

                $scope.find( '.elementor-repeater-item-' + itemId ).toggleClass( 'is-hover' );
            } );
        } else {
            $items.on( 'mouseenter.jetHorTimeline mouseleave.jetHorTimeline', function( event ) {

                if ( firstMouseEvent && 'mouseleave' === event.type ) {
                    return;
                }

                if ( firstMouseEvent && 'mouseenter' === event.type ) {
                    firstMouseEvent = false;
                }

                var itemId = $( this ).data( 'item-id' );

                $scope.find( '.elementor-repeater-item-' + itemId ).toggleClass( 'is-hover' );
            } );
        }

        // Set Line Position
        setLinePosition();
        $( window ).on( 'resize.jetHorTimeline orientationchange.jetHorTimeline', JetElementsTools.debounce( 50, setLinePosition ) );

        function setLinePosition() {
            var $line             = $scope.find( '.jet-hor-timeline__line' ),
                $firstPoint       = $scope.find( '.jet-hor-timeline-item__point-content:first' ),
                $lastPoint        = $scope.find( '.jet-hor-timeline-item__point-content:last' ),
                firstPointLeftPos = $firstPoint.position().left + parseInt( $firstPoint.css( 'marginLeft' ) ),
                lastPointLeftPos  = $lastPoint.position().left + parseInt( $lastPoint.css( 'marginLeft' ) ),
                pointWidth        = $firstPoint.outerWidth();

            $line.css( {
                'left': !isRTL ? ( firstPointLeftPos + pointWidth/2 ) : ( lastPointLeftPos + pointWidth/2 ),
                'width': Math.abs( lastPointLeftPos - firstPointLeftPos )
            } );
        }

        // Arrows Navigation Type
        if ( $nextArrow[0] && maxPosition[ currentDeviceMode ] === 0 ) {
            $nextArrow.addClass( 'jet-arrow-disabled' );
        }

        if ( $arrows[0] ) {
            var xPos = 0,
                yPos = 0,
                diffpos;

            $arrows.on( 'click.jetHorTimeline', function( event ){
                var $this             = $( this ),
                    currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
                    slidesScroll      = slidesToScroll[ currentDeviceMode ],
                    direction         = $this.hasClass( 'jet-next-arrow' ) ? 'next' : 'prev',
                    dirMultiplier     = !isRTL ? -1 : 1;

                if ( slidesScroll > columns[ currentDeviceMode ] ) {
                    slidesScroll = columns[ currentDeviceMode ];
                } else {
                    slidesScroll = slidesToScroll[ currentDeviceMode ]
                }

                $( window ).on( 'resize.jetHorTimeline orientationchange.jetHorTimeline', JetElementsTools.debounce( 50, function(){
                    currentDeviceMode = elementorFrontend.getCurrentDeviceMode();
                    slidesScroll      = slidesToScroll[ currentDeviceMode ];

                    if ( slidesScroll > columns[ currentDeviceMode ] ) {
                        slidesScroll = columns[ currentDeviceMode ];
                    } else {
                        slidesScroll = slidesToScroll[ currentDeviceMode ]
                    }
                } ) );

                if ( 'next' === direction && currentPosition < maxPosition[ currentDeviceMode ] ) {
                    currentPosition += slidesScroll;

                    if ( currentPosition > maxPosition[ currentDeviceMode ] ) {
                        currentPosition = maxPosition[ currentDeviceMode ];
                    }
                }

                if ( 'prev' === direction && currentPosition > 0 ) {
                    currentPosition -= slidesScroll;

                    if ( currentPosition < 0 ) {
                        currentPosition = 0;
                    }
                }

                if ( currentPosition > 0 ) {
                    $prevArrow.removeClass( 'jet-arrow-disabled' );
                } else {
                    $prevArrow.addClass( 'jet-arrow-disabled' );
                }

                if ( currentPosition === maxPosition[ currentDeviceMode ] ) {
                    $nextArrow.addClass( 'jet-arrow-disabled' );
                } else {
                    $nextArrow.removeClass( 'jet-arrow-disabled' );
                }

                if ( currentPosition === 0 ) {
                    currentTransform = 0;
                } else {
                    currentTransform = currentPosition * transform[ currentDeviceMode ];
                }

                $timelineTrack.css({
                    'transform': 'translateX(' + dirMultiplier * currentTransform + '%)'
                });

            } );

            $( $items ).on( 'touchstart', function( e ) {
                var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];

                xPos = touch.pageX;
            } );

            $( $items ).on( 'touchend', function( e ) {
                var touch             = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0],
                    currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
                    slidesScroll      = slidesToScroll[ currentDeviceMode ];

                yPos    = touch.pageX;
                diffpos = yPos - xPos;

                if ( diffpos < -50 ) {
                    var dirMultiplier = !isRTL ? -1 : 1;

                    if ( slidesScroll > columns[ currentDeviceMode ] ) {
                        slidesScroll = columns[ currentDeviceMode ];
                    }

                    if ( currentPosition < maxPosition[ currentDeviceMode ] ) {
                        currentPosition += slidesScroll;

                        if ( currentPosition > maxPosition[ currentDeviceMode ] ) {
                            currentPosition = maxPosition[ currentDeviceMode ];
                        }
                    }

                    if ( currentPosition > 0 ) {
                        $prevArrow.removeClass( 'jet-arrow-disabled' );
                    } else {
                        $prevArrow.addClass( 'jet-arrow-disabled' );
                    }

                    if ( currentPosition === maxPosition[ currentDeviceMode ] ) {
                        $nextArrow.addClass( 'jet-arrow-disabled' );
                    } else {
                        $nextArrow.removeClass( 'jet-arrow-disabled' );
                    }

                    if ( currentPosition === 0 ) {
                        currentTransform = 0;
                    } else {
                        currentTransform = currentPosition * transform[ currentDeviceMode ];
                    }

                    $timelineTrack.css( {
                        'transform': 'translateX(' + dirMultiplier * currentTransform + '%)'
                    } );

                } else if ( diffpos > 50 ) {
                    var dirMultiplier = !isRTL ? -1 : 1;

                    if ( slidesScroll > columns[ currentDeviceMode ] ) {
                        slidesScroll = columns[ currentDeviceMode ];
                    }

                    if ( currentPosition > 0 ) {
                        currentPosition -= slidesScroll;

                        if ( currentPosition < 0 ) {
                            currentPosition = 0;
                        }
                    }

                    if ( currentPosition > 0 ) {
                        $prevArrow.removeClass( 'jet-arrow-disabled' );
                    } else {
                        $prevArrow.addClass( 'jet-arrow-disabled' );
                    }

                    if ( currentPosition === maxPosition[ currentDeviceMode ] ) {
                        $nextArrow.addClass( 'jet-arrow-disabled' );
                    } else {
                        $nextArrow.removeClass( 'jet-arrow-disabled' );
                    }

                    if ( currentPosition === 0 ) {
                        currentTransform = 0;
                    } else {
                        currentTransform = currentPosition * transform[ currentDeviceMode ];
                    }

                    $timelineTrack.css( {
                        'transform': 'translateX(' + dirMultiplier * currentTransform + '%)'
                    } );
                }
            } );
        }

        setArrowPosition();
        $( window ).on( 'resize.jetHorTimeline orientationchange.jetHorTimeline', JetElementsTools.debounce( 150, setArrowPosition ) );
        $( window ).on( 'resize.jetHorTimeline orientationchange.jetHorTimeline', JetElementsTools.debounce( 50, timelineSliderResizeHandler ) );

        function setArrowPosition() {
            if ( ! $arrows[0] ) {
                return;
            }

            var $middleList           = $scope.find( '.jet-hor-timeline-list--middle' ),
                middleListTopPosition = $middleList.position().top,
                middleListHeight      = $middleList.outerHeight();

            $arrows.css({
                'top': middleListTopPosition + middleListHeight/2
            });
        }

        function timelineSliderResizeHandler( event ) {
            if ( ! $timeline.hasClass( 'jet-hor-timeline--arrows-nav' ) ) {
                return;
            }

            var currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
                resetSlider = function() {
                    $prevArrow.addClass( 'jet-arrow-disabled' );

                    if ( $nextArrow.hasClass( 'jet-arrow-disabled' ) ) {
                        $nextArrow.removeClass( 'jet-arrow-disabled' );
                    }

                    if ( maxPosition[ currentDeviceMode ] === 0 ) {
                        $nextArrow.addClass( 'jet-arrow-disabled' );
                    }

                    currentTransform = 0;
                    currentPosition = 0;

                    $timelineTrack.css({
                        'transform': 'translateX(0%)'
                    });
                };

            if ( currentDeviceMode != prevDeviceMode ) {
                resetSlider();
                prevDeviceMode = currentDeviceMode;
            }
        }
    }

    window.widgetHorizontalTimeline = widgetHorizontalTimeline;

})( jQuery );