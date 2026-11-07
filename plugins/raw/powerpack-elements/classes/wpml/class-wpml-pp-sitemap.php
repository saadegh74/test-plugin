<?php

class WPML_PP_Sitemap extends WPML_Elementor_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'tabs';
	}

	public function get_fields() {
		return array( 
			'sitemap_title',
		);
	}

	protected function get_title( $field ) {
		switch( $field ) {
			case 'sitemap_title':
				return esc_html__( 'Sitemap - Sitemap Title', 'powerpack' );
			default:
				return '';
		}
	}

	protected function get_editor_type( $field ) {
		switch( $field ) {
			case 'sitemap_title':
				return 'LINE';
			default:
				return '';
		}
	}

}
