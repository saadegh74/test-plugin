(function ($) {

    "use strict";

    function widgetPosts( $scope ) {

        var $target  = $scope.find( '.jet-carousel' ),
        settings = $target.data( 'slider_options' );

        if ( ! $target.length ) {
            return;
        }

        settings['slide'] = '.jet-posts__item';

        JetElements.initCarousel( $target.find( '.jet-posts' ), settings );
    }

    window.widgetPosts = widgetPosts;

})( jQuery );