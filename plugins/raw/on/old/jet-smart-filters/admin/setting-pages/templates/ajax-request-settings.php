<div
	class="jet-smart-filters-settings-page jet-smart-filters-settings-page__ajax-request-type"
>
	<div class="ajax-request-types">
		<div class="ajax-request-types__header">
			<div class="cx-vui-title"><?php esc_html_e( 'Ajax Request Type', 'jet-smart-filters' ); ?></div>
			<div class="cx-vui-subtitle"><?php esc_html_e( 'This option allows you to set global WordPress variables and variables from the URL in the same way as on the page from which the request was sent. Use this option if the macros or dynamic tags in your query settings don’t work properly.', 'jet-smart-filters' ); ?></div>
			<cx-vui-radio
				name="ajax-request-types"
				v-model="settings.ajax_request_types"
				:optionsList="data.ajax_request_types_options"
			>
			</cx-vui-radio>
		</div>
	</div>
	<div class="ajax-request-signature">
		<cx-vui-switcher
			name="use_signature_verification"
			label="<?php esc_attr_e( 'Add signatures to filters requests', 'jet-smart-filters' ); ?>"
			description="<?php esc_attr_e( 'Enabled by default. Disabling signatures reduces protection for public filter requests and should only be used for compatibility troubleshooting. Purge page and CDN caches after enabling signatures again.', 'jet-smart-filters' ); ?>"
			:wrapper-css="[ 'equalwidth' ]"
			return-true="true"
			return-false="false"
			v-model="settings.use_signature_verification"
		>
		</cx-vui-switcher>
	</div>
</div>
