<?php
/**
 * Sources Base class
 */

namespace Jet_Search\Search_Sources;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Sources Base Abstract Class
 *
 * @since 3.5.0
 */
abstract class Base {

	/**
	 * Source name
	 *
	 * @var null
	 */
	protected $source_name = null;

	/**
	 * Source args
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Source search string
	 *
	 * @var string
	 */
	protected $search_string = null;

	/**
	 * Source render
	 *
	 * @var string
	 */
	protected $render = '';

	/**
	 * Source items_list
	 *
	 * @var array
	 */
	protected $items_list = array();

	/**
	 * Source limit
	 *
	 * @var int
	 */
	protected $limit = 5;

	/**
	 * Source results count
	 *
	 * @var int
	 */
	protected $results_count = 0;

	/**
	 * Indicates whether the source has a listing.
	 *
	 * @var bool
	 */
	protected $has_listing = false;

	/**
	 * Constructor method for the Search_Source_Base class.
	 */
	public function __construct() {
		add_filter(
			'jet-search/ajax-search/blocks-views/attributes',
			array( $this, 'register_source_block_attrs' )
		);

		$this->init();
	}

	/**
	 * Sets the arguments for the search source.
	 *
	 * @param array $args Arguments to be set.
	 */
	public function set_args( array $args ) {
		$this->args = $args;
		$this->set_args_limit();
	}

	/**
	 * Sets the limit from the arguments if provided.
	 */
	public function set_args_limit() {
		$args         = $this->args;
		$source_limit = 'search_source_' . $this->get_name() . '_limit';

		if ( isset( $args[$source_limit] ) && ! empty( $args[$source_limit] ) ) {
			$this->limit = $args[$source_limit];
		}
	}

	/**
	 * Sets the limit for the search results.
	 *
	 * @param int $limit Limit to be set.
	 */
	public function set_limit( int $limit ) {
		$this->limit = $limit;
	}

	/**
	 * Render method. Renders a list of terms with links and icons.
	 *
	 * @return string HTML content of rendered items or an empty string if no terms are found.
	 */
	public function render() {
		$args             = $this->args;
		$items_list       = $this->items_list;
		$terms_items_html = '';
		$source_name      = $this->source_name;
		$holder_class     = 'jet-ajax-search__source-results-holder';
		$base_class       = 'jet-ajax-search__source-results';
		$title_setting    = 'search_source_' . $this->get_name() . '_title';
		$title            = ! empty( $args[$title_setting] ) ? wp_kses_post( $args[$title_setting] ) : '';
		$items_html       = '';

		$listing_id = isset( $this->args['search_source_' . $this->get_name() . '_listing_id'] ) ? $this->args['search_source_' . $this->get_name() . '_listing_id'] : '';

		if ( '' != $listing_id ) {

			if ( class_exists( 'Elementor\Plugin' ) && 'elementor' === jet_engine()->listings->data->get_listing_type( $listing_id ) ) {
				\Elementor\Plugin::instance()->frontend->register_styles();
				\Elementor\Plugin::instance()->frontend->register_scripts();
			}

			$is_bricks_listing    = jet_engine()->listings->data->get_listing_type( $listing_id ) === 'bricks';
			$bricks_listing_class = true === $is_bricks_listing ? 'brxe-jet-listing' : '';

			$initial_object = jet_engine()->listings->data->get_current_object();

			$jet_engine_frontend = jet_engine()->frontend;
			$jet_engine_frontend->set_listing( $listing_id );

			$items = $this->get_query_result( $this->limit );

			$listing_items = array();
			$html          = '';

			foreach ( $items as $item ) {

				$content = '';

				$jet_engine_frontend->set_listing( $listing_id );

				ob_start();

				$content = $jet_engine_frontend->get_listing_item( $item );

				$inline_css = ob_get_clean();

				if ( ! empty( $inline_css ) ) {
					$content = $inline_css . $content;
				}

				$id      = jet_engine()->listings->data->get_current_object_id();
				$content = sprintf( '<div class="jet-listing-base jet-listing-dynamic-post-%s">%s</div>', $id, $content );

				$listing_items[] = $content;
			}

			jet_engine()->frontend->reset_data();
			jet_engine()->listings->data->set_current_object( $initial_object );

			if ( ! empty( $listing_items ) ) {
				$html = sprintf(
					'<div class="' . $holder_class . ' ' . $holder_class . '_' . $source_name . ' ' . $bricks_listing_class . '"><div class="' . $holder_class . '-title">' . $title . '</div>%s</div>',
					implode( "", $listing_items )
				);
			}

			return $html;
		}

		if ( empty( $items_list ) ) {
			return '';
		}

		$icon = ! empty( $args['search_source_' . $source_name . '_icon'] ) ? $args['search_source_' . $source_name . '_icon'] : false;

		$new_icon_html = \Jet_Search_Tools::render_icon( $icon, $base_class . '-item_icon' );
		$icon          = '';

		if ( $new_icon_html ) {
			$icon = $new_icon_html;
		}

		$items_html = '';

		foreach ( $items_list as $item ) {
			$url = ! empty( $item['url'] ) ? esc_url( $item['url'] ) : '';

			if ( '' != $url ) {
				$item_html = '<div class="' . $base_class . '-item ' . $base_class . '-item_' . $source_name . '"><a class="' . $base_class . '-item_link" href="' . $url . '">' . $icon . wp_kses_post( $item['name'] ) . '</a></div>';
			} else {
				continue;
			}

			$items_html .= $item_html;
		}

		$html = sprintf(
			'<div class="' . $holder_class . ' ' . $holder_class . '_' . $source_name . '"><div class="' . $holder_class . '-title">' . $title . '</div>%s</div>',
			$items_html
		);

		return $html;

	}

