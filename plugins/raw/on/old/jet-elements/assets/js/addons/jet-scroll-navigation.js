(function ( $ ) 
{

    "use strict";

    function widgetScrollNavigation( $scope ) 
    {
        var $target = $scope.find( '.jet-scroll-navigation' ),
            $settings = $target.data( 'settings' ) || {};

        if ( ! $target.length ) 
            return;

        var $instance = new JetScrollNavigation( $scope, $target, $settings );
        $instance.init();
    }

    class JetScrollNavigation 
    {

        constructor( $scope, $selector, $settings ) 
        {
            this.$scope = $scope;
            this.$instance = $selector;
            this.$itemsList = $( '.jet-scroll-navigation__item', $selector );

            this.settings = $.extend({
                speed: 500,
                offset: 0,
                sectionSwitch: false,
                sectionSwitchOnMobile: true,
                scroll_threshold: 50,
            }, $settings );

            this.sections = {};
            this.currentSection = null;
            this.isScrolling = false;
            this.scrollCheckInterval = null;
            this.scrollResetTimeout = null;
            this.isTouchDevice = JetElementsTools.mobileAndTabletcheck() || window.matchMedia( '(hover: none)' ).matches;
            this.touchStartY = 0;
            this.touchStartX = 0;
            this.touchTriggered = false;
            this.touchLockUntil = 0;
            this.$window = $( window );
        }

        init() 
        {
            this.setSectionsData();

            if ( this.currentSection )
                this.activateSection( this.currentSection );

            this.bindEvents();
            this.initObservers();
        }

        bindEvents() 
        {
            const self = this;

            this.$itemsList.on( 'click.jetScrollNavigation', function () 
            {
                if ( self.isTouchDevice ) 
                    self.clearAllTouchHoverStates( $( this ) );

                const $sectionId = $( this ).data( 'anchor' );
                self.scrollToSection( $sectionId );
            });

            this.$window.on(
                'resize.jetScrollNavigation',
                JetElementsTools.debounce( 50, () => self.setSectionsData() )
            );

            if ( this.settings.sectionSwitch ) 
                this.enableWheelControl();
        }

        setSectionsData() 
        {
            const self = this;
            this.sections = {};

            this.$itemsList.each( function () 
            {
                const $item = $( this );
                const $sectionId = $item.data( 'anchor' );
                const $invert = $item.data( 'invert' ) === 'yes';
                const $section = $( '#' + $sectionId );
                if ( !$section.length ) 
                    return;

                self.sections[ $sectionId ] = 
                {
                    selector: $section,
                    offset: Math.round( $section.offset().top ),
                    height: $section.outerHeight(),
                    invert: $invert
                };
            });

            if ( ! this.currentSection || ! this.sections[ this.currentSection ] ) 
                this.currentSection = Object.keys( this.sections )[0] || null;
        }

        initObservers() 
        {
            const self = this;
            const $sortedSections = Object.keys( this.sections ).sort( ( a, b ) => 
            {
                return self.sections[ a ].offset - self.sections[ b ].offset;
            });

            $( window ).on( 'scroll.jetScrollNavigation', () => 
            {
                if ( this.isScrolling ) 
                    return;

                self.syncCurrentSectionByScroll( $( window ).scrollTop(), $sortedSections );
            });
        }

        activateSection( $sectionId ) 
        {
            if ( ! this.sections[ $sectionId ] ) 
                return;
        
            this.currentSection = $sectionId;
        
            this.$itemsList.removeClass( 'active invert' );
        
            this.clearAllTouchHoverStates();
        
            $( '[data-anchor="' + $sectionId + '"]', this.$instance ).addClass( 'active' );
        
            if ( this.sections[ $sectionId ].invert ) 
                this.$itemsList.addClass( 'invert' );
        
            if ( ! this.settings.sectionIdVisibility ) 
            {
                if ( history.replaceState ) 
                    history.replaceState( null, null, '#' + $sectionId );
                else 
                    location.hash = $sectionId;
            }
        }

        scrollToSection( $sectionId ) 
        {
            if ( ! this.sections[ $sectionId ] || this.isScrolling ) 
                return;

            const $offset = this.sections[ $sectionId ].offset - this.settings.offset;
            let $lastScrollTop = window.scrollY || window.pageYOffset;
            let $stalledTicks = 0;

            this.clearScrollState();
            this.isScrolling = true;
            this.activateSection( $sectionId );

            window.scrollTo(
            {
                top: $offset,
                behavior: 'smooth'
            });

            this.scrollCheckInterval = setInterval( () => 
            {
                const $scrollTop = window.scrollY || window.pageYOffset;
                const $maxScroll = document.body.scrollHeight - window.innerHeight;
                const $isNearTarget = Math.abs( $scrollTop - $offset ) < 2;
                const $isAtEdge = $scrollTop >= $maxScroll || $scrollTop <= 0;
                const $isStalled = Math.abs( $scrollTop - $lastScrollTop ) < 2;

                if ( $isStalled ) 
                    $stalledTicks++;
                else 
                    $stalledTicks = 0;

                $lastScrollTop = $scrollTop;

                if ( $isNearTarget || $isAtEdge || $stalledTicks >= 8 ) 
                {
                    this.clearScrollState();
                    this.setSectionsData();
                    this.syncCurrentSectionByScroll();
                }
            }, 50 );

            this.scrollResetTimeout = setTimeout( () => 
            {
                this.clearScrollState();
                this.setSectionsData();
                this.syncCurrentSectionByScroll();
            }, 1200 + this.settings.speed );
        }

        clearTouchHoverState( $item ) 
        {
            if ( ! this.isTouchDevice || ! $item || ! $item.length ) 
                return;
        
            const el = $item.get(0);
        
            if ( document.activeElement ) 
                document.activeElement.blur();
        
            el.style.pointerEvents = 'none';
            el.style.transform = 'translateZ(0)';
        
            setTimeout(() => {
                el.style.pointerEvents = '';
                el.style.transform = '';
            }, 100);
        }

        clearAllTouchHoverStates( $preferredItem = null ) 
        {
            if ( ! this.isTouchDevice ) 
                return;

            if ( $preferredItem && $preferredItem.length ) 
                this.clearTouchHoverState( $preferredItem );

            this.$itemsList.each( ( index, item ) => 
            {
                if ( $preferredItem && $preferredItem.length && item === $preferredItem.get( 0 ) ) 
                    return;

                this.clearTouchHoverState( $( item ) );
            });
        }

        clearScrollState() 
        {
            if ( this.scrollCheckInterval ) 
            {
                clearInterval( this.scrollCheckInterval );
                this.scrollCheckInterval = null;
            }

            if ( this.scrollResetTimeout ) 
            {
                clearTimeout( this.scrollResetTimeout );
                this.scrollResetTimeout = null;
            }

            this.isScrolling = false;
        }

        syncCurrentSectionByScroll( $scrollTop = null, $sortedSections = null ) 
        {
            const $sections = $sortedSections || Object.keys( this.sections ).sort( ( a, b ) => this.sections[ a ].offset - this.sections[ b ].offset );

            if ( ! $sections.length ) 
                return;

            const $currentScrollTop = null === $scrollTop ? $( window ).scrollTop() : $scrollTop;
            const $windowHeight = $( window ).height();
            const $maxScroll = $( document ).height() - $windowHeight;
            const $probePoint = $currentScrollTop + Math.round( $windowHeight / 2 );

            if ( $currentScrollTop <= 0 ) 
            {
                this.activateSection( $sections[0] );
                this.clearAllTouchHoverStates();
                return;
            }

            for ( let i = 0; i < $sections.length; i++ ) 
            {
                const $sectionId = $sections[i];
                const $section = this.sections[ $sectionId ];
                const $sectionTop = $section.offset;
                const $sectionBottom = $section.offset + $section.height;

                if ( $probePoint >= $sectionTop && $probePoint < $sectionBottom ) 
                {
                    this.activateSection( $sectionId );
                    this.clearAllTouchHoverStates();
                    return;
                }
            }

            if ( $currentScrollTop >= $maxScroll ) 
            {
                this.activateSection( $sections[ $sections.length - 1 ] );
                this.clearAllTouchHoverStates();
            }
        }

        enableWheelControl() 
        {
            const self = this;
            const $isMobile = JetElementsTools.mobileAndTabletcheck();

            if ( !$isMobile ) 
            {
                window.addEventListener( 'wheel', function ( $event ) 
                {
                    self.onWheel( $event );
                }, { passive: false });
            }

            if ( $isMobile && this.settings.sectionSwitchOnMobile ) 
            {
                window.addEventListener( 'touchstart', function ( $e ) 
                {
                    if ( ! self.$scope.is( ':visible' ) ) 
                        return;

                    self.touchStartY = $e.changedTouches[0].clientY;
                    self.touchStartX = $e.changedTouches[0].clientX;
                    self.touchTriggered = false;
                }, { passive: true });

                window.addEventListener( 'touchmove', function ( $e ) 
                {
                    if ( ! self.$scope.is( ':visible' ) ) 
                        return;

                    if ( self.isScrolling || Date.now() < self.touchLockUntil ) 
                    {
                        $e.preventDefault();
                        return;
                    }

                    const $deltaY = $e.changedTouches[0].clientY - self.touchStartY;
                    const $deltaX = $e.changedTouches[0].clientX - self.touchStartX;

                    if ( Math.abs( $deltaY ) < self.settings.scroll_threshold || Math.abs( $deltaY ) <= Math.abs( $deltaX ) ) 
                        return;

                    $e.preventDefault();
                    
                    if ( self.touchTriggered ) 
                        return;

                    self.touchTriggered = true;
                    self.touchLockUntil = Date.now() + self.settings.speed + 200;

                    const $direction = $deltaY > 0 ? 'up' : 'down';
                    self.switchDirection( $direction );
                }, { passive: false });

                window.addEventListener( 'touchend', function ( $e ) 
                {
                    self.touchTriggered = false;
                }, { passive: true });

                window.addEventListener( 'touchcancel', function () 
                {
                    self.touchTriggered = false;
                }, { passive: true });
            }
        }

        onWheel( $event ) 
        {
            if ( ! this.$scope.is( ':visible' ) || this.isScrolling ) 
                return;

            $event.preventDefault();
            const $direction = $event.deltaY < 0 ? 'up' : 'down';
            this.switchDirection( $direction );
        }

        switchDirection( $direction ) 
        {
            if ( ! this.currentSection ) 
                return;

            const $current = $( '[data-anchor="' + this.currentSection + '"]', this.$instance );
            const $target = $direction === 'up' ? $current.prev() : $current.next();

            if ( $target.length ) 
            {
                $target.trigger( 'click.jetScrollNavigation' );
            } 
            else 
            {
                const $scrollTop = window.scrollY || window.pageYOffset;
                const $maxScroll = document.body.scrollHeight - window.innerHeight;

                if ( $direction === 'up' && $scrollTop > 0 ) 
                {
                    window.scrollTo( { top: 0, behavior: 'smooth' } );
                    const $firstSection = Object.keys( this.sections )[0];
                    this.activateSection( $firstSection );
                }

                if ( $direction === 'down' && $scrollTop < $maxScroll ) 
                {
                    window.scrollTo( { top: $maxScroll, behavior: 'smooth' } );
                    const $lastSection = Object.keys( this.sections ).pop();
                    this.activateSection( $lastSection );
                }

                this.isScrolling = false;
            }
        }
    }

    window.widgetScrollNavigation = widgetScrollNavigation;

})( jQuery );
