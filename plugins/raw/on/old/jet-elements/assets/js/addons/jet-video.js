(function ($) {

    "use strict";

    function widgetVideo( $scope ) {

        var $video = $scope.find( '.jet-video' ),
            $iframe = $scope.find( '.jet-video-iframe' ),
            $videoPlaer = $scope.find( '.jet-video-player' ),
            $mejsPlaer = $scope.find( '.jet-video-mejs-player' ),
            mejsPlaerControls = $mejsPlaer.data( 'controls' ) || ['playpause', 'current', 'progress', 'duration', 'volume', 'fullscreen'],
            $overlay = $scope.find( '.jet-video__overlay' ),
            playButton = $scope.find( '.jet-video__play-button' ),
            hasOverlay = $overlay.length > 0,
            settings = $video.data( 'settings' ) || {},
            lightbox = settings.lightbox || false,
            autoplay = settings.autoplay || false;

        if ( $overlay[0] ) {
            playButton.keypress( function( e ) {
                if ( e.which == 13 ) {
                    $overlay.click();
                    return false;
                }
            } );

            $overlay.on( 'click.jetVideo', function( event ) {

                if ( $videoPlaer[0] ) {
                    $videoPlaer[0].play();

                    $overlay.remove();
                    hasOverlay = false;

                    return;
                }

                if ( $iframe[0] ) {
                    iframeStartPlay();
                }
            } );
        }

        if ( autoplay && $iframe[0] && $overlay[0] ) {
            iframeStartPlay();
        }

        function iframeStartPlay() {
            var lazyLoad = $iframe.data( 'lazy-load' );

            if ( lazyLoad ) {
                $iframe.attr( 'src', lazyLoad );
            }

            if ( ! autoplay ) {
                $iframe[0].src = $iframe[0].src.replace( '&autoplay=0', '&autoplay=1' );
            }

            $overlay.remove();
            hasOverlay = false;
        }

        if ( $videoPlaer[0] ) {
            $videoPlaer.on( 'play.jetVideo', function( event ) {
                if ( hasOverlay ) {
                    $overlay.remove();
                    hasOverlay = false;
                }
            } );

            if ( autoplay ) {
                $overlay.remove();
            }
        }

        if ( $mejsPlaer[0] ) {
            $mejsPlaer.mediaelementplayer( {
                videoVolume: 'horizontal',
                hideVolumeOnTouchDevices: false,
                enableProgressTooltip: false,
                features: mejsPlaerControls,
                success: function( media ) {
                    media.addEventListener( 'timeupdate', function( event ) {
                        var $currentTime = $scope.find( '.mejs-time-current' ),
                            inlineStyle  = $currentTime.attr( 'style' );

                        if ( inlineStyle ) {
                            var scaleX = inlineStyle.match(/scaleX\([0-9.]*\)/gi)[0].replace( 'scaleX(', '' ).replace( ')', '' );

                            if ( scaleX ) {
                                $currentTime.css( 'width', scaleX * 100 + '%' );
                            }
                        }
                    }, false );
                }
            } );
        }
    }

    window.widgetVideo = widgetVideo;

})( jQuery );