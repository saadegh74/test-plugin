<?php
namespace PowerpackElements\Classes;

use PowerpackElements\Classes\PP_Admin_Settings;
use PowerpackElements\Classes\PP_Helper;

/**
 * Handles logic for the site Header / Footer.
 *
 * @package PowerPack Elements
 * @since 1.4.15
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PP_Header_Footer
 */
final class PP_Header_Footer {
	/**
	 * Settings tab constant.
	 */
	const SETTINGS_TAB = 'header_footer';

	/**
	 * Holds an array of posts.
	 *
	 * @var array $templates
	 * @since 1.4.15
	 */
	private static $templates = array();

	/**
	 * Instance of Elemenntor Frontend class.
	 *
	 * @var \Elementor\Frontend()
	 * @since 1.4.15
	 */
	private static $elementor_instance;

	/**
	 * Holds the post ID for header.
	 *
	 * @var int $header
	 * @since 1.4.15
	 */
	public static $header;

	/**
	 * Holds the post ID for footer.
	 *
	 * @var int $footer
	 * @since 1.4.15
	 */
	public static $footer;

	/**
	 * Initialize hooks.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function init() {

		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		self::$elementor_instance = \Elementor\Plugin::instance();

		add_filter( 'pp_elements_admin_settings_tabs', __CLASS__ . '::render_settings_tab', 10, 1 );
		add_action( 'pp_elements_admin_settings_save', __CLASS__ . '::save_settings' );

		add_action( 'after_setup_theme', __CLASS__ . '::load' );
	}

	/**
	 * Render settings tab.
	 *
	 * Adds Header/Footer tab in PowerPack admin settings.
	 *
	 * @since 1.4.15
	 * @param array $tabs Array of existing settings tabs.
	 */
	public static function render_settings_tab( $tabs ) {
		$tabs[ self::SETTINGS_TAB ] = array(
			'title'    => esc_html__( 'Header / Footer', 'powerpack' ),
			'show'     => ! is_network_admin() && ! PP_Admin_Settings::get_option( 'ppwl_hide_header_footer_tab' ),
			'cap'      => ! is_network_admin() ? 'manage_options' : 'manage_network_plugins',
			'file'     => POWERPACK_ELEMENTS_PATH . 'includes/admin/admin-settings-header-footer.php',
			'priority' => 325,
		);

		return $tabs;
	}

