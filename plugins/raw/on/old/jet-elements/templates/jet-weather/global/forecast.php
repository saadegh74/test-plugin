<?php
/**
 * Forecast weather template.
 */

$settings      = $this->get_settings_for_display();
$data          = $this->weather_data ?? [];
$forecast_data = $data['forecast'] ?? [];

$show_current = isset($settings['show_current_weather']) && 'true' === $settings['show_current_weather'];
$start_index  = $show_current ? 1 : 0;
$forecast_days_count = ! empty( $settings['forecast_count']['size'] ) 
    ? abs( $settings['forecast_count']['size'] ) 
    : count($forecast_data);

?>
<div class="jet-weather__forecast">
<?php
// phpcs:disable
for ( $i = $start_index; $i < $start_index + $forecast_days_count; $i ++ ) {
    if ( empty( $forecast_data[ $i ] ) ) {
        continue;
    }

    $day_data = $forecast_data[ $i ];
    $date     = $day_data['date'] ?? '';
    $code     = $day_data['code'] ?? 0;
    $temp_min = $day_data['temp_min'] ?? $day_data['temp'] ?? '';
    $temp_max = $day_data['temp_max'] ?? $day_data['temp'] ?? '';
    ?>
    <div class="jet-weather__forecast-item">
        <div class="jet-weather__forecast-day"><?php echo $this->get_week_day_from_date( $date ); ?></div>
        <div class="jet-weather__forecast-icon" title="<?php echo esc_attr( $this->get_weather_desc( $code ) ); ?>">
            <?php echo $this->get_weather_svg_icon( $code, true ); ?>
        </div>
        <div class="jet-weather__forecast-max-temp"><?php echo $this->get_weather_temp( $temp_max ); ?></div>
        <div class="jet-weather__forecast-min-temp"><?php echo $this->get_weather_temp( $temp_min ); ?></div>
    </div>
<?php
}
// phpcs:enable
?>
</div>