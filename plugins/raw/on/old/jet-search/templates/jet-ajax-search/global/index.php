<?php
/**
 * Main template
 *
 * @var $this Elementor\Jet_Search_Ajax_Search_Widget
 */

$settings = $this->get_settings_for_display();

$this->add_render_attribute( 'wrapper', array(
	'class'         => 'jet-ajax-search',
	'data-settings' => $this->get_settings_json(),
) );

if ( isset( $settings['search_form_responsive_on_mobile'] ) && filter_var( $settings['search_form_responsive_on_mobile'], FILTER_VALIDATE_BOOLEAN ) ) {
	$this->add_render_attribute( 'wrapper', 'class', 'jet-ajax-search jet-ajax-search--mobile-skin' );
}

$listing_id                   = isset( $settings['listing_id'] ) ? $settings['listing_id'] : '';
$hidden_listing_template_html = '';
?>

<?php if ( class_exists( 'Jet_Engine' ) ) : ?>
	<div class="jet_search_listing_grid_hidden_template" style="display: none;">
		<?php

			if ( ! empty( $listing_id ) ) {
				$is_bricks_listing            = jet_engine()->listings->data->get_listing_type( $listing_id ) === 'bricks';
				$hidden_listing_template_html = Jet_Search_Tools::get_listing_grid( $listing_id );

				echo $hidden_listing_template_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( class_exists( 'Jet_Search\Search_Sources\Manager' ) ) {
				$sources_manager = jet_search()->search_sources;
				$sources         = $sources_manager->get_sources();

				foreach ( $sources as $key => $source ) {
					if ( ! isset( $settings['search_source_' . $key] ) ) {
						continue;
					}

					if ( filter_var( $settings['search_source_' . $key], FILTER_VALIDATE_BOOLEAN ) ) {
						$listing_id                   = isset( $settings['search_source_' . $key . '_listing_id'] ) ? $settings['search_source_' . $key . '_listing_id'] : '';
						$source_listing_template_html = '';

						if ( ! empty( $listing_id ) ) {
							$source_listing_template_html = Jet_Search_Tools::get_listing_grid( $listing_id );

							echo $source_listing_template_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}
				}
			}
		?>
	</div>
<?php endif;?>

<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>><?php
	if ( ! empty( $settings['show_search_in_popup'] ) ) {
		include $this->get_global_template( 'popup' );
	} else {
		include $this->get_global_template( 'form' );
		include $this->get_global_template( 'results-area' );

		if ( ! empty( $settings['show_search_suggestions'] ) && ( isset( $settings['search_suggestions_position'] ) && 'under_form' ===  $settings['search_suggestions_position'] ) ) {
			include $this->get_global_template( 'inline-suggestions' );
		}
	}
?></div>
