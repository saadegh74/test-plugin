<?php

if( ! function_exists( 'ES_menu' ) ) {
    function ES_menu() {

        if( ! @function_exists( remove_menu_page( 'jet-dashboard' ) ) ) remove_menu_page( 'jet-dashboard' );

        add_menu_page( 
            'تنظیمات المنتور سایت',
            'المنتور سایت',
            'manage_options',
            'admin.php?page=jet-dashboard-settings-page',
            '',
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAPCAMAAADTRh9nAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAVxQTFRFAAAAp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qtp6qt+gmy7AAAAHR0Uk5TAGG5SRN2uLuqJhGCunLaVhbJzCkUmc3IgIP8Y3Csrp8jsPGop2sLPzsNr+BdOAkcJ90hFW6pq520pKNoF4TLvCrU0dbrl1mIiX4OaZb3oBolCAMdQ3EYBiw+V/L7wxKGv8f4ZMBMfMIovnoyYAo9XzwEAQJevNoTAAABA0lEQVR4nGNgwAYYmZhZWNnY2dk5OLm42UGAh5WBl49fgFtQSEiISVhElE9MUFBQXIJBUkpaREZWTk5OXkFAUYlNWVlZRRUkqCasDgQamgJa2jq6QKAAEtTTFwYCAz0BLUNmIyAwBms3MTUzMzNXENBSsrC0tLSyBgna2NqJiYnZO4iYazs6OTm7uIIEudzcPTw8PPW9vH2AwNLXD2ymvwEQBAQGBYPMDlECC3oxh/Ly8nqHhUdEAkFUtB8Dd4y0jbujs7NzbERcfAIQJCbJMySnpIqkpaSnpytmxNlmgkBKFkN2TkBuXn5OTk6USGBIDgjkFzAEFRYxFBcCQVARkA0GQQC8WDrLUI9ykwAAAABJRU5ErkJggg==
            ',
            30
        );
        
        if ( is_plugin_active( 'jet-theme-core/jet-theme-core.php' ) ) {
            add_submenu_page(
                'admin.php?page=jet-dashboard-settings-page',
                'قالب ساز',
                'قالب ساز',
                'manage_options',
                'edit.php?post_type=jet-theme-core',
            );        
        } 

        if ( is_plugin_active( 'jet-woo-builder/jet-woo-builder.php' ) 
             && is_plugin_active( 'woocommerce/woocommerce.php' )
             && is_plugin_active( 'elementor/elementor.php' )  ) {
            add_submenu_page(
                'admin.php?page=jet-dashboard-settings-page',
                'ووکامرس ساز',
                'ووکامرس ساز',
                'manage_options',
                'edit.php?post_type=jet-woo-builder',
            );        
        } 

    }
    add_action( 'admin_init', 'ES_menu', 99 );
}


