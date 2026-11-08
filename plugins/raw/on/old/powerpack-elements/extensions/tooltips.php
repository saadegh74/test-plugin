<?php
namespace PowerpackElements\Extensions;

// Powerpack Elements classes
use PowerpackElements\Base\Extension_Base;

// Elementor classes
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Tooltips Extension
 *
 * Adds tooltip on widgets
 *
 * @since 2.9.0
 */
class Extension_Tooltips extends Extension_Base {

	/**
	 * Is Common Extension
	 *
	 * Defines if the current extension is common for all element types or not
	 *
	 * @since 2.9.0
	 * @access protected
	 *
	 * @var bool
	 */
	protected $is_common = true;

	/**
	 * A list of scripts that the widgets is depended in
	 *
	 * @since 2.9.0
	 **/
	public function get_script_depends() {
		return array(
			'powerpack-frontend',
			'pp-tooltipster'
		);
	}

	/**
	 * The description of the current extension
	 *
	 * @since 2.9.0
	 **/
	public static function get_description() {
		return __( 'Adds tooltip on widgets.', 'powerpack' );
	}

	/**
	 * Is disabled by default
	 *
	 * Return wether or not the extension should be disabled by default,
	 * prior to user actually saving a value in the admin page
	 *
	 * @access public
	 * @since 2.9.0
	 * @return bool
	 */
	public static function is_default_disabled() {
		return true;
	}

