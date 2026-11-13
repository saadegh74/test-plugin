<?php
/**
 * Jet_Search_Ajax_Handlers class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Ajax_Handlers' ) ) {

	/**
	 * Define Jet_Search_Ajax_Handlers class
	 */
	class Jet_Search_Ajax_Handlers {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   Jet_Search_Ajax_Handlers
		 */
		private static $instance = null;

		/**
		 * Ajax action.
		 *
		 * @var string
		 */
		private $action = 'jet_ajax_search';

		/**
		 * Has navigation.
		 *
		 * @var bool
		 */
		public $has_navigation = false;

		/**
		 * Search query.
		 *
		 * @var array
		 */
		public $search_query = array();

		/**
		 * Table alias.
		 *
		 * @var string
		 */
		private $postmeta_table_alias = 'jetsearch';

		/**
		 * Ajax settings source.
		 *
		 * @var string
		 */
		public $settings_source = 'jet_ajax_search';

		/**
		 * Flag indicating that the internal query for the macro
		 * get_current_results_ids() is currently being executed.
		 *
		 * When this flag is true, pre_get_posts must not modify the query.
		 *
		 * @var bool
		 */
		public $is_building_current_results = false;

		/**
		 * Cached IDs of current JetSearch results.
		 *
		 * @var array
		 */
		public $current_results_ids = array();

		/**
		 * Constructor for the class
		 */
		public function init() {

			if ( false === get_option( 'jet_ajax_search_query_settings' ) ) {
				$this->set_default_query_control_settings();
			}

			// Set search query settings on the search result page
			add_action( 'pre_get_posts', array( $this, 'set_search_query' ) );

			// Search in taxonomy terms
			add_filter( 'posts_search', array( $this, 'set_posts_search' ), 10, 2 );

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				add_action( 'wp_ajax_jet_search_get_query_control_options',   array( $this, 'get_query_control_options' ) );
				add_action( 'wp_ajax_jet_advanced_list_block_get_svg',        array( $this, 'get_icon_svg' ) );
				add_action( 'wp_ajax_suggestions_get_user_id',                array( $this, 'suggestions_get_user_id' ) );
				add_action( 'wp_ajax_nopriv_suggestions_get_user_id',         array( $this, 'suggestions_get_user_id' ) );
				add_action( 'wp_ajax_suggestions_save_settings',              array( $this, 'suggestions_save_settings' ) );
				add_action( 'wp_ajax_suggestions_get_settings',               array( $this, 'suggestions_get_settings' ) );
				add_action( 'wp_ajax_jet_search_save_ajax_search_settings',   array( $this, 'save_ajax_search_settings' ) );
				add_action( 'wp_ajax_jet_search_load_ajax_search_settings',   array( $this, 'load_ajax_search_settings' ) );

				/**
				 * Adds AJAX actions for handling search results and adding/getting suggestions.
				 *
				 * @since 3.5.2
				 */
				add_action( "wp_ajax_{$this->action}",             array( $this, 'get_search_results' ) );
				add_action( "wp_ajax_nopriv_{$this->action}",      array( $this, 'get_search_results' ) );
				add_action( "wp_ajax_get_form_suggestions",        array( $this, 'get_form_suggestions' ) );
				add_action( "wp_ajax_nopriv_get_form_suggestions", array( $this, 'get_form_suggestions' ) );
				add_action( "wp_ajax_add_form_suggestion",         array( $this, 'add_form_suggestion' ) );
				add_action( "wp_ajax_nopriv_add_form_suggestion",  array( $this, 'add_form_suggestion' ) );

				/**
				 * Adds AJAX actions for handling the addition, retrieval, updating, and deletion of search suggestions via AJAX requests.
				 *
				 * @since 3.5.3
				 */
				add_action( "wp_ajax_jet_search_add_suggestion",    array( $this, 'add_suggestion' ) );
				add_action( "wp_ajax_jet_search_get_suggestion",    array( $this, 'get_suggestion' ) );
				add_action( "wp_ajax_jet_search_update_suggestion", array( $this, 'update_suggestion' ) );
				add_action( "wp_ajax_jet_search_delete_suggestion", array( $this, 'delete_suggestion' ) );
				add_action( 'wp_ajax_jet_search_suggestions_remove_duplicates', array( $this, 'suggestions_remove_duplicates' ) );
			}

			// Set Jet Smart Filters extra props
			add_filter( 'jet-smart-filters/filters/localized-data', array( $this, 'set_jet_smart_filters_extra_props' ) );

			// Set custom field post IDs to the range filter search query
			add_filter( 'jet-smart-filters/range-filter/search-query', array( $this, 'set_posts_search' ), 10, 2 );

			// Set JetEngine extra props
			add_filter( 'jet-engine/listing/grid/posts-query-args', array( $this, 'set_jet_engine_extra_props' ), -10, 3 );

			// Set Jet Smart Filters query request
			add_filter( 'jet-smart-filters/query/request', array( $this, 'set_jet_smart_filters_query_request' ), -10, 1 );

			// Set JetWooBuilder extra props
			add_filter( 'jet-woo-builder/shortcodes/jet-woo-products/query-args',      array( $this, 'set_jet_woo_extra_props' ), 10, 2 );
			add_filter( 'jet-woo-builder/shortcodes/jet-woo-products-list/query-args', array( $this, 'set_jet_woo_extra_props' ), 10, 2 );

			add_filter( 'jet-woo-builder/jet-products-loop/custom-validation', array( $this, 'set_jet_woo_builder_products_loop_custom_validation') );
		}

		public function set_jet_woo_builder_products_loop_custom_validation( $result ) {
			$request = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$wc_query        = isset( $request['defaults']['wc_query'] ) ? $request['defaults']['wc_query'] : '';
			$jet_ajax_search = isset( $request['defaults']['jet_ajax_search'] ) ? $request['defaults']['jet_ajax_search'] : false;

			if ( 'product_query' === $wc_query && filter_var( $jet_ajax_search, FILTER_VALIDATE_BOOLEAN ) ) {
				$result = true;
			}

			return $result;
		}

		public function set_jet_smart_filters_query_request( $request ) {
			$wc_query        = isset( $request['defaults']['wc_query'] ) ? $request['defaults']['wc_query'] : '';
			$jet_ajax_search = isset( $request['defaults']['jet_ajax_search'] ) ? $request['defaults']['jet_ajax_search'] : false;

			if ( 'product_query' === $wc_query && filter_var( $jet_ajax_search, FILTER_VALIDATE_BOOLEAN ) ) {

				$args  = $this->get_form_settings();

				$request['defaults']['post_type'] = Jet_Search_Tools::custom_fields_post_type_update( $args['custom_fields_source'], $args['search_source'] );

				if ( ! empty( $args['category__in'] ) ) {
					$tax = ! empty( $args['search_taxonomy'] ) ? $args['search_taxonomy'] : 'category';

					array_push(
						$request['defaults']['tax_query'],
						array(
							'taxonomy' => $tax,
							'field'    => 'id',
							'operator' => 'IN',
							'terms'    => $args['category__in'],
						)
					);
				} else if ( ! empty( $args['include_terms_ids'] ) ) {

					$include_tax_query = array( 'relation' => 'OR' );
					$terms_data        = $this->prepare_terms_data( $args['include_terms_ids'] );

					foreach ( $terms_data as $taxonomy => $terms_ids ) {
						$include_tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'operator' => 'IN',
							'terms'    => $terms_ids,
						);
					}

					array_push(
						$request['defaults']['tax_query'],
						$include_tax_query
					);
				}

				// Exclude specific terms
				if ( ! empty( $args['exclude_terms_ids'] ) ) {

					$exclude_tax_query = array( 'relation' => 'AND' );
					$terms_data        = $this->prepare_terms_data( $args['exclude_terms_ids'] );

					foreach ( $terms_data as $taxonomy => $terms_ids ) {
						$exclude_tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'operator' => 'NOT IN',
							'terms'    => $terms_ids,
						);
					}

					array_push(
						$request['defaults']['tax_query'],
						$exclude_tax_query
					);
				}

				// Exclude specific posts
				if ( ! empty( $args['exclude_posts_ids'] ) ) {
					$request['defaults']['post__not_in'] = $args['exclude_posts_ids'];
				}

				if ( isset( $args['results_order'] ) ) {
					$request['defaults']['order']  = $args['results_order'];
				}

				if ( isset( $args['results_order_by'] ) ) {
					$request['defaults']['orderby'] = $args['results_order_by'];
				}
			}

			return $request;
		}

		public function get_post_ids_by_custom_fields( $query ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$cf_keys = $this->get_cf_search_keys();

			if ( ! $cf_keys ) {
				return '';
			}

			$settings          = null;
			$search            = null;
			$search_post_types = array();

			if ( isset( $_GET['action'] ) && $this->action === $_GET['action']
				&& ! empty( $_GET['data']['custom_fields_source'] )
			) {
				$settings = isset( $_GET['data'] ) ? wp_unslash( $_GET['data'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			} else {
				$settings = $this->get_form_settings();
			}

			if ( empty( $settings ) ) {
				return false;
			}

			global $wpdb;

			$posts_table    = $wpdb->posts;
			$postmeta_table = $wpdb->postmeta;

			$custom_search_query_param = jet_search_ajax_handlers()->get_custom_search_query_param();
			$search_query_param        = ! empty( $_REQUEST[$custom_search_query_param] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $custom_search_query_param ] ) ) : false;

			if ( isset( $_GET['action'] ) && $this->action === $_GET['action']
				&& isset( $_GET['data']['value'] )
				&& ! empty( $_GET['data']['value'] )
			) {
				$search = sanitize_text_field( wp_unslash( $_GET['data']['value'] ) );
			} else if ( false != $search_query_param ) {
				$search = $search_query_param;
			} else {
				$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
			}

			$search_terms = $this->get_title_search_terms( $search );

			if ( empty( $search_terms ) ) {
				return '';
			}

			if ( isset( $settings['current_query'] ) ) {
				$current_query = (array) $settings['current_query'];
				$post_type = isset( $current_query['post_type'] ) ? $current_query['post_type'] : $settings['search_source'];
			} else {
				$post_type = $settings['search_source'];
			}

			$search_post_types = Jet_Search_Tools::custom_fields_post_type_update( $cf_keys, $post_type );

			$posts_ids = $this->get_post_ids_by_postmeta_terms(
				$cf_keys,
				$search_terms,
				$search_post_types,
				$posts_table,
				$postmeta_table
			);

			if ( class_exists( '\Jet_Engine\CPT\Custom_Tables\DB' ) ) {
				$meta_storage_posts_ids = array();

				foreach ( $search_post_types as $post_type ) {
					$meta_db = \Jet_Engine\CPT\Custom_Tables\Manager::instance()->get_db_instance( $post_type );

					if ( $meta_db->is_table_exists() ) {
						$search_columns = array();

						foreach ( $cf_keys as $field ) {
							$column_exists = $meta_db->column_exists( $field );

							if ( ! filter_var( $column_exists, FILTER_VALIDATE_BOOLEAN ) ) {
								continue;
							}

							$search_columns[] = $field;
						}

						if ( ! empty( $search_columns ) ) {
							$meta_storage_posts_ids = array_merge(
								$meta_storage_posts_ids,
								$this->get_post_ids_by_custom_table_terms( $meta_db, $search_columns, $search_terms )
							);
						}
					}
				}
			}

			if ( class_exists( 'WooCommerce' ) && in_array( '_sku', $cf_keys ) ) {
				foreach ( $posts_ids as $key => $value ) {
					$post_id                   = is_object( $value ) ? (int) $value->ID : (int) $value;
					$parent_product            = wc_get_product( $post_id );

					if ( false != $parent_product ) {
						$parent_product_visibility = $parent_product->get_catalog_visibility();

						if ( 'hidden' === $parent_product_visibility
							|| 'catalog' === $parent_product_visibility
						) {
							unset( $posts_ids[ $key ] );
						}
					}
				}
			}

			if ( ! empty( $posts_ids ) ) {
				$excluded_posts_ids = ! empty( $settings['exclude_posts_ids'] ) ? $settings['exclude_posts_ids'] : array();
				$variations_ids     = array();
				$ids                = array();

				if ( array_search( '_sku', $cf_keys ) !== false && ! empty( $excluded_posts_ids ) ) {
					foreach ( $excluded_posts_ids as $value ) {
						$variations_ids = Jet_Search_Tools::get_product_variation_ids( $value );
					}
				}

				foreach ( $posts_ids as $key => $value ) {
					$post_id = is_object( $value ) ? (int) $value->ID : (int) $value;

					if ( ! in_array( $post_id, $excluded_posts_ids ) && ! in_array( $post_id, $variations_ids ) ) {
						$ids[$key] = $post_id;
					}
				}

				if ( ! empty( $meta_storage_posts_ids ) ) {
					$ids = array_merge( $ids, $meta_storage_posts_ids );
				}

				return $ids;
			}

			if ( ! empty( $meta_storage_posts_ids ) ) {
				return $meta_storage_posts_ids;
			}

			return '';
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		protected function get_title_search_terms( $search_string ) {
			$terms = preg_split( '/[\s,]+/u', (string) $search_string, -1, PREG_SPLIT_NO_EMPTY );
			$terms = array_map( 'sanitize_text_field', $terms );
			$terms = array_filter( array_unique( $terms ) );

			return array_values( $terms );
		}

		protected function intersect_found_post_ids( $current_ids, array $new_ids ) {
			$new_ids = array_values( array_unique( array_map( 'intval', $new_ids ) ) );

			if ( null === $current_ids ) {
				return $new_ids;
			}

			return array_values( array_intersect( $current_ids, $new_ids ) );
		}

		protected function get_title_terms_where_sql( $search_string ) {
			$terms = $this->get_title_search_terms( $search_string );

			if ( empty( $terms ) ) {
				return '';
			}

			global $wpdb;

			$clauses = array();

			foreach ( $terms as $term ) {
				$clauses[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like( $term ) . '%' );
			}

			return '( ' . implode( ' AND ', $clauses ) . ' )';
		}

		protected function get_post_ids_by_title_terms( $search_string, $post_types = array() ) {
			$terms = $this->get_title_search_terms( $search_string );

			if ( empty( $terms ) ) {
				return array();
			}

			global $wpdb;

			$where      = array();
			$query_args = array();

			foreach ( $terms as $term ) {
				$where[]      = "{$wpdb->posts}.post_title LIKE %s";
				$query_args[] = '%' . $wpdb->esc_like( $term ) . '%';
			}

			$where[]      = "{$wpdb->posts}.post_status = %s";
			$query_args[] = 'publish';

			if ( ! empty( $post_types ) && 'any' !== $post_types ) {
				$post_types = is_array( $post_types ) ? $post_types : array( $post_types );
				$post_types = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );

				if ( ! empty( $post_types ) ) {
					$placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
					$where[]      = "{$wpdb->posts}.post_type IN ( {$placeholders} )";
					$query_args   = array_merge( $query_args, $post_types );
				}
			}

			$sql = "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE " . implode( ' AND ', $where );

			return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( $sql, $query_args ) ) );
		}

		protected function get_post_ids_by_postmeta_terms( array $fields, array $terms, $post_types, $posts_table, $postmeta_table ) {
			global $wpdb;

			if ( empty( $fields ) || empty( $terms ) ) {
				return array();
			}

			$post_types         = is_array( $post_types ) ? $post_types : array( $post_types );
			$post_types         = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );
			$field_placeholders = implode( ', ', array_fill( 0, count( $fields ), '%s' ) );
			$matched_ids        = null;

			foreach ( $terms as $term ) {
				$where      = array( "pm.meta_key IN ( {$field_placeholders} )", 'pm.meta_value LIKE %s', 'p.post_status = %s' );
				$query_args = $fields;

				$query_args[] = '%' . $wpdb->esc_like( $term ) . '%';
				$query_args[] = 'publish';

				if ( ! empty( $post_types ) && ! in_array( 'any', $post_types, true ) ) {
					$post_type_placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
					$where[]                = "p.post_type IN ( {$post_type_placeholders} )";
					$query_args             = array_merge( $query_args, $post_types );
				}

				$sql = "SELECT DISTINCT p.ID
					FROM {$posts_table} AS p
					INNER JOIN {$postmeta_table} AS pm ON p.ID = pm.post_id
					WHERE " . implode( ' AND ', $where );

				$matched_ids = $this->intersect_found_post_ids(
					$matched_ids,
					$wpdb->get_col( $wpdb->prepare( $sql, $query_args ) )
				);

				if ( empty( $matched_ids ) ) {
					return array();
				}
			}

			return $matched_ids ? array_values( $matched_ids ) : array();
		}

		protected function get_post_ids_by_custom_table_terms( $meta_db, array $search_columns, array $terms ) {
			if ( empty( $search_columns ) || empty( $terms ) ) {
				return array();
			}

			$matched_ids = null;

			foreach ( $terms as $term ) {
				$args = array();

				foreach ( $search_columns as $field ) {
					$args[] = array(
						'field'    => $field,
						'operator' => 'LIKE',
						'value'    => $term,
						'type'     => false,
					);
				}

				$results = $meta_db->query( $args, 0, 0, array(), 'OR' );
				$term_ids = array();

				if ( ! empty( $results ) ) {
					foreach ( $results as $result ) {
						if ( empty( $result['object_ID'] ) ) {
							continue;
						}

						$term_ids[] = (int) $result['object_ID'];
					}
				}

				$matched_ids = $this->intersect_found_post_ids( $matched_ids, $term_ids );

				if ( empty( $matched_ids ) ) {
					return array();
				}
			}

			return $matched_ids ? array_values( $matched_ids ) : array();
		}

		protected function is_fuzzy_public_search_query( $query ) {
			if ( ! $query instanceof \WP_Query ) {
				return false;
			}

			$is_jet_search_query = $query->get( $this->action ) || $query->get( 'jet_ajax_search' );

			if ( ! $is_jet_search_query ) {
				return false;
			}

			return ! filter_var( $query->get( 'sentence' ), FILTER_VALIDATE_BOOLEAN );
		}

		protected function append_fuzzy_title_search( $query, $search_string ) {
			$title_terms_where = $this->get_title_terms_where_sql( $search_string );

			if ( '' === $title_terms_where ) {
				return $query;
			}

			$search_sql = trim( (string) $query );

			if ( '' === $search_sql ) {
				return ' AND ' . $title_terms_where;
			}

			if ( 0 === strpos( $search_sql, 'AND ' ) ) {
				$search_sql = substr( $search_sql, 4 );
			}

			return ' AND ( ' . $search_sql . ' OR ' . $title_terms_where . ' )';
		}

		public function set_posts_search( $query, $type = 'search' ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$wp_query = $type instanceof \WP_Query ? $type : null;

			if ( $wp_query ) {
				$type = 'search';
			}

			$tax_query = new \Jet_Search_Tax_Query();
			$posts_ids = $tax_query->get_posts_ids();

			$custom_fields_posts_ids = $this->get_post_ids_by_custom_fields( $query );
			$title_posts_ids         = array();

			if ( $this->is_fuzzy_public_search_query( $wp_query ) ) {
				$title_posts_ids = $this->get_post_ids_by_title_terms(
					$wp_query->get( 's' ),
					$wp_query->get( 'post_type' )
				);
			}

			$search_by_post_id = apply_filters( 'jet-search/ajax-search/search-by-post-id', false );

			if ( ! empty( $title_posts_ids ) ) {
				$posts_ids = array_merge( (array) $posts_ids, $title_posts_ids );
			}

			if ( ! empty( $posts_ids ) || ! empty( $custom_fields_posts_ids ) || filter_var( $search_by_post_id, FILTER_VALIDATE_BOOLEAN ) ) {

				switch ( $type ) {
					case 'search':
						preg_match( '/\(\(\((.*?)\)\)\)/', $query, $matches );
						break;
					case 'woo_prices':
						preg_match( '/\(\((.*?)\)\)/', $query, $matches );
						break;
				}

					if ( isset( $matches[1] ) ) {
						global $wpdb;

						$search_query = $matches[1];

						if ( ! empty( $posts_ids ) && ! empty( $custom_fields_posts_ids ) ) {
							$posts_ids = array_merge( $posts_ids, $custom_fields_posts_ids );
						} else if ( ! empty( $posts_ids ) ) {
							$posts_ids = $posts_ids;
						} else if ( ! empty( $custom_fields_posts_ids ) ) {
							$posts_ids = $custom_fields_posts_ids;
						}

						$include_posts = '';

						if ( ! empty( $posts_ids ) ) {
							$posts_ids = array_filter( array_map( 'absint', $posts_ids ) );
							$posts_ids = implode( ', ', $posts_ids );

							if ( ! empty( $posts_ids ) ) {
								$include_posts .= " OR $wpdb->posts.ID IN ($posts_ids)";
							}
						}

					$custom_search_query_param = jet_search_ajax_handlers()->get_custom_search_query_param();
					$search_query_param        = ! empty( $_REQUEST[$custom_search_query_param] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $custom_search_query_param ] ) ) : false;

					if ( isset( $_GET['action'] ) && $this->action === $_GET['action']
						&& isset( $_GET['data']['value'] )
						&& ! empty( $_GET['data']['value'] )
					) {
						$search = sanitize_text_field( wp_unslash( $_GET['data']['value'] ) );
					} else if ( false != $search_query_param ) {
						$search = $search_query_param;
					} else {
						$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
					}

					if ( is_numeric( $search ) ) {
						$include_posts .= $wpdb->prepare(" OR {$wpdb->posts}.ID = %d", absint( $search ) );
					}

					switch ( $type)  {
						case 'search':
							$result_query = "(((" . $search_query . "))" . $include_posts . ")";
							break;
						case 'woo_prices':
							$result_query = "((" . $search_query . ")" . $include_posts . ")";
							break;
					}

					$query = str_replace( $matches[0], $result_query, $query );
				}
			}

			if ( $this->is_fuzzy_public_search_query( $wp_query ) ) {
				$query = $this->append_fuzzy_title_search( $query, $wp_query->get( 's' ) );
			}

			return $query;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Get ajax action.
		 *
		 * @since  1.1.2
		 * @return string
		 */
		public function get_ajax_action() {
			return $this->action;
		}

		public function get_ajax_settings_source() {
			return apply_filters( 'jet-search/ajax-settings-source', $this->settings_source );
		}

		public function get_custom_search_query_param() {
			if ( false === get_option( 'jet_ajax_search_query_settings' ) ) {
				return false;
			}

			$settings = get_option( 'jet_ajax_search_query_settings' );

			if ( ! isset( $settings['search_query_param'] ) ) {
				return false;
			}

			return $settings['search_query_param'];
		}

		public function suggestions_save_settings() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Access denied', 'jet-search' ) ) );
			}

			$nonce = ! empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : false; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'jet-search-settings' ) ) {
				wp_send_json_error( array(
					'success' => false,
					array( 'message' => __( 'Nonce validation failed', 'jet-search' )
				) ) );
			}

			$settings = ! empty( $_REQUEST['settings'] ) ? wp_unslash( (array) $_REQUEST['settings'] ) : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ! empty( $settings ) ) {

				$settings_list = array( 'records_limit', 'use_session', 'widget_suggestion_save_permission' );

				foreach ( $settings_list as $setting ) {
					if ( isset( $settings[$setting] ) ) {
						if ( false === get_option( 'jet_search_suggestions_' . $setting ) ) {
							add_option( 'jet_search_suggestions_' . $setting , $settings[$setting] );
						} else {
							update_option( 'jet_search_suggestions_' . $setting, $settings[$setting] );
						}
					}
				}

				wp_send_json_success( array(
					'message' => __( 'Settings saved', 'jet-search' )
				) );
			} else {
				wp_send_json_error( array(
					array( 'message' => __( 'Error', 'jet-search' )
				) ) );
			}
		}

		public function suggestions_get_settings() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Access denied', 'jet-search' ) ) );
			}

			$nonce = ! empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : false; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'jet-search-settings' ) ) {
				wp_send_json_error( array(
					'success' => false,
					array( 'message' => __( 'Nonce validation failed', 'jet-search' )
				) ) );
			}

			$settings_list = array( 'records_limit', 'use_session', 'widget_suggestion_save_permission' );
			$settings      = array();

			foreach ( $settings_list as $setting ) {
				switch ( $setting ) {
					case 'records_limit':
						if ( false === get_option( 'jet_search_suggestions_' . $setting ) ) {
							add_option( 'jet_search_suggestions_' . $setting , 5 );
							$settings[$setting] = 5;
						} else {
							$settings[$setting] = get_option( 'jet_search_suggestions_' . $setting );
						}

						if ( '0' === $settings[$setting] ) {
							update_option( 'jet_search_suggestions_' . $setting, 5 );
							update_option( 'jet_search_suggestions_use_session' , "false" );
						}

						break;
					case 'use_session':
						if ( false === get_option( 'jet_search_suggestions_' . $setting ) ) {
							add_option( 'jet_search_suggestions_' . $setting , "false" );
							$settings[$setting] = "false";
						} else {
							$settings[$setting] = get_option( 'jet_search_suggestions_' . $setting );
						}

						break;
					case 'widget_suggestion_save_permission':
						if ( false === get_option( 'jet_search_suggestions_' . $setting ) ) {
							add_option( 'jet_search_suggestions_' . $setting , "true" );
							$settings[$setting] = "true";
						} else {
							$settings[$setting] = get_option( 'jet_search_suggestions_' . $setting );
						}
						break;
				}
			}

			return wp_send_json_success( array(
				'settings' => $settings
			) );
		}

		/**
		 * Removes duplicate suggestions.
		 *
		 * @since  3.5.3
		 */
		function suggestions_remove_duplicates() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Access denied', 'jet-search' ) ) );
			}

			$nonce = ! empty( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : false; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'jet-search-settings' ) ) {
				wp_send_json_error( array(
					'success' => false,
					array( 'message' => __( 'Nonce validation failed', 'jet-search' )
				) ) );
			}

			global $wpdb;

			$prefix     = 'jet_';
			$table_name = $wpdb->prefix . $prefix . 'search_suggestions';

			$update_query = "
				UPDATE {$table_name} t1
				INNER JOIN (
					SELECT MIN(id) as min_id, TRIM(name) as trimmed_name, SUM(weight) as total_weight
					FROM {$table_name}
					GROUP BY trimmed_name
					HAVING COUNT(*) > 1
				) t2
				ON t1.id = t2.min_id
				SET t1.weight = t2.total_weight;
			";

			$wpdb->query( $update_query );

			$query = "
				DELETE t1 FROM {$table_name} t1
				INNER JOIN {$table_name} t2
				WHERE
					t1.id > t2.id
					AND TRIM(t1.name) = TRIM(t2.name);
			";

			$result = $wpdb->query( $query );

			if ( $result === false ) {
				wp_send_json_error( array(
					array( 'message' => __( 'Error', 'jet-search' )
				) ) );
			} else {
				wp_send_json_success( array(
					'message' => __( 'Duplicates are removed.', 'jet-search' )
				) );
			}
		}

		/**
		 * Returns a SVG code of selected icon
		 *
		 * @return [type] [description]
		 */
		public function get_icon_svg() {

			if ( ! current_user_can( 'upload_files' ) ) {
				wp_send_json_error( 'You are not allowed to do this' );
			}

			$media_id = ! empty( $_GET['media_id'] ) ? absint( $_GET['media_id'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification

			if ( ! $media_id ) {
				wp_send_json_error( 'Media ID not found in the request' );
			}

			$mime = get_post_mime_type( $media_id );

			if ( ! $mime || 'image/svg+xml' !== $mime ) {
				wp_send_json_error( 'This media type is not supported, please use SVG image' );
			}

			$content = apply_filters( 'jet-search/get-svg/content', Jet_Search_Tools::get_svg_attachment_content( $media_id ), $media_id );

			wp_send_json_success( $content );

		}

		/**
		 * Set search query settings on the search result page.
		 *
		 * @param object $query
		 */
		public function set_search_query( $query ) {

			if ( ! is_admin() && is_search() && $query->is_search() ) {

				$form_settings = $this->get_form_settings();

				if ( ! empty( $form_settings ) && $query->is_main_query() ) {
					$this->search_query['s'] = wp_unslash( $_GET['s'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification, , WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

					if ( ! empty( $_REQUEST['jet_search_suggestions_settings'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						$this->set_suggestions_query_settings( $form_settings );
					} else {
						$this->set_query_settings( $form_settings );
					}

					/**
					 * Allow filtering of final search query.
					 */
					$this->search_query = Jet_Search_Tools::prepare_public_search_query_args(
						apply_filters( 'jet-search/ajax-search/query-args', $this->search_query, $this )
					);

					// If the query is created by Query Builder, these query vars are primary.
					if ( isset( $query->query_vars['_query_type'] ) ) {
						$query->query_vars = array_merge( $this->search_query, $query->query_vars );
					} else {
						$query->query_vars = array_merge( $query->query_vars, $this->search_query );
					}

					$query->query_vars = Jet_Search_Tools::prepare_public_search_query_args( $query->query_vars );
				}

				$query = apply_filters( 'jet-search/query/set-search-query', $query );
			}
		}

		/**
		* Set Jet Smart Filters extra props.
		*/
		public function set_jet_smart_filters_extra_props( $data ) {

			$custom_search_query_param = jet_search_ajax_handlers()->get_custom_search_query_param();
			$search_query_param        = ! empty( $_REQUEST[$custom_search_query_param] ) ? wp_unslash( $_REQUEST[$custom_search_query_param] ) : false; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( false === $search_query_param ) {
				if ( ! is_search() ) {
					return $data;
				}
			}

			$settings = $this->get_form_settings();

			if ( ! empty( $settings ) ) {
				if ( false != $search_query_param ) {
					$settings['s'] = $search_query_param;
				}

				if ( function_exists( 'jet_smart_filters' ) && version_compare( jet_smart_filters()->get_version(), '3.6.0', '>=' ) ) {
					$data['extra_props']->jet_ajax_search_settings = json_encode( $settings );
				} else {
					$data['extra_props']['jet_ajax_search_settings'] = json_encode( $settings );
				}

				// For compatibility with Products Loop
				if ( ! empty( $data['queries']['woocommerce-archive'] ) && ! empty( $data['queries']['woocommerce-archive']['default'] ) ) {
					$data['queries']['woocommerce-archive']['default'][ $this->action ] = true;
				}
			}

			return $data;
		}

		/**
		 * Set JetEngine extra props.
		 */
		public function set_jet_engine_extra_props( $args, $render, $settings ) {
			$custom_search_query_param = jet_search_ajax_handlers()->get_custom_search_query_param();
			$search_query_param        = ! empty( $_REQUEST[$custom_search_query_param] ) ?  wp_unslash( $_REQUEST[$custom_search_query_param] ) : false; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( false === $search_query_param ) {
				$is_archive_template = isset( $settings['is_archive_template'] ) && 'yes' === $settings['is_archive_template'];

				if ( ! is_search() || ! $is_archive_template ) {
					return $args;
				}
			}

			$settings = $this->get_form_settings();

			if ( ! empty( $settings ) ) {
				$search_in_post_type = $settings['search_source'];
				$query_post_type     = isset( $args['post_type'] ) ? $args['post_type'] : array();

				$search_in_post_type = ! is_array( $search_in_post_type ) ? [ $search_in_post_type ] : $search_in_post_type;
				$query_post_type     = ! is_array( $query_post_type ) ? [ $query_post_type ] : $query_post_type;

				if ( ! empty( array_intersect( $search_in_post_type, $query_post_type ) ) && false != $search_query_param ) {

					if ( ! empty( $settings ) ) {
						$args['post_type']                = $settings['search_source'];
						$args['s']                        = $search_query_param;
						$args['jet_ajax_search_settings'] = $settings;
						$args                             = array_merge( $args, (array) $settings );

						if ( ! empty( $settings['current_query'] ) && is_object( $settings['current_query'] ) ) {
							$args = Jet_Search_Tools::merge_public_current_query_args(
								$args,
								(array) $settings['current_query']
							);
						}
					}
				} else {
					if ( ! empty( $settings ) ) {
						if ( false === $search_query_param ) {
							$args[ $this->action ]            = true;
							$args['jet_ajax_search_settings'] = $settings;
						}
					}
				}

				if ( isset( $args['post_type'] ) ) {
					$args['post_type'] = Jet_Search_Tools::custom_fields_post_type_update( $settings['custom_fields_source'], $args['post_type'] );
				}
			}

			return $args;
		}

		/**
		 * Set JetWooBuilder extra props
		 */
		public function set_jet_woo_extra_props( $args, $shortcode ) {

			$use_current_query         = $shortcode->get_attr( 'use_current_query' );
			$use_current_query         = filter_var( $use_current_query, FILTER_VALIDATE_BOOLEAN );
			$custom_search_query_param = jet_search_ajax_handlers()->get_custom_search_query_param();
			$search_query_param        = ! empty( $_REQUEST[$custom_search_query_param] ) ? wp_unslash( $_REQUEST[$custom_search_query_param] ) : false; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( false === $search_query_param ) {
				if ( ! is_search() || ! $use_current_query ) {
					return $args;
				}
			}

			$settings = $this->get_form_settings();

			if ( ! empty( $settings ) ) {
				$search_in_post_type = $settings['search_source'];
				$query_post_type     = isset( $args['post_type'] ) ? $args['post_type'] : array();

				$search_in_post_type = ! is_array( $search_in_post_type ) ? [ $search_in_post_type ] : $search_in_post_type;
				$query_post_type     = ! is_array( $query_post_type ) ? [ $query_post_type ] : $query_post_type;
				if ( ! empty( array_intersect( $search_in_post_type, $query_post_type ) ) && false != $search_query_param ) {

					if ( ! empty( $settings ) ) {
						$args['post_type']                = $settings['search_source'];
						$args['s']                        = $search_query_param;
						$args                             = array_merge( $args, (array) $settings );

						if ( ! empty( $settings['current_query'] ) && is_object( $settings['current_query'] ) ) {
							$args = Jet_Search_Tools::merge_public_current_query_args(
								$args,
								(array) $settings['current_query']
							);
						}
					}
				} else {
					if ( ! empty( $settings ) ) {
						if ( false === $search_query_param ) {
							$args[ $this->action ] = true;
						}
					}
				}
			}

			return $args;
		}

		/**
		 * Get form settings on the search result page.
		 *
		 * @return array
		 */
		public function get_form_settings() {
			// phpcs:disable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$form_settings          = array();
			$default_query_settings = array();
			$search_settings        = isset( $_REQUEST['jsearch'] ) ? true : false;
			$search_categories      = ! empty( $_REQUEST['jet_ajax_search_categories'] ) ? wp_unslash( $_REQUEST['jet_ajax_search_categories'] ) : '';
			$default_query_settings = get_option( 'jet_ajax_search_query_settings' );

			// Ajax search form settings

			if ( ! empty( $_REQUEST['jet_ajax_search_settings'] ) ) {
				$form_settings = wp_unslash( $_REQUEST['jet_ajax_search_settings'] );
				$form_settings = stripcslashes( $form_settings );
				$form_settings = json_decode( $form_settings, true );
				$form_settings = ( JSON_ERROR_NONE === json_last_error() && is_array( $form_settings ) ) ? $form_settings : array();
			} elseif ( ! empty( $_REQUEST['query']['jet_ajax_search_settings'] ) ) {
				$form_settings =  wp_unslash( $_REQUEST['query']['jet_ajax_search_settings'] );
			}

			//Suggestions form settings

			if ( ! empty( $_REQUEST['jet_search_suggestions_settings'] ) ) {
				$form_settings = wp_unslash( $_REQUEST['jet_search_suggestions_settings'] );
				$form_settings = stripcslashes( $form_settings );
				$form_settings = json_decode( $form_settings, true );
				$form_settings = ( JSON_ERROR_NONE === json_last_error() && is_array( $form_settings ) ) ? $form_settings : array();
			} elseif ( ! empty( $_REQUEST['query']['jet_search_suggestions_settings'] ) ) {
				$form_settings = wp_unslash( $_REQUEST['query']['jet_search_suggestions_settings'] );
			}
			// phpcs:enable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( false != $default_query_settings && ! empty( $default_query_settings ) ) {
				$widget_current_query = ! empty( $form_settings['current_query'] ) ? $form_settings['current_query'] : '';

				$default_query_settings = \Jet_Search_Tools::prepared_default_search_query_settings( $default_query_settings, $widget_current_query );
			}

			if ( true === $search_settings ) {
				$form_settings = $default_query_settings;
			} else {
				if ( ! empty( $form_settings ) && ! empty( $default_query_settings ) ) {
					foreach ( $default_query_settings as $key => $value ) {
						if ( !array_key_exists( $key, $form_settings ) ) {
							$form_settings[$key] = $value;
						}
					}
				}
			}

			if ( '' != $search_categories ) {
				$form_settings['category__in'] = $search_categories;
			}

			if ( isset( $form_settings['search_results_target_widget_id'] ) ) {
				$val = $form_settings['search_results_target_widget_id'];

				if ( is_scalar( $val ) ) {
					$val = sanitize_text_field( (string) $val );

					if ( ! preg_match( '/^[A-Za-z0-9_-]+$/', $val ) ) {
						$val = '';
					}

				} else {
					$val = '';
				}

				$form_settings['search_results_target_widget_id'] = $val;
			}

			foreach ( ['include_terms_ids','exclude_terms_ids'] as $key ) {
				if ( ! empty( $form_settings[$key] ) ) {
					$form_settings[$key] = array_values( array_filter(
						array_unique( array_map( 'intval', (array) $form_settings[ $key ] ) ),
						function( $id ) {
							$term = get_term( $id );
							return $term && ! is_wp_error( $term );
						}
					) );
				}
			}

			return $form_settings;
		}

		/**
		 * Set search query settings.
		 *
		 * @param array $args
		 */
		protected function set_query_settings( $args = array() ) {
			if ( $args ) {
				$this->search_query[ $this->action ] = true;
				$this->search_query['cache_results'] = true;
				$this->search_query['post_type']     = $args['search_source'];
				$this->search_query['order']         = isset( $args['results_order'] ) ? $args['results_order'] : '';
				$this->search_query['orderby']       = isset( $args['results_order_by'] ) ? $args['results_order_by'] : '';
				$this->search_query['tax_query']     = array( 'relation' => 'AND' );
				$this->search_query['sentence']      = isset( $args['sentence'] ) ? filter_var( $args['sentence'], FILTER_VALIDATE_BOOLEAN ) : false;
				$this->search_query['post_status']   = 'publish';

				// Include specific terms
				if ( ! empty( $args['category__in'] ) ) {
					$tax = ! empty( $args['search_taxonomy'] ) ? $args['search_taxonomy'] : 'category';

					array_push(
						$this->search_query['tax_query'],
						array(
							'taxonomy' => $tax,
							'field'    => 'id',
							'operator' => 'IN',
							'terms'    => $args['category__in'],
						)
					);
				} else if ( ! empty( $args['include_terms_ids'] ) ) {

					$include_tax_query = array( 'relation' => 'OR' );
					$terms_data        = $this->prepare_terms_data( $args['include_terms_ids'] );

					foreach ( $terms_data as $taxonomy => $terms_ids ) {
						$include_tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'operator' => 'IN',
							'terms'    => $terms_ids,
						);
					}

					array_push(
						$this->search_query['tax_query'],
						$include_tax_query
					);
				}

				// Exclude specific terms
				if ( ! empty( $args['exclude_terms_ids'] ) ) {

					$exclude_tax_query = array( 'relation' => 'AND' );
					$terms_data        = $this->prepare_terms_data( $args['exclude_terms_ids'] );

					foreach ( $terms_data as $taxonomy => $terms_ids ) {
						$exclude_tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'operator' => 'NOT IN',
							'terms'    => $terms_ids,
						);
					}

					array_push(
						$this->search_query['tax_query'],
						$exclude_tax_query
					);
				}

				// Exclude specific posts
				if ( ! empty( $args['exclude_posts_ids'] ) ) {
					$this->search_query['post__not_in'] = $args['exclude_posts_ids'];
				}

				// Current Query
				if ( ! empty( $args['current_query'] ) ) {
					$this->search_query = Jet_Search_Tools::merge_public_current_query_args(
						$this->search_query,
						(array) $args['current_query']
					);
				}

				if ( ! empty( $args['custom_fields_source'] ) ) {
					$this->search_query['post_type'] = Jet_Search_Tools::custom_fields_post_type_update( $args['custom_fields_source'], $this->search_query['post_type'] );
				}

				do_action( 'jet-search/ajax-search/search-query', $this, $args );
			}
		}

		/**
		 * Set suggestions search query settings.
		 *
		 * @param array $args
		 */
		protected function set_suggestions_query_settings( $args = array() ) {
			if ( $args ) {
				$this->search_query['cache_results'] = true;
				$this->search_query['tax_query']     = array( 'relation' => 'AND' );
				$this->search_query['post_status']   = 'publish';

				// Include specific terms
				if ( ! empty( $args['category__in'] ) ) {
					$tax = ! empty( $args['search_taxonomy'] ) ? $args['search_taxonomy'] : 'category';

					array_push(
						$this->search_query['tax_query'],
						array(
							'taxonomy' => $tax,
							'field'    => 'id',
							'operator' => 'IN',
							'terms'    => $args['category__in'],
						)
					);
				}

				// Current Query
				if ( ! empty( $args['current_query'] ) ) {
					$this->search_query = Jet_Search_Tools::merge_public_current_query_args(
						$this->search_query,
						(array) $args['current_query']
					);
				}

				do_action( 'jet-search/search-suggestions/search-query', $this, $args );
			}
		}

		/**
		 * Get Query control options list.
		 *
		 * @since  2.0.0
		 * @return void
		 */
		function get_query_control_options() {

			$data = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification

			$q = isset( $data['q'] ) ? sanitize_text_field( wp_unslash( $data['q'] ) ) : '';

			$post_types = array();

			if ( isset( $data['post_type'] ) ) {
				if ( is_array( $data['post_type'] ) ) {
					$post_types = array_map( 'sanitize_key', $data['post_type'] );
				} elseif ( is_string( $data['post_type'] ) && '' !== $data['post_type'] ) {
					$post_types = array_map( 'sanitize_key', array_map( 'trim', explode( ',', $data['post_type'] ) ) );
				}
			}

			if ( ! isset( $data['query_type'] ) ) {
				wp_send_json_error();
				return;
			}

			$results = array();
			$is_bricks_builder = isset( $data['bricks-is-builder'] ) && "1" === $data['bricks-is-builder'];

			switch ( $data['query_type'] ) {
				case 'terms':

					$terms_args = array(
						'hide_empty' => false,
					);

					if ( $q ) {
						$terms_args['search'] = $q;
					}

					if ( $is_bricks_builder ) {
						$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );

						if ( class_exists( 'WooCommerce' ) ) {
							foreach ( array( 'product_cat', 'product_tag', 'product_visibility', 'product_shipping_class' ) as $woo_tax ) {

								if ( taxonomy_exists( $woo_tax ) ) {
									$taxonomies[] = $woo_tax;
								}

							}

							if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
								foreach ( wc_get_attribute_taxonomies() as $attr ) {
									$tax = wc_attribute_taxonomy_name( $attr->attribute_name );

									if ( taxonomy_exists( $tax ) ) {
										$taxonomies[] = $tax;
									}

								}
							}
						}

						$terms_args['taxonomy'] = array_values( array_unique( (array) $taxonomies ) );

					} else {

						if ( ! empty( $post_types ) ) {
							$object = ( 1 === count( $post_types ) ) ? $post_types[0] : $post_types;

							$terms_args['taxonomy'] = get_object_taxonomies( $object, 'names' );
						} else {
							$terms_args['taxonomy'] = get_taxonomies( array( 'show_in_nav_menus' => true ), 'names' );
						}

					}

					if ( ! empty( $data['ids'] ) ) {
						$terms_args['include'] = $data['ids'];
					}

					$terms = get_terms( $terms_args );

					global $wp_taxonomies;

					foreach ( $terms as $term ) {

						if ( $is_bricks_builder ) {
							$results[ (int)$term->term_id] = sprintf( '%1$s: %2$s', $wp_taxonomies[ $term->taxonomy ]->label, $term->name );
						} else {
							$results[] = array(
								'id'   => $term->term_id,
								'text' => sprintf( '%1$s: %2$s', $wp_taxonomies[ $term->taxonomy ]->label, $term->name ),
							);
						}
					}

					break;

				case 'posts':

					$query_args = array(
						'post_type'           => 'any',
						'posts_per_page'      => - 1,
						'suppress_filters'    => false,
						'ignore_sticky_posts' => true,
					);

					if ( ! empty( $data['q'] ) ) {
						$query_args['s_title'] = $data['q'];
						$query_args['orderby'] = 'relevance';
					}

					if ( ! empty( $data['post_type'] ) ) {
						if ( isset( $data['is_global_settings'] ) && 'true' === $data['is_global_settings'] ) {
							$query_args['post_type'] = explode(",", $data['post_type'] );
						} else {
							$query_args['post_type'] = $data['post_type'];
						}
					}

					if ( ! empty( $data['ids'] ) ) {
						if ( isset( $data['is_global_settings'] ) && 'true' === $data['is_global_settings'] ) {
							$query_args['post__in'] = explode(",", $data['ids'] );
						} else {
							$query_args['post__in'] = $data['ids'];
						}
					}

					add_filter( 'posts_where', array( $this, 'force_search_by_title' ), 10, 2 );

					$posts = get_posts( $query_args );

					remove_filter( 'posts_where', array( $this, 'force_search_by_title' ), 10 );

					foreach ( $posts as $post ) {
						if ( $is_bricks_builder ) {
							$results[ (int)$post->ID] = sprintf( '%1$s: %2$s', ucfirst( $post->post_type ), $post->post_title );
						} else {
							$results[] = array(
								'id'   => $post->ID,
								'text' => sprintf( '%1$s: %2$s', ucfirst( $post->post_type ), $post->post_title ),
							);
						}
					}

					break;
			}

			if ( $is_bricks_builder ) {
				$data = $results;

			} else {
				$data = array(
					'results' => $results,
				);
			}

			wp_send_json_success( $data );
		}

		/**
		 * Set Default Query control options.
		 *
		 * @since  3.3.0
		 * @return void
		 */

		public function set_default_query_control_settings() {

			$settings = array(
				'show_search_category_list' => 'false',
				'search_taxonomy'           => 'category',
				'current_query'             => 'false',
				'search_query_param'        => 'jet_search',
				'search_results_url'        => '',
				'search_source'             => array(),
				'include_terms_ids'         => array(),
				'exclude_terms_ids'         => array(),
				'exclude_posts_ids'         => array(),
				'custom_fields_source'      => '',
				'sentence'                  => 'false',
				'search_in_taxonomy'        => 'false',
				'search_in_taxonomy_source' => array(),
				'results_order_by'          => 'relevance',
				'results_order'             => 'asc',
				'catalog_visibility'        => 'false'
			);

			add_option( 'jet_ajax_search_query_settings', $settings );
		}

		/**
		 * Save Query control options.
		 *
		 * @since  3.3.0
		 * @return void
		 */
		public function save_ajax_search_settings() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'Access denied', 'jet-search' ) ) );
			}

			$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : false; // phpcs:ignore

			if ( ! $nonce || ! wp_verify_nonce( $nonce, 'jet-search-settings' ) ) {
				wp_send_json_error( array( 'message' => __( 'Nonce validation failed', 'jet-search' ) ) );
			}

			$data = $_REQUEST;

			if ( ! isset( $data['query_settings'] ) || ! isset( $data['request_settings'] ) ) {
				return wp_send_json_error();
			}

			if ( isset( $data['query_settings'] ) ) {
				$query_settings = json_decode( stripslashes( $data['query_settings'] ), true );

				if ( false === get_option( 'jet_ajax_search_query_settings' ) ) {
					add_option( 'jet_ajax_search_query_settings', $query_settings );
				} else {
					update_option( 'jet_ajax_search_query_settings', $query_settings );
				}
			}

			if ( isset( $data['request_settings'] ) ) {
				$request_settings = json_decode( stripslashes( $data['request_settings'] ), true );

				if ( false === get_option( 'jet_ajax_search_request_settings' ) ) {
					add_option( 'jet_ajax_search_request_settings', $request_settings );
				} else {
					update_option( 'jet_ajax_search_request_settings', $request_settings );
				}
			}

			return wp_send_json_success();
		}

		/**
		 * Save Query control options.
		 *
		 * @since  3.3.0
		 * @return void
		 */
		public function load_ajax_search_settings() {
			$resultData['query_settings']   = get_option( 'jet_ajax_search_query_settings' );
			$resultData['request_settings'] = get_option( 'jet_ajax_search_request_settings' );

			return wp_send_json_success( $resultData );
		}

		/**
		 * Force query to look in post title while searching.
		 *
		 * @since  2.0.0
		 * @param  string $where
		 * @param  object $query
		 * @return string
		 */
		public function force_search_by_title( $where, $query ) {

			$args = $query->query;

			if ( ! isset( $args['s_title'] ) ) {
				return $where;
			}

			$title_terms_where = $this->get_title_terms_where_sql( $args['s_title'] );

			if ( '' !== $title_terms_where ) {
				$where .= ' AND ' . $title_terms_where;
			}

			return $where;
		}

		/**
		 * Prepare terms data for tax query
		 *
		 * @since  2.0.0
		 * @param  array $terms_ids
		 * @return array
		 */
		public function prepare_terms_data( $terms_ids = array() ) {

			$result = array();

			foreach ( $terms_ids as $term_id ) {
				$term = get_term( $term_id );

				if ( ! empty( $term ) ) {
					$taxonomy = $term->taxonomy;

					$result[ $taxonomy ][] = $term_id;
				}
			}

			return $result;
		}

		/**
		 * Get custom fields keys for search
		 *
		 * @since  2.0.0
		 * @return array|bool
		 */
		public function get_cf_search_keys() {

			if ( isset( $_GET['action'] ) && $this->action === $_GET['action'] && ! empty( $_GET['data']['custom_fields_source'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$cf_source = sanitize_text_field( wp_unslash ( $_GET['data']['custom_fields_source'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			} else {
				$settings  = $this->get_form_settings();
				$cf_source = ! empty( $settings['custom_fields_source'] ) ? $settings['custom_fields_source'] : false;
			}

			if ( empty( $cf_source ) ) {
				return false;
			}

			return explode( ',', str_replace( ' ', '', $cf_source ) );
		}

		/**
		 * Extract limit query from data array.
		 *
		 * @since  2.0.0
		 * @param  array $data
		 * @return int
		 */
		public function extract_limit_query( $data ) {
			$limit_query = ! empty( $data['limit_query'] ) ? $data['limit_query'] : 5;

			if ( empty( $data['deviceMode'] ) ) {
				return $limit_query;
			}

			$limit_query_tablet = ! empty( $data['limit_query_tablet'] ) ? $data['limit_query_tablet'] : $limit_query;
			$limit_query_mobile = ! empty( $data['limit_query_mobile'] ) ? $data['limit_query_mobile'] : $limit_query_tablet;

			switch ( $data['deviceMode'] ) {
				case 'tablet':
					$limit_query = $limit_query_tablet;
					break;

				case 'mobile':
					$limit_query = $limit_query_mobile;
					break;
			}

			return $limit_query;
		}

		/**
		 * Return result area navigation.
		 *
		 * @param array $settings
		 *
		 * @return array
		 */
		public function get_results_navigation( $settings = array() ) {
			$navigation_container_html = apply_filters(
				'jet-search/ajax-search/navigation-container-html',
				'<div class="jet-ajax-search__navigation-container">%s</div>'
			);

			$navigation_types = apply_filters(
				'jet-search/ajax-search/navigation-types',
				array( 'bullet_pagination', 'number_pagination', 'navigation_arrows' )
			);

			$header_navigation = '';
			$footer_navigation = '';
			if ( $settings['limit_query'] < $settings['post_count'] ) {

				foreach ( $navigation_types as $type ) {
					if ( ! isset( $settings[ $type ] ) ) {
						continue;
					}

					if ( ! $settings[ $type ] ) {
						continue;
					}

					$buttons = $this->get_navigation_buttons_html( $settings, $type );

					if ( empty( $buttons ) ) {
						continue;
					}

					$this->has_navigation = true;

					switch ( $settings[ $type ] ) {
						case 'in_header':
							$header_navigation .= sprintf( $navigation_container_html, $buttons );
							break;

						case 'in_footer':
							$footer_navigation .= sprintf( $navigation_container_html, $buttons );
							break;

						case 'both':
							$header_navigation .= sprintf( $navigation_container_html, $buttons );
							$footer_navigation .= sprintf( $navigation_container_html, $buttons );
							break;
					}
				}
			}

			return array(
				'in_header' => $header_navigation,
				'in_footer' => $footer_navigation,
			);
		}

		/**
		 * Get results navigation buttons html.
		 *
		 * @param array  $settings
		 * @param string $type
		 *
		 * @return string
		 */
		public function get_navigation_buttons_html( $settings = array(), $type = 'bullet_pagination' ) {
			$output_html = '';
			$bullet_html = apply_filters( 'jet-search/ajax-search/navigate-button-html', '<button role=button class="jet-ajax-search__navigate-button %1$s" data-number="%2$s" aria-label="%3$s"></button>' );

			switch ( $type ) {
				case 'bullet_pagination':
					$button_class = 'jet-ajax-search__bullet-button';

				case 'number_pagination':
					$button_class = isset( $button_class ) ? $button_class : 'jet-ajax-search__number-button';

					for ( $i = 0; $i < $settings['columns']; $i++ ) {
						$active_button_class = ( $i === 0 ) ? ' jet-ajax-search__active-button' : '' ;
						$aria_label   = sprintf( __( 'Page %d', 'jet-search' ), $i + 1 );
						$output_html .= sprintf( $bullet_html, $button_class . $active_button_class, $i + 1, esc_attr( $aria_label ) );
					}
					break;

				case 'navigation_arrows':
					$prev_button = apply_filters( 'jet-search/ajax-search/prev-button-html', '<button role=button class="jet-ajax-search__prev-button jet-ajax-search__arrow-button jet-ajax-search__navigate-button jet-ajax-search__navigate-button-disable" data-direction="-1" aria-label="' . esc_attr__( 'Previous page', 'jet-search' ) . '">%s</button>' );
					$next_button = apply_filters( 'jet-search/ajax-search/next-button-html', '<button role=button class="jet-ajax-search__next-button jet-ajax-search__arrow-button jet-ajax-search__navigate-button" data-direction="1" aria-label="' . esc_attr__( 'Next page', 'jet-search' ) . '">%s</button>' );
					$arrow       = Jet_Search_Tools::get_svg_arrows( $settings['navigation_arrows_type'] );
					$output_html = sprintf( $prev_button . $next_button, $arrow['left'], $arrow['right'] );
					break;
			}

			return $output_html;
		}

		/**
		 * Get search results.
		 *
		 * @since 3.5.2
		 */
		public function get_search_results() {

			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], $this->action ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				wp_send_json_error( array(
					'message' => 'Invalid Nonce!'
				) );

				return;
			}

			$data = $this->get_search_data();

			if ( empty( $data ) ) {
				wp_send_json_error( array(
					'message' => 'Empty Search Data'
				) );

				return;
			}

			wp_send_json_success( $data );
		}

		/**
		 * Get search data.
		 *
		 * @since 3.5.2
		 * @return array|bool
		 */
		public function get_search_data() {
			if ( empty( $_GET['data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return false;
			}

			$data                                      = isset( $_GET['data'] ) ? wp_unslash( $_GET['data'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$data                                      = Jet_Search_Tools::prepare_public_meta_settings( $data );
			$lang									   = isset( $_GET['lang'] ) ? sanitize_text_field( wp_unslash ( $_GET['lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$this->search_query['s']                   = urldecode( esc_sql( $data['value'] ) );
			$this->search_query['nopaging']            = false;
			$this->search_query['ignore_sticky_posts'] = false;
			$this->search_query['posts_per_page']      = isset( $data['limit_query_in_result_area'] ) ? ( int ) $data['limit_query_in_result_area'] : 25;
			$this->search_query['post_status']         = 'publish';

			$this->set_query_settings( $data );

			// Polylang, WPML Compatibility
			if ( '' != $lang ) {
				$this->search_query['lang'] = $lang;
			}

			add_filter( 'wp_query_search_exclusion_prefix', '__return_empty_string' );

			$this->search_query['post_type'] = Jet_Search_Tools::custom_fields_post_type_update( $data['custom_fields_source'] ?? '', $this->search_query['post_type'] );

			//Translatepress Compatibility
			if ( class_exists( 'TRP_Translate_Press' ) ) {
				add_filter( 'trp_force_search', '__return_true' );
			}

			$this->search_query = Jet_Search_Tools::prepare_public_search_query_args(
				apply_filters( 'jet-search/ajax-search/query-args', $this->search_query, $this )
			);

			$search = new WP_Query( $this->search_query );

			if ( class_exists( 'TRP_Translate_Press' ) ) {
				remove_filter( 'trp_force_search', '__return_true' );
			}

			if ( function_exists( 'relevanssi_do_query' ) ) {
				relevanssi_do_query( $search );
			}

			Jet_Search_Tools::filter_public_search_query_results( $search );

			$response = array(
				'error'         => false,
				'post_count'    => 0,
				'message'       => '',
				'posts'         => null,
				'listing_items' => array(),
			);

			remove_filter( 'wp_query_search_exclusion_prefix', '__return_empty_string' );

			if ( is_wp_error( $search ) ) {
				$allowed_tags = Jet_Search_Tools::get_allowed_html_tags();
				$message      = isset( $data['server_error'] ) ? wp_kses_post( $data['server_error'] ) : '';

				$response['error']   = true;
				$response['message'] = wpautop( wp_kses( $message, $allowed_tags ) );

				return wp_send_json_success( $response );
			}

			$data['limit_query'] = jet_search_ajax_handlers()->extract_limit_query( $data );

			$data['post_count'] = $search->post_count;
			$data['columns']    = ceil( $data['post_count'] / $data['limit_query'] );

			if ( isset( $data['highlight_searched_text'] ) && '' != $data['highlight_searched_text'] ) {
				$response['search_value']     = $this->search_query['s'];
				$response['search_highlight'] = true;
			} else {
				$response['search_highlight'] = false;
			}

			$response['posts']              = array();
			$response['columns']            = $data['columns'];
			$response['limit_query']        = $data['limit_query'];
			$response['post_count']         = $data['post_count'];
			$response['results_navigation'] = jet_search_ajax_handlers()->get_results_navigation( $data );
			$response['listing_items']      = array();
			$response['sources']            = array();

			$open_result_new_tab = isset( $data['show_result_new_tab'] )
				? filter_var( $data['show_result_new_tab'], FILTER_VALIDATE_BOOLEAN )
				: false;

			$link_target_attr = $open_result_new_tab ? '_blank' : '';

			$listing_id = ! empty( $data['listing_id'] ) ? (int) $data['listing_id'] : '';

			$sources = null;

			if ( class_exists( 'Jet_Search\Search_Sources\Manager' ) ) {
				$sources_manager       = jet_search()->search_sources;
				$sources               = $sources_manager->get_sources();
				$sources_results_count = 0;

				foreach ( $sources as $key => $source ) {

					if ( ! isset( $data['search_source_' . $key] ) ) {
						continue;
					}

					if ( filter_var( $data['search_source_' . $key], FILTER_VALIDATE_BOOLEAN ) ) {
						$source->set_args( $data );
						$source->set_search_string( $this->search_query['s'] );

						$listing_template = false;

						if ( isset( $data['search_source_' . $key . '_listing_id'] ) ) {
							$listing_template = ! empty( $data['search_source_' . $key . '_listing_id'] ) ?? true;
						}

						$response['sources'][] = array(
							'priority'         => $source->get_priority(),
							'type'             => $source->get_name(),
							'content'          => $source->render(),
							'listing_template' => $listing_template,
						);

						$sources_results_count += $source->get_results_count();
					}
				}

				$response['sources_results_count'] = $sources_results_count;
			}

			do_action_ref_array( 'jet-search/ajax-search/search-results', array( &$response, &$search->posts, $data, $sources ) );

			Jet_Search_Tools::filter_public_search_query_results( $search );

			$data['post_count'] = $search->post_count;
			$data['columns']    = ceil( $data['post_count'] / $data['limit_query'] );

			$response['columns']            = $data['columns'];
			$response['post_count']         = $data['post_count'];
			$response['results_navigation'] = jet_search_ajax_handlers()->get_results_navigation( $data );

			if ( $response['post_count'] > $response['limit_query'] ) {
				$this->has_navigation = true;
			}

			if ( '' != $listing_id && function_exists( 'jet_engine' ) ) {
				if ( class_exists( 'Elementor\Plugin' ) && 'elementor' === jet_engine()->listings->data->get_listing_type( $listing_id ) ) {
					Elementor\Plugin::instance()->frontend->register_styles();
					Elementor\Plugin::instance()->frontend->register_scripts();
					\Jet_Search_Tools::maybe_enqueue_elementor_listing_assets( $listing_id );
				}

				$initial_object = jet_engine()->listings->data->get_current_object();

				$jet_engine_frontend = jet_engine()->frontend;
				$jet_engine_frontend->set_listing( $listing_id );

				$listing_items = array();

				foreach ( $search->posts as $post ) {
					$content = '';
					$item_classes = array(
						'jet-ajax-search__results-item',
						'jet-listing-dynamic-post-' . $post->ID,
					);

					$jet_engine_frontend->set_listing( $listing_id );

					ob_start();

					$content = $jet_engine_frontend->get_listing_item( $post );

					$inline_css = ob_get_clean();

					if ( ! empty( $inline_css ) ) {
						$content = $inline_css . $content;
					}

					if ( function_exists( 'wc_get_product' ) && wc_get_product( $post->ID ) ) {
						$item_classes[] = 'woocommerce';
						$item_classes[] = 'product';
					}

					$content = sprintf( '<div class="%1$s">%2$s</div>', esc_attr( implode( ' ', $item_classes ) ), $content );

					$listing_items[] = $content;
				}

				jet_engine()->frontend->reset_data();
				jet_engine()->listings->data->set_current_object( $initial_object );

				$response['listing_items'] = $listing_items;

				\Jet_Search_Tools::maybe_add_enqueue_assets_data( $response );
			} else {

				foreach ( $search->posts as $key => $post ) {

					$response['posts'][ $key ] = array(
						'title'            => $post->post_title,
						'before_title'     => Jet_Search_Template_Functions::get_meta_fields( $data, $post, 'title_related', 'jet-search-title-fields', array( 'before' ) ),
						'after_title'      => Jet_Search_Template_Functions::get_meta_fields( $data, $post, 'title_related', 'jet-search-title-fields', array( 'after' ) ),
						'content'          => Jet_Search_Template_Functions::get_post_content( $data, $post ),
						'before_content'   => Jet_Search_Template_Functions::get_meta_fields( $data, $post, 'content_related', 'jet-search-content-fields', array( 'before' ) ),
						'after_content'    => Jet_Search_Template_Functions::get_meta_fields( $data, $post, 'content_related', 'jet-search-content-fields', array( 'after' ) ),
						'thumbnail'        => Jet_Search_Template_Functions::get_post_thumbnail( $data, $post ),
						'link'             => esc_url( get_permalink( $post->ID ) ),
						'link_target_attr' => $link_target_attr,
						'price'            => Jet_Search_Template_Functions::get_product_price( $data, $post ),
						'rating'           => Jet_Search_Template_Functions::get_product_rating( $data, $post ),
					);

					$show_add_to_cart = $data['show_add_to_cart'] ?? '';

					if ( function_exists( 'WC' ) && ( 'yes' === $show_add_to_cart || 'true' === $show_add_to_cart ) ) {
						$product = wc_get_product( $post->ID );

						if ( ! empty( $product ) && $product->is_purchasable() && $product->is_in_stock() ) {
							$max_purchase_quantity = $product->get_max_purchase_quantity();
							$min_purchase_quantity = max( 1, $product->get_min_purchase_quantity() );
							$quantity_step         = method_exists( $product, 'get_purchase_quantity_step' ) ? $product->get_purchase_quantity_step() : 1;

							$response['posts'][$key]['is_product']       = true;
							$response['posts'][$key]['product_id']       = $product->get_id();
							$response['posts'][$key]['product_type']     = 'product_type_' . $product->get_type();
							$response['posts'][$key]['product_sku']      = $product->get_sku();
							$response['posts'][$key]['product_label']    = $product->add_to_cart_description();
							$response['posts'][$key]['product_url']      = $product->add_to_cart_url();
							$response['posts'][$key]['product_add_text'] = $product->add_to_cart_text();
							$response['posts'][$key]['product_supports_ajax_add_to_cart'] = $product->supports( 'ajax_add_to_cart' );
							$response['posts'][$key]['product_sold_individually']         = $product->is_sold_individually();
							$response['posts'][$key]['product_qty_min']                   = $min_purchase_quantity;
							$response['posts'][$key]['product_qty_max']                   = 0 < $max_purchase_quantity ? $max_purchase_quantity : '';
							$response['posts'][$key]['product_qty_step']                  = max( 1, $quantity_step );
							$response['posts'][$key]['product_qty_value']                 = $min_purchase_quantity;
						}
					}

					$custom_post_data = apply_filters( 'jet-search/ajax-search/custom-post-data', array(), $data, $post );

					if ( ! empty( $custom_post_data ) ) {
						$response['posts'][ $key ] = array_merge( $response['posts'][ $key ], $custom_post_data );
					}

					if ( ! $this->has_navigation && $key === $data['limit_query'] - 1 ) {
						break;
					}
				}
			}

			if ( empty( $search->post_count ) && empty( $sources_results_count ) ) {
				$allowed_tags = Jet_Search_Tools::get_allowed_html_tags();
				$message      = isset( $data['negative_search'] ) ? wp_kses_post( $data['negative_search'] ) : '';

				$response['message'] = wpautop( wp_kses( $message, $allowed_tags ) );

			}

			return $response;
		}

		/**
		 * Handles the AJAX request to get form suggestions.
		 *
		 * @since 3.5.2
		 */
		public function get_form_suggestions() {

			$action = ! empty( $_GET['action'] ) ? $_GET['action'] : ''; // phpcs:ignore

			if ( 'get_form_suggestions' != $action ) {
				return;
			}

			jet_search()->db->create_all_tables();

			$raw_params = ! empty( $_GET['data'] ) ? wp_unslash( $_GET['data'] ) : '';; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$params = array();
			$result = array();

			if ( is_array( $raw_params ) ) {
				$params['list_type'] = isset( $raw_params['list_type'] ) ? sanitize_text_field( $raw_params['list_type'] ) : 'popular';
				$params['limit']     = isset( $raw_params['limit'] ) ? absint( $raw_params['limit'] ) : 5;
				$params['value']     = isset( $raw_params['value'] ) ? sanitize_text_field( $raw_params['value'] ) : '';
			}

			if ( ! empty( $params ) ) {
				$result = $this->get_form_suggestions_list( $params );
			}

			return wp_send_json( $result );
		}

		/**
		 * Retrieves a list of form suggestions based on the provided parameters.
		 *
		 * @since 3.5.2
		 * @param array $params The parameters for retrieving suggestions.
		 * @return array The list of form suggestions.
		 */
		public function get_form_suggestions_list( $params ) {
			$list_type   = ! empty( $params['list_type'] ) ? $params['list_type'] : 'popular';
			$limit       = ! empty( $params['limit'] ) ? absint( $params['limit'] ) : 5;
			$value       = ! empty( $params['value'] ) ? $params['value'] : '';
			$suggestions = array();

			global $wpdb;

			$prefix      = 'jet_';
			$table_name  = $wpdb->prefix . $prefix . 'search_suggestions';
			$query       = "SELECT * FROM {$table_name}";

			if ( '' != $value ) {
				$value        = '%' . $wpdb->esc_like( $value ) . '%';
				$query       .= " WHERE ( name LIKE %s AND parent = 0 )";
				$query       .= " ORDER BY WEIGHT DESC";
				$query       .= " LIMIT %d OFFSET 0";
				$query        = $wpdb->prepare( $query, $value, $limit );
			} else {
				$query       .= " WHERE parent = 0";

				if ( 'latest' === $list_type ) {
					$query .= " ORDER BY ID DESC";
				} else if ( 'popular' === $list_type ) {
					$query .= " ORDER BY WEIGHT DESC";
				}

				$query .= " LIMIT %d OFFSET 0";
				$query  = $wpdb->prepare( $query, $limit );
			}

			$suggestions = $wpdb->get_results( $query, ARRAY_A );

			return $suggestions;
		}

		/**
		 * Handles the AJAX request to add suggestions via the search form.
		 *
		 * @since 3.5.2
		 */
		public function add_form_suggestion() {

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'form_suggestions' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				wp_send_json_error( array(
					'message' => 'Invalid Nonce!'
				) );

				return;
			}

			if ( isset( $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'] ) ) {
				$referer         = parse_url( $_SERVER['HTTP_REFERER'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$currentSiteHost = parse_url( $_SERVER['HTTP_HOST'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

				if ( isset( $currentSiteHost['host'] ) || isset( $currentSiteHost['path'] ) ) {
					$currentSite['host'] = isset( $currentSiteHost['host'] ) ? $currentSiteHost['host'] : $currentSiteHost['path'];
				} elseif ( isset( $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI'] ) ) {
					$currentSite = parse_url( '//' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				}

				if ( $referer['host'] !== ( $currentSite['host'] ) ) {
					return;
				}
			}

			$params = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( empty( $params ) || ! $params['name'] ) {
				return;
			}

			if ( false === get_option( 'jet_search_suggestions_widget_suggestion_save_permission') ) {
				$save_permission = add_option( 'jet_search_suggestions_widget_suggestion_save_permission' , "true" );
				$save_permission = "true";
			} else {
				$save_permission = get_option( 'jet_search_suggestions_widget_suggestion_save_permission' );
			}

			global $wpdb;

			$prefix              = 'jet_';
			$table_name          = $wpdb->prefix . $prefix . 'search_suggestions';
			$sessions_table_name = $wpdb->prefix . $prefix . 'search_suggestions_sessions';
			$name                = sanitize_text_field( $params['name'] );
			$suggestion_name     = esc_sql( $name );

			$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE name = %s ", $suggestion_name );

			$get_request = $wpdb->get_row( $query, ARRAY_A );

			if ( NULL != $get_request ) {
				$get_request['weight'] += 1;

				$where        = array( 'id' => $get_request['id'] );
				$format       = array( '%s' );
				$where_format = array( '%d' );

				$wpdb->update( $table_name, $get_request, $where, $format, $where_format );
			} else if ( "true" === $save_permission )  {
				$use_session = get_option( 'jet_search_suggestions_use_session' );

				if ( false != $use_session && 'true' === $use_session ) {
					if ( false === get_option( 'jet_search_suggestions_records_limit') ) {
						$records_limit = add_option( 'jet_search_suggestions_records_limit' , 5 );
						$records_limit = 5;
					} else {
						$records_limit = get_option( 'jet_search_suggestions_records_limit' );

						if ( '0' === $records_limit ) {
							update_option( 'jet_search_suggestions_records_limit' , 5 );
							update_option( 'jet_search_suggestions_use_session' , "false" );
						}
					}

					$token         = jet_search_token_manager()->generate_token();
					$count_records = jet_search_token_manager()->check_token_records( $token );

					if ( $count_records >= $records_limit && 0 != $records_limit ) {
						return true;
					}

					$session_record = array(
						"token" => $token
					);

					jet_search_token_manager()->add_token( $session_record );
				}

				$suggestion = array(
					"name"   => $name,
					"weight" => 1,
					"parent" => 0,
					"term"   => NULL
				);

				$wpdb->insert( $table_name, $suggestion, '%s' );

			}
		}

		/**
		 * Handles the AJAX request to add suggestion.
		 *
		 * @since 3.5.3
		 */
		public function add_suggestion() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return wp_send_json_error();
			}

			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'jet-search-settings' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				return wp_send_json_error( esc_html__( 'Invalid Nonce!', 'jet-search' )  );
			}

			$suggestion = isset( $_GET['content'] ) ? $_GET['content'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if ( empty( $suggestion ) ) {
				return wp_send_json_error( esc_html__( 'Error!', 'jet-search' ) );
			}

			$suggestion = stripcslashes( $suggestion );
			$suggestion = json_decode( $suggestion, true );

			if ( empty( $suggestion ) || ! $suggestion['name'] ) {
				return wp_send_json_error( esc_html__( 'The suggestion could not be added.', 'jet-search' ) );
			}

			unset( $suggestion['_locale'] );

			global $wpdb;

			$prefix          = 'jet_';
			$table_name      = $wpdb->prefix . $prefix . 'search_suggestions';
			$suggestion_name = esc_sql( $suggestion['name'] );

			$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE name = %s ", $suggestion_name );

			$get_request = $wpdb->get_row( $query, ARRAY_A );

			if ( NULL != $get_request ) {
				return wp_send_json_error( sprintf(  esc_html__( 'The suggestion with name "%s" already exists.', 'jet-search' ), $suggestion['name'] ) );
			} else {
				if ( is_array( $suggestion['parent'] ) ) {
					$suggestion['parent'] = $suggestion['parent'][0];
				}

				$wpdb->insert( $table_name, $suggestion, '%s' );

				if ( $wpdb->insert_id ) {
					$success_text = sprintf( esc_html__( 'Success! New suggestion: %s has been added', 'jet-search' ), $suggestion['name'] );
				} else {
					$success_text = esc_html__( 'Error!', 'jet-search' );
				}
			}

			return wp_send_json_success( $success_text );
		}

		/**
		 * Handles the AJAX request to get suggestion.
		 *
		 * @since 3.5.3
		 */
		public function get_suggestion() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return wp_send_json_error();
			}

			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'jet-search-settings' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				return wp_send_json_error( esc_html__( 'Invalid Nonce!', 'jet-search' )  );
			}

			jet_search()->db->create_all_tables();

			$params = $_GET;

			$offset         = ! empty( $params['offset'] ) ? absint( $params['offset'] ) : 0;
			$per_page       = ! empty( $params['per_page'] ) ? absint( $params['per_page'] ) : 30;
			$search_parent  = ! empty( $params['query'] ) ? trim( $params['query'] ) : '';
			$sort           = array();
			$filter         = array();
			$ids            = ! empty( $params['ids'] ) ? $params['ids'] : '';
			$action         = ! empty( $params['action'] ) ? $params['action'] : '';
			$result         = array();

			if ( ! empty( $params['sort'] ) ) {
				$sort = $params['sort'];
				$sort = stripcslashes( $sort );
				$sort = json_decode( $sort, true );

				$params['sort'] = $sort;
			}

			if ( ! empty( $params['filter'] ) ) {
				$filter = $params['filter'];
				$filter = stripcslashes( $filter );
				$filter = json_decode( $filter, true );

				$params['filter'] = $filter;
			}

			global $wpdb;

			$prefix     = 'jet_';
			$table_name = $wpdb->prefix . $prefix . 'search_suggestions';
			$query      = "SELECT s1.*, MAX(s2.id) AS child FROM {$table_name} AS s1 LEFT JOIN {$table_name} AS s2 ON s1.id = s2.parent";

			if ( '' != $filter && ( isset ( $filter['search'] ) || isset( $filter['searchType'] ) ) ) {
				$result = $this->get_filtered_items( $query, $params );

				return wp_send_json( $result );
			}

			if ( '' != $search_parent || '' != $ids ) {
				$result = $this->get_options_list( $query, $search_parent, $ids );

				return wp_send_json( $result );
			}

			if ( ! empty( $sort ) && empty( $filter ) ) {
				$allowed_orderby = array(
					'id'        => 's1.id',
					'name'      => 's1.name',
					'weight'    => 's1.weight',
					'parent'    => 's1.parent',
					'child'     => 'child',
					's1.id'     => 's1.id',
					's1.name'   => 's1.name',
					's1.weight' => 's1.weight',
					's1.parent' => 's1.parent',
				);
				$orderby         = ! empty( $sort['orderby'] ) && isset( $allowed_orderby[ $sort['orderby'] ] ) ? $allowed_orderby[ $sort['orderby'] ] : 's1.id';
				$order           = ! empty( $sort['order'] ) ? strtoupper( $sort['order'] ) : 'DESC';
				$order           = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';
				$query  .= " GROUP BY s1.id";
				$query  .= " ORDER BY {$orderby} {$order}";
			} else {
				$query .= " GROUP BY s1.id";
			}

			$query       .= " LIMIT {$per_page} OFFSET {$offset}";
			$suggestions  = $wpdb->get_results( $query, ARRAY_A );
			$count        = jet_search()->db->count( 'search_suggestions' );
			$on_page      = count( $suggestions );
			$parents_list = array();
			$parents_ids  = array();

			if ( $suggestions ) {
				foreach ( $suggestions as $item ) {
					$parent = $item['parent'];
					if ( ! empty( $parent ) ) {
						$parents_ids[] = $parent;
					}
				}

				$parents_ids = array_unique( $parents_ids );

				foreach ( $suggestions as $item ) {
					if ( in_array( $item['id'], $parents_ids, true ) ) {
						$parents_list[] = array(
							'value' => (string) $item['id'],
							'label' => $item['name'],
						);
					}
				}

				$result = array(
					"success"      => true,
					"items_list"   => $suggestions,
					"parents_list" => $parents_list,
					"total"        => (int)$count,
					"on_page"      => $on_page
				);
			} else {
				$result = array(
					"success"      => false,
					"items_list"   => array(),
					"parents_list" => array(),
					"total"        => 0,
					"on_page"      => 0
				);
			}

			return wp_send_json( $result );
		}

		/**
		 * Returns a list of options by IDs or a list of options found by the given name
		 *
		 * @return array
		 */
		public function get_options_list( $query, $search, $ids ) {
			if ( '' != $search ) {
				global $wpdb;

				$result      = array();
				$search_rel  = ' WHERE';
				$search      = '%' . $wpdb->esc_like( $search ) . '%';
				$query      .= "{$search_rel} s1.name LIKE %s AND s1.parent = 0";
				$query      .= " GROUP BY s1.id";
				$query       = $wpdb->prepare( $query, $search );

				$suggestions = $wpdb->get_results( $query, ARRAY_A );

				if ( $suggestions ) {
					foreach ( $suggestions as $suggestion ) {
						$result[] = array(
							'value' => (string) $suggestion['id'],
							'label' => $suggestion['name'],
						);
					}
				}

				return $result;
			} else if ( ! empty( $ids ) ) {
				$parents_list = $this->get_parents_options_list( $ids );

				return $parents_list;
			}
		}

		/**
		 * Returns filtered by name items
		 *
		 * @return array
		 */
		public function get_filtered_items( $query, $params ) {
			$offset      = ! empty( $params['offset'] ) ? absint( $params['offset'] ) : 0;
			$per_page    = ! empty( $params['per_page'] ) ? absint( $params['per_page'] ) : 30;
			$sort        = ! empty( $params['sort'] ) ? $params['sort'] : array();
			$filter      = ! empty( $params['filter'] ) ? $params['filter'] : array();
			$filter_name = ! empty( $filter['search'] ) ? $filter['search'] : '';
			$filter_type = ! empty( $filter['searchType'] ) ? $filter['searchType'] : '';
			$type_query  = '';

			global $wpdb;

			$search_rel  = ' WHERE';
			$query_args  = array();

			if ( '' != $filter_type ) {
				switch ($filter_type) {
					case 'parent':
						$type_query = "s2.id IS NOT NULL";
						break;
					case 'child':
						$type_query = "s1.parent != 0";
						break;
					case 'unassigned':
						$type_query = "( s1.parent = 0 AND s2.id IS NULL )";
						break;
				}
			}

			if ( '' != $filter_name && '' != $filter_type ) {

				$query_args[] = '%' . $wpdb->esc_like( $filter_name ) . '%';
				$query       .= "{$search_rel} ( s1.name LIKE %s AND {$type_query} )";

			} else if ( '' != $filter_name ) {

				$query_args[] = '%' . $wpdb->esc_like( $filter_name ) . '%';
				$query       .= "{$search_rel} ( s1.name LIKE %s )";

			} else if ( '' != $filter_type ) {

				$query .= "{$search_rel} {$type_query}";
			}

			if ( ! empty( $sort ) ) {
				$allowed_orderby = array(
					'id'        => 's1.id',
					'name'      => 's1.name',
					'weight'    => 's1.weight',
					'parent'    => 's1.parent',
					'child'     => 'child',
					's1.id'     => 's1.id',
					's1.name'   => 's1.name',
					's1.weight' => 's1.weight',
					's1.parent' => 's1.parent',
				);
				$orderby         = ! empty( $sort['orderby'] ) && isset( $allowed_orderby[ $sort['orderby'] ] ) ? $allowed_orderby[ $sort['orderby'] ] : 's1.id';
				$order           = ! empty( $sort['order'] ) ? strtoupper( $sort['order'] ) : 'DESC';
				$order           = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';
				$query  .= " GROUP BY s1.id";
				$query  .= " ORDER BY {$orderby} {$order}";
			} else {
				$query .= " GROUP BY s1.id";
			}

			$count_query = $query;

			$query       .= " LIMIT {$per_page} OFFSET {$offset}";

			if ( ! empty( $query_args ) ) {
				$query       = $wpdb->prepare( $query, $query_args );
				$count_query = $wpdb->prepare( $count_query, $query_args );
			}

			$suggestions  = $wpdb->get_results( $query, ARRAY_A );
			$count        = count( $wpdb->get_results( $count_query, ARRAY_A ) );
			$on_page      = count( $suggestions );
			$parents_list = array();
			$parents_ids  = array();

			if ( $suggestions ) {
				foreach ( $suggestions as $item ) {
					$parent = $item['parent'];
					if ( ! empty( $parent ) ) {
						$parents_ids[] = $parent[0];
					}
				}

				$parents_ids = array_unique( $parents_ids );

				foreach ( $suggestions as $item ) {
					if ( in_array( $item['id'], $parents_ids, true ) ) {
						$parents_list[] = array(
							'value' => (string) $item['id'],
							'label' => $item['name'],
						);
					}
				}

				$result = array(
					"success"      => true,
					"items_list"   => $suggestions,
					"parents_list" => $parents_list,
					"total"        => (int)$count,
					"on_page"      => $on_page
				);
			} else {
				$result = array(
					"success"      => false,
					"items_list"   => array(),
					"parents_list" => array(),
					"total"        => 0,
					"on_page"      => 0
				);
			}

			return $result;
		}

		/**
		 * Returns parents list by ids
		 *
		 * @return array
		 */
		public function get_parents_options_list( $ids ) {
			$parents_list = array();

			global $wpdb;

			$prefix        = 'jet_';
			$table_name    = $wpdb->prefix . $prefix . 'search_suggestions';

			if ( ! is_array( $ids ) ) {
				$ids = explode( ',', $ids );
			}

			$ids = array_filter( array_map( 'absint', $ids ) );

			if ( empty( $ids ) ) {
				return $parents_list;
			}

			$parents_query = "SELECT * FROM {$table_name} WHERE id IN (" . implode( ', ', $ids ) . ")";
			$suggestions   = $wpdb->get_results( $parents_query, ARRAY_A );

			foreach ( $suggestions as $item ) {
				$parents_list[] = array(
					'value' => (string) $item['id'],
					'label' => $item['name'],
				);
			}

			return $parents_list;
		}

		/**
		 * Returns unserialized item parents
		 *
		 * @return array
		 */
		public function parents_unserialize( $item ) {
			if ( NULL === $item['parent'] ) {
				$item['parent'] = NULL;
			}

			$item['parent'] = maybe_unserialize( $item['parent'] );
			return $item;
		}

		/**
		 * Handles the AJAX request to update suggestion.
		 *
		 * @since 3.5.3
		 */
		public function update_suggestion() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return wp_send_json_error();
			}

			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'jet-search-settings' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				return wp_send_json_error( esc_html__( 'Invalid Nonce!', 'jet-search' )  );
			}

			$suggestion = isset( $_GET['content'] ) ? $_GET['content'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if ( empty( $suggestion ) ) {
				return wp_send_json_error( esc_html__( 'Error!', 'jet-search' ) );
			}

			$suggestion = stripcslashes( $suggestion );
			$suggestion = json_decode( $suggestion, true );

			global $wpdb;

			$prefix          = 'jet_';
			$table_name      = $wpdb->prefix . $prefix . 'search_suggestions';
			$suggestion_id   = intval( $suggestion['id'] );
			$name            = sanitize_text_field( $suggestion['name'] );
			$suggestion_name = esc_sql( $name );

			$query = $wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id != %s AND name = %s",
				$suggestion_id,
				$suggestion_name
			);

			$get_request = $wpdb->get_row( $query, ARRAY_A );

			if ( NULL === $get_request ) {

				$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %s ", $suggestion_id );

				$get_request = $wpdb->get_row( $query, ARRAY_A );

				if ( NULL != $get_request ) {

					unset( $suggestion['child'] );

					$where                = array( 'id' => $suggestion_id );
					$format               = array( '%s' );
					$where_format         = array( '%d' );

					$wpdb->update( $table_name, $suggestion, $where, $format, $where_format );

					$success_text = sprintf( esc_html__( 'Success! Suggestion: "%s" has been updated', 'jet-search' ), $suggestion['name'] );

					return wp_send_json( array(
						'success' => true,
						'data'    => $success_text,
					) );
				} else {
					$success_text = sprintf( esc_html__( 'Fail! The suggestion with "%s" id has not found', 'jet-search' ), $suggestion['id'] );

					return wp_send_json( array(
						'success' => false,
						'data'    => $success_text,
					) );
				}
			} else {
				$success_text = sprintf( esc_html__( 'Fail! The suggestion with "%s" already exists.', 'jet-search' ), $suggestion['name'] );

				return wp_send_json( array(
					'success' => false,
					'data'    => $success_text,
				) );
			}
		}

		/**
		 * Handles the AJAX request to delete suggestion.
		 *
		 * @since 3.5.3
		 */
		public function delete_suggestion() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return wp_send_json_error();
			}

			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'jet-search-settings' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				return wp_send_json_error( esc_html__( 'Invalid Nonce!', 'jet-search' )  );
			}

			$suggestion = isset( $_GET['content'] ) ? $_GET['content'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			if ( empty( $suggestion ) ) {
				return wp_send_json_error( esc_html__( 'Error!', 'jet-search' ) );
			}

			$suggestion = stripcslashes( $suggestion );
			$suggestion = json_decode( $suggestion, true );

			if ( isset( $suggestion['ids'] ) && is_array( $suggestion['ids'] ) && ! empty( $suggestion['ids'] ) ) {

				global $wpdb;

				$table_name = 'search_suggestions';
				$ids_int    = array_map( 'intval', $suggestion['ids'] );

				foreach ( $ids_int as $sid ) {
					jet_search()->db->delete( $table_name, array( 'id' => esc_sql( $sid ) ) );
				}

				$prefix     = 'jet_';
				$table_name = $wpdb->prefix . $prefix . 'search_suggestions';
				$query      = "SELECT * FROM {$table_name}";
				$suggestions = $wpdb->get_results( $query, ARRAY_A );

				if ( $suggestions ) {
					foreach ( $ids_int as $deleted_id ) {
						foreach ( $suggestions as $suggestion_item ) {
							if ( $suggestion_item['id'] !== $deleted_id ) {
								$this->remove_deleted_parent( $suggestion_item, $deleted_id );
							}
						}
					}
				}

				$success_text = sprintf(
					esc_html__( 'Success! %d suggestion(s) have been deleted', 'jet-search' ),
					count( $ids_int )
				);

				return wp_send_json( array(
					'success' => true,
					'data'    => $success_text,
				) );
			}

			if ( empty( $suggestion ) || ! $suggestion['id'] ) {
				return wp_send_json( array(
					'success' => false,
					'data'    => esc_html__( 'Error! The suggestion could not be deleted.', 'jet-search' ),
				) );
			}

			unset( $suggestion['_locale'] );

			global $wpdb;

			$table_name    = 'search_suggestions';
			$suggestion_id = esc_sql( (int)$suggestion['id'] );
			$where         = array( 'id' => $suggestion_id );
			$name          = sanitize_text_field( $suggestion['name'] );

			jet_search()->db->delete( $table_name, $where );

			$prefix     = 'jet_';
			$table_name = $wpdb->prefix . $prefix . 'search_suggestions';
			$query      = "SELECT * FROM {$table_name}";

			$suggestions = $wpdb->get_results( $query, ARRAY_A );

			if ( $suggestions ) {
				foreach ( $suggestions as $suggestion_item ) {
					if ( $suggestion_item['id'] !== $suggestion_id ) {
						$this->remove_deleted_parent( $suggestion_item, $suggestion_id );
					}
				}
			}

			$success_text = sprintf( esc_html__( 'Success! Suggestion: %s has been deleted', 'jet-search' ), $name );

			return wp_send_json( array(
				'success' => true,
				'data'    => $success_text,
			) );
		}

		/**
		 * Remove deleted suggestion from suggestions parents
		 *
		 * @return void
		 */
		public function remove_deleted_parent( $item, $deleted_id ) {
			if ( "0" != $item['parent'] ) {

				if ( $item['parent'] === $deleted_id ) {
					$item['parent'] = 0;

					global $wpdb;

					$prefix       = 'jet_';
					$table_name   = $wpdb->prefix . $prefix . 'search_suggestions';
					$where        = array( 'id' => $item['id'] );
					$format       = array( '%s' );
					$where_format = array( '%d' );

					$wpdb->update( $table_name, $item, $where, $format, $where_format );
				} else {
					$item['parent'] = maybe_serialize( $item['parent'] );
				}
			}
		}

		/**
		 * Build query args for current JetSearch results (used by macros).
		 *
		 * @param array  $settings      form settings.
		 * @param string $search_string Search string from request.
		 *
		 * @return array
		 */
		protected function build_current_results_query_args( $settings = array(), $search_string = '' ) {

			$args = array(
				's'                   => $search_string,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => false,
				'nopaging'            => false,
				'posts_per_page'      => isset( $settings['limit_query_in_result_area'] ) ? (int) $settings['limit_query_in_result_area'] : 25,
			);

			$search_source = isset( $settings['search_source'] ) ? $settings['search_source'] : 'any';

			$args['post_type'] = $search_source;

			if ( ! empty( $settings['custom_fields_source'] ) && class_exists( 'Jet_Search_Tools' ) ) {
				$args['post_type'] = Jet_Search_Tools::custom_fields_post_type_update(
					$settings['custom_fields_source'],
					$args['post_type']
				);
			}

			$args['sentence'] = isset( $settings['sentence'] ) ? filter_var( $settings['sentence'], FILTER_VALIDATE_BOOLEAN ) : false;

			if ( ! empty( $settings['category__in'] ) ) {
				$tax = ! empty( $settings['search_taxonomy'] ) ? $settings['search_taxonomy'] : 'category';

				$args['tax_query'] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => $tax,
						'field'    => 'id',
						'operator' => 'IN',
						'terms'    => $settings['category__in'],
					),
				);
			} else {
				$tax_query = array( 'relation' => 'AND' );

				if ( ! empty( $settings['include_terms_ids'] ) && method_exists( $this, 'prepare_terms_data' ) ) {
					$include_tax_query = array( 'relation' => 'OR' );
					$terms_data        = $this->prepare_terms_data( $settings['include_terms_ids'] );

					foreach ( $terms_data as $taxonomy => $terms_ids ) {
						$include_tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'operator' => 'IN',
							'terms'    => $terms_ids,
						);
					}

					$tax_query[] = $include_tax_query;
				}

				if ( ! empty( $settings['exclude_terms_ids'] ) && method_exists( $this, 'prepare_terms_data' ) ) {
					$exclude_tax_query = array( 'relation' => 'AND' );
					$terms_data        = $this->prepare_terms_data( $settings['exclude_terms_ids'] );

					foreach ( $terms_data as $taxonomy => $terms_ids ) {
						$exclude_tax_query[] = array(
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'operator' => 'NOT IN',
							'terms'    => $terms_ids,
						);
					}

					$tax_query[] = $exclude_tax_query;
				}

				if ( count( $tax_query ) > 1 ) {
					$args['tax_query'] = $tax_query;
				}
			}

			if ( ! empty( $settings['exclude_posts_ids'] ) ) {
				$args['post__not_in'] = $settings['exclude_posts_ids'];
			}

			if ( ! empty( $settings['current_query'] ) ) {
				$args = Jet_Search_Tools::merge_public_current_query_args(
					$args,
					(array) $settings['current_query']
				);
			}

			if ( ! empty( $settings['results_order_by'] ) ) {
				$args['orderby'] = $settings['results_order_by'];

				if ( ! empty( $settings['results_order'] ) ) {
					$args['order'] = $settings['results_order'];
				}
			}

			return $args;
		}

		/**
		 * Build cache key for current results IDs.
		 *
		 * @param array $query_args
		 *
		 * @return string
		 */
		protected function get_current_results_cache_key( $query_args = array() ) {

			$key_data = array(
				's'         => isset( $query_args['s'] ) ? $query_args['s'] : '',
				'post_type' => isset( $query_args['post_type'] ) ? $query_args['post_type'] : '',
				'tax_query' => isset( $query_args['tax_query'] ) ? $query_args['tax_query'] : '',
				'lang'      => defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '',
				'paged'     => isset( $query_args['paged'] ) ? (int) $query_args['paged'] : 0,
			);

			$raw = wp_json_encode( $key_data );

			return 'jet_search_current_results_' . md5( $raw );
		}


		/**
		 * Get IDs of posts returned by current results.
		 *
		 * @return array
		 */
		public function get_current_results_ids() {

			if ( ! empty( $this->current_results_ids ) && is_array( $this->current_results_ids ) ) {
				$ids = wp_parse_id_list( $this->current_results_ids );

				return array_values( array_unique( $ids ) );
			}

			$search_key    = $this->get_custom_search_query_param();
			$search_string = isset( $_REQUEST[ $search_key ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				? sanitize_text_field( wp_unslash( $_REQUEST[ $search_key ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				: '';

			if ( '' === $search_string ) {
				return array();
			}

			$settings = $this->get_form_settings();

			if ( empty( $settings ) || ! is_array( $settings ) ) {
				return array();
			}

			$query_args = $this->build_current_results_query_args( $settings, $search_string );

			if ( empty( $query_args ) || ! is_array( $query_args ) ) {
				return array();
			}

			$cache_key   = $this->get_current_results_cache_key( $query_args );
			$cache_group = 'jet-search-current-results';

			$cached_ids = wp_cache_get( $cache_key, $cache_group );

			if ( false !== $cached_ids && is_array( $cached_ids ) ) {
				$ids = wp_parse_id_list( $cached_ids );
				$ids = array_values( array_unique( $ids ) );

				$this->current_results_ids = $ids;

				return $ids;
			}

			$query_args['fields'] = 'ids';

			$this->is_building_current_results = true;
			$search = new WP_Query( $query_args );
			$this->is_building_current_results = false;

			if ( is_wp_error( $search ) || empty( $search->posts ) ) {
				return array();
			}

			$ids = wp_parse_id_list( $search->posts );
			$ids = array_values( array_unique( $ids ) );

			$this->current_results_ids = $ids;

			$ttl = apply_filters( 'jet-search/current-results-cache-ttl', HOUR_IN_SECONDS, $query_args, $settings, $search_string );

			wp_cache_set( $cache_key, $ids, $cache_group, $ttl );

			return $ids;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return Jet_Search_Ajax_Handlers
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
 * Returns instance of Jet_Search_Ajax_Handlers
 *
 * @return Jet_Search_Ajax_Handlers
 */
function jet_search_ajax_handlers() {
	return Jet_Search_Ajax_Handlers::get_instance();
}
