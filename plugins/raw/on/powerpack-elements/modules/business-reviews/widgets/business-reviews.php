<?php
namespace PowerpackElements\Modules\BusinessReviews\Widgets;

use PowerpackElements\Base\Powerpack_Widget;

use PowerpackElements\Modules\BusinessReviews\Skins;

// Elementor Classes
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Icons_Manager;
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
 * Business Reviews Widget
 */
class Business_Reviews extends Powerpack_Widget {

	/**
	 * Has Template content
	 *
	 * @var _has_template_content
	 */
	protected $_has_template_content = false; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Retrieve business reviews widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Business_Reviews' );
	}

	/**
	 * Retrieve business reviews widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Business_Reviews' );
	}

	/**
	 * Retrieve business reviews widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Business_Reviews' );
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
		return parent::get_widget_keywords( 'Business_Reviews' );
	}

	/**
	 * Retrieve the list of styles the business reviews widget depended on.
	 *
	 * Used to set style dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_style_depends() {
		if ( Icons_Manager::is_migration_allowed() ) {
			return [
				'elementor-icons-fa-solid',
			];
		}
		return [];
	}

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.13.0
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'swiper',
			'pp-business-reviews',
		);
	}

	/**
	 * Register Skins.
	 *
	 * @access protected
	 */
	protected function register_skins() {
		$this->add_skin( new Skins\Skin_Classic( $this ) );
		$this->add_skin( new Skins\Skin_Card( $this ) );
	}

	/**
	 * Register widget controls
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function register_controls() {

		$this->register_content_business_reviews_controls();
		$this->register_content_layout_controls();
		$this->register_content_carousel_controls();
		$this->register_content_filters_controls();
	}

	/**
	 * Register Business Reviews Controls.
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_content_business_reviews_controls() {
		$this->start_controls_section(
			'section_business_reviews',
			array(
				'label' => __( 'Business Reviews', 'powerpack' ),
			)
		);

		$admin_link = '';
		if ( ! $this->get_google_places_api() ) {
			$this->add_control(
				'google_erorr_msg',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'To display Google Places reviews, you must have a Google Maps API key. <a href="%s" target="_blank" rel="noopener">Click here</a> to setup your Google Maps API key.', 'powerpack' ), $admin_link ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition'       => array(
						'reviews_source!' => 'yelp',
					),
				)
			);
		}

		if ( ! $this->get_yelp_api() ) {
			$this->add_control(
				'yelp_erorr_msg',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'To display Yelp reviews, you must have a Yelp API key. <a href="%s" target="_blank" rel="noopener">Click here</a> to setup your Yelp API key.', 'powerpack' ), $admin_link ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition'       => array(
						'reviews_source!' => 'google',
					),
				)
			);
		}

		$this->add_control(
			'reviews_source',
			array(
				'label'        => __( 'Reviews Source', 'powerpack' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'google',
				'options'      => array(
					'google' => __( 'Google Places', 'powerpack' ),
					'yelp'   => __( 'Yelp', 'powerpack' ),
					'all'    => __( 'Google + Yelp', 'powerpack' ),
				),
				'render_type'  => 'template',
				'prefix_class' => 'pp-social-reviews-',
			)
		);

			$this->add_control(
				'google_place_id',
				array(
					'label'       => __( 'Google Place ID', 'powerpack' ),
					'description' => sprintf( __( 'Click %1$s here %2$s to get your Google Place ID.', 'powerpack' ), '<a href="https://developers.google.com/places/place-id/" target="_blank" rel="noopener">', '</a>' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'dynamic'     => array(
						'active' => true,
					),
					'default'     => __( 'ChIJw____96GhYARCVVwg5cT7c0', 'powerpack' ),
					'ai'          => [
						'active' => false,
					],
					'condition'   => array(
						'reviews_source!' => 'yelp',
					),
				)
			);

			$this->add_control(
				'language_id',
				array(
					'label'       => __( 'Language Code', 'powerpack' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'dynamic'     => array(
						'active' => true,
					),
					'ai'          => [
						'active' => false,
					],
					'condition'   => array(
						'reviews_source!' => 'yelp',
					),
				)
			);

			$this->add_control(
				'language_code_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'Click %1$s here %2$s to check your Language code.', 'powerpack' ), '<a href="https://developers.google.com/admin-sdk/directory/v1/languages" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'pp-editor-info',
					'condition'       => array(
						'reviews_source!' => 'yelp',
					),
				)
			);

			$this->add_control(
				'all_separator',
				array(
					'type'      => Controls_Manager::DIVIDER,
					'condition' => array(
						'reviews_source' => 'all',
					),
				)
			);

			$this->add_control(
				'yelp_business_id',
				array(
					'label'       => __( 'Yelp Business ID', 'powerpack' ),
					'description' => sprintf( __( 'Click %1$s here %2$s to get your Yelp Business ID.', 'powerpack' ), '<a href="#" target="_blank" rel="noopener">', '</a>' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'dynamic'     => array(
						'active' => true,
					),
					'default'     => 'golden-gate-bridge-san-francisco',
					'ai'          => [
						'active' => false,
					],
					'condition'   => array(
						'reviews_source!' => 'google',
					),
				)
			);

			$this->add_control(
				'reviews_refresh_time',
				array(
					'label'   => __( 'Refresh Reviews after', 'powerpack' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'day',
					'options' => array(
						'hour'  => __( 'Hour', 'powerpack' ),
						'day'   => __( 'Day', 'powerpack' ),
						'week'  => __( 'Week', 'powerpack' ),
						'month' => __( 'Month', 'powerpack' ),
						'year'  => __( 'Year', 'powerpack' ),
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Business Reviews Layout Controls.
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_content_layout_controls() {
		$this->start_controls_section(
			'section_content_layout',
			array(
				'label' => __( 'Layout', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'layout',
				array(
					'label'   => __( 'Layout', 'powerpack' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'normal',
					'options' => array(
						'normal'   => __( 'Grid', 'powerpack' ),
						'carousel' => __( 'Carousel', 'powerpack' ),
					),
				)
			);

			$this->add_control(
				'google_reviews_count',
				array(
					'label'     => __( 'Number of Reviews', 'powerpack' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 3,
					'min'       => 1,
					'max'       => 5,
					'condition' => array(
						'reviews_source' => 'google',
					),
				)
			);

		
			$this->add_control(
				'google_max_reviews',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'Google allows maximum 5 reviews. Click <a href="%s" target="_blank" rel="noopener">here</a> to know more.', 'powerpack' ), '#' ),
					'content_classes' => 'pp-editor-info',
					'condition'       => array(
						'reviews_source' => 'google',
					),
				)
			);
		

			$this->add_control(
				'yelp_reviews_count',
				array(
					'label'     => __( 'Number of Reviews', 'powerpack' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 3,
					'min'       => 1,
					'max'       => 3,
					'condition' => array(
						'reviews_source' => 'yelp',
					),
				)
			);

			$this->add_control(
				'yelp_max_reviews',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'Yelp allows maximum 3 reviews. Click <a href="%s" target="_blank" rel="noopener">here</a> to know more.', 'powerpack' ), '#' ),
					'content_classes' => 'pp-editor-info',
					'condition'       => array(
						'reviews_source' => 'yelp',
					),
				)
			);
		
			$this->add_control(
				'reviews_count',
				array(
					'label'     => __( 'Number of Reviews', 'powerpack' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 3,
					'min'       => 1,
					'max'       => 8,
					'condition' => array(
						'reviews_source' => 'all',
					),
				)
			);

			$this->add_control(
				'all_review_length_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'We can fetch only up to 5 Google and 3 Yelp Reviews. So, a maximum of 8 reviews can be displayed. Click <a href="%s" target="_blank" rel="noopener">here</a> to know more.', 'powerpack' ), '#' ),
					'content_classes' => 'pp-editor-info',
					'condition'       => array(
						'reviews_source' => 'all',
					),
				)
			);

			$this->add_responsive_control(
				'columns',
				array(
					'label'          => __( 'Columns', 'powerpack' ),
					'type'           => Controls_Manager::SELECT,
					'default'        => '3',
					'tablet_default' => '2',
					'mobile_default' => '1',
					'options'        => array(
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
					),
					'prefix_class'   => 'elementor-grid%s-',
					'render_type'    => 'template',
				)
			);

			$this->add_control(
				'equal_height',
				array(
					'label'        => __( 'Equal Height', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'no',
					'prefix_class' => 'pp-reviews-equal-height-',
					'render_type'  => 'template',
				)
			);

			$this->add_control(
				'help_doc_equal_height',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( 'Note: This option sets an equal height for all the reviews content boxes. It takes the height of the longest review and applies it to the other reviews.', 'powerpack' ),
					'content_classes' => 'pp-editor-info',
					'condition'       => array(
						'_skin'        => 'bubble',
						'equal_height' => 'yes',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Business Reviews carousel controls.
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_content_carousel_controls() {

		$this->start_controls_section(
			'section_slider_settings',
			array(
				'label'     => __( 'Carousel Settings', 'powerpack' ),
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'slider_speed',
			[
				'label'       => esc_html__( 'Transition Duration', 'powerpack' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 500,
				'condition'   => array(
					'layout' => 'carousel',
				),
			]
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => __( 'Autoplay', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'separator'    => 'before',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label'       => esc_html__( 'Autoplay Speed', 'powerpack' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 3000,
				'condition'   => array(
					'layout'   => 'carousel',
					'autoplay' => 'yes',
				),
			]
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'label'                 => __( 'Pause on Hover', 'powerpack' ),
				'description'           => '',
				'type'                  => Controls_Manager::SWITCHER,
				'default'               => '',
				'label_on'              => __( 'Yes', 'powerpack' ),
				'label_off'             => __( 'No', 'powerpack' ),
				'return_value'          => 'yes',
				'frontend_available'    => true,
				'condition'             => array(
					'layout'   => 'carousel',
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'pause_on_interaction',
			array(
				'label'              => __( 'Pause on Interaction', 'powerpack' ),
				'description'        => __( 'Disables autoplay completely on first interaction with the carousel.', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'frontend_available' => true,
				'condition'          => array(
					'layout'   => 'carousel',
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'infinite_loop',
			array(
				'label'        => __( 'Infinite Loop', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'name_navigation_heading',
			array(
				'label'     => __( 'Navigation', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'        => __( 'Arrows', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'        => __( 'Pagination', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'powerpack' ),
				'label_off'    => __( 'No', 'powerpack' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->add_control(
			'pagination_type',
			array(
				'label'     => __( 'Pagination Type', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bullets',
				'options'   => array(
					'bullets'  => __( 'Dots', 'powerpack' ),
					'fraction' => __( 'Fraction', 'powerpack' ),
				),
				'condition' => array(
					'layout'     => 'carousel',
					'pagination' => 'yes',
				),
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'     => __( 'Direction', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => [
					'auto'  => __( 'Auto', 'powerpack' ),
					'left'  => __( 'Left', 'powerpack' ),
					'right' => __( 'Right', 'powerpack' ),
				],
				'separator' => 'before',
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Business Reviews filters.
	 *
	 * @since 2.8.0
	 * @access protected
	 */
	protected function register_content_filters_controls() {
		$this->start_controls_section(
			'section_filters_controls',
			array(
				'label' => __( 'Filters', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'reviews_filter_by',
				array(
					'label'       => __( 'Filter By', 'powerpack' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => false,
					'default'     => 'rating',
					'options'     => array(
						'default' => 'None',
						'rating'  => 'Minimum Rating',
						'date'    => 'Review Date',
					),
				)
			);

			$this->add_control(
				'reviews_min_rating',
				array(
					'label'       => __( 'Minimum Rating', 'powerpack' ),
					'type'        => Controls_Manager::SELECT,
					'label_block' => false,
					'default'     => 'no',
					'options'     => array(
						'no' => 'No Minimum Rating',
						'2'  => '2 star',
						'3'  => '3 star',
						'4'  => '4 star',
						'5'  => '5 star',
					),
				)
			);

			$this->add_control(
				'reviews_min_rating_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( 'Display reviews of ratings greater than or equal to minimum rating. For example, choosing 3 star will skip the reviews with less than 3 rating from displaying.', 'powerpack' ),
					'content_classes' => 'pp-editor-info',
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Get Google Places API from PowerPack options.
	 *
	 * @since 2.8.0
	 * @return string
	 */
	public function get_google_places_api() {
		return \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_google_places_api_key' );
	}

	/**
	 * Get Yelp API from PowerPack options.
	 *
	 * @since 2.8.0
	 * @return string
	 */
	public function get_yelp_api() {
		return \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_yelp_api_key' );
	}
}
