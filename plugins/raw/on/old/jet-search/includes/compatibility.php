<?php
/**
 * Jet_Search_Compatibility class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Compatibility' ) ) {

	/**
	 * Define Jet_Search_Compatibility class
	 */
	class Jet_Search_Compatibility {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   Jet_Search_Compatibility
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {
			// WPML Compatibility
			if ( defined( 'WPML_ST_VERSION' ) ) {
				add_filter( 'wpml_elementor_widgets_to_translate', array( $this, 'add_wpml_translatable_nodes' ) );
			}

			// Polylang Compatibility
			if ( class_exists( 'Polylang' ) || class_exists( 'Polylang_Pro' ) ) {
				add_filter( 'pll_home_url_white_list',                    array( $this, 'modify_pll_home_url_white_list' ) );
				add_action( 'jet-search/ajax-search/search-query',        array( $this, 'modify_pll_lang_search_query' ), 10, 2 );
				add_action( 'jet-search/search-suggestions/search-query', array( $this, 'modify_pll_lang_search_query' ), 10, 2 );
				add_filter( 'jet-search/search-form/home-url',            array( $this, 'modify_search_form_home_url' ) );
			}

			// WooCommerce Compatibility
			if ( class_exists( 'WooCommerce' ) ) {
				add_action( 'jet-search/ajax-search/add-custom-controls', array( $this, 'add_custom_controls' ), 10, 2 );
				add_filter( 'jet-search/ajax-search/data-settings',       array( $this, 'modify_allowed_settings' ), 10, 2 );
				add_filter( 'jet-search/ajax-search/query-settings',      array( $this, 'modify_query_settings' ), 10, 2 );
				add_action( 'jet-search/ajax-search/search-query',        array( $this, 'modify_search_query' ), 10, 2 );
				add_action( 'jet-search/search-suggestions/search-query', array( $this, 'modify_search_query' ), 10, 2 );
			}

			// Weglot Compatibility
			if ( defined( 'WEGLOT_VERSION' ) || function_exists( 'weglot_init' ) || function_exists( 'weglot_get_current_language' ) ) {
				add_filter( 'jet-search/search-form/home-url', array( $this, 'modify_weglot_home_url' ), 10, 1 );
				add_filter( 'redirect_canonical',              array( $this, 'modify_prevent_canonical' ), 10, 2 );
			}

			add_action( 'jet-search/ajax-search/add-custom-controls',        array( $this, 'add_custom_controls' ), 10, 2 );
			add_action( 'jet-search/ajax-search-bricks/add-custom-controls', array( $this, 'add_bricks_custom_controls' ), 10, 2 );

			// JetEngine compatibility
			if ( function_exists( 'jet_engine' ) ) {
				require_once jet_search()->plugin_path( 'includes/compatibility/jet-engine/manager.php' );

				new Jet_Search_Compatibility_JE();
			}
		}

		/**
		 * Modify the search form home URL to respect the current Weglot language
		 *
		 * @param  string $url Base URL used by the search form
		 * @return string      Corrected URL with the current Weglot language slug
		 */
		public function modify_weglot_home_url( $url ) {
			$home    = trailingslashit( home_url() );
			$current = function_exists( 'weglot_get_current_language' ) ? (string) weglot_get_current_language() : '';
			$default = function_exists( 'weglot_get_original_language' ) ? (string) weglot_get_original_language() : '';

			$request_uri = '';

			if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
				$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			}

			if ( ( ! $current || $current === $default ) && $request_uri ) {
				$path = strtok( $request_uri, '?' );

				if ( preg_match( '#^/([a-z]{2}(?:-[A-Z]{2})?)(/|$)#', $path, $m ) ) {
					$current = $m[1];
				}

			}

			if ( $current && $default && $current !== $default ) {
				return rtrim( $home, '/' ) . '/' . $current . '/';
			}

			return $home;
		}

		/**
		 * Modify the canonical redirect behavior
		 *
		 * @param  string $redirect_url   The canonical URL WordPress wants to redirect to
		 * @param  string $requested_url  The original requested URL
		 * @return string|false           Redirect URL or false to disable redirect
		 */
		public function modify_prevent_canonical( $redirect_url, $requested_url ) {

			if ( is_admin() ) {
				return $redirect_url;
			}

			if ( isset( $_GET['jet_ajax_search_settings'] ) || isset( $_GET['jsearch'] ) ) { // phpcs:ignore
				return false;
			}

			return $redirect_url;
		}

		/**
		 * Add wpml translation nodes
		 *
		 * @param array $nodes_to_translate
		 *
		 * @return array
		 */
		public function add_wpml_translatable_nodes( $nodes_to_translate ) {

			$nodes_to_translate['jet-ajax-search'] = array(
				'conditions' => array( 'widgetType' => 'jet-ajax-search' ),
				'fields'     => array(
					array(
						'field'       => 'search_placeholder_text',
						'type'        => esc_html__( 'Jet Ajax Search: Placeholder Text', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'search_submit_label',
						'type'        => esc_html__( 'Jet Ajax Search: Submit Button Label', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'search_category_select_placeholder',
						'type'        => esc_html__( 'Jet Ajax Search: Select Placeholder', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'results_counter_text',
						'type'        => esc_html__( 'Jet Ajax Search: Results Counter Text', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'full_results_btn_text',
						'type'        => esc_html__( 'Jet Ajax Search: Full Results Button Text', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'negative_search',
						'type'        => esc_html__( 'Jet Ajax Search: Negative search results', 'jet-search' ),
						'editor_type' => 'LINE',
					),
					array(
						'field'       => 'server_error',
						'type'        => esc_html__( 'Jet Ajax Search: Technical error', 'jet-search' ),
						'editor_type' => 'LINE',
					),
				),
			);

			return $nodes_to_translate;
		}

		/**
		 * Modify the white list of the Polylang 'home_url' filter
		 *
		 * @since  1.1.0
		 * @param  array $list
		 * @return array
		 */
		public function modify_pll_home_url_white_list( $list = array() ) {

			$template_path = jet_search()->plugin_path( 'templates/jet-ajax-search' );
			$template_path = ( false === strpos( $template_path, '\\' ) ) ? $template_path : str_replace( '/', '\\', $template_path );

			$list[] = array(
				'file' => $template_path,
			);

			return $list;
		}

		public function modify_pll_lang_search_query( $instance, $args ) {

			$lang = get_locale();

			if ( strlen( $lang ) > 0 ) {
				$lang = explode( '_', $lang )[0];
				$instance->search_query['lang'] = $lang;
			}

		}

		public function modify_search_form_home_url( $url ) {

			if ( function_exists( 'pll_current_language' ) ) {

				$current_lang_slug = pll_current_language( 'slug' );
				$default_lang      = pll_default_language( 'slug' );

				if ( $current_lang_slug !== $default_lang ) {
					return trailingslashit( home_url() ) . $current_lang_slug;
				}
			}

			return $url;
		}

		public function get_current_lang() {
			$lang = '';

			if ( class_exists( 'Polylang' ) || class_exists( 'Polylang_Pro' ) || defined( 'WPML_ST_VERSION' ) ) {
				$lang = get_locale();

				if ( defined( 'WPML_ST_VERSION' ) ) {
					$lang = wpml_get_current_language();
				}

				if ( strlen( $lang ) > 0 ) {
					$lang = explode( '_', $lang )[0];
				}
			}
			return $lang;
		}

		public function add_custom_controls( $instance, $default_query_settings ) {

			if ( class_exists( 'WooCommerce' ) ) {
				$instance->add_control(
					'catalog_visibility',
					array(
						'label'      => esc_html__( 'Use Product Catalog Visibility Settings', 'jet-search' ),
						'type'       => \Elementor\Controls_Manager::SWITCHER,
						'default'    => $default_query_settings['catalog_visibility'],
						'separator'  => 'before',
					)
				);
			}
		}

		public function add_bricks_custom_controls( $instance, $default_query_settings ) {

			if ( class_exists( 'WooCommerce' ) ) {
				$instance->register_jet_control(
					'catalog_visibility',
					[
						'tab'      => 'content',
						'label'    => esc_html__( 'Use Product Catalog Visibility Settings', 'jet-search' ),
						'type'     => 'checkbox',
						'default'  => $default_query_settings['catalog_visibility'],
					]
				);
			}
		}

		public function modify_allowed_settings( $allowed = array(), $settings = array() ) {

			if ( empty( $settings['search_source'] ) || in_array( 'product', $settings['search_source'] ) ) {
				$allowed[] = 'catalog_visibility';
			}

			return $allowed;
		}

		public function modify_query_settings( $allowed = array(), $settings = array() ) {
			if ( empty( $settings['search_source'] ) || in_array( 'product', $settings['search_source'] ) ) {
				$allowed[] = 'catalog_visibility';
			}

			return $allowed;
		}

		public function modify_search_query( $instance, $args ) {

			if ( isset( $args['catalog_visibility'] ) && filter_var( $args['catalog_visibility'], FILTER_VALIDATE_BOOLEAN ) ) {
				array_push(
					$instance->search_query['tax_query'],
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'exclude-from-search',
						'operator' => 'NOT IN',
					)
				);
			}

			if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
				$product_visibility_term_ids  = function_exists( 'wc_get_product_visibility_term_ids' )
					? wc_get_product_visibility_term_ids()
					: array();

				if ( isset( $product_visibility_term_ids['outofstock'] ) ) {
					$instance->search_query['tax_query'][] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'term_taxonomy_id',
						'terms'    => array( (int) $product_visibility_term_ids['outofstock'] ),
						'operator' => 'NOT IN',
					);
				}

			}

			return $instance;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return Jet_Search_Compatibility
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
 * Returns instance of Jet_Search_Compatibility
 *
 * @return Jet_Search_Compatibility
 */
function jet_search_compatibility() {
	return Jet_Search_Compatibility::get_instance();
}
