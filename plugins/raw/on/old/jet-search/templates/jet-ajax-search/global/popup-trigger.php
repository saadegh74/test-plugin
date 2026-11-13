<?php
/**
 * Popup trigger template
 */
$settings = $this->get_settings();

$popup_trigger_title = ! empty( $settings['search_placeholder_text'] )
	? esc_attr( $settings['search_placeholder_text'] )
	: esc_attr__( 'Open search', 'jet-search' );
?>
<div class="jet-ajax-search__popup-trigger-container">
	<button
		type="button"
		class="jet-ajax-search__popup-trigger"
		title="<?php echo esc_attr( $popup_trigger_title ); ?>"
	aria-label="<?php echo esc_attr( $popup_trigger_title ); ?>"
		aria-controls="<?php echo esc_attr( 'jet-ajax-search-popup-' . $this->get_id() ); ?>"
		aria-expanded="false"
	><?php
		$this->icon( 'search_popup_trigger_icon', '<span class="jet-ajax-search__popup-trigger-icon jet-ajax-search-icon">%s</span>' );
	?></button>
</div>
