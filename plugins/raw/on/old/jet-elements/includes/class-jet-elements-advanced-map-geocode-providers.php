<?php
/**
 * Advanced Map geocoding providers registry.
 *
 * @package JetElements
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Geocode_Provider_Base' ) ) {

	abstract class Jet_Elements_Advanced_Map_Geocode_Provider_Base {

		abstract public function get_id();

		abstract public function get_label();

		abstract public function get_location_data( $location );

		protected function remote_get_json( $url, $args = array() ) {

			if ( ! $url ) {
				return false;
			}

			$args = wp_parse_args(
				$args,
				array(
					'timeout' => 20,
				)
			);

			$response = wp_remote_get( $url, $args );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = wp_remote_retrieve_body( $response );

			if ( empty( $body ) ) {
				return array();
			}

			$data = json_decode( $body, true );

			return null === $data ? array() : $data;
		}

		protected function get_user_agent() {
			return 'JetElements/' . jet_elements()->get_version();
		}

		protected function error( $message, $code = 'jet-elements-geocode' ) {
			return new \WP_Error( $code, $message );
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Geocode_Provider_Google' ) ) {

	class Jet_Elements_Advanced_Map_Geocode_Provider_Google extends Jet_Elements_Advanced_Map_Geocode_Provider_Base {

		public function get_id() {
			return 'google';
		}

		public function get_label() {
			return __( 'Google', 'jet-elements' );
		}

		protected function get_api_key() {
			$api_key           = jet_elements_settings()->get( 'api_key' );
			$use_geocoding_key = jet_elements_settings()->get( 'use_geocoding_key' );
			$geocoding_key     = jet_elements_settings()->get( 'geocoding_key' );
			$map_provider      = jet_elements_settings()->get( 'map_provider', 'google' );

			if ( 'google' !== $map_provider ) {
				$use_geocoding_key = 'true';
				$api_key           = false;
			}

			if ( 'true' === $use_geocoding_key && $geocoding_key ) {
				return $geocoding_key;
			}

			return $api_key;
		}

		public function get_location_data( $location ) {

			if ( ! $location ) {
				return false;
			}

			$api_key = $this->get_api_key();

			if ( ! $api_key ) {
				return $this->error( __( 'Please set Google Geocoding API key before using this widget.', 'jet-elements' ), 'jet-elements-geocode-google' );
			}

			$url = add_query_arg(
				array(
					'address'  => urlencode( $location ),
					'key'      => urlencode( $api_key ),
					'language' => substr( get_bloginfo( 'language' ), 0, 2 ),
				),
				'https://maps.googleapis.com/maps/api/geocode/json'
			);

			$data = $this->remote_get_json( $url );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( isset( $data['error_message'] ) ) {
				return $this->error( $data['error_message'], 'jet-elements-geocode-google' );
			}

			if ( empty( $data['results'][0]['geometry']['location'] ) ) {
				return false;
			}

			return $data['results'][0]['geometry']['location'];
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Geocode_Provider_OpenStreetMap' ) ) {

	class Jet_Elements_Advanced_Map_Geocode_Provider_OpenStreetMap extends Jet_Elements_Advanced_Map_Geocode_Provider_Base {

		public function get_id() {
			return 'openstreetmap';
		}

		public function get_label() {
			return __( 'OpenStreetMap', 'jet-elements' );
		}

		public function get_location_data( $location ) {

			if ( ! $location ) {
				return false;
			}

			$url = add_query_arg(
				array(
					'q'      => urlencode( $location ),
					'format' => 'json',
					'limit'  => 1,
				),
				'https://nominatim.openstreetmap.org/search'
			);

			$data = $this->remote_get_json(
				$url,
				array(
					'headers' => array(
						'User-Agent' => $this->get_user_agent(),
					),
				)
			);

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( empty( $data[0]['lat'] ) || empty( $data[0]['lon'] ) ) {
				return false;
			}

			return array(
				'lat' => (float) $data[0]['lat'],
				'lng' => (float) $data[0]['lon'],
			);
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Geocode_Provider_Photon' ) ) {

	class Jet_Elements_Advanced_Map_Geocode_Provider_Photon extends Jet_Elements_Advanced_Map_Geocode_Provider_Base {

		public function get_id() {
			return 'photon';
		}

		public function get_label() {
			return __( 'Photon', 'jet-elements' );
		}

		public function get_location_data( $location ) {

			if ( ! $location ) {
				return false;
			}

			$url = add_query_arg(
				array(
					'q'     => urlencode( $location ),
					'limit' => 1,
				),
				'https://photon.komoot.io/api/'
			);

			$data = $this->remote_get_json( $url );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( empty( $data['features'][0]['geometry']['coordinates'] ) ) {
				return false;
			}

			$coords = $data['features'][0]['geometry']['coordinates'];

			return array(
				'lat' => (float) $coords[1],
				'lng' => (float) $coords[0],
			);
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Geocode_Provider_Bing' ) ) {

	class Jet_Elements_Advanced_Map_Geocode_Provider_Bing extends Jet_Elements_Advanced_Map_Geocode_Provider_Base {

		public function get_id() {
			return 'bing';
		}

		public function get_label() {
			return __( 'Bing', 'jet-elements' );
		}

		protected function get_api_key() {
			return jet_elements_settings()->get( 'bing_key', '' );
		}

		public function get_location_data( $location ) {

			if ( ! $location ) {
				return false;
			}

			$api_key = $this->get_api_key();

			if ( ! $api_key ) {
				return $this->error( __( 'Please set Bing Maps API key before using this widget.', 'jet-elements' ), 'jet-elements-geocode-bing' );
			}

			$url = add_query_arg(
				array(
					'query' => urlencode( $location ),
					'key'   => urlencode( $api_key ),
				),
				'https://dev.virtualearth.net/REST/v1/Locations'
			);

			$data = $this->remote_get_json( $url );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			if ( empty( $data['resourceSets'][0]['resources'][0]['point']['coordinates'] ) ) {
				return false;
			}

			$coords = $data['resourceSets'][0]['resources'][0]['point']['coordinates'];

			return array(
				'lat' => (float) $coords[0],
				'lng' => (float) $coords[1],
			);
		}
	}
}

if ( ! class_exists( 'Jet_Elements_Advanced_Map_Geocode_Providers' ) ) {

	class Jet_Elements_Advanced_Map_Geocode_Providers {

		private static $instance = null;

		private $providers = array();

		private $default_provider = 'google';

		private function __construct() {
			$this->register_provider( new Jet_Elements_Advanced_Map_Geocode_Provider_Google() );
			$this->register_provider( new Jet_Elements_Advanced_Map_Geocode_Provider_OpenStreetMap() );
			$this->register_provider( new Jet_Elements_Advanced_Map_Geocode_Provider_Photon() );
			$this->register_provider( new Jet_Elements_Advanced_Map_Geocode_Provider_Bing() );
		}

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function register_provider( $provider ) {
			if ( ! $provider instanceof Jet_Elements_Advanced_Map_Geocode_Provider_Base ) {
				return;
			}

			$this->providers[ $provider->get_id() ] = $provider;
		}

		public function get_provider( $id ) {
			return isset( $this->providers[ $id ] ) ? $this->providers[ $id ] : null;
		}

		public function get_active_provider() {
			$provider_id = jet_elements_settings()->get( 'geocode_provider', $this->default_provider );

			return $this->get_provider( $provider_id ) ?: $this->get_provider( $this->default_provider );
		}

		public function get_default_provider() {
			return $this->default_provider;
		}

		public function get_providers_for_controls() {
			$result = array();

			foreach ( $this->providers as $provider ) {
				$result[ $provider->get_id() ] = $provider->get_label();
			}

			return $result;
		}
	}
}

if ( ! function_exists( 'jet_elements_advanced_map_geocode_providers' ) ) {
	function jet_elements_advanced_map_geocode_providers() {
		return Jet_Elements_Advanced_Map_Geocode_Providers::instance();
	}
}

jet_elements_advanced_map_geocode_providers();


