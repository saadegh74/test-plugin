(function ($) {

    "use strict";

    function widgetPricingTable( $scope ) {

        var $target         = $scope.find( '.pricing-table' ),
            $tooltips       = $( '.pricing-feature .pricing-feature__inner[data-tippy-content]', $target ),
            settings        = $target.data( 'tooltips-settings' ),
            $fold_target    = $scope.find( '.pricing-table__fold-mask' ),
            $fold_button    = $scope.find( '.pricing-table__fold-button' ),
            $fold_mask      = $fold_target,
            $fold_content   = $( '.pricing-table__features', $fold_target ),
            fold_settings   = $fold_target.data( 'fold-settings' ) || {},
            fold_enabled    = fold_settings['fold_enabled'] || false,
            fold_maskHeight = 0,
            contentHeight   = 0,
            unfoldDuration  = fold_settings['unfoldDuration'],
            foldDuration    = fold_settings['unfoldDuration'],
            unfoldEasing    = fold_settings['unfoldEasing'],
            foldEasing      = fold_settings['foldEasing'];

        if ( $tooltips[0] ) {
            $tooltips.each( function() {
                var $this        = $( this ),
                    itemSelector = $this[0];

                if ( itemSelector._tippy ) {
                    itemSelector._tippy.destroy();
                }

                tippy( [ itemSelector ], {
                    arrow: settings['tooltipArrow'] ? true : false,
                    duration: [ settings['tooltipShowDuration']['size'], settings['tooltipHideDuration']['size'] ],
                    delay: settings['tooltipDelay']['size'],
                    placement: settings['tooltipPlacement'],
                    trigger: settings['tooltipTrigger'],
                    animation: settings['tooltipAnimation'],
                    appendTo: itemSelector,
                    offset: [ 0, settings['tooltipDistance']['size'] ],
                    allowHTML: true,
                    popperOptions: {
                        strategy: 'fixed',
                    },
                } );

            } );
        }

        function maskHeight(){
            $scope.find( '.pricing-table__fold-mask .fold_visible' ).each( function() {
                fold_maskHeight = fold_maskHeight + $( this ).outerHeight( true );
            } );
        }

        function fold_content_height(){
            contentHeight = 0;
            $scope.find( '.pricing-table__fold-mask .pricing-feature' ).each( function() {
                contentHeight = contentHeight + $( this ).outerHeight( true );
            } )
        }

        if ( fold_enabled ) {

            maskHeight();
            fold_content_height();

            if ( !$fold_target.hasClass( 'pricing-table-unfold-state' ) ) {
                $fold_mask.css( {
                    'height': fold_maskHeight
                } );
            }

            $scope.find( '.pricing-table__fold-mask' ).css('max-height', 'none');

            $fold_button.keypress( function( e ) {
                if ( e.which == 13 ) {
                    $fold_button.click();
                    return false;
                }
            } );

            $fold_button.on( 'click.jetPricingTable', function() {
                var $this                      = $( this ),
                    $buttonText                = $( '.pricing-table__fold-button-text', $this ),
                    $buttonIcon                = $( '.pricing-table__fold-button-icon', $this ),
                    unfoldText                 = $this.data( 'unfold-text' ),
                    unfoldTextAccessibility    = $this.data( 'unfold-text-accessibility' ),
                    foldText                   = $this.data( 'fold-text' ),
                    foldTextAccessibility      = $this.data( 'fold-text-accessibility' ),
                    unfoldIcon                 = $this.data( 'unfold-icon' ),
                    foldIcon                   = $this.data( 'fold-icon' );

                if ( ! $fold_target.hasClass( 'pricing-table-unfold-state' ) ) {
                    $fold_target.addClass( 'pricing-table-unfold-state' );

                    fold_content_height();

                    $buttonIcon.html( foldIcon );
                    // Label HTML is sanitized in PHP and transported without decoding its entities.
                    $buttonText.html( foldText );
                    $this.attr( 'aria-label', foldTextAccessibility );

                    anime( {
                        targets:  $fold_mask[0],
                        height:   contentHeight,
                        duration: unfoldDuration['size'] || unfoldDuration,
                        easing:   unfoldEasing
                    } );
                } else {
                    $fold_target.removeClass( 'pricing-table-unfold-state' );

                    $buttonIcon.html( unfoldIcon );
                    $buttonText.html( unfoldText );
                    $this.attr( 'aria-label', unfoldTextAccessibility );

                    anime( {
                        targets:  $fold_mask[0],
                        height:   fold_maskHeight,
                        duration: foldDuration['size'] || foldDuration,
                        easing:   foldEasing
                    } );
                }
            } );
        }
    }

    window.widgetPricingTable = widgetPricingTable;

})( jQuery );
