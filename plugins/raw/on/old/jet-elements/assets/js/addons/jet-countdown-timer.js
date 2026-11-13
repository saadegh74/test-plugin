(function ($) {

    "use strict";

    function widgetCountdown( $scope ) {

        var timeInterval,
            $countdown        = $scope.find( '.jet-countdown-timer' ),
            type              = $countdown.data( 'type' ),
            endTime           = null,
            dueDate           = $countdown.data( 'due-date' ),
            startDate         = $countdown.data( 'start-date' ),
            actions           = $countdown.data( 'expire-actions' ),
            evergreenInterval = $countdown.data( 'evergreen-interval' ),
            restartInterval   = $countdown.data( 'restart-interval' ),
            elements          = {
                days: $countdown.find( '[data-value="days"]' ),
                hours: $countdown.find( '[data-value="hours"]' ),
                minutes: $countdown.find( '[data-value="minutes"]' ),
                seconds: $countdown.find( '[data-value="seconds"]' )
            };

        var initClock = function() {

            switch( type ) {
                case 'due_date':
                    endTime = new Date( dueDate * 1000 );
                    break;

                case 'evergreen':
                    if ( evergreenInterval > 0 ) {
                        endTime = getEvergreenDate();
                    }
                    break;

                case 'endless':
                    var currentTime               = new Date(),
                        currentTimeTimezoneOffset = Math.abs( currentTime.getTimezoneOffset() ),
                        startTime                 = new Date( startDate * 1000 ),
                        startTimeTimezoneOffset   = Math.abs( startTime.getTimezoneOffset() ),
                        timeZoneOffsetDiff        = startTimeTimezoneOffset - currentTimeTimezoneOffset;

                    if ( currentTime > startTime ) {
                        endTime = new Date( (startDate + restartInterval) * 1000 );
                    }

                    if ( endTime && ( currentTime > endTime ) ) {
                        endTime = endTime.setSeconds( endTime.getSeconds() + (Math.floor( (currentTime - endTime) / (restartInterval * 1000) ) + 1) * restartInterval );
                    }

                    if ( 0 != timeZoneOffsetDiff ) {
                        endTime -= timeZoneOffsetDiff * 60 * 1000;
                    }

                    break;
            }

            updateClock();
            timeInterval = setInterval( updateClock, 1000 );
        };

        var updateClock = function() {

            if ( ! endTime ) {
                return;
            }

            var timeRemaining = getTimeRemaining(
                endTime,
                {
                    days:    elements.days.length,
                    hours:   elements.hours.length,
                    minutes: elements.minutes.length
                }
            );

            $.each( timeRemaining.parts, function( timePart ) {

                var $element = elements[ timePart ];

                if ( $element.length ) {
                    $element.html( this );
                }

            } );

            if ( timeRemaining.total <= 0 ) {
                clearInterval( timeInterval );
                runActions();
            }
        };

        var splitNum = function( num ) {

            var num    = num.toString(),
                arr    = [],
                result = '';

            if ( 1 === num.length ) {
                num = 0 + num;
            }

            arr = num.match(/\d{1}/g);

            $.each( arr, function( index, val ) {
                result += '<span class="jet-countdown-timer__digit">' + val + '</span>';
            });

            return result;
        };

        var getTimeRemaining = function( endTime, visible ) {

            var timeRemaining = endTime - new Date(),
                seconds = Math.floor( ( timeRemaining / 1000 ) % 60 ),
                minutes = Math.floor( ( timeRemaining / 1000 / 60 ) % 60 ),
                hours = Math.floor( ( timeRemaining / ( 1000 * 60 * 60 ) ) % 24 ),
                days = Math.floor( timeRemaining / ( 1000 * 60 * 60 * 24 ) );

            if ( days < 0 || hours < 0 || minutes < 0 ) {
                seconds = minutes = hours = days = 0;
            }

            if ( ! visible.days ) {
                hours += days * 24;
                days = 0;
            }

            if ( ! visible.hours ) {
                minutes += hours * 60;
                hours = 0;
            }

            if ( ! visible.minutes ) {
                seconds += minutes * 60;
                minutes = 0;
            }

            return {
                total: timeRemaining,
                parts: {
                    days: splitNum( days ),
                    hours: splitNum( hours ),
                    minutes: splitNum( minutes ),
                    seconds: splitNum( seconds )
                }
            };
        };

        var runActions = function() {

            $scope.trigger( 'jetCountdownTimerExpire', $scope );

            if ( ! actions ) {
                return;
            }

            $.each( actions, function( index, action ) {
                switch ( action ) {
                    case 'redirect':
                        var redirect_url = $countdown.data( 'expire-redirect-url' );

                        if ( redirect_url ) {
                            window.location.href = redirect_url;
                        }

                        break;

                    case 'message':
                        $scope.find( '.jet-countdown-timer-message' ).show();
                        break;

                    case 'hide':
                        $countdown.hide();
                        break;

                    case 'restart':

                        endTime = new Date();
                        endTime = endTime.setSeconds( endTime.getSeconds() + restartInterval );

                        updateClock();
                        timeInterval = setInterval( updateClock, 1000 );
                        break;
                }
            } );
        };

        var getEvergreenDate = function() {
            var id = $scope.data( 'id' ),
                dueDateKey = 'jet_evergreen_countdown_due_date_' + id,
                intervalKey = 'jet_evergreen_countdown_interval_' + id,
                localDueDate = localStorage.getItem( dueDateKey ),
                localInterval = localStorage.getItem( intervalKey ),

                initEvergreenTimer = function(){
                    var dueDate = new Date(),
                        _endTime = dueDate.setSeconds( dueDate.getSeconds() + evergreenInterval );

                    localStorage.setItem( dueDateKey, _endTime );
                    localStorage.setItem( intervalKey, evergreenInterval );

                    return _endTime;
                };

            if ( null === localDueDate && null === localInterval ) {
                return initEvergreenTimer();
            }

            if ( null !== localDueDate && evergreenInterval !== parseInt( localInterval, 10 ) ) {
                return initEvergreenTimer();
            }

            if ( localDueDate > 0 && parseInt( localInterval, 10 ) === evergreenInterval ) {
                return localDueDate;
            }
        };

        initClock();
    }

    window.widgetCountdown = widgetCountdown;

})( jQuery );