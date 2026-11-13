<?php
/**
 * Popup template
 */
$settings = $this->get_settings();

$popup_classes = array( 'jet-ajax-search__popup' );

$this->add_render_attribute( 'jet-ajax-search-popup', 'class', implode( ' ', $popup_classes ) );
$this->add_render_attribute( 'jet-ajax-search-popup', 'id', 'jet-ajax-search-popup-' . $this->get_id() );
$this->add_render_attribute( 'jet-ajax-search-popup', 'role', 'dialog' );
$this->add_render_attribute( 'jet-ajax-search-popup', 'aria-hidden', 'true' );

if ( ! empty( $settings['full_screen_popup'] ) ) {
	$popup_classes[] = 'jet-ajax-search__popup--full-screen';
}

if ( isset( $settings['popup_show_effect'] ) ) {
	$popup_classes[] = sprintf( 'jet-ajax-search__popup--%s-effect', $settings['popup_show_effect'] );
}

$this->add_render_attribute( 'jet-ajax-search-popup', 'class', implode( ' ', $popup_classes ) );
?>
<div <?php $this->print_render_attribute_string( 'jet-ajax-search-popup' ); ?>>
	<div class="jet-ajax-search__popup-content"><?php
		?><div class="jet-ajax-search__popup-form"><?php
			include $this->get_global_template( 'form' );
			include $this->get_global_template( 'popup-close' );
		?></div><?php

		include $this->get_global_template( 'results-area' );

		if ( ! empty( $settings['show_search_suggestions'] ) && ( isset( $settings['search_suggestions_position'] ) && 'under_form' === $settings['search_suggestions_position'] ) ) {
			include $this->get_global_template( 'inline-suggestions' );
		}
	?></div>
</div>
<?php include $this->get_global_template( 'popup-trigger' ); ?>