	/**
	 * Save settings.
	 *
	 * Saves setting fields value in options.
	 *
	 * @since 1.4.15
	 */
	public static function save_settings() {
		if ( ! isset( $_POST['pp_header_footer_page'] ) ) {
			return;
		}

		$header = isset( $_POST['pp_header_footer_template_header'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_header_footer_template_header'] ) ) : '';
		$footer = isset( $_POST['pp_header_footer_template_footer'] ) ? sanitize_text_field( wp_unslash( $_POST['pp_header_footer_template_footer'] ) ) : '';

		update_option( 'pp_header_footer_template_header', $header );
		update_option( 'pp_header_footer_template_footer', $footer );

		if ( isset( $_POST['pp_header_footer_fixed_header'] ) ) {
			update_option( 'pp_header_footer_fixed_header', 1 );
		} else {
			delete_option( 'pp_header_footer_fixed_header' );
		}

		if ( isset( $_POST['pp_header_footer_fixed_header_breakpoints'] ) ) {
			update_option( 'pp_header_footer_fixed_header_breakpoints', sanitize_text_field( wp_unslash( $_POST['pp_header_footer_fixed_header_breakpoints'] ) ) );
		} else {
			delete_option( 'pp_header_footer_fixed_header_breakpoints' );
		}

		if ( isset( $_POST['pp_header_footer_shrink_header'] ) ) {
			update_option( 'pp_header_footer_shrink_header', 1 );
		} else {
			delete_option( 'pp_header_footer_shrink_header' );
		}

		if ( isset( $_POST['pp_header_footer_overlay_header'] ) ) {
			update_option( 'pp_header_footer_overlay_header', 1 );
		} else {
			delete_option( 'pp_header_footer_overlay_header' );
		}

		if ( isset( $_POST['pp_header_footer_overlay_header_bg'] ) ) {
			update_option( 'pp_header_footer_overlay_header_bg', sanitize_text_field( wp_unslash( $_POST['pp_header_footer_overlay_header_bg'] ) ) );
		}
	}

	/**
	 * Add CSS classes to the body tag.
	 *
	 * Fired by 'body_class' filter.
	 *
	 * @since 1.4.15
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
	 * @since 1.4.15
	 */
	public static function get_templates() {
		if ( ! empty( self::$templates ) ) {
			return self::$templates;
		}

		$args = array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'posts_per_page'         => '-1',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$pages = get_posts( $args );

		$args['post_type'] = 'elementor_library';

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
			'pages'     => $pages,
			'templates' => $templates,
		);

		return self::$templates;
	}

	/**
	 * Get templates HTML.
	 *
	 * Get all pages and Elementor templates and build options for select field.
	 *
	 * @since 1.4.15
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
	 * Get breakpoints list.
	 *
	 * Get all responsive breakpoint options for select field.
	 *
	 * @since 2.2.0
	 * @param string $selected Selected template for the field.
	 */
	public static function get_breakpoints( $selected = '' ) {
		$breakpoints = array(
			'all'               => __( 'Always', 'powerpack' ),
			'large'             => __( 'Desktop Only', 'powerpack' ),
			'large-medium'      => __( 'Desktop & Tablet', 'powerpack' ),
			'medium'            => __( 'Tablet Only', 'powerpack' ),
			'medium-responsive' => __( 'Tablet & Mobile', 'powerpack' ),
			'responsive'        => __( 'Mobile Only', 'powerpack' ),
		);

		$options = '<option value="">' . esc_html__( 'Default', 'powerpack' ) . '</option>';

		foreach ( $breakpoints as $breakpoint => $label ) {
			$options .= '<option value="' . $breakpoint . '" ' . selected( $selected, $breakpoint, false ) . '>' . $label . '</option>';
		}

		return $options;
	}

	/**
	 * Returns the slug for supported theme.
	 *
	 * @since 1.4.15
	 * @return mixed
	 */
	public static function get_theme_support_slug() {
		$slug = false;

		if ( defined( 'ASTRA_THEME_VERSION' ) ) {
			$slug = 'astra';
		} elseif ( defined( 'FL_THEME_VERSION' ) ) {
			$slug = 'bb-theme';
		} elseif ( function_exists( 'genesis' ) ) {
			$slug = 'genesis';
		} elseif ( defined( 'GENERATE_VERSION' ) ) {
			$slug = 'generatepress';
		} elseif ( defined( 'OCEANWP_THEME_VERSION' ) ) {
			$slug = 'oceanwp';
		} elseif ( defined( 'WPBF_VERSION' ) ) {
			$slug = 'pbf';
		} elseif ( function_exists( 'storefront_is_woocommerce_activated' ) ) {
			$slug = 'storefront';
		} else {
			$slug = 'universal-elementor';
		}

		return $slug;
	}

	/**
	 * Loads theme support if we have a supported theme.
	 *
	 * @since 1.4.15
	 * @return void
	 */
	public static function load() {
		self::$header = get_option( 'pp_header_footer_template_header' );
		self::$footer = get_option( 'pp_header_footer_template_footer' );

		// Remove option if header template has deleted.
		if ( ! empty( self::$header ) && 'publish' != get_post_status( self::$header ) ) {
			delete_option( 'pp_header_footer_template_header' );
		}
		// Remove option if footer template has deleted.
		if ( ! empty( self::$footer ) && 'publish' != get_post_status( self::$footer ) ) {
			delete_option( 'pp_header_footer_template_footer' );
		}

		if ( empty( self::$header ) && empty( self::$footer ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_scripts' );

		$slug = self::get_theme_support_slug();

		add_filter( 'body_class', __CLASS__ . '::body_class' );

		if ( $slug ) {
			require_once POWERPACK_ELEMENTS_PATH . "classes/theme-support/class-pp-theme-support-$slug.php";
		}
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public static function enqueue_scripts() {
		$suffix    = ( PP_Helper::is_script_debug() ) ? '' : '.min';
		$path_css  = ( PP_Helper::is_script_debug() ) ? 'assets/css/' : 'assets/css/min/';
		$path_js   = ( PP_Helper::is_script_debug() ) ? 'assets/js/' : 'assets/js/min/';
		$header_id = self::$header;
		$footer_id = self::$footer;

		// Enqueue jQyery throttle.
		wp_enqueue_script( 'jquery-throttle' );

		// Enqueue imagesloaded.
		wp_enqueue_script( 'imagesloaded' );

		if ( class_exists( '\Elementor\Plugin' ) ) {
			$elementor = \Elementor\Plugin::instance();
			$elementor->frontend->enqueue_styles();
		}

		if ( class_exists( '\ElementorPro\Plugin' ) ) {
			$elementor_pro = \ElementorPro\Plugin::instance();
			$elementor_pro->enqueue_styles();
		}

		if ( pp_header_enabled() ) {
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $header_id );
			} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
				$css_file = new \Elementor\Post_CSS_File( $header_id );
			}

			$css_file->enqueue();
		}

		if ( pp_footer_enabled() ) {
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $footer_id );
			} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
				$css_file = new \Elementor\Post_CSS_File( $footer_id );
			}