	/**
	 * Registers general controls for the editor.
	 *
	 * @return array Settings for the editor controls.
	 */
	public function editor_general_controls() {
		$name  = $this->get_name();
		$label = $this->get_label();

		$settings = array(
			'section_additional_sources' => array(
				'search_source_' . $name => array(
					'label'     => __( $label . ' Search Source', 'jet-search' ),
					'type'      => 'switcher',
					'default'   => '',
					'label_on'  => __( 'Show', 'jet-search' ),
					'label_off' => __( 'Hide', 'jet-search' ),
					'separator' => 'before',
				),
				'search_source_' . $name . '_title' => array(
					'label'     => __( 'Title', 'jet-search' ),
					'type'      => 'text',
					'default'   => '',
					'condition' => array(
						'search_source_' . $name . '!' => '',
					),
				),
				'search_source_' . $name . '_icon' => array(
					'label'            => __( 'Icon', 'jet-search' ),
					'type'             => 'icons',
					'skin'             => 'inline',
					'fa4compatibility' => 'search_field_icon',
					'condition'        => array(
						'search_source_' . $name . '!' => '',
					),
				),
				'search_source_' . $name . '_limit' => array(
					'label'     => __( 'Limit', 'jet-search' ),
					'type'      => 'number',
					'min'       => 1,
					'max'       => 25,
					'default'   => 5,
					'condition' => array(
						'search_source_' . $name . '!' => '',
					),
				),
			)
		);

		if ( filter_var( $this->has_listing, FILTER_VALIDATE_BOOLEAN ) ) {
			if ( class_exists( 'Jet_Engine' ) ) {
				$settings['section_additional_sources']['search_source_' . $name . '_listing_id'] = array(
					'label'           => __( 'Listing', 'jet-search' ),
					'description'     => esc_html__( 'Select the listing to be used as a template for the search results items.', 'jet-search' ),
					'type'            => 'jet-query',
					'query_type'      => 'post',
					'create_button'   => array(
						'active'  => true,
						'handler' => 'JetListings',
					),
					'query'           => array(
						'post_type' => jet_engine()->post_type->slug(),
					),
					'prevent_looping' => true,
					'condition'       => array(
						'search_source_' . $name . '!' => '',
					)
				);

				$settings['section_additional_sources']['search_source_' . $name . '_icon'] = array_merge( $settings['section_additional_sources']['search_source_' . $name . '_icon'], array(
					'condition' => array(
						'search_source_' . $name . '!' => '',
						'search_source_' . $name . '_listing_id' => '',
					)
				) );
			} else {
				$settings['section_additional_sources']['listing_jetengine_' . $name . '_notice'] = array(
					'type'        => 'notice',
					'notice_type' => 'warning',
					'dismissible' => false,
					'content'     => esc_html__( 'After JetEngine installation, you can use listings as templates for search results from the search source.', 'jet-search' ),
				);
			}
		}

		$additional_controls = $this->additional_editor_general_controls();

		if ( ! empty( $additional_controls ) ) {

			foreach ( $additional_controls as $key => $controls ) {
				if ( isset( $settings[$key] ) ) {
					$settings[$key] = array_merge( $settings[$key], $additional_controls[$key] );
				} else {
					$settings[$key] = $additional_controls[$key];
				}
			}
		}

		return $settings;
	}

