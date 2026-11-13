<?php
/**
 * Data class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Render' ) ) {
	/**
	 * Define Jet_Smart_Filters_Render class
	 */
	class Jet_Smart_Filters_Render {

		private $_rendered_providers = array();
		private $hierarchy_request;
		private $request_query_vars  = array(
			'tax',
			'meta',
			'date',
			'sort',
			'alphabet',
			'_s',
			'search',
			'_sm',
			'search-by-meta',
			'pagenum',
			'plain_query',
		);

		/**
		 * Whether public request signatures are enabled.
		 *
		 * @var bool
		 */
		public $use_signature_verification = true;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			$this->use_signature_verification = jet_smart_filters()->request_signature->is_enabled();

			add_action( 'parse_request', array( $this, 'apply_filters_from_request' ) );
			add_action( 'parse_request', array( $this, 'apply_filters_from_permalink' ) );

			// backward compatibility
			add_action( 'parse_request', array( $this, 'apply_filters_from_request_backward_compatibility' ) );

			add_action( 'wp_ajax_jet_smart_filters', array( $this, 'ajax_apply_filters' ) );
			add_action( 'wp_ajax_nopriv_jet_smart_filters', array( $this, 'ajax_apply_filters' ) );

			$this->hierarchy_request = new Jet_Smart_Filters_Hierarchy_Request();

			add_action( 'wp_ajax_jet_smart_filters_get_hierarchy_level', array( $this->hierarchy_request, 'handle' ) );
			add_action( 'wp_ajax_nopriv_jet_smart_filters_get_hierarchy_level', array( $this->hierarchy_request, 'handle' ) );

			add_action( 'wp_ajax_jet_smart_filters_get_indexed_data', array( $this, 'get_indexed_data' ) );
			add_action( 'wp_ajax_nopriv_jet_smart_filters_get_indexed_data', array( $this, 'get_indexed_data' ) );
		}

		public function get_request_query_vars() {
			return apply_filters( 'jet-smart-filters/render/query-vars', $this->request_query_vars );
		}

		/**
		 * Get indexed data
		 */
		public function get_indexed_data() {

			$request = jet_smart_filters()->data->get_request();
			$indexer = jet_smart_filters()->indexer;

			if ( empty( $indexer->data ) ) {
				wp_send_json_error( __( 'Indexer is disabled.', 'jet-smart-filters' ) );
			}

			/*
			 * This endpoint must remain available to visitors because indexer counters
			 * can be refreshed after client-side content updates. When request signatures
			 * are enabled, bind the request to the provider query and filter IDs localized
			 * by this site. Unsigned mode retains shape and narrowing validation below.
			 */
			$provider_key = isset( $request['provider'] ) && is_string( $request['provider'] )
				? sanitize_text_field( wp_unslash( $request['provider'] ) )
				: false;
			$context = isset( $request['context'] ) && is_array( $request['context'] )
				? wp_unslash( $request['context'] )
				: array();
			$defaults = isset( $request['defaults'] ) && is_array( $request['defaults'] )
				? wp_unslash( $request['defaults'] )
				: array();
			$query_args = isset( $request['query_args'] ) && is_array( $request['query_args'] )
				? wp_unslash( $request['query_args'] )
				: array();

			$provider_data = $provider_key ? jet_smart_filters()->query->decode_provider_data( $provider_key ) : array();
			$provider      = ! empty( $provider_data['provider'] ) ? $provider_data['provider'] : '';
			$query_id      = ! empty( $provider_data['query_id'] ) ? $provider_data['query_id'] : 'default';
			$provider_obj  = $provider ? jet_smart_filters()->providers->get_providers( $provider ) : false;
			$filter_ids    = isset( $context['filters'] ) && is_array( $context['filters'] )
				? array_values( array_unique( array_filter( array_map( 'absint', array_filter( $context['filters'], 'is_scalar' ) ) ) ) )
				: array();

			if ( count( $filter_ids ) > 50 ) {
				wp_send_json_error( __( 'Request data is incorrect.', 'jet-smart-filters' ) );
			}

			$valid_filter_ids = jet_smart_filters()->utils->select_published_filters( $filter_ids );

			if (
				! $provider_key
				|| ! $provider
				|| ! $provider_obj
				|| ! $filter_ids
				|| count( $valid_filter_ids ) !== count( $filter_ids )
				|| ( $this->use_signature_verification && ! jet_smart_filters()->request_signature->verify_indexer_context( $defaults, $provider, $query_id, $filter_ids, $context ) )
			) {
				wp_send_json_error( __( 'Request data is incorrect.', 'jet-smart-filters' ) );
			}

			foreach ( $filter_ids as $filter_id ) {
				$indexer->data->add_indexing_data_from_filter( $provider_key, $filter_id );
			}

			$query_args = $indexer->data->prepare_indexed_query_args( $defaults, $query_args, $provider_key );

			if ( false === $query_args ) {
				wp_send_json_error( __( 'Request data is incorrect.', 'jet-smart-filters' ) );
			}

			$indexed_data = $indexer->data->get_indexed_data( $provider_key, $query_args );

			wp_send_json_success( $indexed_data );
		}

		/**
		 * Returns requested provider ID
		 */
		public function request_provider( $return = null ) {

			return jet_smart_filters()->query->get_current_provider( $return );
		}

		/**
		 * Create a request signature through the legacy render API.
		 *
		 * Kept for integrations that called the render service before signature
		 * generation was moved to Jet_Smart_Filters_Request_Signature.
		 *
		 * @deprecated 3.8.6 Use jet_smart_filters()->request_signature->create().
		 *
		 * @param array $data Data to sign.
		 *
		 * @return string
		 */
		public function create_signature( $data = array() ) {

			return jet_smart_filters()->request_signature->create( $data );
		}

		/**
		 * Apply filters form REQUEST parameters.
		 */
		public function apply_filters_from_request() {

			$request = jet_smart_filters()->data->get_request();

			if ( empty( $request['jsf'] ) ) {
				return;
			}

			$provider_name = ! empty( $request['provider'] )
				? $request['provider']
				: $request['jsf'];

			if ( ! is_string( $provider_name ) ) {
				return;
			}

			jet_smart_filters()->query->set_provider_from_request( $provider_name );

			$provider_id = $this->request_provider( 'provider' );
			$provider    = jet_smart_filters()->providers->get_providers( $provider_id );

			if ( ! $provider || ! is_callable( array( $provider, 'apply_filters_in_request' ) ) ) {
				return;
			}

			foreach ( $this->get_request_query_vars() as $query_var ) {
				if ( ! isset( $request[ $query_var ] ) || ! jet_smart_filters()->utils->is_truthy_or_zero( $request[ $query_var ] ) ) {
					continue;
				}

				jet_smart_filters()->query->set_query_var_to_request( $query_var, $request[ $query_var ] );
			}

			jet_smart_filters()->query->get_query_from_request();
			$provider->apply_filters_in_request();
		}

		/**
		 * Apply filters form url permalink.
		 */
		public function apply_filters_from_permalink( $query ) {

			$request = jet_smart_filters()->data->get_request();

			if ( empty( $query->query_vars['jsf'] ) || isset( $request['jsf'] ) ) {
				return;
			}

			if ( apply_filters( 'jet-smart-filters/render/filters-applied', false, $query ) ) {
				return;
			}

			$jsf_query_str = $query->query_vars['jsf'];

			$_REQUEST['jsf'] = strtok( $jsf_query_str, '/' );

			foreach ( $this->get_request_query_vars() as $query_var ) {
				preg_match_all( "/\/$query_var\/([^\/]*)/", $jsf_query_str, $matches );

				if ( ! isset( $matches[1][0] ) || ! jet_smart_filters()->utils->is_truthy_or_zero( $matches[1][0] ) ) {
					continue;
				}

				$_REQUEST[ $query_var ] = apply_filters(
					'jet-smart-filters/render/set-query-var',
					urldecode( $matches[1][0] ),
					$query_var,
					$this
				);
			}

			$this->apply_filters_from_request();
		}

		/**
		 * Apply filters form REQUEST parameters backward compatibility.
		 */
		public function apply_filters_from_request_backward_compatibility() {

			$jsf_request_val = jet_smart_filters()->data->get_request_var( 'jet-smart-filters' );

			if ( ! $jsf_request_val ) {
				return;
			}

			$provider_id = $this->request_provider( 'provider' );
			$provider    = jet_smart_filters()->providers->get_providers( $provider_id );

			if ( ! $provider ) {
				return;
			}

			if ( is_callable( array( $provider, 'apply_filters_in_request' ) ) ) {
				jet_smart_filters()->query->get_query_from_request();
				$provider->apply_filters_in_request();
			}
		}

		/**
		 * Apply filters in AJAX request
		 */
		public function ajax_apply_filters() {

			$request     = jet_smart_filters()->data->get_request();

			$provider_id = $this->request_provider( 'provider' );
			$query_id    = $this->request_provider( 'query_id' );
			$provider    = $provider_id ? jet_smart_filters()->providers->get_providers( $provider_id ) : false;

			if ( ! $provider || ! is_object( $provider ) ) {
				wp_send_json_error( __( 'Provider not found.', 'jet-smart-filters' ) );
			}

			if ( $this->use_signature_verification && ! jet_smart_filters()->request_signature->verify_default_query( $provider_id, $query_id ) ) {
				wp_send_json_error( __( 'Request data is incorrect.', 'jet-smart-filters' ) );
			}

			if ( ! jet_smart_filters()->filter_request_validator->verify_filter_request( $provider_id, $query_id, $request ) ) {
				wp_send_json_error( __( 'Request data is incorrect.', 'jet-smart-filters' ) );
			}

			do_action( 'jet-smart-filters/render/ajax/before', $this, $provider_id, $query_id, $provider );

			jet_smart_filters()->query->get_query_from_request();

			// When enabled, signatures ensure provider settings originated from the page.
			if ( $this->use_signature_verification && ! jet_smart_filters()->request_signature->verify_provider_settings( $provider_id, $query_id ) ) {
				wp_send_json_error( __( 'Request data is incorrect.', 'jet-smart-filters' ) );
			}

			if ( ! empty( $request['props'] ) ) {

				jet_smart_filters()->query->set_props(
					$provider_id,
					$request['props'],
					$query_id
				);
			}

			$args = array(
				'content'    => $this->render_content( $provider ),
				'pagination' => jet_smart_filters()->query->get_current_query_props()
			);

			$defaults = isset( $request['defaults'] ) && is_array( $request['defaults'] )
				? wp_unslash( $request['defaults'] )
				: array();
			$referrer = isset( $request['referrer'] ) && is_array( $request['referrer'] )
				? wp_unslash( $request['referrer'] )
				: array();
			$query_context = jet_smart_filters()->filter_request_validator->get_query_contexts(
				array(
					$provider_id => array(
						$query_id => $defaults,
					),
				),
				$referrer,
				array(
					$provider_id => array(
						$query_id => $args['pagination'],
					),
				)
			);

			if ( ! empty( $query_context ) ) {
				$args['query_context'] = $query_context;
			}

			if ( is_callable( array( $provider, 'is_data' ) ) && $provider->is_data() ) {
				$args['is_data'] = 1;
			}

			$args = apply_filters( 'jet-smart-filters/render/ajax/data', $args );

			if ( ! headers_sent() ) {
				nocache_headers();
			}

			wp_send_json( $args );
		}

		/**
		 * Render content
		 */
		public function render_content( $provider ) {

			ob_start();

			if ( is_callable( array( $provider, 'ajax_get_content' ) ) ) {
				$provider->ajax_get_content();
			} else {
				esc_html_e( 'Incorrect input data', 'jet-smart-filters' );
			}

			return ob_get_clean();
		}
	}
}
