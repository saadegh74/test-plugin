<?php
namespace PowerpackElements\Modules\Posts\Skins;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Modules\Posts\Module;

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
 * Card Skin for Posts widget
 */
class Skin_Card extends Skin_Base {

	/**
	 * Retrieve Skin ID.
	 *
	 * @access public
	 *
	 * @return string Skin ID.
	 */
	public function get_id() {
		return 'card';
	}

	/**
	 * Retrieve Skin title.
	 *
	 * @access public
	 *
	 * @return string Skin title.
	 */
	public function get_title() {
		return __( 'Card', 'powerpack' );
	}

	/**
	 * Register Control Actions.
	 *
	 * @access protected
	 */
	protected function _register_controls_actions() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		parent::_register_controls_actions();

		add_action( 'elementor/element/pp-posts/card_section_post_meta/before_section_end', array( $this, 'add_card_meta_controls' ) );
		add_action( 'elementor/element/pp-posts/card_section_meta_style/before_section_end', array( $this, 'add_card_meta_style_controls' ) );
	}

	public function add_card_meta_controls() {

		$this->add_control(
			'heading_author_avatar',
			array(
				'label'     => __( 'Author Avtar', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$this->get_control_id( 'post_meta' ) => 'yes',
				),
			)
		);

		$this->add_control(
			'author_avatar',
			array(
				'label'        => __( 'Show Author Avatar', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					$this->get_control_id( 'post_meta' ) => 'yes',
				),
			)
		);

		$this->add_control(
			'author_avatar_size',
			array(
				'label'     => __( 'Avatar Size', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'xs' => __( 'Extra Small', 'powerpack' ),
					'sm' => __( 'Small', 'powerpack' ),
					'md' => __( 'Medium', 'powerpack' ),
					'lg' => __( 'Large', 'powerpack' ),
					'xl' => __( 'Extra Large', 'powerpack' ),
				),
				'default'   => 'sm',
				'condition' => array(
					$this->get_control_id( 'post_meta' ) => 'yes',
					$this->get_control_id( 'author_avatar' ) => 'yes',
				),
			)
		);

	}

	protected function register_image_controls() {
		parent::register_image_controls();

		$this->remove_control( 'thumbnail_location' );
	}

	protected function register_content_order() {
		parent::register_content_order();

		$this->remove_control( 'meta_order' );
		$this->remove_control( 'thumbnail_order' );
	}

	public function add_card_meta_style_controls() {

		$this->add_control(
			'meta_border_color',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e6e6e6',
				'selectors' => array(
					'{{WRAPPER}} .pp-post-meta-wrap' => 'border-top-color: {{VALUE}}',
				),
				'condition' => array(
					$this->get_control_id( 'post_meta' ) => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'meta_border_width',
			array(
				'label'      => __( 'Border Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 1,
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-meta-wrap' => 'border-top-width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					$this->get_control_id( 'post_meta' ) => 'yes',
				),
			)
		);

		$this->add_control(
			'heading_post_author_avatar',
			array(
				'label'     => __( 'Author Avatar', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					$this->get_control_id( 'post_meta' ) => 'yes',
					$this->get_control_id( 'author_avatar' ) => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'author_avatar_image_width',
			array(
				'label'      => __( 'Image Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 240,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 40,
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-avtar img' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					$this->get_control_id( 'post_meta' ) => 'yes',
					$this->get_control_id( 'author_avatar' ) => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'author_avatar_image_spacing',
			array(
				'label'      => __( 'Image Spacing', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 80,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => 10,
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-avtar' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					$this->get_control_id( 'post_meta' ) => 'yes',
					$this->get_control_id( 'author_avatar' ) => 'yes',
				),
			)
		);

	}

	protected function register_excerpt_controls() {
		parent::register_excerpt_controls();

		$this->update_control(
			'show_excerpt',
			array(
				'label'        => __( 'Show Content', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
			)
		);
	}

	protected function register_style_content_controls() {
		parent::register_style_content_controls();

		$this->update_control(
			'post_content_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'    => '20',
					'right'  => '20',
					'bottom' => '20',
					'left'   => '20',
					'unit'   => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-content-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
	}

	protected function register_style_image_controls() {
		parent::register_style_image_controls();

		$this->remove_control( 'img_border_radius' );
		$this->remove_control( 'image_spacing' );
	}

	protected function register_style_excerpt_controls() {
		parent::register_style_excerpt_controls();

		$this->update_responsive_control(
			'excerpt_margin_bottom',
			array(
				'label'      => __( 'Margin Bottom', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'default'    => array(
					'size' => '',
				),
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-post-excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					$this->get_control_id( 'show_excerpt' ) => 'yes',
				),
			)
		);
	}

	protected function register_style_meta_controls() {
		parent::register_style_meta_controls();

		$this->remove_control( 'meta_margin_bottom' );
	}

	/**
	 * Render post meta output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_post_meta() {
		$settings           = $this->parent->get_settings_for_display();
		$post_meta          = $this->get_instance_value( 'post_meta' );
		$author_avatar      = $this->get_instance_value( 'author_avatar' );
		$author_avatar_size = $this->get_instance_value( 'author_avatar_size' );

		if ( 'yes' === $post_meta ) { ?>
			<?php do_action( 'ppe_before_single_post_meta', get_the_ID(), $settings ); ?>
			<div class="pp-post-meta-wrap">
				<?php if ( 'yes' === $author_avatar ) { ?>
					<div class="pp-post-avtar">
						<?php
							$avatar_size = $this->get_avatar_size( $author_avatar_size );

							echo get_avatar( get_the_author_meta( 'ID' ), $avatar_size );
						?>
					</div>
				<?php } ?>
				<div class="pp-post-meta">
					<?php
					$meta_items = $this->get_ordered_items( Module::get_meta_items() );

					foreach ( $meta_items as $meta_item => $index ) {
						if ( 'author' === $meta_item ) {
							// Post Author
							$this->render_meta_item( 'author' );
						}

						if ( 'date' === $meta_item ) {
							// Post Date
							$this->render_meta_item( 'date' );
						}

						if ( 'comments' === $meta_item ) {
							// Post Comments
							$this->render_meta_item( 'comments' );
						}
					}
					?>
				</div>
			</div>
			<?php
			do_action( 'ppe_after_single_post_meta', get_the_ID(), $settings );
		}
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
		$author_avatar      = $this->get_instance_value( 'author_avatar' );
		$author_avatar_size = $this->get_instance_value( 'author_avatar_size' );

		do_action( 'ppe_before_single_post_wrap', get_the_ID(), $settings );
		?>
		<div <?php post_class( $this->get_item_wrap_classes() ); ?>>
			<?php do_action( 'ppe_before_single_post', get_the_ID(), $settings ); ?>
			<div class="<?php echo esc_attr( $this->get_item_classes() ); ?>">
				<?php
					$this->render_post_thumbnail();
				?>

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

				<?php $this->render_post_meta(); ?>
			</div>
			<?php do_action( 'ppe_after_single_post', get_the_ID(), $settings ); ?>
		</div>
		<?php
		do_action( 'ppe_after_single_post_wrap', get_the_ID(), $settings );
	}
}
