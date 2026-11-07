<?php
/**
 * Compatibility class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Blocks_Compatibility' ) ) {

	/**
	 * Define Jet_Blocks_Compatibility class
	 */
	class Jet_Blocks_Compatibility {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   Jet_Blocks_Compatibility
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {

			// Compatibility with Advanced noCaptcha & invisible Captcha plugin
			if ( class_exists( 'anr_captcha_class' ) ) {

				if ( function_exists( 'anr_is_form_enabled' ) && anr_is_form_enabled( 'registration' ) ) {
					$anr_captcha_class = anr_captcha_class::init();

					add_action( 'jet_register_form',               array( $anr_captcha_class, 'form_field' ) );
					add_filter( 'jet_register_form_custom_error',  array( $this, 'captcha_verify' ) );
				}
			}

			// WPML Compatibility
			if ( defined( 'WPML_ST_VERSION' ) ) {
				add_filter( 'cx_breadcrumbs/post', array( $this, 'set_wpml_post_id' ) );
			}

			add_filter( 'jet-blocks/reset/permalink', array( $this, 'set_profile_builder_url' ) );
		}

		/**
		 * Set WPML translated breadcrumbs.
		 *
		 * @param $post
		 *
		 * @return mixed|void
		 */
		public function set_wpml_post_id( $post) {

			$post_id = apply_filters( 'wpml_object_id', $post->ID, $post->post_type, true );

			return get_post($post_id);

		}

		/**
		 * Profile Builder Compatibility 
		 *
		 * @param $permalink
		 *
		 * @return mixed|void
		 */
		public function set_profile_builder_url( $permalink ) {

			if ( function_exists( 'jet_engine' ) && jet_engine()->modules->is_module_active( 'profile-builder' ) ) {
				$profile_builder = jet_engine()->modules->get_module( 'profile-builder' );
			 
				$is_account_page = $profile_builder->instance->query->is_account_page();
				$sub_page        = $profile_builder->instance->query->get_subpage();
			 
				if ( $is_account_page && $sub_page ) {
				   return trailingslashit( $permalink . '/' . $sub_page );
				}
			}
			return $permalink;
		}

		/**
		 * Captcha verify.
		 *
		 * @param  mixed $verify
		 * @return WP_Error
		 */
		public function captcha_verify( $verify ) {
			$anr_captcha_class = anr_captcha_class::init();

			if ( ! $anr_captcha_class->verify() ) {
				return new WP_Error(
					'anr_error',
					$anr_captcha_class->add_error_to_mgs()
				);
			}

			return $verify;
		}

		/**
		 * Returns the instance.
		 *
		 * @return Jet_Blocks_Compatibility
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
	}

}

/**
 * Returns instance of Jet_Blocks_Compatibility
 *
 * @return Jet_Blocks_Compatibility
 */
function jet_blocks_compatibility() {
	return Jet_Blocks_Compatibility::get_instance();
}
