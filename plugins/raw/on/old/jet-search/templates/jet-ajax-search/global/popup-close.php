<?php
/**
 * Popup close template
 */
$settings = $this->get_settings();

$close_label = esc_attr__( 'Close search', 'jet-search' );

$this->icon(
	'search_close_icon',
	sprintf(
		'<button type="button" class="jet-ajax-search__popup-close" aria-label="%1$s"><span class="jet-ajax-search__popup-close-icon jet-ajax-search-icon">%%s</span></button>',
		$close_label
	)
);
