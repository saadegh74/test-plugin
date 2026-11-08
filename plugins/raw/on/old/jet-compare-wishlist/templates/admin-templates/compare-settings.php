<?php
/**
 * Compare settings dashboard template.
 *
 * @since 1.2.2
 * @since 1.5.2 Template updated. `compare_message_max_items` control added.
 */
?>
<div id="jet-cw-settings-page jet-cw-settings-page__compare">
	<cx-vui-switcher
		name="enable_compare"
		label="<?php _e( 'Enable Compare', 'jet-cw' ); ?>"
		description="<?php _e( 'Enable compare functionality.', 'jet-cw' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="true"
		return-false="false"
		v-model="pageOptions.enable_compare.value"
	></cx-vui-switcher>

	<cx-vui-select
		v-if="pageOptions.enable_compare.value === 'true'"
		name="compare_store_type"
		label="<?php _e( 'Store type', 'jet-cw' ); ?>"
		description="<?php _e( 'Select store type for compare.', 'jet-cw' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:options-list="pageOptions.compare_store_type.options"
		v-model="pageOptions.compare_store_type.value"
	></cx-vui-select>

	<cx-vui-switcher
		v-if="pageOptions.enable_compare.value === 'true'"
		name="save_user_compare_list"
		label="<?php _e( 'Save the list for logged users', 'jet-cw' ); ?>"
		description="<?php _e( 'Enable this option if you want save compare list for logged users.', 'jet-cw' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="true"
		return-false="false"
		v-model="pageOptions.save_user_compare_list.value"
	></cx-vui-switcher>

	<cx-vui-select
		v-if="pageOptions.enable_compare.value === 'true'"
		name="compare_page"
		label="<?php _e( 'Compare Page', 'jet-cw' ); ?>"
		description="<?php _e( 'Choose compare page.', 'jet-cw' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:options-list="pageOptions.compare_page.options"
		v-model="pageOptions.compare_page.value"
	></cx-vui-select>

	<cx-vui-select
		v-if="pageOptions.enable_compare.value === 'true'"
		name="compare_page_max_items"
		label="<?php _e( 'Count products to compare', 'jet-cw' ); ?>"
		description="<?php _e( 'Count products to show in compare widget.', 'jet-cw' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		:options-list="pageOptions.compare_page_max_items.options"
		v-model="pageOptions.compare_page_max_items.value"
	></cx-vui-select>

	<cx-vui-input
		v-if="pageOptions.enable_compare.value === 'true'"
		name="compare_message_max_items"
		label="<?php _e( 'Compare Message', 'jet-engine' ); ?>"
		description="<?php _e( 'Message that will show when you reach max products count in compare list.', 'jet-engine' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="pageOptions.compare_message_max_items.value"
	></cx-vui-input>

	<cx-vui-switcher
		v-if="pageOptions.enable_compare.value === 'true'"
		name="add_default_compare_button"
		label="<?php _e( 'Add default Compare Button', 'jet-cw' ); ?>"
		description="<?php _e( 'Add compare button to default WooCommerce templates.', 'jet-cw' ); ?>"
		:wrapper-css="[ 'equalwidth' ]"
		return-true="true"
		return-false="false"
		v-model="pageOptions.add_default_compare_button.value"
	></cx-vui-switcher>
</div>
