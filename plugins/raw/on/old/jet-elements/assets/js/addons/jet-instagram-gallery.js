(function ($) {

    "use strict";

    function widgetInstagramGallery( $scope ) {

        var $target         = $scope.find( '.jet-instagram-gallery__instance' ),
            $loadMoreButton = $scope.find( '.jet-instagram-gallery__load-more' ),
            defaultSettings = {},
            settings        = {},
            currentVisible  = 0,
            allItems        = [];

        if ( ! $target.length ) {
            return;
        }

        settings = $target.data( 'settings' );

        /*
        * Default Settings
        */
        defaultSettings = {
            layoutType: 'masonry',
        };

        /**
         * Checking options, settings and options merging
         */
        settings = $.extend( defaultSettings, settings );

        function getPostsCountByDevice() {
            var currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
                postsCount        = settings.postsCounter || 0;

            if ( 'mobile' === currentDeviceMode && settings.postsCounterMobile ) {
                postsCount = settings.postsCounterMobile;
            } else if ( 'tablet' === currentDeviceMode && settings.postsCounterTablet ) {
                postsCount = settings.postsCounterTablet;
            }

            return parseInt( postsCount, 10 ) || 0;
        }

        function getItems() {
            return $target.find( '.jet-instagram-gallery__item' );
        }

        function updateLoadMoreButton( visiblePostsCount ) {
            if ( ! settings.enableLoadMore || ! $loadMoreButton.length ) {
                return;
            }

            $loadMoreButton.toggle( visiblePostsCount < allItems.length );
        }

        function appendMasonryItems( count ) {
            var itemsToAppend = [],
                i;

            for ( i = 0; i < count && currentVisible < allItems.length; i++ ) {
                itemsToAppend.push( allItems[ currentVisible ] );
                currentVisible++;
            }

            if ( itemsToAppend.length ) {
                salvattore.appendElements( $target.get( 0 ), itemsToAppend );
            }

            updateLoadMoreButton( currentVisible );
        }

        function updateVisiblePosts() {
            var initialVisiblePosts = getPostsCountByDevice(),
                visiblePostsCount   = settings.enableLoadMore ? Math.max( currentVisible, initialVisiblePosts ) : initialVisiblePosts,
                $items              = getItems();

            visiblePostsCount = Math.min( visiblePostsCount, $items.length );
            currentVisible    = visiblePostsCount;

            $items.each( function( index ) {
                $( this ).toggle( index < visiblePostsCount );
            } );

            updateLoadMoreButton( visiblePostsCount );
        }

        if ( 'masonry' === settings.layoutType ) {
            salvattore.init();
            allItems = getItems().toArray();
            getItems().detach();
            currentVisible = 0;
            appendMasonryItems( getPostsCountByDevice() );

            $( window ).on( 'resize orientationchange', function() {
                salvattore.rescanMediaQueries();
            } );
        } else {
            allItems = getItems().toArray();
            currentVisible = getPostsCountByDevice();
            updateVisiblePosts();
            $( window ).on( 'resize orientationchange', updateVisiblePosts );
        }

        if ( settings.enableLoadMore && $loadMoreButton.length ) {
            $loadMoreButton.on( 'click', function() {
                var loadCount = parseInt( settings.loadMoreCount, 10 ) || 1;

                if ( 'masonry' === settings.layoutType ) {
                    appendMasonryItems( loadCount );
                } else {
                    currentVisible += loadCount;
                    updateVisiblePosts();
                }
            } );
        }
    }

    window.widgetInstagramGallery = widgetInstagramGallery;

})( jQuery );
