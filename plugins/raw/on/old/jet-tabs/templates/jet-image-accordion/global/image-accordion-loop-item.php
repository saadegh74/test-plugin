<?php
/**
 * Image accordion list item template
 */
$settings = $this->get_settings_for_display();

$item = ! empty( $this->__processed_item ) && is_array( $this->__processed_item )
	? $this->__processed_item
	: array();

$item_key = 'jet_image_accordion_item_' . ( ! empty( $item['_id'] ) ? $item['_id'] : uniqid() );

$item_html_tag = ! empty( $settings['item_html_tag'] ) ? $settings['item_html_tag'] : 'h5';
$item_html_tag = $this->validate_html_tag( $item_html_tag );

$allowed_title_display_options = [
    'on_hover',
    'on_default_state',
    'on_both_states'
];
$title_display = ! empty( $settings['title_display'] ) ? $settings['title_display'] : 'on_hover';
$title_display = in_array( $title_display, $allowed_title_display_options ) ? $title_display : 'on_hover';

$show_default_title = ( $title_display === 'on_default_state' || $title_display === 'on_both_states' );
$show_hover_title   = ( $title_display === 'on_hover' || $title_display === 'on_both_states' );

$this->add_render_attribute( $item_key, 'class', array(
	'jet-image-accordion__item',
	'title-display-mode__' . $title_display,
) );

$this->add_render_attribute( $item_key, array(
	'role'          => 'tab',
	'aria-selected' => 'false',
	'tabindex'      => '0',
) );

if ( ! empty( $item['_id'] ) ) {
	$this->add_render_attribute( $item_key, 'class', 'elementor-repeater-item-' . $item['_id'] );
}

if ( ! empty( $item['control_id'] ) ) {
	$this->add_render_attribute( $item_key, 'id', esc_attr( ltrim( trim( $item['control_id'] ), '#' ) ) );
}
?>
<div <?php echo $this->get_render_attribute_string( $item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php echo wp_kses_post( $this->__loop_item_image() ); ?>

	<?php if ( $show_default_title ) : ?>
		<?php echo wp_kses_post( $this->__loop_item( array( 'item_title' ), '<' . $item_html_tag . ' class="jet-image-accordion__title-default">%s</' . $item_html_tag . '>' ) ); ?>
	<?php endif; ?>

	<div class="jet-image-accordion__content">
		<?php
		if ( $show_hover_title ) :
		    echo wp_kses_post( $this->__loop_item( array( 'item_title' ), '<' . $item_html_tag . ' class="jet-image-accordion__title">%s</' . $item_html_tag . '>' ) );
		endif;

		echo wp_kses_post( $this->__loop_item( array( 'item_desc' ), '<div class="jet-image-accordion__desc">%s</div>' ) );
		echo $this->__generate_action_button(); // phpcs:ignore ?></div>
	<div class="jet-image-accordion__item-loader"><span></span></div>
</div>
