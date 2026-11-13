(function ($) {

    "use strict";

    function widgetTestimonials( $scope ) {

        var $target        = $scope.find( '.jet-testimonials__instance' ),
        $imagesTagList = $( '.jet-testimonials__figure', $target ),
        targetContent  = $( '.jet-testimonials__content', $target ),
        instance       = null,
        settings       = $target.data( 'settings' ),
        ratingSettings = $target.data( 'rating-settings' );

        if ( ! $target.length ) {
            return;
        }

        targetContent.each( function() {
            var ratingList = $( '.jet-testimonials__rating', this );

            if ( ratingList ) {
                var rating = ratingList.data('rating');

                ratingList.each( function() {

                    $( 'i', this ).each( function( index ) {
                        if ( index <= rating - 1 ) {
                            var itemClass = $( this ).data( 'active-star' );
                            $( this ).addClass( itemClass );
                        } else {
                            var itemClass = $( this ).data( 'star' );
                            $( this ).addClass( itemClass );
                        }
                    } )
                } )
            }
        } )

        settings.adaptiveHeight = settings['adaptiveHeight'];

        settings['slide'] = '.jet-testimonials__item';

        JetElements.initCarousel( $target, settings );
    }

    window.widgetTestimonials = widgetTestimonials;

})( jQuery );