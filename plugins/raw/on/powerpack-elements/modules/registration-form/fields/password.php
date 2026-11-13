<div class="pp-rf-field-inner">
	<input
		type="password" 
		class="pp-rf-control elementor-field elementor-size-sm elementor-size-<?php echo esc_attr( $settings['input_size'] ); ?> form-field-password elementor-field-textual" 
		name="<?php echo esc_attr( $field_name ); ?>" 
		id="<?php echo esc_attr( $field_id ); ?>" 
		value="" 
		placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" 
		autocomplete="off" 
		autocorrect="off" 
		autocapitalize="off" 
		spellcheck="false" 
		aria-required="true" 
		aria-describedby="login_error" 
	/>
	<?php if ( 'yes' === $field['password_toggle'] ) { ?>
	<button type="button" class="pp-rf-toggle-pw hide-if-no-js" aria-label="<?php esc_attr_e( 'Show password', 'powerpack' ); ?>">
		<span class="fa far fa-eye" aria-hidden="true"></span>
	</button>
	<?php } ?>
</div>
<div class="pp-rf-pws-status"></div>
