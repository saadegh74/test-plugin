<?php
/**
 * Request signature helper.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Request_Signature' ) ) {
	/**
	 * Signs and verifies public filtering request data.
	 */
	class Jet_Smart_Filters_Request_Signature {

		const QUERY_SIGNATURE_KEY        = '_jsf_query_signature';
		const QUERY_CONTEXT_KEY          = '_jsf_query_context';
		const SETTINGS_SIGNATURE_KEY     = 'jsf_signature';
		const SETTINGS_SIGNATURE_VERSION = 'v3';

		/**
		 * Check whether request signatures are enabled.
		 *
		 * Signatures are enabled by default. Disabling them is an explicit
		 * compatibility opt-out and reduces protection for public requests.
		 *
		 * @return bool
		 */
		public function is_enabled() {

			$enabled = filter_var(
				jet_smart_filters()->settings->get( 'use_signature_verification', true ),
				FILTER_VALIDATE_BOOLEAN
			);

			// Enable protection during the first request of a 3.8.6 install/update.
			if ( ! get_option( 'jet_smart_filters_386_signatures_migrated', false ) ) {
				$enabled = true;
			}

			return (bool) apply_filters( 'jet-smart-filters/request/signatures-enabled', $enabled, $this );
		}

		/**
		 * Create a signature based on input data.
		 *
		 * @param array $data Data to sign.
		 *
		 * @return string
		 */
		public function create( $data = array() ) {

			$signature_string = wp_json_encode( $this->prepare_data( $data ) );

			return 'v2:' . hash_hmac( 'sha256', $signature_string, wp_salt( 'auth' ) );
		}

		/**
		 * Verify a signature, including legacy signatures from cached pages.
		 *
		 * @param string $signature Signature to verify.
		 * @param array  $data      Signed data.
		 *
		 * @return bool
		 */
		public function verify( $signature, $data = array() ) {

			if ( ! is_string( $signature ) || '' === $signature ) {
				return false;
			}

			if ( 0 === strpos( $signature, 'v2:' ) ) {
				return hash_equals( $this->create( $data ), $signature );
			}

			return hash_equals( $this->create_legacy( $data ), $signature );
		}

		/**
		 * Create a signature for a default provider query.
		 *
		 * @param array  $query_args Default provider query arguments.
		 * @param string $provider   Provider ID.
		 * @param string $query_id   Provider query ID.
		 *
		 * @return string
		 */
		public function create_query( $query_args = array(), $provider = '', $query_id = 'default' ) {

			$query_args = $this->prepare_query_args( $query_args );

			return $this->create( array(
				'provider' => $provider,
				'query_id' => $query_id,
				'query'    => $query_args,
			) );
		}

		/**
		 * Verify the signed default provider query in the current AJAX request.
		 *
		 * @param string $provider Provider ID.
		 * @param string $query_id Provider query ID.
		 *
		 * @return bool
		 */
		public function verify_default_query( $provider, $query_id ) {

			$request = jet_smart_filters()->data->get_request();
			$defaults = isset( $request['defaults'] ) && is_array( $request['defaults'] )
				? wp_unslash( $request['defaults'] )
				: array();
			$signature = isset( $defaults[ self::QUERY_SIGNATURE_KEY ] ) && is_string( $defaults[ self::QUERY_SIGNATURE_KEY ] )
				? $defaults[ self::QUERY_SIGNATURE_KEY ]
				: '';

			$defaults = $this->prepare_query_args( $defaults );

			$result = $provider && $signature && $this->verify(
				$signature,
				array(
					'provider' => $provider,
					'query_id' => $query_id,
					'query'    => $defaults,
				)
			);

			$result = apply_filters( 'jet-smart-filters/render/ajax/verify-default-query-signature', $result, $defaults, $provider, $query_id );

			if ( $result ) {
				jet_smart_filters()->query->set_verified_default_query( $defaults );
			}

			return $result;
		}

		/**
		 * Create a provider- and query-scoped signature for render settings.
		 *
		 * @param array  $settings    Provider settings.
		 * @param string $provider_id Provider ID.
		 * @param string $query_id    Provider query ID.
		 *
		 * @return string
		 */
		public function create_provider_settings( $settings, $provider_id, $query_id = 'default' ) {

			$settings = $this->prepare_provider_settings( $settings, $provider_id );
			$data = array(
				'provider' => (string) $provider_id,
				'query_id' => (string) $query_id,
				'settings' => $settings,
			);
			$signature_string = wp_json_encode( $this->prepare_data( $data ) );

			return self::SETTINGS_SIGNATURE_VERSION . ':' . hash_hmac( 'sha256', $signature_string, wp_salt( 'auth' ) );
		}

		/**
		 * Verify the signed provider settings in the current AJAX request.
		 *
		 * @param string $provider_id Provider ID.
		 * @param string $query_id    Provider query ID.
		 *
		 * @return bool
		 */
		public function verify_provider_settings( $provider_id, $query_id = 'default' ) {

			$request = jet_smart_filters()->data->get_request();
			$result  = false;

			if ( ! empty( $request['settings'] ) && is_array( $request['settings'] ) ) {
				$request['settings'] = wp_unslash( $request['settings'] );
			}

			$request_settings  = array();
			$verified_settings = array();

			if ( ! empty( $request['settings'][ self::SETTINGS_SIGNATURE_KEY ] ) ) {
				$request_signature = $request['settings'][ self::SETTINGS_SIGNATURE_KEY ];
				unset( $request['settings'][ self::SETTINGS_SIGNATURE_KEY ] );
				$verified_settings = $request['settings'];
				$request_settings  = $this->prepare_provider_settings( $request['settings'], $provider_id );

				if ( is_string( $request_signature ) && 0 === strpos( $request_signature, self::SETTINGS_SIGNATURE_VERSION . ':' ) ) {
					$result = hash_equals(
						$this->create_provider_settings( $request['settings'], $provider_id, $query_id ),
						$request_signature
					);
				} else {
					// Old settings signatures are not scoped to a provider/query pair.
					// Trusted server code may opt in temporarily while migrating custom clients.
					$allow_legacy = (bool) apply_filters(
						'jet-smart-filters/render/ajax/allow-legacy-settings-signature',
						false,
						$request_signature,
						$request_settings,
						$provider_id,
						$query_id
					);

					$result = $allow_legacy && is_string( $request_signature ) && $this->verify( $request_signature, $request_settings );
				}
			}

			$result = apply_filters( 'jet-smart-filters/render/ajax/verify-signature', $result );

			if ( $result ) {
				jet_smart_filters()->query->set_verified_query_settings( $verified_settings );
			}

			return $result;
		}

		/**
		 * Prepare provider settings before creating a signature.
		 *
		 * @param array  $settings    Provider settings.
		 * @param string $provider_id Provider ID.
		 *
		 * @return array
		 */
		public function prepare_provider_settings( $settings = array(), $provider_id = false ) {

			if ( ! is_array( $settings ) || ! $provider_id ) {
				return $settings;
			}

			$provider = jet_smart_filters()->providers->get_providers( $provider_id );

			if ( $provider && is_callable( array( $provider, 'prepare_settings_for_signature' ) ) ) {
				return $provider->prepare_settings_for_signature( $settings );
			}

			return $settings;
		}

		/**
		 * Create a signature for a public indexed-data request context.
		 *
		 * @param array  $query_args Default provider query arguments.
		 * @param string $provider   Provider ID.
		 * @param string $query_id   Provider query ID.
		 * @param array  $filter_ids Indexer filter IDs rendered for this query.
		 *
		 * @return string
		 */
		public function create_indexer_context( $query_args = array(), $provider = '', $query_id = 'default', $filter_ids = array() ) {

			$query_args = $this->prepare_query_args( $query_args );
			$filter_ids = $this->prepare_filter_ids( $filter_ids );

			return $this->create( array(
				'provider' => $provider,
				'query_id' => $query_id,
				'query'    => $query_args,
				'filters'  => $filter_ids,
			) );
		}

		/**
		 * Verify a public indexed-data request context.
		 *
		 * @param array  $query_args Default provider query arguments.
		 * @param string $provider   Provider ID.
		 * @param string $query_id   Provider query ID.
		 * @param array  $filter_ids Indexer filter IDs.
		 * @param array  $context    Request context.
		 *
		 * @return bool
		 */
		public function verify_indexer_context( $query_args, $provider, $query_id, $filter_ids, $context ) {

			$signature  = $this->get_context_signature( $context );
			$query_args = $this->prepare_query_args( $query_args );
			$filter_ids = $this->prepare_filter_ids( $filter_ids );

			return $provider && $signature && $this->verify(
				$signature,
				array(
					'provider' => $provider,
					'query_id' => $query_id,
					'query'    => $query_args,
					'filters'  => $filter_ids,
				)
			);
		}

		/**
		 * Create a signature for a public hierarchy request context.
		 *
		 * @param int   $filter_id Hierarchical filter ID.
		 * @param array $args      Hierarchical filter layout arguments.
		 *
		 * @return string
		 */
		public function create_hierarchy_context( $filter_id, $args = array() ) {

			return $this->create( array(
				'filter_id' => absint( $filter_id ),
				'args'      => $args,
			) );
		}

		/**
		 * Verify a public hierarchy request context.
		 *
		 * @param int   $filter_id Hierarchical filter ID.
		 * @param array $args      Hierarchical filter layout arguments.
		 * @param array $context   Request context.
		 *
		 * @return bool
		 */
		public function verify_hierarchy_context( $filter_id, $args, $context ) {

			$signature = $this->get_context_signature( $context );

			return $signature && $this->verify(
				$signature,
				array(
					'filter_id' => absint( $filter_id ),
					'args'      => $args,
				)
			);
		}

		/**
		 * Normalize data before it is signed.
		 *
		 * @param array $data Data to normalize.
		 *
		 * @return array
		 */
		public function prepare_data( $data = array() ) {

			if ( is_object( $data ) ) {
				$data = get_object_vars( $data );
			}

			if ( ! is_array( $data ) ) {
				if ( false === $data ) {
					return 'false';
				}

				if ( true === $data ) {
					return 'true';
				}

				return (string) $data;
			}

			$prepared_data = array();

			foreach ( $data as $key => $value ) {
				// jQuery form serialization omits empty arrays from AJAX payloads.
				if ( is_array( $value ) && empty( $value ) ) {
					continue;
				}

				$prepared_data[ $key ] = $this->prepare_data( $value );
			}

			if ( ! $this->is_list( $prepared_data ) ) {
				ksort( $prepared_data );
			}

			return $prepared_data;
		}

		/**
		 * Create an old-format signature for cached markup compatibility.
		 *
		 * @param array $data Signed data.
		 *
		 * @return string
		 */
		private function create_legacy( $data ) {

			$secret = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

			return md5( wp_json_encode( $this->prepare_legacy_data( $data ) ) . $secret );
		}

		private function get_context_signature( $context ) {

			return is_array( $context ) && isset( $context['signature'] ) && is_string( $context['signature'] )
				? $context['signature']
				: '';
		}

		private function prepare_query_args( $query_args ) {

			$query_args = is_array( $query_args ) ? $query_args : array();

			unset( $query_args[ self::QUERY_SIGNATURE_KEY ], $query_args[ self::QUERY_CONTEXT_KEY ] );

			return $query_args;
		}

		private function prepare_filter_ids( $filter_ids ) {

			return array_values( array_unique( array_filter( array_map( 'absint', (array) $filter_ids ) ) ) );
		}

		private function is_list( $data ) {

			return empty( $data ) || array_keys( $data ) === range( 0, count( $data ) - 1 );
		}

		private function prepare_legacy_data( $data ) {

			$prepared_data = array();

			foreach ( (array) $data as $key => $value ) {
				if ( is_array( $value ) && ! empty( $value ) ) {
					$prepared_data[ $key ] = $this->prepare_legacy_data( $value );
				} elseif ( ! is_array( $value ) ) {
					if ( false === $value ) {
						$value = 'false';
					} elseif ( true === $value ) {
						$value = 'true';
					}

					$prepared_data[ $key ] = (string) $value;
				}
			}

			return array_filter( $prepared_data );
		}
	}
}
