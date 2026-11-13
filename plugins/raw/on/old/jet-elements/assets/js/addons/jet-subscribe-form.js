(function ($) {

    "use strict";

    function widgetSubscribeForm( $scope ) {

        var $target           = $scope.find( '.jet-subscribe-form' ),
        scoreId               = $scope.data( 'id' ),
        settings              = $target.data( 'settings' ),
        jetSubscribeFormAjax  = null,
        subscribeFormAjax     = 'jet_subscribe_form_ajax',
        ajaxRequestSuccess    = false,
        $subscribeForm        = $( '.jet-subscribe-form__form', $target ),
        $fields               = $( '.jet-subscribe-form__fields', $target ),
        $mailField            = $( '.jet-subscribe-form__mail-field', $target ),
        $inputData            = $mailField.data( 'instance-data' ),
        $submitButton         = $( '.jet-subscribe-form__submit', $target ),
        $subscribeFormMessage = $( '.jet-subscribe-form__message', $target ),
        timeout               = null,
        invalidMailMessage    = window.jetElements.messages.invalidMail || 'Please specify a valid email';

        $mailField.on( 'focus', function() {
            $mailField.removeClass( 'mail-invalid' );
        } );

        $( document ).keydown( function( event ) {

            if ( 13 === event.keyCode && $mailField.is( ':focus' ) ) {
                subscribeHandle();

                return false;
            }
        } );

        $submitButton.on( 'click', function() {
            subscribeHandle();

            return false;
        } );

        function subscribeHandle() {
            var inputValue     = $mailField.val(),
                sendData       = {
                    'email': inputValue,
                    'use_target_list_id': settings['use_target_list_id'] || false,
                    'target_list_id': settings['target_list_id'] || '',
                    'data': $inputData
                },
                serializeArray = $subscribeForm.serializeArray(),
                additionalFields = {};

            if ( JetElementsTools.validateEmail( inputValue ) ) {

                $.each( serializeArray, function( key, fieldData ) {

                    if ( 'email' === fieldData.name ) {
                        sendData[ fieldData.name ] = fieldData.value;
                    } else {
                        additionalFields[ fieldData.name ] = fieldData.value;
                    }
                } );

                sendData['additional'] = additionalFields;

                if ( ! ajaxRequestSuccess && jetSubscribeFormAjax ) {
                    jetSubscribeFormAjax.abort();
                }

                jetSubscribeFormAjax = $.ajax( {
                    type: 'POST',
                    url: window.jetElements.ajaxUrl,
                    data: {
                        action: subscribeFormAjax,
                        data: sendData
                    },
                    cache: false,
                    beforeSend: function() {
                        $submitButton.addClass( 'loading' );
                        ajaxRequestSuccess = false;
                    },
                    success: function( data ){
                        var successType   = data.type,
                            message       = data.message || '',
                            responceClass = 'jet-subscribe-form--response-' + successType;

                        $submitButton.removeClass( 'loading' );
                        ajaxRequestSuccess = true;

                        $target.removeClass( 'jet-subscribe-form--response-error' );
                        $target.addClass( responceClass );

                        $( 'span', $subscribeFormMessage ).html( message );
                        $subscribeFormMessage.css( { 'visibility': 'visible' } );

                        timeout = setTimeout( function() {
                            $subscribeFormMessage.css( { 'visibility': 'hidden' } );
                            $target.removeClass( responceClass );
                        }, 20000 );

                        if ( settings['redirect'] ) {
                            window.location.href = settings['redirect_url'];
                        }

                        $( window ).trigger( {
                            type: 'jet-elements/subscribe',
                            elementId: scoreId,
                            successType: successType,
                            inputData: $inputData
                        } );
                    }
                });

            } else {
                $mailField.addClass( 'mail-invalid' );

                $target.addClass( 'jet-subscribe-form--response-error' );
                $( 'span', $subscribeFormMessage ).html( invalidMailMessage );
                $subscribeFormMessage.css( { 'visibility': 'visible' } );

                timeout = setTimeout( function() {
                    $target.removeClass( 'jet-subscribe-form--response-error' );
                    $subscribeFormMessage.css( { 'visibility': 'hidden' } );
                    $mailField.removeClass( 'mail-invalid' );
                }, 20000 );
            }
        }
    }

    window.widgetSubscribeForm = widgetSubscribeForm;

})( jQuery );