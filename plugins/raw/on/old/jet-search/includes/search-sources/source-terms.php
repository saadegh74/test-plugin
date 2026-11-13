<?php
namespace Jet_Search\Search_Sources;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Terms Source Class
 *
 * @since 3.5.0
 */
class Terms extends Base {

	/**
	 * Exact whole-word matches weighting option key.
	 *
	 * @var string
	 */
	protected $whole_word_weighting_key = 'search_source_terms_prioritize_whole_word_matches';

	/**
	 * Source name
	 *
	 * @var string
	 */
	protected $source_name = 'terms';

	/**
	 * Returns source human-readable name
	 *
	 * @return string The label of the source.
	 */
	public function get_label() {
		return __( 'Terms', 'jet-search' );
	}

	/**
	 * Indicates whether the source has a listing.
	 *
	 * @var bool
	 */
	protected $has_listing = true;

	/**
	 * Source taxonomy
	 *
	 * @var string
	 */
	protected $taxonomy = null;

	/**
	 * Returns the priority of the source.
	 *
	 * @return int The priority of the source.
	 */
	public function get_priority() {
		$priority = apply_filters( 'jet-search/ajax-search/search-source/terms/priority', -2 );

		if ( ! is_int( $priority ) || 0 === $priority ) {
			return -2;
		}

		return $priority;
	}

	/**
	 * Retrieves the query result list.
	 * Sets the items list and results count based on the query.
	 * Returns an array where each element contains 'name' and 'url'.
	 */
	public function build_items_list() {
		$result = array();
		$terms  = $this->get_query_result();

		if ( empty( $terms ) ) {
			$this->results_count = 0;

			return;
		}

		foreach ( $terms as $term ) {
			$term_link = get_term_link( $term->term_id );

			if ( ! empty( $term_link ) ) {
				$result[] = array( 'name' => esc_html( $term->name ), 'url' => esc_url( $term_link ) );
			}
		}

		$result = apply_filters( 'jet-search/ajax-search/search-source/terms/search-result-list', $result );

		$this->results_count = count( $result );

		$this->items_list = $result;
	}

	/**
	 * Retrieves the query result based on the search string and other parameters.
	 *
	 * @param int    $limit The number of results to return.
	 * @return mixed The query result.
	 */
	public function get_query_result( $limit = null ) {
		$args = $this->args;

		$this->taxonomy = isset( $args['search_source_terms_taxonomy'] ) ? $args['search_source_terms_taxonomy'] : 'category';

			$terms_list = array();
			$result     = array();
			$limit      = null != $limit ? $limit : $this->limit;

		$args = array(
			'taxonomy'   => $this->taxonomy,
			'search'     => $this->search_string,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => $limit,
			'offset'     => 0,
			'fields'     => 'all',
		);

		$term_query = new \WP_Term_Query( apply_filters( 'jet-search/ajax-search/search-source/terms/query_args', $args ) );

		$results = apply_filters( 'jet-search/ajax-search/search-source/terms/query-results', $term_query->terms );

		if ( $this->is_whole_word_weighting_enabled() ) {
			$results = $this->prioritize_whole_word_matches( $results );
		}

		return $results;
	}

	/**
	 * Check if exact whole-word matches prioritization is enabled.
	 *
	 * @return bool
	 */
	protected function is_whole_word_weighting_enabled() {
		return ! empty( $this->args[ $this->whole_word_weighting_key ] )
			&& filter_var( $this->args[ $this->whole_word_weighting_key ], FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Prioritize exact whole-word matches while preserving the existing order
	 * inside each relevance bucket returned by the current query.
	 *
	 * @param array $terms Terms search results.
	 *
	 * @return array
	 */
	protected function prioritize_whole_word_matches( $terms ) {
		if ( empty( $terms ) || empty( $this->search_string ) ) {
			return $terms;
		}

		$ranked_terms = array();

		foreach ( $terms as $index => $term ) {
			$ranked_terms[] = array(
				'term'              => $term,
				'has_whole_word'    => $this->has_whole_word_match( $term ),
				'original_position' => $index,
			);
		}

		usort(
			$ranked_terms,
			function( $left, $right ) {
				if ( $left['has_whole_word'] === $right['has_whole_word'] ) {
					return $left['original_position'] <=> $right['original_position'];
				}

				return $left['has_whole_word'] ? -1 : 1;
			}
		);

		return array_map(
			function( $item ) {
				return $item['term'];
			},
			$ranked_terms
		);
	}

	/**
	 * Determine whether the term title contains the searched phrase as a whole word.
	 *
	 * @param \WP_Term $term Term object.
	 *
	 * @return bool
	 */
	protected function has_whole_word_match( $term ) {
		if ( empty( $term->name ) || ! is_string( $term->name ) ) {
			return false;
		}

		$search_string = trim( wp_strip_all_tags( (string) $this->search_string ) );

		if ( '' === $search_string ) {
			return false;
		}

		$pattern = '/(^|[^\p{L}\p{N}_])' . preg_quote( $search_string, '/' ) . '($|[^\p{L}\p{N}_])/iu';

		return 1 === preg_match( $pattern, wp_strip_all_tags( $term->name ) );
	}

	/**
	 * Provides additional general controls for the editor.
	 *
	 * @return array Additional settings for the editor.
	 */
	public function additional_editor_general_controls() {
		$name  = $this->source_name;
		$label = $this->get_label();

		$settings = array(
			'section_additional_sources' => array(
				'search_source_' . $name . '_taxonomy' => array(
					'label'     => esc_html__( 'Taxonomy', 'jet-search' ),
					'type'      => 'select',
					'default'   => 'category',
					'options'   => \Jet_Search_Tools::get_taxonomies(),
					'condition' => array(
						'search_source_' . $name . '!' => '',
					),
				),
				$this->whole_word_weighting_key => array(
					'label'     => esc_html__( 'Prioritize Exact Whole-Word Matches', 'jet-search' ),
					'description' => esc_html__( 'Raise taxonomy titles containing the exact searched word above partial matches.', 'jet-search' ),
					'type'      => 'switcher',
					'default'   => '',
					'condition' => array(
						'search_source_' . $name . '!' => '',
					),
				),
			)
		);

		return $settings;
	}
}
