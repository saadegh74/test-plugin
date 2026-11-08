<?php
namespace PowerpackElements\Modules\RegistrationForm;

use PowerpackElements\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Module extends Module_Base {
	/**
	 * Holds form fields with sanitized value.
	 *
	 * @since 1.5.0
	 * @access protected
	 * @var array $form_fields
	 */
	protected $form_fields = array();

	/**
	 * Module is active or not.
	 *
	 * @since 1.5.0
	 *
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_active() {
		return true;
	}

	/**
	 * Get Module Name.
	 *
	 * @since 1.5.0
	 *
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'pp-registration-form';
	}

	/**
	 * Get Widgets.
	 *
	 * @since 1.5.0
	 *
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array(
			'Registration_Form',
		);
	}

	/**
	 * Member Variable
	 *
	 * @var array mail content
	 */
	private static $email_content = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_ppe_register_user', array( $this, 'register_user' ) );
		add_action( 'wp_ajax_nopriv_ppe_register_user', array( $this, 'register_user' ) );
	}

	/**
	 * Register user - AJAX callback.
	 *
	 * @since 1.5.0
	 *
	 * @see wp_insert_user() For inserting user.
	 * @link https://core.trac.wordpress.org/browser/tags/5.3/src/wp-includes/user.php
	 *
	 * @see wp_signon() For auto sign-in.
	 * @link https://core.trac.wordpress.org/browser/tags/5.3/src/wp-includes/user.php
	 *
	 * @return void
	 */
	public function register_user() {
		check_ajax_referer( 'ppe-registration-nonce', 'security' );

		if ( ! get_option( 'users_can_register' ) ) {
			wp_send_json_error(
				array(
					'code'    => 'registration_disabled',
					'message' => __( 'Registration is disabled.', 'powerpack' ),
				)
			);
		}

		$userdata = $_POST;
		$response = array();

		// Get the form post data.
		$widget_id          = isset( $_POST['node_id'] ) ? wp_unslash( $_POST['node_id'] ) : false;
		$recaptcha_response = isset( $_POST['recaptcha_response'] ) ? $_POST['recaptcha_response'] : false;
		$elementor = \Elementor\Plugin::$instance;

		if ( $widget_id ) {
			$post_id = isset( $_POST['post_id'] ) ? wp_unslash( $_POST['post_id'] ) : false;

			$document = $elementor->documents->get( $widget_id );

			if ( ! empty( $document ) ) {
				$post_id = $document->get_main_id();
			}

			$meta      = $elementor->documents->get( $post_id )->get_elements_data();

			$widget_data = $this->find_element_recursive( $meta, $widget_id );

			if ( isset( $widget_data['templateID'] ) ) {
				$template_data = \Elementor\Plugin::$instance->templates_manager->get_template_data( [
					'source'      => 'local',
					'template_id' => $widget_data['templateID'],
				] );

				if ( is_array( $template_data ) && isset( $template_data['content'] ) ) {
					$widget_data = $template_data['content'][0];
				}
			}

			if ( null != $widget_data ) {
				$widget = $elementor->elements_manager->create_element_instance( $widget_data );

				$settings = $widget->get_settings_for_display();

				// Validate reCAPTCHA if enabled.
				if ( isset( $_POST['recaptcha'] ) && $recaptcha_response ) {
					// Get reCAPTCHA Site Key from PP admin settings.
					$recaptcha_v2_site_key = \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_recaptcha_site_key' );
					// Get reCAPTCHA Secret Key from PP admin settings.
					$recaptcha_v2_secret_key = \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_recaptcha_secret_key' );
					// Get reCAPTCHA V3 Site Key from PP admin settings.
					$recaptcha_v3_site_key = \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_recaptcha_v3_site_key' );
					// Get reCAPTCHA V3 Secret Key from PP admin settings.
					$recaptcha_v3_secret_key = \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_recaptcha_v3_secret_key' );

					$recaptcha_validate_type = $settings['recaptcha_validate_type'];
					$recaptcha_site_key      = 'invisible_v3' === $recaptcha_validate_type ? $recaptcha_v3_site_key : $recaptcha_v2_site_key;
					$recaptcha_secret_key    = 'invisible_v3' === $recaptcha_validate_type ? $recaptcha_v3_secret_key : $recaptcha_v2_secret_key;

					if ( ! empty( $recaptcha_secret_key ) && ! empty( $recaptcha_site_key ) ) {
						if ( version_compare( phpversion(), '5.3', '>=' ) ) {
							$validate = new \PP_ReCaptcha( $recaptcha_secret_key, $recaptcha_validate_type, $recaptcha_response );
							if ( ! $validate->is_success() ) {
								wp_send_json_error(
									array(
										'code'    => 'recaptcha',
										'message' => __( 'Error verifying reCAPTCHA, please try again.', 'powerpack' ),
									)
								);
							}
						} else {
							wp_send_json_error(
								array(
									'code'    => 'recaptcha_php_ver',
									'message' => __( 'reCAPTCHA API requires PHP version 5.3 or above.', 'powerpack' ),
								)
							);
						}
					} else {
						wp_send_json_error(
							array(
								'code'    => 'recaptcha_missing_key',
								'message' => __( 'Your reCAPTCHA Site or Secret Key is missing!', 'powerpack' ),
							)
						);
					}
				}

				// Validate username.
				if ( ! isset( $userdata['user_login'] ) || empty( $userdata['user_login'] ) ) {
					$username = $this->create_username( $userdata['user_email'], $userdata );

					if ( is_wp_error( $username ) ) {
						wp_send_json_error(
							array(
								'code'    => 'username_wp_error',
								'message' => $username->get_error_message(),
							)
						);
					} else {
						$userdata['user_login'] = $username;
					}
				} elseif ( ! validate_username( $userdata['user_login'] ) ) {
					wp_send_json_error(
						array(
							'code'    => 'invalid_username',
							'message' => __( 'This username is invalid because it uses illegal characters. Please enter a valid username.', 'powerpack' ),
						)
					);
				} elseif ( username_exists( $userdata['user_login'] ) ) {
					wp_send_json_error(
						array(
							'code'    => 'username_exists',
							'message' => __( 'This username is already registered. Please choose another one.', 'powerpack' ),
						)
					);
				}

				// Validate email.
				if ( ! isset( $userdata['user_email'] ) || empty( $userdata['user_email'] ) ) {
					wp_send_json_error(
						array(
							'code'    => 'empty_email',
							'message' => __( 'Please type your email address.', 'powerpack' ),
						)
					);
				} elseif ( ! is_email( $userdata['user_email'] ) ) {
					wp_send_json_error(
						array(
							'code'    => 'invalid_email',
							'message' => __( 'The email address isn&#8217;t correct!', 'powerpack' ),
						)
					);
				} elseif ( email_exists( $userdata['user_email'] ) ) {
					wp_send_json_error(
						array(
							'code'    => 'email_exists',
							'message' => __( 'The email is already registered, please choose another one.', 'powerpack' ),
						)
					);
				}

				// Validate Password.
				if ( ! isset( $userdata['user_pass'] ) || empty( $userdata['user_pass'] ) ) {
					$userdata['user_pass'] = wp_generate_password();
				} else {
					if ( false !== strpos( wp_unslash( $userdata['user_pass'] ), '\\' ) ) {
						wp_send_json_error(
							array(
								'code'    => 'password',
								'message' => __( 'Password must not contain the character "\\"', 'powerpack' ),
							)
						);
					}
				}

				// Validate and match confirm password.
				if ( isset( $userdata['confirm_user_pass'] ) && ! empty( $userdata['confirm_user_pass'] ) ) {
					$password_hash   = md5( $userdata['user_pass'] );
					$c_password_hash = md5( $userdata['confirm_user_pass'] );

					if ( $c_password_hash !== $password_hash ) {
						wp_send_json_error(
							array(
								'code'    => 'password_mismatch',
								'message' => __( 'Password does not match.', 'powerpack' ),
							)
						);
					}
				}

				// Validate website.
				if ( isset( $userdata['user_url'] ) && ! empty( $userdata['user_url'] ) ) {
					$url = esc_url_raw( $userdata['user_url'] );
					if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
						wp_send_json_error(
							array(
								'code'    => 'invalid_url',
								'message' => __( 'URL seems to be invalid.', 'powerpack' ),
							)
						);
					}
				}

				unset( $userdata['action'] );
				unset( $userdata['security'] );

				// set form data.
				$userdata        = stripslashes_deep( $userdata );
				$this->form_data = $userdata;

				// build user data.
				$user_fields = apply_filters(
					'pp_rf_user_fields',
					array(
						'user_login',
						'user_email',
						'user_pass',
						'first_name',
						'last_name',
						'user_url',
					)
				);

				$user_args = array();

				foreach ( $user_fields as $user_field ) {
					if ( isset( $this->form_data[ $user_field ] ) ) {
						$user_args[ $user_field ] = $this->form_data[ $user_field ];
					}
				}

				// add user role.
				$user_args['role'] = empty( $settings['user_role'] ) ? get_option( 'default_role' ) : $settings['user_role'];

				// user activation.
				$auto_login = ( ! empty( $settings['actions_array'] ) && is_array( $settings['actions_array'] ) && in_array( 'auto_login', $settings['actions_array'], true ) );

				// insert user.
				$user_id = wp_insert_user( $user_args );

				// if error occurres while inserting user, stop and send error message.
				if ( is_wp_error( $user_id ) ) {
					wp_send_json_error(
						array(
							'code'    => 'user_wp_error',
							'message' => $user_id->get_error_message(),
						)
					);
				}

				/**
				 * Fires immediately after a new user is registered.
				 *
				 * @since 1.5.0
				 *
				 * @param int       $user_id    User ID.
				 * @param array     $userdata   User data.
				 * @param object    $settings   Module settings.
				 */
				do_action( 'pp_rf_user_register', $user_id, $userdata, $settings );

				// auto login based on module settings and redirect to homepage.
				if ( ! is_user_logged_in() && $auto_login ) {
					$login_creds = array(
						'user_login'    => $this->form_data['user_login'],
						'user_password' => $this->form_data['user_pass'],
						'remember'      => true,
					);

					$login_response = wp_signon( $login_creds, false );

					$response['auto_login'] = 'yes';
				}

				// set form fields.
				$this->set_fields( $settings, $widget, $widget_id );
				// set metadata.
				$this->set_meta( $settings );

				// send email.
				$send_email_enabled = false;
				if ( ( ! empty( $settings['actions_array'] ) && is_array( $settings['actions_array'] ) && in_array( 'send_email', $settings['actions_array'], true ) ) ) {
					$send_email_enabled = true;
				} elseif ( 'send_email' === $settings['actions_array'] ) {
					$send_email_enabled = true;
				}

				if ( $send_email_enabled ) {
					$response['email'] = $this->send_email( $settings, $widget );
				}

				// redirect.
				$redirect_enabled = false;
				if ( ( ! empty( $settings['actions_array'] ) && is_array( $settings['actions_array'] ) && in_array( 'redirect', $settings['actions_array'], true ) ) ) {
					$redirect_enabled = true;
				} elseif ( 'redirect' === $settings['actions_array'] ) {
					$redirect_enabled = true;
				}

				if ( $redirect_enabled && ! empty( $settings['redirect_url']['url'] ) ) {
					if ( filter_var( $settings['redirect_url']['url'], FILTER_VALIDATE_URL ) ) {
						$response['redirect_url'] = $settings['redirect_url']['url'];
					}
				}
			}

			wp_send_json_success( $response );
		}
	}

	/**
	 * Create Username.
	 *
	 * Creates a username based on email address.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param string $email New user email address.
	 * @param array  $new_user_args {
	 *   An array of user data.
	 *
	 *      @type string $user_login    Generated username.
	 *      @type string $user_pass     The user password.
	 *      @type string $user_email    The user email address.
	 *      @type string $user_url      The user website URL.
	 *      @type string $first_name    The user first name.
	 *      @type string $last_name     The user last name.
	 * }
	 * @param string $suffix A suffix to be applied after generated username.
	 * @return string   $email or $username
	 */
	private function create_username( $email, $new_user_args = array(), $suffix = '' ) {
		/**
		 * Make email as username.
		 *
		 * Use filter pp_rf_use_email_as_username to override this.
		 *
		 * @since 1.5.0
		 *
		 * @param bool true|false
		 */
		if ( apply_filters( 'pp_rf_use_email_as_username', true ) ) {
			return $email;
		}

		$username_parts = array();

		if ( isset( $new_user_args['first_name'] ) ) {
			$username_parts[] = sanitize_user( $new_user_args['first_name'], true );
		}

		if ( isset( $new_user_args['last_name'] ) ) {
			$username_parts[] = sanitize_user( $new_user_args['last_name'], true );
		}

		// Remove empty parts.
		$username_parts = array_filter( $username_parts );

		// If there are no parts, e.g. name had unicode chars, or was not provided, fallback to email.
		if ( empty( $username_parts ) ) {
			$email_parts    = explode( '@', $email );
			$email_username = $email_parts[0];

			// Exclude common prefixes.
			if ( in_array(
				$email_username,
				array(
					'sales',
					'hello',
					'mail',
					'contact',
					'info',
				),
				true
			) ) {
				// Get the domain part.
				$email_username = $email_parts[1];
			}

			$username_parts[] = sanitize_user( $email_username, true );
		}

		$username = mb_strtolower( implode( '.', $username_parts ) );

		if ( $suffix ) {
			$username .= $suffix;
		}

		/**
		 * WordPress 4.4 - filters the list of blacklisted usernames.
		 *
		 * @param array $usernames Array of blacklisted usernames.
		 * @
		 */
		$illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );

		// Stop illegal logins and generate a new random username.
		if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegal_logins ), true ) ) {
			$new_args = array();

			/**
			 * Filter generated custom username.
			 *
			 * @param string $username      Generated username.
			 * @param string $email         New user email address.
			 * @param array  $new_user_args Array of new user args, maybe including first and last names.
			 * @param string $suffix        Append string to username to make it unique.
			 */
			$new_args['first_name'] = apply_filters(
				'pp_rf_generated_username',
				'pp_user_' . zeroise( wp_rand( 0, 9999 ), 4 ),
				$email,
				$new_user_args,
				$suffix
			);

			return $this->create_username( $email, $new_args, $suffix );
		}

		if ( username_exists( $username ) ) {
			// Generate something unique to append to the username in case of a conflict with another user.
			$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
			return $this->create_username( $email, $new_user_args, $suffix );
		}

		/**
		 * Filter new customer username.
		 *
		 * @param string $username      Username.
		 * @param string $email         New user email address.
		 * @param array  $new_user_args Array of new user args, maybe including first and last names.
		 * @param string $suffix        Append string to username to make it unique.
		 */
		return apply_filters( 'pp_rf_new_username', $username, $email, $new_user_args, $suffix );
	}

	/**
	 * Set fields.
	 *
	 * Build form fields data to have this while replacing
	 * merge tags in form fields.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param object $settings    Module settings.
	 * @param object $widget      Widget object.
	 * @param string $widget_id   Module ID.
	 * @return void
	 */
	private function set_fields( $settings, $widget, $widget_id ) {
		$form_fields = $settings['form_fields'];

		foreach ( $form_fields as $form_field ) {
			$field_name = $form_field['field_type'];

			$field = array(
				'id'        => 'field-' . $form_field['_id'],
				'type'      => $form_field['field_type'],
				'name'      => $field_name,
				'label'     => $form_field['field_label'],
				'value'     => '',
				'raw_value' => '',
				'required'  => 'yes' === $form_field['required'],
			);

			if ( isset( $this->form_data[ $field_name ] ) ) {
				$field['raw_value'] = $this->form_data[ $field_name ];

				$value = $field['raw_value'];

				if ( is_array( $value ) ) {
					$value = implode( ', ', $value );
				}

				$field['value'] = $this->sanitize_field( $field, $value );
			}

			$this->form_fields[ $field_name ] = $field;
		}
	}

	/**
	 * Set meta.
	 *
	 * Set meta information of the user who is registering
	 * via the form.
	 *
	 * @since 1.5.0
	 *
	 * @param object $settings  Module settings.
	 * @return void
	 */
	private function set_meta( $settings ) {
		$this->form_meta['date']       = array(
			'label' => __( 'Date', 'powerpack' ),
			'value' => date_i18n( get_option( 'date_format' ) ),
		);
		$this->form_meta['time']       = array(
			'label' => __( 'Time', 'powerpack' ),
			'value' => date_i18n( get_option( 'time_format' ) ),
		);
		$this->form_meta['page_url']   = array(
			'label' => __( 'Page URL', 'powerpack' ),
			'value' => $this->form_data['referrer'],
		);
		$this->form_meta['user_agent'] = array(
			'label' => __( 'User Agent', 'powerpack' ),
			'value' => $_SERVER['HTTP_USER_AGENT'],
		);
		$this->form_meta['remote_ip']  = array(
			'label' => __( 'Remote IP', 'powerpack' ),
			'value' => \PowerpackElements\Classes\PP_Helper::get_client_ip(),
		);
	}

	/**
	 * Send email
	 *
	 * Send a notification email to registered user and admin.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param object $settings  Module settings.
	 * @param object $widget    Widget object.
	 * @return $response
	 */
	private function send_email( $settings, $widget ) {
		$response = array(
			'code'    => '',
			'message' => '',
			'error'   => false,
		);

		// Get site info.
		$site_info = $widget->get_site_info();
		// Admin email.
		$admin_email = $site_info['admin_email'];
		// Site name.
		$blogname = $site_info['blogname'];
		// From email.
		$from_email = ! empty( $settings['email_from'] ) && is_email( $settings['email_from'] ) ? $settings['email_from'] : $admin_email;

		// User email fields.
		$email_fields = array(
			'email_to'            => $this->form_data['user_email'],
			// translators: %s: New message.
			'email_subject'       => ! empty( $settings['email_subject'] ) ? $settings['email_subject'] : sprintf( __( 'Registration Successful - %s', 'powerpack' ), $blogname ),
			'email_content'       => wpautop( $settings['email_content'] ),
			'email_from_name'     => ! empty( $settings['email_from_name'] ) ? $settings['email_from_name'] : $blogname,
			'email_from'          => $from_email,
			'email_reply_to'      => $from_email,
			'admin_email_to'      => isset( $settings['admin_email_to'] ) && is_email( $settings['admin_email_to'] ) ? $settings['admin_email_to'] : $admin_email,
			// translators: %s: New message.
			'admin_email_subject' => isset( $settings['admin_email_subject'] ) && ! empty( $settings['admin_email_subject'] ) ? $settings['admin_email_subject'] : __( 'New User Registration', 'powerpack' ),
			'admin_email_content' => isset( $settings['admin_email_content'] ) ? wpautop( $settings['admin_email_content'] ) : '',
		);

		foreach ( $email_fields as $key => $value ) {
			$value = trim( $value );
			$value = $this->replace_tags( $value, $widget );
			if ( ! empty( $value ) ) {
				$email_fields[ $key ] = $value;
			}
		}

		// User email content.
		$email_fields['email_content'] = $this->replace_content_tags( $email_fields['email_content'] );

		// Admin email content.
		$email_fields['admin_email_content'] = $this->replace_content_tags( $email_fields['admin_email_content'] );

		// Admin email metadata.
		$email_meta     = '';
		$email_metadata = empty( $settings['email_metadata'] ) ? array() : $settings['email_metadata'];

		foreach ( $this->form_meta as $id => $field ) {
			if ( in_array( $id, $email_metadata, true ) ) {
				$email_meta .= $this->field_formatted( $field ) . '<br>';
			}
		}

		if ( ! empty( $email_meta ) ) {
			$email_fields['admin_email_content'] .= '<br>' . '---' . '<br>' . '<br>' . $email_meta;
		}

		// translators: %s: email_from_name.
		$headers = sprintf( 'From: %s <%s>' . "\r\n", $email_fields['email_from_name'], $email_fields['email_from'] );
		// translators: %s: email_reply_to.
		$headers .= sprintf( 'Reply-To: %s' . "\r\n", $email_fields['email_reply_to'] );
		$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

		// Send email to user.
		$email_sent = wp_mail( $email_fields['email_to'], $email_fields['email_subject'], $email_fields['email_content'], $headers );

		$response['sent_user_email'] = $email_sent;

		// Send email to admin.
		if ( isset( $settings['enable_admin_email'] ) && 'yes' === $settings['enable_admin_email'] ) {
			// translators: %s: site name and admin email.
			$admin_headers = sprintf( 'From: %s <%s>' . "\r\n", $blogname, $admin_email );
			// translators: %s: admin email.
			$admin_headers .= sprintf( 'Reply-To: %s' . "\r\n", $admin_email );
			$admin_headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

			$admin_email_sent = wp_mail( $email_fields['admin_email_to'], $email_fields['admin_email_subject'], $email_fields['admin_email_content'], $admin_headers );

			$response['sent_admin_email'] = $admin_email_sent;
		}

		/**
		 * Fires just after the notification email sent.
		 *
		 * @since 1.5.0
		 *
		 * @param object $settings  Module settings.
		 */
		do_action( 'pp_rf_email_sent', $settings );

		if ( ! $email_sent || ( isset( $admin_email_sent ) && ! $admin_email_sent ) ) {
			$response['code']    = 'email_failed';
			$response['message'] = __( 'An error occurred sending email!', 'powerpack' );
			$response['error']   = true;
		}

		return $response;
	}

	/**
	 * Sanitize field.
	 *
	 * Sanitizes the field value.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param array  $field  An array of field data.
	 * @param string $value  Field value.
	 *
	 * @return string   $value  Sanitized field value.
	 */
	private function sanitize_field( $field, $value ) {
		$field_type = $field['type'];

		switch ( $field_type ) {
			case 'text':
			case 'password':
			case 'hidden':
			case 'search':
			case 'checkbox':
			case 'radio':
			case 'select':
				$value = sanitize_text_field( $value );
				break;
			case 'url':
				$value = esc_url_raw( $value );
				break;
			case 'textarea':
				$value = sanitize_textarea_field( $value );
				break;
			case 'email':
				$value = sanitize_email( $value );
				break;
			default:
				$value = apply_filters( "pp_rf_sanitize_{$field_type}", $value, $field );
		}

		return $value;
	}

	/**
	 * Replace tags.
	 *
	 * Replaces merge tags in fields with their original value.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param string $field_value  Field value.
	 * @param Object $widget       Widget object.
	 * @return string   $value     Original field value.
	 */
	private function replace_tags( $field_value, $widget ) {
		$site_info = $widget->get_site_info();

		return preg_replace_callback(
			'/{{(.*?)}}/i',
			function( $matches ) use ( $site_info ) {
				$value = $matches[0];

				if ( isset( $this->form_fields[ $matches[1] ] ) ) {
					$value = $this->form_fields[ $matches[1] ]['value'];
				} elseif ( isset( $site_info[ $matches[1] ] ) ) {
					$value = $site_info[ $matches[1] ];
				}

				return $value;
			},
			$field_value
		);
	}

	/**
	 * Replace content tags.
	 *
	 * Replaces merge tags in email content field with their value.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param string $email_content     Email content.
	 *
	 * @return string $email_content    Email content with actual value.
	 */
	private function replace_content_tags( $email_content ) {
		$email_content = do_shortcode( $email_content );

		$all_fields_tag = '{{all-fields}}';

		if ( false !== strpos( $email_content, $all_fields_tag ) ) {
			$text = '<br>';
			foreach ( $this->form_fields as $field ) {
				$text .= $this->field_formatted( $field ) . '<br>';
			}

			$email_content = str_replace( $all_fields_tag, $text, $email_content );
		}

		return $email_content;
	}

	/**
	 * Field formatted.
	 *
	 * Returns field value with it's label.
	 *
	 * @since 1.5.0
	 * @access private
	 *
	 * @param array $field          An array of field data.
	 *
	 * @return string $formatted    Field label with value.
	 */
	private function field_formatted( $field ) {
		$formatted = '';
		if ( ! empty( $field['label'] ) ) {
			// translators: %s: Field Label.
			$formatted = sprintf( '%s: %s', $field['label'], $field['value'] );
		} elseif ( ! empty( $field['value'] ) ) {
			// translators: %s: Value.
			$formatted = sprintf( '%s', $field['value'] );
		}

		return $formatted;
	}

	/**
	 * Get Widget Setting data.
	 *
	 * @since 1.5.0
	 * @access public
	 * @param array  $elements Element array.
	 * @param string $form_id Element ID.
	 * @return Boolean True/False.
	 */
	public function find_element_recursive( $elements, $form_id ) {

		foreach ( $elements as $element ) {
			if ( $form_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = $this->find_element_recursive( $element['elements'], $form_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}
}
