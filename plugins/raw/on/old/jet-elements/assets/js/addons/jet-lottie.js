(function ($) {

    "use strict";

    function widgetLottie( $scope ) {

        var $lottie     = $scope.find( '.jet-lottie' ),
            $lottieElem = $lottie.find( '.jet-lottie__elem' ),
            settings    = $lottie.data( 'settings' ),
            options,
            instance;

        if ( ! $lottie[0] ) {
            return;
        }

        // Elementor editor can re-run widget init for listing items without
        // replacing the existing node, so destroy the previous animation first.
        if ( $lottieElem[0] ) {
            var prevInstance = $lottieElem.data( 'jet-lottie-instance' );

            if ( prevInstance && 'function' === typeof prevInstance.destroy ) {
                prevInstance.destroy();
            }

            $lottieElem.removeData( 'jet-lottie-instance' );
            $lottieElem.empty();
        }

        var loopValue = 'on_click_reversible' === settings.action_start ? false : ( '' === settings.loop_times ? settings.loop : settings.loop_times );
        
        if ( settings.reversed && loopValue ) {
            loopValue = false;
        }

        options = {
            container: $lottieElem[0],
            renderer:  settings.renderer,
            loop:      loopValue,
            autoplay:  false,
            path:      settings.path,
            name:      'jet-lottie'
        };

        instance = window.JetElementsLottie.loadAnimation( options );
        $lottieElem.data( 'jet-lottie-instance', instance );

        if ( 'canvas' === settings.renderer ) {
            var resizeObserver,
                canvasReady = false,
                resizeCanvas = function() {
                    // Tabs/popups and Elementor can initialize while hidden.
                    // Do not replace the intrinsic size with a zero bitmap.
                    if ( canvasReady && instance.isLoaded && instance.renderer && $lottieElem[0].offsetWidth && $lottieElem[0].offsetHeight ) {
                        instance.resize();
                    }
                },
                configureCanvas = function() {
                    var canvas = $lottieElem.find( 'canvas' )[0],
                        data = instance.animationData;

                    if ( ! canvas || ! data.w || ! data.h ) {
                        return;
                    }

                    // Unlike SVG, the old Canvas player has no viewBox and
                    // sizes its bitmap from the container before JSON is ready.
                    // Keep the animation ratio independent of that bitmap size.
                    canvas.width = data.w;
                    canvas.height = data.h;
                    canvas.style.display = 'block';
                    canvas.style.height = 'auto';
                    canvas.style.aspectRatio = data.w + ' / ' + data.h;
                    canvasReady = true;
                    resizeCanvas();

                    if ( window.ResizeObserver ) {
                        resizeObserver = new ResizeObserver( resizeCanvas );
                        resizeObserver.observe( $lottieElem[0] );
                    } else {
                        window.addEventListener( 'resize', resizeCanvas );
                    }
                };

            // config_ready precedes asset loading and renderer.initItems().
            // resize() forces a frame, so wait until all layers exist.
            instance.addEventListener( 'DOMLoaded', configureCanvas );
            instance.addEventListener( 'destroy', function() {
                canvasReady = false;
                if ( resizeObserver ) {
                    resizeObserver.disconnect();
                }
                window.removeEventListener( 'resize', resizeCanvas );
            } );
        }

        if ( settings.play_speed ) {
            instance.setSpeed( settings.play_speed );
        }

        if ( settings.reversed ) {
            instance.setDirection( -1 );
            var setReversedFrame = function() {
                if ( instance.totalFrames ) {
                    instance.goToAndStop( instance.totalFrames - 1, true );
                }
            };
            instance.addEventListener( 'data_ready', setReversedFrame );
            
            var originalLoopValue = 'on_click_reversible' === settings.action_start ? false : ( '' === settings.loop_times ? settings.loop : settings.loop_times );
            if ( originalLoopValue ) {
                var loopCount = 0,
                    maxLoops = '' !== settings.loop_times ? +settings.loop_times : Infinity,
                    onReversedComplete = function() {
                        if ( loopCount < maxLoops ) {
                            loopCount++;
                            if ( instance.totalFrames ) {
                                instance.goToAndPlay( instance.totalFrames - 1, true );
                            }
                        }
                    };
                instance.addEventListener( 'complete', onReversedComplete );
            }
        }

        var start = 0,
            end = 0;

        if ( settings.viewport ) {
            start = -settings.viewport.start || 0;
            end = -(100 - settings.viewport.end) || 0;
        }

        switch( settings.action_start ) {
            case 'on_hover':

                var startFlag = false,

                    onHoverHandler = function() {
                        if ( startFlag && 'reverse' === settings.on_hover_out ) {
                            var reverseValue = settings.reversed ? -1 : 1;
                            instance.setDirection( reverseValue );
                        }

                        instance.play();
                        startFlag = true;
                    },

                    onHoverOutHandler = function() {
                        switch ( settings.on_hover_out ) {
                            case 'pause':
                                instance.pause();
                                break;

                            case 'stop':
                                instance.stop();
                                break;

                            case 'reverse':
                                var reverseValue = settings.reversed ? 1 : -1;
                                instance.setDirection( reverseValue );
                                instance.play();
                        }
                    };

                $lottie
                    .off( 'mouseenter.JetLottie', onHoverHandler )
                    .on( 'mouseenter.JetLottie', onHoverHandler );

                $lottie
                    .off( 'mouseleave.JetLottie', onHoverOutHandler )
                    .on( 'mouseleave.JetLottie', onHoverOutHandler );

                break;

            case 'on_click':

                var $link = $lottie.find( '.jet-lottie__link' ),
                    redirectTimeout = +settings.redirect_timeout,

                    onClickHandler = function() {
                        if ( settings.reversed ) {
                            instance.setDirection( -1 );
                            instance.goToAndStop( instance.totalFrames - 1, true );
                        } else {
                            instance.setDirection( 1 );
                            instance.goToAndStop( instance.firstFrame, true );
                        }
                        instance.play();
                    },

                    onLinkClickHandler = function( event ) {

                        event.preventDefault();

                        if ( settings.reversed ) {
                            instance.setDirection( -1 );
                            instance.goToAndStop( instance.totalFrames - 1, true );
                        } else {
                            instance.setDirection( 1 );
                            instance.goToAndStop( instance.firstFrame, true );
                        }
                        instance.play();

                        var url = $( this ).attr( 'href' ),
                            target = '_blank' === $( this ).attr( 'target' ) ? '_blank' : '_self';

                        setTimeout( function() {
                            window.open( url, target );
                        }, redirectTimeout );
                    };

                if ( $link[0] && redirectTimeout > 0 ) {

                    $link
                        .off( 'click.JetLottie', onLinkClickHandler )
                        .on( 'click.JetLottie', onLinkClickHandler );

                } else {
                    $lottie
                        .off( 'click.JetLottie', onClickHandler )
                        .on( 'click.JetLottie', onClickHandler );
                }

                break;

            case 'on_click_reversible':

                var isReversed = settings.reversed ? true : false,
                    isClickEnabled = true,

                    onReversibleClickHandler = function() {
                        if ( ! isClickEnabled ) {
                            return;
                        }

                        isClickEnabled = false;

                        var direction = isReversed ? -1 : 1;

                        instance.setDirection( direction );
                        
                        if ( direction === -1 ) {
                            instance.goToAndPlay( instance.totalFrames - 1, true );
                        } else {
                            instance.goToAndPlay( instance.firstFrame, true );
                        }

                        isReversed = ! isReversed;							
                    },

                    onAnimationComplete = function() {
                        isClickEnabled = true;
                    };

                instance.addEventListener( 'complete', onAnimationComplete );

                $lottie
                    .off( 'click.JetLottie', onReversibleClickHandler )
                    .on( 'click.JetLottie', onReversibleClickHandler );

                break;

            case 'on_viewport':

                if ( undefined !== window.IntersectionObserver ) {
                    var observer = new IntersectionObserver(
                        function( entries, observer ) {
                            if ( entries[0].isIntersecting ) {
                                if ( settings.reversed && instance.totalFrames ) {
                                    instance.goToAndStop( instance.totalFrames - 1, true );
                                }
                                instance.play();
                            } else {
                                instance.pause();
                            }
                        },
                        {
                            rootMargin: end +'% 0% ' + start + '%'
                        }
                    );

                    observer.observe( $lottie[0] );
                } else {
                    if ( settings.reversed && instance.totalFrames ) {
                        instance.goToAndStop( instance.totalFrames - 1, true );
                    }
                    instance.play();
                }

                break;

            case 'on_scroll':

                if ( undefined !== window.IntersectionObserver ) {
                    var scrollY = 0,
                        requestId,
                        scrollObserver = new IntersectionObserver(
                            function( entries, observer ) {
                                if ( entries[0].isIntersecting ) {

                                    requestId = requestAnimationFrame( function scrollAnimation() {
                                        if ( window.scrollY !== scrollY ) {
                                            var viewportPercentage = JetElementsTools.getElementPercentageSeen( $lottieElem, { start: start, end: end } ),
                                                nextFrame = (instance.totalFrames - instance.firstFrame) * viewportPercentage / 100;

                                            instance.goToAndStop( nextFrame, true );
                                            scrollY = window.scrollY;
                                        }

                                        requestId = requestAnimationFrame( scrollAnimation );
                                    } );
                                } else {
                                    cancelAnimationFrame(requestId);
                                }
                            },
                            {
                                rootMargin: end +'% 0% ' + start + '%'
                            }
                        );

                    scrollObserver.observe( $lottie[0] );
                }

                break;

            default:
                var delay = +settings.delay,
                    playAnimation = function() {
                        instance.play();
                    };

                if ( settings.reversed ) {						
                    var setReversedFrameAndPlay = function() {
                        if ( instance.totalFrames ) {
                            instance.goToAndStop( instance.totalFrames - 1, true );
                        }
                        if ( delay > 0 ) {
                            setTimeout( playAnimation, delay );
                        } else {
                            playAnimation();
                        }
                    };
                    
                    if ( instance.totalFrames ) {
                        setReversedFrameAndPlay();
                    } else {
                        instance.addEventListener( 'data_ready', setReversedFrameAndPlay );
                    }
                } else {
                    if ( delay > 0 ) {
                        setTimeout( playAnimation, delay );
                    } else {
                        playAnimation();
                    }
                }
        }
    }

    window.widgetLottie = widgetLottie;

})( jQuery );
