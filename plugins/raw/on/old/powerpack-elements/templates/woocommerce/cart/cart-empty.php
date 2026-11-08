<?php
/**
 * Empty Cart Page 
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit();

?>
<div class="woocommerce pp-elementor-empty-cart">
    <?php
        do_action( 'pp_cart_empty_content' );
    ?>
</div>