	/**
	 * Add common sections
	 *
	 * @since 2.9.0
	 *
	 * @access protected
	 */
	protected function add_common_sections_actions() {

		/* // Activate sections for sections
		add_action( 'elementor/element/section/section_custom_css/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for columns
		add_action( 'elementor/element/column/section_custom_css/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 ); */

		// Activate sections for widgets
		add_action( 'elementor/element/common/section_custom_css/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		/* // Activate sections for sections if elementor pro
		add_action( 'elementor/element/section/section_custom_css_pro/after_section_end', function( $element, $args ) {
			$this->add_common_sections( $element, $args );
		}, 10, 2 );

		// Activate sections for columns if elementor pro
		add_action( 'elementor/element/column/section_custom_css_pro/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 ); */

		// Activate sections for widgets if elementor pro
		add_action( 'elementor/element/common/section_custom_css_pro/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );
	}

	/**
	 * Add Controls
	 *
	 * @since 2.9.0
	 *
	 * @access private
	 */
	private function add_controls( $element, $args ) {

		$element_type = $element->get_type();

		$element->add_control(
			'pp_elements_tooltip_enable',
			array(
				'label'        => __( 'Tooltip', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'frontend_available' => true,
			)
		);

		$element->start_controls_tabs( 'pp_elements_tooltip_tabs', [
			'condition' => [
				'pp_elements_tooltip_enable!' => '',
			],
		] );

		$element->start_controls_tab( 'pp_elements_tooltip_settings', [
			'label'     => __( 'Settings', 'powerpack' ),
			'condition' => [
				'pp_elements_tooltip_enable!' => '',
			],
		] );

		$element->add_control(
			'pp_elements_tooltip_content',
			array(
				'label'       => __( 'Tooltip Content', 'powerpack' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'Tooltip Content', 'powerpack' ),
				'label_block' => true,
				'rows'        => 3,
				'condition'   => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->add_control(
			'pp_elements_tooltip_target',
			array(
				'label'              => __( 'Target', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'current',
				'options'            => array(
					'current' => __( 'Current Element', 'powerpack' ),
					'custom'  => __( 'Custom Selector', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition' => array(
					'pp_elements_tooltip_enable!' => '',
				),
			)
		);

		$element->add_control(
			'pp_elements_tooltip_selector',
			array(
				'label'       => __( 'CSS Selector', 'powerpack' ),
				'description' => __( 'Use a CSS selector for any html element within this element.', 'powerpack' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => false,
				'frontend_available' => true,
				'condition'   => [
					'pp_elements_tooltip_enable!' => '',
					'pp_elements_tooltip_target'  => 'custom',
				],
			)
		);

		$element->add_control(
			'pp_elements_tooltip_trigger',
			array(
				'label'              => __( 'Trigger', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'hover',
				'options'            => array(
					'hover'     => __( 'Hover', 'powerpack' ),
					'click'     => __( 'Click', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition' => array(
					'pp_elements_tooltip_enable!' => '',
				),
			)
		);

		$element->add_control(
			'pp_elements_tooltip_position',
			array(
				'label'   => __( 'Tooltip Position', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'top'          => __( 'Top', 'powerpack' ),
					'bottom'       => __( 'Bottom', 'powerpack' ),
					'left'         => __( 'Left', 'powerpack' ),
					'right'        => __( 'Right', 'powerpack' ),
				),
				'frontend_available' => true,
			)
		);

		$element->add_control(
			'pp_elements_tooltip_arrow',
			array(
				'label'              => __( 'Show Arrow', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'yes',
				'options' => array(
					'yes' => __( 'Yes', 'powerpack' ),
					'no'  => __( 'No', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition' => array(
					'pp_elements_tooltip_enable!' => '',
				),
			)
		);

		$element->add_control(
			'pp_elements_tooltip_animation',
			array(
				'label'   => __( 'Animation', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fade',
				'options' => array(
					'fade'  => __( 'Fade', 'powerpack' ),
					'fall'  => __( 'Fall', 'powerpack' ),
					'grow'  => __( 'Grow', 'powerpack' ),
					'slide' => __( 'Slide', 'powerpack' ),
					'swing' => __( 'Swing', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition' => array(
					'pp_elements_tooltip_enable!' => '',
				),
			)
		);

		$element->add_control(
			'pp_elements_tooltip_distance',
			array(
				'label'       => __( 'Distance', 'powerpack' ),
				'description' => __( 'The distance between the hotspot and the tooltip.', 'powerpack' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => '',
				),
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'frontend_available' => true,
				'condition' => array(
					'pp_elements_tooltip_enable!' => '',
				),
			)
		);

		$element->add_control(
			'pp_elements_tooltip_zindex',
			array(
				'label'              => __( 'Z-Index', 'powerpack' ),
				'description'        => __( 'Increase the z-index value if you are unable to see the tooltip. For example: 99, 999, 9999 ', 'powerpack' ),
				'type'               => Controls_Manager::NUMBER,
				'default'            => 99,
				'min'                => -9999999,
				'step'               => 1,
				'frontend_available' => true,
				'condition' => array(
					'pp_elements_tooltip_enable!' => '',
				),
			)
		);

		$element->end_controls_tab();

		$element->start_controls_tab( 'pp_elements_tooltip_style', [
			'label'     => __( 'Style', 'powerpack' ),
			'condition' => [
				'pp_elements_tooltip_enable!' => '',
			],
		] );

		$element->add_control(
			'pp_elements_tooltip_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.pp-tooltip.pp-tooltip-{{ID}} .tooltipster-box' => 'background-color: {{VALUE}};',
					'.pp-tooltip.pp-tooltip-{{ID}}.tooltipster-top .tooltipster-arrow-background' => 'border-top-color: {{VALUE}};',
					'.pp-tooltip.pp-tooltip-{{ID}}.tooltipster-bottom .tooltipster-arrow-background' => 'border-bottom-color: {{VALUE}};',
					'.pp-tooltip.pp-tooltip-{{ID}}.tooltipster-left .tooltipster-arrow-background' => 'border-left-color: {{VALUE}};',
					'.pp-tooltip.pp-tooltip-{{ID}}.tooltipster-right .tooltipster-arrow-background' => 'border-right-color: {{VALUE}};',
				),
				'condition' => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->add_control(
			'pp_elements_tooltip_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'.pp-tooltip.pp-tooltip-{{ID}} .pp-tooltip-content' => 'color: {{VALUE}};',
				),
				'condition' => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'pp_elements_tooltip_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '.pp-tooltip.pp-tooltip-{{ID}} .pp-tooltip-content',
				'condition' => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'pp_elements_tooltip_box_shadow',
				'selector' => '.pp-tooltip.pp-tooltip-{{ID}} .tooltipster-box',
				'condition' => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'pp_elements_tooltip_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '.pp-tooltip.pp-tooltip-{{ID}} .tooltipster-box',
				'condition' => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->add_control(
			'pp_elements_tooltip_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'.pp-tooltip.pp-tooltip-{{ID}} .tooltipster-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition' => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->add_responsive_control(
			'pp_elements_tooltip_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'.pp-tooltip.pp-tooltip-{{ID}} .tooltipster-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition' => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->add_control(
			'pp_elements_tooltip_width',
			array(
				'label'     => __( 'Width', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 100,
						'max'  => 400,
						'step' => 1,
					),
				),
				'frontend_available' => true,
				'condition' => [
					'pp_elements_tooltip_enable!' => '',
				],
			)
		);

		$element->end_controls_tab();

		$element->end_controls_tabs();
	}

	protected function render() {
		$settings = $element->get_settings();
	}

	/**
	 * Add Actions
	 *
	 * @since 2.9.0
	 *
	 * @access protected
	 */
	protected function add_actions() {

		// Activate controls for section
		/* add_action( 'elementor/element/section/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {
			$this->add_controls( $element, $args );
		}, 10, 2 );

		// Activate controls for columns
		add_action( 'elementor/element/column/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {
			$this->add_controls( $element, $args );
		}, 10, 2 ); */

		// Activate controls for widgets
		add_action( 'elementor/element/common/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {
			$this->add_controls( $element, $args );
		}, 10, 2 );

		// Conditions for sections
		add_action( 'elementor/widget/before_render_content', function( $element ) {
			$settings = $element->get_settings_for_display();

			if ( 'yes' !== $settings['pp_elements_tooltip_enable'] ) {
				return;
			}

			$tooltip_settings = array(
				'target'      => $settings['pp_elements_tooltip_target'],
				'selector'    => $settings['pp_elements_tooltip_selector'],
				'trigger'     => ( $settings['pp_elements_tooltip_trigger'] ) ? $settings['pp_elements_tooltip_trigger'] : 'hover',
				'distance'    => ( $settings['pp_elements_tooltip_distance'] ) ? $settings['pp_elements_tooltip_distance']['size'] : '',
				'arrow'       => esc_html( $settings['pp_elements_tooltip_arrow'] ),
				'animation'   => esc_html( $settings['pp_elements_tooltip_animation'] ),
				'zindex'      => $settings['pp_elements_tooltip_zindex'],
				'width'       => ( $settings['pp_elements_tooltip_width']['size'] ) ? $settings['pp_elements_tooltip_width']['size'] : '',
			);

			$element->add_render_attribute(
				'pp-tooltip', [
					'class' => 'pp-tooltip-content',
					'id'    => 'pp-tooltip-content-' . $element->get_id(),
				]
			);
		}, 10, 1 );

		add_action( 'elementor/widget/render_content', function( $content, $element ) {
			$settings = $element->get_settings_for_display();

			if ( 'yes' !== $settings['pp_elements_tooltip_enable'] ) {
				return $content;
			}

			ob_start();
			?>
			<div class="pp-tooltip-container"><div <?php echo wp_kses_post( $element->get_render_attribute_string( 'pp-tooltip' ) ); ?>>
				<?php echo $this->parse_text_editor( $settings['pp_elements_tooltip_content'], $element ); ?>
			</div></div>
			<?php
			$content .= ob_get_clean();

			return $content;
		}, 10, 2 );

		add_action( 'elementor/widget/print_template', function( $template, $widget ) {
			if ( ! $template ) {
				return;
			}

			ob_start();
			?><#
			if ( 'yes' === settings.pp_elements_tooltip_enable ) {

				view.addRenderAttribute( 'pp-tooltip', {
					'class': 'pp-tooltip-content',
					'id':    'pp-tooltip-content-' + view.$el.data('id'),
				} );
				#>
				<div class="pp-tooltip-container">
				<div {{{ view.getRenderAttributeString( 'pp-tooltip' ) }}}>
					{{{ settings.pp_elements_tooltip_content }}}
				</div>
				</div>

			<# } #><?php

			$template .= ob_get_clean();

			return $template;
		}, 10, 2 );
	}

	/**
	 * Parse text editor.
	 *
	 * Parses the content from rich text editor with shortcodes, oEmbed and
	 * filtered data.
	 *
	 * @since 2.9.0
	 * @access protected
	 *
	 * @param string $content Text editor content.
	 *
	 * @return string Parsed content.
	 */
	protected function parse_text_editor( $content, $element ) {
		/** This filter is documented in wp-includes/widgets/class-wp-widget-text.php */
		$content = apply_filters( 'widget_text', $content, $element->get_settings() );

		$content = shortcode_unautop( $content );
		$content = do_shortcode( $content );
		$content = wptexturize( $content );

		if ( $GLOBALS['wp_embed'] instanceof \WP_Embed ) {
			$content = $GLOBALS['wp_embed']->autoembed( $content );
		}

		return $content;
	}
}
