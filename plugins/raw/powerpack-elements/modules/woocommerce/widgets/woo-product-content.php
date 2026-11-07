<?php
namespace PowerpackElements\Modules\Woocommerce\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Woo_Helper;

use Elementor\Controls_Manager;
use Elementor\Core\Schemes;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Woo - Product Content widget
 */
class Woo_Product_Content extends Powerpack_Widget {

	public function get_categories() {
		return parent::get_woo_categories();
	}

	/**
	 * Retrieve Woo - Product Content widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Product_Content' );
	}

	/**
	 * Retrieve Woo - Product Content widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Product_Content' );
	}

	/**
	 * Retrieve Woo - Product Content widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Product_Content' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Woo - Product Content widget belongs to.
	 *
	 * @since 1.4.13.4
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Product_Content' );
	}

	/**
	 * Retrieve the list of styles the Woo - Product Content depended on.
	 *
	 * Used to set style dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_style_depends() {
		return array(
			'pp-woocommerce',
		);
	}

	/**
	 * Register Woo - Product Content widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'powerpack' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'powerpack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'powerpack' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'powerpack' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'powerpack' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}};',
				],
				'scheme' => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_3,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'scheme' => Schemes\Typography::TYPOGRAPHY_3,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		do_action( 'pp_woo_builder_widget_before_render', $this );

		if ( Plugin::instance()->editor->is_edit_mode() ) {
			echo wp_kses_post( PP_Woo_Helper::get_instance()->default( $this->get_name() ) );
		} else {
			the_content();
		}

		do_action( 'pp_woo_builder_widget_after_render', $this );
	}

	public function render_plain_content() {}

}
