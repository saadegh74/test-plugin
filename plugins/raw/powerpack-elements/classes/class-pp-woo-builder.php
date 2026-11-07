<?php
namespace PowerpackElements\Classes;

use PowerpackElements\Classes\PP_Admin_Settings;

/**
 * Handles logic for the site Header / Footer.
 *
 * @package PowerPack Elements
 * @since 2.1.0
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PP_Woo_Builder
 */
final class PP_Woo_Builder {
	/**
	 * Settings tab constant.
	 */
	const SETTINGS_TAB = 'woo_template_manager';

	/**
	 * Holds an array of posts.
	 *
	 * @var array $templates
	 * @since 2.1.0
	 */
	private static $templates = array();

	/**
	 * Instance of Elemenntor Frontend class.
	 *
	 * @var \Elementor\Frontend()
	 * @since 2.1.0
	 */
	private static $elementor_instance;

	public static $pp_woo_elementor_template = array();

	/**
	 * Holds the post ID for header.
	 *
	 * @var int $single_product
	 * @since 2.1.0
	 */
	public static $single_product;

	/**
	 * Holds the post ID for footer.
	 *
	 * @var int $archive_product
	 * @since 2.1.0
	 */
	public static $archive_product;

	public static $templates_path;

	/**
	 * Initialize hooks.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public static function init() {

		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		self::$templates_path = POWERPACK_ELEMENTS_PATH . 'templates/';

		self::$elementor_instance = \Elementor\Plugin::instance();

		self::init_hooks();
	}

	public static function init_hooks() {
		if ( get_option( 'pp_woo_builder_enable' ) ) {
			add_filter( 'wc_get_template_part', __CLASS__ . '::get_product_page_template', 99, 3 );
			add_filter( 'wc_get_template', __CLASS__ . '::get_archive_page_template', 99, 3 );
			add_filter( 'woocommerce_locate_template', __CLASS__ . '::locate_template', 10, 3 );
			add_filter( 'template_include', __CLASS__ . '::template_include', 999 );
			add_action( 'pp_woocommerce_product_content', __CLASS__ . '::get_product_content_elementor', 5 );
			add_action( 'pp_woocommerce_product_content', __CLASS__ . '::get_default_product_data', 10 );

			// Archive
			//add_action( 'pp_woocommerce_archive_product_content', __CLASS__ . '::pp_product_archive_content', 5 );
			// Product Archive Page
			add_action( 'template_redirect', __CLASS__ . '::pp_product_archive_template', 999 );
			add_filter( 'template_include', __CLASS__ . '::pp_redirect_product_archive_template', 999 );
			add_action( 'pp_woocommerce_archive_product_content', __CLASS__ . '::pp_archive_product_page_content' );

			// Cart
			add_action( 'pp_cart_content', __CLASS__ . '::pp_cart_content', 5 );
			add_action( 'pp_cart_empty_content', __CLASS__ . '::pp_empty_cart_content', 10 );

			// Checkout
			add_action( 'pp_checkout_content', __CLASS__ . '::pp_checkout_content', 15 );
			//add_action( 'pp_checkout_top_content', __CLASS__ . '::pp_checkout_top_content', 20 );

			// My Account
			add_action( 'pp_woocommerce_account_content', __CLASS__ . '::pp_account_content', 25 );
			add_action( 'pp_woocommerce_account_content_form_login', __CLASS__ . '::pp_account_login_content', 30 );

		}

		add_filter( 'pp_elements_admin_settings_tabs', __CLASS__ . '::render_settings_tab', 10, 1 );
		add_action( 'pp_elements_admin_settings_save', __CLASS__ . '::save_settings' );

		add_action( 'after_setup_theme', __CLASS__ . '::load' );
	}

	public static function get_product_page_template( $template, $slug, $name ) {
		if ( 'content' === $slug && 'single-product' === $name ) {
			if ( self::woo_custom_product_template() ) {
				$template = self::$templates_path . 'woocommerce/single-product.php';
			}
		}

		return $template;
	}

	public static function get_archive_page_template( $template, $template_name, $templates_path ) {
		if ( 'content-product.php' === $template_name ) {
			$template = self::$templates_path . 'woocommerce/archive-product.php';
		}

		return $template;
	}

	public static function locate_template( $template, $template_name, $template_path ) {
		$template_id = '';

		if ( 'cart/cart.php' === $template_name ) {
			$template_id = get_option( 'pp_woo_template_product_cart' );
		} elseif ( 'checkout/form-checkout.php' === $template_name ) {
			$template_id = get_option( 'pp_woo_template_product_checkout' );
		} elseif ( 'checkout/thankyou.php' === $template_name ) {
			$template_id = get_option( 'pp_woo_template_product_thankyou_page' );
		} elseif ( 'myaccount/my-account.php' === $template_name ) {
			$template_id = get_option( 'pp_woo_template_product_myaccount_page' );
		} elseif ( 'myaccount/form-login.php' === $template_name ) {
			//$template_id = get_option( 'pp_woo_template_product_login_page' );
		}

		if ( ! empty( $template_id ) && file_exists( self::$templates_path . 'woocommerce/' . $template_name ) ) {
			return self::$templates_path . 'woocommerce/' . $template_name;
		}

		return $template;
	}

	public static function get_page_template_path( $template_slug ) {
		$path = '';

		if ( 'elementor_header_footer' === $template_slug ) {
			$path = self::$templates_path . 'page/header-footer.php';
		} elseif ( 'elementor_canvas' === $template_slug ) {
			$path = self::$templates_path . 'page/canvas.php';
		}

		return $path;
	}

	public static function template_include( $template ) {
		$page_template_slug = '';
		$template_id = '';

		if ( is_cart() ) {
			$template_id = get_option( 'pp_woo_template_product_cart' );
		} elseif ( is_checkout() ) {
			$template_id = get_option( 'pp_woo_template_product_checkout' );
		} elseif ( is_account_page() && is_user_logged_in() ) {
			$template_id = get_option( 'pp_woo_template_product_myaccount_page' );
		}

		if ( ! empty( $template_id ) ) {
			$page_template_slug = get_page_template_slug( $template_id );
		}
		if ( ! empty( $page_template_slug ) ) {
			$template_path = self::get_page_template_path( $page_template_slug );
			if ( $template_path ) {
				$template = $template_path;
			}
		}

		return $template;
	}

	public static function get_product_content_elementor( $post ) {
		if ( self::woo_custom_product_template() ) {
			$template_id = get_option( 'pp_woo_template_single_product' );

			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			the_content();
		}
	}

	// product data
	public static function get_default_product_data() {
		WC()->structured_data->generate_product_data();
	}

	public static function woo_custom_product_template() {
		$templatestatus = false;
		if ( is_product() ) {
			global $post;
			if ( ! isset( self::$pp_woo_elementor_template[ $post->ID ] ) ) {
				$single_product_default = get_option( 'pp_woo_template_single_product' );
				if ( ! empty( $single_product_default ) && 'default' !== $single_product_default ) {
					$templatestatus                               = true;
					self::$pp_woo_elementor_template[ $post->ID ] = true;
				}
			} else {
				$templatestatus = self::$pp_woo_elementor_template[ $post->ID ];
			}
		}
		return apply_filters( 'pp_woo_custom_product_template', $templatestatus );
	}

	/* public static function pp_product_archive_content() {
		$template_id = PP_Admin_Settings::get_option( 'pp_woo_template_product_archive' );
		if ( ! empty( $template_id ) ) {
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id );
		}
	} */

