<?php
/**
 * Compare & Wishlist base class
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

abstract class Jet_CW_Base extends Widget_Base {

	public $__processed_item  = false;
	public $__new_icon_prefix = 'selected_';

	/**
	 * Returns jet help url
	 *
	 * @return false
	 */
	public function get_jet_help_url() {
		return false;
	}

	/**
	 * Returns helps url
	 *
	 * @return false|string
	 */
	public function get_help_url() {

		$url = $this->get_jet_help_url();

		if ( ! empty( $url ) ) {
			return add_query_arg(
				array(
					'utm_source'   => 'need-help',
					'utm_medium'   => $this->get_name(),
					'utm_campaign' => 'jetcomparewishlist',
				),
				esc_url( $url )
			);
		}

		return false;

	}

	/**
	 * Get global template.
	 *
	 * Returns globally affected template.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param null $name Name of the template
	 *
	 * @return string
	 */
	public function __get_global_template( $name = null ) {
		return jet_cw()->get_template( $this->get_name() . '/global/' . $name . '.php' );
	}

	/**
	 * Open standard wrapper
	 *
	 * @return void
	 */
	public function __open_wrap() {
		printf( '<div class="%s jet-cw">', $this->get_name() );
	}

	/**
	 * Close standard wrapper
	 *
	 * @return void
	 */
	public function __close_wrap() {
		echo '</div>';
	}

	/**
	 * Add icon control
	 *
	 * @param string $id
	 * @param array  $args
	 * @param object $instance
	 */
	public function __add_advanced_icon_control( $id = '', array $args = array(), $instance = null ) {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {

			$_id = $id; // old control id
			$id  = $this->__new_icon_prefix . $id;

			$args['type']             = Controls_Manager::ICONS;
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
	 * @param string $id Old icon control ID.
	 *
	 * @return string
	 */
	public function __prepare_icon_id_for_condition( $id ) {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {
			return $this->__new_icon_prefix . $id . '[value]';
		}

		return $id;

	}

	/**
	 * Print HTML icon template
	 *
	 * @param array  $setting
	 * @param string $format
	 * @param string $icon_class
	 * @param bool   $echo
	 *
	 * @return void|string
	 */
	public function __render_icon( $setting = null, $format = '%s', $icon_class = '', $echo = true ) {

		if ( false === $this->__processed_item ) {
			$settings = $this->get_settings_for_display();
		} else {
			$settings = $this->__processed_item;
		}

		$new_setting = $this->__new_icon_prefix . $setting;

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

			$icon_html = sprintf( '<i class="%s" aria-hidden="true"></i>', $icon_class );
		}

		if ( empty( $icon_html ) ) {
			return;
		}

		if ( ! $echo ) {
			return sprintf( $format, $icon_html );
		}

		printf( $format, $icon_html );

	}

	/**
	 * Set custom size units.
	 *
	 * Extend list of units with custom option.
	 *
	 * @since  1.5.4
	 * @access public
	 *
	 * @param array $units List of units.
	 *
	 * @return mixed
	 */
	public function set_custom_size_unit( $units ) {

		if ( version_compare( ELEMENTOR_VERSION, '3.10.0', '>=' ) ) {
			$units[] = 'custom';
		}

		return $units;

	}

}
