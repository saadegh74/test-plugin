<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname( __DIR__ ) . '/class-jet-elements-settings-validator.php';
if ( class_exists( '\\Elementor\\Repeater' ) ) { require_once __DIR__ . '/class-jet-elements-repeater.php'; }

abstract class Jet_Elements_Base extends Widget_Base {

	public $_context          = 'render';
	public $_processed_item   = false;
	public $_processed_index  = 0;
	public $_load_level       = 100;
	public $_include_controls = [];
	public $_exclude_controls = [];
	public $_new_icon_prefix  = 'selected_';

	private $normalized_display_settings = null;
	private $display_settings_source = null;
	private $display_settings_controls = null;

	/**
	 * [__construct description]
	 * @param array  $data [description]
	 * @param [type] $args [description]
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		$this->_load_level = (int)jet_elements_settings()->get( 'widgets_load_level', 100 );

		$widget_name = $this->get_name();

		$this->_include_controls = apply_filters( "jet-elements/editor/{$widget_name}/include-controls", [], $widget_name, $this );

		$this->_exclude_controls = apply_filters( "jet-elements/editor/{$widget_name}/exclude-controls", [], $widget_name, $this );
	}

	/** Validate vendor options and serialize values for the shortcode grammar. */
	protected function get_vendor_shortcode_attributes( $settings, $aliases = array() ) {
		$attributes = '';

		foreach ( $this->_atts() as $name => $control ) {
			$default = isset( $control['default'] ) ? $control['default'] : '';
			$value = isset( $settings[ $name ] ) ? $settings[ $name ] : $default;
			$type = isset( $control['type'] ) ? $control['type'] : '';

			if ( isset( $control['options'] ) ) {
				$options = array_map( 'strval', array_keys( $control['options'] ) );
				if ( ! empty( $control['multiple'] ) ) {
					$values = is_array( $value ) ? $value : array();
					$value = array();
					foreach ( $values as $choice ) {
						if ( ( is_string( $choice ) || is_int( $choice ) ) && in_array( (string) $choice, $options, true ) ) {
							$value[] = (string) $choice;
						}
					}
					$value = implode( ',', $value );
				} elseif ( ! ( is_string( $value ) || is_int( $value ) ) || ! in_array( (string) $value, $options, true ) ) {
					if ( ! in_array( (string) $default, $options, true ) ) {
						continue;
					}
					$value = $default;
				}
			} elseif ( 'switcher' === $type ) {
				$return_value = isset( $control['return_value'] ) ? $control['return_value'] : 'yes';
				$value = $return_value === $value ? $return_value : '';
			} elseif ( 'number' === $type || in_array( $name, array( 'calendar', 'year', 'month', 'product_id', 'row_height', 'font_size' ), true ) ) {
				// IDs/counts are integers. Timetable dimensions can also be decimal.
				$pattern = in_array( $name, array( 'row_height', 'font_size' ), true ) ? '/^\d+(?:\.\d+)?$/' : '/^-?\d+$/';
				$valid = ( is_string( $value ) || is_int( $value ) || is_float( $value ) ) && preg_match( $pattern, (string) $value );
				$minimum = 'per_page' === $name ? -1 : 1;
				$maximum = 'month' === $name ? 12 : ( 'year' === $name ? 9999 : PHP_INT_MAX );
				if ( ! $valid || $value < $minimum || $value > $maximum || ( 'per_page' === $name && 0 == $value ) ) {
					$value = $default;
				}
			} else {
				$value = is_string( $value ) || is_numeric( $value ) ? sanitize_text_field( $value ) : '';
			}

			// HTML escaping alone does not protect bracket/backslash delimiters.
			$value = str_replace(
				array( '"', '[', ']', '\\', '<', '>' ),
				array( '&quot;', '&#91;', '&#93;', '&#92;', '&lt;', '&gt;' ),
				(string) $value
			);
			$attribute = isset( $aliases[ $name ] ) ? $aliases[ $name ] : $name;
			$attributes .= sprintf( ' %1$s="%2$s"', $attribute, $value );
		}

		return $attributes;
	}

	public function add_control( $control_id, array $args, $options = array() ) {
		\Jet_Elements_Settings_Validator::remember( $this->get_name(), $control_id, $args );
		return parent::add_control( $control_id, $args, $options );
	}

	public function update_control( $control_id, array $args, array $options = array() ) {
		\Jet_Elements_Settings_Validator::remember( $this->get_name(), $control_id, $args );
		return parent::update_control( $control_id, $args, $options );
	}

