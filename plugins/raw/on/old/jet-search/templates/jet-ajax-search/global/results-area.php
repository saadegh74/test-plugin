<?php
/**
 * Result Area template
 */

$show = '';
$hide = false;

$bricks_listing_class = isset( $is_bricks_listing ) && true === $is_bricks_listing ? 'brxe-jet-listing' : '';

$custom_area_styles   = '';
$column_classes       = array();
$column_blocks_styles = '';

$columns = array(
	'cols_desk'            => isset( $settings['results_area_columns'] ) ? absint( $settings['results_area_columns'] ) : 1,
	'cols_tablet'          => isset( $settings['results_area_columns_tablet'] ) ? absint( $settings['results_area_columns_tablet'] ) : 1,
	'cols_mobile'          => isset( $settings['results_area_columns_mobile'] ) ? absint( $settings['results_area_columns_mobile'] ) : 1,
	'cols_mobile_portrait' => isset( $settings['results_area_columns_mobile_portrait'] ) ? absint( $settings['results_area_columns_mobile_portrait'] ) : 1,
);
$column_classes = array_merge( $column_classes, array(
	'results-area-col-desk-' . $columns['cols_desk'],
	'results-area-col-tablet-' . $columns['cols_tablet'],
	'results-area-col-mobile-' . $columns['cols_mobile'],
	'results-area-col-mobile-portrait-' . $columns['cols_mobile_portrait'],
) );

$column_classes = esc_attr( implode( ' ', $column_classes ) );

if ( $this->preview_results() || $this->preview_focus_suggestions() ) {
	$show = ' show';

	if ( 'custom' === $settings['results_area_width_by'] && isset( $settings['results_area_custom_width'] ) ) {
		$custom_area_width = absint( $settings['results_area_custom_width'] );

		if ( $custom_area_width > 0 ) {
			$custom_area_width_style = 'width: ' . $custom_area_width . 'px;';
		}
	}

	if ( 'custom' === $settings['results_area_width_by'] && isset( $settings['results_area_custom_position'] ) ) {

		$result_area_custom_width_position = $settings['results_area_custom_position'];
		$custom_area_width_position_style  = '';

		switch( $result_area_custom_width_position ) {
			case 'left':
				$custom_area_width_position_style = 'left: 0; right: auto';
				break;
			case 'center':
				$custom_area_width_position_style = 'left: 50%; right: auto; -webkit-transform:translateX(-55%); transform:translateX(-50%);';
				break;
			case 'right':
				$custom_area_width_position_style = 'left: auto; right: 0';
				break;
		}
	}

	if ( $this->preview_focus_suggestions() ) {
		$hide = true;
	}

	$results_area_columns = isset( $settings['results_area_columns'] ) ? absint( $settings['results_area_columns'] ) : 1;

	$custom_area_styles .= 'style="' . $custom_area_width_style . $custom_area_width_position_style . '"';
	$column_blocks_styles ='style="--columns:' . $results_area_columns . '"';
}

?>

<div class="jet-ajax-search__results-area<?php echo esc_attr( $show ); ?>" <?php echo $custom_area_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="jet-ajax-search__results-holder<?php echo esc_attr( $show ); ?>">
		<?php if ( false === $hide ): ?>
			<div class="jet-ajax-search__results-header">
				<?php $this->glob_inc_if( 'results-count', array( 'show_results_counter' ) ); ?>
				<div class="jet-ajax-search__navigation-holder"><?php
					$this->preview_navigation_template( 'top' );
				?></div>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $settings['show_search_suggestions'] ) && ( isset( $settings['search_suggestions_position'] ) && 'inside_results_area' ===  $settings['search_suggestions_position'] ) ): ?>
			<div class="jet-ajax-search__results-suggestions-area">
				<?php if ( ! empty( $settings['search_suggestions_title'] ) ): ?>
                    <div class="jet-ajax-search__results-suggestions-area-title"><?php echo esc_html( $settings['search_suggestions_title'] ); ?></div>
				<?php endif; ?>
				<?php
					$this->preview_results_suggestions_template();
				?>
			</div>
		<?php endif; ?>
		<div class="jet-ajax-search__results-list <?php echo $column_classes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" <?php echo $column_blocks_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php $this->preview_source_template( 'before' ); ?>
            <div class="jet-ajax-search__results-list-inner <?php echo esc_attr( $bricks_listing_class ); ?>"><?php
				$this->preview_template();
			?></div>
			<?php $this->preview_source_template( 'after' ); ?>
		</div>
		<?php if ( false === $hide ): ?>
			<div class="jet-ajax-search__results-footer">
				<?php if ( ! empty( $settings['show_full_results'] ) ): ?>
					<?php $this->html( 'full_results_btn_text', '<button class="jet-ajax-search__full-results">%s</button>' ); ?>
				<?php endif; ?>
				<div class="jet-ajax-search__navigation-holder"><?php
					$this->preview_navigation_template( 'bottom' );
				?></div>
			</div>
		<?php endif; ?>
	</div>
	<div class="jet-ajax-search__message"></div>
	<?php
		if ( ! $show ) {
			include $this->get_global_template( 'spinner' );
		}
	?>
</div>
