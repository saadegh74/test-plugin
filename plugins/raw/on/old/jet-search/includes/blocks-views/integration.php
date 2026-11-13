<?php
/**
 * Jet_Search_Blocks_Integration class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Blocks_Integration' ) ) {

	/**
	 * Define Jet_Search_Blocks_Integration class
	 */
	class Jet_Search_Blocks_Integration {

		public $rendered = 0;
		public $style_manager = null;

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   Jet_Search_Blocks_Integration
		 */
		private static $instance = null;

		/**
		 * Initialize integration hooks
		 *
		 * @return void
		 */
		public function init() {
			require_once jet_search()->plugin_path( 'includes/blocks-views/blocks-styles/ajax-search.php' );
			require_once jet_search()->plugin_path( 'includes/blocks-views/blocks-styles/search-suggestions.php' );

			add_action( 'init', array( $this, 'register_block_type' ), 999 );
			add_action( 'init', 'ajax_search_block_add_style', 10 );
			add_action( 'init', 'search_suggestions_block_add_style', 10 );

			add_action( 'enqueue_block_assets',   array( jet_search_assets(), 'enqueue_styles' ), 0 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'register_styles' ), 0 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_styles' ), 0 );
		}

		/**
		 * Returns blocks style manager instance.
		 *
		 * @return \Crocoblock\Blocks_Style\Manager|false
		 */
		public function get_style_manager() {

			if ( null !== $this->style_manager ) {
				return $this->style_manager;
			}

			if ( empty( jet_search()->module_loader ) ) {
				return false;
			}

			$module_data = jet_search()->module_loader->get_included_module_data( 'style-manager.php' );

			if ( empty( $module_data['path'] ) || empty( $module_data['url'] ) ) {
				return false;
			}

			$this->style_manager = new \Crocoblock\Blocks_Style\Manager(
				array(
					'path' => $module_data['path'],
					'url'  => $module_data['url'],
				)
			);

			return $this->style_manager;
		}

		/**
		 * Register block type for search
		 *
		 * @return [type] [description]
		 */
		public function register_block_type() {

			wp_register_script(
				'jet-search-block',
				jet_search()->plugin_url( 'assets/js/jet-search-block.js' ),
				array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-block-editor', 'wp-i18n', 'wp-polyfill', 'lodash', 'wp-api-fetch' ),
				jet_search()->get_version() . time(),
				true
			);

			$mimes = get_allowed_mime_types();

			wp_localize_script( 'jet-search-block', 'JetSearchData', array(
				'supportSVG'                  => isset( $mimes['svg'] ) ? true : false,
				'taxonomiesList'              => $this->get_taxonomies_list(),
				'taxonomiesListExtended'      => $this->get_taxonomies_list( true ),
				'postTypesList'               => $this->get_post_types_list(),
				'metaCallbacks'               => \Jet_Search_Tools::allowed_meta_callbacks(),
				'thumbSizes'                  => $this->get_thumb_sizes(),
				'placeholderImgUrl'           => $this->get_placeholder_img_url(),
				'arrowsType'                  => $this->get_arrows_type(),
				'suggestionsSettingsPageLink' => jet_search_settings()->get_settings_page_link(),
				'ajaxSearchSettingsPageLink'  => jet_search_settings()->get_settings_page_link( 'ajax-search' ),
				'jetEngineListingsAdminUrl'   => jet_search_settings()->get_jetengine_listings_admin_url(),
				'additionalSourcesControls'   => $this->get_sources_controls(),
			) );

			$json_path    = jet_search()->plugin_path('includes/blocks-views/blocks/ajax-search-block.json');
			$file_content = json_decode(file_get_contents($json_path), true);
			$attributes   = $file_content['attributes'];
			$settings     = get_option( 'jet_ajax_search_query_settings' );

			if ( false != $settings ) {
				$default_query_settings = \Jet_Search_Tools::prepared_settings_for_blocks( $settings );

				$attributes = array_merge( $attributes, $default_query_settings );
			}

			register_block_type(
				jet_search()->plugin_path( 'includes/blocks-views/blocks/ajax-search-block.json' ),
				array(
					'render_callback' => array( $this, 'search_render_callback' ),
					'attributes'      => apply_filters( 'jet-search/ajax-search/blocks-views/attributes', $attributes ),
				)
			);

			register_block_type(
				jet_search()->plugin_path( 'includes/blocks-views/blocks/search-suggestions-block.json' ),
				array(
					'render_callback' => array( $this, 'search_suggestions_render_callback' ),
				)
			);
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

			wp_register_style(
				'jet-search-editor',
				jet_search()->plugin_url( 'assets/css/jet-search-editor.css' ),
				array(),
				jet_search()->get_version()
			);
		}

		/**
		 * Register plugin stylesheets.
		 *
		 * @return void
		 */
		public function enqueue_editor_styles() {
			wp_enqueue_style( 'jet-search-editor' );
		}

		public function get_taxonomies_list( $include_custom_attributes = false ) {

			$taxonomies = \Jet_Search_Tools::get_taxonomies( false, $include_custom_attributes );

			foreach ( $taxonomies as $value => $label ) {
				$taxonomies_list[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			return $taxonomies_list;
		}

		public function get_post_types_list() {
			$post_types = \Jet_Search_Tools::get_post_types();

			foreach ( $post_types as $value => $label ) {
				$post_types_list[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			return $post_types_list;
		}

		public function get_thumb_sizes() {
			$thumb_sizes = \Jet_Search_Tools::get_image_sizes();

			foreach ( $thumb_sizes as $value => $label ) {
				$thumb_sizes_list[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			return $thumb_sizes_list;
		}

		public function get_placeholder_img_url() {
			return jet_search()->plugin_url( 'assets/images/placeholder.png' );
		}

		public function get_arrows_type() {

			$arrows_type = \Jet_Search_Tools::get_available_prev_arrows_list();

			foreach ( $arrows_type as $value => $label ) {
				$arrows_type_list[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			return $arrows_type_list;
		}

		public function search_render_callback( $attributes = array() ) {

			$this->rendered++;
			$render = new Jet_Search_Render( $attributes, $this->rendered );

			ob_start();
			$render->render();

			$class_name = ! empty( $attributes['className'] ) ? esc_attr( $attributes['className'] ) : '';

			return sprintf(
				'<div class="jet-ajax-search-block %2$s" data-is-block="jet-search/ajax-search">%1$s</div>',
				ob_get_clean(),
				$class_name
			);
		}

		public function search_suggestions_render_callback( $attributes = array() ) {

			$this->rendered++;
			$render = new Jet_Search_Suggestions_Render( $attributes, $this->rendered );

			ob_start();
			$render->render();

			$class_name = ! empty( $attributes['className'] ) ? esc_attr( $attributes['className'] ) : '';

			return sprintf(
				'<div class="jet-search-suggestions-block %2$s" data-is-block="jet-search/search-suggestions">%1$s</div>',
				ob_get_clean(),
				$class_name
			);
		}

		/**
		 * Retrieves and prepares the source controls settings.
		 *
		 * This method fetches the source controls from the Search Sources Manager,
		 * prepares each control's settings by processing its controls, and returns
		 * an array of these settings.
		 *
		 * @return array An array of prepared source controls settings.
		 */
		public function get_sources_controls() {

			$sources_settings = array();

			if ( class_exists( 'Jet_Search\Search_Sources\Manager' ) ) {
				$sources_manager = jet_search()->search_sources;
				$sources         = $sources_manager->get_sources();

				foreach ( $sources as $key => $source ) {
					$settings = $source->editor_general_controls();

					if ( ! empty( $settings ) ) {
						foreach ( $settings as $section => $controls ) {
							$settings[ $section ] = $this->get_prepared_controls( $controls );
						}

						$sources_settings[ $key ] = $settings;
					}
				}
			}

			return $sources_settings;
		}

		/**
		 * Prepares the controls for source settings.
		 *
		 * This method processes the controls by preparing each control's options and
		 * structuring the control data into a result array.
		 *
		 * @param array $controls An array of controls to be prepared.
		 * @return array An array of prepared controls.
		 */
		public function get_prepared_controls( array $controls ) {

			$result = array();

			foreach ( $controls as $key => $control ) {

				if ( ! empty( $control['options'] ) ) {
					$control['options'] = $this->get_prepared_control_options( $control['options'] );
				}

				$result[] = array(
					'key'     => $key,
					'control' => $control,
				);
			}

			return $result;
		}

		/**
		 * Prepares the control options.
		 *
		 * This method processes the control options, converting them into an array
		 * of value-label pairs.
		 *
		 * @param array $options An array of control options.
		 * @return array An array of prepared control options.
		 */
		public function get_prepared_control_options( array $options ) {

			$result = array();

			foreach ( $options as $value => $label ) {
				$result[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			return $result;
		}

		/**
		 * Filters and prepares allowed attributes for a block.
		 *
		 * This method processes the block attributes, filtering out disallowed attributes,
		 * and prepares the allowed attributes by setting their type and default values.
		 *
		 * @param array $block_atts An array of block attributes to be processed.
		 * @return array An array of allowed and prepared attributes.
		 */
		public function get_allowed_atts( array $block_atts ) {

			$atts       = array();
			$disallowed = array();

			foreach ( $block_atts as $key => $args ) {

				if ( in_array( $key, $disallowed ) ) {
					continue;
				}

				$attr = array();

				switch ( $args['type'] ) {
					case 'number':
						$attr['type'] = 'number';

						if ( isset( $args['default'] ) ) {
							$attr['default'] = intval( $args['default'] );
						}

						break;

					case 'slider':
						$attr['type'] = 'number';

						if ( isset( $args['default']['size'] ) ) {
							$attr['default'] = intval( $args['default']['size'] );
						}

						break;

					case 'switcher':
						$attr['type']    = 'boolean';
						$attr['default'] = ! empty( $args['default'] ) ? filter_var( $args['default'], FILTER_VALIDATE_BOOLEAN ) : false;

						break;

					case 'icons':
						$attr['type']    = 'object';
						$attr['default'] = ! empty( $args['default'] ) ? $args['default'] : new \stdClass();

						break;

					default:
						$attr['type'] = 'string';
						$attr['default'] = ! empty( $args['default'] ) ? $args['default'] : '';
				}

				$atts[ $key ] = $attr;
			}

			return $atts;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return Jet_Search_Blocks_Integration
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
 * Returns instance of Jet_Search_Blocks_Integration
 *
 * @return Jet_Search_Blocks_Integration
 */
function jet_search_blocks_integration() {
	return Jet_Search_Blocks_Integration::get_instance();
}

/**
 * Register block style controls in the built-in blocks style manager.
 *
 * @param string|false $name Block name.
 *
 * @return \Crocoblock\Blocks_Style\Proxy|false
 */
function jet_search_register_style_for_block( $name = false ) {

	if ( ! $name ) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html__( 'Block name is required.', 'jet-search' ),
			jet_search()->get_version()
		);

		return false;
	}

	$style_manager = jet_search_blocks_integration()->get_style_manager();

	if ( ! $style_manager ) {
		return false;
	}

	$style_manager->register_block_support( $name );

	return $style_manager->get_proxy( $name );
}
