<?php
/**
 * Public filter request validation helper.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Filter_Request_Validator' ) ) {
	/**
	 * Validates the allowed query surface of public filtering requests.
	 */
	class Jet_Smart_Filters_Filter_Request_Validator {

		private $query_rules = array();

		/**
		 * Register a query key rendered for a provider.
		 *
		 * @param string       $provider             Provider ID.
		 * @param string       $query_id             Provider query ID.
		 * @param string       $query_key            Frontend query key.
		 * @param array|string $additional_providers Additional provider keys.
		 * @param array        $rule                 Optional validation rule.
		 */
		public function register_query_key( $provider, $query_id, $query_key, $additional_providers = array(), $rule = array() ) {

			$provider = is_scalar( $provider ) ? (string) $provider : '';
			$query_id = is_scalar( $query_id ) && '' !== (string) $query_id ? (string) $query_id : 'default';
			$query_key = is_scalar( $query_key ) ? (string) $query_key : '';

			if ( ! $provider || ! $this->is_safe_query_key( $query_key ) ) {
				return;
			}

			$this->add_query_rule( $provider, $query_id, $query_key, $rule );

			foreach ( $this->prepare_additional_providers( $additional_providers ) as $provider_key ) {
				$provider_data = jet_smart_filters()->query->decode_provider_data( $provider_key );

				if ( empty( $provider_data['provider'] ) ) {
					continue;
				}

				$this->add_query_rule(
					$provider_data['provider'],
					! empty( $provider_data['query_id'] ) ? $provider_data['query_id'] : 'default',
					$query_key,
					$rule
				);
			}
		}

		/**
		 * Register pagination for a provider.
		 *
		 * @param string       $provider             Provider ID.
		 * @param string       $query_id             Provider query ID.
		 * @param array|string $additional_providers Additional provider keys.
		 */
		public function register_pagination( $provider, $query_id = 'default', $additional_providers = array() ) {

			$this->register_query_key(
				$provider,
				$query_id,
				'jet_paged',
				$additional_providers,
				array( 'type' => 'pagination' )
			);
		}

		/**
		 * Create query contexts for localized frontend data.
		 *
		 * @param array $queries  Provider default queries.
		 * @param array $referrer Optional frontend referrer data.
		 * @param array $props    Provider query properties.
		 *
		 * @return array
		 */
		public function get_query_contexts( $queries = array(), $referrer = array(), $props = array() ) {

			$contexts = array();
			$referrer = $this->prepare_referrer( $referrer );

			if ( false === $referrer ) {
				return $contexts;
			}

			foreach ( $this->query_rules as $provider => $provider_queries ) {
				foreach ( $provider_queries as $query_id => $rules ) {
					$rules = apply_filters( 'jet-smart-filters/request/query-rules', $rules, $provider, $query_id, $this );
					$rules = $this->prepare_query_rules( $rules, $provider, $query_id, $props );

					if ( false === $rules ) {
						continue;
					}

					$defaults = $this->get_provider_data( $queries, $provider, $query_id );
					$context  = array( 'rules' => $rules );

					if ( jet_smart_filters()->request_signature->is_enabled() ) {
						$context['signature'] = jet_smart_filters()->request_signature->create(
							$this->get_query_signature_data( $provider, $query_id, $defaults, $rules, $referrer )
						);
					}

					$contexts[ $provider ][ $query_id ] = $context;
				}
			}

			return $contexts;
		}

		/**
		 * Verify the query, pagination and mutable request data used by filtering AJAX.
		 *
		 * @param string $provider Provider ID.
		 * @param string $query_id Provider query ID.
		 * @param array  $request  Public request data.
		 *
		 * @return bool
		 */
		public function verify_filter_request( $provider, $query_id, $request ) {

			if ( ! is_array( $request ) ) {
				return false;
			}

			$query = isset( $request['query'] ) && is_array( $request['query'] )
				? wp_unslash( $request['query'] )
				: array();
			$defaults = isset( $request['defaults'] ) && is_array( $request['defaults'] )
				? wp_unslash( $request['defaults'] )
				: array();
			$context = isset( $request['query_context'] ) && is_array( $request['query_context'] )
				? wp_unslash( $request['query_context'] )
				: ( isset( $defaults[ Jet_Smart_Filters_Request_Signature::QUERY_CONTEXT_KEY ] ) && is_array( $defaults[ Jet_Smart_Filters_Request_Signature::QUERY_CONTEXT_KEY ] )
					? $defaults[ Jet_Smart_Filters_Request_Signature::QUERY_CONTEXT_KEY ]
					: array() );

			unset( $defaults[ Jet_Smart_Filters_Request_Signature::QUERY_CONTEXT_KEY ] );
			$referrer = isset( $request['referrer'] ) && is_array( $request['referrer'] )
				? $this->prepare_referrer( wp_unslash( $request['referrer'] ) )
				: array();
			$rules = isset( $context['rules'] ) && is_array( $context['rules'] )
				? $this->prepare_query_rules( $context['rules'], $provider, $query_id )
				: false;
			$signature = isset( $context['signature'] ) && is_string( $context['signature'] )
				? $context['signature']
				: '';

			if ( false === $rules || false === $referrer ) {
				return false;
			}

			if ( jet_smart_filters()->request_signature->is_enabled() ) {
				if (
					! $signature
					|| ! jet_smart_filters()->request_signature->verify(
						$signature,
						$this->get_query_signature_data( $provider, $query_id, $defaults, $rules, $referrer )
					)
				) {
					return false;
				}
			}

			if ( ! jet_smart_filters()->request_data_validator->is_safe_request_data( $query, 0, 6, 100, 2000 ) ) {
				return false;
			}

			foreach ( $query as $key => $value ) {
				if ( ! is_string( $key ) || ! isset( $rules[ $key ] ) || ! $this->validate_query_value( $value, $rules[ $key ], $key ) ) {
					return false;
				}
			}

			if ( isset( $request['paged'] ) ) {
				if ( ! isset( $rules['jet_paged'] ) || ! $this->validate_query_value( $request['paged'], $rules['jet_paged'] ) ) {
					return false;
				}

				if ( isset( $query['jet_paged'] ) && absint( $request['paged'] ) !== absint( $query['jet_paged'] ) ) {
					return false;
				}
			}

			if ( isset( $request['props'] ) && ! jet_smart_filters()->request_data_validator->is_safe_request_data( $request['props'], 0, 5, 100, 2000 ) ) {
				return false;
			}

			return (bool) apply_filters(
				'jet-smart-filters/request/validate-filter-query',
				true,
				$query,
				$rules,
				$provider,
				$query_id,
				$this
			);
		}

		private function add_query_rule( $provider, $query_id, $query_key, $rule ) {

			$rule = is_array( $rule ) ? $rule : array();
			$rule['type'] = ! empty( $rule['type'] ) ? sanitize_key( $rule['type'] ) : 'filter';

			if ( isset( $this->query_rules[ $provider ][ $query_id ][ $query_key ] ) ) {
				$current_rule = $this->query_rules[ $provider ][ $query_id ][ $query_key ];

				if ( 'sorting' === $rule['type'] && 'sorting' === $current_rule['type'] ) {
					$current_values = ! empty( $current_rule['values'] ) ? $current_rule['values'] : array();
					$new_values     = ! empty( $rule['values'] ) && is_array( $rule['values'] ) ? $rule['values'] : array();
					$rule['values'] = array_values( array_unique( array_merge( $current_values, $new_values ) ) );
				}
			}

			$this->query_rules[ $provider ][ $query_id ][ $query_key ] = $rule;
		}

		private function prepare_additional_providers( $providers ) {

			if ( is_string( $providers ) ) {
				$providers = json_decode( html_entity_decode( $providers, ENT_QUOTES, 'UTF-8' ), true );
			}

			if ( ! is_array( $providers ) ) {
				return array();
			}

			return array_values( array_filter( $providers, 'is_string' ) );
		}

		private function prepare_query_rules( $rules, $provider, $query_id, $props = array() ) {

			if ( ! is_array( $rules ) || empty( $rules ) || count( $rules ) > 100 ) {
				return false;
			}

			$prepared = array();
			$query_props = $this->get_provider_data( $props, $provider, $query_id );

			foreach ( $rules as $key => $rule ) {
				if ( ! is_string( $key ) || ! $this->is_safe_query_key( $key ) || ! is_array( $rule ) ) {
					return false;
				}

				$type = ! empty( $rule['type'] ) && is_string( $rule['type'] ) ? sanitize_key( $rule['type'] ) : 'filter';

				if ( ! in_array( $type, array( 'filter', 'sorting', 'pagination' ), true ) ) {
					return false;
				}

				$prepared[ $key ] = array( 'type' => $type );

				if ( 'sorting' === $type ) {
					$values = ! empty( $rule['values'] ) && is_array( $rule['values'] ) ? $rule['values'] : array();
					$values = array_values( array_unique( array_filter( $values, function( $value ) {
						return is_string( $value ) && strlen( $value ) <= 2000;
					} ) ) );

					if ( count( $values ) > 100 ) {
						return false;
					}

					$prepared[ $key ]['values'] = $values;
				} elseif ( 'pagination' === $type ) {
					$max_page = ! empty( $query_props['max_num_pages'] )
						? absint( $query_props['max_num_pages'] )
						: ( ! empty( $rule['max'] ) ? absint( $rule['max'] ) : 10000 );
					$prepared[ $key ]['max'] = max( 1, $max_page );
				}
			}

			ksort( $prepared );

			return $prepared;
		}

		private function validate_query_value( $value, $rule, $key = '' ) {

			switch ( $rule['type'] ) {
				case 'sorting':
					$values = is_array( $value ) ? $value : array( $value );

					foreach ( $values as $sorting_value ) {
						if ( ! is_string( $sorting_value ) || ! in_array( $sorting_value, $rule['values'], true ) ) {
							return false;
						}
					}

					return true;

				case 'pagination':
					return is_scalar( $value )
						&& preg_match( '/^\d+$/', (string) $value )
						&& absint( $value ) >= 1
						&& absint( $value ) <= $rule['max'];
			}

			return $this->validate_filter_value( $value, $key );
		}

		private function validate_filter_value( $value, $key ) {

			if ( ! is_array( $value ) ) {
				return is_scalar( $value );
			}

			foreach ( $value as $item ) {
				if ( ! is_scalar( $item ) ) {
					return false;
				}
			}

			if (
				'date_query' === $key
				|| 0 === strpos( $key, '_date_query_' )
				|| 0 === strpos( $key, '__s_' )
			) {
				return false;
			}

			$key_parts = explode( '|', $key, 2 );
			$suffixes  = isset( $key_parts[1] ) ? explode( ',', $key_parts[1] ) : array();

			return ! array_intersect( $suffixes, array( 'date', 'range', 'rating', 'search' ) );
		}

		private function get_query_signature_data( $provider, $query_id, $defaults, $rules, $referrer ) {

			if ( is_array( $defaults ) ) {
				unset(
					$defaults[ Jet_Smart_Filters_Request_Signature::QUERY_SIGNATURE_KEY ],
					$defaults[ Jet_Smart_Filters_Request_Signature::QUERY_CONTEXT_KEY ]
				);
			}

			return array(
				'provider' => (string) $provider,
				'query_id' => (string) $query_id,
				'defaults' => is_array( $defaults ) ? $defaults : array(),
				'rules'    => $rules,
				'referrer' => $referrer,
			);
		}

		private function get_provider_data( $data, $provider, $query_id ) {

			if ( isset( $data[ $provider ][ $query_id ] ) && is_array( $data[ $provider ][ $query_id ] ) ) {
				return $data[ $provider ][ $query_id ];
			}

			return array();
		}

		private function prepare_referrer( $referrer ) {

			if ( empty( $referrer ) ) {
				return array();
			}

			if ( ! is_array( $referrer ) ) {
				return false;
			}

			$prepared = array();

			foreach ( array( 'uri', 'info', 'self' ) as $key ) {
				if ( ! isset( $referrer[ $key ] ) || '' === $referrer[ $key ] ) {
					continue;
				}

				if ( ! is_string( $referrer[ $key ] ) || strlen( $referrer[ $key ] ) > 4096 ) {
					return false;
				}

				$prepared[ $key ] = $referrer[ $key ];
			}

			return $prepared;
		}

		private function is_safe_query_key( $key ) {

			return is_string( $key )
				&& '' !== $key
				&& strlen( $key ) <= 300
				&& ! preg_match( '/[\x00-\x1F\x7F]/', $key );
		}
	}
}
