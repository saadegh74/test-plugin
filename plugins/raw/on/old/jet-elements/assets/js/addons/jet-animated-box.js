(function ($) {

    "use strict";

    function widgetAnimatedBox( $scope ) {

        onAnimatedBoxSectionActivated( $scope );
        initAnimatedBoxLoopCarouselFallback();

        var $target         = $scope.find( '.jet-animated-box' ),
            defaultSettings = {
                widgetId: null,
                switchEventType: 'hover',
                paperFoldDirection: 'left',
                slideOutDirection: 'left',
                peelCornerPosition: 'right'
            },
            settings        = $target.data( 'settings' ),
            settings        = $.extend( {}, defaultSettings, settings ),
            scrollOffset    = $( window ).scrollTop(),
            firstMouseEvent = true,
            editMode        = Boolean( elementorFrontend.isEditMode() ),
            backButton      = $( '.jet-animated-box__button--back', $scope );

        if ( ! $target.length ) {
            return;
        }

        $target.data( 'jetAnimatedBoxInitialized', true );

        backButton.on( 'touchend', function( event ) {
            event.stopPropagation();
        } );

        switch( settings['switchEventType'] ) {
            case 'hover':
                if ( ! editMode ) {
                    hoverSwitchType();
                } else {
                    clickSwitchType();
                }

                break;

            case 'click':
                clickSwitchType();
                break;

            case 'toggle':
                toggleSwitchType();
                break;

            case 'scratch':
                scratchSwitchTypeFunc();
                break;

            case 'fold':
                foldSwitchType();
                break;

            case 'peel':
                peelSwitchType( settings['peelCornerPosition'] );
                break;

            case 'slide-out':
                slideOutSwitchType();
                break;
        }

        function hoverSwitchType() {

            if ( 'ontouchend' in window || 'ontouchstart' in window ) {
                $target.on( 'touchstart', function( event ) {
                    scrollOffset = $( window ).scrollTop();
                } );

                $target.on( 'touchend', function( event ) {

                    if ( scrollOffset !== $( window ).scrollTop() ) {
                        return false;
                    }

                    var $this = $( this );

                    if ( $this.hasClass( 'flipped-stop' ) ) {
                        return;
                    }

                    setTimeout( function() {
                        $this.toggleClass( 'flipped' );
                    }, 10 );

                    $this.find( '.jet-animated-box__button--back' ).on( 'focus', function() {
                        if ( ! $this.hasClass( 'flipped-stop' ) ) {
                            $this.addClass( 'flipped' );
                        }
                    } );
                        
                    $this.find( '.jet-animated-box__button--back' ).on( 'focusout', function() {
                        $this.removeClass( 'flipped' )
                    } );

                } );

                $( document ).on( 'touchend', function( event ) {

                    if ( $( event.target ).closest( $target ).length ) {
                        return;
                    }

                    $target.not( '.flipped-stop' ).filter( '.flipped' ).removeClass( 'flipped' );
                } );
            } else {

                $target.on( 'mouseenter mouseleave', function( event ) {

                    if ( firstMouseEvent && 'mouseleave' === event.type ) {

                        if ( ! $( this ).hasClass( 'flipped-stop' ) ) {
                            $( this ).removeClass( 'flipped' );
                        }
                        return;
                    }

                    if ( firstMouseEvent && 'mouseenter' === event.type ) {

                        if ( ! $( this ).hasClass( 'flipped-stop' ) ) {
                            $( this ).addClass( 'flipped' );
                        }

                        //firstMouseEvent = false;
                    }

                } );

                backButton.on( 'focus', function() {
                    var $box = $( this ).closest( '.jet-animated-box' );

                    if ( ! $box.hasClass( 'flipped-stop' ) ) {
                        $box.addClass( 'flipped' );
                    }
                } );

                backButton.on( 'focusout', function() {
                    $( this ).closest( '.jet-animated-box' ).removeClass( 'flipped' )
                } );
            }
        }

        function clickSwitchType() {
            if ( 'ontouchend' in window || 'ontouchstart' in window ) {
                $target.on( 'touchstart', function( event ) {
                    scrollOffset = $( window ).scrollTop();
                } );

                $target.on( 'touchend', function( event ) {

                    if ( scrollOffset !== $( window ).scrollTop() ) {
                        return false;
                    }

                    var $this = $( this );

                    if ( $this.hasClass( 'flipped-stop' ) ) {
                        return;
                    }

                    setTimeout( function() {
                        $this.toggleClass( 'flipped' );
                    }, 10 );
                } );

                $( document ).on( 'touchend', function( event ) {

                    if ( $( event.target ).closest( $target ).length ) {
                        return;
                    }

                    $target.not( '.flipped-stop' ).filter( '.flipped' ).removeClass( 'flipped' );
                } );
            } else {
                $target.on( 'click', function( event ) {
                    var $this = $( this );

                    if ( ! $this.hasClass( 'flipped-stop' ) ) {
                        $this.toggleClass( 'flipped' );
                    }
                } );

                backButton.on( 'focus', function() {
                    var $box = $( this ).closest( '.jet-animated-box' );

                    if ( ! $box.hasClass( 'flipped-stop' ) ) {
                        $box.addClass( 'flipped' );
                    }
                } );

                backButton.on( 'focusout', function() {
                    $( this ).closest( '.jet-animated-box' ).removeClass( 'flipped' )
                } );
            }
        }

        function toggleSwitchType() {
            if ( 'ontouchend' in window || 'ontouchstart' in window ) {
                $target.on( 'touchstart', '.jet-animated-box__toggle', function( event ) {
                    var $box = $( this ).closest( '.jet-animated-box' );

                    if ( ! $box.hasClass( 'flipped-stop' ) ) {
                        $box.toggleClass( 'flipped' );
                    }
                } );
            } else {
                $target.on( 'click', '.jet-animated-box__toggle', function( event ) {
                    var $box = $( this ).closest( '.jet-animated-box' );

                    if ( ! $box.hasClass( 'flipped-stop' ) ) {
                        $box.toggleClass( 'flipped' );
                    }
                } );
            }

            backButton.on( 'focus', function() {
                var $box = $( this ).closest( '.jet-animated-box' );

                if ( ! $box.hasClass( 'flipped-stop' ) ) {
                    $box.addClass( 'flipped' );
                }
            } );

            backButton.on( 'focusout', function() {
                $( this ).closest( '.jet-animated-box' ).removeClass( 'flipped' )
            } );
        }

        function scratchSwitchTypeFunc() {

            var popupSettings = $( $target ).closest( '.jet-popup' ).data( 'settings' ),
                popupDelay    = 420,
                requestIdle   = window.requestIdleCallback || function( callback ) {
                    return window.setTimeout( callback, 120 );
                },
                requestFrame  = window.requestAnimationFrame || function( callback ) {
                    return window.setTimeout( callback, 16 );
                };

            warmupScratchRenderer();

            function queueScratchSwitchType( attempt ) {
                attempt = attempt || 0;

                requestFrame( function() {
                    requestFrame( function() {
                        if ( ! $target.is( ':visible' ) || ! $target.outerWidth() || ! $target.outerHeight() ) {
                            if ( attempt < 10 ) {
                                queueScratchSwitchType( attempt + 1 );
                            }

                            return;
                        }

                        scratchSwitchType();
                    } );
                } );
            }

            if ( $( $target ).closest( '.jet-popup' ).length && ( popupSettings && popupSettings[ 'use-ajax' ] === false ) ) {
                $( window ).one( 'jet-popup-open-trigger', function() {
                    window.setTimeout( function() {
                        requestIdle( function() {
                            queueScratchSwitchType();
                        } );
                    }, popupDelay );
                } );
            } else {
                queueScratchSwitchType();
            }
        }

        function warmupScratchRenderer() {
            var $warmupNode,
                requestIdle = window.requestIdleCallback || function( callback ) {
                    return window.setTimeout( callback, 100 );
                };

            if (
                $( document ).data( 'jetScratchRendererWarmed' ) ||
                $( document ).data( 'jetScratchRendererWarming' ) ||
                'function' !== typeof html2canvas
            ) {
                return;
            }

            $( document ).data( 'jetScratchRendererWarming', true );

            requestIdle( function() {
                $warmupNode = $( '<div class="jet-scratch-warmup"></div>' ).css( {
                    position: 'fixed',
                    top: '-9999px',
                    left: '-9999px',
                    width: '2px',
                    height: '2px',
                    opacity: 0,
                    pointerEvents: 'none',
                    zIndex: -1
                } );

                $( 'body' ).append( $warmupNode );

                html2canvas( $warmupNode[0], {
                    allowTaint: true,
                    backgroundColor: null,
                    windowWidth: $( window ).width(),
                    windowHeight: $( window ).height(),
                    scrollX: 0,
                    scrollY: 0
                } ).then( function() {
                    $( document ).data( 'jetScratchRendererWarmed', true );
                } ).finally( function() {
                    $( document ).removeData( 'jetScratchRendererWarming' );
                    $warmupNode.remove();
                } );
            } );
        }

        function scratchSwitchType() {
            var $container      = document.querySelector( '#jet-animated-box-' + settings['widgetId'] ),
                rect            = $container.getBoundingClientRect(),
                baseTopDistance = rect.top;

            if ( editMode ) {
                return false;
            }

            var windowWidth = $( window ).width();

            html2canvas( document.querySelector( '#jet-animated-box__front-' + settings['widgetId'] ), {
                allowTaint: true,
                backgroundColor: null,
                windowWidth: $( window ).width(),
                windowHeight: $( window ).height(),
                scrollX: 0,
                scrollY: -window.scrollY,
            } ).then( function( canvas ) {
                canvas.setAttribute( 'id', 'jet-animated-box-canvas-' + settings['widgetId'] );
                $target.prepend( canvas );

                $( '.jet-animated-box__front', $target ).fadeOut( 300, function() {
                    $( this ).remove();
                } );

                $( window ).one( 'resize.jetScratch', function() {
                    if ( $( window ).width() !== windowWidth ) {
                        windowWidth = $( window ).width();

                        $( canvas ).fadeOut( 250, function() {
                            $( this ).remove();
                        } );
                    }
                } );

                new jetScratchEffect(
                    '#jet-animated-box-' + settings['widgetId'],
                    '#jet-animated-box-canvas-' + settings['widgetId'],
                    function() {
                        $( canvas ).fadeOut( 300, function() {
                            $( this ).remove();
                            $target.removeClass( 'back-events-inactive' );
                        } );
                    },
                    settings['scratchFillPercent'],
                    baseTopDistance
                );
            });
        }

        function foldSwitchType() {

            if ( editMode ) {
                $target.addClass( 'fold-init' );

                return false;
            }

            var folded        = null,
                frontSelector = '#jet-animated-box__front-' + settings['widgetId'];

            folded = new OriDomi( document.querySelector( frontSelector ), {
                vPanels:          5,
                hPanels:          5,
                speed:            500,
                ripple:           true,
                shadingIntensity: .9,
                perspective:      1000,
                //maxAngle:         90,
                shading:          false,
                gapNudge:          0,
                touchSensitivity: .25,
                touchMoveCallback: function( moveCoordinate, event ) {

                    if ( 89.5 < moveCoordinate ) {
                        $( frontSelector ).remove();
                    }
                }
            }).accordion( 0, settings['paperFoldDirection'] );

            $target.addClass( 'fold-init' );

            backButton.on( 'focus', function() {
                folded.foldUp();
            } );

            backButton.on( 'focusout', function() {
                folded.accordion( 0, settings['paperFoldDirection'] );
            } );
        }

        function peelSwitchType( peelCornerPosition ) {

            if ( editMode ) {
                $target.addClass( 'peel-ready' );

                return false;
            }

            var $front = $( '.jet-animated-box__front', $target ),
                $frontClone = $front.clone();

            $( '.jet-animated-box__front', $target ).addClass( 'peel-top' );

            $frontClone.removeAttr('id');
            $frontClone.addClass('peel-back');
            $frontClone.insertAfter( '#jet-animated-box__front-' + settings['widgetId'] );

            $( '.jet-animated-box__back', $target ).addClass( 'peel-bottom' );

            var targetWidth = $target.width(),
                targetHeight = $target.height();

            if ( 'left' === peelCornerPosition ) {
                var peel = new Peel( '#jet-animated-box-' + settings['widgetId'], {
                    corner: Peel.Corners.TOP_LEFT
                } );

                peel.setPeelPosition( 30, 40 );
            } else {
                var peel = new Peel( '#jet-animated-box-' + settings['widgetId'], {
                    corner: Peel.Corners.TOP_RIGHT
                } );

                peel.setPeelPosition( targetWidth - 30, 40 );
            }

            peel.setFadeThreshold(.8);

            backButton.on( 'focus', function() {
                peel.removeEvents();

                $( '.peel-top, .peel-back, .peel-bottom-shadow', $target ).remove();
            } );

            peel.handleDrag( function( evt, x, y ) {
                var targetOffset = $target.offset(),
                    offsetX      = targetOffset.left,
                    offsetY      = targetOffset.top,
                    deltaX       = x - offsetX,
                    deltaY       = y - offsetY;

                deltaX = deltaX < 0 ? deltaX*=3 : deltaX;
                deltaY = deltaY < 0 ? deltaY*=3 : deltaY;

                if ( 0.98 < this.getAmountClipped() ) {
                    this.removeEvents();

                    $( '.peel-top, .peel-back, .peel-bottom-shadow', $target ).remove();
                }

                peel.setPeelPosition( Math.round( deltaX ), Math.round( deltaY ) );

            });
        }

        function slideOutSwitchType() {

            var $frontSide    = $( '.jet-animated-box__front', $target ),
                $backSide     = $( '.jet-animated-box__back', $target ),
                $targetWidth  = $target.width(),
                $targetHeight = $target.height(),
                axis          = ( 'left' === settings.slideOutDirection || 'right' === settings.slideOutDirection ) ? 'x' : 'y';

            $frontSide.draggable( {
                axis: axis,
                drag: function( event, ui ) {
                    var dragData = ui.position;
                    switch( settings.slideOutDirection ) {
                        case 'left':
                            if ( dragData.left >= 0 ) {
                                ui.position.left = 0;
                            }
                            break;
                        case 'right':
                            if ( dragData.left <= 0 ) {
                                ui.position.left = 0;
                            }
                            break;
                        case 'top':
                            if ( dragData.top >= 0 ) {
                                ui.position.top = 0;
                            }
                            break;
                        case 'bottom':
                            if ( dragData.top <= 0 ) {
                                ui.position.top = 0;
                            }
                            break;
                    }

                },
            } );

            backButton.on( 'focus', function() {
                $frontSide.draggable( "disable" );
                $frontSide.hide();
            } );
        }
    }

    function onAnimatedBoxSectionActivated( $scope ) {

        if ( ! window.elementor ) {
            return;
        }

        if ( ! window.JetElementsEditor ) {
            return;
        }

        if ( ! window.JetElementsEditor.activeSection ) {
            return;
        }

        var section = window.JetElementsEditor.activeSection;
        var isBackSide = -1 !== [ 'section_back_content', 'section_action_button_style' ].indexOf( section );

        if ( isBackSide ) {
            $scope.find( '.jet-animated-box' ).addClass( 'flipped' );
            $scope.find( '.jet-animated-box' ).addClass( 'flipped-stop' );
        } else {
            $scope.find( '.jet-animated-box' ).removeClass( 'flipped' );
            $scope.find( '.jet-animated-box' ).removeClass( 'flipped-stop' );
        }
    }

    function initAnimatedBoxLoopCarouselFallback() {

        if ( $( document ).data( 'jetAnimatedBoxLoopCarouselFallback' ) ) {
            return;
        }

        $( document ).data( 'jetAnimatedBoxLoopCarouselFallback', true );

        document.addEventListener( 'touchstart', handleLoopCarouselTouchStart, true );
        document.addEventListener( 'touchmove', handleLoopCarouselTouchMove, true );
        document.addEventListener( 'touchend', handleLoopCarouselTouchEnd, true );

        function handleLoopCarouselTouchStart( event ) {
            var box = getLoopCarouselAnimatedBox( event.target );

            if ( ! box || ! event.touches || ! event.touches.length ) {
                return;
            }

            $( box ).data( 'jetAnimatedBoxTouch', {
                x: event.touches[0].clientX,
                y: event.touches[0].clientY,
                scrollTop: $( window ).scrollTop(),
                moved: false
            } );
        }

        function handleLoopCarouselTouchMove( event ) {
            var box       = getLoopCarouselAnimatedBox( event.target ),
                touchData = box ? $( box ).data( 'jetAnimatedBoxTouch' ) : null,
                touch     = event.touches && event.touches.length ? event.touches[0] : null;

            if ( ! box || ! touchData || ! touch ) {
                return;
            }

            if (
                8 < Math.abs( touch.clientX - touchData.x ) ||
                8 < Math.abs( touch.clientY - touchData.y )
            ) {
                touchData.moved = true;
                $( box ).data( 'jetAnimatedBoxTouch', touchData );
            }
        }

        function handleLoopCarouselTouchEnd( event ) {
            var box       = getLoopCarouselAnimatedBox( event.target ),
                $box      = $( box ),
                settings  = $.extend( {}, { switchEventType: 'hover' }, $box.data( 'settings' ) ),
                touchData = $box.data( 'jetAnimatedBoxTouch' );

            if ( ! box || ! touchData ) {
                return;
            }

            $box.removeData( 'jetAnimatedBoxTouch' );

            if ( event.target.closest( '.jet-animated-box__button--back' ) ) {
                return;
            }

            if ( 'toggle' === settings.switchEventType && ! event.target.closest( '.jet-animated-box__toggle' ) ) {
                return;
            }

            if ( -1 === [ 'hover', 'click', 'toggle' ].indexOf( settings.switchEventType ) ) {
                return;
            }

            if ( touchData.moved || 5 < Math.abs( touchData.scrollTop - $( window ).scrollTop() ) ) {
                return;
            }

            if ( $box.hasClass( 'flipped-stop' ) ) {
                return;
            }

            if ( event.cancelable ) {
                event.preventDefault();
            }

            event.stopPropagation();

            setTimeout( function() {
                $box.toggleClass( 'flipped' );
            }, 10 );
        }

        function getLoopCarouselAnimatedBox( target ) {
            var box = target.closest ? target.closest( '.jet-animated-box' ) : null;

            if ( ! box ) {
                return null;
            }

            if (
                ! box.closest( '.elementor-widget-loop-carousel' ) &&
                ! box.closest( '[data-widget_type^="loop-carousel"]' ) &&
                ! box.closest( '.e-loop-carousel' )
            ) {
                return null;
            }

            return box;
        }
    }

    /**
	 * [jetScratchEffect description]
	 * @param  {[type]} elementId        [description]
	 * @param  {[type]} canvasId         [description]
	 * @param  {[type]} completeCallback [description]
	 * @return {[type]}                  [description]
	 */

	function jetScratchEffect( elementId, canvasId, completeCallback, fillpercent = 75, baseTopDistance ) {
		var container    = document.querySelector( elementId ),
			canvas       = document.querySelector( canvasId ),
			canvasWidth  = canvas.width,
			canvasHeight = canvas.height,
			ctx          = canvas.getContext('2d'),
			brush        = new Image(),
			isDrawing = false,
			lastPoint,
			backButton   = $( '.jet-animated-box__button--back', container );

			backButton.on( 'focus', function() {
				handlePercentage( 100 );
			} );

			function getPageTop(el) {
				var rect  = el.getBoundingClientRect(),
					docEl = document.documentElement;

				return {
					top: rect.top,
					scrollTop: docEl.scrollTop,
				};
			}

			brush.src = 'data:image/png;base64,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';

			canvas.addEventListener( 'mousedown', handleMouseDown, false );
			canvas.addEventListener( 'mousemove', JetElementsTools.debounce( 5, handleMouseMove ), false );
			canvas.addEventListener( 'mouseup', handleMouseUp, false );

			canvas.addEventListener( 'touchstart', handleMouseDown, false );
			canvas.addEventListener( 'touchmove', handleMouseMove, false );
			canvas.addEventListener( 'touchend', handleMouseUp, false );

		function distanceBetween( point1, point2 ) {
			return Math.sqrt( Math.pow( point2.x - point1.x, 2 ) + Math.pow( point2.y - point1.y, 2 ) );
		}

		function angleBetween( point1, point2 ) {
			return Math.atan2( point2.x - point1.x, point2.y - point1.y );
		}

		function getFilledInPixels( stride ) {

			if ( ! stride || stride < 1 ) {
				stride = 1;
			}

			var pixels   = ctx.getImageData(0, 0, canvasWidth, canvasHeight),
				pdata    = pixels.data,
				l        = pdata.length,				
				total    = ( l / 4 / stride ),
				count    = 0;

			for( var i = count = 0; i < l; i += (stride * 4) ) {
				if ( parseInt( pdata[i + 3] ) === 0 ) {
					count++;
				}
			}

			return Math.round( ( count / total ) * 100 );
		}

		function getMouse( e, canvas ) {
			var offsetX = 0,
				offsetY = 0,
				mx,
				my;

			mx = ( e.pageX || e.touches[0].clientX ) - offsetX;
			my = ( e.pageY || e.touches[0].clientY ) - offsetY;

			return { x: mx, y: my };
		}

		function handlePercentage( filledInPixels ) {
			filledInPixels = filledInPixels || 0;

			if ( filledInPixels > fillpercent && completeCallback ) {
				completeCallback.call( canvas );
			}
		}

		function handleMouseDown( e ) {
			isDrawing = true;
			lastPoint = getMouse( e, canvas );
		}

		function handleMouseMove( e ) {
			var currentPageTop = getPageTop(canvas),
				fixOffset      = 0;

			if ( baseTopDistance.top != currentPageTop.top ){
				fixOffset = ( currentPageTop.top + currentPageTop.scrollTop ) - baseTopDistance;
			}

			if ( ! isDrawing ) {
				return;
			}

			e.preventDefault();

			var currentPoint      = getMouse( e, canvas ),
				dist              = distanceBetween( lastPoint, currentPoint ),
				angle             = angleBetween( lastPoint, currentPoint ),
				x                 = 0,
				y                 = 0,
				userAgent         = navigator.userAgent || navigator.vendor || window.opera,
				isIos             = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream,
				isMobileDevice    = JetElementsTools.mobileAndTabletcheck(),
				yScroll           = ( isMobileDevice && !isIos ) ? window.scrollY : 0;

			for ( var i = 0; i < dist; i++ ) {
				x = lastPoint.x + ( Math.sin( angle ) * i ) - 40;
				y = lastPoint.y + ( Math.cos( angle ) * i ) - 40 + yScroll - fixOffset;
				ctx.globalCompositeOperation = 'destination-out';
				ctx.drawImage( brush, x, y, 80, 80 );
			}

			lastPoint = currentPoint;

			handlePercentage( getFilledInPixels( 32 ) );
		}

		function handleMouseUp( e ) {
			isDrawing = false;
		}
	}
    
    window.widgetAnimatedBox = widgetAnimatedBox;

})( jQuery );
