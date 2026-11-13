<?php
/**
 * Jet Smart Filters Indexer Data class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Indexer_Data' ) ) {
	/**
	 * Define Jet_Smart_Filters_Indexer_Data class
	 */
	class Jet_Smart_Filters_Indexer_Data {

		public $indexing_data = array();
		public $registered_filters = array();

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 */
		public function __construct() {

			add_filter( 'jet-smart-filters/filters/localized-data', array( $this, 'prepare_localized_data' ) );
			add_filter( 'jet-smart-filters/render/ajax/data', array( $this, 'prepare_ajax_data' ) );
		}

		/**
		 * Prepare localized data
		 */
		public function prepare_localized_data( $args ) {

			do_action( 'jet-smart-filters/indexer/before-prepare-data' );

			$indexed_data    = array();
			$indexer_context = array();

			foreach ( jet_smart_filters()->query->get_default_queries() as $provider => $queries ) {
				foreach ( $queries as $query_id => $query_args ) {
					$provider_key = $provider . '/' . $query_id;
					$filter_ids   = $this->get_registered_filters( $provider_key );

					if ( empty( $this->indexing_data[ $provider_key ] ) || empty( $filter_ids ) ) {
						continue;
					}

					if ( ! isset( $indexer_context[ $provider ] ) ) {
						$indexer_context[ $provider ] = array();
					}

					$indexer_context[ $provider ][ $query_id ] = array(
						'filters' => $filter_ids,
					);

					if ( jet_smart_filters()->request_signature->is_enabled() ) {
						$indexer_context[ $provider ][ $query_id ]['signature'] = jet_smart_filters()->request_signature->create_indexer_context(
							$query_args,
							$provider,
							$query_id,
							$filter_ids
						);
					}

					$query_args = jet_smart_filters()->utils->merge_query_args(
						$query_args,
						jet_smart_filters()->query->get_query_args()
					);

					$provider_indexed_data = $this->get_indexed_data( $provider_key, $query_args );

					if ( $provider_indexed_data ) {
						$indexed_data[$provider_key] = $provider_indexed_data;
					}
				}
			}

			if ( ! empty( $indexed_data ) ) {
				$args['jetFiltersIndexedData'] = $indexed_data;
			}

			if ( ! empty( $indexer_context ) ) {
				$args['indexer_context'] = $indexer_context;
			}

			do_action( 'jet-smart-filters/indexer/after-prepare-data' );

			return $args;
		}

		/**
		 * Return the indexer filter IDs registered for a provider query.
		 *
		 * @param string $provider_key Provider and query ID pair.
		 *
		 * @return array
		 */
		public function get_registered_filters( $provider_key ) {

			if ( ! is_string( $provider_key ) || empty( $this->registered_filters[ $provider_key ] ) ) {
				return array();
			}

			return array_values( array_unique( array_filter( array_map( 'absint', $this->registered_filters[ $provider_key ] ) ) ) );
		}

		/**
		 * Prepare data for ajax actions
		 */
		public function prepare_ajax_data( $args ) {

			do_action( 'jet-smart-filters/indexer/before-prepare-data' );

			$request = jet_smart_filters()->data->get_request();

			// Provider
			$provider_key = isset( $request['provider'] )
				? sanitize_text_field( wp_unslash( $request['provider'] ) )
				: false;

			// Indexing filters
			$indexing_filters = false;

			if ( isset( $request['indexing_filters'] ) ) {
				$indexing_filters = json_decode(
					sanitize_textarea_field(
						wp_unslash( $request['indexing_filters'] )
					),
					true
				);
			}

			if ( ! ( $provider_key && is_array( $indexing_filters ) && $indexing_filters ) ) {
				return $args;
			}

			$indexed_data = array();

			foreach ( $indexing_filters as $filter_id ) {
				$this->add_indexing_data_from_filter( $provider_key, $filter_id );
			}

			if ( ! empty( $this->indexing_data[$provider_key] ) ) {
				$query_args = jet_smart_filters()->utils->merge_query_args(
					jet_smart_filters()->query->get_default_queries(),
					jet_smart_filters()->query->get_query_args()
				);
				//$query_args   = jet_smart_filters()->query->get_query_args();
				$indexed_data = $this->get_indexed_data( $provider_key, $query_args );
			}

			$args['jetFiltersIndexedData'] = array(
				$provider_key => $indexed_data
			);

			do_action( 'jet-smart-filters/indexer/after-prepare-data' );

			return $args;
		}

		/**
		 * Merge a provider query with safe, narrowing indexer arguments.
		 *
		 * The public indexed-data endpoint is allowed to update counters after
		 * client-side rendering. Its request must never replace the provider's
		 * provider scope or query arbitrary metadata outside of rendered filters.
		 *
		 * @param array  $default_args Default provider query arguments.
		 * @param array  $request_args Client query arguments.
		 * @param string $provider_key Provider and query ID pair.
		 *
		 * @return array|false
		 */
		public function prepare_indexed_query_args( $default_args, $request_args, $provider_key ) {

			if ( ! is_array( $default_args ) || ! is_array( $request_args ) || ! is_string( $provider_key ) ) {
				return false;
			}

			unset(
				$default_args[ Jet_Smart_Filters_Request_Signature::QUERY_SIGNATURE_KEY ],
				$default_args[ Jet_Smart_Filters_Request_Signature::QUERY_CONTEXT_KEY ]
			);

			if (
				! jet_smart_filters()->request_data_validator->is_safe_request_data( $default_args, 0, 6, 100, 2000 )
				|| ! jet_smart_filters()->request_data_validator->is_safe_request_data( $request_args )
			) {
				return false;
			}

			if ( ! jet_smart_filters()->request_signature->is_enabled() ) {
				$provider_data = jet_smart_filters()->query->decode_provider_data( $provider_key );
				$provider      = ! empty( $provider_data['provider'] ) ? $provider_data['provider'] : '';
				$default_args  = jet_smart_filters()->query->sanitize_unsigned_default_query( $default_args, $provider );
			}

			$allowed_args = array(
				'tax_query',
				'meta_query',
				'date_query',
				'post__in',
				'post__not_in',
				's',
			);
			$query_args   = $default_args;

			foreach ( $request_args as $key => $value ) {
				if ( ! is_string( $key ) || ! in_array( $key, $allowed_args, true ) ) {
					continue;
				}

				switch ( $key ) {
					case 'tax_query':
						$value = jet_smart_filters()->request_data_validator->validate_query_clauses( $value, $this->get_indexed_query_keys( $provider_key, 'tax_query' ), 'taxonomy' );
						break;

					case 'meta_query':
						$value = jet_smart_filters()->request_data_validator->validate_query_clauses( $value, $this->get_indexed_query_keys( $provider_key, 'meta_query' ), 'key' );
						break;

					case 'date_query':
						$value = is_array( $value ) ? $value : false;
						break;

					case 'post__in':
						$value = wp_parse_id_list( $value );
						if ( ! empty( $query_args[ $key ] ) ) {
							$value = array_values( array_intersect( wp_parse_id_list( $query_args[ $key ] ), $value ) );

							// WP_Query ignores an empty post__in and returns all matching posts.
							if ( empty( $value ) ) {
								$value = array( 0 );
							}
						}
						break;

					case 'post__not_in':
						$value = array_values( array_unique( array_merge( wp_parse_id_list( isset( $query_args[ $key ] ) ? $query_args[ $key ] : array() ), wp_parse_id_list( $value ) ) ) );
						break;

					case 's':
						$value = is_string( $value ) ? sanitize_text_field( $value ) : false;

						if ( ! empty( $query_args['s'] ) ) {
							$value = false;
						}
						break;
				}

				if ( false === $value ) {
					return false;
				}

				if ( in_array( $key, array( 'tax_query', 'meta_query', 'date_query' ), true ) && ! empty( $query_args[ $key ] ) ) {
					$query_args[ $key ] = array(
						'relation' => 'AND',
						$query_args[ $key ],
						$value,
					);
				} else {
					$query_args[ $key ] = $value;
				}
			}

			return $query_args;
		}

		/**
		 * Return indexable taxonomy or meta keys registered for a provider query.
		 *
		 * @param string $provider_key Provider and query ID pair.
		 * @param string $query_type   Indexer query type.
		 *
		 * @return array
		 */
		public function get_indexed_query_keys( $provider_key, $query_type ) {

			if ( empty( $this->indexing_data[ $provider_key ][ $query_type ] ) || ! is_array( $this->indexing_data[ $provider_key ][ $query_type ] ) ) {
				return array();
			}

			$keys = array();

			foreach ( array_keys( $this->indexing_data[ $provider_key ][ $query_type ] ) as $key ) {
				$key = explode( '|', (string) $key, 2 )[0];
				$keys = array_merge( $keys, array_map( 'trim', explode( ',', $key ) ) );
			}

			return array_values( array_unique( array_filter( $keys ) ) );
		}

		public function get_indexed_data( $provider_key, $query_args ) {
			if ( ! is_string( $provider_key ) || ! is_array( $query_args ) ) {
				return array();
			}

			$indexed_data  = array();
			$queried_ids   = $this->get_queried_ids( $query_args );
			$indexing_data = isset( $this->indexing_data[$provider_key] ) ? $this->indexing_data[$provider_key] : array();
			$type          = ! empty( $query_args['_query_type'] ) && $query_args['_query_type'] === 'users' ? 'user' : 'post';
			$conditions     = array();
			$prepare_values = array();

			$pre_get_data = apply_filters( 'jet-smart-filters/pre-get-indexed-data', false, $provider_key, $query_args, $this );

			if ( false !== $pre_get_data ) {
				return $pre_get_data;
			}

			if ( empty( $indexing_data ) ) {
				return $indexed_data;
			}

			foreach ( $indexing_data as $query_type => $query_data ) {
				if ( ! is_array( $query_data ) ) {
					continue;
				}

				switch ( $query_type ) {
					case 'tax_query':
						foreach ( $query_data as $tax_key => $tax_data ) {
							$tax_key  = (string) $tax_key;
							$tax_data = array_values( array_filter( (array) $tax_data, 'is_scalar' ) );

							if ( empty( $tax_data ) ) {
								continue;
							}

							$tax_value_placeholders = implode( ', ', array_fill( 0, count( $tax_data ), '%s' ) );
							$conditions[]           = "(item_query = %s AND item_key = %s AND item_value IN ( $tax_value_placeholders ))";
							$prepare_values[]       = $query_type;
							$prepare_values[]       = $tax_key;
							$prepare_values         = array_merge( $prepare_values, array_map( 'strval', $tax_data ) );
						}

						break;

					case 'meta_query':
						foreach ( $query_data as $meta_key => $meta_data ) {
							$meta_key = (string) $meta_key;
							if ( ! $meta_data ) {
								continue;
							}

							if ( strpos( $meta_key, '|' ) ) {
								$suffix_data = explode( '|', $meta_key, 2 );

								switch ( $suffix_data[1] ) {
									case 'range':
										$range_data = array();

										foreach ( (array) $meta_data as $value ) {
											if ( ! is_array( $value ) || ! isset( $value['min'], $value['max'] ) ) {
												continue;
											}

											if ( ! isset( $range_data['min'] ) || $range_data['min'] > $value['min'] ) {
												$range_data['min'] = $value['min'];
											}

											if ( ! isset( $range_data['max'] ) || $range_data['max'] < $value['max'] ) {
												$range_data['max'] = $value['max'];
											}
										}

										if ( ! isset( $range_data['min'], $range_data['max'] ) ) {
											continue 2;
										}

										$meta_keys             = array_values( array_filter( array_map( 'trim', explode( ',', $suffix_data[0] ) ) ) );

										if ( empty( $meta_keys ) ) {
											continue 2;
										}

										$meta_key_placeholders = implode( ', ', array_fill( 0, count( $meta_keys ), '%s' ) );
										$conditions[]          = "(item_query = %s AND item_key IN ( $meta_key_placeholders ) AND item_value >= %f AND item_value <= %f)";
										$prepare_values[]      = $query_type;
										$prepare_values        = array_merge( $prepare_values, $meta_keys );
										$prepare_values[]      = (float) $range_data['min'];
										$prepare_values[]      = (float) $range_data['max'];

									break;
								}
							} else {
								$meta_keys = array_values( array_filter( array_map( 'trim', explode( ',', $meta_key ) ) ) );
								$meta_data = array_values( array_filter( (array) $meta_data, 'is_scalar' ) );

								if ( empty( $meta_keys ) || empty( $meta_data ) ) {
									continue;
								}

								$meta_key_placeholders   = implode( ', ', array_fill( 0, count( $meta_keys ), '%s' ) );
								$meta_value_placeholders = implode( ', ', array_fill( 0, count( $meta_data ), '%s' ) );
								$conditions[]            = "(item_query = %s AND item_key IN ( $meta_key_placeholders ) AND item_value IN ( $meta_value_placeholders ))";
								$prepare_values[]        = $query_type;
								$prepare_values          = array_merge( $prepare_values, $meta_keys, array_map( 'strval', $meta_data ) );
							}
						}

						break;
				}
			}

			$sql_and = '';

			if ( $conditions ) {
				$sql_and = 'AND (' . implode( ' OR ', $conditions ) . ')';
			}

			global $wpdb;

			if ( array( -1 ) !== $queried_ids ) {
				$queried_ids = wp_parse_id_list( $queried_ids );
			}

			if ( empty( $queried_ids ) ) {
				$queried_ids = array( -1 );
			}

			$id_placeholders = implode( ', ', array_fill( 0, count( $queried_ids ), '%d' ) );
			$sql = $wpdb->prepare( "
			SELECT MAX(item_query) as item_query, MAX(item_key) as item_key, item_value, COUNT(item_id) as count
				FROM " . jet_smart_filters()->indexer->table_name . "
				WHERE item_id IN ( $id_placeholders )
				AND (type = %s)
				$sql_and
				GROUP BY item_key, item_value
				ORDER BY item_value ASC",
				array_merge( $queried_ids, array( $type ), $prepare_values )
			);
			$result = $wpdb->get_results( $sql, ARRAY_A );

			foreach ( $this->indexing_data[$provider_key] as $query_type => $query_type_data ) {
				$indexed_data[$query_type] = array();

				foreach ( $query_type_data as $key => $data ) {

					if ( strpos( $key, '|' ) ) {
						$suffix_data = explode( '|', $key, 2 );

						switch ( $suffix_data[1] ) {
							case 'range':
								foreach ( $data as $range_key => $range_data ) {
									foreach ( $result as $row ) {
										$is_current_key = strpos( $suffix_data[0], ',' )
											? boolval( preg_match( "/\b" . $row['item_key'] . "\b/i", $suffix_data[0] ) )
											: $row['item_key'] === $suffix_data[0];

										if ( ! $is_current_key || $row['item_query'] !== $query_type || $row['item_value'] < $range_data['min'] || $row['item_value'] > $range_data['max'] ) {
											continue;
										}

										if ( ! isset( $indexed_data[$query_type][$suffix_data[0]] ) ) {
											$indexed_data[$query_type][$suffix_data[0]] = array();
										}

										if ( ! isset( $indexed_data[$query_type][$suffix_data[0]][$range_key] ) ) {
											$indexed_data[$query_type][$suffix_data[0]][$range_key] = 0;
										}

										$indexed_data[$query_type][$suffix_data[0]][$range_key] += $row['count'];
									}
								}

							break;
						}
					} elseif ( ! empty( $data ) ) {
						foreach ( $data as $item ) {
							foreach ( $result as $row ) {
								$is_current_key = strpos( $key, ',' )
									? boolval( preg_match( "/\b" . $row['item_key'] . "\b/i", $key ) )
									: $row['item_key'] === $key;

								if ( ! $is_current_key || $row['item_query'] !== $query_type || $row['item_value'] != $item ) {
									continue;
								}

								if ( ! isset( $indexed_data[$query_type][$key] ) ) {
									$indexed_data[$query_type][$key] = array();
								}

								$indexed_data[$query_type][$key][$item] = $row['count'];
							}
						}
					}

					$indexed_data[ $query_type ] = apply_filters(
						'jet-smart-filters/filters/indexed-data/query-type-data',
						$indexed_data[ $query_type ],
						$query_type,
						$key,
						$data,
						$this,
						$queried_ids,
						$provider_key
					);
				}
			}

			return apply_filters( 'jet-smart-filters/filters/indexed-data', $indexed_data, array(
				'provider_key'  => $provider_key,
				'query_args'    => $query_args,
				'type'          => $type,
				'queried_ids'   => $queried_ids,
				'indexing_data' => $indexing_data
			) );
		}

		/**
		 * Add indexing data from filter by id
		 */
		public function add_indexing_data_from_filter( $provider_key, $filter_id ) {

			$filter_id = absint( $filter_id );

			if ( ! is_string( $provider_key ) || ! $filter_id ) {
				return;
			}

			if ( ! isset( $this->registered_filters[ $provider_key ] ) ) {
				$this->registered_filters[ $provider_key ] = array();
			}

			$this->registered_filters[ $provider_key ][] = $filter_id;

			$data        = array();
			$query_type  = '';
			$query_var   = '';
			// filter source for getting indexed data
			$source      = apply_filters( 'jet-smart-filters/indexer/filter-source', get_post_meta( $filter_id ) );
			$data_source = ! empty( $source['_data_source'] ) ? $source['_data_source'][0] : false;

			if ( $source['_filter_type'][0] === 'check-range' ) {
				$query_type = 'meta_query';
				$query_var  = $source['_query_var'][0];

				if ( ! $query_var ) {
					return;
				}

				$query_var .= '|range';

				if ( ! isset( $this->indexing_data[$provider_key][$query_type] ) ) {
					$this->indexing_data[$provider_key][$query_type] = array();
				}

				if ( ! isset( $this->indexing_data[$provider_key][$query_type][$query_var] ) ) {
					$this->indexing_data[$provider_key][$query_type][$query_var] = array();
				}

				$range_items = jet_smart_filters()->utils->safe_unserialize( $source['_source_manual_input_range'][0], array() );

				foreach ( $range_items as $range_data ) {
					$min = ! empty( $range_data['min'] ) ? intval( $range_data['min'] ) : 0;
					$max = ! empty( $range_data['max'] ) || $range_data['max'] === '0' ? intval( $range_data['max'] ) : 100;
					$key = $min . '_' . $max;

					if ( ! isset( $this->indexing_data[$provider_key][$query_type][$query_var][$key] ) ) {
						$this->indexing_data[$provider_key][$query_type][$query_var][$key] = array(
							'min' => $min,
							'max' => $max,
						);
					}
				}

				return;
			} else if ( $source['_filter_type'][0] === 'select' && filter_var( $source['_is_hierarchical'][0], FILTER_VALIDATE_BOOLEAN ) ) {
				$query_type  = 'tax_query';

				$hierarchy_items = jet_smart_filters()->utils->safe_unserialize( $source['_ih_source_map'][0], array() );

				foreach ( $hierarchy_items as $item ) {
					$tax = $item['tax'];
					$data[$tax] = $this->get_terms_by_tax( $tax );
				}
			} else {
				switch ( $data_source ) {
					case 'taxonomies':
						$query_type = 'tax_query';
						$tax        = ! empty( $source['_source_taxonomy'] ) ? $source['_source_taxonomy'][0] : false;

						$exclude_include      = isset( $source['_use_exclude_include'] ) && isset( $source['_use_exclude_include'][0] ) ? $source['_use_exclude_include'][0] : '';
						$data_exclude_include = ! empty( $source['_data_exclude_include'][0] )
							? jet_smart_filters()->utils->safe_unserialize( $source['_data_exclude_include'][0], false )
							: false;

						if ( $exclude_include === 'include' ) {
							$data[$tax] = is_array( $data_exclude_include ) && ! empty( $data_exclude_include )
								? $data_exclude_include
								: $this->get_terms_by_tax( $tax );
						} else {
							$data[$tax] = $this->get_terms_by_tax( $tax );

							if ( $exclude_include === 'exclude' && is_array( $data_exclude_include ) && ! empty( $data_exclude_include ) ) {
								$data[$tax] = array_diff( $data[$tax], $data_exclude_include );
							}
						}

						if ( ! empty( $source['_is_custom_query_var'] ) && filter_var( $source['_is_custom_query_var'][0], FILTER_VALIDATE_BOOLEAN ) && $source['_custom_query_var'][0] ) {
							$query_type = 'meta_query';
							$query_var  = $source['_custom_query_var'][0];

							$data[$query_var] = $data[$tax];
							unset( $data[$tax] );
						}

						break;

					case 'manual_input':
						$query_type = 'meta_query';
						$query_var  = $source['_query_var'][0];

						if ( $source['_filter_type'][0] === 'color-image' ) {
							$input_data = $source['_source_color_image_input'][0];
						} else {
							$input_data = $source['_source_manual_input'][0];
						}

						if ( ! $input_data ) {
							return;
						}

						$manual_input_items = jet_smart_filters()->utils->safe_unserialize( $input_data, array() );

						foreach ( $manual_input_items as $input_item ) {
							$data[$query_var][] = $input_item['value'];
						}

						break;

					case 'posts':
						$query_type = 'meta_query';
						$query_var  = $source['_query_var'][0];
						$post_type  = isset( $source['_source_post_type'] ) ? $source['_source_post_type'][0] : false;

						if ( ! $post_type || ! is_scalar( $post_type ) ) {
							break;
						}

						global $wpdb;

						$input_data = $wpdb->get_results(
							$wpdb->prepare(
								"SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish'",
								$post_type
							)
						);

						if ( empty( $input_data ) ) {
							break;
						}

						foreach ( $input_data as $input_item ) {
							$data[$query_var][] = $input_item->ID;
						}

						break;

					case 'custom_fields':
						$query_type          = 'meta_query';
						$query_var           = $source['_query_var'][0];
						$custom_field        = $source['_source_custom_field'][0];
						$custom_field_source = $source['_custom_field_source_plugin'][0];
						$get_from_field_data = isset( $source['_source_get_from_field_data'][0] ) ? filter_var( $source['_source_get_from_field_data'][0], FILTER_VALIDATE_BOOLEAN ) : false;

						if ( $get_from_field_data ) {
							$custom_field_options = jet_smart_filters()->data->get_choices_from_field_data( array(
								'field_key' => $custom_field,
								'source'    => $custom_field_source,
							) );
						} else {
							$custom_field_options = jet_smart_filters()->data->get_options_by_field_key( $custom_field );
						}

						$data[$query_var] = array_keys( $custom_field_options );

						break;

					default:
						$query_var   = $source['_query_var'][0];
						$custom_args = apply_filters( 'jet-smart-filters/indexer/custom-args', array(), $filter_id );
						$query_type  = isset( $custom_args['query_type'] ) ? $custom_args['query_type'] : 'meta_query';
						$options     = ! empty( $custom_args['options'] ) ? $custom_args['options'] : false;

						if ( empty( $options ) ) {
							$options = apply_filters( 'jet-smart-filters/filters/filter-options', $options, $filter_id, false );

							if ( ! empty( $options ) ) {
								$options = array_filter( array_keys( $options ), function( $value ) {
									return '' !== $value;
								} );
							}
						}

						$data[$query_var] = $options;

						break;
				}
			}

			if ( $query_type && $data ) {
				if ( ! isset( $this->indexing_data[$provider_key] ) ) {
					$this->indexing_data[$provider_key] = array();
				}

				if ( ! isset( $this->indexing_data[$provider_key][$query_type] ) ) {
					$this->indexing_data[$provider_key][$query_type] = array();
				}

				if ( $query_type === 'tax_query' ) {
					$this->indexing_data[$provider_key][$query_type] = array_merge_recursive( $this->indexing_data[$provider_key][$query_type], $data );
				} else {
					$this->indexing_data[$provider_key][$query_type] = array_merge( $this->indexing_data[$provider_key][$query_type], $data );
				}
			}

		}

		public function get_terms_by_tax( $tax ) {

			global $wpdb;
			$terms = array();

			if ( ! is_scalar( $tax ) ) {
				return $terms;
			}

			$sql = $wpdb->prepare( "
				SELECT term_id
				FROM $wpdb->term_taxonomy
				WHERE taxonomy = %s",
				$tax
			);

			$result = $wpdb->get_results( $sql, ARRAY_A );
			foreach ( $result as $term_id ) {
				array_push( $terms, $term_id['term_id'] );
			}

			return $terms;
		}

		public function get_queried_ids( $args ) {

			$args = apply_filters( 'jet-smart-filters/indexer/queried-ids-args', $args );

			$ids  = array( -1 );
			$type = ! empty( $args['_query_type'] ) ? $args['_query_type'] : 'posts';

			unset( $args['jet_smart_filters'] );
			unset( $args['paged'] );
			unset( $args['nopaging'] );

			switch ( $type ) {
				case 'posts':
				case 'current-wp-query':
					// arguments that are better to remove to optimize the query
					unset( $args['wc_query'] );

					$post_main_args = [
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids'
					];

					$query = new WP_Query( wp_parse_args( $post_main_args, $args ) );

					if ( ! empty( $query->posts ) ) {
						$ids = $query->posts;
					}

					break;

				case 'users':
					$user_main_args = [
						'number'      => -1,
						'count_total' => false,
						'fields'      => 'ID'
					];

					$users_ids =  get_users( wp_parse_args( $user_main_args, $args ) ) ;

					if ( ! empty( $users_ids ) ) {
						$ids = $users_ids;
					}

					break;

				case 'wc-product-query':
					$wc_main_args = [
						'status' => 'publish',
						'limit'  => -1,
						'return' => 'ids'
					];

					$wc_ids =  wc_get_products( wp_parse_args( $wc_main_args, $args ) ) ;

					if ( ! empty( $wc_ids->products ) ) {
						$ids = $wc_ids->products;
					}

					break;
			}

			if ( array( -1 ) === $ids ) {
				return $ids;
			}

			return wp_parse_id_list( $ids );
		}
	}
}