	/**
	 * Optional additional editor general controls.
	 *
	 * @return array Empty array by default, can be overridden by child classes.
	 */
	public function additional_editor_general_controls(){
		return array();
	}

	/**
	 * Registers source block attributes.
	 *
	 * @param array $attrs Attributes to be registered.
	 * @return array Updated attributes.
	 */
	public function register_source_block_attrs( array $attrs ) {
		$settings     = $this->editor_general_controls();
		$source_attrs = array();

		if ( empty( $settings ) ) {
			return $attrs;
		}

		foreach ( $settings as $key => $section ) {
			foreach ( $section as $key => $control ) {
				$source_attrs[ $key ] = $control;
			}
		}

		if ( ! empty( $source_attrs ) ) {
			$source_attrs = jet_search_blocks_integration()->get_allowed_atts( $source_attrs );

			$attrs = array_merge( $attrs, $source_attrs );
		}

		return $attrs;
	}

	/**
	 * Optional additional initializtion for source. Can be overriden from child class if needed.
	 * @return [type] [description]
	 */
	public function init() {}

	/**
	 * Abstract method to get label. Must be implemented in child class.
	 *
	 * @return string Label of the source.
	 */
	abstract public function get_label();

	/**
	 * Abstract method to get priority. Must be implemented in child class.
	 *
	 * @return int Priority of the source.
	 */
	abstract public function get_priority();

	/**
	 * Abstract method to get query result list. Must be implemented in child class.
	 *
	 * @return array Query result list where each element contains 'name' and 'url'.
	 */
	abstract public function build_items_list();

	/**
	 * Abstract method to get query result. Must be implemented in child class.
	 *
	 * @return mixed Query result.
	 */
	abstract public function get_query_result();

	/**
	 * Get search string
	 *
	 * @return array search_string.
	 */
	public function get_search_string() {
		return $this->search_string;
	}

	/**
	 * Sets the search string and updates the query result list..
	 *
	 * @param string $search_string The search string to set.
	 */
	public function set_search_string( string $search_string ) {
		$search_string = urldecode( esc_sql( $search_string ) );

		$this->search_string = $search_string;

		$this->build_items_list();
	}

	/**
	 * Set items list
	 *
	 * @param array $items_list Items list to be set.
	 */
	public function set_items_list( array $items_list = array() ) {
		$this->items_list = $items_list;
	}

	/**
	 * Get items list
	 *
	 */
	public function get_items_list() {
		return $this->items_list;
	}

	/**
	 * Get results count
	 *
	 * @return int Results count.
	 */
	public function get_results_count() {
		return $this->results_count;
	}

	/**
	 * Get source name
	 *
	 * @return string source name.
	 */
	public function get_name() {
		return $this->source_name;
	}
}
