<?php if ( isset( $recaptcha_site_key ) && ! empty( $recaptcha_site_key ) ) { ?>
	<div id="<?php echo esc_attr( $id ); ?>-pp-grecaptcha" class="pp-grecaptcha" data-sitekey="<?php echo esc_attr( $recaptcha_site_key ); ?>" data-validate="<?php echo esc_attr( $recaptcha_validate_type ); ?>" data-theme="<?php echo esc_attr( $recaptcha_theme ); ?>"></div>
<?php } else { ?>
	<div><?php echo pp_get_recaptcha_desc(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div> 
<?php } ?>
