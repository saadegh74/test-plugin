<?php
/**
 * @since 3.1.0
 */
class Jet_Search_Tax_Query {

	/**
	 * Ajax action.
	 *
	 * @var string
	 */
	private $action = 'jet_ajax_search';

	private $search = null;

	public $settings          = null;
	public $terms_ids         = array();
	public $include_terms_ids = array();
	public $exclude_terms_ids = array();
	public $exclude_posts_ids = array();

	public function __construct() {
		$this->set_settings();
		$this->get_search_string();
	}

	public function set_settings() {
		// phpcs:disable
		if ( isset( $_GET['action'] ) && $this->action === $_GET['action']
			&& ! empty( $_GET['data']['search_in_taxonomy'] )
			&& ! empty( $_GET['data']['search_in_taxonomy_source'] )
		) {
			$raw_data = wp_unslash( $_GET['data'] );

			$this->settings = map_deep( $raw_data, 'sanitize_text_field' );
		} else {
			$this->settings = jet_search_ajax_handlers()->get_form_settings();
		}
		// phpcs:enable
	}

	public function get_taxonomies() {
		$taxonomies = ! empty( $this->settings['search_in_taxonomy'] ) && ! empty( $this->settings['search_in_taxonomy_source'] ) ? $this->settings['search_in_taxonomy_source'] : false;

		return $taxonomies;
	}

	public function get_search_string() {
		// phpcs:disable WordPress.Security.NonceVerification
		$search = null;

		$custom_search_query_param = jet_search_ajax_handlers()->get_custom_search_query_param();
		$search_query_param        = ! empty( $_REQUEST[$custom_search_query_param] ) ? $_REQUEST[$custom_search_query_param] : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		if ( isset( $_GET['action'] ) && $this->action === $_GET['action']
			&& isset( $_GET['data']['value'] )
			&& ! empty( $_GET['data']['value'] )
		) {
			$search = sanitize_text_field( wp_unslash( $_GET['data']['value'] ) );
		} else if ( false != $search_query_param ) {
			$search = sanitize_text_field( wp_unslash ( $search_query_param ) );
		} else {
			$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash ( $_GET['s'] ) ) : '';
		}

		$this->search = $search;
		// phpcs:enable WordPress.Security.NonceVerification
	}

	public function get_posts_ids() {
		$taxonomies = $this->get_taxonomies();
		$settings   = $this->settings;

		$include_terms_ids = ! empty( $settings['include_terms_ids'] ) ? array_map( 'intval', (array) $settings['include_terms_ids'] ) : array();
		$exclude_terms_ids = ! empty( $settings['exclude_terms_ids'] ) ? array_map( 'intval', (array) $settings['exclude_terms_ids'] ) : array();
		$exclude_posts_ids = ! empty( $settings['exclude_posts_ids'] ) ? array_map( 'intval', (array) $settings['exclude_posts_ids'] ) : array();

		if ( $taxonomies ) {
			global $wpdb;

			$posts_table              = $wpdb->posts;
			$term_relationships_table = $wpdb->term_relationships;
			$term_taxonomy_table      = $wpdb->term_taxonomy;
			$terms_table              = $wpdb->terms;

			$search = $this->search;

			if ( ! empty( $search ) ) {

				$s_query = $wpdb->esc_like( $search );
				$tax_in  = [];

				foreach ( $taxonomies as $tax ) {
					$tax_in[] = $wpdb->prepare( 'tt.taxonomy = %s', $tax );
				}

				$tax_in = implode( ' OR ', $tax_in );

				$sql_args = array( '%' . $s_query . '%' );

				$include_terms_sql = '';
				if ( ! empty( $include_terms_ids ) ) {
					$placeholders      = implode( ',', array_fill( 0, count( $include_terms_ids ), '%d' ) );
					$include_terms_sql = " AND t.term_id IN ($placeholders)";
					$sql_args          = array_merge( $sql_args, $include_terms_ids );
				}

				$exclude_terms_sql = '';
				if ( ! empty( $exclude_terms_ids ) ) {
					$placeholders      = implode( ',', array_fill( 0, count( $exclude_terms_ids ), '%d' ) );
					$exclude_terms_sql = " AND t.term_id NOT IN ($placeholders)";
					$sql_args          = array_merge( $sql_args, $exclude_terms_ids );
				}

				$db_query = "SELECT DISTINCT p.ID
							FROM {$posts_table} AS p
							INNER JOIN {$term_relationships_table} AS tr ON p.ID = tr.object_id
							INNER JOIN {$term_taxonomy_table} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
							INNER JOIN {$terms_table} AS t ON tt.term_id = t.term_id
							WHERE ( {$tax_in} )
							AND t.name LIKE %s 
							{$include_terms_sql} 
							{$exclude_terms_sql}";

				$db_query .= ";";

				$posts_ids = $wpdb->get_results( $wpdb->prepare( $db_query, ...$sql_args ) );

				if ( ! empty( $posts_ids ) ) {
					$ids = array();

					foreach ( $posts_ids as $key => $value ) {
						$ids[$key] = $value->ID;
					}

					if ( ! empty( $exclude_posts_ids ) ) {
						$ids = array_values( array_diff( $ids, $exclude_posts_ids ) );
					}

					return $ids;
				}
			}

		}

		/**
		 * Enables support for searching by custom product attributes.
		 *
		 * This allows external code to hook into 'jet_search/custom_attribute_search_ids'
		 * and return matching post IDs from wp_postmeta
		 */
		$custom_ids = apply_filters(
			'jet_search/custom_attribute_search_ids',
			[],
			$this->search,
			$taxonomies,
			$this->settings
		);

		if ( ! empty( $custom_ids ) && is_array( $custom_ids ) ) {
			$ids = isset( $ids ) ? array_merge( $ids, $custom_ids ) : $custom_ids;
		}

		return ! empty( $ids ) ? array_unique( $ids ) : false;
	}

}