	public function get_controls( $control_id = null ) {
		$controls = parent::get_controls( $control_id );
		if ( null === $control_id ) { return \Jet_Elements_Settings_Validator::schema( $this->get_name(), $controls ); }
		return $controls ? \Jet_Elements_Settings_Validator::schema( $this->get_name(), array( $control_id => $controls ) )[ $control_id ] : $controls;
	}

	public function get_data( $item = null ) {
		$data = parent::get_data( $item );
		if ( 'settings' === $item ) { return \Jet_Elements_Settings_Validator::normalize( $data, \Jet_Elements_Settings_Validator::controls( $this ) ); }
		if ( null === $item && isset( $data['settings'] ) ) {
			$data['settings'] = \Jet_Elements_Settings_Validator::normalize( $data['settings'], \Jet_Elements_Settings_Validator::controls( $this ) );
		}
		return $data;
	}

	protected function get_init_settings() {
		return \Jet_Elements_Settings_Validator::normalize( parent::get_init_settings(), \Jet_Elements_Settings_Validator::controls( $this ) );
	}

	public function get_settings_for_display( $setting_key = null ) {
		$source = parent::get_settings_for_display();
		$controls = \Jet_Elements_Settings_Validator::controls( $this );

		// Elementor retains the source array between reads. PHP's copy-on-write
		// keeps this comparison cheap while detecting rebuilt settings or schemas.
		if ( null === $this->normalized_display_settings || $source !== $this->display_settings_source || $controls !== $this->display_settings_controls ) {
			$this->normalized_display_settings = \Jet_Elements_Settings_Validator::normalize( $source, $controls );
			$this->display_settings_source = $source;
			$this->display_settings_controls = $controls;
		}

		$settings = $this->normalized_display_settings;
		return null === $setting_key ? $settings : ( isset( $settings[ $setting_key ] ) ? $settings[ $setting_key ] : null );
	}

	public function parse_dynamic_settings( $settings, $controls = null, $all_settings = null ) {
		$controls = null === $controls ? \Jet_Elements_Settings_Validator::controls( $this ) : \Jet_Elements_Settings_Validator::schema( $this->get_name(), $controls );
		$settings = \Jet_Elements_Settings_Validator::normalize( $settings, $controls );
		return \Jet_Elements_Settings_Validator::normalize( parent::parse_dynamic_settings( $settings, $controls, $all_settings ), $controls );
	}

	public function get_raw_data( $with_html_content = false ) {
		$data = parent::get_raw_data( $with_html_content );
		$data['settings'] = \Jet_Elements_Settings_Validator::normalize( $data['settings'], \Jet_Elements_Settings_Validator::controls( $this ) );
		return $data;
	}

	/**
	 * Ensure value is in allowed list.
	 *
	 * @param mixed $current Current value.
	 * @param array $allowed Allowed values.
	 * @return mixed First element of allowed array if current value is not in the list.
	 */
	public function ensure_allowed_value( $current, $allowed ) {
		return in_array( $current, $allowed, true ) ? $current : $allowed[0];
	}

	/**
	 * [get_jet_help_url description]
	 * @return [type] [description]
	 */
	public function get_jet_help_url() {
		return false;
	}

