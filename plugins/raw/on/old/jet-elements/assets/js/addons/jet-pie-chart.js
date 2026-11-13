(function ($) {

    "use strict";

    function widgetPieChart( $scope ) {

        var $container     = $scope.find( '.jet-pie-chart-container' ),
            $canvas        = $scope.find( '.jet-pie-chart' )[0],
            data           = $container.data( 'chart' ) || {},
            options        = $container.data( 'options' ) || {},
            tooltip        = $container.data( 'tooltip' ) || '',
            defaultOptions = {
                maintainAspectRatio: false
            };

        options = $.extend( {}, defaultOptions, options );

        if ( true === options.tooltips.enabled ) {
            options.tooltips.callbacks = {
                label: function( tooltipItem, data ) {
                    return ' ' + data.labels[tooltipItem.index] + ': ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] + tooltip;
                }
            };
        }

        JetElements.observer( $scope, function() {
            var chartInstance = new Chart( $canvas, {
                type:    'pie',
                data:    data,
                options: options
            } );
        }, JetElements.prepareWaypointOptions( $scope, {
            offset: 'bottom-in-view'
        } ) );
        }

    window.widgetPieChart = widgetPieChart;

})( jQuery );