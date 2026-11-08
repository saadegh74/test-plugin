<?php
namespace PowerpackElements\Classes;

use PowerpackElements\Classes\PP_Admin_Settings;

/**
 * Handles logic for login and registration pages.
 *
 * @package PowerPack
 * @since 1.4.15
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PP_Login_Register.
 */
final class PP_Login_Register {
	/**
	 * Settings Tab constant.
	 */
	const SETTINGS_TAB = 'login_register';

	private static $cached_data = array();

	/**
	 * Initializing PowerPack maintenance mode.
	 *
	 * @since 1.4.15
	 */
	public static function init() {
		add_filter( 'pp_elements_admin_settings_tabs', __CLASS__ . '::render_settings_tab', 10, 1 );
		add_action( 'pp_elements_admin_settings_save', __CLASS__ . '::save_settings' );
		// add_action( 'login_init', 				__CLASS__ . '::redirect' );
		//add_action( 'init', 					__CLASS__ . '::login_redirect' );
		add_filter( 'authenticate', __CLASS__ . '::auth_redirect', 10, 3 );
		add_action( 'wp_logout', __CLASS__ . '::logout_redirect' );
	}

	/**
	 * Render settings tab.
	 *
	 * Adds Login / Register tab in PowerPack admin settings.
	 *
	 * @since 1.4.15
	 * @param array $tabs Array of existing settings tabs.
	 */
	public static function render_settings_tab( $tabs ) {
		$tabs[ self::SETTINGS_TAB ] = array(
			'title'             => esc_html__( 'Login / Register', 'powerpack' ),
			'show'              => ! PP_Admin_Settings::get_option( 'ppwl_hide_login_register_tab' ),
			'file'              => POWERPACK_ELEMENTS_PATH . 'includes/admin/admin-settings-login-register.php',
			'priority'          => 355,
		);

		return $tabs;
	}

	/**
	 * Save settings.
	 *
	 * Saves setting fields value in options.
	 *
	 * @since 2.6.10
	 */
	public static function save_settings() {
		if ( isset( $_POST['pp_login_page'] ) ) {
			$login_page = wp_unslash( $_POST['pp_login_page'] );
			update_option( 'pp_login_page', $login_page );
		}
		if ( isset( $_POST['pp_register_page'] ) ) {
			$register_page = wp_unslash( $_POST['pp_register_page'] );
			update_option( 'pp_register_page', $register_page );
		}
	}

	/**
	 * Get pages.
	 *
	 * Get all pages and create options for select field.
	 *
	 * @since 1.4.15
	 * @param string $selected  Selected page for the field.
	 * @return array $options   An array of pages.
	 */
	public static function get_pages( $selected = '' ) {
		if ( empty( self::$cached_data ) ) {
			$args = array(
				'post_type'         => 'page',
				'post_status'       => 'publish',
				'orderby'           => 'title',
				'order'             => 'ASC',
				'posts_per_page'    => '-1',
				'update_post_meta_cache' => false,
			);

			self::$cached_data = get_posts( $args );
		}

		$options = '<option value="">' . __( '-- Select --', 'powerpack' ) . '</option>';

		if ( count( self::$cached_data ) ) {
			foreach ( self::$cached_data as $post ) {
				$options .= '<option value="' . $post->ID . '" ' . selected( $selected, $post->ID, false ) . '>' . $post->post_title . '</option>';
			}
		} else {
			$options = '<option value="" disabled>' . __( 'No pages found!', 'powerpack' ) . '</option>';
		}

		return $options;
	}

	/**
	 * Redirect.
	 *
	 * Redirects wp-login.php to custom login page or register page.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function redirect() {
		$redirect_to = '';
		$page_id = '';

		if ( isset( $_REQUEST['interim-login'] ) ) {
			return;
		}

		if ( isset( $_GET['action'] ) && 'register' == $_GET['action'] ) {
			$page_id = PP_Admin_Settings::get_option( 'pp_register_page', true );
		} else {
			if ( ! is_user_logged_in() ) {
				$page_id = PP_Admin_Settings::get_option( 'pp_login_page', true );
			}
		}

		if ( ! empty( $page_id ) ) {
			$redirect_to = get_permalink( $page_id );
		}

		if ( ! empty( $redirect_to ) ) {
			wp_redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * Login redirect.
	 *
	 * Redirects wp-login.php to custom login page.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function login_redirect() {
		$redirect_to = '';

		if (
			'wp-login.php' == basename( $_SERVER['REQUEST_URI'] ) &&
			'GET' == $_SERVER['REQUEST_METHOD']
		) {
			$page_id = '';

			if ( isset( $_GET['action'] ) && 'register' == $_GET['action'] ) {
				$page_id = PP_Admin_Settings::get_option( 'pp_register_page', true );
			} else {
				$page_id = PP_Admin_Settings::get_option( 'pp_login_page', true );
			}

			if ( ! empty( $page_id ) ) {
				$redirect_to = get_permalink( $page_id );
			}
		}

		if ( ! empty( $redirect_to ) ) {
			wp_redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * Authentication redirect.
	 *
	 * Redirect to custom login page if username and password fields
	 * left empty.
	 *
	 * @since 1.4.15
	 * @param object $user      User object.
	 * @param string $username  User's login name.
	 * @param string $password  User's login password.
	 * @return object $user
	 */
	public static function auth_redirect( $user, $username, $password ) {
		if ( isset( $_REQUEST['interim-login'] ) ) {
			return $user;
		}

		if ( empty( $username ) || empty( $password ) ) {
			$id = PP_Admin_Settings::get_option( 'pp_login_page', true );

			if ( ! empty( $id ) ) {
				$login_page = get_permalink( $id );

				wp_redirect( $login_page );
				exit;
			}
		}

		return $user;
	}

	/**
	 * Logout redirect.
	 *
	 * Redirects to login page after succesful logout.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function logout_redirect() {
		$id = PP_Admin_Settings::get_option( 'pp_login_page', true );

		if ( ! empty( $id ) ) {
			$login_page = get_permalink( $id );

			wp_redirect( $login_page );
			exit;
		}
	}
}

// Initialize the class.
PP_Login_Register::init();
