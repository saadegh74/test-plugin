<?php
	use PowerpackElements\Classes\PP_Admin_Settings;
	use PowerpackElements\Classes\PP_Login_Register;

	$settings   = PP_Admin_Settings::get_settings();
?>
<h3><?php _e('Login / Register Pages Setup', 'powerpack'); ?></h3>

<table class="form-table maintenance-mode-config">
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_login_page"><?php esc_html_e( 'Login page', 'powerpack' ); ?></label>
		</th>
		<td>
			<?php $selected = PP_Admin_Settings::get_option( 'pp_login_page', true ); ?>
			<select id="pp_login_page" name="pp_login_page" style="min-width: 200px;">
				<?php echo PP_Login_Register::get_pages( $selected ); ?>
			</select>
		</td>
	</tr>
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_register_page"><?php esc_html_e( 'Register page', 'powerpack' ); ?></label>
		</th>
		<td>
			<?php $selected = PP_Admin_Settings::get_option( 'pp_register_page', true ); ?>
			<select id="pp_register_page" name="pp_register_page" style="min-width: 200px;">
				<?php echo PP_Login_Register::get_pages( $selected ); ?>
			</select>
		</td>
	</tr>
</table>