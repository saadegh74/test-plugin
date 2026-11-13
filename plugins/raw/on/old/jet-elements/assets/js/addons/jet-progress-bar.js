(function ($) {

    "use strict";

    function widgetProgressBar( $scope ) {

        var $target  = $scope.find( '.jet-progress-bar' ),
        percent      = $target.data( 'percent' ),
        type         = $target.data( 'type' ),
        deltaPercent = percent * 0.01;

        JetElements.observer( $target, function( direction ) {
            var $this        = $( this ),
                animeObject  = { charged: 0 },
                $statusBar   = $( '.jet-progress-bar__status-bar', $this ),
                $percent     = $( '.jet-progress-bar__percent-value', $this ),
                currentValue = $target.data( 'current-value' ),
                maxValue     = $target.data( 'max-value' ),
                animeProgress,
                animePercent;

            if ( currentValue && maxValue ) {
                if ( ( currentValue > maxValue ) ) {
                    percent = 100;						
                    animeObject = { Counter: 0 };
                } else {
                    animeObject  = { Counter: 0 }
                }
            }

            if ( 'type-7' == type ) {
                $statusBar.css( {
                    'height': percent + '%'
                } );
            } else {
                $statusBar.css( {
                    'width': percent + '%'
                } );
            }

            animePercent = anime( {
                targets: animeObject,
                charged: percent,
                round: 1,
                duration: 1000,
                easing: 'easeInOutQuad',
                begin: function(){
                    if ( currentValue && maxValue ) {
                        $( { Counter: 0 } ).animate( { Counter: currentValue }, {
                            duration: 1000,
                            easing: 'swing',
                            step: function ( now ) { 
                                $percent.text( Math.round( now ) + '/' + maxValue ); 
                            }
                        } );
                    }
                },
                update: function() {
                    $percent.html( animeObject.charged );
                },
            } );

        }, JetElements.prepareWaypointOptions( $scope ) );
    }

    window.widgetProgressBar = widgetProgressBar;

})( jQuery );