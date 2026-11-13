(function ($) {

    "use strict";

    function widgetMap( $scope ) {

        var $container    = $scope.find( '.jet-map' ),
            pinsOpenLimit = 0,
            map,
            init          = {},
            pins          = [],
            infowindows   = [],
            pinsAutoClose = false,
            provider;

        if ( ! $container.length ) {
            return;
        }

        init          = $container.data( 'init' ) || {};
        pins          = $container.data( 'pins' ) || [];
        provider      = $container.data( 'provider' ) || init.provider || 'google';
        pinsAutoClose = !! init.pinsAutoClose;

        if ( 'leaflet' === provider ) {
            initLeafletMap( $container, init, pins );
            return;
        }

        if ( 'mapbox' === provider ) {
            initMapboxMap( $container, init, pins );
            return;
        }

        if ( ! window.google ) {
            return;
        }

        if ( true === pinsAutoClose ) {
            pinsOpenLimit = 1;
        }

        if ( typeof console !== 'undefined' ) {
            const originalWarn = console.warn;
            console.warn = function ( message ) {
                if ( message && message.includes( 'google.maps.Marker is deprecated' )) {
                    return;
                }
                originalWarn.apply( console, arguments ); 
            };
        }

        map  = new google.maps.Map( $container[0], init );

        if ( pins ) {
            $.each( pins, function( index, pin ) {

                var marker,
                    infowindow,
                    pinData = {
                        position: pin.position,
                        map: map,
                        title: pin.address,
                    };

                if ( pin.image ) {

                    if ( undefined !== pin.image_width && undefined !== pin.image_height ) {
                        var icon = {
                            url:        pin.image,
                            scaledSize: new google.maps.Size( pin.image_width, pin.image_height ),
                            origin:     new google.maps.Point( 0, 0 ),
                            anchor:     new google.maps.Point( pin.image_width/2, pin.image_height/2 )
                        }

                        pinData.icon = icon;
                    } else {
                        pinData.icon = pin.image;
                    }
                }

                marker = new google.maps.Marker( pinData );

                if ( '' !== pin.desc || undefined !== pin.link_title ) {

                    if ( undefined !== pin.link_title && pin.link ) {
                        pin.desc += buildMapPinLink( pin );
                    }

                    infowindow = new google.maps.InfoWindow({
                        content: pin.desc,
                        disableAutoPan: true
                    });

                    infowindows[index] = infowindow;
                }

                marker.addListener( 'click', function() {
                    infowindow.setOptions({ disableAutoPan: false });

                    if ( true === pinsAutoClose ) {
                        $.each( infowindows, function( index, item ) {
                            item.close();
                        } );
                    }

                    infowindow.open( map, marker );
                });

                if ( 1 === pinsOpenLimit ) {
                    if ( 'visible' === pin.state && '' !== pin.desc ) {
                        infowindow.open( map, marker );
                        pinsOpenLimit++;
                    }
                } else if ( 0 === pinsOpenLimit ) {
                    if ( 'visible' === pin.state && '' !== pin.desc ) {
                        infowindow.open( map, marker );
                    }
                }

            });
        }
    }

    function initLeafletMap( $container, init, pins ) {

        if ( 'undefined' === typeof window.L ) {
            return;
        }

        var mapOptions = {
            zoom: init.zoom || 11,
            scrollWheelZoom: init.scrollwheel,
            zoomControl: init.zoomControl,
        };

        if ( init.center ) {
            mapOptions.center = [ init.center.lat, init.center.lng ];
        } else {
            mapOptions.center = [ 0, 0 ];
        }

        if ( false === init.draggable ) {
            mapOptions.dragging = false;
        }

        var map = window.L.map( $container[0], mapOptions );

        var tileSettings = window.JetElementsLeafletTiles || {};
        var tileURL = tileSettings.url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        var tileOptions = tileSettings.options || {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        };

        window.L.tileLayer( tileURL, tileOptions ).addTo( map );

        if ( false === init.scrollwheel ) {
            map.scrollWheelZoom.disable();
        }

        if ( false === init.zoomControl && map.zoomControl ) {
            map.zoomControl.remove();
        }

        if ( false === init.draggable ) {
            map.dragging.disable();
        }

        var openedPopup = null,
            pinsOpenLimit = !! init.pinsAutoClose ? 1 : 0;

        if ( pins ) {
            pins.forEach( function( pin ) {
                if ( ! pin.position ) {
                    return;
                }

                var markerOptions = {};

                if ( pin.image ) {
                    var iconOptions = {
                        iconUrl: pin.image
                    };

                    if ( undefined !== pin.image_width && undefined !== pin.image_height ) {
                        iconOptions.iconSize = [ pin.image_width, pin.image_height ];
                        iconOptions.iconAnchor = [ pin.image_width / 2, pin.image_height / 2 ];
                    }

                    markerOptions.icon = window.L.icon( iconOptions );
                }

                var marker = window.L.marker( [ pin.position.lat, pin.position.lng ], markerOptions ).addTo( map );

                if ( '' !== pin.desc || undefined !== pin.link_title ) {
                    var popupContent = pin.desc || '';

                    if ( undefined !== pin.link_title && pin.link ) {
                        popupContent += buildMapPinLink( pin );
                    }

                    var popupOptions = {
                        className: 'jet-map-box',
                        autoClose: !! init.pinsAutoClose,
                        closeOnClick: !! init.pinsAutoClose,
                    };

                    marker.bindPopup( popupContent, popupOptions );

                    if ( !! init.pinsAutoClose ) {
                        marker.on( 'popupopen', function( e ) {
                            if ( openedPopup && openedPopup !== e.popup ) {
                                openedPopup.close();
                            }
                            openedPopup = e.popup;
                        } );
                    }
                }

                if ( 'visible' === pin.state && '' !== pin.desc ) {
                    if ( 1 === pinsOpenLimit ) {
                        marker.openPopup();
                        pinsOpenLimit++;
                    } else if ( 0 === pinsOpenLimit ) {
                        marker.openPopup();
                    }
                }
            } );
        }
    }

    function initMapboxMap( $container, init, pins ) {

        if ( 'undefined' === typeof window.mapboxgl ) {
            return;
        }

        if ( 'undefined' === typeof window.jetElements || ! jetElements.mapboxToken ) {
            console.warn( 'Mapbox access token is missing.' );
            return;
        }

        mapboxgl.accessToken = jetElements.mapboxToken;

        var containerId = $container.attr( 'id' );

        if ( ! containerId ) {
            containerId = 'jet-map-' + Math.random().toString( 36 ).substr( 2, 9 );
            $container.attr( 'id', containerId );
        }

        var options = {
            container: containerId,
            style: init.mapboxStyle || 'mapbox://styles/mapbox/streets-v11',
            center: init.center ? [ init.center.lng, init.center.lat ] : [ 0, 0 ],
            zoom: init.zoom || 11,
        };

        var map = new mapboxgl.Map( options );

        if ( false === init.scrollwheel ) {
            map.scrollZoom.disable();
        }

        if ( false === init.draggable ) {
            map.dragPan.disable();
            map.dragRotate.disable();
            map.touchZoomRotate.disable();
            map.boxZoom.disable();
            map.doubleClickZoom.disable();
            map.keyboard.disable();
        }

        if ( false !== init.zoomControl ) {
            map.addControl( new mapboxgl.NavigationControl() );
        }

        var autoClose = !! init.pinsAutoClose,
            openedPopups = [],
            initialOpeners = [];

        var registerPopup = function( popup ) {
            popup.on( 'open', function() {
                if ( autoClose ) {
                    openedPopups.forEach( function( activePopup ) {
                        activePopup.remove();
                    } );
                    openedPopups = [];
                }

                openedPopups.push( popup );
            } );

            popup.on( 'close', function() {
                openedPopups = openedPopups.filter( function( activePopup ) {
                    return activePopup !== popup;
                } );
            } );
        };

        map.on( 'load', function() {

            if ( pins ) {
                pins.forEach( function( pin ) {

                    if ( ! pin.position ) {
                        return;
                    }

                    var marker;

                    if ( pin.image ) {
                        var markerElement = document.createElement( 'div' );

                        markerElement.className = 'jet-mapbox-marker';

                        var markerImage = document.createElement( 'img' );
                        markerImage.src = pin.image;

                        if ( pin.image_width ) {
                            markerImage.style.width = pin.image_width + 'px';
                        }

                        if ( pin.image_height ) {
                            markerImage.style.height = pin.image_height + 'px';
                        }

                        markerElement.appendChild( markerImage );
                        marker = new mapboxgl.Marker( markerElement );
                    } else {
                        marker = new mapboxgl.Marker();
                    }

                    marker.setLngLat( [ pin.position.lng, pin.position.lat ] ).addTo( map );
                    marker.getElement().style.cursor = 'pointer';

                    if ( pin.desc || ( pin.link_title && pin.link ) ) {
                        var popupContent = pin.desc || '';

                        if ( pin.link_title && pin.link ) {
                            popupContent += buildMapPinLink( pin );
                        }

                        var popup = new mapboxgl.Popup( {
                            closeButton: true,
                            closeOnClick: false,
                            focusAfterOpen: false,
                            className: 'jet-map-box',
                            maxWidth: '320px'
                        } ).setHTML( popupContent );

                        registerPopup( popup );
                        marker.setPopup( popup );

                        if ( 'visible' === pin.state && '' !== pin.desc ) {
                            initialOpeners.push( function() {
                                if ( ! marker.getPopup().isOpen() ) {
                                    marker.togglePopup();
                                }
                            } );
                        }
                    }
                } );
            }

            if ( initialOpeners.length ) {
                initialOpeners.forEach( function( opener ) {
                    opener();
                } );
            }
        } );
    }

    function buildMapPinLink( pin ) {
        var wrapper = document.createElement( 'div' ),
            link = document.createElement( 'a' ),
            url = typeof pin.link.url === 'string' ? pin.link.url.trim() : '',
            // Browsers ignore tabs and newlines when parsing URL schemes.
            normalizedURL = url.replace( /[\u0000-\u0020\u007f]/g, '' ),
            scheme = normalizedURL.match( /^([^/?#:]+):/ );

        if ( scheme && ! /^(https?|mailto|tel)$/i.test( scheme[1] ) ) {
            url = '#';
        }

        wrapper.className = 'jet-map-pin__wrapper';
        link.className = 'jet-map-pin__link';
        link.setAttribute( 'href', url || '#' );
        link.textContent = pin.link_title;

        if ( 'on' === pin.link.is_external ) {
            link.setAttribute( 'target', '_blank' );
        }

        if ( 'on' === pin.link.nofollow ) {
            link.setAttribute( 'rel', 'nofollow' );
        }

        parseMapCustomAttributes( pin.link.custom_attributes, link );
        wrapper.appendChild( link );

        return wrapper.outerHTML;
    }

    function parseMapCustomAttributes( attributes_string, link ) {
        if ( typeof attributes_string !== 'string' || ! attributes_string ) {
            return;
        }

        attributes_string.split( ',' ).forEach( function( attribute ) {
            var separator = attribute.indexOf( '|' ),
                key = ( separator < 0 ? attribute : attribute.substring( 0, separator ) ).trim().toLowerCase(),
                value = separator < 0 ? '' : attribute.substring( separator + 1 ).trim();

            // Custom attributes must not override the validated URL or add handlers.
            if ( ! /^[-_a-z0-9]+$/.test( key ) || 'href' === key || 'on' === key.substring( 0, 2 ) ) {
                return;
            }

            if ( ! link.hasAttribute( key ) ) {
                link.setAttribute( key, value );
            }
        } );
    }

    window.widgetMap = widgetMap;

})( jQuery );
