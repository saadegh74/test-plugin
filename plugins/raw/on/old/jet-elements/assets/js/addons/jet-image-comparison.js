(function ($) {

    "use strict";

    function widgetImageComparison( $scope ) {

        var $target          = $scope.find( '.jet-image-comparison__instance' ),
        instance             = null,
        imageComparisonItems = $( '.jet-image-comparison__container', $target ),
        settings             = $target.data( 'settings' ),
        elementId            = $scope.data( 'id' );

        if ( ! $target.length ) {
            return;
        } 
        
        window.juxtapose.scanPage( '.jet-juxtapose' );
        settings.draggable = false;
        settings.infinite = false;

        JetElements.initCarousel( $target, settings );
    }

    window.widgetImageComparison = widgetImageComparison;

})( jQuery );