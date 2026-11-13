<?php
/**
 * Weather template
 */
$settings = $this->get_settings_for_display();
$background_media = $this->get_weather_background_media_markup();
$weather_classes  = 'jet-weather';

if ( ! empty( $background_media ) ) {
	$weather_classes .= ' jet-weather--has-media';
}
?>
<div class="<?php echo esc_attr( $weather_classes ); ?>">
	<?php echo $background_media; // phpcs:ignore ?>
	<div class="jet-weather__container"><?php
		echo $this->get_weather_title(); // phpcs:ignore

		if ( isset( $settings['show_current_weather'] ) && 'true' === $settings['show_current_weather'] ) {
			include $this->_get_global_template( 'current' );
		}

		if ( isset( $settings['show_forecast_weather'] ) && 'true' === $settings['show_forecast_weather'] ) {
			include $this->_get_global_template( 'forecast' );
		}
	?></div>
</div>
