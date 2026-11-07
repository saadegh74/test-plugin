<?php
use PowerpackElements\Classes\PP_Helper;
use PowerpackElements\Classes\PP_Admin_Settings;

$settings   = PP_Admin_Settings::get_settings();
$languages  = PP_Helper::get_google_map_languages();
?>
<h3><?php _e( 'Integration', 'powerpack' ); ?></h3>
<p><?php echo __( 'Facebook App ID is required only if you want to use Facebook Comments Module. All other Facebook Modules can be used without a Facebook App ID. Note that this option will not work on local sites and on domains that don\'t have public access.', 'powerpack' ); ?></p>

<table class="form-table">
	<tr align="top" id="pp-settings__fb-app-id">
		<th scope="row" valign="top">
			<label for="pp_fb_app_id"><?php esc_html_e( 'Facebook App ID', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_fb_app_id" name="pp_fb_app_id" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_fb_app_id', true ); ?>" />
			<p class="description">
				<?php // translators: %s: Facebook App Setting link ?>
				<?php echo sprintf( __( 'To get your Facebook App ID, you need to <a href="%1$s" target="_blank">register and configure</a> an app. Once registered, add the domain to your <a href="%2$s" target="_blank">App Domains</a>', 'powerpack' ), 'https://developers.facebook.com/docs/apps/register/', PP_Helper::get_fb_app_settings_url() ); ?>
			</p>
		</td>
	</tr>
	<tr align="top" id="pp-settings__fb-app-secret" >
		<th scope="row" valign="top">
			<label for="pp_fb_app_secret"><?php esc_html_e( 'Facebook App Secret', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_fb_app_secret" name="pp_fb_app_secret" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_fb_app_secret', true ); ?>" autofill="false" autocomplete="false" autosuggest="false" />
			<p class="description">
				<?php // translators: %s: Facebook App Setting link ?>
				<?php echo sprintf( __( 'To get your Facebook App Secret, you need to <a href="%1$s" target="_blank">register and configure</a> an app. Once registered, you will find App Secret under <a href="%2$s" target="_blank">App Domains</a>', 'powerpack' ), 'https://developers.facebook.com/docs/apps/register/', PP_Helper::get_fb_app_settings_url() ); ?>
			</p>
		</td>
	</tr>
	<tr align="top" id="pp-settings__google-client-id" >
		<th scope="row" valign="top">
			<label for="pp_google_client_id"><?php esc_html_e( 'Google Client ID', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_google_client_id" name="pp_google_client_id" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_google_client_id', true ); ?>" />
			<p class="description">
				<?php // translators: %s: Google API document ?>
				<?php echo sprintf( __( 'To get your Google Client ID, read <a href="https://powerpackelements.com/docs/create-google-client-id/" target="_blank">this document</a>', 'powerpack' ), '#' ); ?>
			</p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<?php esc_html_e( 'Google Map API Key', 'powerpack' ); ?>
		</th>
		<td>
			<input id="pp_google_map_api" name="pp_google_map_api" type="text" class="regular-text" value="<?php echo $settings['google_map_api']; ?>" />
		<p class="description">
			<?php // translators: %s: Google API document ?>
			<?php echo sprintf( __( 'To get your Google API Key, read <a href="%s" target="_blank">this document</a>', 'powerpack' ), 'https://developers.google.com/maps/documentation/javascript/get-api-key' ); ?>
		</p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<?php esc_html_e( 'Google Map Localization', 'powerpack' ); ?>
		</th>
		<td>
			<select name="pp_google_map_lang" id="pp-google-map-language" class="placeholder placeholder-active">
				<option value=""><?php _e( 'Default', 'powerpack' ); ?></option>
				<?php foreach ( $languages as $key => $value ) { ?>
					<?php
					$selected = '';
					if ( $key === $settings['google_map_lang'] ) {
						$selected = 'selected="selected" ';
					}
					?>
					<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo esc_attr( $value ); ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_google_places_api_key"><?php esc_html_e( 'Google Places API Key', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_google_places_api_key" name="pp_google_places_api_key" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_google_places_api_key', true ); ?>" />
			<p class="description">
				<?php // translators: %s: Google API document ?>
				<?php echo sprintf( __( 'To get your Google Places API Key, read <a href="%s" target="_blank">this document</a>', 'powerpack' ), 'https://developers.google.com/places/web-service/get-api-key' ); ?>
			</p>
		</td>
	</tr>
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_yelp_api_key"><?php esc_html_e( 'Yelp Business API Key', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_yelp_api_key" name="pp_yelp_api_key" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_yelp_api_key', true ); ?>" />
			<p class="description">
				<?php // translators: %s: Yelp API document ?>
				<?php echo sprintf( __( 'To get your Yelp API Key, read <a href="%s" target="_blank">this document</a>', 'powerpack' ), 'https://www.yelp.com/developers/documentation/v3/authentication' ); ?>
			</p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" valign="top">
			<?php esc_html_e( 'Instagram Access Token', 'powerpack' ); ?>
		</th>
		<td>
			<input id="pp_instagram_access_token" name="pp_instagram_access_token" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_instagram_access_token', true ); ?>" />
		<p class="description">
			<?php // translators: %s: Google API document ?>
			<?php echo sprintf( __( 'To get your Instagram Access Token, read <a href="%s" target="_blank">this document</a>', 'powerpack' ), 'https://powerpackelements.com/docs/create-instagram-access-token-for-instagram-feed-widget/' ); ?>
		</p>
		</td>
	</tr>
</table>

<h3><?php esc_html_e( 'reCAPTCHA V2', 'powerpack' ); ?></h3>
<p>
	<?php // translators: %s: reCAPTCHA Site Key document ?>
	<?php echo sprintf( __( 'Register keys for your website at the <a href="%s" target="_blank">Google Admin Console</a>.', 'powerpack' ), 'https://www.google.com/recaptcha/admin' ); ?>
</p>
<table class="form-table">
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_recaptcha_site_key"><?php esc_html_e( 'Site Key', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_recaptcha_site_key" name="pp_recaptcha_site_key" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_recaptcha_site_key', true ); ?>" />
		</td>
	</tr>
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_recaptcha_secret_key"><?php esc_html_e( 'Secret Key', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_recaptcha_secret_key" name="pp_recaptcha_secret_key" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_recaptcha_secret_key', true ); ?>" />
		</td>
	</tr>
</table>

<h3><?php esc_html_e( 'reCAPTCHA V3', 'powerpack' ); ?></h3>
<p>
	<?php // translators: %s: reCAPTCHA Site Key document ?>
	<?php echo sprintf( __( 'Register keys for your website at the <a href="%s" target="_blank">Google Admin Console</a>.', 'powerpack' ), 'https://www.google.com/recaptcha/admin' ); ?>
	<br />
	<?php echo sprintf( __( '<a href="%s" target="_blank">More info about reCAPTCHA V3</a>', 'powerpack' ), 'https://developers.google.com/recaptcha/docs/v3' ); ?>
</p>
<table class="form-table">
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_recaptcha_v3_site_key"><?php esc_html_e( 'Site Key', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_recaptcha_v3_site_key" name="pp_recaptcha_v3_site_key" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_recaptcha_v3_site_key', true ); ?>" />
		</td>
	</tr>
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_recaptcha_v3_secret_key"><?php esc_html_e( 'Secret Key', 'powerpack' ); ?></label>
		</th>
		<td>
			<input id="pp_recaptcha_v3_secret_key" name="pp_recaptcha_v3_secret_key" type="text" class="regular-text" value="<?php echo PP_Admin_Settings::get_option( 'pp_recaptcha_v3_secret_key', true ); ?>" />
		</td>
	</tr>
</table>

<h3><?php esc_html_e( 'CSV Upload', 'powerpack' ); ?></h3>
<table class="form-table">
	<tr align="top">
		<th scope="row" valign="top">
			<label for="pp_enable_csv_upload"><?php esc_html_e( 'Enable CSV Upload', 'powerpack' ); ?></label>
		</th>
		<td>
		<?php $selected = PP_Admin_Settings::get_option( 'pp_enable_csv_upload', true ); ?>
			<select name="pp_enable_csv_upload" id="pp_enable_csv_upload" class="placeholder placeholder-active">
				<option value="disabled" <?php echo ( 'disbaled' == $selected ) ? ' selected="selected"' : ''; ?>><?php _e( 'Disabled', 'powerpack' ); ?></option>
				<option value="enabled" <?php echo ( 'enabled' == $selected ) ? ' selected="selected"' : ''; ?>><?php _e( 'Enabled', 'powerpack' ); ?></option>
			</select>
			<p class="description">
				<?php // translators: %s: Enable CSV Upload ?>
				<?php echo sprintf( __( 'Latest versions of WordPress have enabled more stringent security checks for file types that can be uploaded via Media Uploader.<br/>
				Please enable the CSV Upload option in case you\'re facing troubles in uploading CSV file in the Table Widget.', 'powerpack' )); ?>
			</p>
		</td>
	</tr>
</table>
