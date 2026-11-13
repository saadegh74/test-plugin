<?php
/**
 * Hierarchy request handler.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Hierarchy_Request' ) ) {
	/**
	 * Handles public hierarchy AJAX requests.
	 */
	class Jet_Smart_Filters_Hierarchy_Request {

		/**
		 * Update hierarchy levels starting from depth.
		 */
		public function handle() {

			$request   = jet_smart_filters()->data->get_request();
			$validator = jet_smart_filters()->hierarchy_request_validator;

			$filter_id = isset( $request['filter_id'] ) && is_scalar( $request['filter_id'] )
				? absint( $request['filter_id'] )
				: 0;
			$args = isset( $request['args'] )
				? $validator->prepare_args( wp_unslash( $request['args'] ) )
				: $validator->prepare_args();
			$context = isset( $request['context'] ) && is_array( $request['context'] )
				? wp_unslash( $request['context'] )
				: array();

			if (
				! $filter_id
				|| false === $args
				|| ! jet_smart_filters()->utils->is_filter_published( $filter_id )
				|| 'select' !== get_post_meta( $filter_id, '_filter_type', true )
				|| ! filter_var( get_post_meta( $filter_id, '_is_hierarchical', true ), FILTER_VALIDATE_BOOLEAN )
				|| ( jet_smart_filters()->request_signature->is_enabled() && ! jet_smart_filters()->request_signature->verify_hierarchy_context( $filter_id, $args, $context ) )
			) {
				wp_send_json_error( __( 'Request data is incorrect.', 'jet-smart-filters' ) );
			}

			$hierarchy = $validator->prepare_map( $filter_id );
			$depth     = $validator->prepare_depth( $request, count( $hierarchy ) );
			$values    = $validator->prepare_values( $request, $hierarchy );

			if ( empty( $hierarchy ) || null === $depth || false === $values ) {
				wp_send_json_error( __( 'Request data is incorrect.', 'jet-smart-filters' ) );
			}

			require jet_smart_filters()->plugin_path( 'includes/hierarchy.php' );

			$hierarchy = new Jet_Smart_Filters_Hierarchy(
				$filter_id,
				$depth,
				$values,
				$args
			);

			wp_send_json_success( $hierarchy->get_levels() );
		}
	}
}
