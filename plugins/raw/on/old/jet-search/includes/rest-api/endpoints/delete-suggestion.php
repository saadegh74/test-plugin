<?php
class Jet_Search_Rest_Delete_Suggestion extends Jet_Search_Rest_Base_Route {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'delete-suggestion';
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELETE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'POST';
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$params     = $request->get_params();
		$suggestion = isset( $params['content'] ) ? $params['content'] : '';
		$suggestion = is_string( $suggestion ) ? json_decode( $suggestion, true ) : (array) $suggestion;

		$is_bulk = isset( $suggestion['ids'] ) && is_array( $suggestion['ids'] ) && ! empty( $suggestion['ids'] );
		$has_id  = isset( $suggestion['id'] ) && $suggestion['id'];

		if ( ! $is_bulk && ! $has_id ) {
			return rest_ensure_response( array(
				'success' => false,
				'data'    => esc_html__( 'Error! The suggestion could not be deleted.', 'jet-search' ),
			) );
		}

		if ( isset( $suggestion['_locale'] ) ) {
			unset( $suggestion['_locale'] );
		}

		global $wpdb;

		$table_name = 'search_suggestions';

		$ids_to_delete = array();

		if ( $is_bulk ) {
			foreach ( $suggestion['ids'] as $raw_id ) {
				$ids_to_delete[] = (int) $raw_id;
			}
		} else {
			$ids_to_delete[] = (int) $suggestion['id'];
		}

		$ids_to_delete = array_values( array_unique( array_filter( $ids_to_delete, function( $v ){ return $v > 0; } ) ) );

		if ( empty( $ids_to_delete ) ) {
			return rest_ensure_response( array(
				'success' => false,
				'data'    => esc_html__( 'Error! The suggestion could not be deleted.', 'jet-search' ),
			) );
		}

		foreach ( $ids_to_delete as $sid ) {
			$where = array( 'id' => esc_sql( $sid ) );
			jet_search()->db->delete( $table_name, $where );
		}

		$prefix       = 'jet_';
		$table_name   = $wpdb->prefix . $prefix . 'search_suggestions';
		$suggestions  = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );

		if ( $suggestions ) {
			foreach ( $suggestions as $suggestion_item ) {
				foreach ( $ids_to_delete as $deleted_id ) {
					if ( (int) $suggestion_item['id'] !== (int) $deleted_id ) {
						$this->remove_deleted_parent( $suggestion_item, $deleted_id );
					}
				}
			}
		}

		if ( $is_bulk ) {
			$success_text = sprintf(
				esc_html__( 'Success! %d suggestion(s) have been deleted', 'jet-search' ),
				count( $ids_to_delete )
			);
		} else {
			$name         = isset( $suggestion['name'] ) ? $suggestion['name'] : '#' . (int) $suggestion['id'];
			$success_text = sprintf(
				esc_html__( 'Success! Suggestion: %s has been deleted', 'jet-search' ),
				$name
			);
		}

		return rest_ensure_response( array(
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
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {

		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns arguments config
	 *
	 * @return array
	 */
	public function get_args() {
		return array();
	}

}
