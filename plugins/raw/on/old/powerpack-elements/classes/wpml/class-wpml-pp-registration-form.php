<?php

class WPML_PP_Registration_Form extends WPML_Elementor_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'tabs';
	}

	public function get_fields() {
		return array( 
			'field_label',
			'placeholder',
			'default_value',
			'static_text',
			'validation_msg',
		);
	}

	protected function get_title( $field ) {
		switch( $field ) {
			case 'field_label':
				return esc_html__( 'Registration Form - Field Label', 'powerpack' );
			case 'placeholder':
				return esc_html__( 'Registration Form - Field Placeholder', 'powerpack' );
			case 'default_value':
				return esc_html__( 'Registration Form - Field Default Value', 'powerpack' );
			case 'static_text':
				return esc_html__( 'Registration Form - Static Field Text', 'powerpack' );
			case 'validation_msg':
				return esc_html__( 'Registration Form - Custom Validation Message', 'powerpack' );
			default:
				return '';
		}
	}

	protected function get_editor_type( $field ) {
		switch( $field ) {
			case 'field_label':
				return 'LINE';
			case 'placeholder':
				return 'LINE';
			case 'default_value':
				return 'LINE';
			case 'static_text':
				return 'AREA';
			case 'validation_msg':
				return 'LINE';
			default:
				return '';
		}
	}

}
