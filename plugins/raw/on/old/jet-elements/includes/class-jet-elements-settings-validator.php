<?php
/** Registered-control validation shared by rendering, CSS and editor data. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Jet_Elements_Settings_Validator {
	private static $schemas = array();

	public static function remember( $scope, $name, $args ) {
		$keys = array_flip( array( 'type', 'default', 'query_type', 'options', 'groups', 'fields', 'multiple', 'return_value', 'toggle' ) );
		self::$schemas[ $scope ][ $name ] = array_replace( self::$schemas[ $scope ][ $name ] ?? array(), array_intersect_key( $args, $keys ) );
	}

	public static function schema( $scope, $controls ) {
		foreach ( $controls as $name => &$control ) {
			$schema = self::$schemas[ $scope ][ $name ] ?? array();
			if ( ! $schema && 'extensions' === $scope ) {
				foreach ( self::$schemas[ $scope ] ?? array() as $base => $candidate ) {
					if ( 0 === strpos( $name, $base . '_' ) ) { $schema = $candidate; break; }
				}
			}
			$control = array_replace( array( 'name' => $name, 'type' => 'text' ), $schema, $control );
			// Preserve the missing-schema distinction when Elementor later merges
			// the control type's default (empty) options while generating CSS.
			if ( in_array( $control['type'], array( 'select', 'select2', 'jet-select2', 'choose' ), true ) && ! array_key_exists( 'options', $control ) && ! array_key_exists( 'groups', $control ) ) {
				$control['_jet_elements_missing_choices'] = true;
			}
		}
		return $controls;
	}

	private static function registries( $controls ) {
		foreach ( $controls as &$control ) {
			$type = $control['type'] ?? '';
			if ( 'repeater' === $type && isset( $control['fields'] ) ) { $control['fields'] = self::registries( $control['fields'] ); }
			if ( ! in_array( $type, array( 'icon', 'animation', 'hover_animation' ), true ) ) { continue; }
			if ( ! isset( $control['options'] ) ) {
				$registry = \Elementor\Plugin::$instance->controls_manager->get_control( $type );
				if ( ! $registry ) { continue; }
				$options = 'icon' === $type ? $registry->get_settings( 'options' ) : $registry::get_animations();
				$control['options'] = array();
				foreach ( (array) $options as $key => $label ) {
					if ( is_array( $label ) ) { $control['options'] += $label; }
					else { $control['options'][ $key ] = $label; }
				}
			}
			if ( ! empty( $control['include'] ) ) { $control['options'] = array_intersect_key( $control['options'], array_flip( $control['include'] ) ); }
			if ( ! empty( $control['exclude'] ) ) { $control['options'] = array_diff_key( $control['options'], array_flip( $control['exclude'] ) ); }
		}
		return $controls;
	}

	public static function controls( $element ) {
		$controls = $element->get_controls();
		$stack = $element->get_stack();
		$controls += $stack['style_controls'] ?? array();
		$scope = $element instanceof \Elementor\Jet_Elements_Base ? $element->get_name() : 'extensions';
		if ( 'extensions' !== $scope ) { $controls += self::$schemas[ $scope ] ?? array(); }
		return class_exists( '\\Elementor\\Plugin' ) ? self::registries( self::schema( $scope, $controls ) ) : self::schema( $scope, $controls );
	}

	public static function add_extension_responsive_control( $element, $name, $args ) {
		return self::add_extension_control( $element, $name, $args, true );
	}

	public static function add_extension_control( $element, $name, $args, $responsive = false ) {
		self::remember( 'extensions', $name, $args );
		return $responsive ? $element->add_responsive_control( $name, $args ) : $element->add_control( $name, $args );
	}

	public static function normalize( $settings, $controls ) {
		$settings = is_array( $settings ) ? $settings : array();
		foreach ( $controls as $key => $control ) {
			$name = isset( $control['name'] ) ? $control['name'] : $key;
			if ( ! array_key_exists( $name, $settings ) ) { continue; }
			$type = isset( $control['type'] ) ? $control['type'] : '';
			if ( in_array( $type, array( 'repeater', 'jet-repeater' ), true ) && isset( $control['fields'] ) ) {
				$rows = is_array( $settings[ $name ] ) ? $settings[ $name ] : array();
				$settings[ $name ] = array();
				foreach ( $rows as $row ) {
					if ( is_array( $row ) ) { $settings[ $name ][] = self::normalize( $row, $control['fields'] ); }
				}
			} else {
				$settings[ $name ] = self::value( $settings[ $name ], $control );
			}
		}
		return $settings;
	}

	public static function value( $value, $control ) {
		$type = isset( $control['type'] ) ? $control['type'] : '';
		$default = isset( $control['default'] ) ? $control['default'] : '';
		if ( 'jet-query' === $type && 'elementor_templates' === ( $control['query_type'] ?? '' ) ) {
			if ( ! ( is_string( $value ) || is_int( $value ) ) || ! preg_match( '/^[1-9][0-9]*$/D', (string) $value ) ) { return 0; }
			$post = get_post( (int) $value );
			return $post && 'elementor_library' === $post->post_type && 'publish' === $post->post_status ? (int) $post->ID : 0;
		}
		if ( 'switcher' === $type ) {
			$on = isset( $control['return_value'] ) ? $control['return_value'] : 'yes';
			$off = in_array( $default, array( '', 'false', 'no', '0', 0, false ), true ) ? $default : '';
			if ( $value === $on || true === $value ) { return $on; }
			if ( in_array( $value, array( '', false, 'false', 'no', '0', 0, $off ), true ) ) { return $off; }
			return $default === $on ? $on : $off;
		}
		if ( ! in_array( $type, array( 'select', 'select2', 'jet-select2', 'choose', 'icon', 'animation', 'hover_animation' ), true ) ) { return $value; }
		// Elementor strips options from inherited Advanced controls on the frontend.
		// Without a choice schema, leave validation to the control's owner. An
		// explicitly empty options/groups list must still reject unknown values.
		if ( ! empty( $control['_jet_elements_missing_choices'] ) || ( ! array_key_exists( 'options', $control ) && ! array_key_exists( 'groups', $control ) ) ) { return $value; }
		$options = isset( $control['options'] ) && is_array( $control['options'] ) ? $control['options'] : array();
		if ( isset( $control['groups'] ) && is_array( $control['groups'] ) ) {
			foreach ( $control['groups'] as $key => $group ) {
				if ( is_array( $group ) && isset( $group['options'] ) ) { $options += $group['options']; }
				elseif ( is_string( $group ) ) { $options[ $key ] = $group; }
			}
		}
		$keys = array_map( 'strval', array_keys( $options ) );
		$valid = function( $item ) use ( $keys ) {
			return ( is_string( $item ) || is_int( $item ) || ( is_float( $item ) && is_finite( $item ) ) ) && in_array( (string) $item, $keys, true );
		};
		if ( ! empty( $control['multiple'] ) ) {
			if ( ! is_array( $value ) ) { $value = is_array( $default ) ? $default : array(); }
			return array_values( array_unique( array_map( 'strval', array_filter( $value, $valid ) ) ) );
		}
		if ( $valid( $value ) ) { return (string) $value; }
		// Empty values are Elementor's unset/inherited state, including responsive styles.
		if ( '' === $value && ( '' === $default || ! empty( $control['responsive'] ) || ( 'choose' === $type && ( ! isset( $control['toggle'] ) || $control['toggle'] ) ) ) ) { return ''; }
		return $valid( $default ) ? (string) $default : '';
	}

	public static function extension_controls( $controls ) {
		return array_filter( $controls, function( $control, $key ) {
			$name = isset( $control['name'] ) ? $control['name'] : $key;
			$schemas = self::$schemas['extensions'] ?? array();
			if ( isset( $schemas[ $name ] ) ) { return true; }
			// Elementor generates responsive variants of our registered controls.
			if ( isset( $control['responsive'] ) ) {
				foreach ( $schemas as $base => $schema ) {
					if ( 0 === strpos( $name, $base . '_' ) ) { return true; }
				}
			}
			// The jet_ prefix is also used by JetEngine and other plugins.
			return false;
		}, ARRAY_FILTER_USE_BOTH );
	}

	public static function normalize_element( $element ) {
		$controls = self::controls( $element );
		if ( ! $element instanceof \Elementor\Jet_Elements_Base ) { $controls = self::extension_controls( $controls ); }
		$element->set_settings( self::normalize( $element->get_settings(), $controls ) );
	}

	public static function document_elements( $elements ) {
		if ( ! is_array( $elements ) ) { return $elements; }
		foreach ( $elements as &$data ) {
			if ( ! is_array( $data ) ) { continue; }
			$element = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $data );
			if ( $element && isset( $data['settings'] ) ) {
				$controls = self::controls( $element );
				if ( ! $element instanceof \Elementor\Jet_Elements_Base ) { $controls = self::extension_controls( $controls ); }
				$data['settings'] = self::normalize( $data['settings'], $controls );
			}
			if ( isset( $data['elements'] ) ) { $data['elements'] = self::document_elements( $data['elements'] ); }
		}
		return $elements;
	}

	public static function document_save( $data ) {
		if ( isset( $data['elements'] ) ) { $data['elements'] = self::document_elements( $data['elements'] ); }
		return $data;
	}

	public static function init() {
		add_action( 'elementor/frontend/before_render', array( __CLASS__, 'normalize_element' ), 1 );
		add_action( 'elementor/element/before_parse_css', function( $file, $element ) { self::normalize_element( $element ); }, 1, 2 );
		add_filter( 'elementor/document/load/data', array( __CLASS__, 'document_elements' ) );
		add_filter( 'elementor/document/save/data', array( __CLASS__, 'document_save' ) );
	}
}