	/**
	 * [get_help_url description]
	 * @return [type] [description]
	 */
	public function get_help_url() {

		$url = $this->get_jet_help_url();

		$style_parent_theme = wp_get_theme( get_template() );

		$author_slug = strtolower( preg_replace('/\s+/', '', $style_parent_theme->get('Author') ) );

		if ( ! empty( $url ) ) {
			return add_query_arg(
				array(
					'utm_source'   => $author_slug,
					'utm_medium'   => 'jetelements' . '_' . $this->get_name(),
					'utm_campaign' => 'need-help',
				),
				esc_url( $url )
			);
		}

		return false;
	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __get_global_template( $name = null ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_get_global_template()' );

		return $this->_get_global_template( $name );
	}

	/**
	 * Get globaly affected template
	 *
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function _get_global_template( $name = null ) {

		$template = call_user_func( array( $this, sprintf( '_get_%s_template', $this->_context ) ), $name );

		if ( ! $template ) {
			$template = jet_elements()->get_template( $this->get_name() . '/global/' . $name . '.php' );
		}

		return $template;
	}

	/**
	 * Get front-end template
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function _get_render_template( $name = null ) {
		return jet_elements()->get_template( $this->get_name() . '/render/' . $name . '.php' );
	}

	/**
	 * Get editor template
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function _get_edit_template( $name = null ) {
		return jet_elements()->get_template( $this->get_name() . '/edit/' . $name . '.php' );
	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __get_global_looped_template( $name = null, $setting = null, $callback = null ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_get_global_looped_template()' );

		$this->_get_global_looped_template( $name, $setting, $callback );
	}

	/**
	 * Get global looped template for settings
	 * Required only to process repeater settings.
	 *
	 * @param  string $name     Base template name.
	 * @param  string $setting  Repeater setting that provide data for template.
	 * @param  string $callback Callback for preparing a loop array
	 * @return void
	 */
	public function _get_global_looped_template( $name = null, $setting = null, $callback = null ) {

		$templates = array(
			'start' => $this->_get_global_template( $name . '-loop-start' ),
			'loop'  => $this->_get_global_template( $name . '-loop-item' ),
			'end'   => $this->_get_global_template( $name . '-loop-end' ),
		);

		call_user_func(
			array( $this, sprintf( '_get_%s_looped_template', $this->_context ) ), $templates, $setting, $callback
		);

	}

	/**
	 * Get render mode looped template
	 *
	 * @param  array  $templates [description]
	 * @param  string $setting   [description]
	 * @param  string $callback  Callback for preparing a loop array
	 * @return void
	 */
	public function _get_render_looped_template( $templates = array(), $setting = null, $callback = null ) {

		$loop = $this->get_settings_for_display( $setting );
		$loop = apply_filters( 'jet-elements/widget/loop-items', $loop, $setting, $this );

		if ( empty( $loop ) ) {
			return;
		}

		if ( $callback && is_callable( $callback ) ) {
			$loop = call_user_func( $callback, $loop );
		}

		if ( ! empty( $templates['start'] ) ) {
			include $templates['start'];
		}

		do_action( 'jet-engine-query-gateway/before-loop', $setting, $this );

		foreach ( $loop as $item ) {

			do_action( 'jet-engine-query-gateway/do-item', $item );

			$this->_processed_item = $item;

			if ( ! empty( $templates['loop'] ) ) {
				add_filter( 'elementor/document/wrapper_attributes', array( $this, 'modify_template_attributes' ), 10, 2 );
				include $templates['loop'];
				add_filter( 'elementor/document/wrapper_attributes', array( $this, 'modify_template_attributes' ), 10, 2 );
			}
			$this->_processed_index++;
		}

		do_action( 'jet-engine-query-gateway/reset-item' );

		$this->_processed_item = false;
		$this->_processed_index = 0;

		if ( ! empty( $templates['end'] ) ) {
			include $templates['end'];
		}

	}

	public function modify_template_attributes( $attributes, $document ) {
		if ( empty( $this->_processed_item['template_id'] ) || ( int ) $document->get_main_id() !== ( int ) $this->_processed_item['template_id'] ) {
			return $attributes;
		}

		$attributes['data-unique-item-id'] = sprintf( '%s-%s-%s', $this->get_id(), $document->get_main_id(), $this->_processed_index );
		$this->_processed_item['unique_item_id'] = $attributes['data-unique-item-id'];
		return $attributes;
	}
	
	/**
	 * Get edit mode looped template
	 *
	 * @param  array  $templates [description]
	 * @param  [type] $setting   [description]
	 * @return [type]            [description]
	 */
	public function _get_edit_looped_template( $templates = array(), $setting = null ) {
		?>
		<# if ( settings.<?php echo $setting; // phpcs:ignore ?> ) { #>
		<?php
			if ( ! empty( $templates['start'] ) ) {
				include $templates['start'];
			}
		?>
			<# _.each( settings.<?php echo $setting; // phpcs:ignore ?>, function( item ) { #>
			<?php
				if ( ! empty( $templates['loop'] ) ) {
					include $templates['loop'];
				}
			?>
			<# } ); #>
		<?php
			if ( ! empty( $templates['end'] ) ) {
				include $templates['end'];
			}
		?>
		<# } #>
		<?php
	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __loop_item( $keys = array(), $format = '%s' ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_loop_item()' );

		return $this->_loop_item( $keys, $format );
	}

	/**
	 * Get current looped item dependends from context.
	 *
	 * @param  string $key Key to get from processed item
	 * @return mixed
	 */
	public function _loop_item( $keys = array(), $format = '%s' ) {

		return call_user_func( array( $this, sprintf( '_%s_loop_item', $this->_context ) ), $keys, $format );

	}

	/**
	 * Loop edit item
	 *
	 * @param  [type]  $keys       [description]
	 * @param  string  $format     [description]
	 * @param  boolean $nested_key [description]
	 * @return [type]              [description]
	 */
	public function _edit_loop_item( $keys = array(), $format = '%s' ) {

		$settings = $keys[0];

		if ( isset( $keys[1] ) ) {
			$settings .= '.' . $keys[1];
		}

		ob_start();

		echo '<# if ( item.' . $settings . ' ) { #>'; // phpcs:ignore
		printf( $format, $this->get_template_render_value( 'item.' . $settings, $format ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<# } #>';

		return ob_get_clean();
	}

	/**
	 * Loop render item
	 *
	 * @param  string  $format     [description]
	 * @param  [type]  $key        [description]
	 * @param  boolean $nested_key [description]
	 * @return [type]              [description]
	 */
	public function _render_loop_item( $keys = array(), $format = '%s' ) {

		$item = $this->_processed_item;

		$key        = $keys[0];
		$nested_key = isset( $keys[1] ) ? $keys[1] : false;

		if ( empty( $item ) || ! isset( $item[ $key ] ) ) {
			return false;
		}

		if ( false === $nested_key || ! is_array( $item[ $key ] ) ) {
			$value = $item[ $key ];
		} else {
			$value = isset( $item[ $key ][ $nested_key ] ) ? $item[ $key ][ $nested_key ] : false;
		}

		if ( ! empty( $value ) ) {
			return sprintf( $format, $this->sanitize_render_value( $value, $format ) );
		}

	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __glob_inc_if( $name = null, $settings = array() ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_glob_inc_if()' );

		$this->_glob_inc_if( $name, $settings );
	}

	/**
	 * Include global template if any of passed settings is defined
	 *
	 * @param  [type] $name     [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function _glob_inc_if( $name = null, $settings = array() ) {

		$template = $this->_get_global_template( $name );

		call_user_func( array( $this, sprintf( '_%s_inc_if', $this->_context ) ), $template, $settings );

	}

	/**
	 * Include render template if any of passed setting is not empty
	 *
	 * @param  [type] $file     [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function _render_inc_if( $file = null, $settings = array() ) {

		foreach ( $settings as $setting ) {
			$val = $this->get_settings_for_display( $setting );

			if ( ! empty( $val ) ) {
				include $file;
				return;
			}

		}

	}

	/**
	 * Include render template if any of passed setting is not empty
	 *
	 * @param  [type] $file     [description]
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function _edit_inc_if( $file = null, $settings = array() ) {

		$condition = null;
		$sep       = null;

		foreach ( $settings as $setting ) {
			$condition .= $sep . 'settings.' . $setting;
			$sep = ' || ';
		}

		?>

		<# if ( <?php echo $condition; // phpcs:ignore ?> ) { #>

			<?php include $file; ?>

		<# } #>

		<?php
	}

	/**
	 * Open standard wrapper
	 *
	 * @return void
	 */
	public function _open_wrap() {
		printf( '<div class="elementor-%s jet-elements">', $this->get_name() ); // phpcs:ignore
	}

	/**
	 * Close standard wrapper
	 *
	 * @return void
	 */
	public function _close_wrap() {
		echo '</div>';
	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __html( $setting = null, $format = '%s' ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_html()' );

		$this->_html( $setting, $format );
	}

	/**
	 * Print HTML markup if passed setting not empty.
	 *
	 * @param  string $setting Passed setting.
	 * @param  string $format  Required markup.
	 * @param  array  $args    Additional variables to pass into format string.
	 * @param  bool   $echo    Echo or return.
	 * @return string|void
	 */
	public function _html( $setting = null, $format = '%s' ) {

		call_user_func( array( $this, sprintf( '_%s_html', $this->_context ) ), $setting, $format );

	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __get_html( $setting = null, $format = '%s' ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_get_html()' );

		return $this->_get_html( $setting, $format );
	}

	/**
	 * Returns HTML markup if passed setting not empty.
	 *
	 * @param  string $setting Passed setting.
	 * @param  string $format  Required markup.
	 * @param  array  $args    Additional variables to pass into format string.
	 * @param  bool   $echo    Echo or return.
	 * @return string|void
	 */
	public function _get_html( $setting = null, $format = '%s' ) {

		ob_start();
		$this->_html( $setting, $format );
		return ob_get_clean();

	}

	/**
	 * Print HTML template
	 *
	 * @param  [type] $setting [description]
	 * @param  [type] $format  [description]
	 * @return [type]          [description]
	 */
	public function _render_html( $setting = null, $format = '%s' ) {

		if ( is_array( $setting ) ) {
			$key     = $setting[1];
			$setting = $setting[0];
		}

		$val = $this->get_settings_for_display( $setting );

		if ( ! is_array( $val ) && '0' === $val ) {
			printf( $format, $this->sanitize_render_value( $val, $format ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( is_array( $val ) && empty( $val[ $key ] ) ) {
			return '';
		}

		if ( ! is_array( $val ) && empty( $val ) ) {
			return '';
		}

		if ( is_array( $val ) ) {
			printf( $format, $this->sanitize_render_value( $val[ $key ], $format ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			printf( $format, $this->sanitize_render_value( $val, $format ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

	}

	/**
	 * Sanitize a value for the context implied by a render format string.
	 *
	 * @param  mixed  $value    Value to sanitize.
	 * @param  string $format   sprintf/vsprintf format string.
	 * @param  int    $position Placeholder position for vsprintf formats.
	 * @return string
	 */
	public function sanitize_render_value( $value, $format = '%s', $position = 1 ) {

		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}

		$value = (string) $value;

		if ( $this->is_url_placeholder_context( $format, $position ) ) {
			return esc_url( $value );
		}

		if ( $this->is_attribute_placeholder_context( $format, $position ) ) {
			return esc_attr( $value );
		}

		return wp_kses_post( $value );
	}

	/**
	 * Sanitize Elementor URL control data before adding it to render attributes.
	 *
	 * @param  array $url_control Elementor URL control value.
	 * @return array
	 */
	public function sanitize_link_control( $url_control ) {

		if ( ! is_array( $url_control ) ) {
			return array(
				'url' => esc_url( $url_control ),
			);
		}

		if ( isset( $url_control['url'] ) ) {
			$url_control['url'] = esc_url( $url_control['url'] );
		}

		return $url_control;
	}

	/**
	 * Check whether placeholder is used inside URL-like attribute.
	 *
	 * @param  string $format   sprintf/vsprintf format string.
	 * @param  int    $position Placeholder position.
	 * @return bool
	 */
	protected function is_url_placeholder_context( $format = '%s', $position = 1 ) {

		$context = $this->get_placeholder_context( $format, $position );

		return (bool) preg_match( '/(?:href|src|action|formaction|poster|data-slide-url)\s*=\s*["\']?[^"\']*$/i', $context );
	}

	/**
	 * Check whether placeholder is used inside an HTML attribute.
	 *
	 * @param  string $format   sprintf/vsprintf format string.
	 * @param  int    $position Placeholder position.
	 * @return bool
	 */
	protected function is_attribute_placeholder_context( $format = '%s', $position = 1 ) {

		$context = $this->get_placeholder_context( $format, $position );

		return (bool) preg_match( '/[a-z0-9_:-]+\s*=\s*["\']?[^"\']*$/i', $context );
	}

	/**
	 * Return format substring before selected string placeholder.
	 *
	 * @param  string $format   sprintf/vsprintf format string.
	 * @param  int    $position Placeholder position.
	 * @return string
	 */
	protected function get_placeholder_context( $format = '%s', $position = 1 ) {

		if ( ! preg_match_all( '/%(?:(\d+)\$)?s/', $format, $matches, PREG_OFFSET_CAPTURE ) ) {
			return '';
		}

		$implicit_position = 0;

		foreach ( $matches[0] as $index => $match ) {
			$placeholder_position = ! empty( $matches[1][ $index ][0] ) ? absint( $matches[1][ $index ][0] ) : ++$implicit_position;

			if ( $placeholder_position === absint( $position ) ) {
				return substr( $format, 0, $match[1] );
			}
		}

		return '';
	}

	/**
	 * Return an underscore template expression escaped for the implied context.
	 *
	 * @param  string $expression Underscore template JS expression.
	 * @param  string $format     sprintf/vsprintf format string.
	 * @param  int    $position   Placeholder position.
	 * @return string
	 */
	protected function get_template_render_value( $expression = '', $format = '%s', $position = 1 ) {

		if ( $this->is_url_placeholder_context( $format, $position ) ) {
			return '{{ ' . $this->get_template_url_escape_expression( $expression ) . ' }}';
		}

		if ( $this->is_attribute_placeholder_context( $format, $position ) ) {
			return '{{ ' . $expression . ' || "" }}';
		}

		return '{{{ ' . $expression . ' }}}';
	}

	/**
	 * Return JS expression that escapes URL attributes in editor templates.
	 *
	 * @param  string $expression Underscore template JS expression.
	 * @return string
	 */
	protected function get_template_url_escape_expression( $expression = '' ) {
		$value = '( ' . $expression . ' || "" )';

		return '(/^\\s*(?:javascript|data):/i.test( ' . $value . ' ) ? "" : ' . $value . ')';
	}

	/**
	 * Print underscore template
	 *
	 * @param  [type] $setting [description]
	 * @param  [type] $format  [description]
	 * @return [type]          [description]
	 */
	public function _edit_html( $setting = null, $format = '%s' ) {

		if ( is_array( $setting ) ) {
			$setting = $setting[0] . '.' . $setting[1];
		}

		echo '<# if ( settings.' . $setting . ' ) { #>'; // phpcs:ignore
		printf( $format, $this->get_template_render_value( 'settings.' . $setting, $format ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<# } #>';
	}

	/**
	 * Add icon control
	 *
	 * @param string $id
	 * @param array  $args
	 * @param object $instance
	 */
	public function _add_advanced_icon_control( $id, array $args = array(), $instance = null ) {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {

			$_id = $id; // old control id
			$id  = $this->_new_icon_prefix . $id;

			$args['type'] = Controls_Manager::ICONS;
			$args['fa4compatibility'] = $_id;

			unset( $args['file'] );
			unset( $args['default'] );

			if ( isset( $args['fa5_default'] ) ) {
				$args['default'] = $args['fa5_default'];

				unset( $args['fa5_default'] );
			}
		} else {
			$args['type'] = Controls_Manager::ICON;
			unset( $args['fa5_default'] );
		}

		if ( null !== $instance ) {
			$instance->add_control( $id, $args );
		} else {
			$this->add_control( $id, $args );
		}
	}

	/**
	 * Prepare icon control ID for condition.
	 *
	 * @param  string $id Old icon control ID.
	 * @return string
	 */
	public function _prepare_icon_id_for_condition( $id ) {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {
			return $this->_new_icon_prefix . $id . '[value]';
		}

		return $id;
	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __icon( $setting = null, $format = '%s', $icon_class = '' ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_icon()' );

		$this->_icon( $setting, $format, $icon_class );
	}

	/**
	 * Print HTML icon markup
	 *
	 * @param  array $setting
	 * @param  string $format
	 * @param  string $icon_class
	 * @return void
	 */
	public function _icon( $setting = null, $format = '%s', $icon_class = '' ) {
		call_user_func( array( $this, sprintf( '_%s_icon', $this->_context ) ), $setting, $format, $icon_class );
	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __get_icon( $setting = null, $format = '%s', $icon_class = '' ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_get_icon()' );

		return $this->_get_icon( $setting, $format, $icon_class );
	}

	/**
	 * Returns HTML icon markup
	 *
	 * @param  array $setting
	 * @param  string $format
	 * @param  string $icon_class
	 * @return string
	 */
	public function _get_icon( $setting = null, $format = '%s', $icon_class = '' ) {
		return $this->_render_icon( $setting, $format, $icon_class, false );
	}

	/**
	 * @deprecated 2.2.15
	 */
	public function __render_icon( $setting = null, $format = '%s', $icon_class = '', $echo = true ) {
		_deprecated_function( __METHOD__, '2.2.15', __CLASS__ . '::_render_icon()' );

		return $this->_render_icon( $setting, $format, $icon_class, $echo );
	}

	/**
	 * Print HTML icon template
	 *
	 * @param  array  $setting
	 * @param  string $format
	 * @param  string $icon_class
	 * @param  bool   $echo
	 *
	 * @return void|string
	 */
	public function _render_icon( $setting = null, $format = '%s', $icon_class = '', $echo = true , $force_settings = false ) {

		if ( false === $this->_processed_item || true === $force_settings ) {
			$settings = $this->get_settings_for_display();
		} else {
			$settings = $this->_processed_item;
		}

		$new_setting = $this->_new_icon_prefix . $setting;

		$migrated = isset( $settings['__fa4_migrated'][ $new_setting ] );
		$is_new   = empty( $settings[ $setting ] ) && class_exists( 'Elementor\Icons_Manager' ) && Icons_Manager::is_migration_allowed();

		$icon_html = '';

		if ( $is_new || $migrated ) {

			$attr = array( 'aria-hidden' => 'true' );

			if ( ! empty( $icon_class ) ) {
				$attr['class'] = $icon_class;
			}

			if ( isset( $settings[ $new_setting ] ) ) {
				ob_start();
				Icons_Manager::render_icon( $settings[ $new_setting ], $attr );

				$icon_html = ob_get_clean();
			}

		} else if ( ! empty( $settings[ $setting ] ) ) {

			if ( empty( $icon_class ) ) {
				$icon_class = $settings[ $setting ];
			} else {
				$icon_class .= ' ' . $settings[ $setting ];
			}

			$icon_html = sprintf( '<i class="%s" aria-hidden="true"></i>', esc_attr( $icon_class ) );
		}

		if ( empty( $icon_html ) ) {
			return;
		}

		if ( ! $echo ) {
			return sprintf( $format, $icon_html );
		}

		printf( $format, $icon_html ); // phpcs:ignore
	}


	/**
	 * [__add_control description]
	 * @param  boolean $control_id   [description]
	 * @param  array   $control_args [description]
	 * @param  integer $load_level   [description]
	 * @return [type]                [description]
	 */
	public function _add_control( $control_id = false, $control_args = [], $load_level = 100 ) {
		if ( $control_id ) { \Jet_Elements_Settings_Validator::remember( $this->get_name(), $control_id, $control_args ); }

		if (
			( $this->_load_level < $load_level
			  || 0 === $this->_load_level
			  || in_array( $control_id, $this->_exclude_controls )
			) && !in_array( $control_id, $this->_include_controls )
		) {
			return false;
		}

		if ( function_exists( 'jet_styles_manager' ) && jet_styles_manager()->compatibility ) {
			$control_args = jet_styles_manager()->compatibility->set_control_args(
				$control_args,
				$load_level,
				'jet-elements'
			);
		}

		$this->add_control( $control_id, $control_args );
	}

	/**
	 * [__add_responsive_control description]
	 * @param  boolean $control_id   [description]
	 * @param  array   $control_args [description]
	 * @param  integer $load_level   [description]
	 * @return [type]                [description]
	 */
	public function _add_responsive_control( $control_id = false, $control_args = [], $load_level = 100 ) {
		if ( $control_id ) {
			\Jet_Elements_Settings_Validator::remember( $this->get_name(), $control_id, $control_args );
			$breakpoints = isset( Plugin::$instance->breakpoints ) ? Plugin::$instance->breakpoints->get_active_breakpoints() : array( 'tablet' => null, 'mobile' => null );
			foreach ( $breakpoints as $device => $breakpoint ) {
				$args = $control_args;
				$args['default'] = isset( $control_args[ $device . '_default' ] ) ? $control_args[ $device . '_default' ] : '';
				\Jet_Elements_Settings_Validator::remember( $this->get_name(), $control_id . '_' . $device, $args );
			}
		}

		if (
			( $this->_load_level < $load_level
			  || 0 === $this->_load_level
			  || in_array( $control_id, $this->_exclude_controls )
			) && !in_array( $control_id, $this->_include_controls )
		) {
			return false;
		}

		if ( function_exists( 'jet_styles_manager' ) && jet_styles_manager()->compatibility ) {
			$control_args = jet_styles_manager()->compatibility->set_control_args(
				$control_args,
				$load_level,
				'jet-elements'
			);
		}

		$this->add_responsive_control( $control_id, $control_args );
	}

	/**
	 * [__add_group_control description]
	 * @param  boolean $group_control_type [description]
	 * @param  array   $group_control_args [description]
	 * @param  integer $load_level         [description]
	 * @return [type]                      [description]
	 */
	public function _add_group_control( $group_control_type = false, $group_control_args = [], $load_level = 100 ) {

		if (
			( $this->_load_level < $load_level
			  || 0 === $this->_load_level
			  || in_array( $group_control_args['name'], $this->_exclude_controls )
			) && !in_array( $group_control_args['name'], $this->_include_controls )
		) {
			return false;
		}

		if ( function_exists( 'jet_styles_manager' ) && jet_styles_manager()->compatibility ) {
			$group_control_args = jet_styles_manager()->compatibility->set_group_control_args(
				$group_control_type,
				$group_control_args,
				$load_level,
				'jet-elements'
			);
		}

		$this->add_group_control( $group_control_type, $group_control_args );
	}

	/**
	 * [__add_icon_control description]
	 * @param  [type] $id   [description]
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public function _add_icon_control( $id, array $args = array(), $load_level = 100 ) {

		if (
			( $this->_load_level < $load_level
			  || 0 === $this->_load_level
			  || in_array( $id, $this->_exclude_controls )
			) && !in_array( $id, $this->_include_controls )
		) {
			return false;
		}

		$this->_add_advanced_icon_control( $id, $args );
	}

	/**
	 * [__start_controls_section description]
	 * @param  boolean $controls_section_id   [description]
	 * @param  array   $controls_section_args [description]
	 * @param  integer $load_level            [description]
	 * @return [type]                         [description]
	 */
	public function _start_controls_section( $controls_section_id = false, $controls_section_args = [], $load_level = 25 ) {

		if ( ! $controls_section_id || $this->_load_level < $load_level || 0 === $this->_load_level ) {
			return false;
		}

		$this->start_controls_section( $controls_section_id, $controls_section_args );
	}

	/**
	 * [__end_controls_section description]
	 * @param  integer $load_level [description]
	 * @return [type]              [description]
	 */
	public function _end_controls_section( $load_level = 25 ) {

		if ( $this->_load_level < $load_level || 0 === $this->_load_level ) {
			return false;
		}

		$this->end_controls_section();
	}

	/**
	 * [__start_controls_tabs description]
	 * @param  boolean $tabs_id    [description]
	 * @param  integer $load_level [description]
	 * @return [type]              [description]
	 */
	public function _start_controls_tabs( $tabs_id = false, $load_level = 25 ) {

		if ( ! $tabs_id || $this->_load_level < $load_level || 0 === $this->_load_level ) {
			return false;
		}

		$this->start_controls_tabs( $tabs_id );
	}

	/**
	 * [__end_controls_tabs description]
	 * @param  integer $load_level [description]
	 * @return [type]              [description]
	 */
	public function _end_controls_tabs( $load_level = 25 ) {

		if ( $this->_load_level < $load_level || 0 === $this->_load_level ) {
			return false;
		}

		$this->end_controls_tabs();
	}

	/**
	 * [__start_controls_tab description]
	 * @param  boolean $tab_id     [description]
	 * @param  array   $tab_args   [description]
	 * @param  integer $load_level [description]
	 * @return [type]              [description]
	 */
	public function _start_controls_tab( $tab_id = false, $tab_args = [], $load_level = 25 ) {

		if ( ! $tab_id || $this->_load_level < $load_level || 0 === $this->_load_level ) {
			return false;
		}

		$this->start_controls_tab( $tab_id, $tab_args );
	}

	/**
	 * [__end_controls_tab description]
	 * @param  integer $load_level [description]
	 * @return [type]              [description]
	 */
	public function _end_controls_tab( $load_level = 25 ) {

		if ( $this->_load_level < $load_level || 0 === $this->_load_level ) {
			return false;
		}

		$this->end_controls_tab();
	}

	/**
	 * Start popover
	 *
	 * @param int $load_level
	 * @return void|bool
	 */
	public function _start_popover( $load_level = 25 ) {

		if ( $this->_load_level < $load_level || 0 === $this->_load_level ) {
			return false;
		}

		$this->start_popover();
	}

	/**
	 * End popover
	 *
	 * @param int $load_level
	 * @return void|bool
	 */
	public function _end_popover( $load_level = 25 ) {

		if ( $this->_load_level < $load_level || 0 === $this->_load_level ) {
			return false;
		}

		$this->end_popover();
	}

	public function _add_link_attributes( $element, array $url_control, $overwrite = false ) {
		$url_control = $this->sanitize_link_control( $url_control );

		if ( method_exists( $this, 'add_link_attributes' ) ) {
			return $this->add_link_attributes( $element, $url_control, $overwrite );
		}

		$attributes = array();

		if ( ! empty( $url_control['url'] ) ) {
			$attributes['href'] = esc_url( $url_control['url'] );
		}

		if ( ! empty( $url_control['is_external'] ) ) {
			$attributes['target'] = '_blank';
		}

		if ( ! empty( $url_control['nofollow'] ) ) {
			$attributes['rel'] = 'nofollow';
		}

		if ( $attributes ) {
			$this->add_render_attribute( $element, $attributes, $overwrite );
		}

		return $this;
	}

}
