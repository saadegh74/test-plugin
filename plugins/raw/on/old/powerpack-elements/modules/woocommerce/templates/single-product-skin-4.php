<?php if ( 'yes' == $settings['show_image'] ) :
	include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/partials/product-image.php';
endif; ?>

<div class="product-content">

    <?php
    if ( 'yes' == $settings['product_title'] ) :
		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/partials/product-title.php';
    endif;

	if ( 'yes' == $settings['product_rating'] ) :
		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/partials/product-rating.php';
    endif;

	if ( 'yes' == $settings['product_price'] ) :
		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/partials/product-price.php';
    endif;

    if ( 'yes' == $settings['product_short_description'] ) :
		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/partials/product-description.php';
    endif;

    if ( 'cart' == $settings['button_type'] ) :
		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/partials/product-add-button.php';
    endif;

    if ( 'custom' == $settings['button_type'] ) :
		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/partials/product-custom-button.php';
    endif;

    if ( 'yes' == $settings['show_sku'] || 'yes' == $settings['show_taxonomy'] ) :
		include POWERPACK_ELEMENTS_PATH . 'modules/woocommerce/templates/partials/product-meta.php';
	endif;
	?>

</div>  <!-- product-content -->

