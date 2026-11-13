<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Elements_Settings' ) ) {

	/**
	 * Define Jet_Elements_Settings class
	 */
	class Jet_Elements_Settings {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * [$key description]
		 * @var string
		 */
		public $key = 'jet-elements-settings';

		/**
		 * Access Token transient option key
		 *
		 * @var string
		 */
		private $insta_updated_access_token_key = 'jet_elements_instagram_updated_access_token';

		/**
		 * [$builder description]
		 * @var null
		 */
		public $builder  = null;

		/**
		 * [$settings description]
		 * @var null
		 */
		public $settings = null;

		/**
		 * Available Widgets array
		 *
		 * @var array
		 */
		public $avaliable_widgets = [];

		/**
		 * [$default_avaliable_extensions description]
		 * @var [type]
		 */
		public $default_avaliable_extensions = [
			'section_parallax'  => 'true',
		];

		/**
		 * [$settings_page_config description]
		 * @var [type]
		 */
		public $settings_page_config = [];

		/**
		 * Available Widgets Slugs
		 *
		 * @var array
		 */
		public $avaliable_widgets_slugs = [];

		/**
		 * Init page
		 */
		public function init() {

			foreach ( glob( jet_elements()->plugin_path( 'includes/addons/' ) . '*.php' ) as $file ) {
				$data = get_file_data( $file, array( 'class' => 'Class', 'name' => 'Name', 'slug' => 'Slug' ) );

				$slug = basename( $file, '.php' );
				$this->avaliable_widgets[ $slug ] = $data['name'];
				$this->avaliable_widgets_slugs[]  = $data['slug'];
			}

			add_action(
				'jet-styles-manager/compatibility/register-plugin',
				array( $this, 'register_for_styles_manager' )
			);

			// Refresh Instagram Access Token
			add_action( 'admin_init', array( $this, 'refresh_instagram_access_token' ) );
		}

		/**
		 * Register jet-elements plugin for styles manager
		 *
		 * @param  object $compatibility_manager JetStyleManager->compatibility instance
		 * @return void
		 */
		public function register_for_styles_manager( $compatibility_manager ) {
			$compatibility_manager->register_plugin( 'jet-elements', (int) $this->get( 'widgets_load_level', 100 ) );
		}

		/**
		 * [generate_frontend_config_data description]
		 * @return [type] [description]
		 */
		public function generate_frontend_config_data() {

			$default_active_widgets = [];

			foreach ( $this->avaliable_widgets as $slug => $name ) {

				$avaliable_widgets[] = [
					'label' => $name,
					'value' => $slug,
				];

				$default_active_widgets[ $slug ] = 'true';
			}

			$active_widgets = $this->get( 'avaliable_widgets', $default_active_widgets );

			$avaliable_extensions = [
				[
					'label' => esc_html__( 'Section Parallax Extension', 'jet-elements' ),
					'value' => 'section_parallax',
				],
			];

			$active_extensions = $this->get( 'avaliable_extensions', $this->default_avaliable_extensions );

			$rest_api_url = apply_filters( 'jet-elements/rest/frontend/url', get_rest_url() );

			$map_provider_options = [];
			$geocode_provider_options = [];

			foreach ( jet_elements_advanced_map_providers()->get_providers_for_controls() as $value => $label ) {
				$map_provider_options[] = [
					'label' => $label,
					'value' => $value,
				];
			}

			foreach ( jet_elements_advanced_map_geocode_providers()->get_providers_for_controls() as $value => $label ) {
				$geocode_provider_options[] = [
					'label' => $label,
					'value' => $value,
				];
			}

			$this->settings_page_config = [
				'messages' => [
					'saveSuccess' => esc_html__( 'Saved', 'jet-elements' ),
					'saveError'   => esc_html__( 'Error', 'jet-elements' ),
				],
				'settingsApiUrl' => '/jet-elements-api/v1/plugin-settings',
				'settingsData' => [
					'svg_uploads'             => [
						'value' => $this->get( 'svg_uploads', 'enabled' ),
					],
					'widgets_load_level'      => [
						'value'   => $this->get( 'widgets_load_level', 100 ),
						'options' => [
							[
								'label' => 'None',
								'value' => 0,
							],
							[
								'label' => 'Low',
								'value' => 25,
							],
							[
								'label' => 'Medium',
								'value' => 50,
							],
							[
								'label' => 'Advanced',
								'value' => 75,
							],
							[
								'label' => 'Full',
								'value' => 100,
							],
						],
					],
					'api_key'                 => [
						'value' => $this->get( 'api_key', '' ),
					],
					'disable_api_js'          => [
						'value' => $this->get( 'disable_api_js', [ 'disable' => 'false' ] ),
					],
					'map_provider'            => [
						'value'   => $this->get( 'map_provider', jet_elements_advanced_map_providers()->get_default_provider() ),
						'options' => $map_provider_options,
					],
					'geocode_provider'        => [
						'value'   => $this->get( 'geocode_provider', jet_elements_advanced_map_geocode_providers()->get_default_provider() ),
						'options' => $geocode_provider_options,
					],
					'mapbox_access_token'     => [
						'value' => $this->get( 'mapbox_access_token', '' ),
					],
					'use_geocoding_key'       => [
						'value' => $this->get( 'use_geocoding_key', [ 'disable' => 'false' ] ),
					],
					'geocoding_key'           => [
						'value' => $this->get( 'geocoding_key', '' ),
					],
					'bing_key'                => [
						'value' => $this->get( 'bing_key', '' ),
					],
					'mailchimp-api-key'       => [
						'value' => $this->get( 'mailchimp-api-key', '' ),
					],
					'mailchimp-list-id'       => [
						'value' => $this->get( 'mailchimp-list-id', '' ),
					],
					'mailchimp-double-opt-in' => [
						'value' => $this->get( 'mailchimp-double-opt-in', false ),
					],
					'insta_access_token'      => [
						'value' => $this->get( 'insta_access_token', '' ),
					],
					'insta_business_access_token' => [
						'value' => $this->get( 'insta_business_access_token', '' ),
					],
					'insta_business_user_id' => [
						'value' => $this->get( 'insta_business_user_id', '' ),
					],
					'weather_api_key'         => [
						'value' => $this->get( 'weather_api_key', '' ),
					],
					'openweathermap_api_key'         => [
						'value' => $this->get( 'openweathermap_api_key', '' ),
					],
					'avaliable_widgets'       => [
						'value'   => $active_widgets,
						'options' => $avaliable_widgets,
					],
					'avaliable_extensions'    => [
						'value'   => $active_extensions,
						'options' => $avaliable_extensions,
					],
				],
			];

			return $this->settings_page_config;
		}

		/**
		 * Return settings page URL
		 *
		 * @param  string $subpage
		 * @return string
		 */
		public function get_settings_page_link( $subpage = 'general' ) {

			return add_query_arg(
				array(
					'page'    => 'jet-dashboard-settings-page',
					'subpage' => 'jet-elements-' . $subpage . '-settings',
				),
				esc_url( admin_url( 'admin.php' ) )
			);

		}

		/**
		 * [get description]
		 * @param  [type]  $setting [description]
		 * @param  boolean $default [description]
		 * @return [type]           [description]
		 */
		/** Validate raw storage/API values; escaping belongs at the output boundary. */
		public function sanitize( $data ) {
			if ( ! is_array( $data ) ) { return array(); }
			$result = array();
			$text_keys = array( 'api_key', 'mapbox_access_token', 'geocoding_key', 'bing_key', 'mailchimp-api-key', 'mailchimp-list-id', 'insta_access_token', 'insta_business_access_token', 'insta_business_user_id', 'weather_api_key', 'openweathermap_api_key' );
			$boolean = function( $value ) {
				if ( in_array( $value, array( true, 'true', 1, '1' ), true ) ) { return 'true'; }
				if ( in_array( $value, array( false, 'false', 0, '0', '' ), true ) ) { return 'false'; }
				return null;
			};
			foreach ( $data as $key => $value ) {
				if ( in_array( $key, $text_keys, true ) ) {
					if ( is_string( $value ) ) { $result[ $key ] = sanitize_text_field( $value ); }
				} elseif ( 'widgets_load_level' === $key ) {
					if ( ( is_int( $value ) || is_string( $value ) ) && in_array( (string) $value, array( '0', '25', '50', '75', '100' ), true ) ) { $result[ $key ] = (int) $value; }
				} elseif ( in_array( $key, array( 'svg_uploads', 'jet_templates' ), true ) ) {
					if ( in_array( $value, array( 'enabled', 'disabled' ), true ) ) { $result[ $key ] = $value; }
				} elseif ( 'mailchimp-double-opt-in' === $key ) {
					$normalized = $boolean( $value );
					if ( null !== $normalized ) { $result[ $key ] = 'true' === $normalized; }
				} elseif ( in_array( $key, array( 'map_provider', 'geocode_provider' ), true ) ) {
					$registry = 'map_provider' === $key ? jet_elements_advanced_map_providers() : jet_elements_advanced_map_geocode_providers();
					if ( is_string( $value ) && array_key_exists( $value, $registry->get_providers_for_controls() ) ) { $result[ $key ] = $value; }
				} elseif ( 'use_geocoding_key' === $key ) {
					$normalized = $boolean( $value );
					if ( null !== $normalized ) { $result[ $key ] = $normalized; }
				} elseif ( in_array( $key, array( 'avaliable_widgets', 'avaliable_extensions', 'disable_api_js' ), true ) && is_array( $value ) ) {
					$allowed = 'avaliable_widgets' === $key ? array_keys( $this->avaliable_widgets ) : ( 'avaliable_extensions' === $key ? array_keys( $this->default_avaliable_extensions ) : array( 'disable' ) );
					$result[ $key ] = array();
					foreach ( $value as $name => $flag ) {
						$normalized = $boolean( $flag );
						if ( in_array( $name, $allowed, true ) && null !== $normalized ) { $result[ $key ][ $name ] = $normalized; }
					}
				}
			}
			return $result;
		}

		public function get( $setting, $default = false ) {

			if ( null === $this->settings ) {
				$this->settings = get_option( $this->key, array() );
			}

			$settings = $this->sanitize( $this->settings );
			return isset( $settings[ $setting ] ) ? $settings[ $setting ] : $default;

		}

		/**
		 * Refresh Instagram Access Token
		 *
		 * @return void
		 */
		public function refresh_instagram_access_token() {
			$access_token = $this->get( 'insta_access_token' );
			$access_token = trim( $access_token );

			if ( empty( $access_token ) ) {
				return;
			}

			$updated = get_transient( $this->insta_updated_access_token_key );

			if ( ! empty( $updated ) ) {
				return;
			}

			$url = add_query_arg(
				array(
					'grant_type'   => 'ig_refresh_token',
					'access_token' => $access_token,
				),
				'https://graph.instagram.com/refresh_access_token'
			);

			$response = wp_remote_get( $url );

			if ( ! $response || is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				set_transient( $this->insta_updated_access_token_key, 'error', DAY_IN_SECONDS );
				return;
			}

			$body = wp_remote_retrieve_body( $response );

			if ( ! $body ) {
				set_transient( $this->insta_updated_access_token_key, 'error', DAY_IN_SECONDS );
				return;
			}

			$body = json_decode( $body, true );

			if ( empty( $body['access_token'] ) || empty( $body['expires_in'] ) ) {
				set_transient( $this->insta_updated_access_token_key, 'error', DAY_IN_SECONDS );
				return;
			}

			set_transient( $this->insta_updated_access_token_key, 'updated', 30 * DAY_IN_SECONDS );
		}


		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

/**
 * Returns instance of Jet_Elements_Settings
 *
 * @return object
 */
function jet_elements_settings() {
	return Jet_Elements_Settings::get_instance();
}

jet_elements_settings()->init();
