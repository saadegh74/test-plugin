<?php
namespace PowerpackElements\Classes;

use Elementor\Controls_Manager;
use PowerpackElements\Modules\QueryControl\Module as QueryModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class PP_Woo_Builder_Preview {
	public $preview_args = false;
	public $preview_query = null;
	public $current_post_id = 0;
	public $template_id = 0;
	protected $request = [];

	public function __construct() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		$this->template_id = get_option( 'pp_woo_template_single_product' );

		if ( empty( $this->template_id ) ) {
			return;
		}

		//add_action( 'elementor/init', [ $this, 'init_preview_query' ], 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'pp_woo_builder_widget_before_render', [ $this, 'set_preview_query' ] );
		add_action( 'pp_woo_builder_widget_after_render', [ $this, 'reset_preview_query' ] );
		add_action( 'elementor/documents/register_controls', [ $this, 'register_preview_control' ] );
		add_filter( 'body_class', [ $this, 'filter_body_classes' ] );

		if ( $this->is_elementor_active() ) {
			add_action( 'init', [ $this, 'register_wc_hooks' ], 5 );
		}
	}

	public function is_elementor_active() {
		if ( empty( $this->request ) ) {
			$this->request = $_REQUEST;
		}

		if ( isset( $this->request['action'] ) && 'elementor' === $this->request['action'] && is_admin() && $this->template_id == $this->request['post'] ) {
			return true;
		}
		if ( isset( $this->request['elementor_library'] ) && isset( $this->request['preview_id'] ) && $this->template_id == $this->request['preview_id'] ) {
			return true;
		}
		if ( isset( $this->request['elementor_library'] ) && isset( $this->request['elementor-preview'] ) && $this->template_id == $this->request['elementor-preview'] ) {
			return true;
		}
		if ( isset( $_SERVER['HTTP_REFERER'] ) && is_admin() ) {
			$http_referer = $_SERVER['HTTP_REFERER'];
			if ( strpos( $http_referer, 'action=elementor' ) !== false && strpos( $http_referer, 'post=' . $this->template_id ) !== false ) {
				return true;
			}
		}

		return false;
	}

	public function register_wc_hooks() {
		if ( function_exists( 'wc' ) && ! is_admin() ) {
			wc()->frontend_includes();
		}
	}

	public function enqueue_scripts() {
		// In preview mode it's not a real Product page - enqueue manually.
		if ( $this->is_elementor_active() ) {
			$this->set_preview_query();

			global $product;

			if ( is_singular( 'product' ) ) {
				$product = wc_get_product();
			}

			if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
				wp_enqueue_script( 'zoom' );
			}
			if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
				wp_enqueue_script( 'flexslider' );
			}
			if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
				wp_enqueue_script( 'photoswipe-ui-default' );
				wp_enqueue_style( 'photoswipe-default-skin' );
				add_action( 'wp_footer', 'woocommerce_photoswipe' );
			}
			wp_enqueue_script( 'wc-single-product' );

			wp_enqueue_style( 'photoswipe' );
			wp_enqueue_style( 'photoswipe-default-skin' );
			wp_enqueue_style( 'photoswipe-default-skin' );
			wp_enqueue_style( 'woocommerce_prettyPhoto_css' );

			$this->reset_preview_query();
		}
	}

	public function filter_body_classes( $body_classes ) {
		if ( get_the_ID() == $this->template_id || $this->is_elementor_active() ) {
			$body_classes[] = 'woocommerce';
		}

		return $body_classes;
	}

	public function get_current_post() {
		global $post;
		if ( ! is_object( $post ) ) {
			$post_id = $this->template_id;
			if ( $post_id ) {
				$post = get_post( $post_id );
			}
		}

		return $post;
	}

	public function init_preview_query() {
		$post = $this->get_current_post();

		// Make sure we're on a theme layout.
		if ( ! is_object( $post ) || 'elementor_library' != $post->post_type ) {
			return;
		}

		if ( $this->template_id != $post->ID ) {
			return;
		}

		$this->current_post_id = $post->ID;
		$document = pp_get_elementor()->documents->get_doc_or_auto_save( $post->ID );
		$product_post_id = 0;

		if ( ! $document ) {
			$product_post_id = $this->get_a_product( 'id' );
		} else {
			$preview_id = (int) $document->get_settings( 'pp_preview_id' );
			$product_post_id = $preview_id ? $preview_id : $this->get_a_product( 'id' );
		}

		$this->preview_args = [
			'p' => $product_post_id,
			'post_type' => 'product',
		];

		// Setup the preview hooks.
		if ( $this->preview_args ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'set_preview_query' ], 1 );
			add_action( 'wp_enqueue_scripts', [ $this, 'reset_preview_query' ], PHP_INT_MAX );
		}
	}

	public function set_query() {
		if ( ! $this->preview_args ) {
			return;
		}

		$post_id = $this->preview_args['p'];
		$GLOBALS['post'] = get_post( $post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Set the template as `$wp_query->current_object` for `wp_title` and etc.
		query_posts( $this->preview_args );
	}

	/**
	 * Overrides the main query based on the current theme
	 * layout that is being edited and the location preview
	 * that has been set for it.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function set_preview_query() {
		global $wp_query;
		global $post;

		if ( ! $this->is_elementor_active() ) {
			return;
		}

		// Make sure we have preview args.
		if ( ! $this->preview_args ) {
			$this->init_preview_query();
		}

		// Create the preview query.
		$this->preview_query = new \WP_Query( $this->preview_args );

		// Make sure the preview query returns a post.
		if ( ! is_object( $this->preview_query->post ) ) {
			return;
		}

		// Override $wp_query and $post with the preview query.
		$wp_query = $this->preview_query;
		$post     = $this->preview_query->post;
		setup_postdata( $post );
	}

	/**
	 * Resets the preview query back to the main query.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function reset_preview_query() {
		if ( ! $this->is_elementor_active() ) {
			return;
		}
		// Make sure we have a preview query.
		if ( ! $this->preview_args || ! $this->preview_query ) {
			return;
		}

		// Rewind posts and reset the query.
		rewind_posts();
		wp_reset_query();

		// Reset the builder's post ID.
		if ( defined( 'DOING_AJAX' ) ) {

		}
	}

	public function register_preview_control( $document ) {
		$post_id = $document->get_main_id();
		$woo_builder_tmpl_id = get_option( 'pp_woo_template_single_product' );

		if ( $post_id != $woo_builder_tmpl_id ) {
			return;
		}

		$document->start_controls_section(
			'pp_preview_settings',
			[
				'label' => __( 'Preview Settings', 'powerpack' ),
				'tab' => Controls_Manager::TAB_SETTINGS,
			]
		);

		$document->add_control(
			'pp_preview_id',
			[
				'label'                 => __( 'Choose Product', 'powerpack' ),
				'type'                  => 'pp-query',
				'label_block'           => true,
				'multiple'              => false,
				'query_type'            => 'posts',
				'object_type'           => 'product',
			]
		);

		$document->add_control(
			'pp_apply_preview',
			[
				'type' => Controls_Manager::BUTTON,
				'label' => __( 'Apply & Preview', 'powerpack' ),
				'label_block' => true,
				'show_label' => false,
				'text' => __( 'Apply & Preview', 'powerpack' ),
				'separator' => 'none',
				'event' => 'ppWooBuilder:ApplyPreview',
			]
		);

		$document->end_controls_section();
	}

	private function get_a_product( $prop = false ) {
		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'numberposts' => '1',
			'ignore_sticky_posts' => true,
		);

		$posts = get_posts( $args );
		$id = 0;

		if ( ! is_wp_error( $posts ) && ! empty( $posts ) ) {
			if ( 'id' === $prop ) {
				return $id;
			}
			return $posts[0];
		}

		return $id;
	}
}
new PP_Woo_Builder_Preview();
