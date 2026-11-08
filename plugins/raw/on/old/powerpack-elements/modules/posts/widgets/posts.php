<?php
namespace PowerpackElements\Modules\Posts\Widgets;

// use PowerpackElements\Base\Powerpack_Widget;

use PowerpackElements\Modules\Posts\Skins;

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
 * Posts Grid Widget
 */
class Posts extends Posts_Base {

	/**
	 * Retrieve posts grid widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Posts' );
	}

	/**
	 * Retrieve posts grid widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Posts' );
	}

	/**
	 * Retrieve posts grid widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Posts' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Posts' );
	}

	/**
	 * Register Skins.
	 *
	 * @since 2.2.7
	 * @access protected
	 */
	protected function register_skins() {
		$this->add_skin( new Skins\Skin_Classic( $this ) );
		$this->add_skin( new Skins\Skin_Card( $this ) );
		$this->add_skin( new Skins\Skin_Checkerboard( $this ) );
		$this->add_skin( new Skins\Skin_Creative( $this ) );
		$this->add_skin( new Skins\Skin_Event( $this ) );
		$this->add_skin( new Skins\Skin_News( $this ) );
		$this->add_skin( new Skins\Skin_Overlap( $this ) );
		$this->add_skin( new Skins\Skin_Portfolio( $this ) );
		$this->add_skin( new Skins\Skin_Template( $this ) );
	}

	/**
	 * Register posts widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_skin_field',
			array(
				'label' => __( 'Layout', 'powerpack' ),
			)
		);

		$this->add_control(
			'templates',
			array(
				'label'       => __( 'Choose Template', 'powerpack' ),
				'type'        => 'pp-query',
				'label_block' => false,
				'multiple'    => false,
				'query_type'  => 'templates-all',
				'condition'   => array(
					'_skin' => 'template',
				),
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'     => __( 'Posts Per Page', 'powerpack' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6,
				'condition' => array(
					'query_type' => 'custom',
				),
			)
		);

		$this->end_controls_section();

		$this->register_query_section_controls( array(), 'posts', '', 'yes' );
	}
}
