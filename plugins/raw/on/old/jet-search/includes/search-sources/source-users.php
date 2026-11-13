<?php
namespace Jet_Search\Search_Sources;

use Jet_Engine\Modules\Profile_Builder;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Users Source Class
 *
 * @since 3.5.0
 */
class Users extends Base {

	/**
	 * Source name
	 *
	 * @var string
	 */
	protected $source_name = 'users';

	/**
	 * Returns source human-readable name
	 *
	 * @return string The label of the source.
	 */
	public function get_label() {
		return __( 'Users', 'jet-search' );
	}

	/**
	 * Indicates whether the source has a listing.
	 *
	 * @var bool
	 */
	protected $has_listing = true;

	/**
	 * Current user being processed
	 *
	 * @var WP_User
	 */
	protected $current_user = null;

	/**
	 * Returns the priority of the source.
	 *
	 * @return int The priority of the source.
	 */
	public function get_priority() {
		$priority = apply_filters( 'jet-search/ajax-search/search-source/users/priority', -1 );

		if ( ! is_int( $priority ) || 0 === $priority ) {
			return -1;
		}

		return $priority;
	}

	/**
	 * Retrieves the query result list.
	 * Sets the items list and results count based on the query.
	 */
	public function build_items_list() {
		$profile_builder = null;
		$result          = array();

		if ( class_exists( 'Jet_Engine' ) ) {
			if ( jet_engine()->modules->is_module_active( 'profile-builder' ) ) {
				$profile_builder = jet_engine()->modules->get_module( 'profile-builder' );
			}
		}

		$users = $this->get_query_result();

		if ( empty( $users ) ) {
			$this->results_count = 0;

			return;
		}

		foreach ( $users as $user ) {
			$this->current_user = $user;

			if ( isset( $profile_builder ) ) {
				add_filter( 'jet-engine/profile-builder/query/pre-get-queried-user', function( $user ) {
					return $this->current_user;
				} );

				$page_url = $profile_builder->instance->settings->get_page_url( 'single_user_page' );

				if ( false === $page_url ) {
					$page_url = get_author_posts_url( $this->current_user->ID );
				}
			} else {
				$page_url = get_author_posts_url( $this->current_user->ID );
			}

			$result[] = apply_filters( 'jet-search/ajax-search/search-source/users/search-result-list-item', array(
				'name' => $this->current_user->data->display_name,
				'url'  => $page_url
			), $user );
		}

		$result = apply_filters( 'jet-search/ajax-search/search-source/users/search-result-list', $result );

		$this->results_count = count( $result );

		$this->items_list = $result;
	}

	/**
	 * Retrieves the query result based on the search string and other parameters.
	 *
	 * @param int    $limit The number of results to return.
	 * @return mixed The query result.
	 */
	public function get_query_result( $limit = null ) {
		$limit = null != $limit ? $limit : $this->limit;

		$args = array(
			'search'         => '*' . esc_attr(  $this->search_string ) . '*',
			'search_columns' => array(
				'user_login',
				'user_nicename',
			),
			'role__not_in'   => array( 'administrator' ),
			'number'         => $limit,
		);

		$users_query = new \WP_User_Query( apply_filters( 'jet-search/ajax-search/search-source/users/query_args', $args ) );

		$results = apply_filters( 'jet-search/ajax-search/search-source/users/query-results', $users_query->results );

		return $results;
	}
}
