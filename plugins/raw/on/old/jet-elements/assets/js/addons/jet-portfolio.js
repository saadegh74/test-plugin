(function ($) {

    "use strict";

    function widgetPortfolio( $scope ) {

        var $target   = $scope.find( '.jet-portfolio' ),
            instance  = null,
            eSettings = JetElementsTools.getElementorElementSettings( $scope ),
            settings  = {
            id: $scope.data( 'id' )
        };

        if ( ! $target.length ) {
            return;
        }

        settings = $.extend( {}, settings, $target.data( 'settings' ), eSettings );
        instance = new jetPortfolio( $target, settings );
        instance.init();
    }

    /**
	 * Jet Portfolio Class
	 *
	 * @return {void}
	 */
    class jetPortfolio {

        constructor( $selector, settings ) {
    
            var self            = this,
                $instance       = $selector,
                $instanceList   = $( '.jet-portfolio__list', $instance ),
                $itemsList      = $( '.jet-portfolio__item', $instance ),
                $filterList     = $( '.jet-portfolio__filter-item', $instance ),
                $moreWrapper    = $( '.jet-portfolio__view-more', $instance ),
                $moreButton     = $( '.jet-portfolio__view-more-button', $instance ),
                isViewMore      = $moreButton[ 0 ],
                itemsData       = {},
                filterData      = {},
                currentFilter   = 'all',
                activeSlug      = [],
                isRTL           = JetElementsTools.isRTL(),
                editMode        = Boolean( elementorFrontend.isEditMode() ),
                defaultSettings = {
                    layoutType: 'masonry',
                    columns: 3,
                    perPage: 6
                },
                masonryOptions  = {
                    itemSelector: '.jet-portfolio__item',
                    percentPosition: true,
                    isOriginLeft: true === isRTL ? false : true
                },
                settings        = $.extend( defaultSettings, settings ),
                $masonryInstance,
                page            = 1;
    
            /**
             * Init
             */
            self.init = function () {
    
                self.layoutBuild();
    
                if ( editMode && $masonryInstance.get( 0 ) ) {
    
                    $( window ).on(
                        'resize',
                        JetElementsTools.debounce(
                            50,
                            function () {
                                $masonryInstance.masonry( 'layout' );
                            }
                        )
                    );
                }
            };
    
            /**
             * Layout build
             */
            self.layoutBuild = function () {
    
                self.generateData();
    
                $filterList.data( 'showItems', isViewMore ? settings.perPage : 'all' );
    
                if ( 'justify' === settings.layoutType ) {
                    masonryOptions.columnWidth = '.grid-sizer';
                }
    
                if ( 'masonry' === settings.layoutType || 'justify' === settings.layoutType ) {
                    $masonryInstance = $instanceList.masonry( masonryOptions );
                }
    
                if ( $.isFunction( $.fn.imagesLoaded ) ) {
    
                    $( '.jet-portfolio__image', $itemsList )
                        .imagesLoaded()
                        .progress( function ( instance, image ) {
    
                            var $image     = $( image.img ),
                                $parentItem = $image.closest( '.jet-portfolio__item' ),
                                $loader     = $( '.jet-portfolio__image-loader', $parentItem );
    
                            $loader.remove();
                            $parentItem.addClass( 'item-loaded' );
    
                            if ( $masonryInstance ) {
                                $masonryInstance.masonry( 'layout' );
                            }
                        } );
    
                } else {
    
                    var $loader = $( '.jet-portfolio__image-loader', $itemsList );
    
                    $itemsList.addClass( 'item-loaded' );
                    $loader.remove();
                }
    
                $filterList.on( 'click.jetPortfolio', self.filterHandler );
                $moreButton.on( 'click.jetPortfolio', self.moreButtonHandler );
    
                self.render();
                self.checkMoreButton();
            };
    
            self.generateData = function () {
    
                if ( $filterList[ 0 ] ) {
    
                    $filterList.each( function () {
    
                        var $this = $( this ),
                            slug  = $this.data( 'slug' );
    
                        filterData[ slug ] = ( 'all' === slug );
                    } );
    
                } else {
                    filterData.all = true;
                }
    
                $itemsList.each( function ( index ) {
    
                    var $this = $( this ),
                        slug  = $this.data( 'slug' );
    
                    itemsData[ index ] = {
                        selector: $this,
                        slug: slug,
                        visible: $this.hasClass( 'visible-status' ),
                        more: $this.hasClass( 'hidden-status' ),
                        lightboxEnabled:
                            'yes' === $this
                                .find( '.jet-portfolio__link' )
                                .data( 'elementor-open-lightbox' )
                    };
                } );
            };
    
            self.filterHandler = function ( event ) {
    
                event.preventDefault();
    
                var $this     = $( this ),
                    counter   = 1,
                    slug      = $this.data( 'slug' ),
                    showItems = $this.data( 'showItems' );
    
                $filterList.removeClass( 'active' );
                $this.addClass( 'active' );
    
                for ( var slugName in filterData ) {
    
                    filterData[ slugName ] = ( slugName === slug );
    
                    if ( slugName === slug ) {
                        currentFilter = slugName;
                    }
                }
    
                $.each( itemsData, function ( index, obj ) {
    
                    var visible = false;
    
                    if ( 'all' === showItems ) {
    
                        if ( self.isItemVisible( obj.slug ) && ! obj.more ) {
                            visible = true;
                        }
    
                    } else if ( self.isItemVisible( obj.slug ) ) {
    
                        if ( counter <= showItems ) {
                            visible = true;
                            obj.more = false;
                        } else {
                            obj.more = true;
                        }
    
                        counter++;
                    }
    
                    obj.visible = visible;
                } );
    
                self.render();
                self.checkMoreButton();
            };
    
            self.moreButtonHandler = function () {
    
                var counter      = 1,
                    activeFilter = $( '.jet-portfolio__filter-item.active', $instance ),
                    showItems;
    
                $.each( itemsData, function ( index, obj ) {
    
                    if ( self.isItemVisible( obj.slug ) && obj.more && counter <= settings.perPage ) {
                        obj.more = false;
                        obj.visible = true;
                        counter++;
                    }
                } );
    
                if ( activeFilter[ 0 ] ) {
                    showItems = activeFilter.data( 'showItems' );
                    activeFilter.data( 'showItems', showItems + counter - 1 );
                }
    
                self.render();
                self.checkMoreButton();
            };
    
            self.checkMoreButton = function () {
    
                var check = false;
    
                $.each( itemsData, function ( index, obj ) {
    
                    if ( self.isItemVisible( obj.slug ) && obj.more ) {
                        check = true;
                    }
                } );
    
                $moreWrapper.toggleClass( 'hidden-status', ! check );
            };
    
            self.isItemVisible = function ( slugs ) {
    
                var slugList = JetElementsTools.getObjectValues( slugs );
    
                for ( var slug in filterData ) {
                    if ( filterData[ slug ] && -1 !== slugList.indexOf( slug ) ) {
                        return true;
                    }
                }
    
                return false;
            };
    
            self.render = function () {
    
                $itemsList.removeClass( 'visible-status hidden-status' );
    
                $.each( itemsData, function ( index, itemData ) {
    
                    var selector   = $( '.jet-portfolio__inner', itemData.selector ),
                        $itemLink  = $( '.jet-portfolio__link', itemData.selector ),
                        slideshowID = settings.id + '-' + currentFilter;
    
                    if ( itemData.visible ) {
    
                        itemData.selector.addClass( 'visible-status' );
    
                        if ( itemData.lightboxEnabled ) {
                            $itemLink[ 0 ].setAttribute(
                                'data-elementor-lightbox-slideshow',
                                slideshowID
                            );
                        }
    
                        anime( {
                            targets: selector[ 0 ],
                            opacity: { value: 1, duration: 400 },
                            scale: { value: 1, duration: 500, easing: 'easeOutExpo' },
                            delay: 50,
                            elasticity: false
                        } );
    
                    } else {
    
                        itemData.selector.addClass( 'hidden-status' );
                        $itemLink[ 0 ].removeAttribute(
                            'data-elementor-lightbox-slideshow'
                        );
    
                        anime( {
                            targets: selector[ 0 ],
                            opacity: 0,
                            scale: 0,
                            duration: 500,
                            elasticity: false
                        } );
                    }
                } );
    
                if ( $masonryInstance ) {
                    $masonryInstance.masonry( 'layout' );
                }
            };
        }
    }
    
    window.widgetPortfolio = widgetPortfolio;

})( jQuery );