<?php
/**
 * Jet_Search_Custom_URL_Handler class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Custom_URL_Handler' ) ) {

	/**
	 * Define Jet_Search_Custom_URL_Handler class
	 */
	class Jet_Search_Custom_URL_Handler {

		private $settings = null;

		/**
		 * Search query.
		 *
		 * @var array
		 */
		public $search_query = array();

		/**
		 * Query builder post type.
		 *
		 * @var array
		 */
		public $query_builder_post_type = array();

		private $target_widget_id = null;
		private $active_query_element_id = null;
		private $processed_element_ids = array();

		public function __construct() {
			$this->target_widget_id = $this->get_settings( 'search_results_target_widget_id' );

			if ( ! empty( $this->target_widget_id ) ) {
				$this->target_widget_id = sanitize_key( $this->target_widget_id );
			}

			if ( $this->get_search_string() ) {
				add_action( 'pre_get_posts', array( $this, 'maybe_setup_handler_by_context' ), 1 );
			}

		}

		/**
		 * Decide which handler to use based on page type and search parameters.
		 */
		public function maybe_setup_handler_by_context( $query ) {
			if ( $query->is_main_query() ) {
				$this->handle_main_archive_query( $query );

				if ( $this->target_widget_id ) {
					add_filter( 'jet-engine/listing/grid/source', array( $this, 'maybe_set_current_widget_id' ), -999, 2 );
				}

			} else if ( ! is_archive() && $this->target_widget_id ) {
				add_filter( 'jet-engine/listing/grid/source', array( $this, 'maybe_set_current_widget_id' ), -999, 2 );
			} else if ( ! is_archive() && $this->allow_handle_custom_results_page() ) {
				add_action( 'pre_get_posts', array( $this, 'handle_custom_results_page' ) );
				add_action( 'jet-engine/query-builder/query/after-query-setup', array( $this, 'final_query_setup' ) );
			}

		}

		/**
		 * Handles search query injection for main archive queries.
		 *
		 * Applies the 'jet_search' parameter and other JetSearch settings
		 * to the main WP_Query on archive pages.
		 *
		 * @param WP_Query $query Main archive query.
		 */
		public function handle_main_archive_query( $query ) {

			if ( is_admin() || ! is_archive() || ! $query->is_main_query() ) {
				return;
			}
			// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			if ( isset( $_GET['jet_search'] ) && empty( $query->get('s') ) ) {
				$query->set( 's', sanitize_text_field( $_GET['jet_search'] ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$args = $this->get_settings();
			$this->set_query_settings( $args );

			$query->set( 's', $this->get_search_string() );

			if ( ! empty( $this->query_builder_post_type ) ) {
				$this->search_query['post_type'] = $this->query_builder_post_type;
			}

			if ( ! empty( $this->search_query ) ) {
				foreach ( $this->search_query as $key => $value ) {
					$query->set( $key, $value );
					$query->query[$key] = $value;
				}
			}

			if ( isset( $args['results_order_by'] ) && function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
				$order_by = strtolower( $args['results_order_by'] );
				$order    = strtoupper( ! empty( $args['results_order'] ) ? $args['results_order'] : 'ASC' );

				$query->set( 'orderby', "{$order_by}-{$order}" );
			}
		}

		public function maybe_set_current_widget_id( $source, $settings ) {

			if ( empty( $this->target_widget_id ) || empty( $settings['_element_id'] ) ) {
				return $source;
			}

			$element_id = sanitize_key( $settings['_element_id'] );

			if ( $this->target_widget_id === $element_id ) {

				$this->active_query_element_id = $element_id;

				if ( ! is_archive() && $this->allow_handle_custom_results_page() ) {
					add_action( 'pre_get_posts', array( $this, 'handle_custom_results_page' ), 999 );
				}

				add_action( 'jet-engine/query-builder/query/after-query-setup', array( $this, 'final_query_setup' ), 1 );
			}

			return $source;
		}

		public function final_query_setup( $query ) {

			if ( empty( $this->search_query ) ) {
				$args = $this->get_settings();

				$this->set_query_settings( $args );
			}

			$element_id = $this->active_query_element_id;

			if ( empty( $element_id ) || $element_id !== $this->target_widget_id ) {
				return;
			}

			if ( in_array( $element_id, $this->processed_element_ids, true ) ) {
				return;
			}

			$this->processed_element_ids[] = $element_id;

			$allowed_query_types = [ 'wc-product-query', 'posts' ];

			/**
			 * Do not process not posts or WC queries
			 */
			if ( isset( $query->final_query['_query_type'] )
				&& ! in_array( $query->final_query['_query_type'], $allowed_query_types )
			) {
				return;
			}

			$query_type_exists    = isset( $query->final_query['_query_type'] );
			$query_type_not_empty = ! empty( $query->final_query['_query_type'] );
			$post_type_exists     = isset( $query->final_query['post_type'] );
			$is_wc_product_query  = $query->final_query['_query_type'] === 'wc-product-query';

			if (
				( $query_type_exists && $query_type_not_empty ) &&
				( $post_type_exists || $is_wc_product_query ) ||
				( $query_type_not_empty && ! $post_type_exists )
			) {
				$search_post_type = ! is_array( $this->search_query['post_type'] ) ? [ $this->search_query['post_type'] ] : $this->search_query['post_type'];

				if ( $is_wc_product_query ) {
					$current_post_type = array( 'product' );
				} else if ( $post_type_exists ) {
					$current_post_type = $query->final_query['post_type'];
				} else if ( ! $post_type_exists ) {
					$current_post_type = 'any';
					$query->final_query['post_type'] = array( 'any' );
				}

				if ( ! empty(  $this->get_settings( 'custom_fields_source' ) ) && isset( $current_post_type ) ) {
					$current_post_type = Jet_Search_Tools::custom_fields_post_type_update( $this->get_settings( 'custom_fields_source' ), $current_post_type );

					if ( ! $post_type_exists ) {
						$query->final_query['post_type'] = $current_post_type;
					}
				}

				if ( 'any' === $search_post_type[0] || ( isset( $current_post_type ) && ! empty( array_intersect( $current_post_type, $search_post_type ) ) || ! $post_type_exists ) ) {
					$final_search_query = $this->search_query;

					$post_types = get_post_types( array( 'exclude_from_search' => false ), 'names' );
					$post_types = array_values( $post_types );

					$final_search_query['post_type'] = $post_types;

					if ( $post_type_exists ) {
						if ( ! filter_var( $this->allow_merge_queries_post_types(), FILTER_VALIDATE_BOOLEAN ) ) {

							if ( in_array( 'product', $final_search_query['post_type'] ) && in_array( 'product_variation', $final_search_query['post_type'] ) && in_array( 'product', $query->final_query['post_type'] )
							) {
								$final_search_query['post_type'] = array( 'product', 'product_variation' );
							} else {
								unset( $final_search_query['post_type'] );
							}
						}
					} else {
						unset( $final_search_query['post_type'] );
					}

					if ( isset( $final_search_query['post__not_in'] ) && ! empty( $query->final_query['post__not_in'] ) ) {
						$query->final_query['post__not_in'] = array_unique(
							array_merge(
								(array) $query->final_query['post__not_in'],
								(array) $final_search_query['post__not_in']
							)
						);

						unset( $final_search_query['post__not_in'] );
					}

					$query->final_query = array_merge( $query->final_query, $final_search_query );

					$is_negative_search = false;

					if ( isset( $query->final_query['post__not_in'] ) && is_array( $query->final_query['post__not_in'] ) ) {
						$current_results_ids = jet_search_ajax_handlers()->get_current_results_ids();

						if ( ! empty( $current_results_ids ) && is_array( $current_results_ids ) ) {
							$post_not_in         = array_map( 'intval', $query->final_query['post__not_in'] );
							$current_results_ids = array_map( 'intval', $current_results_ids );

							sort( $post_not_in );
							sort( $current_results_ids );

							if ( $post_not_in === $current_results_ids ) {
								$is_negative_search = true;
							}
						}
					}

					if ( ! $is_negative_search ) {
						$query->final_query['s'] = $this->get_search_string();
					}

					if ( function_exists( 'jet_smart_filters' ) ) {
						$request_query = jet_smart_filters()->query->get_query_from_request();

						if ( isset( $request_query ) && ! empty( $request_query ) ) {
							$query->final_query = array_merge( $query->final_query, $request_query );
						}
					}

					if ( isset( $query->final_query['orderby'] ) ) {
						if ( 'relevance' === $query->final_query['orderby'] ) {
							$order = ! empty( $query->final_query['order'] ) ? $query->final_query['order'] : 'desc';
							$order = is_array( $order ) ? reset( $order ) : $order;
							$order = strtolower( (string) $order );

							if ( ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
								$order = 'desc';
							}

							$query->final_query['orderby'] = array(
								array(
									'orderby' => 'relevance',
									'order'   => $order,
								),
							);

							unset( $query->final_query['order'] );
						} else {
							$query->final_query['orderby'] = (array) $query->final_query['orderby'];
						}
					}

					if ( isset( $query->final_query['order'] ) ) {
						$query->final_query['order'] = (array) $query->final_query['order'];
					}

					if ( isset( $query->final_query['post__in'] ) ) {
						if ( empty( $query->final_query['post__in'] ) ) {
							unset( $query->final_query['post__in'] );
						}
					}
				}
			}

			if ( ! empty( $this->target_widget_id ) ) {
				remove_action( 'jet-engine/query-builder/query/after-query-setup', array( $this, 'final_query_setup' ), 1  );
			}
		}

		/**
		 * Check if we need to automatically handle query on custom results page.
		 * Use 'jet-search/custom-url-handler/allowed' hook to disable auto-handle
		 * and manually add required search parameters on query you need
		 *
		 * @return bool
		 */
		public function allow_handle_custom_results_page() {
			return apply_filters( 'jet-search/custom-url-handler/allowed', true );
		}

		/**
		 * Check if we need to allow merging of post types from search query and query builder.
		 * Use 'jet-search/custom-url-handler/allow-merge-queries-post-types' hook to disable merge post types
		 *
		 * @return bool
		 */
		public function allow_merge_queries_post_types() {
			return apply_filters( 'jet-search/custom-url-handler/allow-merge-queries-post-types', true );
		}

		/**
		 * Get search query from request
		 *
		 * @return [type] [description]
		 */
		public function get_search_string() {

			$search_key    = jet_search_ajax_handlers()->get_custom_search_query_param();
			$search_string = isset( $_REQUEST[ $search_key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $search_key ] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			return $search_string;

		}

		/**
		 * Get current search settings (from URL or defaults)
		 *
		 * @param  string $setting [description]
		 * @return [type]          [description]
		 */
		public function get_settings( $setting = '' ) {

			if ( null === $this->settings ) {
				$this->settings = jet_search_ajax_handlers()->get_form_settings();
			}

			if ( ! $setting ) {
				return $this->settings;
			} else {
				return ( isset( $this->settings[ $setting ] ) ) ? $this->settings[ $setting ] : false;
			}

		}

		/**
		 * Check if given query is query to search for
		 *
		 * @param  [type]  $query [description]
		 * @return boolean        [description]
		 */
		public function is_search_query( $query ) {

			$result = false;

			// if is any archive page - apply results only to main query
			if ( $query->is_archive() || $query->is_posts_page ) {
				$result = $query->is_main_query();
			} else {

				// for any other page - apply search paramters to any query with the same post type
				// if post type not set - doesn't apply search automatically, because is a high risk to break the page
				$search_in_post_type = $this->get_settings( 'search_source' );
				$query_post_type     = $query->get( 'post_type' );

				if ( ! empty( $search_in_post_type ) && ! empty( $query_post_type ) ) {
					$query_post_type     = ! is_array( $query_post_type ) ? [ $query_post_type ] : $query_post_type;
					$search_in_post_type = ! is_array( $search_in_post_type ) ? [ $search_in_post_type ] : $search_in_post_type;

					if ( 'any' === $search_in_post_type || ! empty( array_intersect( $search_in_post_type, $query_post_type ) ) ) {
						$result = true;

						if ( ! filter_var( $this->allow_merge_queries_post_types(), FILTER_VALIDATE_BOOLEAN ) ) {
							$this->query_builder_post_type = $query_post_type;
						}
					}
				}

			}

			return apply_filters( 'jet-search/custom-url-handler/is-search-query', $result, $query );
		}

		/**
		 * Check if is query to apply search for and set required parameters if is.
		 *
		 * @param  [type] $query [description]
		 * @return [type]        [description]
		 */
		public function handle_custom_results_page( $query ) {

			if ( ! empty( jet_search_ajax_handlers()->is_building_current_results ) ) {
				return;
			}

			if ( ! $this->is_search_query( $query ) ) {
				return;
			}

			$args = $this->get_settings();

			$this->set_query_settings( $args );

			$is_negative_search = $this->is_exclude_current_results_query( $query );

			if ( ! $is_negative_search ) {
				$query->set( 's', $this->get_search_string() );
			}

			if ( ! empty( $this->query_builder_post_type ) ) {
				$this->search_query['post_type'] = $this->query_builder_post_type;
			}

			if ( ! empty( $this->search_query ) ) {
				foreach ( $this->search_query as $key => $value ) {

					if ( 'post__not_in' === $key ) {
						$existing = $query->get( 'post__not_in' );

						if ( ! empty( $existing ) ) {
							$value = array_unique( array_merge( (array) $existing, (array) $value ) );
						}
					}

					$query->set( $key, $value );
					$query->query[$key] = $value;
				}
			}

			if ( ! empty( $this->target_widget_id ) ) {
				remove_action( 'pre_get_posts',  array(  $this, 'handle_custom_results_page' ) );
			}
		}

		protected function set_query_settings( $args = array() ) {
			if ( $args ) {
				$this->search_query['jet_ajax_search'] = true;
				$this->search_query['cache_results']   = true;
				$this->search_query['post_type']       = $args['search_source'];
				$this->search_query['tax_query']       = array( 'relation' => 'AND' );
				$this->search_query['sentence']        = isset( $args['sentence'] ) ? filter_var( $args['sentence'], FILTER_VALIDATE_BOOLEAN ) : false;
				$this->search_query['post_status']     = 'publish';

				if ( ! empty( $args['results_order_by'] ) ) {
					$order = ! empty( $args['results_order'] ) ? $args['results_order'] : 'asc';

					if ( 'relevance' === $args['results_order_by'] ) {
						$this->search_query['orderby'] = 'relevance';
						$this->search_query['order']   = 'desc';
					} else {
						$this->search_query['orderby'] = array(
							$args['results_order_by'] => $order,
						);
					}
				}

				if ( function_exists( 'jet_smart_filters' ) ) {
					$sort = isset( $_REQUEST['query']['_sort_standard'] ) || isset( $_REQUEST['sort'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification

					if ( $sort ) {
						unset( $this->search_query['orderby']);
						unset( $this->search_query['order']);
					}
				}

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

				/**
				 * Allow filtering of final search query.
				 */
				$this->search_query = Jet_Search_Tools::prepare_public_search_query_args(
					apply_filters( 'jet-search/ajax-search/query-args', $this->search_query, $args )
				);

				do_action( 'jet-search/ajax-search/search-query', $this, $args );
			}
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

			foreach ( wp_parse_id_list( (array) $terms_ids ) as $term_id ) {
				$term = get_term( $term_id );

				if ( ! $term || is_wp_error( $term ) || empty( $term->taxonomy ) ) {
					continue;
				}

				$taxonomy = $term->taxonomy;
				$result[ $taxonomy ][] = (int) $term->term_id;
			}

			return $result;
		}

		/**
		 * Check if current query excludes exactly current results
		 * jet_search_current_results macros.
		 *
		 * @param WP_Query $query
		 *
		 * @return bool
		 */
		protected function is_exclude_current_results_query( $query ) {

			$post_not_in = $query->get( 'post__not_in' );

			if ( empty( $post_not_in ) || ! is_array( $post_not_in ) ) {
				return false;
			}

			$current_results_ids = jet_search_ajax_handlers()->get_current_results_ids();

			if ( empty( $current_results_ids ) || ! is_array( $current_results_ids ) ) {
				return false;
			}

			$post_not_in         = array_map( 'intval', $post_not_in );
			$current_results_ids = array_map( 'intval', $current_results_ids );

			sort( $post_not_in );
			sort( $current_results_ids );

			return $post_not_in === $current_results_ids;
		}

	}

}
