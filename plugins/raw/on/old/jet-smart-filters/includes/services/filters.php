<?php
/**
 * Filters service class
 */

class Jet_Smart_Filters_Service_Filters {

	private $_multilingual;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		// Init admin data
		add_action( 'init', function() {
			if ( isset( jet_smart_filters()->admin->multilingual_support ) ) {
				$this->_multilingual = jet_smart_filters()->admin->multilingual_support;
			} else {
				require_once jet_smart_filters()->plugin_path( 'admin/includes/multilingual-support.php' );
				$this->_multilingual = new Jet_Smart_Filters_Admin_Multilingual_Support();
			}
		}, 9999 );
	}

	public function get( $args ) {

		global $wpdb;
		$args = is_array( $args ) ? $args : array();

		$args = array_merge( array(
			'status'   => 'publish',
			'page'     => 1,
			'per_page' => 20,
			'orderby'  => 'date',
			'order'    => 'DESC',
			'search'   => false,
			'type'     => false,
			'source'   => false,
			'date'     => false,
			'language' => false
		), $args );

		$status = is_scalar( $args['status'] ) ? sanitize_text_field( (string) $args['status'] ) : 'publish';

		// Pagination
		$page     = is_scalar( $args['page'] ) ? max( 1, absint( $args['page'] ) ) : 1;
		$per_page = is_scalar( $args['per_page'] ) ? max( 1, absint( $args['per_page'] ) ) : 20;
		$offset   = $per_page * ( $page - 1 );

		// Sort
		$order_by = is_scalar( $args['orderby'] ) ? sanitize_text_field( (string) $args['orderby'] ) : 'title';
		$order    = is_scalar( $args['order'] ) ? sanitize_text_field( (string) $args['order'] ) : 'asc';

		$allowed_orderby = [ 'title', 'date' ];
		$allowed_order   = [ 'asc', 'desc' ];

		$order_by = in_array( $order_by, $allowed_orderby ) ? $order_by : 'title';
		$order    = in_array( $order, $allowed_order ) ? $order : 'asc';

		// Search
		$searchSql   = '';
		$search      = is_scalar( $args['search'] ) ? (string) $args['search'] : '';
		$query_values = array( $status );

		if ( $search ) {
			$searchSql = "AND {$wpdb->posts}.post_title LIKE %s";
			$query_values[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		// Filtration
		$filtrationSql = '';
		$filter_values = array();
		$type          = is_scalar( $args['type'] ) ? (string) $args['type'] : '';
		$source        = is_scalar( $args['source'] ) ? (string) $args['source'] : '';
		$date          = $args['date'];

		if ( $type ) {
			$filtrationSql .= 'AND postmeta_type.meta_value = %s ';
			$filter_values[] = $type;
		}

		if ( $source ) {
			$filtrationSql .= 'AND postmeta_source.meta_value = %s ';
			$filter_values[] = $source;
		}

		if ( is_array( $date ) ) {
			if ( ! empty( $date['from'] ) && is_scalar( $date['from'] ) ) {
				$filtrationSql .= "AND $wpdb->posts.post_date >= %s ";
				$filter_values[] = (string) $date['from'] . ' 00:00:00';
			}
			if ( ! empty( $date['to'] ) && is_scalar( $date['to'] ) ) {
				$filtrationSql .= "AND $wpdb->posts.post_date <= %s ";
				$filter_values[] = (string) $date['to'] . ' 23:59:59';
			}
		}

		// Multilingual
		$current_language = is_scalar( $args['language'] ) ? sanitize_text_field( (string) $args['language'] ) : false;

		// Display all languages
		if ( $status === 'trash' || $current_language === 'all' ) {
			$current_language = false;
		}

		$ml_SQL_parts = $this->_multilingual->get_SQL_filters_parts( $current_language );
		$ml_join       = $ml_SQL_parts['join'];
		$ml_where      = isset( $ml_SQL_parts['where_format'] ) ? $ml_SQL_parts['where_format'] : $ml_SQL_parts['where'];
		$ml_values     = isset( $ml_SQL_parts['where_values'] ) ? $ml_SQL_parts['where_values'] : array();

		$sql_main = $wpdb->prepare( "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title as title, $wpdb->posts.post_date as date, postmeta_type.meta_value as type, postmeta_source.meta_value as source
		FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta as postmeta_type ON ($wpdb->posts.ID = postmeta_type.post_ID AND postmeta_type.meta_key = '_filter_type')
			LEFT JOIN $wpdb->postmeta as postmeta_source ON ($wpdb->posts.ID = postmeta_source.post_ID AND postmeta_source.meta_key = '_data_source')
			$ml_join
			WHERE $wpdb->posts.post_type = 'jet-smart-filters'
				AND $wpdb->posts.post_status = %s
				$searchSql
				$filtrationSql
				$ml_where
			ORDER BY $order_by $order
			LIMIT %d, %d",
			array_merge( $query_values, $filter_values, $ml_values, array( $offset, $per_page ) )
		);
		$filters_result = $wpdb->get_results( $sql_main, ARRAY_A );

		$sql_count = $wpdb->prepare( "
		SELECT COUNT(*) as count
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta as postmeta_type ON ($wpdb->posts.ID = postmeta_type.post_ID AND postmeta_type.meta_key = '_filter_type')
		LEFT JOIN $wpdb->postmeta as postmeta_source ON ($wpdb->posts.ID = postmeta_source.post_ID AND postmeta_source.meta_key = '_data_source')
			$ml_join
			WHERE $wpdb->posts.post_type = 'jet-smart-filters'
				AND $wpdb->posts.post_status = %s
				$searchSql
				$filtrationSql
				$ml_where",
			array_merge( $query_values, $filter_values, $ml_values )
		);
		$count_result = $wpdb->get_results( $sql_count, ARRAY_A );

		$sql_total_count = "
		SELECT COUNT(*) as count
		FROM $wpdb->posts
			WHERE $wpdb->posts.post_type = 'jet-smart-filters'
				AND $wpdb->posts.post_status = 'publish'";
		$total_count_result = $wpdb->get_results( $sql_total_count, ARRAY_A );

		$sql_total_trash_count = "
		SELECT COUNT(*) as count
		FROM $wpdb->posts
			WHERE $wpdb->posts.post_type = 'jet-smart-filters'
				AND $wpdb->posts.post_status = 'trash'";
		$total_trash_count_result = $wpdb->get_results( $sql_total_trash_count, ARRAY_A );

		$quantity = array(
			'filters' => $total_count_result[0]['count'],
			'trash'   => $total_trash_count_result[0]['count'],
		);

		$output = array(
			'filters'  => $filters_result,
			'count'    => $count_result[0]['count'],
			'quantity' => $quantity
		);

		if ( $this->_multilingual->is_Enabled ) {
			// add translations quantity to response
			$output['multilingual_quantity']        = $this->_multilingual->get_translations_count();
			$output['multilingual_quantity']['all'] = $quantity['filters'];

			// add translations data to filters list
			$this->_multilingual->add_data_to_list( $output['filters'] );
		}

		return $output;
	}

	public function restore( $ids ) {

		global $wpdb;

		if ( $ids !== 'all' && ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( $ids !== 'all' ) {
			$ids = wp_parse_id_list( $ids );

			if ( empty( $ids ) ) {
				return 0;
			}
		}

		$sql = "
		UPDATE $wpdb->posts
		SET post_status = 'publish'
			WHERE $wpdb->posts.post_type = 'jet-smart-filters'
				AND $wpdb->posts.post_status = 'trash'";
		if ( $ids !== 'all' ) {
			$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
			$sql .= " AND $wpdb->posts.ID IN ( $placeholders )";
			$sql = $wpdb->prepare( $sql, $ids );
		}

		$result = $wpdb->query( $sql );

		return $result;
	}

	public function move_to_trash( $ids ) {

		global $wpdb;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids = wp_parse_id_list( $ids );

		if ( empty( $ids ) ) {
			return 0;
		}

		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );

		$sql = $wpdb->prepare( "
		UPDATE $wpdb->posts
		SET post_status = 'trash'
			WHERE $wpdb->posts.post_type = 'jet-smart-filters'
				AND $wpdb->posts.post_status = 'publish'
				AND $wpdb->posts.ID IN ( $placeholders )",
			$ids
		);

		$result = $wpdb->query( $sql );

		return $result;
	}
	public function delete( $ids ) {

		global $wpdb;

		if ( $ids !== 'all' && ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( $ids === 'all' ) {
			$sql = "
			SELECT $wpdb->posts.ID
			FROM $wpdb->posts
				WHERE $wpdb->posts.post_type = 'jet-smart-filters'
					AND $wpdb->posts.post_status = 'trash'
				GROUP BY $wpdb->posts.ID";
			$result = $wpdb->get_results( $sql, ARRAY_A );

			$ids = array_map( function($row) {
				return $row['ID'];
			}, $result );
		} else {
			$ids = wp_parse_id_list( $ids );
		}

		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
	}
}
