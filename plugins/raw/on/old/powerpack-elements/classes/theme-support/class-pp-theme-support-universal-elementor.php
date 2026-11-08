<?php
/**
 * Add support for overriding header and footer
 *
 * Add support for overriding header and footer using the Elementor's
 * method of creating custom header.php and footer.php files.
 *
 * @link https://powerpackelements.com/
 *
 * @package powerpack-elements
 * @subpackage header-footer
 * @since 1.5.1
 */

use PowerpackElements\Classes\PP_Header_Footer;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Support for the Storefront theme.
 *
 * @since 1.4.15
 */
final class PP_Header_Footer_Universal {
	/**
	 * Setup support for the theme.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function init() {
		add_action( 'wp', __CLASS__ . '::setup_headers_and_footers' );
	}

	/**
	 * Setup headers and footers.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function setup_headers_and_footers() {
		if ( ! empty( PP_Header_Footer::$header ) ) {
			add_action( 'get_header', __CLASS__ . '::override_header' );
			add_action( 'pp_header', __CLASS__ . '::render_header' );
		}
		if ( ! empty( PP_Header_Footer::$footer ) ) {
			add_action( 'get_footer', __CLASS__ . '::override_footer' );
			add_action( 'pp_footer', __CLASS__ . '::render_footer' );
		}
	}

	/**
	 * Renders the header for the current page.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function render_header() {
		PP_Header_Footer::render_header();
	}

	/**
	 * Renders the footer for the current page.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function render_footer() {
		PP_Header_Footer::render_footer();
	}

	/**
	 * Function for overriding the header in the elmentor way.
	 *
	 * @since 1.5.1
	 *
	 * @return void
	 */
	public static function override_header() {
		require POWERPACK_ELEMENTS_PATH . 'classes/theme-support/includes/ppe-header.php';
		$templates   = array();
		$templates[] = 'header.php';
		// Avoid running wp_head hooks again.
		remove_all_actions( 'wp_head' );
		ob_start();
		locate_template( $templates, true );
		ob_get_clean();
	}

	/**
	 * Function for overriding the footer in the elmentor way.
	 *
	 * @since 1.5.1
	 *
	 * @return void
	 */
	public static function override_footer() {
		require POWERPACK_ELEMENTS_PATH . 'classes/theme-support/includes/ppe-footer.php';
		$templates   = array();
		$templates[] = 'footer.php';
		// Avoid running wp_footer hooks again.
		remove_all_actions( 'wp_footer' );
		ob_start();
		locate_template( $templates, true );
		ob_get_clean();
	}
}

PP_Header_Footer_Universal::init();
