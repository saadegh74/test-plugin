<?php
/**
 * Get searching posts endpoint
 */
use Jet_Search\Search_Sources\Manager;

class Jet_Search_Rest_Search_Route extends Jet_Search_Rest_Base_Route {

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
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'search-posts';
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELETE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'GET';
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$params = $request->get_params();

		if ( empty( $params['data'] ) ) {
			return false;
		}

		$data                                      = Jet_Search_Tools::prepare_public_meta_settings( $params['data'] );
		$lang									   = isset( $params['lang'] ) ? $params['lang'] : '';
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

		do_action_ref_array(
			'jet-search/ajax-search/before-search-sources',
			array( &$response, &$search->posts, $data )
		);

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
					'jet-listing-grid__item',
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

				$content = sprintf(
					'<div class="%1$s" data-post-id="%2$s">%3$s</div>',
					esc_attr( implode( ' ', $item_classes ) ),
					esc_attr( $post->ID ),
					$content
				);

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

		return wp_send_json_success( $response );

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
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return true;
	}

	/**
	 * Returns arguments config
	 *
	 * @return array
	 */
	public function get_args() {
		return array(
			'data' => array(
				'default'  => array(),
				'required' => true,
			),
		);
	}
}
