(function ($) {

    "use strict";

    /**
     * Initialize a line chart with a text-only custom tooltip.
     * @param {Object} $scope Elementor widget scope.
     */
    function widgetLineChart( $scope ) {

        var id                  = $scope.data( 'id' ),
            $line_chart         = $scope.find( '.jet-line-chart-container' ),
            $line_chart_canvas  = $line_chart.find( '.jet-line-chart' ),
            $compare            = $line_chart.data( 'compare' ),
            previous_label      = $line_chart.data( 'previous-label' ),
            current_label       = $line_chart.data( 'current-label' ),
            settings            = $line_chart.data( 'settings' ),
            compare_labels_type = $line_chart.data( 'compare-labels-type' ),
            tooltip_prefix      = $line_chart.data( 'tooltip-prefix' ) || '',
            tooltip_suffix      = $line_chart.data( 'tooltip-suffix' ) || '',
            tooltip_separator   = $line_chart.data( 'tooltip-separator' ) || '';

        if ( ! $line_chart.length ) {
            return;
        }

        /**
         * Create tooltip markup using literal text for all setting values.
         * @param {string} className Fixed tooltip class.
         * @param {*} text Optional text content.
         * @return {HTMLElement} Tooltip element.
         */
        function createTooltipElement( className, text ) {
            var element = document.createElement( 'div' );
            element.className = className;
            if ( text !== undefined && text !== null ) {
                element.textContent = text;
            }
            return element;
        }

        /**
         * Format a dataset value with the configured separator, prefix and suffix.
         * @param {*} value Dataset value.
         * @return {string} Display text.
         */
        function formatValue( value ) {
            return tooltip_prefix + ( '' != tooltip_separator ? JetElementsTools.addThousandCommaSeparator( value, tooltip_separator ) : value ) + tooltip_suffix;
        }

        JetElements.observer( $line_chart_canvas, function() {

            var $this   = $( this ),
                ctx     = $this[0].getContext( '2d' ),
                myLineChart = new Chart( ctx, settings );

                myLineChart.options.tooltips = {
                    enabled:   false,
                    mode:      'x-axis',
                    intersect: false,
                    custom: function( tooltipModel ) {
                        // Tooltip Element
                        var tooltipEl = document.getElementById( 'chartjs-tooltip_' + id );

                        // Create element on first render
                        if ( !tooltipEl ) {
                            tooltipEl = document.createElement( 'div' );
                            tooltipEl.id = 'chartjs-tooltip_' + id;
                            tooltipEl.appendChild( createTooltipElement( 'jet-line-chart-tooltip' ) );
                            document.body.appendChild( tooltipEl );
                        }

                        // Hide if no tooltip
                        if ( tooltipModel.opacity === 0 ) {
                            tooltipEl.style.opacity = 0;
                            return;
                        }
                        // Set caret Position
                        tooltipEl.classList.remove( 'above', 'below', 'no-transform' );
                        if ( tooltipModel.yAlign ) {
                            tooltipEl.classList.add( tooltipModel.yAlign );
                        } else {
                            tooltipEl.classList.add( 'no-transform' );
                        }

                        var tableRoot = tooltipEl.querySelector( 'div' ),
                            wrapper = createTooltipElement( 'jet-line-chart-tooltip-wrapper' );

                        ( tooltipModel.dataPoints || [] ).forEach( function( item ) {
                            var data = myLineChart.data,
                                dataset = data.datasets[item.datasetIndex],
                                body = createTooltipElement( 'jet-line-chart-tooltip-body' ),
                                colorBox = document.createElement( 'span' );

                            colorBox.className = 'jet-line-chart-tooltip-color-box';
                            // Assign a single CSS property; never parse settings as HTML or CSS declarations.
                            colorBox.style.backgroundColor = dataset.borderColor;

                            if ( true === $compare ) {
                                var title = createTooltipElement( 'jet-line-chart-tooltip-title', dataset.label ),
                                    currentLabel = 'custom' === compare_labels_type ? current_label : data.labels[item.index];

                                title.insertBefore( colorBox, title.firstChild );
                                wrapper.appendChild( title );
                                body.appendChild( createTooltipElement( 'jet-line-chart-tooltip-compare-current', currentLabel + ' : ' + formatValue( dataset.data[item.index] ) ) );

                                if ( typeof data.labels[item.index - 1] !== 'undefined' && data.labels[item.index - 1] !== null ) {
                                    var previousLabel = 'custom' === compare_labels_type ? previous_label : data.labels[item.index - 1];
                                    body.appendChild( createTooltipElement( 'jet-line-chart-tooltip-compare-previous', previousLabel + ' : ' + formatValue( dataset.data[item.index - 1] ) ) );
                                }
                            } else {
                                body.textContent = dataset.label + ' : ' + formatValue( dataset.data[item.index] );
                                body.insertBefore( colorBox, body.firstChild );
                            }

                            wrapper.appendChild( body );
                        } );

                        tableRoot.textContent = '';
                        tableRoot.appendChild( wrapper );

                        // `this` will be the overall tooltip

                        var _this         = this,
                            position      = this._chart.canvas.getBoundingClientRect(),
                            tooltipWidth  = tooltipEl.offsetWidth,
                            tooltipHeight = tooltipEl.offsetHeight,
                            offsetX       = 0,
                            offsetY       = 0;

                        setTimeout( function(){
                            tooltipWidth = tooltipEl.offsetWidth;
                            tooltipHeight = tooltipEl.offsetHeight;

                            if ( _this._chart.width / 2 > _this._chart.tooltip._eventPosition.x ) {
                                offsetX = 0;
                            } else {
                                offsetX = -tooltipWidth;
                            }

                            if ( _this._chart.height / 2 > _this._chart.tooltip._eventPosition.y ) {
                                offsetY = 0;
                            } else {
                                offsetY = -tooltipHeight;
                            }
                            tooltipEl.style.left = position.left + window.pageXOffset + tooltipModel.caretX + offsetX + 'px';
                            tooltipEl.style.top = position.top + window.pageYOffset + tooltipModel.caretY + offsetY + 'px';
                            tooltipEl.style.opacity = 1;
                        }, 10 );

                        // Display, position, and set styles for font
                        tooltipEl.style.position = 'absolute';
                        tooltipEl.style.fontFamily = tooltipModel._bodyFontFamily;
                        tooltipEl.style.fontSize = tooltipModel.bodyFontSize + 'px';
                        tooltipEl.style.fontStyle = tooltipModel._bodyFontStyle;
                        tooltipEl.style.padding = tooltipModel.yPadding + 'px ' + tooltipModel.xPadding + 'px';
                        tooltipEl.style.pointerEvents = 'none';
                    },
                };
                myLineChart.update();
        }, JetElements.prepareWaypointOptions( $scope, {
            offset: 'bottom-in-view'
        } ) );
    }

    window.widgetLineChart = widgetLineChart;

})( jQuery );