			$css_file->enqueue();
		}

		wp_enqueue_style( 'pp-header-layout-style', POWERPACK_ELEMENTS_URL . $path_css . 'header-layout' . $suffix . '.css', array(), POWERPACK_ELEMENTS_VER );
		wp_enqueue_script( 'pp-header-layout-script', POWERPACK_ELEMENTS_URL . $path_js . 'header-layout' . $suffix . '.js', array('jquery'), POWERPACK_ELEMENTS_VER, true );

	}

	/**
	 * Renders the header for the current page.
	 * Used by theme support classes.
	 *
	 * @param mixed $tag    HTML tag for header.
	 * @param mixed $id     Header ID.
	 * @since 1.4.15
	 * @return void
	 */
	public static function render_header( $tag = null, $id = '' ) {
		$tag              = ! $tag ? 'header' : $tag;
		$id               = ! $id ? 'masthead' : $id;
		$header_id        = self::$header;
		$is_fixed         = get_option( 'pp_header_footer_fixed_header' );
		$fixed_breakpoint = get_option( 'pp_header_footer_fixed_header_breakpoints', 'large-medium' );
		$is_shrink        = get_option( 'pp_header_footer_shrink_header' );
		$is_overlay       = get_option( 'pp_header_footer_overlay_header' );
		$overlay_bg       = get_option( 'pp_header_footer_overlay_header_bg', 'default' );

		do_action( 'pp_header_footer_before_render_header', $header_id );

		// Print the styles if we are outside of the head tag.
		if ( did_action( 'wp_enqueue_scripts' ) && ! doing_filter( 'wp_enqueue_scripts' ) ) {
			wp_print_styles();
		}

		$data_sticky     = $is_fixed ? '1' : '0';
		$data_shrink     = $is_shrink ? '1' : '0';
		$data_overlay    = $is_overlay ? '1' : '0';
		$data_overlay_bg = $overlay_bg;
		$fixed_breakpoint = ( $fixed_breakpoint ) ? $fixed_breakpoint : 'large-medium';
		?>
		<<?php echo esc_html( $tag ); ?> <?php if ( $id) { echo 'id="' . esc_attr( $id ) . '"'; } ?> class="pp-elementor-header" itemscope="itemscope" itemtype="https://schema.org/WPHeader" data-type="header" data-header-id="<?php echo esc_attr( $header_id ); ?>" data-sticky="<?php echo esc_attr( $data_sticky ); ?>" data-shrink="<?php echo esc_attr( $data_shrink ); ?>" data-overlay="<?php echo esc_attr( $data_overlay ); ?>" data-overlay-bg="<?php echo esc_attr( $overlay_bg ); ?>" data-breakpoint="<?php echo esc_attr( $fixed_breakpoint ); ?>">
			<?php echo self::$elementor_instance->frontend->get_builder_content_for_display( $header_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</<?php echo esc_html( $tag ); ?>>
		<?php

		do_action( 'pp_header_footer_after_render_header', $header_id );
	}

	/**
	 * Renders the footer for the current page.
	 * Used by theme support classes.
	 *
	 * @param mixed $tag    HTML tag for footer.
	 * @since 1.4.15
	 * @return void
	 */
	public static function render_footer( $tag = null ) {
		$tag       = ! $tag ? 'footer' : $tag;
		$footer_id = self::$footer;

		// Print the styles if we are outside of the head tag.
		if ( did_action( 'wp_enqueue_scripts' ) && ! doing_filter( 'wp_enqueue_scripts' ) ) {
			wp_print_styles();
		}

		do_action( 'pp_header_footer_before_render_footer', $footer_id );
		?>
		<<?php echo esc_html( $tag ); ?> itemtype="https://schema.org/WPFooter" itemscope="itemscope" id="colophon" role="contentinfo">
			<?php echo self::$elementor_instance->frontend->get_builder_content_for_display( $footer_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</<?php echo esc_html( $tag ); ?>>
		<?php

		do_action( 'pp_header_footer_after_render_footer', $footer_id );
	}

	/**
	 * Overrides the default editor content for headers
	 * and footers since those are edited in place.
	 *
	 * @param string $content   Post content.
	 * @since 1.4.15
	 * @return string
	 */
	public static function override_the_content( $content ) {
		return '<div style="padding: 200px 100px; text-align:center; opacity:0.5;">' . __( 'Content Area', 'powerpack' ) . '</div>';
	}
}

// Initialize the class.
PP_Header_Footer::init();
