<?php

namespace iThemesSecurity\Security_Headers\Repository;

use WP_Meta_Query;

final class X_Frame_Options_Repository {

	public const META_KEY = 'itsec_x_frame_options';
	private const CACHE_KEY_ENABLED_FOR_ANY_POST = 'itsec_x_frame_options_enabled_for_any_post';

	private const META_QUERY = [
		[
			'key'     => self::META_KEY,
			'compare' => 'EXISTS',
		],
		'relation' => 'OR',
	];

	public function get_x_frame_options( int $post_id ): string {
		return (string) get_post_meta( $post_id, self::META_KEY, true );
	}

	public function update_x_frame_options( int $post_id, string $value ): bool {
		$result = update_post_meta( $post_id, self::META_KEY, $value );

		return $result !== false;
	}

	public function delete_x_frame_options( int $post_id ): bool {
		$result = delete_post_meta( $post_id, self::META_KEY );

		return $result !== false;
	}

	public function is_enabled_for_any_post(): bool {
		$cached_result = get_transient( self::CACHE_KEY_ENABLED_FOR_ANY_POST );

		if ( in_array( $cached_result, [ 'yes', 'no' ], true ) ) {
			return $cached_result === 'yes';
		}

		add_filter( 'posts_groupby', [ $this, 'remove_group_by_clause' ], 10, 2 );
		$posts_with_custom_x_frame_options_setting = get_posts( [
			'post_status'            => 'publish', // We don't care about non-published posts.
			'posts_per_page'         => 1,
			'post_type'              => 'any',
			'meta_query'             => self::META_QUERY,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'orderby'                => 'none',
			'suppress_filters'       => false,
		] );
		remove_filter( 'posts_groupby', [ $this, 'remove_group_by_clause' ] );

		$result = count( $posts_with_custom_x_frame_options_setting ) > 0 ? 'yes' : 'no';
		set_transient( self::CACHE_KEY_ENABLED_FOR_ANY_POST, $result, DAY_IN_SECONDS );

		return $result === 'yes';
	}

	/**
	 * The original WordPress query adds GROUP BY post.ID to the query, when post-meta queries are used.
	 * It decreases performance, and we don't need it because `itsec_x_frame_options` is a singular meta.
	 *
	 * @internal This is a filter hook.
	 */
	public function remove_group_by_clause( string $groupby, \WP_Query $query ): string {
		return $query->meta_query instanceof WP_Meta_Query
		       && $query->meta_query->queries === self::META_QUERY
			? ''
			: $groupby;
	}

	public function clear_cache(): void {
		delete_transient( self::CACHE_KEY_ENABLED_FOR_ANY_POST );
	}
}
