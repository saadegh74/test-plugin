<?php
namespace PowerpackElements\Modules\DynamicTags\Tags;

use Elementor\Controls_Manager;
use Elementor\Utils;
use \Elementor\Core\DynamicTags\Data_Tag as Base_Tag;
use PowerpackElements\Modules\DynamicTags\Module as Dynamic_Tags;

class Taxonomy_Thumbnail extends Base_Tag {

	/**
	* Get Name
	*
	* Returns the Name of the tag
	*
	* @since 2.3.6
	* @access public
	*
	* @return string
	*/
	public function get_name() {
		return 'pp-taxonomy-thumbnail';
	}

	/**
	* Get Title
	*
	* Returns the title of the Tag
	*
	* @since 2.3.6
	* @access public
	*
	* @return string
	*/
	public function get_title() {
		return __( 'Taxonomy Thumbnail', 'powerpack' );
	}

	/**
	* Get Group
	*
	* Returns the Group of the tag
	*
	* @since 2.3.6
	* @access public
	*
	* @return string
	*/
	public function get_group() {
		return Dynamic_Tags::POWERPACK_TAGS_GROUP;
	}

	/**
	* Get Categories
	*
	* Returns an array of tag categories
	*
	* @since 2.3.6
	* @access public
	*
	* @return array
	*/
	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY ];
	}

	protected function register_controls() {
		$this->add_control(
			'pp_taxonomy_fallback_image',
			array(
				'label'     => __( 'Fallback Image', 'powerpack' ),
				'type'      => Controls_Manager::MEDIA,
			)
		);
	}

	public function get_value( array $options = [] ) {

		$fallback_image = $this->get_settings( 'pp_taxonomy_fallback_image' );

		if ( is_archive() ) {
			$term_detail = get_queried_object();
		}

		if ( is_single() ) {
			$current_post_id = get_the_ID();
			//$term_detail = get_the_category( $current_post_id );
			$post_type   = get_post_type( get_the_ID() );   
			$taxonomies  = get_object_taxonomies( $post_type );
			$term_detail = wp_get_object_terms( get_the_ID(), $taxonomies );
		}

		$enabled_taxonomies = pp_get_enabled_taxonomies();
		if ( empty( $enabled_taxonomies ) || empty( $term_detail ) ) {
			return [];
		}

		if ( is_object( $term_detail ) ) {
			$taxonomy_name = $term_detail->taxonomy;
			$term_id       = $term_detail->term_id;
		} else {
			$taxonomy_name = $term_detail[0]->taxonomy;
			$term_id       = $term_detail[0]->term_id;
		}

		if ( in_array( $taxonomy_name, $enabled_taxonomies, true ) ) {
			$cat_thumb_id          = get_term_meta( $term_id, 'thumbnail_id', true );
			$taxonomy_thumbnail_id = get_term_meta( $term_id, 'taxonomy_thumbnail_id', true );

			if ( empty( $cat_thumb_id ) ) {
				$cat_thumb_id = $taxonomy_thumbnail_id;
			}

			$category_image = wp_get_attachment_image_src( $cat_thumb_id, 'full' );

			if ( is_array( $category_image ) && ! empty( $category_image ) ) {
				return [
					'id'  => $cat_thumb_id,
					'url' => $category_image[0],
				];
			}
		}

		if ( ! empty( $fallback_image ) ) {
			return [
				'id'  => ( $fallback_image['id'] ) ? $fallback_image['id'] : 0,
				'url' => $fallback_image['url'],
			];
		} else {
			return [];
		}
	}
}
