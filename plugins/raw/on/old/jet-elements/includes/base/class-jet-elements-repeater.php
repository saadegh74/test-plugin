<?php
namespace Elementor;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Retain choice schemas that Elementor drops during frontend control optimization. */
class Jet_Elements_Repeater extends Repeater {
	private $choice_schema = array();

	public function add_control( $control_id, array $args, $options = array() ) {
		$this->choice_schema[ $control_id ] = array_intersect_key( $args, array_flip( array( 'options', 'groups', 'fields', 'multiple', 'return_value', 'toggle' ) ) );
		return parent::add_control( $control_id, $args, $options );
	}

	public function get_controls( $control_id = null ) {
		$controls = parent::get_controls( $control_id );
		if ( null !== $control_id ) { return $controls ? array_replace( $this->choice_schema[ $control_id ] ?? array(), $controls ) : $controls; }
		$stack = $this->get_stack();
		$controls += $stack['style_controls'] ?? array();
		foreach ( $controls as $name => &$control ) { $control = array_replace( $this->choice_schema[ $name ] ?? array(), $control ); }
		return $controls;
	}
}
