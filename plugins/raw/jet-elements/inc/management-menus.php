<?php

if( ! function_exists( 'ES_menu' ) ) {
    function ES_menu() {

 
        if ( is_plugin_active( 'jet-elements/jet-elements.php' ) ) {
                remove_menu_page( 'jet-dashboard' );
        }

        add_menu_page( 
            'تنظیمات جت',
            'تنظیمات جت',
            'manage_options',
            'admin.php?page=jet-dashboard-settings-page',
            '',
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAsAAAASCAMAAAC6q9RHAAAAAXNSR0IB2cksfwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAD9QTFRFAAAA////////////////////////////////////////////////////////////////////////////////Q7v6agAAABV0Uk5TAGCf3/8wz0Dv8JDAoNCwgL+PX1DgyMNPEQAAAF9JREFUeJxtzkcSwCAIAECM2DXV/781tJl4CKcFAQHgcJtHH1ghIkckpizEQlUjVoAmaNVRhzwnWdHZShjEXQlcPn689qyzsjNbon+d182Xle8GSJZkuXmIh848feIMLzH6AygC2uZPAAAAAElFTkSuQmCC
            ',
            30
        );
        
  

    }
    add_action( 'admin_init', 'ES_menu', 99 );
}


