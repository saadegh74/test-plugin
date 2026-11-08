<?php
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
final class PP_Header_Footer_Storefront {
	/**
	 * Setup support for the theme.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	static public function init()
	{
		add_action( 'wp', __CLASS__ . '::setup_headers_and_footers' );
	}

	/**
	 * Setup headers and footers.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	static public function setup_headers_and_footers()
	{
		if ( ! empty( PP_Header_Footer::$header ) ) {
			remove_all_actions( 'storefront_header' );
			add_action( 'storefront_header', __CLASS__ . '::render_header' );
		}
		if ( ! empty( PP_Header_Footer::$footer ) ) {
			remove_all_actions( 'storefront_footer' );
			add_action( 'storefront_footer', __CLASS__ . '::render_footer' );
		}
	}

	/**
	 * Renders the header for the current page.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	static public function render_header()
	{
		PP_Header_Footer::render_header( 'div', 'pphead' );
	}

	/**
	 * Renders the footer for the current page.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	static public function render_footer()
	{
		PP_Header_Footer::render_footer( 'div' );
	}
}

PP_Header_Footer_Storefront::init();