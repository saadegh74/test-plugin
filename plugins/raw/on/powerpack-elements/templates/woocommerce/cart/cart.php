<?php
/**
 * Cart Page 
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit();

?>
<div class="woocommerce pp-elementor-cart">
    <?php
        wc_print_notices();
        do_action( 'pp_cart_content' );
    ?>
</div>