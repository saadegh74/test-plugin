(function ($) {

    "use strict";

    function widgetDropbar( $scope ) {

        var $dropbar       = $scope.find( '.jet-dropbar' ),
            $dropbar_inner = $dropbar.find( '.jet-dropbar__inner' ),
            $btn           = $dropbar.find( '.jet-dropbar__button' ),
            $content       = $dropbar.find( '.jet-dropbar__content' ),
            settings       = $dropbar.data( 'settings' ) || {},
            mode           = settings['mode'] || 'hover',
            hide_delay     = +settings['hide_delay'] || 0,
            activeClass    = 'jet-dropbar-open',
            eContainer     = $scope.parents( '.e-con' ),
            scrollOffset,
            timer,
            loader         = $( '.jet-elements-loader', $content ),
            signature      = settings['signature'];

        if ( 'click' === mode ) {
            $btn.on( 'click.jetDropbar', function( event ) {
                $dropbar.toggleClass( activeClass );

                if ( $dropbar.hasClass( activeClass ) && 'yes' === settings['ajax_template'] ) {
                    ajaxLoadTemplate( settings['template_id'] );
                }
            } );
        } else {
            if ( 'ontouchstart' in window || 'ontouchend' in window ) {
                $btn.on( 'touchend.jetDropbar', function( event ) {
                    if ( $( window ).scrollTop() !== scrollOffset ) {
                        return;
                    }
                    $dropbar.toggleClass( activeClass );
                    
                    if ( $dropbar.hasClass( activeClass ) && 'yes' === settings['ajax_template'] ) {
                        ajaxLoadTemplate( settings['template_id'] );
                    }
                } );
            } else {
                $dropbar_inner.on( 'mouseenter.jetDropbar', function( event ) {
                    clearTimeout( timer );

                    $( '.jet-dropbar' ).each( function() {
                        $( this ).removeClass( activeClass );

                        $( this ).parents( '.e-con' ).css( 'z-index', '' );
                    } );

                    eContainer.css( 'z-index', '' );

                    if ( eContainer.css( 'z-index' ) === 'auto' ) {
                        eContainer.css( 'z-index', 1 );
                    }

                    $dropbar.addClass( activeClass );

                    if ( $dropbar.hasClass( activeClass ) && 'yes' === settings['ajax_template'] ) {
                        ajaxLoadTemplate( settings['template_id'] );
                    }
        
                } );

                $dropbar_inner.on( 'mouseleave.jetDropbar', function( event ) {
                    timer = setTimeout( function() {
                        $dropbar.removeClass( activeClass );
                    }, hide_delay );
                } );
            }
        }

        $( document ).on( 'touchstart.jetDropbar', function( event ) {
            scrollOffset = $( window ).scrollTop();
        } );

        $( document ).on( 'click.jetDropbar touchend.jetDropbar', function( event ) {

            if ( 'touchend' === event.type && $( window ).scrollTop() !== scrollOffset ) {
                return;
            }

            if ( $( event.target ).closest( $btn ).length || $( event.target ).closest( $content ).length ) {
                return;
            }

            if ( ! $dropbar.hasClass( activeClass ) ) {
                return;
            }

            $dropbar.removeClass( activeClass );
        } );

        function ajaxLoadTemplate( templateId ) {

            if ( $content.data( 'loaded' ) || false === templateId ) {
                return false;
            }

            $content.data( 'loaded', true );

            if ( ! templateId ) {
                return;
            }

            $.ajax({
                type: 'GET',
                url: window.jetElements.templateApiUrl,
                dataType: 'json',
                data: {
                    'id': templateId,
                    'dev': window.jetElements.devMode,
                    'signature' : signature
                },
                success: function ( response, textStatus, jqXHR ) {

                    var templateContent     = response['template_content'],
                        templateScripts     = response['template_scripts'],
                        templateStyles      = response['template_styles'];

                    for ( var scriptHandler in templateScripts ) {
                        JetElements.addedAssetsPromises.push( JetElements.loadScriptAsync( scriptHandler, templateScripts[ scriptHandler ] ) );
                    }

                    for ( var styleHandler in templateStyles ) {
                        JetElements.addedAssetsPromises.push( JetElements.loadStyle( styleHandler, templateStyles[ styleHandler ] ) );
                    }

                    Promise.all( JetElements.addedAssetsPromises ).then( function( value ) {
                        loader.remove();
                        $content.append( templateContent );
                        JetElements.initElementsHandlers( $content );

                    }, function( reason ) {
                        console.log( 'Script Loaded Error' );
                    });

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error( 'Script Loaded Error:', textStatus, errorThrown );
                }
            });
            
        }
    }

    window.widgetDropbar = widgetDropbar;

})( jQuery );