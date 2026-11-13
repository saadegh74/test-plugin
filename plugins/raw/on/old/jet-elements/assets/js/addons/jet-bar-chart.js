(function ($) {

    "use strict";

    function widgetBarChart( $scope ) {

        var $chart            = $scope.find( '.jet-bar-chart-container' ),
            $chart_canvas     = $chart.find( '.jet-bar-chart' ),
            settings          = $chart.data( 'settings' ),
            tooltip_prefix    = $chart.data( 'tooltip-prefix' ) || '',
            tooltip_suffix    = $chart.data( 'tooltip-suffix' ) || '',
            tooltip_separator = $chart.data( 'tooltip-separator' ) || '',
            bar_type          = settings['type'] || 'bar',
            axis_separator    = $chart.data( 'axis-separator' ) || '',
            labels_length     = $chart.data( 'labels-length' ) || 50;

        if ( true === settings.options.tooltips.enabled ) {
            settings.options.tooltips.callbacks = {
                label: function(tooltipItem, data) {
                    return ' ' + data.datasets[tooltipItem.datasetIndex].label + ': ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                }
            }
        }

        if ( ! $chart.length ) {
            return;
        }

        if ( true === settings.options.tooltips.enabled ) {
            settings.options.tooltips.callbacks = {
                label: function( tooltipItem, data ) {
                    var value = '' != tooltip_separator ? JetElementsTools.addThousandCommaSeparator( data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index], tooltip_separator) : data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                    return ' ' + data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltip_prefix + value + tooltip_suffix;
                }
            };
        }

        if ( true === axis_separator ) {

            if ( 'bar' === bar_type ) {
                settings.options.scales.yAxes[0].ticks.callback = function(value, index, values){
                    return value.toLocaleString("en-US")
                }
            } else {
                settings.options.scales.xAxes[0].ticks.callback = function(value, index, values){
                    return value.toLocaleString("en-US")
                }
            }
        }

        JetElements.observer( $chart_canvas, function() {
            var $this         = $( this ),
                ctx           = $this[0].getContext( '2d' ),
                wrappedLabels = [];

                var wrap  = ( label, limit ) => {
                    // Ensure that label is a string
                    label = String( label );
                    
                    let words = label.split(" ");
                    let labels = [];
                    let concat = [];
                    
                    for ( let i = 0; i < words.length; i++ ) {
                        concat.push( words[i] );
                        let join = concat.join(' ');
                        if ( join.length > limit ) {
                            labels.push( join );
                            concat = [];
                        }
                    }
                    
                    if ( concat.length ) {
                        labels.push( concat.join(' ').trim() );
                    }
                    
                    return labels;
                }
                
                settings.data.labels.forEach( function( label ) {
                    wrappedLabels.push( wrap( label, labels_length ));
                });

            settings.data.labels = wrappedLabels;

            var myChart = new Chart( ctx, settings );

        }, JetElements.prepareWaypointOptions( $scope, {
            offset: 'bottom-in-view'
        } ) );
    }

    window.widgetBarChart = widgetBarChart;

})( jQuery );