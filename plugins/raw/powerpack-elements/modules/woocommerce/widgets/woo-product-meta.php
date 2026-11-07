<?php
/**
 * PowerPack WooCommerce Product Meta widget.
 *
 * @package PowerPack
 */

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
 * Woo - Product Meta widget
 */
class Woo_Product_Meta extends Powerpack_Widget {
	public function get_categories() {
		return parent::get_woo_categories();
	}

	/**
	 * Retrieve Woo - Product Meta widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Woo_Product_Meta' );
	}

	/**
	 * Retrieve Woo - Product Meta widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Woo_Product_Meta' );
	}

	/**
	 * Retrieve Woo - Product Meta widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Woo_Product_Meta' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Woo - Product Meta widget belongs to.
	 *
	 * @since 1.4.13.4
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Woo_Product_Meta' );
	}

	/**
	 * Retrieve the list of styles the Woo - Product Meta depended on.
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
	 * Register Woo - Product Meta widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_product_meta_style',
			[
				'label' => __( 'Style', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wc_style_warning',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => __( 'The style of this widget is often affected by your theme and plugins. If you experience any such issue, try to switch to a basic theme and deactivate related plugins.', 'powerpack' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'view',
			[
				'label' => __( 'View', 'powerpack' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => [
					'table' => __( 'Table', 'powerpack' ),
					'stacked' => __( 'Stacked', 'powerpack' ),
					'inline' => __( 'Inline', 'powerpack' ),
				],
				'prefix_class' => 'pp-woo-meta--view-',
			]
		);

		$this->add_responsive_control(
			'space_between',
			[
				'label' => __( 'Space Between', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}}:not(.pp-woo-meta--view-inline) .product_meta .detail-container:not(:last-child)' => 'padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}}:not(.pp-woo-meta--view-inline) .product_meta .detail-container:not(:first-child)' => 'margin-top: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}}.pp-woo-meta--view-inline .product_meta .detail-container' => 'margin-right: calc({{SIZE}}{{UNIT}}/2); margin-left: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}}.pp-woo-meta--view-inline .product_meta' => 'margin-right: calc(-{{SIZE}}{{UNIT}}/2); margin-left: calc(-{{SIZE}}{{UNIT}}/2)',
					'body:not(.rtl) {{WRAPPER}}.pp-woo-meta--view-inline .detail-container:after' => 'right: calc( (-{{SIZE}}{{UNIT}}/2) + (-{{divider_weight.SIZE}}px/2) )',
					'body:not.rtl {{WRAPPER}}.pp-woo-meta--view-inline .detail-container:after' => 'left: calc( (-{{SIZE}}{{UNIT}}/2) - ({{divider_weight.SIZE}}px/2) )',
				],
			]
		);

		$this->add_control(
			'divider',
			[
				'label' => __( 'Divider', 'powerpack' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Off', 'powerpack' ),
				'label_on' => __( 'On', 'powerpack' ),
				'selectors' => [
					'{{WRAPPER}} .product_meta .detail-container:not(:last-child):after' => 'content: ""',
				],
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'divider_style',
			[
				'label' => __( 'Style', 'powerpack' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'solid' => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
					'dashed' => __( 'Dashed', 'powerpack' ),
				],
				'default' => 'solid',
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}:not(.pp-woo-meta--view-inline) .product_meta .detail-container:not(:last-child):after' => 'border-top-style: {{VALUE}}',
					'{{WRAPPER}}.pp-woo-meta--view-inline .product_meta .detail-container:not(:last-child):after' => 'border-left-style: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'divider_weight',
			[
				'label' => __( 'Weight', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 1,
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}:not(.pp-woo-meta--view-inline) .product_meta .detail-container:not(:last-child):after' => 'border-top-width: {{SIZE}}{{UNIT}}; margin-bottom: calc(-{{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}}.pp-woo-meta--view-inline .product_meta .detail-container:not(:last-child):after' => 'border-left-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'divider_width',
			[
				'label' => __( 'Width', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'default' => [
					'unit' => '%',
				],
				'condition' => [
					'divider' => 'yes',
					'view!' => 'inline',
				],
				'selectors' => [
					'{{WRAPPER}} .product_meta .detail-container:not(:last-child):after' => 'width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'divider_height',
			[
				'label' => __( 'Height', 'powerpack' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'default' => [
					'unit' => '%',
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'condition' => [
					'divider' => 'yes',
					'view' => 'inline',
				],
				'selectors' => [
					'{{WRAPPER}} .product_meta .detail-container:not(:last-child):after' => 'height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label' => __( 'Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ddd',
				'scheme' => [
					'type' => Schemes\Color::get_type(),
					'value' => Schemes\Color::COLOR_3,
				],
				'condition' => [
					'divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .product_meta .detail-container:not(:last-child):after' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'heading_text_style',
			[
				'label' => __( 'Text', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'selector' => '{{WRAPPER}}',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'heading_link_style',
			[
				'label' => __( 'Link', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'link_typography',
				'selector' => '{{WRAPPER}} a',
			]
		);

		$this->add_control(
			'link_color',
			[
				'label' => __( 'Color', 'powerpack' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_product_meta_captions',
			[
				'label' => __( 'Captions', 'powerpack' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_category_caption',
			[
				'label' => __( 'Category', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'category_caption_single',
			[
				'label' => __( 'Singular', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'Category', 'powerpack' ),
			]
		);

		$this->add_control(
			'category_caption_plural',
			[
				'label' => __( 'Plural', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'Categories', 'powerpack' ),
			]
		);

		$this->add_control(
			'heading_tag_caption',
			[
				'label' => __( 'Tag', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tag_caption_single',
			[
				'label' => __( 'Singular', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'Tag', 'powerpack' ),
			]
		);

		$this->add_control(
			'tag_caption_plural',
			[
				'label' => __( 'Plural', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'Tags', 'powerpack' ),
			]
		);

		$this->add_control(
			'heading_sku_caption',
			[
				'label' => __( 'SKU', 'powerpack' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sku_caption',
			[
				'label' => __( 'SKU', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'SKU', 'powerpack' ),
			]
		);

		$this->add_control(
			'sku_missing_caption',
			[
				'label' => __( 'Missing', 'powerpack' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'N/A', 'powerpack' ),
			]
		);

		$this->end_controls_section();
	}

	private function get_plural_or_single( $single, $plural, $count ) {
		return 1 === $count ? $single : $plural;
	}

	protected function render() {
		do_action( 'pp_woo_builder_widget_before_render', $this );
		global $product;

		$product = wc_get_product();

		if ( Plugin::instance()->editor->is_edit_mode() ) {
			echo wp_kses_post( PP_Woo_Helper::get_instance()->default( $this->get_name() ) );
		} else {
			if ( empty( $product ) ) {
				return;
			}

			$sku = $product->get_sku();

			$settings = $this->get_settings_for_display();
			$sku_caption = ! empty( $settings['sku_caption'] ) ? $settings['sku_caption'] : __( 'SKU', 'powerpack' );
			$sku_missing = ! empty( $settings['sku_missing_caption'] ) ? $settings['sku_missing_caption'] : __( 'N/A', 'powerpack' );
			$category_caption_single = ! empty( $settings['category_caption_single'] ) ? $settings['category_caption_single'] : __( 'Category', 'powerpack' );
			$category_caption_plural = ! empty( $settings['category_caption_plural'] ) ? $settings['category_caption_plural'] : __( 'Categories', 'powerpack' );
			$tag_caption_single = ! empty( $settings['tag_caption_single'] ) ? $settings['tag_caption_single'] : __( 'Tag', 'powerpack' );
			$tag_caption_plural = ! empty( $settings['tag_caption_plural'] ) ? $settings['tag_caption_plural'] : __( 'Tags', 'powerpack' );
			?>
			<div class="product_meta">

				<?php do_action( 'woocommerce_product_meta_start' ); ?>

				<?php if ( wc_product_sku_enabled() && ( $sku || $product->is_type( 'variable' ) ) ) : ?>
					<span class="sku_wrapper detail-container"><span class="detail-label"><?php echo esc_html( $sku_caption ); ?></span> <span class="sku"><?php echo $sku ? wp_kses_post( $sku ) : esc_html( $sku_missing ); ?></span></span>
				<?php endif; ?>

				<?php if ( count( $product->get_category_ids() ) ) : ?>
					<span class="posted_in detail-container"><span class="detail-label"><?php echo esc_html( $this->get_plural_or_single( $category_caption_single, $category_caption_plural, count( $product->get_category_ids() ) ) ); ?></span> <span class="detail-content"><?php echo get_the_term_list( $product->get_id(), 'product_cat', '', ', ' ); ?></span></span>
				<?php endif; ?>

				<?php if ( count( $product->get_tag_ids() ) ) : ?>
					<span class="tagged_as detail-container"><span class="detail-label"><?php echo esc_html( $this->get_plural_or_single( $tag_caption_single, $tag_caption_plural, count( $product->get_tag_ids() ) ) ); ?></span> <span class="detail-content"><?php echo get_the_term_list( $product->get_id(), 'product_tag', '', ', ' ); ?></span></span>
				<?php endif; ?>

				<?php do_action( 'woocommerce_product_meta_end' ); ?>

			</div>
			<?php
		}
		do_action( 'pp_woo_builder_widget_after_render', $this );
	}

	public function render_plain_content() {}
}
