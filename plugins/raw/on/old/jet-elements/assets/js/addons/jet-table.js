(function ($) {

    "use strict";

    function widgetTable( $scope ) {

        var $target = $scope.find( '.jet-table' ),
            options = {
                cssHeader: 'jet-table-header-sort',
                cssAsc: 'jet-table-header-sort--up',
                cssDesc: 'jet-table-header-sort--down',
                initWidgets: false,
                textExtraction: function( node ) {
                    var sortValue = $( node ).attr( 'data-sort-value' );

                    return undefined !== sortValue ? sortValue : $( node ).text();
                }
            };

        if ( ! $target.length ) {
            return;
        }

        if ( $target.hasClass( 'jet-table--sorting' ) ) {
            $target.tablesorter( options );
        }

        $( '.jet-table__body-row', $target ).each( function() {
            var _this         = $( this ),
                itemsCounter  = 0,
                emptyContents = 0;

            $( '.jet-table__cell', _this ).each( function() {
                var image      = $( 'img', $( this ) ),
                    svg        = $( 'svg', $( this ) ),
                    icon       = $( 'i', $( this ) ),
                    itemImages = 0;

                if ( 0 === svg.length && 0 === icon.length ) {
                    image.each( function() {
                        if ( '' != $( this ).attr( 'src' ) ) {
                            itemImages++;
                        }
                    } )

                    if ( 0 === $( this ).text().length && 0 === itemImages ) {
                        emptyContents++;
                    }
                }

                itemsCounter++;
            } )

            if( emptyContents === itemsCounter ) {
                _this.remove();
            }
        } )
    }

    window.widgetTable = widgetTable;

})( jQuery );
