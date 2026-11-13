<?php

if( ! function_exists( 'ES_menu' ) ) {
    function ES_menu() {

        if ( is_plugin_active( 'jet-search/jet-search.php' ) ) {
                remove_menu_page( 'jet-dashboard' );
        }
 

    }
    add_action( 'admin_init', 'ES_menu', 99 );
}