	/*
	* Archive Page
	*/
	public static function pp_product_archive_template() {
		$archive_template_id = 0;
		if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
			$termobj = get_queried_object();
			if ( is_shop() || ( is_tax( 'product_cat' ) && is_product_category() ) || ( is_tax( 'product_tag' ) && is_product_tag() ) || ( isset( $termobj->taxonomy ) && is_tax( $termobj->taxonomy ) ) ) {
				$product_achive_custom_page_id = get_option( 'pp_woo_template_product_archive' );

				// Meta value
				$pp_term_layout_id = 0;
				if ( ( is_tax( 'product_cat' ) && is_product_category() ) || ( is_tax( 'product_tag' ) && is_product_tag() ) ) {
					$pp_term_layout_id = get_term_meta( $termobj->term_id, 'wooletor_selectcategory_layout', true ) ? get_term_meta( $termobj->term_id, 'wooletor_selectcategory_layout', true ) : '0';
				}
				if ( $pp_term_layout_id != '0' ) {
					$archive_template_id = $pp_term_layout_id;
				} else {
					if ( ! empty( $product_achive_custom_page_id ) ) {
						$archive_template_id = $product_achive_custom_page_id;
					}
				}
				return $archive_template_id;
			}
			return $archive_template_id;
		}
	}

	public static function pp_redirect_product_archive_template( $template ) {
		$archive_template_id = self::pp_product_archive_template();

		if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
			if ( is_shop() || ( is_tax( 'product_cat' ) && is_product_category() ) || ( is_tax( 'product_tag' ) && is_product_tag() ) || ( isset( $termobj->taxonomy ) && is_tax( $termobj->taxonomy ) ) ) {
				$templatefile   = array();
				$templatefile[] = self::$templates_path . 'woocommerce/archive-product.php';

				if ( $archive_template_id != '0' ) {
					$template = locate_template( $templatefile );
					if ( ! $template || ( ! empty( $status_options['template_debug_mode'] ) && current_user_can( 'manage_options' ) ) ) {
						$template = self::$templates_path . 'woocommerce/archive-product.php';
					}
					$page_template_slug = get_page_template_slug( $archive_template_id );
					if ( 'elementor_header_footer' === $page_template_slug ) {
						$template = self::$templates_path . 'woocommerce/archive-product-fullwidth.php';
					} elseif ( 'elementor_canvas' === $page_template_slug ) {
						$template = self::$templates_path . 'woocommerce/archive-product-canvas.php';
					}
				}
			}
		}

		return $template;
	}

	// Element Content
	public static function pp_archive_product_page_content( $post ) {
		$archive_template_id = self::pp_product_archive_template();
		if ( $archive_template_id != '0' ) {
			echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $archive_template_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			the_content(); }
	}

	public static function pp_empty_cart_content() {
		$template_id = get_option( 'pp_woo_template_product_cart' );
		if ( ! empty( $template_id ) ) {
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public static function pp_cart_content() {
		$template_id = get_option( 'pp_woo_template_product_cart' );
		if ( ! empty( $template_id ) ) {
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public static function pp_checkout_content() {
		$template_id = get_option( 'pp_woo_template_product_checkout' );
		if ( ! empty( $template_id ) ) {
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			the_content();
		}
	}

	public static function pp_checkout_top_content() {
		$template_id = get_option( 'pp_woo_template_product_checkout' );
		if ( ! empty( $template_id ) ) {
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			the_content();
		}
	}

	public static function pp_thankyou_content() {
		$template_id = get_option( 'pp_woo_template_product_thankyou_page' );
		if ( ! empty( $template_id ) ) {
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			the_content();
		}
	}

	public static function pp_account_content() {
		$template_id = get_option( 'pp_woo_template_product_myaccount_page' );
		if ( is_user_logged_in() && ! empty( $template_id ) ) {
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			the_content();
		}
	}

	public static function pp_account_login_content() {
		$template_id = get_option( 'pp_woo_template_product_myaccount_page' );
		if ( ! empty( $template_id ) ) {
			echo self::$elementor_instance->frontend->get_builder_content_for_display( $template_id );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			the_content();
		}
	}

	/**
	 * Render settings tab.
	 *
	 * Adds Header/Footer tab in PowerPack admin settings.
	 *
	 * @since 2.1.0
	 * @param array $tabs Array of existing settings tabs.
	 */
	public static function render_settings_tab( $tabs ) {
		$tabs[ self::SETTINGS_TAB ] = array(
			'title'    => esc_html__( 'WooCommerce Builder', 'powerpack' ),
			'show'     => true,
			'cap'      => ! is_network_admin() ? 'manage_options' : 'manage_network_plugins',
			'file'     => POWERPACK_ELEMENTS_PATH . 'includes/admin/admin-settings-woo-templates.php',
			'priority' => 330,
		);

		return $tabs;
	}

	/**
	 * Save settings.
	 *
	 * Saves setting fields value in options.
	 *
	 * @since 2.1.0
	 */
	public static function save_settings() {
		if ( ! isset( $_POST['pp_woo_builder_page'] ) ) {
			return;
		}

		$single_product    = isset( $_POST['pp_woo_template_single_product'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_woo_template_single_product'] ) ) : '';
		$archive_product   = isset( $_POST['pp_woo_template_product_archive'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_woo_template_product_archive'] ) ) : '';
		$cart_product      = isset( $_POST['pp_woo_template_product_cart'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_woo_template_product_cart'] ) ) : '';
		$checkout_product  = isset( $_POST['pp_woo_template_product_checkout'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_woo_template_product_checkout'] ) ) : '';
		$thankyou_product  = isset( $_POST['pp_woo_template_product_thankyou_page'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_woo_template_product_thankyou_page'] ) ) : '';
		$myaccount_product = isset( $_POST['pp_woo_template_product_myaccount_page'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_woo_template_product_myaccount_page'] ) ) : '';

		update_option( 'pp_woo_template_single_product', $single_product );
		update_option( 'pp_woo_template_product_archive', $archive_product );
		update_option( 'pp_woo_template_product_cart', $cart_product );
		update_option( 'pp_woo_template_product_checkout', $checkout_product );
		update_option( 'pp_woo_template_product_thankyou_page', $thankyou_product );
		update_option( 'pp_woo_template_product_myaccount_page', $myaccount_product );

		if ( isset( $_POST['pp_woo_builder_enable'] ) ) {
			update_option( 'pp_woo_builder_enable', 1 );
		} else {
			delete_option( 'pp_woo_builder_enable' );
		}
	}

	/**
	 * Add CSS classes to the body tag.
	 *
	 * Fired by 'body_class' filter.
	 *
	 * @since 2.1.0
	 *
	 * @param array $classes An array of body classes.
	 *
	 * @return array An array of body classes.
	 */
	public static function body_class( $classes ) {
		$classes[] = 'pp-elementor-header-footer';

		return $classes;
	}

	/**
	 * Get templates.
	 *
	 * Get all pages and Elementor templates.
	 *
	 * @since 2.1.0
	 */
	public static function get_templates() {
		if ( ! empty( self::$templates ) ) {
			return self::$templates;
		}

		$args = array(
			'post_type'              => 'elementor_library',
			'post_status'            => 'publish',
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'posts_per_page'         => '-1',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$args['tax_query'] = array(
			array(
				'taxonomy' => 'elementor_library_type',
				'field'    => 'slug',
				'terms'    => array(
					'section',
					'widget',
					'page',
					'header',
					'footer',
				),
			),
		);

		$templates = get_posts( $args );

		self::$templates = array(
			'templates' => $templates,
		);

		return self::$templates;
	}

	/**
	 * Get templates HTML.
	 *
	 * Get all pages and Elementor templates and build options for select field.
	 *
	 * @since 2.1.0
	 * @param string $selected Selected template for the field.
	 */
	public static function get_templates_html( $selected = '' ) {
		$templates = self::get_templates();

		$options = '<option value="">' . esc_html__( 'Default', 'powerpack' ) . '</option>';

		foreach ( $templates as $type => $data ) {
			if ( ! count( $data ) ) {
				continue;
			}

			$label = '';

			if ( 'pages' === $type ) {
				$label = esc_html__( 'Pages', 'powerpack' );
			}
			if ( 'templates' === $type ) {
				$label = esc_html__( 'Builder Templates', 'powerpack' );
			}

			$options .= '<optgroup label="' . $label . '">';

			foreach ( $data as $post ) {
				$options .= '<option value="' . $post->ID . '" ' . selected( $selected, $post->ID, false ) . '>' . $post->post_title . '</option>';
			}

			$options .= '</optgroup>';
		}

		return $options;
	}

	/**
	 * Returns the slug for supported theme.
	 *
	 * @since 2.1.0
	 * @return mixed
	 */
	public static function get_theme_support_slug() {
		if ( is_pp_woocommerce() ) {
			return true;
		}

		return false;
	}

	/**
	 * Loads theme support if we have a supported theme.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public static function load() {
		self::$single_product  = get_option( 'pp_woo_template_single_product' );
		self::$archive_product = get_option( 'pp_woo_template_product_archive' );

		// Remove option if header template has deleted.
		if ( ! empty( self::$single_product ) && 'publish' != get_post_status( self::$single_product ) ) {
			delete_option( 'pp_woo_template_single_product' );
		}
		// Remove option if footer template has deleted.
		if ( ! empty( self::$archive_product ) && 'publish' != get_post_status( self::$archive_product ) ) {
			delete_option( 'pp_woo_template_product_archive' );
		}

		if ( empty( self::$single_product ) && empty( self::$archive_product ) ) {
			return;
		}

		add_filter( 'body_class', __CLASS__ . '::body_class' );
	}
}

// Initialize the class.
PP_Woo_Builder::init();
