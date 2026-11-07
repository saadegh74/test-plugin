<?php
/**
 * PP Presets Style.
 */

namespace PowerpackElements\Modules\PresetsStyle\Controls;

use Elementor\Base_Data_Control;
use PowerpackElements\Modules\PresetsStyle\Module;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Presets_Style.
 */
class Presets_Style extends Base_Data_Control {

	const CONTROL_ID = 'pp-presets-style';

	/**
	 * Get Control Type.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return self::CONTROL_ID;
	}

	/**
	 * Get Default Settings.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @return array Settings.
	 */
	protected function get_default_settings() {
		return array(
			'label_block' => false,
			'multiple'    => false,
			'options'     => [],
		);
	}

	/**
	 * Enqueue control scripts and styles.
	 *
	 * @since 2.9.0
	 * @access public
	 */
	public function enqueue() {
		wp_register_script( 'pp-presets-style', POWERPACK_ELEMENTS_URL . 'assets/js/pp-presets-style.js', [ 'jquery' ], '1.0.0' );
		wp_enqueue_script( 'pp-presets-style' );

		wp_localize_script(
			'pp-presets-style',
			'pp_presets',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'pp-presets-nonce' ),
			)
		);
	}

	/**
	 * Control content template.
	 *
	 * @since 2.9.0
	 * @access public
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<# if ( data.label ) {#>
				<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper elementor-control-unit-5">
				<select id="<?php echo esc_attr( $control_uid ); ?>" data-setting="{{ data.name }}">
				<#
					var printOptions = function( options ) {
						_.each( options, function( option_title, option_value ) { #>
								<option value="{{ option_value }}">{{{ option_title }}}</option>
						<# } );
					};

					if ( data.groups ) {
						for ( var groupIndex in data.groups ) {
							var groupArgs = data.groups[ groupIndex ];
								if ( groupArgs.options ) { #>
									<optgroup label="{{ groupArgs.label }}">
										<# printOptions( groupArgs.options ) #>
									</optgroup>
								<# } else if ( _.isString( groupArgs ) ) { #>
									<option value="{{ groupIndex }}">{{{ groupArgs }}}</option>
								<# }
						}
					} else {
						printOptions( data.options );
					}
				#>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
