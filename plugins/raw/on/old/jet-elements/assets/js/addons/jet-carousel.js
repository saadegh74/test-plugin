(function ($) {

    "use strict";

    function widgetCarousel( $scope ) {

        var $carousel = $scope.find( '.jet-carousel' ),
            fraction_nav = $carousel.find( '.jet-carousel__fraction-navigation' );

        if ( !$carousel.length ) {
            return;
        }

        var options = $carousel.data( 'slider_options' ) || {};

        if ( options.fractionNav ) {
            $carousel.find( '.elementor-slick-slider' )
                .on( 'init reInit afterChange', function ( event, slick, currentSlide ) {
                    var i = ( currentSlide || 0 ) + 1;
                    fraction_nav.html(
                        '<span class="current">' + i + '</span>' +
                        '<span class="separator">/</span>' +
                        '<span class="total">' + slick.slideCount + '</span>'
                    );
                });
        }

        JetElements.initCarousel( $carousel.find( '.elementor-slick-slider' ), options );
    }

    window.widgetCarousel = widgetCarousel;

})( jQuery );