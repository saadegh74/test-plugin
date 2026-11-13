<?php
/**
 * Advanced Map providers registry.
 *
 * @package JetElements
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Provider_Base' ) ) {

	/**
	 * Base provider.
	 */
	abstract class Jet_Elements_Advanced_Map_Provider_Base {

		/**
		 * Returns unique provider slug.
		 *
		 * @return string
		 */
		abstract public function get_id();

		/**
		 * Returns human-readable label.
		 *
		 * @return string
		 */
		abstract public function get_label();

		/**
		 * Additional script handles to enqueue.
		 *
		 * @return string[]
		 */
		public function get_script_depends() {
			return array();
		}

		/**
		 * Additional style handles to enqueue.
		 *
		 * @return string[]
		 */
		public function get_style_depends() {
			return array();
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Provider_Google' ) ) {

	/**
	 * Google provider.
	 */
	class Jet_Elements_Advanced_Map_Provider_Google extends Jet_Elements_Advanced_Map_Provider_Base {

		/**
		 * {@inheritdoc}
		 */
		public function get_id() {
			return 'google';
		}

		/**
		 * {@inheritdoc}
		 */
		public function get_label() {
			return __( 'Google Maps', 'jet-elements' );
		}

		/**
		 * {@inheritdoc}
		 */
		public function get_script_depends() {
			$api_disabled = jet_elements_settings()->get( 'disable_api_js', array( 'disable' => 'false' ) );
			$key          = jet_elements_settings()->get( 'api_key' );

			if ( empty( $key ) || ( ! empty( $api_disabled ) && 'true' === $api_disabled['disable'] ) ) {
				return array();
			}

			return array( 'google-maps-api' );
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Provider_Leaflet' ) ) {

	/**
	 * Leaflet provider.
	 */
	class Jet_Elements_Advanced_Map_Provider_Leaflet extends Jet_Elements_Advanced_Map_Provider_Base {

		/**
		 * {@inheritdoc}
		 */
		public function get_id() {
		 return 'leaflet';
		}

		/**
		 * {@inheritdoc}
		 */
		public function get_label() {
			return __( 'Leaflet', 'jet-elements' );
		}

		/**
		 * {@inheritdoc}
		 */
		public function get_script_depends() {
			return array(
				'jet-elements-leaflet',
				'jet-elements-leaflet-cluster',
			);
		}

		/**
		 * {@inheritdoc}
		 */
		public function get_style_depends() {
			return array(
				'jet-elements-leaflet',
				'jet-elements-leaflet-cluster',
				'jet-elements-leaflet-cluster-default',
			);
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Provider_Mapbox' ) ) {

	class Jet_Elements_Advanced_Map_Provider_Mapbox extends Jet_Elements_Advanced_Map_Provider_Base {

		public function get_id() {
			return 'mapbox';
		}

		public function get_label() {
			return __( 'Mapbox', 'jet-elements' );
		}

		public function get_script_depends() {
			return array(
				'jet-elements-mapbox-gl',
			);
		}

		public function get_style_depends() {
			return array(
				'jet-elements-mapbox',
			);
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Providers' ) ) {

	/**
	 * Providers registry.
	 */
	class Jet_Elements_Advanced_Map_Providers {

		/**
		 * Instance.
		 *
		 * @var null|Jet_Elements_Advanced_Map_Providers
		 */
		private static $instance = null;

		/**
		 * Registered providers.
		 *
		 * @var Jet_Elements_Advanced_Map_Provider_Base[]
		 */
		private $providers = array();

		/**
		 * Default provider slug.
		 *
		 * @var string
		 */
		private $default_provider = 'google';

		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->register_provider( new Jet_Elements_Advanced_Map_Provider_Google() );
			$this->register_provider( new Jet_Elements_Advanced_Map_Provider_Leaflet() );
			$this->register_provider( new Jet_Elements_Advanced_Map_Provider_Mapbox() );

			do_action( 'jet-elements/advanced-map/register-providers', $this );
		}

		/**
		 * Returns singleton.
		 *
		 * @return Jet_Elements_Advanced_Map_Providers
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Register provider.
		 *
		 * @param Jet_Elements_Advanced_Map_Provider_Base $provider Provider instance.
		 */
		public function register_provider( $provider ) {
			if ( ! $provider instanceof Jet_Elements_Advanced_Map_Provider_Base ) {
				return;
			}

			$this->providers[ $provider->get_id() ] = $provider;
		}

		/**
		 * Get provider object.
		 *
		 * @param string $id Provider ID.
		 *
		 * @return Jet_Elements_Advanced_Map_Provider_Base|null
		 */
		public function get_provider( $id ) {
			if ( empty( $id ) ) {
				return null;
			}

			return isset( $this->providers[ $id ] ) ? $this->providers[ $id ] : null;
		}

		/**
		 * Returns providers list for select control.
		 *
		 * @return array
		 */
		public function get_providers_for_controls() {
			$options = array();

			foreach ( $this->providers as $provider ) {
				$options[ $provider->get_id() ] = $provider->get_label();
			}

			return $options;
		}

		/**
		 * Default provider slug.
		 *
		 * @return string
		 */
		public function get_default_provider() {
			return $this->default_provider;
		}
	}
}

if ( ! function_exists( 'jet_elements_advanced_map_providers' ) ) {
	/**
	 * Helper.
	 *
	 * @return Jet_Elements_Advanced_Map_Providers
	 */
	function jet_elements_advanced_map_providers() {
		return Jet_Elements_Advanced_Map_Providers::instance();
	}
}

jet_elements_advanced_map_providers();



