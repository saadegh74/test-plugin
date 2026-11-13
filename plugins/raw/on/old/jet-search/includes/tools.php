<?php
/**
 * Jet_Search_Tools class
 *
 * @package   jet-search
 * @author    Zemez
 * @license   GPL-2.0+
 */
use Jet_Engine\Bricks_Views\Elements\Listing_Grid;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Search_Tools' ) ) {

	/**
	 * Define Jet_Search_Tools class
	 */
	class Jet_Search_Tools {

		/**
		 * Get public post types options list
		 *
		 * @return array
		 */
		public static function get_post_types() {
			$post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'objects' );

			$result = array();

			if ( empty( $post_types ) ) {
				return $result;
			}

			foreach ( $post_types as $slug => $post_type ) {
				$result[ $slug ] = $post_type->label;
			}

			return apply_filters( 'jet-search/tools/get-post-types', $result );
		}

		/**
		 * Get public taxonomies options list
		 *
		 * @return array
		 */
		public static function get_taxonomies( $with_hidden = false, $include_custom_attributes = false ) {

			$args   = array();
			$result = array();

			if ( ! $with_hidden ) {
				$args['show_in_nav_menus'] = true;
			}

			$taxonomies = get_taxonomies( $args, 'objects' );

			if ( empty( $taxonomies ) ) {
				return $result;
			}

			foreach ( $taxonomies as $slug => $post_type ) {
				$result[ $slug ] = $post_type->label;
			}

			if ( $include_custom_attributes ) {
				/**
				 * Filters the list of taxonomies to allow adding custom attribute-like entries.
				 *
				 * @param array $result Taxonomies list before custom attributes.
				 *
				 * @return array Modified taxonomies list.
				 */
				$result = apply_filters( 'jet_search/extend_taxonomies_with_custom_attributes', $result );
			}

			return $result;
		}

		/**
		 * Returns image size array in slug => name format
		 *
		 * @return  array
		 */
		public static function get_image_sizes() {

			global $_wp_additional_image_sizes;

			$sizes  = get_intermediate_image_sizes();
			$result = array();

			foreach ( $sizes as $size ) {
				if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					$result[ $size ] = ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) );
				} else {
					$result[ $size ] = sprintf(
						'%1$s (%2$sx%3$s)',
						ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) ),
						$_wp_additional_image_sizes[ $size ]['width'],
						$_wp_additional_image_sizes[ $size ]['height']
					);
				}
			}

			return array_merge( array( 'full' => esc_html__( 'Full', 'jet-search' ), ), $result );
		}

		/**
		 * Return available prev arrows list
		 *
		 * @return array
		 */
		public static function get_available_prev_arrows_list() {
			return apply_filters(
				'jet-search/available-nav-arrows/prev',
				array(
					'angle'          => esc_html__( 'Angle', 'jet-search' ),
					'chevron'        => esc_html__( 'Chevron', 'jet-search' ),
					'angle-double'   => esc_html__( 'Angle Double', 'jet-search' ),
					'arrow'          => esc_html__( 'Arrow', 'jet-search' ),
					'caret'          => esc_html__( 'Caret', 'jet-search' ),
					'long-arrow'     => esc_html__( 'Long Arrow', 'jet-search' ),
					'arrow-circle'   => esc_html__( 'Arrow Circle', 'jet-search' ),
					'chevron-circle' => esc_html__( 'Chevron Circle', 'jet-search' ),
					'caret-square'   => esc_html__( 'Caret Square', 'jet-search' ),
				)
			);
		}

		public static function get_svg_arrows( $arrow_type ) {

			$arrow = [];

			switch( $arrow_type ){
				case 'angle':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.625 5.3999L16.3 7.0749L11.35 12.0249L16.3 16.9749L14.625 18.6499L7.99999 12.0249L14.625 5.3999Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.37501 18.6001L7.70001 16.9251L12.65 11.9751L7.70001 7.0251L9.37501 5.3501L16 11.9751L9.37501 18.6001Z" fill="#0F172A"/></svg>';
					break;
				case 'chevron':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.4 1.4499L18.4 3.4749L9.82502 12.0499L18.4 20.6249L16.4 22.6499L5.80002 12.0499L16.4 1.4499Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.59998 22.5501L5.59998 20.5251L14.175 11.9501L5.59998 3.3751L7.59998 1.3501L18.2 11.9501L7.59998 22.5501Z" fill="#0F172A"/></svg>';
					break;
				case 'angle-double':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.925 4.5748L6.39999 11.9998L11.925 19.4248L9.02499 19.4248L3.47499 11.9998L9.02499 4.5748L11.925 4.5748ZM19.3 4.57481L13.775 11.9998L19.3 19.4248L16.375 19.4248L10.85 11.9998L16.375 4.57481L19.3 4.57481Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.075 19.4252L17.6 12.0002L12.075 4.5752H14.975L20.525 12.0002L14.975 19.4252H12.075ZM4.70001 19.4252L10.225 12.0002L4.70001 4.5752H7.62501L13.15 12.0002L7.62501 19.4252H4.70001Z" fill="#0F172A"/></svg>';
					break;
				case 'arrow':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3.7249L13.675 5.4249L8.25 10.8249L20.275 10.8249L20.275 13.1749L8.24999 13.1749L13.675 18.5999L12 20.2749L3.725 11.9999L12 3.7249Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 20.2751L10.325 18.5751L15.75 13.1751H3.72501V10.8251H15.75L10.325 5.4001L12 3.7251L20.275 12.0001L12 20.2751Z" fill="#0F172A"/></svg>';
					break;
				case 'caret':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.425 5.9749L14.425 18.0249L8.39999 11.9999L14.425 5.9749Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.57501 18.0251V5.9751L15.6 12.0001L9.57501 18.0251Z" fill="#0F172A"/></svg>';
					break;
				case 'long-arrow':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.72499 6.62519L8.42499 8.30019L5.92499 10.8002L21.425 10.8002L21.425 13.1502L5.89999 13.1502L8.42499 15.6502L6.74999 17.3252L1.39999 11.9752L6.72499 6.62519Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.275 17.3748L15.575 15.6998L18.075 13.1998H2.57501V10.8498H18.1L15.575 8.3498L17.25 6.6748L22.6 12.0248L17.275 17.3748Z" fill="#0F172A"/></svg>';
					break;
				case 'arrow-circle':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8.35L8.35 12L12 15.65L13.05 14.6L11.2 12.75L15.75 12.75L15.75 11.25L11.2 11.25L13.05 9.4L12 8.35ZM12 2C13.4167 2 14.7333 2.25417 15.95 2.7625C17.1667 3.27083 18.225 3.975 19.125 4.875C20.025 5.775 20.7292 6.83333 21.2375 8.05C21.7458 9.26667 22 10.5833 22 12C22 13.4 21.7458 14.7083 21.2375 15.925C20.7292 17.1417 20.025 18.2 19.125 19.1C18.225 20 17.1667 20.7083 15.95 21.225C14.7333 21.7417 13.4167 22 12 22C10.6 22 9.29167 21.7417 8.075 21.225C6.85833 20.7083 5.8 20 4.9 19.1C4 18.2 3.29167 17.1417 2.775 15.925C2.25833 14.7083 2 13.4 2 12C2 10.5833 2.25833 9.26666 2.775 8.05C3.29167 6.83333 4 5.775 4.9 4.875C5.8 3.975 6.85834 3.27083 8.075 2.7625C9.29167 2.25417 10.6 2 12 2Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15.65L15.65 12L12 8.35L10.95 9.4L12.8 11.25H8.25V12.75H12.8L10.95 14.6L12 15.65ZM12 22C10.5833 22 9.26667 21.7458 8.05 21.2375C6.83333 20.7292 5.775 20.025 4.875 19.125C3.975 18.225 3.27083 17.1667 2.7625 15.95C2.25417 14.7333 2 13.4167 2 12C2 10.6 2.25417 9.29167 2.7625 8.075C3.27083 6.85833 3.975 5.8 4.875 4.9C5.775 4 6.83333 3.29167 8.05 2.775C9.26667 2.25833 10.5833 2 12 2C13.4 2 14.7083 2.25833 15.925 2.775C17.1417 3.29167 18.2 4 19.1 4.9C20 5.8 20.7083 6.85833 21.225 8.075C21.7417 9.29167 22 10.6 22 12C22 13.4167 21.7417 14.7333 21.225 15.95C20.7083 17.1667 20 18.225 19.1 19.125C18.2 20.025 17.1417 20.7292 15.925 21.2375C14.7083 21.7458 13.4 22 12 22Z" fill="#0F172A"/></svg>';
					break;
				case 'chevron-circle':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.25 11.9751L13.075 16.8251L14.625 15.2251L11.375 11.9751L14.625 8.7251L13.075 7.1251L8.25 11.9751ZM1.35 12.0001C1.35 10.4834 1.62083 9.0751 2.1625 7.7751C2.70417 6.4751 3.45 5.3501 4.4 4.4001C5.35 3.4501 6.475 2.70426 7.775 2.1626C9.075 1.62093 10.4833 1.3501 12 1.3501C13.4833 1.3501 14.8708 1.62093 16.1625 2.1626C17.4542 2.70426 18.5792 3.4501 19.5375 4.4001C20.4958 5.3501 21.25 6.4751 21.8 7.7751C22.35 9.0751 22.625 10.4834 22.625 12.0001C22.625 13.4834 22.35 14.8709 21.8 16.1626C21.25 17.4543 20.4958 18.5793 19.5375 19.5376C18.5792 20.4959 17.4542 21.2501 16.1625 21.8001C14.8708 22.3501 13.475 22.6251 11.975 22.6251C10.475 22.6251 9.075 22.3501 7.775 21.8001C6.475 21.2501 5.35 20.4959 4.4 19.5376C3.45 18.5793 2.70417 17.4543 2.1625 16.1626C1.62083 14.8709 1.35 13.4834 1.35 12.0001Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.725 12L10.9 7.15L9.35001 8.75L12.6 12L9.35001 15.25L10.9 16.85L15.725 12ZM22.625 11.975C22.625 13.4917 22.3542 14.9 21.8125 16.2C21.2708 17.5 20.525 18.625 19.575 19.575C18.625 20.525 17.5 21.2708 16.2 21.8125C14.9 22.3542 13.4917 22.625 11.975 22.625C10.4917 22.625 9.10417 22.3542 7.81251 21.8125C6.52084 21.2708 5.39584 20.525 4.43751 19.575C3.47917 18.625 2.72501 17.5 2.17501 16.2C1.62501 14.9 1.35001 13.4917 1.35001 11.975C1.35001 10.4917 1.62501 9.10417 2.17501 7.8125C2.72501 6.52083 3.47917 5.39583 4.43751 4.4375C5.39584 3.47917 6.52084 2.725 7.81251 2.175C9.10417 1.625 10.5 1.35 12 1.35C13.5 1.35 14.9 1.625 16.2 2.175C17.5 2.725 18.625 3.47917 19.575 4.4375C20.525 5.39583 21.2708 6.52083 21.8125 7.8125C22.3542 9.10417 22.625 10.4917 22.625 11.975Z" fill="#0F172A"/></svg>';
					break;
				case 'caret-square':
					$arrow['left']  = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M20 22C21.1046 22 22 21.1046 22 20L22 4C22 2.89543 21.1046 2 20 2L4 2C2.89543 2 2 2.89543 2 4L2 20C2 21.1046 2.89543 22 4 22L20 22ZM8.4 11.975L14.425 18L14.425 5.95L8.4 11.975Z" fill="#0F172A"/></svg>';
					$arrow['right'] = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4 2C2.89543 2 2 2.89543 2 4V20C2 21.1046 2.89543 22 4 22H20C21.1046 22 22 21.1046 22 20V4C22 2.89543 21.1046 2 20 2H4ZM15.6 12.025L9.575 6V18.05L15.6 12.025Z" fill="#0F172A"/></svg>';
					break;
			}

			$arrow = apply_filters( 'jet-search/nav-arrow', $arrow, $arrow_type );

			return $arrow;
		}

		/**
		 * Check if is valid timestamp
		 *
		 * @param  int|string $timestamp
		 * @return boolean
		 */
		public static function is_valid_timestamp( $timestamp ) {
			return ( ( string ) ( int ) $timestamp === $timestamp ) && ( $timestamp <= PHP_INT_MAX ) && ( $timestamp >= ~PHP_INT_MAX );
		}

		public static function allowed_meta_callbacks() {
			$callbacks = apply_filters( 'jet-search/ajax-search/meta_callbacks', array(
				''                        => esc_html__( 'Clean', 'jet-search' ),
				'date'                    => esc_html__( 'Format date', 'jet-search' ),
				'date_i18n'               => esc_html__( 'Format date (localized)', 'jet-search' ),
				'get_the_title'           => 'get_the_title',
				'wp_get_attachment_url'   => 'wp_get_attachment_url',
				'wp_get_attachment_image' => 'wp_get_attachment_image',
			) );

			return $callbacks;
		}

		/**
		 * Sign the meta fields deliberately exposed by a rendered search widget.
		 * The token is public and cacheable; its signature prevents changing field keys.
		 *
		 * @param array $settings Server-side widget settings.
		 * @return string
		 */
		public static function sign_public_meta_settings( $settings ) {
			// An unpublished preview must not become a signing oracle for contributors.
			if ( ( is_admin() || is_preview() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) )
				&& ! current_user_can( 'publish_posts' )
			) {
				return '';
			}

			$meta_settings = array();

			foreach ( array( 'title_related', 'content_related' ) as $position ) {
				$show = isset( $settings[ 'show_' . $position . '_meta' ] ) ? $settings[ 'show_' . $position . '_meta' ] : '';

				if ( ! in_array( $show, array( 'yes', 'true', true ), true ) ) {
					continue;
				}

				$meta_settings[ 'show_' . $position . '_meta' ]     = 'yes';
				$meta_settings[ 'meta_' . $position . '_position' ] = $settings[ 'meta_' . $position . '_position' ] ?? '';
				$meta_settings[ $position . '_meta' ]              = $settings[ $position . '_meta' ] ?? array();
			}

			if ( 'custom-field' === ( $settings['post_content_source'] ?? '' ) ) {
				$meta_settings['post_content_custom_field_key'] = $settings['post_content_custom_field_key'] ?? '';
			}

			if ( empty( $meta_settings ) ) {
				return '';
			}

			$meta_settings['search_source'] = $settings['search_source'] ?? 'any';
			$payload                       = base64_encode( wp_json_encode( $meta_settings ) );
			$signature                     = hash_hmac( 'sha256', 'jet-search-meta:' . get_current_blog_id() . ':' . $payload, wp_salt( 'auth' ) );

			return $payload . '.' . $signature;
		}

		/**
		 * Restore authorized meta settings, ignoring all unsigned field selections.
		 *
		 * @param array $settings Public search request settings.
		 * @return array
		 */
		public static function prepare_public_meta_settings( $settings ) {
			$defaults = array(
				'show_title_related_meta'        => '',
				'meta_title_related_position'    => '',
				'title_related_meta'             => array(),
				'show_content_related_meta'      => '',
				'meta_content_related_position' => '',
				'content_related_meta'           => array(),
				'post_content_custom_field_key'  => '',
			);
			$settings = array_merge( is_array( $settings ) ? $settings : array(), $defaults );
			$token    = $settings['meta_settings'] ?? '';

			if ( ! is_string( $token ) ) {
				return $settings;
			}

			$parts = explode( '.', $token );

			if ( 2 !== count( $parts ) ) {
				return $settings;
			}

			$signature = hash_hmac( 'sha256', 'jet-search-meta:' . get_current_blog_id() . ':' . $parts[0], wp_salt( 'auth' ) );

			if ( ! hash_equals( $signature, $parts[1] ) ) {
				return $settings;
			}

			$meta_settings = json_decode( base64_decode( $parts[0], true ), true );

			if ( is_array( $meta_settings ) ) {
				$settings = array_merge( $settings, $meta_settings );
			}

			return $settings;
		}

		public static function get_allowed_html_tags() {
			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array()
				),
				'p'      => array(
					'style' => array()
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
			);
			return apply_filters( 'jet-search/ajax-search/notifications/allowed_html_tags', $allowed_tags );
		}

		public static function get_placeholder_image_src() {
			$placeholder = trailingslashit( plugin_dir_url( 'jet-search' ) ) . 'jet-search/assets/images/placeholder.png';
			return $placeholder;
		}

		public static function prepared_default_search_query_settings( $settings, $widget_current_query = '' ) {

			$settings = self::prepared_settings($settings);

			if ( '' === $settings['show_search_category_list'] ) {
				unset( $settings['search_taxonomy'] );
				unset( $settings['show_search_category_list'] );
			}

			unset( $settings['show_search_category_list'] );

			if ( true === $settings['current_query'] || 'yes' === $widget_current_query || true === $widget_current_query ) {
				unset( $settings['search_source'] );
				unset( $settings['include_terms_ids'] );
				unset( $settings['exclude_terms_ids'] );
				unset( $settings['exclude_posts_ids'] );
			}

			if ( empty( $settings['search_source'] ) ) {
				$settings['search_source'] = 'any';
			}

			return apply_filters( 'jet-search/tools/prepared-default-settings', $settings );
		}

		public static function prepared_settings( $settings ) {

			if ( isset( $settings['show_search_category_list'] ) ) {
				$settings['show_search_category_list'] = 'false' === $settings['show_search_category_list'] || '' === $settings['show_search_category_list'] || false === $settings['show_search_category_list'] ? false : true;
			}

			if ( isset( $settings['current_query'] ) ) {
				$settings['current_query'] = 'false' === $settings['current_query'] || '' === $settings['current_query'] || false === $settings['current_query'] ? false : true;
			}

			if ( isset( $settings['sentence'] ) ) {
				$settings['sentence'] = 'false' === $settings['sentence'] || '' === $settings['sentence'] || false === $settings['sentence'] ? false : true;
			}

			if ( isset( $settings['search_in_taxonomy'] ) ) {
				$settings['search_in_taxonomy'] = 'false' === $settings['search_in_taxonomy'] || '' === $settings['search_in_taxonomy'] || false === $settings['search_in_taxonomy'] ? false : true;

			}

			if ( isset( $settings['catalog_visibility'] ) ) {
				$settings['catalog_visibility'] = 'false' === $settings['catalog_visibility'] || '' === $settings['catalog_visibility'] || false === $settings['catalog_visibility'] ? false : true;
			}

			return $settings;
		}

		public static function prepared_settings_for_elementor( $settings ) {

			$settings['show_search_category_list'] = 'false' === $settings['show_search_category_list'] ? '' : 'yes';
			$settings['current_query']             = 'false' === $settings['current_query'] ? '' : 'yes';
			$settings['sentence']                  = 'false' === $settings['sentence'] ? '' : 'yes';
			$settings['search_in_taxonomy']        = 'false' === $settings['search_in_taxonomy'] ? '' : 'yes';
			$settings['catalog_visibility']        = 'false' === $settings['catalog_visibility'] ? '' : 'yes';

			return $settings;
		}

		public static function prepared_settings_for_bricks( $settings ) {
			$settings['show_search_category_list'] = 'false' === $settings['show_search_category_list'] ? false : true;
			$settings['current_query']             = 'false' === $settings['current_query'] ? false : true;
			$settings['sentence']                  = 'false' === $settings['sentence'] ? false : true;
			$settings['search_in_taxonomy']        = 'false' === $settings['search_in_taxonomy'] ? false : true;
			$settings['catalog_visibility']        = 'false' === $settings['catalog_visibility'] ? false : true;

			return $settings;
		}

		public static function prepared_settings_for_blocks( $settings ) {
			$settings['show_search_category_list'] = 'false' === $settings['show_search_category_list']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			$settings['search_taxonomy'] = isset( $settings['search_taxonomy'] )
				? array(
					'type'    => 'string',
					'default' => $settings['search_taxonomy'],
				)
				: array(
					'type'    => 'string',
					'default' => 'category',
				);

			$settings['current_query'] = 'false' === $settings['current_query']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			$settings['search_results_url'] = isset( $settings['search_results_url'] )
				? array(
					'type'    => 'string',
					'default' => $settings['search_results_url'],
				)
				: array(
					'type'    => 'string',
					'default' => '',
				);

			$settings['search_results_target_widget_id'] = isset( $settings['search_results_target_widget_id'] )
				? array(
					'type'    => 'string',
					'default' => $settings['search_results_target_widget_id'],
				)
				: array(
					'type'    => 'string',
					'default' => '',
				);

			if ( isset( $settings['search_source'] ) ) {
				self::set_default_settings_list( 'search_source', 'search_source_list', 'post_types', $settings );
			}

			if ( isset( $settings['include_terms_ids'] ) ) {
				self::set_default_settings_list( 'include_terms_ids', 'include_terms_ids_list', 'terms', $settings );
			}

			if ( isset( $settings['exclude_terms_ids'] ) ) {
				self::set_default_settings_list( 'exclude_terms_ids', 'exclude_terms_ids_list', 'terms', $settings );
			}

			if ( isset( $settings['exclude_posts_ids'] ) ) {
				self::set_default_settings_list( 'exclude_posts_ids', 'exclude_posts_ids_list', 'posts', $settings );
			}

			$settings['custom_fields_source'] = isset( $settings['custom_fields_source'] )
				? array(
					'type'    => 'string',
					'default' => $settings['custom_fields_source'],
				)
				: array(
					'type'    => 'string',
					'default' => '',
				);

			$settings['sentence'] = 'false' === $settings['sentence']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			$settings['search_in_taxonomy'] = 'false' === $settings['search_in_taxonomy']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			if ( isset( $settings['search_in_taxonomy_source'] ) ) {
				self::set_default_settings_list( 'search_in_taxonomy_source', 'search_in_taxonomy_source_list', 'taxonomies', $settings );
			}

			$settings['results_order_by'] = isset( $settings['results_order_by'] )
				? array(
					'type'    => 'string',
					'default' => $settings['results_order_by'],
				)
				: array(
					'type'    => 'string',
					'default' => '',
				);

			$settings['results_order'] = isset( $settings['results_order'] )
				? array(
					'type'    => 'string',
					'default' => $settings['results_order'],
				)
				: array(
					'type'    => 'string',
					'default' => 'asc',
				);

			$settings['catalog_visibility'] = 'false' === $settings['catalog_visibility']
				? array(
					'type'    => 'boolean',
					'default' => false,
				)
				: array(
					'type'    => 'boolean',
					'default' => true,
				);

			return $settings;
		}

		public static function set_default_settings_list( $setting, $setting_list, $list_type, &$settings ) {

			$list = array();

			switch ( $list_type ) {
				case 'post_types':
					$list = jet_search_blocks_integration()->get_post_types_list();
					break;
				case 'taxonomies':
					$list = jet_search_blocks_integration()->get_taxonomies_list( true );
					break;
				case 'terms':
					$ids = $settings[$setting];

					if ( ! empty( $ids ) ) {
						$terms_args = array(
							'hide_empty' => false,
						);
						$terms_args['include'] = $ids;

						$terms = get_terms( $terms_args );

						global $wp_taxonomies;

						foreach ( $terms as $term ) {
							if ( isset( $wp_taxonomies[ $term->taxonomy ] ) ) {
								$list[] = array(
									'value' => $term->term_id,
									'label' => sprintf( '%1$s: %2$s', $wp_taxonomies[ $term->taxonomy ]->label, $term->name ),
								);
							}
						}
					}
					break;
				case 'posts':
					$ids = $settings[$setting];

					if ( ! empty( $ids ) ) {
						$args = array(
							'post_type'           => 'any',
							'posts_per_page'      => - 1,
							'suppress_filters'    => false,
							'ignore_sticky_posts' => true,
							'post__in'            => $ids
						);

						$posts = get_posts( $args );

						foreach ( $posts as $post ) {
							$list[] = array(
								'value' => $post->ID,
								'label' => sprintf( '%1$s: %2$s', ucfirst( $post->post_type ), $post->post_title ),
							);
						}
					}

					break;
			}

			if ( empty( $list ) ) {
				$settings[$setting] = array(
					'type'    => 'array',
					'default' => array(),
				);

				return $settings;
			}

			$result = array();

			if ( ! empty( $settings[$setting] ) ) {
				foreach ( $list as $list_item ) {
					if ( in_array( $list_item["value"], $settings[$setting] ) ) {
						$result[] = array( "value" => $list_item["value"], "label" => $list_item["label"] );
					}
				}
			}

			$settings[$setting] = ! empty( $settings[$setting] )
				? array(
					'type'    => 'array',
					'default' => $settings[$setting],
				)
				: array(
					'type'    => 'array',
					'default' => array(),
				);

			$settings[$setting_list] = ! empty( $result )
				? array(
					'type'    => 'array',
					'default' => $result,
				)
				: array(
					'type'    => 'array',
					'default' => array(),
				);

			return $settings;
		}

		public static function get_listing_grid( $listing_id ) {
			$listing_html = '';

			if ( empty( $listing_id  ) ) {
				return $listing_html;
			}

			$is_bricks_listing    = false;
			$is_blocks_listing    = false;
			$is_elementor_listing = false;

			if ( '' != $listing_id && class_exists( 'Jet_Engine' ) ) {
				if ( defined( 'BRICKS_VERSION' ) ) {
					wp_enqueue_style( 'bricks-photoswipe' );
					wp_enqueue_script( 'bricks-photoswipe' );
					wp_enqueue_script( 'bricks-photoswipe-lightbox' );
				}

				wp_enqueue_script(
					'jquery-slick',
					jet_engine()->plugin_url( 'assets/lib/slick/slick.min.js' ),
					array( 'jquery' ),
					'1.8.1',
					true
				);

				$is_bricks_listing    = jet_engine()->listings->data->get_listing_type( $listing_id ) === 'bricks';
				$is_blocks_listing    = jet_engine()->listings->data->get_listing_type( $listing_id ) === 'blocks';
				$is_elementor_listing = jet_engine()->listings->data->get_listing_type( $listing_id ) === 'elementor';

				if (  $is_bricks_listing ) {
					$listing_grid = new Listing_Grid;

					$listing_grid->settings['lisitng_id'] = $listing_id;
					$listing_grid->settings['posts_num']  = 1;

					$listing_grid->render();
				}

				if ( $is_blocks_listing ) {
					$blocks_listing_grid_instance = jet_engine()->blocks_views->block_types->get_block_type_instance( 'listing-grid' );
					$listing_html                 = $blocks_listing_grid_instance->render_callback( [ 'lisitng_id' => $listing_id, '_block_id' => '', 'posts_num' => 1 ] );
				}

				if (  $is_elementor_listing ) {
					$initial_object = jet_engine()->listings->data->get_current_object();

					$listing_html = jet_engine()->frontend->get_listing_item_content( $listing_id );

					jet_engine()->frontend->reset_data();
					jet_engine()->listings->data->set_current_object( $initial_object );
				}
			}

			return $listing_html;
		}

		/**
		 * Retrieves and displays the source listing grid.
		 *
		 * This method generates HTML for the source listing grid based on the provided listing ID and source.
		 *
		 * @param int $listing_id The ID of the listing.
		 * @param object $source The source object that provides methods to set arguments, search string, limit, and render.
		 * @return string The generated HTML for the source listing grid.
		 */
		public static function get_source_listing_grid( $listing_id, $source ) {
			$listing_html = '';

			if ( empty( $listing_id  )  ) {
				return $listing_html;
			}

			if ( filter_var( $settings['search_source_' . $key], FILTER_VALIDATE_BOOLEAN ) ) {
				$source->set_args( $settings );
				$source->set_search_string( '' );
				$source->set_limit( 1 );

				$render = $source->render();

				if ( ! empty( $render ) ) {
					echo $render[0]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}

		/**
		 * Returns SVG attachment content without executing the uploaded file.
		 *
		 * Uploaded files must always be treated as data, never as PHP source.
		 *
		 * @param int $attachment_id Attachment ID.
		 * @return string SVG content or an empty string when it cannot be read.
		 */
		public static function get_svg_attachment_content( $attachment_id ) {
			$attachment_id = absint( $attachment_id );

			if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
				return '';
			}

			$file = get_attached_file( $attachment_id );

			if ( ! $file || ! is_readable( $file ) ) {
				return '';
			}

			$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			return false === $content ? '' : self::sanitize_icon_html( $content );
		}

		/**
		 * Allow static SVG and font icons without scripts, events or foreign content.
		 *
		 * @param string $html Icon markup.
		 * @return string
		 */
		public static function sanitize_icon_html( $html ) {
			$allowed    = array_intersect_key( wp_kses_allowed_html( 'post' ), array_flip( array( 'div', 'span', 'i', 'img' ) ) );
			$attributes = array_fill_keys( array(
				'id', 'class', 'role', 'aria-hidden', 'aria-label', 'aria-labelledby', 'aria-describedby',
				'width', 'height', 'viewbox', 'preserveaspectratio', 'focusable', 'style',
				'version', 'shape-rendering', 'text-rendering', 'image-rendering', 'enable-background',
				'xml:space',
				'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'fx', 'fy', 'fr', 'd', 'points',
				'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-linecap',
				'stroke-linejoin', 'stroke-miterlimit', 'stroke-dasharray', 'stroke-dashoffset',
				'stroke-opacity', 'opacity', 'transform', 'clip-path', 'clip-rule', 'mask',
				'clippathunits', 'maskunits', 'maskcontentunits', 'gradientunits', 'gradienttransform',
				'spreadmethod', 'offset', 'stop-color', 'stop-opacity', 'vector-effect',
				'font-family', 'font-size', 'font-weight', 'text-anchor', 'dominant-baseline', 'dx', 'dy',
			), true );
			$attributes['xmlns'] = array( 'values' => array( 'http://www.w3.org/2000/svg' ) );
			$attributes['xmlns:xlink'] = array( 'values' => array( 'http://www.w3.org/1999/xlink' ) );
			$attributes['href'] = array(
				'value_callback' => static function ( $value ) {
					return 1 === preg_match( '/^#[a-zA-Z0-9_.:-]+$/', $value );
				},
			);
			$attributes['xlink:href'] = $attributes['href'];

			foreach ( array( 'svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon',
				'defs', 'symbol', 'use', 'clippath', 'mask', 'lineargradient', 'radialgradient', 'stop',
				'title', 'desc', 'text', 'tspan' ) as $tag
			) {
				$allowed[ $tag ] = $attributes;
			}

			return wp_kses( $html, $allowed );
		}

		/**
		 * Render new elementor icons
		 *
		 * @return string The rendered icon HTML.
		 */
		public static function render_icon( $icon = null, $icon_class = '', $custom_atts = array() ) {

			$custom_atts_string = '';

			if ( ! empty( $custom_atts ) ) {
				foreach ( $custom_atts as $key => $value ) {
					$custom_atts_string .= sprintf( ' %1$s="%2$s"', esc_attr( $key ), esc_attr( $value ) );
				}
			}

			if ( ! is_array( $icon ) && is_numeric( $icon ) ) {

				ob_start();

				echo '<div class="' . esc_attr( $icon_class ) . ' is-svg-icon"' . $custom_atts_string . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				$mime = get_post_mime_type( $icon );

				if ( 'image/svg+xml' === $mime ) {
					echo self::get_svg_attachment_content( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				} else {
					echo wp_get_attachment_image( $icon, 'full' );
				}

				echo '</div>';

				return self::sanitize_icon_html( ob_get_clean() );

			}
			// Render Bricks svg icon
			elseif ( ! is_array( $icon ) && false !== str_contains( $icon, '<svg' ) ) {

				ob_start();

				echo '<div class="' . esc_attr( $icon_class ) . ' is-svg-icon"' . $custom_atts_string . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';

				return self::sanitize_icon_html( ob_get_clean() );

			}
			// Render Bricks font icon
			elseif ( ! is_array( $icon ) && false !== str_contains( $icon, '<i' ) ) {

				ob_start();

				echo '<div class="' . esc_attr( $icon_class ) . '">';
				echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';

				return self::sanitize_icon_html( ob_get_clean() );
			}
			// Bricks font icon with array value
			elseif ( is_array( $icon ) && isset( $icon['library'] ) && isset( $icon['icon'] ) ) {
				return self::sanitize_icon_html( sprintf( '<div class="%1$s"><i class="%2$s"></i></div>', esc_attr( $icon_class ), esc_attr( $icon['icon'] ) ) );
			}

			// Pre-process Gutenberg icon
			if ( ! empty( $icon ) ) {

				$icon_src = null;

				if ( is_array( $icon ) && ! empty( $icon['src'] ) ) {
					$icon_src = $icon['src'];

					return self::sanitize_icon_html( sprintf( '<div class="%1$s is-svg-icon"%2$s>%3$s</div>', esc_attr( $icon_class ), $custom_atts_string, $icon_src ) );
				}
			}

			$is_new = class_exists( 'Elementor\Icons_Manager' ) && Elementor\Icons_Manager::is_migration_allowed();

			if ( $is_new ) {
				ob_start();

				$custom_atts['class']       = $icon_class;
				$custom_atts['aria-hidden'] = 'true';

				Elementor\Icons_Manager::render_icon( $icon, $custom_atts );

				$html = ob_get_clean();

				$is_svg_library = 'svg' === $icon['library'];
				$is_svg_inline  = false !== strpos( $html, 'e-font-icon-svg' );

				if ( $is_svg_library || $is_svg_inline ) {

					if ( $is_svg_inline ) {
						$html = str_replace( $icon_class . ' ', '', $html );
					}

					$html = sprintf( '<div class="%1$s is-svg-icon"%2$s>%3$s</div>', esc_attr( $icon_class ), $custom_atts_string, $html );
				}

				return self::sanitize_icon_html( $html );

			} else {
				return false;
			}

		}

		public static function custom_fields_post_type_update( $custom_fields_source, $query_post_type ) {

			$cf_keys = ! is_array( $custom_fields_source ) ? explode( ',', str_replace( ' ', '', $custom_fields_source ) ) : $custom_fields_source;

			if ( ( $key = array_search( '_sku', $cf_keys ) ) !== false ) {
				$show_product_variation = apply_filters( 'jet-search/ajax-search/show-product-variations', true );

				if ( $show_product_variation ) {
					if ( function_exists( 'WC' ) ) {
						if ( 'any' === $query_post_type ) {
							$post_types = get_post_types( array( 'exclude_from_search' => false ), 'names' );
							$post_types = array_values( $post_types );

							$is_product_source = array_search( 'product', $post_types );

							if ( false !== $is_product_source ) {
								$query_post_type = array_merge( $post_types, array( 'product_variation' ) );
							}
						} else {
							$query_post_type = is_array( $query_post_type ) ? $query_post_type : (array) $query_post_type;
							$is_product_source = array_search( 'product', $query_post_type );

							if ( false !== $is_product_source ) {
								$query_post_type = array_merge( $query_post_type, array( 'product_variation' ) );
							}
						}
					}
				}
			} else {
				if ( 'any' === $query_post_type ) {
					$post_types      = get_post_types( array( 'exclude_from_search' => false ), 'names' );
					$query_post_type = array_values( $post_types );
				}
			}

			if ( ! is_array( $query_post_type ) ) {
				$query_post_type = explode( ',', str_replace( ' ', '', $query_post_type ) );
			}

			return $query_post_type;
		}

		public static function maybe_add_enqueue_assets_data( &$response ) {
			if ( isset( $_REQUEST['isEditMode'] ) && filter_var( $_REQUEST['isEditMode'], FILTER_VALIDATE_BOOLEAN ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				return;
			}

			// Ensure registered `jet-plugins` script.
			if ( ! wp_script_is( 'jet-plugins', 'registered' )  ) {
				jet_search()->register_jet_plugins_js();
			}

			wp_scripts()->done[] = 'jquery';
			wp_scripts()->done[] = 'jet-plugins';
			wp_scripts()->done[] = 'jet-engine-frontend';

			$scripts = wp_scripts()->queue;
			$styles  = wp_styles()->queue;

			// AJAX requests skip the normal block assets enqueue hook. Include core styles only for rendered block listings.
			if ( did_filter( 'jet-engine/listing/content/blocks' ) ) {
				$styles[] = 'wp-block-library';
			}

			if ( ! empty( $scripts ) ) {
				$response['scripts'] = array();

				foreach ( (array) $scripts as $script ) {

					ob_start();
					wp_scripts()->do_items( $script );
					$script_html = ob_get_clean();

					$response['scripts'][ $script ] = $script_html;
				}
			}

			if ( ! empty( $styles ) ) {
				$response['styles'] = array();

				foreach ( (array) $styles as $style ) {

					ob_start();
					wp_styles()->do_items( $style );
					$style_html = ob_get_clean();

					$response['styles'][ $style ] = $style_html;
				}
			}

		}

		public static function maybe_enqueue_elementor_listing_assets( $listing_id ) {
			if ( empty( $listing_id ) || ! class_exists( '\Elementor\Plugin' ) ) {
				return;
			}

			$document = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( $listing_id );

			if ( ! $document || ! $document->is_built_with_elementor() ) {
				return;
			}

			$elements_data = $document->get_elements_data();
			$widget_types  = self::get_elementor_widget_types( $elements_data );

			self::register_elementor_listing_widget_assets( $widget_types );
			self::enable_elementor_listing_page_assets( $listing_id, $document );
			self::enqueue_elementor_listing_widget_assets( $elements_data );
		}

		protected static function get_elementor_widget_types( $elements_data ) {
			$widget_types = array();

			foreach ( (array) $elements_data as $element ) {
				if ( empty( $element ) || ! is_array( $element ) ) {
					continue;
				}

				if ( ! empty( $element['elType'] ) && 'widget' === $element['elType'] && ! empty( $element['widgetType'] ) ) {
					$widget_types[] = $element['widgetType'];
				}

				if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
					$widget_types = array_merge( $widget_types, self::get_elementor_widget_types( $element['elements'] ) );
				}
			}

			return array_values( array_unique( $widget_types ) );
		}

		protected static function register_elementor_listing_widget_assets( $widget_types ) {
			if ( empty( $widget_types ) ) {
				return;
			}

			$has_jet_woo_widgets = false;

			foreach ( $widget_types as $widget_type ) {
				if ( 0 === strpos( $widget_type, 'jet-woo-builder-' ) ) {
					$has_jet_woo_widgets = true;
					break;
				}
			}

			if ( $has_jet_woo_widgets && function_exists( 'jet_woo_builder_assets' ) ) {
				self::register_jet_woo_builder_compat_assets();
				jet_woo_builder_assets()->enqueue_styles();
				jet_woo_builder_assets()->enqueue_scripts();
			}

			do_action( 'jet-search/elementor-listing/register-widget-assets', $widget_types );
		}

		protected static function register_jet_woo_builder_compat_assets() {
			if ( class_exists( 'WC_Frontend_Scripts' ) ) {
				WC_Frontend_Scripts::load_scripts();
			}

			if ( function_exists( 'jet_woo_product_gallery_assets' ) ) {
				jet_woo_product_gallery_assets()->enqueue_missed_assets();
				jet_woo_product_gallery_assets()->enqueue_styles();
			}
		}

		protected static function enable_elementor_listing_page_assets( $listing_id, $document ) {
			if ( ! class_exists( '\Elementor\Core\Base\Elements_Iteration_Actions\Assets' ) ) {
				return;
			}

			$meta_key    = \Elementor\Core\Base\Elements_Iteration_Actions\Assets::ASSETS_META_KEY;
			$page_assets = get_post_meta( $listing_id, $meta_key, true );

			if ( empty( $page_assets ) && is_object( $document ) && method_exists( $document, 'update_runtime_elements' ) ) {
				$document->update_runtime_elements();
				$page_assets = get_post_meta( $listing_id, $meta_key, true );
			}

			if ( empty( $page_assets ) || empty( \Elementor\Plugin::instance()->assets_loader ) ) {
				return;
			}

			\Elementor\Plugin::instance()->assets_loader->enable_assets( $page_assets );
		}

		protected static function enqueue_elementor_listing_widget_assets( $elements_data ) {
			foreach ( (array) $elements_data as $element_data ) {
				if ( empty( $element_data ) || ! is_array( $element_data ) ) {
					continue;
				}

				if ( ! empty( $element_data['elType'] ) && 'widget' === $element_data['elType'] ) {
					$element = \Elementor\Plugin::instance()->elements_manager->create_element_instance( $element_data );

					if ( $element ) {
						$element->enqueue_scripts();
						$element->enqueue_styles();
					}
				}

				if ( ! empty( $element_data['elements'] ) && is_array( $element_data['elements'] ) ) {
					self::enqueue_elementor_listing_widget_assets( $element_data['elements'] );
				}
			}
		}

		public static function merge_arrays_unique_by_id( array $array1, array $array2 ) {
			if ( empty( $array1 ) ) {
				return $array2;
			}

			if ( empty( $array2 ) ) {
				return $array1;
			}

			$result = [];

			foreach ( $array1 as $item ) {
				$result[$item['id']] = $item;
			}

			foreach ( $array2 as $item) {
				if ( ! isset( $result[$item['id']] ) ) {
					$result[$item['id']] = $item;
				}
			}

			return array_values($result);
		}

		public static function get_product_variation_ids( $product_id ) {

			if ( ! class_exists( 'WooCommerce' ) ) {
				return array();
			}

			$product = wc_get_product( $product_id );

			if ( $product && $product->is_type( 'variable' ) ) {
				return $product->get_children();
			}

			return array();
		}

		/**
		 * Sanitize and allowlist the `current_query` array before merging into WP_Query args.
		 *
		 * Only a safe subset of WP_Query parameters that represent page-context filters
		 * (taxonomy, category, date, etc.) are permitted. Parameters that map directly
		 * to SQL fragments or that the plugin controls independently (author, meta_query,
		 * post__not_in, s, posts_per_page, …) are stripped out unconditionally.
		 *
		 * @param  array $raw Unsanitized user-supplied current_query array.
		 * @return array      Sanitized array ready for array_merge into search_query.
		 */
		public static function sanitize_current_query( array $raw ) {

			/**
			 * Filters the allowlist of top-level WP_Query keys permitted inside
			 * `current_query`. Use this hook to extend the list for custom use-cases
			 * while keeping default safety intact.
			 *
			 * @param array $allowed_keys Indexed list of allowed key names.
			 */
			$allowed_keys = apply_filters( 'jet-search/ajax-search/current-query-allowlist', array(
				'post_type',
				'post_status',
				'cat',
				'category_name',
				'category__in',
				'category__not_in',
				'category__and',
				'tag',
				'tag_id',
				'tag__in',
				'tag__not_in',
				'tag__and',
				'tag_slug__in',
				'tag_slug__and',
				'tax_query',
				'taxonomy',
				'term',
				'term_id',
				'year',
				'monthnum',
				'day',
				'm',
				'w',
				'order',
				'orderby',
				'lang',
			) );

			$allowed_post_statuses = array( 'publish' );
			$allowed_order_values  = array( 'ASC', 'DESC' );

			$sanitized = array();

			foreach ( $raw as $key => $value ) {

				if ( ! in_array( $key, $allowed_keys, true ) ) {
					continue;
				}

				switch ( $key ) {

					// Single integer IDs.
					case 'cat':
					case 'tag_id':
					case 'term_id':
					case 'year':
					case 'monthnum':
					case 'day':
					case 'm':
					case 'w':
						$sanitized[ $key ] = intval( $value );
						break;

					// Arrays of integer IDs.
					case 'category__in':
					case 'category__not_in':
					case 'category__and':
					case 'tag__in':
					case 'tag__not_in':
					case 'tag__and':
						$sanitized[ $key ] = array_map( 'intval', (array) $value );
						break;

					// Arrays of text slugs.
					case 'tag_slug__in':
					case 'tag_slug__and':
						$sanitized[ $key ] = array_map( 'sanitize_text_field', (array) $value );
						break;

					// post_type: scalar or array of strings.
					case 'post_type':
						$public_post_type = self::sanitize_public_search_post_type_arg( $value );

						if ( false === $public_post_type ) {
							break;
						}

						if ( is_array( $value ) ) {
							$sanitized[ $key ] = $public_post_type;
						} else {
							$sanitized[ $key ] = $public_post_type;
						}
						break;

					// post_status: scalar or array, values restricted to allowed list.
					case 'post_status':
						if ( is_array( $value ) ) {
							$filtered = array_intersect( $value, $allowed_post_statuses );
							if ( ! empty( $filtered ) ) {
								$sanitized[ $key ] = array_values( $filtered );
							}
						} elseif ( in_array( $value, $allowed_post_statuses, true ) ) {
							$sanitized[ $key ] = $value;
						}
						break;

					// Strict enum: ASC or DESC only.
					case 'order':
						$upper = strtoupper( (string) $value );
						if ( in_array( $upper, $allowed_order_values, true ) ) {
							$sanitized[ $key ] = $upper;
						}
						break;

					// Structural validation for tax_query.
					case 'tax_query':
						$clean_tax_query = self::sanitize_tax_query( $value );
						if ( ! empty( $clean_tax_query ) ) {
							$sanitized[ $key ] = $clean_tax_query;
						}
						break;

					// Plain text fields.
					default:
						$sanitized[ $key ] = sanitize_text_field( (string) $value );
						break;
				}
			}

			return $sanitized;
		}

		/**
		 * Merge page-context query args into public Ajax Search args.
		 *
		 * @param array $query_args    Base WP_Query args controlled by JetSearch.
		 * @param array $current_query User-controlled current query args.
		 * @return array
		 */
		public static function merge_public_current_query_args( array $query_args, array $current_query ) {

			$sanitized_current_query = self::sanitize_current_query( $current_query );

			if ( ! empty( $sanitized_current_query ) ) {
				$query_args = array_merge( $query_args, $sanitized_current_query );
			}

			return self::prepare_public_search_query_args( $query_args );
		}

		/**
		 * Enforce the public Ajax Search visibility policy on final WP_Query args.
		 *
		 * Public search responses must not be able to broaden the plugin-controlled
		 * published-post constraint through current_query or query-args filters.
		 *
		 * @param array $query_args WP_Query args.
		 * @return array
		 */
		public static function prepare_public_search_query_args( array $query_args ) {

			$query_args['post_status'] = 'publish';
			unset( $query_args['perm'] );

			return $query_args;
		}

		/**
		 * Remove non-public post objects from public Ajax Search results.
		 *
		 * This is a defense-in-depth boundary for plugins that mutate WP_Query results
		 * after the SQL query has already run.
		 *
		 * @param WP_Query $query Search query instance.
		 * @return WP_Query
		 */
		public static function filter_public_search_query_results( $query ) {

			if ( ! $query instanceof WP_Query ) {
				return $query;
			}

			$query->posts         = self::filter_public_search_result_posts( $query->posts );
			$query->post_count    = count( $query->posts );
			$query->found_posts   = $query->post_count;

			$posts_per_page = ! empty( $query->query_vars['posts_per_page'] ) ? (int) $query->query_vars['posts_per_page'] : 0;
			$query->max_num_pages = 0 < $posts_per_page
				? (int) ceil( $query->found_posts / $posts_per_page )
				: 0;

			return $query;
		}

		/**
		 * Filter a list of post objects to public Ajax Search results only.
		 *
		 * @param array $posts List of WP_Post objects.
		 * @return array
		 */
		public static function filter_public_search_result_posts( $posts ) {

			$posts = array_filter(
				(array) $posts,
				array( __CLASS__, 'is_public_search_result_post' )
			);

			return array_values( $posts );
		}

		/**
		 * Check whether a post object can be serialized by public Ajax Search.
		 *
		 * @param mixed $post Post object or ID.
		 * @return bool
		 */
		public static function is_public_search_result_post( $post ) {

			$post = get_post( $post );

			if ( ! $post ) {
				return false;
			}

			$status = get_post_status_object( $post->post_status );

			if ( ! $status || empty( $status->public ) ) {
				return false;
			}

			$post_type = get_post_type_object( $post->post_type );

			if ( ! $post_type || ! is_post_type_viewable( $post_type ) ) {
				return false;
			}

			return (bool) apply_filters( 'jet-search/ajax-search/is-public-search-result-post', true, $post );
		}

		/**
		 * Sanitize post_type values for public Ajax Search queries.
		 *
		 * @param mixed $post_type Raw post_type query arg.
		 * @return string|array|false
		 */
		protected static function sanitize_public_search_post_type_arg( $post_type ) {

			if ( 'any' === $post_type ) {
				return 'any';
			}

			$is_array = is_array( $post_type );
			$types    = $is_array ? $post_type : array( $post_type );
			$types    = array_filter( array_map( 'sanitize_key', $types ) );
			$types    = array_filter( $types, array( __CLASS__, 'is_public_search_post_type' ) );
			$types    = array_values( array_unique( $types ) );

			if ( empty( $types ) ) {
				return false;
			}

			return $is_array ? $types : $types[0];
		}

		/**
		 * Check whether a post type is safe for public Ajax Search queries.
		 *
		 * @param string $post_type Post type slug.
		 * @return bool
		 */
		protected static function is_public_search_post_type( $post_type ) {

			$post_type_object = get_post_type_object( $post_type );

			return $post_type_object && is_post_type_viewable( $post_type_object );
		}

		/**
		 * Sanitize a tax_query array for use inside sanitize_current_query().
		 *
		 * Each clause must be an associative array with a defined set of sub-keys.
		 * The top-level 'relation' key (AND / OR) is preserved.
		 *
		 * @param  mixed $raw_tax_query Raw tax_query value.
		 * @return array                Sanitized tax_query or empty array.
		 */
		protected static function sanitize_tax_query( $raw_tax_query ) {

			if ( ! is_array( $raw_tax_query ) ) {
				return array();
			}

			$allowed_operators = array( 'IN', 'NOT IN', 'AND', 'EXISTS', 'NOT EXISTS' );
			$allowed_fields    = array( 'term_id', 'name', 'slug', 'term_taxonomy_id' );
			$allowed_relations = array( 'AND', 'OR' );

			$clean = array();

			if ( isset( $raw_tax_query['relation'] ) ) {
				$relation = strtoupper( (string) $raw_tax_query['relation'] );
				if ( in_array( $relation, $allowed_relations, true ) ) {
					$clean['relation'] = $relation;
				}
			}

			foreach ( $raw_tax_query as $index => $clause ) {
				if ( $index === 'relation' ) {
					continue;
				}

				if ( ! is_array( $clause ) ) {
					continue;
				}

				$clean_clause = array();

				if ( isset( $clause['taxonomy'] ) ) {
					$clean_clause['taxonomy'] = sanitize_key( (string) $clause['taxonomy'] );
				}

				if ( isset( $clause['field'] ) ) {
					$field = (string) $clause['field'];
					$clean_clause['field'] = in_array( $field, $allowed_fields, true ) ? $field : 'term_id';
				}

				if ( isset( $clause['operator'] ) ) {
					$operator = strtoupper( (string) $clause['operator'] );
					$clean_clause['operator'] = in_array( $operator, $allowed_operators, true ) ? $operator : 'IN';
				}

				if ( isset( $clause['terms'] ) ) {
					if ( is_array( $clause['terms'] ) ) {
						$clean_clause['terms'] = array_map( 'intval', $clause['terms'] );
					} else {
						$clean_clause['terms'] = intval( $clause['terms'] );
					}
				}

				if ( isset( $clause['include_children'] ) ) {
					$clean_clause['include_children'] = (bool) $clause['include_children'];
				}

				if ( ! empty( $clean_clause ) ) {
					$clean[] = $clean_clause;
				}
			}

			return $clean;
		}
	}
}
