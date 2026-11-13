<div
	class="jet-elements-settings-page jet-elements-settings-page__integratios"
>
	<div
		class="cx-vui-subtitle"
		v-html="'<?php esc_html_e( 'Google Maps', 'jet-elements' ); ?>'"></div>

	<cx-vui-select
		name="map-provider"
		label="<?php esc_attr_e( 'Map Provider', 'jet-elements' ); ?>"
		description="<?php esc_attr_e( 'Select a maps rendering provider for the Advanced Map widget.', 'jet-elements' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:options-list="pageOptions.map_provider.options"
		v-model="pageOptions.map_provider.value"></cx-vui-select>

	<template v-if="pageOptions.map_provider.value === 'google'">
	<cx-vui-input
		name="google-map-api-key"
		label="<?php esc_attr_e( 'Google Map API Key', 'jet-elements' ); ?>"
		description="<?php
			echo sprintf( esc_html__( 'Create own API key, more info %1$s', 'jet-elements' ),
				esc_html( '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a>' )
			);
		?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions.api_key.value"></cx-vui-input>

		<cx-vui-switcher
			name="google-map-disable-api-js"
			label="<?php esc_attr_e( 'Disable Google Maps API JS file', 'jet-elements' ); ?>"
			description="<?php esc_attr_e( 'Disable Google Maps API JS file, if it already included by another plugin or theme', 'jet-elements' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="pageOptions.disable_api_js.value.disable">
		</cx-vui-switcher>
	</template>

	<template v-if="pageOptions.map_provider.value === 'mapbox'">
		<cx-vui-input
			name="mapbox-access-token"
			label="<?php esc_attr_e( 'Access token', 'jet-elements' ); ?>"
			description="<?php _e( 'Create access token at Mapbox <a href=\'https://www.mapbox.com/\' target=\'_blank\'>website</a>', 'jet-elements' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-model="pageOptions.mapbox_access_token.value"></cx-vui-input>
	</template>

	<cx-vui-select
		name="geocode-provider"
		label="<?php esc_attr_e( 'Geocoding Provider', 'jet-elements' ); ?>"
		description="<?php esc_attr_e( 'Select a service for converting addresses into coordinates.', 'jet-elements' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:options-list="pageOptions.geocode_provider.options"
		v-model="pageOptions.geocode_provider.value"></cx-vui-select>

	<template v-if="pageOptions.geocode_provider.value === 'google'">
		<template v-if="pageOptions.map_provider.value === 'google'">
	<cx-vui-switcher
		name="google-map-use-geocoding-key"
		label="<?php esc_attr_e( 'Separate Geocoding API key', 'jet-elements' ); ?>"
		description="<?php esc_attr_e( 'Use separate key for Geocoding API. This allows you to set more accurate restrictions for your API key.', 'jet-elements' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="true"
		return-false="false"
		v-model="pageOptions.use_geocoding_key.value">
	</cx-vui-switcher>

	<cx-vui-input
		name="google-map-geocoding-api-key"
		label="<?php esc_attr_e( 'Geocoding API Key', 'jet-elements' ); ?>"
		description="<?php esc_attr_e( 'Google maps API key with Geocoding API enabled. For this key <b>Application restrictions</b> should be set to <b>None</b> or <b>IP addresses</b> and in the <b>API restrictions</b> you need to select <b>Don\'t restrict key</b> or enable <b>Geocoding API</b>', 'jet-elements' );?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions.geocoding_key.value"
		v-if="pageOptions.use_geocoding_key.value === 'true'">
	</cx-vui-input>
		</template>
		<template v-else>
			<cx-vui-input
				name="google-map-geocoding-api-key"
				label="<?php esc_attr_e( 'Geocoding API Key', 'jet-elements' ); ?>"
				description="<?php esc_attr_e( 'Google maps API key with Geocoding API enabled. For this key <b>Application restrictions</b> should be set to <b>None</b> or <b>IP addresses</b> and in the <b>API restrictions</b> you need to select <b>Don\'t restrict key</b> or enable <b>Geocoding API</b>', 'jet-elements' );?>"
				:wrapper-css="[ 'equalwidth' ]"
				size="fullwidth"
				v-model="pageOptions.geocoding_key.value">
			</cx-vui-input>
		</template>
	</template>

	<template v-if="pageOptions.geocode_provider.value === 'openstreetmap'">
		<cx-vui-component-wrapper
			label="<?php esc_attr_e( 'Note', 'jet-elements' ); ?>"
			description="<?php esc_attr_e( 'Be aware that this service runs on donated servers and has a very limited capacity. So please avoid heavy uses (an absolute maximum of 1 request per second).', 'jet-elements' ); ?>"
		></cx-vui-component-wrapper>
	</template>

	<template v-if="pageOptions.geocode_provider.value === 'bing'">
		<cx-vui-input
			name="bing-map-api-key"
			label="<?php esc_attr_e( 'Bing API Key', 'jet-elements' ); ?>"
			description="<?php _e( 'API key instructions - <a href=\'https://www.microsoft.com/en-us/maps/create-a-bing-maps-key\' target=\'_blank\'>https://www.microsoft.com/en-us/maps/create-a-bing-maps-key</a>', 'jet-elements' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-model="pageOptions.bing_key.value">
		</cx-vui-input>
	</template>

	<div
		class="cx-vui-subtitle"
		v-html="'<?php esc_html_e( 'MailChimp', 'jet-elements' ); ?>'"></div>

	<cx-vui-input
		name="mailchimp-api-key"
		label="<?php esc_attr_e( 'MailChimp API key', 'jet-elements' ); ?>"
		description="<?php
			echo sprintf( esc_html__( 'Input your MailChimp API key %1$s', 'jet-elements' ),
				esc_html( '<a href="http://kb.mailchimp.com/integrations/api-integrations/about-api-keys" target="_blank">About API Keys</a>' )
			);
		?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions['mailchimp-api-key'].value"></cx-vui-input>

	<cx-vui-input
		name="mailchimp-list-id"
		label="<?php esc_attr_e( 'MailChimp list ID', 'jet-elements' ); ?>"
		description="<?php
			echo sprintf( esc_html__( 'Input MailChimp list ID %1$s', 'jet-elements' ),
				esc_html( '<a href="http://kb.mailchimp.com/integrations/api-integrations/about-api-keys" target="_blank">About Mailchimp List Keys</a>' )
			);?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions['mailchimp-list-id'].value"></cx-vui-input>

	<cx-vui-switcher
		name="mailchimp-double-opt-in"
		label="<?php esc_attr_e( 'Double opt-in', 'jet-elements' ); ?>"
		description="<?php esc_attr_e( 'Send contacts an opt-in confirmation email when they subscribe to your list.', 'jet-elements' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="true"
		return-false="false"
		v-model="pageOptions['mailchimp-double-opt-in'].value">
	</cx-vui-switcher>

	<div
		class="cx-vui-subtitle"
		v-html="'<?php esc_html_e( 'Instagram', 'jet-elements' ); ?>'"></div>

	<cx-vui-input
		name="insta-access-token"
		label="<?php esc_attr_e( 'Business Access Token', 'jet-elements' ); ?>"
		description="<?php
			echo sprintf( esc_html__( 'Read more about how to get Instagram Access Token %1$s', 'jet-elements' ),
				esc_html( '<a href="https://crocoblock.com/knowledge-base/articles/how-to-create-instagram-access-token-for-jetelements-instagram-widget/" target="_blank">here</a>' )
			); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions.insta_access_token.value"></cx-vui-input>

	<?php /*

	<cx-vui-input
		name="insta-business-access-token"
		label="<?php _e( 'Business Access Token', 'jet-elements' ); ?>"
		description="<?php
		echo sprintf( esc_html__( 'Read more about how to get Business Instagram Access Token %1$s', 'jet-elements' ),
			htmlspecialchars( '<a href="https://crocoblock.com/knowledge-base/articles/jetelements-how-to-display-instagram-tagged-photos/" target="_blank">here</a>', ENT_QUOTES )
		); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions.insta_business_access_token.value"></cx-vui-input>

	<cx-vui-input
		name="insta-business-user-id"
		label="<?php _e( 'Business User ID', 'jet-elements' ); ?>"
		description="<?php
		echo sprintf( esc_html__( 'Read more about how to get Business User ID %1$s', 'jet-elements' ),
			htmlspecialchars( '<a href="https://crocoblock.com/knowledge-base/articles/jetelements-how-to-display-instagram-tagged-photos/" target="_blank">here</a>', ENT_QUOTES )
		); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions.insta_business_user_id.value"></cx-vui-input>

	 */ ?>

	<div
		class="cx-vui-subtitle"
		v-html="'<?php esc_html_e( 'Weatherbit.io API (APIXU API deprecated)', 'jet-elements' ); ?>'"></div>

	<div class="cx-vui-component__desc"
		v-html="'<?php
		echo sprintf( esc_html__( 'If you plan to use the weather widget commercially, please choose the applicable pricing plan: %1$s', 'jet-elements' ),
			esc_html( '<a href="https://www.weatherbit.io/terms" target="_blank">Terms and Conditions of Weatherbit</a>' )
		); ?>'">
	</div>

	<cx-vui-input
		name="weatherstack-api-key"
		label="<?php esc_attr_e( 'Weatherbit.io API Key', 'jet-elements' ); ?>"
		description="<?php
		echo sprintf( esc_html__( 'Create own Weatherbit.io API key, more info %1$s', 'jet-elements' ),
			esc_html( '<a href="https://www.weatherbit.io/" target="_blank">here</a>' )
		);?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions.weather_api_key.value"></cx-vui-input>

		<div
			class="cx-vui-subtitle"
			v-html="'<?php esc_html_e( 'OpenWeatherMap API (free & commercial plans)', 'jet-elements' ); ?>'">
		</div>

		<div class="cx-vui-component__desc"
			v-html="'<?php
				echo sprintf( esc_html__( 'If you plan to use the weather widget commercially, please choose the applicable pricing plan: %1$s', 'jet-elements' ),
					esc_html( '<a href="https://openweathermap.org/price" target="_blank">Pricing and Terms of OpenWeatherMap</a>' )
				); ?>'">
		</div>

		<cx-vui-input
			name="openweathermap-api-key"
			label="<?php esc_attr_e( 'OpenWeatherMap API Key', 'jet-elements' ); ?>"
			description="<?php
				echo sprintf( esc_html__( 'Create your own OpenWeatherMap API key, more info %1$s', 'jet-elements' ),
					esc_html( '<a href="https://home.openweathermap.org/users/sign_up" target="_blank">here</a>' )
				);?>"
			:wrapper-css="[ 'equalwidth' ]"
			size="fullwidth"
			v-model="pageOptions.openweathermap_api_key.value">
		</cx-vui-input>
</div>
