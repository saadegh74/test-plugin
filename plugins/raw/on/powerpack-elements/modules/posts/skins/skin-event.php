<?php
namespace PowerpackElements\Modules\Posts\Skins;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Modules\Posts\Module;
use PowerpackElements\Classes\PP_Helper;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Event Skin for Posts widget
 */
class Skin_Event extends Skin_Base {

	/**
	 * Retrieve Skin ID.
	 *
	 * @access public
	 *
	 * @return string Skin ID.
	 */
	public function get_id() {
		return 'event';
	}

	/**
	 * Retrieve Skin title.
	 *
	 * @access public
	 *
	 * @return string Skin title.
	 */
	public function get_title() {
		return __( 'Event', 'powerpack' );
	}

	/**
	 * Register Control Actions.
	 *
	 * @access protected
	 */
	protected function _register_controls_actions() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		parent::_register_controls_actions();

		add_action( 'elementor/element/pp-posts/event_section_post_content_style/after_section_end', array( $this, 'add_event_date_controls' ) );
	}

	protected function register_image_controls() {
		parent::register_image_controls();

		$this->remove_control( 'thumbnail_location' );
	}

	protected function register_content_order() {
		parent::register_content_order();

		$this->remove_control( 'thumbnail_order' );
	}

	protected function register_style_content_controls() {
		parent::register_style_content_controls();

		$this->update_control(
			'post_content_align',
			array(
				'label'       => __( 'Alignment', 'powerpack' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => array(
					'left'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'     => 'center',
				'selectors'   => array(
					'{{WRAPPER}} .pp-post-content-wrap' => 'text-align: {{VALUE}};',
				),
			)
		);

	}

	public function add_event_date_controls() {

		$this->start_controls_section(
			'section_date_style',
			array(
				'label' => __( 'Date', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'date_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-event-date' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'date_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-event-date' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'date_typography',
				'label'    => __( 'Typography', 'powerpack' ),
				'selector' => '{{WRAPPER}} .pp-post-event-date',
			)
		);

		$this->add_responsive_control(
			'date_box_size',
			array(
				'label'      => __( 'Box Size', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-event-date' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; margin-top: calc(-{{SIZE}}{{UNIT}} / 2);',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render post thumbnail output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_post_thumbnail() {

		$image_wrap_tag = 'div';
		$image_wrap_key = 'image-wrap-' . get_the_ID();
		$image_link     = $this->get_instance_value( 'thumbnail_link' );
		$thumbnail_html = $this->get_post_thumbnail();

		if ( empty( $thumbnail_html ) ) {
			return;
		}

		$this->parent->add_render_attribute( $image_wrap_key, 'class', 'pp-post-thumbnail-wrap' );

		if ( 'yes' === $image_link ) {
			$image_link_atts   = array();
			$image_wrap_tag    = 'a';
			$image_link_target = $this->get_instance_value( 'thumbnail_link_target' );

			$image_link_atts['href'] = apply_filters( 'ppe_posts_image_link', get_the_permalink(), get_the_ID() );

			if ( 'yes' === $image_link_target ) {
				$image_link_atts['target'] = '_blank';
			}

			$image_link_atts['title'] = the_title_attribute( 'echo=0' );

			$image_link_atts = apply_filters( 'ppe_posts_image_link_atts', $image_link_atts, $image_link_atts );

			$this->parent->add_render_attribute( $image_wrap_key, $image_link_atts );
		}
		?>
		<div class="pp-post-thumbnail">
			<<?php PP_Helper::print_validated_html_tag( $image_wrap_tag ); ?> <?php $this->parent->print_render_attribute_string( $image_wrap_key ) ?>>
				<?php echo wp_kses_post( $thumbnail_html ); ?>
			</<?php PP_Helper::print_validated_html_tag( $image_wrap_tag ); ?>>

			<div class="pp-post-event-date">
				<?php
				if ( PP_Helper::is_tribe_events_post( get_the_ID() ) && function_exists( 'tribe_get_start_date' ) ) {
					$post_month = tribe_get_start_time( get_the_ID(), 'M' );
					$post_day = tribe_get_start_time( get_the_ID(), 'd' );
				} else {
					$post_month = get_the_date( 'M' );
					$post_day = get_the_date( 'd' );
				}
				?>
				<span class="pp-post-month">
					<?php echo wp_kses_post( $post_month ); ?>
				</span>
				<span class="pp-post-day">
					<?php echo wp_kses_post( $post_day ); ?>
				</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render post body output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_post_body() {
		$settings = $this->parent->get_settings_for_display();

		$post_terms         = $this->get_instance_value( 'post_terms' );
		$post_meta          = $this->get_instance_value( 'post_meta' );
		$thumbnail_location = $this->get_instance_value( 'thumbnail_location' );

		do_action( 'ppe_before_single_post_wrap', get_the_ID(), $settings );
		?>
		<div <?php post_class( $this->get_item_wrap_classes() ); ?>>
			<?php do_action( 'ppe_before_single_post', get_the_ID(), $settings ); ?>
			<div class="<?php echo esc_attr( $this->get_item_classes() ); ?>">
				<?php
					$this->render_post_thumbnail();
				?>

				<?php do_action( 'ppe_before_single_post_content', get_the_ID(), $settings ); ?>

				<div class="pp-post-content-wrap">
					<div class="pp-post-content">
						<?php
							$content_parts = $this->get_ordered_items( Module::get_post_parts() );

							foreach ( $content_parts as $part => $index ) {
								if ( 'terms' === $part ) {
									$this->render_terms();
								}

								if ( 'title' === $part ) {
									$this->render_post_title();
								}

								if ( 'meta' === $part ) {
									$this->render_post_meta();
								}

								if ( 'excerpt' === $part ) {
									$this->render_excerpt();
								}
							}
						?>
					</div>
					<?php
						if ( 'button' === $part ) {
							$this->render_button();
						}
					?>
				</div>

				<?php do_action( 'ppe_after_single_post_content', get_the_ID(), $settings ); ?>
			</div>
			<?php do_action( 'ppe_after_single_post', get_the_ID(), $settings ); ?>
		</div>
		<?php
		do_action( 'ppe_after_single_post_wrap', get_the_ID(), $settings );
	}
}
