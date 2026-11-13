<?php
/**
 * Brands loop start template
 */
$is_render_context = 'render' === $this->_context;
$dir               = is_rtl() ? 'rtl' : 'ltr';

if ( ! $is_render_context ) {
	?>
	<# 
	var displayType = settings.display_type || 'grid',
		isSlider = 'slider' === displayType,
		prevIconClass = settings.selected_prev_arrow && settings.selected_prev_arrow.value ? settings.selected_prev_arrow.value : settings.prev_arrow,
		nextIconClass = settings.selected_next_arrow && settings.selected_next_arrow.value ? settings.selected_next_arrow.value : settings.next_arrow,
		sliderOptions = {
			autoplaySpeed: settings.autoplay_speed ? parseInt( settings.autoplay_speed, 10 ) : 5000,
			autoplay: 'true' === settings.autoplay,
			infinite: '' === settings.infinite || 'true' === settings.infinite,
			arrows: 'true' === settings.arrows,
			dots: 'true' === settings.dots,
			slidesToScroll: settings.slides_to_scroll ? parseInt( settings.slides_to_scroll, 10 ) : 1,
			prevArrow: '.jet-brands__prev-arrow-<?php echo esc_js( sanitize_key( $this->get_id() ) ); ?>',
			nextArrow: '.jet-brands__next-arrow-<?php echo esc_js( sanitize_key( $this->get_id() ) ); ?>',
			rtl: <?php echo is_rtl() ? 'true' : 'false'; ?>
		};
	#>
	<div class="brands-list brands-list--{{ displayType }} <# if ( isSlider ) { #>elementor-slick-slider<# } else { #>col-row<# } #>" <# if ( isSlider ) { #>data-slider_options="{{{ _.escape( JSON.stringify( sliderOptions ) ) }}}"<# } #> dir="<?php echo esc_attr( $dir ); ?>">
		<# if ( isSlider && 'true' === settings.arrows ) { #>
			<div class="jet-brands__prev-arrow-<?php echo esc_attr( sanitize_key( $this->get_id() ) ); ?> jet-arrow prev-arrow">
				<# if ( prevIconClass ) { #><i class="{{ prevIconClass }}" aria-hidden="true"></i><# } #>
			</div>
			<div class="jet-brands__next-arrow-<?php echo esc_attr( sanitize_key( $this->get_id() ) ); ?> jet-arrow next-arrow">
				<# if ( nextIconClass ) { #><i class="{{ nextIconClass }}" aria-hidden="true"></i><# } #>
			</div>
		<# } #>
	<?php
	return;
}

$settings         = $this->get_settings_for_display();
$display_type     = ! empty( $settings['display_type'] ) ? $settings['display_type'] : 'grid';
$is_slider_layout = 'slider' === $display_type;
$class_list       = array( 'brands-list', 'brands-list--' . $display_type );
$data_settings    = $is_slider_layout ? $this->generate_slider_settings_json() : '';

if ( $is_slider_layout ) {
	$class_list[] = 'elementor-slick-slider';
} else {
	$class_list[] = 'col-row';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $class_list ) ); ?>"<?php echo $data_settings; // phpcs:ignore ?> dir="<?php echo esc_attr( $dir ); ?>">
	<?php if ( $is_slider_layout && ! empty( $settings['arrows'] ) && filter_var( $settings['arrows'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
		<?php
		printf(
			'<div class="jet-brands__prev-arrow-%1$s jet-arrow prev-arrow">%2$s</div><div class="jet-brands__next-arrow-%1$s jet-arrow next-arrow">%3$s</div>',
			esc_attr( sanitize_key( $this->get_id() ) ),
			$this->_render_icon( 'prev_arrow', '%s', '', false ), // phpcs:ignore
			$this->_render_icon( 'next_arrow', '%s', '', false ) // phpcs:ignore
		);
		?>
	<?php endif; ?>
