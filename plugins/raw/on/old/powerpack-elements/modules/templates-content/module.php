<?php
namespace PowerpackElements\Modules\TemplatesContent;

use PowerpackElements\Base\Module_Base;
use PowerpackElements\Classes\PP_Helper;

// Elementor Classes
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @since 2.2.0
 */
class Module extends Module_Base {

	/**
	 * @since 2.2.0
	 */
	public function get_name() {
		return 'templates';
	}

	/**
	 * @since 2.2.0
	 */
	public function get_widgets() {
		return [];
	}

	/**
	 * @since 2.2.0
	 */
	protected static function get_templates( $args = [] ) {

		if ( ! method_exists( '\Elementor\TemplateLibrary\Manager', 'get_source' ) || ! method_exists( '\Elementor\TemplateLibrary\Source_Local', 'get_items' ) ) {
			return;
		}

		return pp_get_elementor()->templates_manager->get_source( 'local' )->get_items( $args );
	}

	/**
	 * Markup for when no templates exist
	 *
	 * @since 2.2.0
	 * @return string
	 */
	protected static function empty_templates_message( $template_type = '' ) {
		return '<div id="elementor-widget-template-empty-templates">
				<div class="elementor-widget-template-empty-templates-icon"><i class="eicon-nerd"></i></div>
				<div class="elementor-widget-template-empty-templates-title">' . sprintf( __( 'You Haven’t Saved %sTemplates Yet.', 'powerpack' ), ucfirst( $template_type ) . ' ' ) . '</div>
				<div class="elementor-widget-template-empty-templates-footer">' . __( 'Want to learn more about Elementor library?', 'powerpack' ) . ' <a class="elementor-widget-template-empty-templates-footer-url" href="https://go.elementor.com/docs-library/" target="_blank">' . __( 'Click Here', 'powerpack' ) . '</a>
				</div>
				</div>';
	}

	/**
	 * Add Render Attributes
	 *
	 * Action that adds additional attributes to the element specific
	 * to the loop
	 *
	 * @param   Element_Base    $element   The Elementor element object
	 * @since   2.2.0
	 * @return  void
	 */
	public static function add_render_attributes( $element ) {
		$unique_id = implode( '-', [ $element->get_id(), get_the_ID() ] );

		$element->add_render_attribute( [
			'_wrapper' => [
				'data-pp-template-widget-id' => $unique_id,
				'class' => [
					'elementor-pp-element-' . $unique_id,
				],
			],
		] );
	}

	/**
	 * Render Template Content
	 *
	 * Renders the content of an Elementor template for with the specified post ID
	 *
	 * @param int                                       $template_id  The template post ID
	 * @param \PowerpackElements\Base\Powerpack_Widget  $widget       The widget instance
	 * @since 1.4.13.2
	 */
	public static function render_template_content( $template_id, \PowerpackElements\Base\Powerpack_Widget $widget, $in_loop = false ) {

		if ( 'publish' !== get_post_status( $template_id ) || ! method_exists( '\Elementor\Frontend', 'get_builder_content_for_display' ) ) {
			return;
		}

		if ( ! $template_id ) {
			if ( method_exists( $widget, 'render_editor_placeholder' ) ) {
				$placeholder = __( 'Choose a post template that you want to use as post skin in widget settings.', 'powerpack' );

				$widget->render_editor_placeholder([
					'title' => __( 'No template selected!', 'powerpack' ),
					'body' => $placeholder,
				]);
			} else {
				esc_attr_e( 'No template selected!', 'powerpack' );
			}
		} else {

			global $wp_query;

			$print_styles = false;

			if ( $in_loop ) { // If we're inside a loop we need to make sure the global query is replaced with the current post

				$print_styles = true;

				// Keep old global wp_query
				$old_query = $wp_query;

				// Create a new query from the current post in loop
				$new_query = new \WP_Query( [
					'post_type' => 'any',
					'p' => get_the_ID(),
				] );

				// Set the global query to the new query
				$wp_query = $new_query;
			}

			$background = new BackgroundImage();
			$background->set_template_id( $template_id );

			if ( $in_loop ) {
				// Alter element rendering to account for template
				add_action( 'elementor/frontend/before_render', [ __CLASS__, 'add_render_attributes' ], 10, 1 );
				add_action( 'elementor/frontend/before_render', [ $background, 'add_actions' ], 20, 1 );
			}

			// Fetch the template
			$template = PP_Helper::elementor()->frontend->get_builder_content_for_display( $template_id, $print_styles );

			if ( $in_loop ) {
				// Remove action
				remove_action( 'elementor/frontend/before_render', [ $background, 'add_actions' ] );
				remove_action( 'elementor/frontend/before_render', [ __CLASS__, 'add_render_attributes' ] );

				// Revert to the initial query
				$wp_query = $old_query;
			}

			?><div class="elementor-template"><?php echo $template; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php
		}
	}
}
