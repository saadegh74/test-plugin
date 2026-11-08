<?php
/**
 * PPE Login Form.
 *
 * @package PPE
 */

namespace PowerpackElements\Modules\LoginForm\Widgets;

use PowerpackElements\Base\Powerpack_Widget;
use PowerpackElements\Classes\PP_Helper;
use PowerpackElements\Classes\PP_Config;
use PowerpackElements\Classes\PP_Admin_Settings;

use Elementor\Controls_Manager;
use Elementor\Control_Media;
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Core\Schemes\Color as Scheme_Color;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Login Form Widget
 */
class Login_Form extends Powerpack_Widget {

	/**
	 * Retrieve login form widget name.
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_name( 'Login_Form' );
	}

	/**
	 * Retrieve login form widget title.
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Login_Form' );
	}

	/**
	 * Retrieve login form widget icon.
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Login_Form' );
	}

	/**
	 * Retrieve login form widget keywords.
	 *
	 * @return array List of keywords
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Login_Form' );
	}

	/**
	 * Retrieve the list of scripts the login form widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'pp-login-form',
			'pp-google-login',
			'pp-google-recaptcha',
			'powerpack-frontend',
		);
	}

	/**
	 * Register login form widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 2.0.3
	 * @access protected
	 */
	protected function register_controls() {
		/**
		 * Content Tab: Form Fields
\		 */
		$this->start_controls_section(
			'section_fields_content',
			array(
				'label' => __( 'Form Fields', 'powerpack' ),
			)
		);

		$this->add_control(
			'show_labels',
			array(
				'label'   => __( 'Labels', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'default' => __( 'Default', 'powerpack' ),
					'custom'  => __( 'Custom', 'powerpack' ),
					''        => __( 'None', 'powerpack' ),
				),
				'default' => 'default',
			)
		);

		$this->add_control(
			'user_label',
			array(
				'label'       => __( 'Username Label', 'powerpack' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => __( ' Username or Email Address', 'powerpack' ),
				'condition'   => array(
					'show_labels' => 'custom',
				),
			)
		);

		$this->add_control(
			'user_placeholder',
			array(
				'label'       => __( 'Username Placeholder', 'powerpack' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => __( ' Username or Email Address', 'powerpack' ),
				'condition'   => array(
					'show_labels' => 'custom',
				),
			)
		);

		$this->add_control(
			'password_label',
			array(
				'label'       => __( 'Password Label', 'powerpack' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Password', 'powerpack' ),
				'condition'   => array(
					'show_labels' => 'custom',
				),
			)
		);

		$this->add_control(
			'password_placeholder',
			array(
				'label'       => __( 'Password Placeholder', 'powerpack' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Password', 'powerpack' ),
				'condition'   => array(
					'show_labels' => 'custom',
				),
			)
		);

		$this->add_control(
			'input_size',
			array(
				'label'   => __( 'Input Size', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'xs' => __( 'Extra Small', 'powerpack' ),
					'sm' => __( 'Small', 'powerpack' ),
					'md' => __( 'Medium', 'powerpack' ),
					'lg' => __( 'Large', 'powerpack' ),
					'xl' => __( 'Extra Large', 'powerpack' ),
				),
				'default' => 'sm',
			)
		);

		$this->add_control(
			'show_remember_me',
			array(
				'label'     => __( 'Remember Me', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_off' => __( 'Hide', 'powerpack' ),
				'label_on'  => __( 'Show', 'powerpack' ),
			)
		);

		$this->add_control(
			'remember_me_text',
			array(
				'label'     => __( 'Remember Me Text', 'powerpack' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Remember Me', 'powerpack' ),
				'condition' => array(
					'show_remember_me' => 'yes',
				),
			)
		);

		$this->add_control(
			'enable_ajax',
			array(
				'label'              => __( 'Enable AJAX Login', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'yes',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();

		/**
		 * Content Tab: Button
\		 */
		$this->start_controls_section(
			'section_button_content',
			array(
				'label' => __( 'Button', 'powerpack' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Text', 'powerpack' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Log In', 'powerpack' ),
			)
		);

		$this->add_control(
			'button_size',
			array(
				'label'   => __( 'Size', 'powerpack' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'xs' => __( 'Extra Small', 'powerpack' ),
					'sm' => __( 'Small', 'powerpack' ),
					'md' => __( 'Medium', 'powerpack' ),
					'lg' => __( 'Large', 'powerpack' ),
					'xl' => __( 'Extra Large', 'powerpack' ),
				),
				'default' => 'sm',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'        => __( 'Alignment', 'powerpack' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'start'   => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'end'     => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
					'stretch' => array(
						'title' => __( 'Justified', 'powerpack' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'prefix_class' => 'elementor%s-button-align-',
				'default'      => '',
			)
		);

		$this->end_controls_section();

		/**
		 * Content Tab: reCAPTCHA
		 */
		$this->start_controls_section(
			'section_recaptcha',
			array(
				'label' => __( 'reCAPTCHA', 'powerpack' ),
			)
		);

		$this->add_control(
			'enable_recaptcha',
			array(
				'label'              => __( 'Enable reCAPTCHA', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'no',
				'label_off'          => __( 'No', 'powerpack' ),
				'label_on'           => __( 'Yes', 'powerpack' ),
				'frontend_available' => true,
			)
		);

		if ( ! $this->is_recaptcha() ) {
			$this->add_control(
				'google_clientid_setting',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => PP_Helper::get_recaptcha_desc(),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition'       => array(
						'enable_recaptcha' => 'yes',
					),
				)
			);
		}

		if ( $this->is_recaptcha() ) {
			$this->add_control(
				'recaptcha_validate_type',
				array(
					'label'       => __( 'Validate Type', 'powerpack' ),
					'description' => __( 'Validate users with checkbox or in the background.<br />Note: Checkbox and Invisible types use seperate API keys.', 'powerpack' ),
					'type'        => Controls_Manager::SELECT,
					'multiple'    => true,
					'label_block' => true,
					'default'     => 'normal',
					'options'     => array(
						'normal'       => __( '"I\'m not a robot" checkbox (V2)', 'powerpack' ),
						'invisible'    => __( 'Invisible (V2)', 'powerpack' ),
						'invisible_v3' => __( 'Invisible (V3)', 'powerpack' ),
					),
					'condition'   => array(
						'enable_recaptcha' => 'yes',
					),
				)
			);

			$this->add_control(
				'recaptcha_theme',
				array(
					'label'     => __( 'Theme', 'powerpack' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'light',
					'options'   => array(
						'light' => __( 'Light', 'powerpack' ),
						'dark'  => __( 'Dark', 'powerpack' ),
					),
					'condition' => array(
						'enable_recaptcha' => 'yes',
					),
				)
			);
		}

		$this->end_controls_section();

		/**
		 * Content Tab: Social Login
		 */
		$this->start_controls_section(
			'section_social_login_content',
			array(
				'label' => __( 'Social Login', 'powerpack' ),
			)
		);

		$this->add_control(
			'facebook_login',
			array(
				'label'              => __( 'Enable Facebook Login', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => 'no',
				'label_off'          => __( 'No', 'powerpack' ),
				'label_on'           => __( 'Yes', 'powerpack' ),
				'frontend_available' => true,
			)
		);

		if ( ! $this->is_fb_data() ) {
			$this->add_control(
				'facebook_app_secret_setting',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'To use Facebook Login, you need to configure App ID and App Secret under <a href="%s" target="_blank">Integration Settings</a>', 'powerpack' ), PP_Admin_Settings::get_form_action( '&tab=integration' ) ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition'       => array(
						'facebook_login' => 'yes',
					),
				)
			);
		}

		if ( $this->is_fb_data() ) {
			$this->add_control(
				'facebook_login_label',
				array(
					'label'       => __( 'Facebook Button Text', 'powerpack' ),
					'label_block' => false,
					'type'        => Controls_Manager::TEXT,
					'dynamic'     => array(
						'active' => true,
					),
					'default'     => __( 'Facebook', 'powerpack' ),
					'condition'   => array(
						'facebook_login' => 'yes',
					),
				)
			);
		}

		$this->add_control(
			'google_login',
			array(
				'label'     => __( 'Enable Google Login', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'label_off' => __( 'No', 'powerpack' ),
				'label_on'  => __( 'Yes', 'powerpack' ),
			)
		);

		if ( ! $this->is_google_data() ) {
			$this->add_control(
				'google_client_id_setting',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %s admin link */
					'raw'             => sprintf( __( 'To use Google Login, you need to configure Google Client ID under <a href="%s" target="_blank">Integration Settings</a>', 'powerpack' ), PP_Admin_Settings::get_form_action( '&tab=integration' ) ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition'       => array(
						'google_login' => 'yes',
					),
				)
			);
		}

		if ( $this->is_google_data() ) {
			$this->add_control(
				'google_login_label',
				array(
					'label'       => __( 'Google Button Text', 'powerpack' ),
					'label_block' => false,
					'type'        => Controls_Manager::TEXT,
					'dynamic'     => array(
						'active' => true,
					),
					'default'     => __( 'Google', 'powerpack' ),
					'condition'   => array(
						'google_login' => 'yes',
					),
				)
			);
		}

		$this->add_control(
			'social_button_layout',
			array(
				'label'      => __( 'Layout', 'powerpack' ),
				'type'       => Controls_Manager::SELECT,
				'default'    => 'inline',
				'options'    => array(
					'inline'  => __( 'Inline', 'powerpack' ),
					'stacked' => __( 'Stacked', 'powerpack' ),
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'facebook_login',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'name'     => 'google_login',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'social_separator',
			array(
				'label'      => __( 'Use Separator', 'powerpack' ),
				'type'       => Controls_Manager::SWITCHER,
				'default'    => 'no',
				'label_off'  => __( 'No', 'powerpack' ),
				'label_on'   => __( 'Yes', 'powerpack' ),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'facebook_login',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'name'     => 'google_login',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'separator_text',
			array(
				'label'       => __( 'Separator Text', 'powerpack' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => __( 'Continue with', 'powerpack' ),
				'conditions'  => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'social_separator',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'facebook_login',
									'operator' => '==',
									'value'    => 'yes',
								),
								array(
									'name'     => 'google_login',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Content Tab: Additional Options
		 */
		$this->start_controls_section(
			'section_login_content',
			array(
				'label' => __( 'Additional Options', 'powerpack' ),
			)
		);

		$this->add_control(
			'password_toggle',
			array(
				'label'        => __( 'Password Visibility Toggle', 'powerpack' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'redirect_after_login',
			array(
				'label'     => __( 'Redirect After Login', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'label_off' => __( 'Off', 'powerpack' ),
				'label_on'  => __( 'On', 'powerpack' ),
			)
		);

		$this->add_control(
			'redirect_url',
			array(
				'type'          => Controls_Manager::URL,
				'dynamic'       => [
					'active'  => true,
				],
				'show_label'    => false,
				'show_external' => false,
				'separator'     => false,
				'placeholder'   => __( 'https://your-link.com', 'powerpack' ),
				'description'   => __( 'Note: Because of security reasons, you can ONLY use your current domain here.', 'powerpack' ),
				'condition'     => array(
					'redirect_after_login' => 'yes',
				),
			)
		);

		$this->add_control(
			'redirect_after_logout',
			array(
				'label'     => __( 'Redirect After Logout', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'label_off' => __( 'Off', 'powerpack' ),
				'label_on'  => __( 'On', 'powerpack' ),
			)
		);

		$this->add_control(
			'redirect_logout_url',
			array(
				'type'          => Controls_Manager::URL,
				'dynamic'       => [
					'active'  => true,
				],
				'show_label'    => false,
				'show_external' => false,
				'separator'     => false,
				'placeholder'   => __( 'https://your-link.com', 'powerpack' ),
				'description'   => __( 'Note: Because of security reasons, you can ONLY use your current domain here.', 'powerpack' ),
				'condition'     => array(
					'redirect_after_logout' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_lost_password',
			array(
				'label'     => __( 'Password Reset Link', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_off' => __( 'Hide', 'powerpack' ),
				'label_on'  => __( 'Show', 'powerpack' ),
			)
		);

		$this->add_control(
			'lost_password_text',
			array(
				'label'       => __( 'Lost Password Text', 'powerpack' ),
				'label_block' => false,
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => __( 'Lost your password?', 'powerpack' ),
				'condition'   => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->add_control(
			'lost_password_link',
			array(
				'label'     => __( 'Link to', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'default'  => __( 'Default', 'powerpack' ),
					'wp-login' => __( 'WordPress Login Page', 'powerpack' ),
					'custom'   => __( 'Custom URL', 'powerpack' ),
				),
				'default'   => 'default',
				'condition' => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->add_control(
			'lost_password_url',
			array(
				'label'     => __( 'Enter Custom URL', 'powerpack' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array(
					'active' => true,
				),
				'condition' => array(
					'show_lost_password' => 'yes',
					'lost_password_link' => 'custom',
				),
			)
		);

		if ( get_option( 'users_can_register' ) ) {
			$this->add_control(
				'show_register',
				array(
					'label'        => __( 'Register Link', 'powerpack' ),
					'descriptuion' => __( 'This option will only be available if the registration is enabled in WP admin general settings.', 'powerpack' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'label_off'    => __( 'Hide', 'powerpack' ),
					'label_on'     => __( 'Show', 'powerpack' ),
				)
			);

			$this->add_control(
				'register_text',
				array(
					'label'       => __( 'Register Link Text', 'powerpack' ),
					'label_block' => false,
					'type'        => Controls_Manager::TEXT,
					'dynamic'     => array(
						'active' => true,
					),
					'default'     => __( 'Register', 'powerpack' ),
					'condition'   => array(
						'show_register' => 'yes',
					),
				)
			);

			$this->add_control(
				'register_link',
				array(
					'label'     => __( 'Link to', 'powerpack' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'wp-register' => __( 'WordPress Register Page', 'powerpack' ),
						'custom'      => __( 'Custom URL', 'powerpack' ),
					),
					'default'   => 'wp-register',
					'condition' => array(
						'show_register' => 'yes',
					),
				)
			);
	
			$this->add_control(
				'register_url',
				array(
					'label'     => __( 'Enter Custom URL', 'powerpack' ),
					'type'      => Controls_Manager::URL,
					'dynamic'   => array(
						'active' => true,
					),
					'condition' => array(
						'show_register' => 'yes',
						'register_link' => 'custom',
					),
				)
			);
		}

		$this->add_control(
			'show_logged_in_message',
			array(
				'label'       => __( 'Logged in Message', 'powerpack' ),
				'description' => __( 'Message is visible only after the page is published and when the user is logged into the site.', 'powerpack' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'label_off'   => __( 'Hide', 'powerpack' ),
				'label_on'    => __( 'Show', 'powerpack' ),
			)
		);

		$this->add_control(
			'lost_password_form_heading',
			array(
				'label'       => __( 'Lost Password Form', 'powerpack' ),
				'type'        => Controls_Manager::HEADING,
				'separator'   => 'before',
				'condition'   => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->add_control(
			'redirect_after_lost_password',
			array(
				'label'     => __( 'Redirect After Password Reset', 'powerpack' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'label_off' => __( 'Off', 'powerpack' ),
				'label_on'  => __( 'On', 'powerpack' ),
				'condition'   => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->add_control(
			'redirect_lost_password',
			array(
				'type'          => Controls_Manager::URL,
				'dynamic'       => [
					'active'  => true,
				],
				'show_label'    => false,
				'show_external' => false,
				'separator'     => false,
				'placeholder'   => __( 'https://your-link.com', 'powerpack' ),
				'description'   => __( 'Note: Because of security reasons, you can ONLY use your current domain here.', 'powerpack' ),
				'condition'   => array(
					'redirect_after_lost_password' => 'yes',
				),
			)
		);

		$this->add_control(
			'lost_password_form_message',
			array(
				'label'       => __( 'Lost Password Form Message', 'powerpack' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => __( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'powerpack' ),
				'condition'   => array(
					'show_lost_password'            => 'yes',
					'redirect_after_lost_password!' => 'yes',
				),
			)
		);

		$this->add_control(
			'reset_password_button_text',
			array(
				'label'       => __( 'Reset Password Button Text', 'powerpack' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'default'     => __( 'Reset password', 'powerpack' ),
				'condition'   => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$help_docs = PP_Config::get_widget_help_links( 'Login_Form' );
		if ( ! empty( $help_docs ) ) {
			/**
			 * Content Tab: Docs Links
			 *
			 * @since 1.4.8
			 * @access protected
			 */
			$this->start_controls_section(
				'section_help_docs',
				array(
					'label' => __( 'Help Docs', 'powerpack' ),
				)
			);

			$hd_counter = 1;
			foreach ( $help_docs as $hd_title => $hd_link ) {
				$this->add_control(
					'help_doc_' . $hd_counter,
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'raw'             => sprintf( '%1$s ' . $hd_title . ' %2$s', '<a href="' . $hd_link . '" target="_blank" rel="noopener">', '</a>' ),
						'content_classes' => 'pp-editor-doc-links',
					)
				);

				$hd_counter++;
			}

			$this->end_controls_section();
		}

		/**
		 * Style Tab: Form
\		 */
		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'Form', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'row_gap',
			array(
				'label'     => __( 'Rows Gap', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 20,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'links_heading_style',
			array(
				'label'     => __( 'Links', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'links_align',
			array(
				'label'      => __( 'Alignment', 'powerpack' ),
				'type'       => Controls_Manager::CHOOSE,
				'options'    => array(
					'flex-start' => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'    => 'flex-start',
				'selectors'  => array(
					'{{WRAPPER}} .pp-login-form-links' => 'justify-content: {{VALUE}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'show_lost_password',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'name'     => 'show_register',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'links_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group > a' => 'color: {{VALUE}};',
				),
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				),
			)
		);

		$this->add_control(
			'links_hover_color',
			array(
				'label'     => __( 'Hover Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group > a:hover' => 'color: {{VALUE}};',
				),
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'links_typography',
				'selector' => '{{WRAPPER}} .elementor-field-group > a',
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Label
\		 */
		$this->start_controls_section(
			'section_style_labels',
			array(
				'label'     => __( 'Label', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_labels!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'label_spacing',
			array(
				'label'     => __( 'Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => '0',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors' => array(
					'body {{WRAPPER}} .elementor-field-group > label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-form-fields-wrapper label' => 'color: {{VALUE}};',
				),
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'selector' => '{{WRAPPER}} .elementor-form-fields-wrapper label',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_3,
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Fields
\		 */
		$this->start_controls_section(
			'section_field_style',
			array(
				'label' => __( 'Fields', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'field_typography',
				'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field, {{WRAPPER}} .elementor-field-subgroup label',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_3,
			)
		);

		$this->start_controls_tabs( 'tabs_fields_style' );

		$this->start_controls_tab(
			'tab_fields_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'field_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field' => 'color: {{VALUE}};',
				),
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				),
			)
		);

		$this->add_control(
			'field_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'field_border_style',
			array(
				'label'     => __( 'Border Style', 'powerpack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'none'   => __( 'None', 'powerpack' ),
					'solid'  => __( 'Solid', 'powerpack' ),
					'double' => __( 'Double', 'powerpack' ),
					'dotted' => __( 'Dotted', 'powerpack' ),
					'dashed' => __( 'Dashed', 'powerpack' ),
					'groove' => __( 'Groove', 'powerpack' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field' => 'border-style: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'field_border_color',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .elementor-field-group .elementor-select-wrapper::before' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'field_border_style!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'field_border_width',
			array(
				'label'       => __( 'Border Width', 'powerpack' ),
				'type'        => Controls_Manager::DIMENSIONS,
				'placeholder' => '1',
				'size_units'  => array( 'px' ),
				'selectors'   => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'   => array(
					'field_border_style!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'field_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'field_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field',
			)
		);

		$this->add_responsive_control(
			'field_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_fields_focus',
			array(
				'label' => __( 'Focus', 'powerpack' ),
			)
		);

		$this->add_control(
			'field_text_color_focus',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field:focus' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'field_background_color_focus',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field:focus' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'field_border_color_focus',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .elementor-field-group .elementor-field:focus' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'field_border_style!' => 'none',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'field_box_shadow_focus',
				'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field:focus',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style Tab: Button
\		 */
		$this->start_controls_section(
			'section_button_style',
			array(
				'label' => __( 'Button', 'powerpack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'scheme'   => Scheme_Typography::TYPOGRAPHY_4,
				'selector' => '{{WRAPPER}} .elementor-button',
			)
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			array(
				'label' => __( 'Normal', 'powerpack' ),
			)
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .elementor-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				),
				'selectors' => array(
					'{{WRAPPER}} .elementor-button' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .elementor-button',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-button',
			)
		);

		$this->add_responsive_control(
			'button_text_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			array(
				'label' => __( 'Hover', 'powerpack' ),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-button:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background_hover_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-button:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_border_color',
			array(
				'label'     => __( 'Border Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-button:hover' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'button_border_border!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_hover_box_shadow',
				'selector' => '{{WRAPPER}} .elementor-button:hover',
			)
		);

		$this->add_control(
			'button_hover_animation',
			array(
				'label' => __( 'Animation', 'powerpack' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		/**
		 * Style Tab: Social Login
\		 */
		$this->start_controls_section(
			'section_style_social',
			array(
				'label'      => __( 'Social Login', 'powerpack' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'facebook_login',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'name'     => 'google_login',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'social_buttons_align',
			array(
				'label'      => __( 'Alignment', 'powerpack' ),
				'type'       => Controls_Manager::CHOOSE,
				'options'    => array(
					'flex-start' => array(
						'title' => __( 'Left', 'powerpack' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'powerpack' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'powerpack' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'    => 'center',
				'selectors'  => array(
					'{{WRAPPER}} .pp-social-login-wrap' => 'justify-content: {{VALUE}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'facebook_login',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'name'     => 'google_login',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'social_button_type',
			array(
				'label'      => __( 'Button Type', 'powerpack' ),
				'type'       => Controls_Manager::SELECT,
				'options'    => array(
					'solid'       => __( 'Solid', 'powerpack' ),
					'transparent' => __( 'Transparent', 'powerpack' ),
					'custom'      => __( 'Custom', 'powerpack' ),
				),
				'default'    => 'solid',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'facebook_login',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'name'     => 'google_login',
							'operator' => '==',
							'value'    => 'yes',
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'social_buttons_width',
			array(
				'label'     => __( 'Button Width', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 20,
						'max' => 300,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'social_buttons_spacing',
			array(
				'label'     => __( 'Button Spacing', 'powerpack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button:not(:last-of-type)' => 'margin-right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'social_fb_button_heading_style',
			array(
				'label'     => __( 'Facebook', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'facebook_login'     => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->start_controls_tabs(
			'tabs_social_fb_button_style',
			array(
				'condition' => array(
					'facebook_login'     => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->start_controls_tab(
			'tab_social_fb_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'facebook_login'     => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'social_fb_button_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-fb-login-button' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-fb-login-button svg path' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'facebook_login'     => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'social_fb_button_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-fb-login-button' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'facebook_login'     => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_social_fb_button_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'facebook_login'     => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'social_fb_button_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-fb-login-button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-fb-login-button:hover svg path' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'facebook_login'     => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'social_fb_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-fb-login-button:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'facebook_login'     => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'social_google_button_heading_style',
			array(
				'label'     => __( 'Google', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'google_login'       => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->start_controls_tabs(
			'tabs_social_google_button_style',
			array(
				'condition' => array(
					'google_login'       => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->start_controls_tab(
			'tab_social_google_button_normal',
			array(
				'label'     => __( 'Normal', 'powerpack' ),
				'condition' => array(
					'google_login'       => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'social_google_button_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-google-login-button' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'google_login'       => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'social_google_button_bg_color',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-google-login-button' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'google_login'       => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_social_google_button_hover',
			array(
				'label'     => __( 'Hover', 'powerpack' ),
				'condition' => array(
					'google_login'       => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'social_google_button_color_hover',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-google-login-button:hover' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'google_login'       => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->add_control(
			'social_google_button_bg_color_hover',
			array(
				'label'     => __( 'Background Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-social-login-button.pp-google-login-button:hover' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'google_login'       => 'yes',
					'social_button_type' => 'custom',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'separator_heading_style',
			array(
				'label'      => __( 'Separator', 'powerpack' ),
				'type'       => Controls_Manager::HEADING,
				'separator'  => 'before',
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'social_separator',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'facebook_login',
									'operator' => '==',
									'value'    => 'yes',
								),
								array(
									'name'     => 'google_login',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'separator_color',
			array(
				'label'      => __( 'Separator Color', 'powerpack' ),
				'type'       => Controls_Manager::COLOR,
				'selectors'  => array(
					'{{WRAPPER}} .pp-login-form .pp-login-form-sep-text:after, {{WRAPPER}} .pp-login-form .pp-login-form-sep-text:before' => 'border-bottom-color: {{VALUE}};',
				),
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'social_separator',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'facebook_login',
									'operator' => '==',
									'value'    => 'yes',
								),
								array(
									'name'     => 'google_login',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'separator_text_color',
			array(
				'label'      => __( 'Separator Text Color', 'powerpack' ),
				'type'       => Controls_Manager::COLOR,
				'selectors'  => array(
					'{{WRAPPER}} .pp-login-form .pp-login-form-sep-text' => 'color: {{VALUE}};',
				),
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'social_separator',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'facebook_login',
									'operator' => '==',
									'value'    => 'yes',
								),
								array(
									'name'     => 'google_login',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'       => 'separator_text_typography',
				'selector'   => '{{WRAPPER}} .pp-login-form .pp-login-form-sep-text',
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'social_separator',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'facebook_login',
									'operator' => '==',
									'value'    => 'yes',
								),
								array(
									'name'     => 'google_login',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'separator_width',
			array(
				'label'      => __( 'Separator Width', 'powerpack' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pp-login-form .pp-login-form-sep-text:after, {{WRAPPER}} .pp-login-form .pp-login-form-sep-text:before' => 'width: {{SIZE}}{{UNIT}};',
				),
				'conditions' => array(
					'relation' => 'and',
					'terms'    => array(
						array(
							'name'     => 'social_separator',
							'operator' => '==',
							'value'    => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'facebook_login',
									'operator' => '==',
									'value'    => 'yes',
								),
								array(
									'name'     => 'google_login',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Logged in Message
\		 */
		$this->start_controls_section(
			'section_style_message',
			array(
				'label'     => __( 'Logged in Message', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_logged_in_message' => 'yes',
				),
			)
		);

		$this->add_control(
			'message_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .elementor-widget-container .elementor-login__logged-in-message' => 'color: {{VALUE}};',
				),
				'scheme'    => array(
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				),
				'condition' => array(
					'show_logged_in_message' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'message_typography',
				'selector'  => '{{WRAPPER}} .elementor-widget-container .elementor-login__logged-in-message',
				'scheme'    => Scheme_Typography::TYPOGRAPHY_3,
				'condition' => array(
					'show_logged_in_message' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		/**
		 * Style Tab: Lost Password Form
\		 */
		$this->start_controls_section(
			'section_style_lost_password_form',
			array(
				'label'     => __( 'Lost Password Form', 'powerpack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->add_control(
			'lost_password_form_message_style',
			array(
				'label'     => __( 'Lost Password Form Message', 'powerpack' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->add_control(
			'lost_password_form_message_color',
			array(
				'label'     => __( 'Text Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pp-login-form .pp-login-form--lost-pass-message' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'lost_password_form_message_typography',
				'selector'  => '{{WRAPPER}} .pp-login-form .pp-login-form--lost-pass-message',
				'condition' => array(
					'show_lost_password' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Get registration url
	 *
	 * @return string registration link
	 */
	public function get_registration_url() {
		$page_id = PP_Admin_Settings::get_option( 'pp_register_page', true );

		if ( empty( $page_id ) ) {
			return wp_registration_url();
		}

		return get_permalink( $page_id );
	}

	/**
	 * Check if reCaptcha is enabled
	 *
	 * @return bool
	 */
	public function is_recaptcha() {
		$recaptcha_v2_site_key = PP_Admin_Settings::get_option( 'pp_recaptcha_site_key' );
		// Get reCAPTCHA Secret Key from PP admin settings.
		$recaptcha_v2_secret_key = PP_Admin_Settings::get_option( 'pp_recaptcha_secret_key' );
		// Get reCAPTCHA V3 Site Key from PP admin settings.
		$recaptcha_v3_site_key = PP_Admin_Settings::get_option( 'pp_recaptcha_v3_site_key' );
		// Get reCAPTCHA V3 Secret Key from PP admin settings.
		$recaptcha_v3_secret_key = PP_Admin_Settings::get_option( 'pp_recaptcha_v3_secret_key' );

		if ( ( $recaptcha_v2_site_key && $recaptcha_v2_secret_key ) || ( $recaptcha_v3_site_key && $recaptcha_v3_secret_key ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if Facebook login data is entered
	 *
	 * @return bool
	 */
	public function is_fb_data() {
		// Get Facebook App ID from PP admin settings.
		$fb_app_id = PP_Admin_Settings::get_option( 'pp_fb_app_id' );
		// Get Facebook App Secret Key from PP admin settings.
		$fb_app_secret = PP_Admin_Settings::get_option( 'pp_fb_app_secret' );

		if ( $fb_app_id && $fb_app_secret ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if Google login data is entered
	 *
	 * @return bool
	 */
	public function is_google_data() {
		// Get Google Client ID from PP admin settings.
		$google_client_id = PP_Admin_Settings::get_option( 'pp_google_client_id' );

		if ( $google_client_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders reCAPTCHA field.
	 *
	 * @since 1.5.0
	 *
	 * @param string $instance_id   Unique module ID.
	 * @return void
	 */
	public function render_recaptcha_field( $instance_id ) {
		$settings  = $this->get_settings();
		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

		// Get reCAPTCHA Site Key from PP admin settings.
		$recaptcha_v2_site_key = PP_Admin_Settings::get_option( 'pp_recaptcha_site_key' );
		// Get reCAPTCHA V3 Site Key from PP admin settings.
		$recaptcha_v3_site_key = PP_Admin_Settings::get_option( 'pp_recaptcha_v3_site_key' );
		?>
		<div class="pp-login-form-field pp-field-group pp-field-type-recaptcha">
			<?php
			$id                      = $instance_id;
			$recaptcha_site_key      = 'invisible_v3' === $settings['recaptcha_validate_type'] ? $recaptcha_v3_site_key : $recaptcha_v2_site_key;
			$recaptcha_validate      = ( 'invisible_v3' === $settings['recaptcha_validate_type'] || 'invisible' === $settings['recaptcha_validate_type'] ) ? 'invisible' : $settings['recaptcha_validate_type'];
			$recaptcha_validate_type = ( 'invisible_v3' === $settings['recaptcha_validate_type'] ) ? 'invisible_v3' : $settings['recaptcha_validate_type'];
			$recaptcha_theme         = $settings['recaptcha_theme'];

			if ( $recaptcha_site_key ) {
				?>
				<div id="<?php echo esc_attr( $id ); ?>-pp-grecaptcha" class="pp-grecaptcha" data-sitekey="<?php echo wp_kses_post( $recaptcha_site_key ); ?>" data-validate="<?php echo wp_kses_post( $recaptcha_validate ); ?>"data-validate-type="<?php echo wp_kses_post( $recaptcha_validate_type ); ?>" data-theme="<?php echo wp_kses_post( $recaptcha_theme ); ?>"></div>
			<?php } else { ?>
				<?php if ( $is_editor ) { ?>
					<div class="pp-editor-placeholder"><?php echo wp_kses_post( PP_Helper::get_recaptcha_desc() ); ?></div>
				<?php } ?>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Render Social Media icons on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	private function render_social_login() {
		$settings         = $this->get_settings_for_display();
		$fb_app_id        = PP_Admin_Settings::get_option( 'pp_fb_app_id' );
		$google_client_id = PP_Admin_Settings::get_option( 'pp_google_client_id' );
		?>
		<?php if ( $this->is_fb_data() || $this->is_google_data() ) { ?>
			<?php if ( 'yes' === $settings['facebook_login'] || 'yes' === $settings['google_login'] ) { ?>
				<?php if ( 'yes' === $settings['social_separator'] ) { ?>
					<div class="pp-login-form-sep">
						<span class="pp-login-form-sep-text"><?php echo wp_kses_post( $settings['separator_text'] ); ?></span>
					</div>
					<?php } ?>
					<div class="pp-social-login-wrap pp-social-login--<?php echo wp_kses_post( $settings['social_button_type'] ); ?> pp-social-login--layout-<?php echo wp_kses_post( $settings['social_button_layout'] ); ?>">
						<?php if ( $this->is_fb_data() && 'yes' === $settings['facebook_login'] ) { ?>
							<div class="pp-fb-login-button pp-social-login-button" id="pp-fb-login-button" tabindex="0" role="button" data-appid="<?php echo esc_attr( $fb_app_id ); ?>">
								<span class="pp-social-login-icon">
									<svg xmlns="http://www.w3.org/2000/svg">
										<path d="M22.688 0H1.323C.589 0 0 .589 0 1.322v21.356C0 23.41.59 24 1.323 24h11.505v-9.289H9.693V11.09h3.124V8.422c0-3.1 1.89-4.789 4.658-4.789 1.322 0 2.467.1 2.8.145v3.244h-1.922c-1.5 0-1.801.711-1.801 1.767V11.1h3.59l-.466 3.622h-3.113V24h6.114c.734 0 1.323-.589 1.323-1.322V1.322A1.302 1.302 0 0 0 22.688 0z"></path>
									</svg>
								</span>
								<?php if ( $settings['facebook_login_label'] ) { ?>
									<span class="pp-social-login-label"><?php echo wp_kses_post( $settings['facebook_login_label'] ); ?></span>
								<?php } ?>
							</div>
						<?php } ?>
						<?php if ( $this->is_google_data() && 'yes' === $settings['google_login'] ) { ?>
							<div class="pp-google-login-button pp-social-login-button" id="pp-google-login-button" tabindex="0" role="button" data-clientid="<?php echo esc_attr( $google_client_id ); ?>">
								<span class="pp-social-login-icon">
									<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" class="pp-google-button-svg">
										<g>
											<path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
											<path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
											<path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
											<path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
											<path fill="none" d="M0 0h48v48H0z"></path>
										</g>
									</svg>
								</span>
								<?php if ( $settings['google_login_label'] ) { ?>
									<span class="pp-social-login-label"><?php echo wp_kses_post( $settings['google_login_label'] ); ?></span>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			<?php } ?>
		<?php
	}

	/**
	 * Render form fields attributes
	 *
	 * @access private
	 */
	private function form_fields_render_attributes() {
		$settings = $this->get_settings();

		if ( ! empty( $settings['button_size'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-size-' . $settings['button_size'] );
			$this->add_render_attribute( 'lost_password_button', 'class', 'elementor-size-' . $settings['button_size'] );
			$this->add_render_attribute( 'reset_password_button', 'class', 'elementor-size-' . $settings['button_size'] );
		}

		if ( $settings['button_hover_animation'] ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
			$this->add_render_attribute( 'lost_password_button', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
			$this->add_render_attribute( 'reset_password_button', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
		}

		$this->add_render_attribute(
			array(
				'wrapper'               => array(
					'class' => array(
						'elementor-form-fields-wrapper',
					),
				),
				'field-group'           => array(
					'class' => array(
						'elementor-field-type-text',
						'elementor-field-group',
						'elementor-column',
						'elementor-col-100',
					),
				),
				'password-field-group'  => array(
					'class' => array(
						'elementor-field-type-text',
						'elementor-field-group',
						'elementor-column',
						'elementor-col-100',
						'form-field-password',
					),
				),
				'submit-group'          => array(
					'class' => array(
						'elementor-field-group',
						'elementor-column',
						'elementor-field-type-submit',
						'elementor-col-100',
					),
				),
				'button'                => array(
					'class' => array(
						'elementor-button',
						'pp-submit-button',
					),
					'name'  => 'wp-submit',
				),
				'user_input'            => array(
					'type'        => 'text',
					'name'        => 'log',
					'id'          => 'user',
					'placeholder' => $settings['user_placeholder'],
					'class'       => array(
						'elementor-field',
						'elementor-field-textual',
						'elementor-size-' . $settings['input_size'],
					),
				),
				'password_input'        => array(
					'type'        => 'password',
					'name'        => 'pwd',
					'id'          => 'password',
					'placeholder' => $settings['password_placeholder'],
					'class'       => array(
						'elementor-field',
						'elementor-field-textual',
						'elementor-size-' . $settings['input_size'],
					),
				),
				'label_user'            => array(
					'for'   => 'user',
					'class' => 'elementor-field-label',
				),
				'label_password'        => array(
					'for'   => 'password',
					'class' => 'elementor-field-label',
				),
				'lost_password_button'  => array(
					'class' => array(
						'elementor-button',
						'pp-submit-button',
						'pp-lost-password-button',
					),
					'name'  => 'pp-login-form-lost-pw',
					'type'  => 'submit',
				),
				'lost_password_label'   => array(
					'for'   => 'user_login',
					'class' => 'elementor-field-label',
				),
				'lost_password_input'   => array(
					'type'         => 'text',
					'name'         => 'user_login',
					'id'           => 'user_login',
					'placeholder'  => $settings['user_placeholder'],
					'class'        => array(
						'elementor-field',
						'elementor-field-textual',
						'elementor-size-' . $settings['input_size'],
					),
					'size'         => '1',
					'autocomplete' => 'username',
				),
				'reset_password_1'      => array(
					'type'         => 'password',
					'name'         => 'password_1',
					'id'           => 'password_1',
					'placeholder'  => __( 'Enter New Password', 'powerpack' ),
					'class'        => array(
						'elementor-field',
						'elementor-field-textual',
						'elementor-size-' . $settings['input_size'],
					),
					'size'         => '1',
					'autocomplete' => 'new-password',
				),
				'reset_password_2'      => array(
					'type'         => 'password',
					'name'         => 'password_2',
					'id'           => 'password_2',
					'placeholder'  => __( 'Re-enter New Password', 'powerpack' ),
					'class'        => array(
						'elementor-field',
						'elementor-field-textual',
						'elementor-size-' . $settings['input_size'],
					),
					'size'         => '1',
					'autocomplete' => 'new-password',
				),
				'reset_password_button' => array(
					'class' => array(
						'elementor-button',
						'pp-submit-button',
						'pp-lost-password-button',
						'pp-login-form--lost-pass',
					),
					'name'  => 'pp-login-form-reset-pw',
					'type'  => 'submit',
				),
			)
		);

		if ( ! $settings['show_labels'] ) {
			$this->add_render_attribute( 'label', 'class', 'elementor-screen-only' );
		}

		if ( isset( $settings['password_toggle'] ) && 'yes' === $settings['password_toggle'] ) {
			$this->add_render_attribute( 'password-field-group', 'class', 'pp-lf-field-pw-toggle' );
		}

		$this->add_render_attribute( 'field-group', 'class', 'elementor-field-required' )
			->add_render_attribute( 'input', 'required', true )
			->add_render_attribute( 'input', 'aria-required', 'true' );

	}

	/**
	 * Get Lost Password Form
	 *
	 * Form asks for users email or username to send a password reset email to
	 * their registered email account.
	 *
	 * @since 2.1.0
	 * @access private
	 */
	private function get_lost_password_form( $settings, $id ) {
		do_action( 'pp_login_form_before_lost_password_form', $settings, $id );
		?>
		<form method="post" class="pp-login-form pp-login-form--lost-pass elementor-form">
			<div class="pp-login-form--lost-pass-message">
				<p>
					<?php
					$lost_password_form_message = ( $settings['lost_password_form_message'] ) ? $settings['lost_password_form_message'] : esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'powerpack' );
					$lost_password_form_message = apply_filters( 'pp_login_form_lost_password_message', $lost_password_form_message );
					echo wp_kses_post( $lost_password_form_message );
					?>
				</p><?php // @codingStandardsIgnoreLine ?>
			</div>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'wrapper' ) ); ?>>
				<div class="elementor-field-group elementor-column elementor-col-100">
					<?php
					if ( 'custom' === $settings['show_labels'] ) {
						echo '<label ' . wp_kses_post( $this->get_render_attribute_string( 'lost_password_label' ) ) . '>' . wp_kses_post( $settings['user_label'] ) . '</label>';
					} elseif ( 'default' === $settings['show_labels'] ) {
						echo '<label ' . wp_kses_post( $this->get_render_attribute_string( 'lost_password_label' ) ) . '>';
						echo esc_attr__( 'Username or Email Address', 'powerpack' );
						echo '</label>';
					}

					$current_url     = remove_query_arg( 'fake_arg' );
					$redirect_url    = $current_url;

					if ( 'yes' === $settings['redirect_after_lost_password'] && ! empty( $settings['redirect_lost_password']['url'] ) ) {
						$redirect_url = $settings['redirect_lost_password']['url'];
					}
					$is_redirect = ( 'yes' === $settings['redirect_after_lost_password'] ) ? 1 : 0;
					?>
					<input <?php echo wp_kses_post( $this->get_render_attribute_string( 'lost_password_input' ) ); ?> />
					<input type="hidden" name="lost_redirect_to" value="<?php echo esc_attr( $redirect_url ); ?>">
					<input type="hidden" name="is_lost_redirect" value="<?php echo esc_attr( $is_redirect ); ?>">
				</div>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'submit-group' ) ); ?>>
					<button <?php echo wp_kses_post( $this->get_render_attribute_string( 'lost_password_button' ) ); ?>>
						<span class="pp-login-form--button-text">
							<?php
							if ( $settings['reset_password_button_text'] ) {
								echo wp_kses_post( $settings['reset_password_button_text'] );
							} else {
								esc_html_e( 'Reset Password', 'powerpack' );
							}
							?>
						</span>
					</button>
				</div>
			</div>

			<?php wp_nonce_field( 'lost_password', 'pp-lf-lost-password-nonce' ); ?>
		</form>
		<?php
		do_action( 'pp_login_form_after_lost_password_form', $settings, $id );
	}

	/**
	 * Get Password Reset Form
	 *
	 * Form allows user to add new password. It appears after user has clicked password
	 * reset link from their email account.
	 *
	 * @since 2.1.0
	 * @access private
	 */

	private function get_reset_password_form( $settings, $id ) {
		defined( 'ABSPATH' ) || exit;

		$key        = esc_attr( wp_unslash( $_GET['key'] ) );
		$user_id    = esc_attr( wp_unslash( $_GET['id'] ) );
		$userdata   = get_userdata( absint( $user_id ) );
		$user_login = $userdata ? $userdata->user_login : '';

		$validate_reset_key = check_password_reset_key( $key, $user_login );
		if ( is_wp_error( $validate_reset_key ) ) {
			echo '<span class="pp-lf-error">' . esc_attr__( 'This key is invalid or has already been used. Please reset your password again if needed.', 'powerpack' ) . '</span>';
			return;
		}

		do_action( 'pp_login_form_before_reset_password_form', $settings, $id );
		?>
		<form method="post" class="pp-login-form pp-login-form--reset-pass">
			<p><?php echo apply_filters( 'pp_login_form_reset_password_message', esc_html__( 'Enter a password below.', 'powerpack' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'wrapper' ) ); ?>>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'field-group' ) ); ?>>
					<label for="password_1"><?php esc_html_e( 'New Password', 'powerpack' ); ?></label>
					<input <?php echo wp_kses_post( $this->get_render_attribute_string( 'reset_password_1' ) ); ?> />
				</div>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'field-group' ) ); ?>>
					<label for="password_2"><?php esc_html_e( 'Re-enter New Password', 'powerpack' ); ?></label>
					<input <?php echo wp_kses_post( $this->get_render_attribute_string( 'reset_password_2' ) ); ?> />
				</div>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'submit-group' ) ); ?>>
					<button <?php echo wp_kses_post( $this->get_render_attribute_string( 'reset_password_button' ) ); ?>>
						<span class="pp-login-form--button-text"><?php esc_html_e( 'Save', 'powerpack' ); ?></span>
					</button>
				</div>
			</div>

			<input type="hidden" name="reset_key" value="<?php echo esc_attr( $key ); ?>" />
			<input type="hidden" name="reset_login" value="<?php echo esc_attr( $user_login ); ?>" />

			<?php wp_nonce_field( 'reset_password', 'pp-lf-reset-password-nonce' ); ?>
		</form>
		<?php
		do_action( 'pp_login_form_after_reset_password_form', $settings, $id );
	}

	/**
	 * Render Login-Form output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function render() {
		$settings        = $this->get_settings_for_display();
		$id              = $this->get_id();
		$current_url     = remove_query_arg( 'fake_arg' );
		$redirect_url    = $current_url;
		$logout_redirect = $current_url;

		$action            = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
		$is_lost_password  = 'lost_pass' === $action || isset( $_GET['lost_pass'] );
		$is_reset_password = 'reset_pass' === $action || isset( $_GET['reset_pass'] );

		if ( 'yes' === $settings['redirect_after_login'] && ! empty( $settings['redirect_url']['url'] ) ) {
			$redirect_url = $settings['redirect_url']['url'];
		}

		if ( 'yes' === $settings['redirect_after_logout'] && ! empty( $settings['redirect_logout_url']['url'] ) ) {
			$logout_redirect = $settings['redirect_logout_url']['url'];
		}

		if ( is_user_logged_in() && ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			if ( 'yes' === $settings['show_logged_in_message'] ) {
				$current_user = wp_get_current_user();

				$user_name   = $current_user->display_name;
				$a_tag       = '<a href="' . esc_url( wp_logout_url( $logout_redirect ) ) . '">';
				$close_a_tag = '</a>';
				echo '<div class="elementor-login elementor-login__logged-in-message">';
				/* translators: %1$s user display name, %2$s: opening a tag, %3$s: closing a tag  */
				printf( esc_html__( 'You are Logged in as %1$s (%2$sLogout%3$s)', 'powerpack' ), wp_kses_post( $user_name ), wp_kses_post( $a_tag ), wp_kses_post( $close_a_tag ) );
				echo '</div>';
			}

			return;
		}

		$this->form_fields_render_attributes();
		?>
		<div class="pp-login-form-wrap" data-page-url="<?php echo esc_url( get_permalink() ); ?>">

		<?php if ( ! $is_lost_password && ! $is_reset_password ) { ?>

			<form class="pp-form pp-login-form elementor-form" id="pp-form-<?php echo esc_attr( $this->get_id() ); ?>" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>">
				<?php
				/**
				 * Hook to add custom content just after form opening tag.
				 *
				 * @since 2.2.2
				 *
				 * @param array $settings  Module settings.
				 */
				do_action( 'pp_login_form_start', $settings );
				?>
				<?php wp_nonce_field( 'pp_login_nonce', 'ppe-lf-login-nonce' ); ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_url ); ?>">
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'wrapper' ) ); ?>>
					<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'field-group' ) ); ?>>
						<?php
						if ( 'custom' === $settings['show_labels'] ) {
							echo '<label ' . wp_kses_post( $this->get_render_attribute_string( 'label_user' ) ) . '>' . wp_kses_post( $settings['user_label'] ) . '</label>';
						} elseif ( 'default' === $settings['show_labels'] ) {
							echo '<label ' . wp_kses_post( $this->get_render_attribute_string( 'label_user' ) ) . '>';
							echo esc_attr__( 'Username or Email Address', 'powerpack' );
							echo '</label>';
						}

						echo '<input size="1" ' . wp_kses_post( $this->get_render_attribute_string( 'user_input' ) ) . '>';
						?>
					</div>
					<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'password-field-group' ) ); ?>>
						<?php
						if ( 'custom' === $settings['show_labels'] ) {
							echo '<label ' . wp_kses_post( $this->get_render_attribute_string( 'label_password' ) ) . '>' . wp_kses_post( $settings['password_label'] ) . '</label>';
						} elseif ( 'default' === $settings['show_labels'] ) {
							echo '<label ' . wp_kses_post( $this->get_render_attribute_string( 'label_password' ) ) . '>';
							echo esc_attr__( 'Password', 'powerpack' );
							echo '</label>';
						}

						?>
						<div class="pp-lf-field-inner">
							<input size="1" <?php echo wp_kses_post( $this->get_render_attribute_string( 'password_input' ) ); ?> >
							<?php if ( 'yes' === $settings['password_toggle'] ) { ?>
								<button type="button" class="pp-lf-toggle-pw hide-if-no-js" aria-label="<?php esc_attr_e( 'Show password', 'powerpack' ); ?>">
									<span class="fa far fa-eye" aria-hidden="true"></span>
								</button>
							<?php } ?>
						</div>
					</div>

					<?php if ( 'yes' === $settings['show_remember_me'] ) : ?>
						<?php
						$remember_me_text = ( $settings['remember_me_text'] ) ? $settings['remember_me_text'] : __( 'Remember Me', 'powerpack' );
						?>
						<div class="elementor-field-type-checkbox elementor-field-group elementor-column elementor-col-100 elementor-remember-me">
							<label for="pp-login-remember-me">
								<input type="checkbox" id="elementor-login-remember-me" name="rememberme" value="forever">
								<span class="pp-login-remember-text"><?php echo esc_attr( $remember_me_text ); ?></span>
							</label>
						</div>
					<?php endif; ?>

					<?php
					// Render reCAPTCHA field.
					if ( 'yes' === $settings['enable_recaptcha'] ) {
						$this->render_recaptcha_field( $id );
					}
					?>

					<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'submit-group' ) ); ?>>
						<button type="submit" <?php echo wp_kses_post( $this->get_render_attribute_string( 'button' ) ); ?>>
							<?php if ( ! empty( $settings['button_text'] ) ) : ?>
								<span class="elementor-button-text"><?php echo wp_kses_post( $settings['button_text'] ); ?></span>
							<?php endif; ?>
						</button>
					</div>

					<?php
					$show_lost_password = 'yes' === $settings['show_lost_password'];
					$show_register      = get_option( 'users_can_register' ) && 'yes' === $settings['show_register'];

					if ( $show_lost_password || $show_register ) :
						$this->add_render_attribute( 'lost_pass_link', 'class', 'elementor-lost-password' );

						if ( 'wp-login' === $settings['lost_password_link'] ) {

							$this->add_render_attribute( 'lost_pass_link', 'href', wp_lostpassword_url() );

						} elseif ( 'custom' === $settings['lost_password_link'] && ! empty( $settings['lost_password_url']['url'] ) ) {

							$this->add_link_attributes( 'lost_pass_link', $settings['lost_password_url'] );

						} else {
							$this->add_render_attribute( 'lost_pass_link', 'href', esc_url( add_query_arg( 'lost_pass', '1' ) ) );
						}

						$this->add_render_attribute( 'register_link', 'class', 'elementor-register' );

						if ( isset( $settings['register_link'] ) && 'custom' === $settings['register_link'] && ! empty( $settings['register_url']['url'] ) ) {

							$this->add_link_attributes( 'register_link', $settings['register_url'] );

						} else {
							$this->add_render_attribute( 'register_link', 'href', esc_url( $this->get_registration_url() ) );
						}
						?>
						<div class="elementor-field-group elementor-column elementor-col-100 pp-login-form-links">
							<?php if ( $show_lost_password ) : ?>
								<a <?php echo wp_kses_post( $this->get_render_attribute_string( 'lost_pass_link' ) ); ?>>
									<?php echo ! empty( $settings['lost_password_text'] ) ? wp_kses_post( $settings['lost_password_text'] ) : esc_attr__( 'Lost your password?', 'powerpack' ); ?>
								</a>
							<?php endif; ?>

							<?php if ( $show_register ) : ?>
								<a <?php echo wp_kses_post( $this->get_render_attribute_string( 'register_link' ) ); ?>>
									<?php echo wp_kses_post( $settings['register_text'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php
					$this->render_social_login();
				?>

				<?php
				/**
				 * Hook to add custom content just before form closing tag.
				 *
				 * @since 2.2.2
				 *
				 * @param array $settings  Module settings.
				 */
				do_action( 'pp_login_form_end', $settings );
				?>
			</form>
			<?php
		} elseif ( $is_lost_password ) {
			$this->get_lost_password_form( $settings, $id );
		} elseif ( $is_reset_password ) {
			$this->get_reset_password_form( $settings, $id );
		}
		?>

		</div>
		<?php
	}
}
