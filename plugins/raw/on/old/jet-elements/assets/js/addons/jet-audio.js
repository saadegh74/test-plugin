(function ($) {

    "use strict";

    function widgetAudio( $scope ) {

        initAudioPlayer( $scope );
    }

    function initAudioPlayer( $scope ) {
        var $wrapper    = $scope.find( '.jet-audio' ),
            $player     = $scope.find( '.jet-audio-player' ),
            settings    = $wrapper.data( 'audio-settings' ),
            unmuted     = 0,
            hasVolume   = false,
            startVolume;

        if ( ! $player[0] ) {
            return;
        }

        startVolume = settings['startVolume'] || 0.8;

        settings['controls'].map( function( control ) {
            if ( 'volume' === control ) {
                hasVolume = true;
            }
        } );

        $player.each( function() {

            if ( ! $( this ).hasClass( 'mejs-container' ) ) {

                $( this ).mediaelementplayer({
                    features: settings['controls'] || ['playpause', 'current', 'progress', 'duration', 'volume'],
                    audioVolume: settings['audioVolume'] || 'horizontal',
                    startVolume: startVolume,
                    hideVolumeOnTouchDevices: settings['hideVolumeOnTouchDevices'],
                    enableProgressTooltip: false,
                    success: function( media ) {

                        var muteBtn = $scope.find( '.mejs-button button' );

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
    
                        if ( hasVolume && 'yes' === settings['hasVolumeBar'] && !settings['hideVolumeOnTouchDevices']) {
                            media.setVolume( startVolume );
                            media.addEventListener( 'volumechange', function() {
                                var volumeBar          = 'horizontal' === settings['audioVolume'] ? $scope.find( '.mejs-horizontal-volume-current' ) : $scope.find( '.mejs-volume-current' ),
                                    volumeValue        = 'horizontal' === settings['audioVolume'] ? parseInt( volumeBar[0].style.width, 10 ) / 100 : parseInt( volumeBar[0].style.height, 10 ) / 100,
                                    volumeBarTotal     = 'horizontal' === settings['audioVolume'] ? $scope.find( '.mejs-horizontal-volume-total' ) : $scope.find( '.mejs-volume-slider .mejs-volume-total' ),
                                    playBrn            = $scope.find( '.mejs-playpause-button' ),
                                    volumeCurrentValue = '';
    
                                volumeBarTotal.on( 'click', function() {
                                    if ( 'horizontal' === settings['audioVolume'] ) {
                                        volumeCurrentValue = parseInt( $scope.find( '.mejs-horizontal-volume-total .mejs-horizontal-volume-current' )[0].style.width, 10 ) / 100;
                                    } else {
                                        volumeCurrentValue = parseInt( $scope.find( '.mejs-volume-slider .mejs-volume-total .mejs-volume-current' )[0].style.height, 10 ) / 100;
                                    }
                                } )
    
                                playBrn.on( 'click', function() {
                                    if ( '' !== volumeCurrentValue ) {
                                        media.setVolume( volumeCurrentValue );
                                    }
                                } )
    
                                muteBtn.on( 'click', function() {
                                    if ( ! media.muted ) {
                                        if ( 'yes' === settings['muted'] && 0 === unmuted && 0 === volumeValue ) {
                                            media.setVolume( startVolume );
                                            unmuted = 1;
                                        }
                                    }
                                } )
                            }, false );
                        } else if ( hasVolume && !settings['hideVolumeOnTouchDevices'] ) {
                            muteBtn.on( 'click', function() {
                                media.setVolume( startVolume );
                            } )
                        }
                    }
                } );

                $( this ).attr( 'preload', 'metadata' );
            }
        });
    }

    window.widgetAudio = widgetAudio;

})( jQuery );