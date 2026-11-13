<?php
/**
 * Jet_Search_Assets class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Assets' ) ) {

	/**
	 * Define Jet_Search_Assets class
	 */
	class Jet_Search_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   Jet_Search_Assets
		 */
		private static $instance = null;

		/**
		 * Localize data.
		 *
		 * @var array
		 */
		public $localize_data = array();

		private $full_deps_enqueued = false;
		/**
		 * Constructor for the class
		 */
		public function init() {

			add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'register_scripts' ), 0 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_preview_scripts' ), 0 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'register_styles' ), 0 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_styles' ), 0 );
			add_action( 'wp_print_footer_scripts', array( $this, 'print_results_item_js_template' ), 0 );
			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'font_styles' ) );
			add_action( 'elementor/preview/enqueue_styles', array( $this, 'font_styles' ) );

			/**
			 * Filters the authentication errors for REST API requests.
			 *
			 * This filter allows specific Jet Search REST API routes by bypassing
			 * authentication errors for them.
			 *
			 * @since 3.5.2
			 */
			add_filter( 'rest_authentication_errors', array( $this, 'allow_jet_search_rest_api_routes' ), 999 );
		}

		/**
		 * Register plugin stylesheets.
		 *
		 * @return void
		 */
		public function register_styles() {

			wp_register_style(
				'jquery-chosen',
				jet_search()->plugin_url( 'assets/lib/chosen/chosen.min.css' ),
				false,
				'1.8.7'
			);

			wp_register_style(
				'jet-search',
				jet_search()->plugin_url( 'assets/css/jet-search.css' ),
				array(),
				jet_search()->get_version()
			);
		}

		/**
		 * Enqueue icon font styles
		 *
		 * @return void
		 */
		public function font_styles() {

			wp_enqueue_style(
				'jet-search-font',
				jet_search()->plugin_url( 'assets/css/lib/jet-search-icons/jet-search-icons.css' ),
				array(),
				jet_search()->get_version()
			);

		}

		// /**
		//  * Enqueue plugin stylesheets.
		//  *
		//  * @return void
		//  */
		public function enqueue_styles() {
			wp_enqueue_style( 'jet-search' );
			wp_enqueue_style( 'jquery-chosen' );
		}

		/**
		 * Register plugin scripts
		 *
		 * @return void
		 */
		public function register_scripts() {

			// Register vendor chosen.jquery.min.js script (https://github.com/harvesthq/chosen/)
			wp_register_script(
				'jquery-chosen',
				jet_search()->plugin_url( 'assets/lib/chosen/chosen.jquery.min.js' ),
				array( 'jquery' ),
				'1.8.7',
				true
			);

			wp_register_script(
				'imagesLoaded',
				'/wp-includes/js/imagesloaded.min.js',
				array('jquery'),
				false,
				false
			);

			wp_register_script(
				'jet-plugins',
				jet_search()->plugin_url( 'assets/lib/jet-plugins/jet-plugins.js' ),
				array( 'jquery' ),
				'1.0.0',
				true
			);

			wp_register_script(
				'jet-search',
				jet_search()->plugin_url( 'assets/js/jet-search.js' ),
				array( 'jquery', 'wp-util', 'imagesLoaded', 'jquery-chosen', 'jet-plugins' ),
				jet_search()->get_version(),
				true
			);

		}

		public function get_localize_data( $settings = null ) {

			if ( empty( $this->localize_data ) ) {

				$ajax_action        = jet_search_ajax_handlers()->get_ajax_action();
				$use_session        = get_option( 'jet_search_suggestions_use_session' );
				$suggestions_limits = $this->get_ajax_search_suggestions_limits();

				//Ajax search
				$this->localize_data['rest_api_url']                = get_rest_url() . "jet-search/v1/search-posts";
				$this->localize_data['action']                      = $ajax_action;
				$this->localize_data['nonce']                       = wp_create_nonce( $ajax_action );
				$this->localize_data['sumbitOnEnter']               = true;
				$this->localize_data['ajaxSearchSuggestionsLimits'] = $suggestions_limits;
				$this->localize_data['ajaxurl']                     = esc_url( admin_url( 'admin-ajax.php' ) );

				//Ajax search suggestions
				$this->localize_data['searchSuggestions'] = array(
					'ajaxurl'                      => esc_url( admin_url( 'admin-ajax.php' ) ),
					'get_suggestions_rest_api_url' => get_rest_url() . "jet-search/v1/get-suggestions",
					'add_suggestions_rest_api_url' => get_rest_url() . "jet-search/v1/form-add-suggestion",
					'get_action'                   => 'get_form_suggestions',
					'add_action'                   => 'add_form_suggestion',
					'nonce_rest'                   => wp_create_nonce( 'wp_rest' ),
					'nonce'                        => wp_create_nonce( 'form_suggestions' ),
					'use_session'                  => $use_session
				);

				/**
				 * Apply the filter to determine whether to use the legacy AJAX method or REST API for search functionality.
				 *
				 * @since 3.5.2
				 */
				$use_legacy = apply_filters( 'jet-ajax-search/assets/localize-data/use-legacy-ajax', false );

				/**
				 * Retrieves the request settings.
				 *
				 * @since 3.5.3
				 */
				$request_options = get_option( 'jet_ajax_search_request_settings' );
				$request_type    = '';

				if ( false != $request_options ) {
					$request_type = isset( $request_options['request_type'] ) ? $request_options['request_type'] : '';
				}

				if ( $use_legacy || 'ajax' === $request_type ) {
					$this->localize_data['rest_api_url'] = $this->localize_data['ajaxurl'];
					$this->localize_data['searchSuggestions']['get_suggestions_rest_api_url'] = $this->localize_data['ajaxurl'];
					$this->localize_data['searchSuggestions']['add_suggestions_rest_api_url'] = $this->localize_data['ajaxurl'];
				}
			}

			$show_search_suggestions   = isset( $settings['show_search_suggestions'] ) ? $settings['show_search_suggestions'] : '';
			$search_suggestions_source = isset( $settings['search_suggestions_source'] ) ? $settings['search_suggestions_source'] : '';
			$preview_suggestions       = isset( $settings['preview_suggestions'] ) ? $settings['preview_suggestions'] : false;

			if ( filter_var( $preview_suggestions, FILTER_VALIDATE_BOOLEAN ) || ( 'popular' === $search_suggestions_source && filter_var( $show_search_suggestions, FILTER_VALIDATE_BOOLEAN ) && ! isset( $this->localize_data['ajaxSearchPopularSuggestions'] ) ) ) {
				$suggestions_limits = $this->get_ajax_search_suggestions_limits();

				$suggestions = array();

				$data = array(
					'data[list_type]' => 'popular',
					'data[value]'     => '',
					'data[limit]'     => $suggestions_limits,
					'nonce'           => $this->localize_data['searchSuggestions']['nonce']
				);

				$data = build_query( $data );

				$rest_api_url = $this->localize_data['searchSuggestions']['get_suggestions_rest_api_url'] . '?action=' . $this->localize_data['searchSuggestions']['get_action'] . "&" . $data;

				$response = wp_remote_request( $rest_api_url );

				if ( ! is_wp_error( $response ) ) {
					$code = (int) wp_remote_retrieve_response_code( $response );

					if ( $code === 200 ) {
						$body = wp_remote_retrieve_body( $response );

						$suggestions = json_decode( $body, true );

						$suggestions_list = array();

						if ( ! empty( $suggestions ) ) {
							foreach ( $suggestions as $item ) {
								if ( isset( $item["name"] ) ) {
									$suggestions_list[] = $item["name"];
								}
							}
						}

						$this->localize_data['ajaxSearchPopularSuggestions'] = $suggestions_list;
					}
				}
			}

			$this->localize_data = apply_filters( 'jet-ajax-search/assets/localize-data', $this->localize_data );

			return $this->localize_data;

		}

		public function get_ajax_search_suggestions_limits() {
			return apply_filters( 'jet-ajax-search/search-suggestions-limits', 20 );
		}

		// /**
		//  * Enqueue plugin scripts
		//  *
		//  * @return void
		//  */
		public function enqueue_scripts( $settings = '' ) {

			if ( !$this->full_deps_enqueued ) {
				if ( isset( $settings['show_search_category_list'] ) && ( true === $settings['show_search_category_list'] || 'yes' === $settings['show_search_category_list'] ) ) {
					$deps = array( 'jquery', 'wp-util', 'imagesLoaded', 'jquery-chosen', 'jet-plugins' );
					wp_deregister_script( 'jet-search' );
					wp_dequeue_script( 'jet-search' );
					$this->full_deps_enqueued = true;
				} else {
					$deps = array( 'jquery', 'wp-util', 'imagesLoaded', 'jet-plugins' );
				}

				wp_enqueue_script(
					'jet-search',
					jet_search()->plugin_url( 'assets/js/jet-search.js' ),
					$deps,
					jet_search()->get_version(),
					true
				);
			}

			wp_localize_script( 'jet-search', 'jetSearchSettings', $this->get_localize_data( $settings ) );
		}

		public function enqueue_preview_scripts() {

			wp_enqueue_script(
				'jet-search',
				jet_search()->plugin_url( 'assets/js/jet-search.js' ),
				array( 'jquery', 'wp-util', 'imagesLoaded', 'jquery-chosen' ),
				jet_search()->get_version(),
				true
			);

			$settings['preview_suggestions'] = true;

			wp_localize_script( 'jet-search', 'jetSearchSettings', $this->get_localize_data( $settings ) );

		}

		/**
		 * Enqueue editor scripts
		 */
		public function editor_scripts() {
			wp_enqueue_script(
				'jet-search-editor',
				jet_search()->plugin_url( 'assets/js/jet-search-editor.js' ),
				array( 'jquery' ),
				jet_search()->get_version(),
				true
			);
		}

		/**
		 * Enqueue editor styles
		 *
		 * @return void
		 */
		public function editor_styles() {

			if ( is_rtl() ) {
				wp_enqueue_style(
					'jet-search-editor',
					jet_search()->plugin_url( 'assets/css/jet-search-editor.css' ),
					array(),
					jet_search()->get_version()
				);

				$ui_theme = \Elementor\Core\Settings\Manager::get_settings_managers( 'editorPreferences' )->get_model()->get_settings( 'ui_theme' );

				if ( 'dark' === $ui_theme ) {
					wp_add_inline_style( 'jet-search-editor', '.rtl .jet-search-text-align-control{--jet-search-text-align-control-border-color:#64666a}' );
				}
			}

		}

		/**
		 * Print results item js template.
		 */
		public function print_results_item_js_template() {
			if ( ! wp_script_is( 'jet-search', 'enqueued' ) ) {
				return;
			}

			ob_start();
			include jet_search()->get_template( 'jet-ajax-search/global/results-item.php' );
			$content_search = ob_get_clean();

			ob_start();
			include jet_search()->get_template( 'jet-search-suggestions/global/focus-suggestion-item.php' );
			$content_search_suggestions_focus = ob_get_clean();

			ob_start();
			include jet_search()->get_template( 'jet-search-suggestions/global/inline-suggestion-item.php' );
			$content_search_suggestions_inline = ob_get_clean();

			ob_start();
			include jet_search()->get_template( 'jet-ajax-search/global/inline-suggestion-item.php' );
			$content_ajax_search_suggestions_inline = ob_get_clean();

			ob_start();
			include jet_search()->get_template( 'jet-ajax-search/global/results-suggestion-item.php' );
			$content_ajax_search_suggestions_results = ob_get_clean();

			if ( ! empty( $content_search ) ) {
				$content_search = apply_filters( 'jet-ajax-search/results_item_js_template' , $content_search );

				$search_output = sprintf(
					'<script type="text/html" id="tmpl-jet-ajax-search-results-item">%s</script>',
					$content_search
				);

				echo $search_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( ! empty( $content_search_suggestions_focus ) ) {
				$content_search_suggestions_focus  = apply_filters( 'jet-search-suggestions/focus_suggestion_item_js_template' , $content_search_suggestions_focus );

				$search_suggestions_focus_output = sprintf(
					'<script type="text/html" id="tmpl-jet-search-focus-suggestion-item">%s</script>',
					$content_search_suggestions_focus
				);

				echo $search_suggestions_focus_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( ! empty( $content_search_suggestions_inline ) ) {
				$content_search_suggestions_inline = apply_filters( 'jet-search-suggestions/inline_suggestion_item_js_template' , $content_search_suggestions_inline );

				$search_suggestions_inline_output = sprintf(
					'<script type="text/html" id="tmpl-jet-search-inline-suggestion-item">%s</script>',
					$content_search_suggestions_inline
				);

				echo $search_suggestions_inline_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( ! empty( $content_ajax_search_suggestions_inline ) ) {
				$content_ajax_search_suggestions_inline = apply_filters( 'jet-ajax-search/inline_suggestion_item_js_template' , $content_ajax_search_suggestions_inline );

				$content_ajax_search_suggestions_inline_output = sprintf(
					'<script type="text/html" id="tmpl-jet-ajax-search-inline-suggestion-item">%s</script>',
					$content_ajax_search_suggestions_inline
				);

				echo $content_ajax_search_suggestions_inline_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( ! empty( $content_ajax_search_suggestions_results ) ) {
				$content_ajax_search_suggestions_results = apply_filters( 'jet-ajax-search/results_suggestion_item_js_template' , $content_ajax_search_suggestions_results );

				$content_ajax_search_suggestions_results_output = sprintf(
					'<script type="text/html" id="tmpl-jet-ajax-search-results-suggestion-item">%s</script>',
					$content_ajax_search_suggestions_results
				);

				echo $content_ajax_search_suggestions_results_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Allows specific Jet Search REST API routes.
		 *
		 * This function checks if the current request is for one of the allowed Jet Search REST API routes.
		 * If it is, the function returns true to allow the route. Otherwise, it returns any existing errors.
		 *
		 * @since 3.5.2
		 * @param mixed $errors The current error status. Can be boolean or WP_Error object.
		 * @return mixed True if the route is allowed, otherwise the original errors.
		 */
		public function allow_jet_search_rest_api_routes( $errors ) {

			if ( true === $errors ) {
				return $errors;
			}

			$allowed_routes = array(
				'/jet-search/v1/search-posts',
				'/jet-search/v1/get-suggestions',
				'/jet-search/v1/form-add-suggestion',
			);

			$current_route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? $GLOBALS['wp']->query_vars['rest_route'] : '';

			if ( ! empty( $current_route ) || '/' != $current_route ) {
				$current_route = untrailingslashit( $current_route );
			}

			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {

				if ( in_array( $current_route, $allowed_routes ) ) {
					return true;
				}
			}

			return $errors;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return Jet_Search_Assets
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
 * Returns instance of Jet_Search_Assets
 *
 * @return Jet_Search_Assets
 */
function jet_search_assets() {
	return Jet_Search_Assets::get_instance();
}
