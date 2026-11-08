<?php
namespace PowerpackElements\Modules\LoginForm;

use PowerpackElements\Base\Module_Base;
use PowerpackElements\Classes\PP_ReCaptcha;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Module extends Module_Base {

	/**
	 * Form error
	 *
	 * @var $form_error
	 */
	public $form_error = false;

	/**
	 * Get Module Name
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'pp-login-form';
	}

	/**
	 * Get Widgets
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array(
			'Login_Form',
		);
	}

	/**
	 * Construct
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_ppe_lf_process_login', array( $this, 'process_login' ) );
		add_action( 'wp_ajax_nopriv_ppe_lf_process_login', array( $this, 'process_login' ) );
		add_action( 'wp_ajax_pp_lf_process_social_login', array( $this, 'process_social_login' ) );
		add_action( 'wp_ajax_nopriv_pp_lf_process_social_login', array( $this, 'process_social_login' ) );
		add_action( 'wp_ajax_pp_lf_process_lost_pass', array( $this, 'process_lost_password' ) );
		add_action( 'wp_ajax_nopriv_pp_lf_process_lost_pass', array( $this, 'process_lost_password' ) );
		add_action( 'wp_ajax_pp_lf_process_reset_pass', array( $this, 'process_reset_password' ) );
		add_action( 'wp_ajax_nopriv_pp_lf_process_reset_pass', array( $this, 'process_reset_password' ) );
	}

	/**
	 * Process the login form.
	 *
	 * @throws Exception On login error.
	 */
	public function process_login() {
		if (
			! isset( $_POST['ppe-lf-login-nonce'] ) ||
			! wp_verify_nonce( wp_unslash( $_POST['ppe-lf-login-nonce'] ), 'pp_login_nonce' ) ) {
				wp_send_json_error( __( 'Invalid data.', 'powerpack' ) );
		}

		$recaptcha_response = isset( $_POST['recaptcha_response'] ) ? $_POST['recaptcha_response'] : false;

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

			$recaptcha_validate = sanitize_text_field( $_POST['recaptcha_validate'] );
			$recaptcha_validate_type = sanitize_text_field( $_POST['recaptcha_validate_type'] );
			$recaptcha_site_key      = 'invisible_v3' === $recaptcha_validate_type ? $recaptcha_v3_site_key : $recaptcha_v2_site_key;
			$recaptcha_secret_key    = 'invisible_v3' === $recaptcha_validate_type ? $recaptcha_v3_secret_key : $recaptcha_v2_secret_key;

			if ( ! empty( $recaptcha_secret_key ) && ! empty( $recaptcha_site_key ) ) {
				if ( version_compare( phpversion(), '5.3', '>=' ) ) {
					$validate = new \PP_ReCaptcha( $recaptcha_secret_key, $recaptcha_validate, $recaptcha_response );
					if ( ! $validate->is_success() ) {
						wp_send_json_error( __( 'Error verifying reCAPTCHA, please try again.', 'powerpack' ) );
					}
				} else {
					wp_send_json_error( __( 'reCAPTCHA API requires PHP version 5.3 or above.', 'powerpack' ) );
				}
			} else {
				wp_send_json_error( __( 'Your reCAPTCHA Site or Secret Key is missing!', 'powerpack' ) );
			}
		}

		if ( isset( $_POST['username'], $_POST['password'] ) ) {
			try {
				$creds = array(
					'user_login'    => trim( wp_unslash( $_POST['username'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'user_password' => $_POST['password'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					'remember'      => isset( $_POST['remember'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				$validation_error = new \WP_Error();
				$validation_error = apply_filters( 'pp_login_form_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

				if ( $validation_error->get_error_code() ) {
					throw new \Exception( '<strong>' . __( 'Error:', 'powerpack' ) . '</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $creds['user_login'] ) ) {
					throw new \Exception( '<strong>' . __( 'Error:', 'powerpack' ) . '</strong> ' . __( 'Username is required.', 'powerpack' ) );
				}

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, $user_data->roles[0] );
					}
				}

				// Perform the login.
				$user = wp_signon( apply_filters( 'pp_login_form_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					$message = $user->get_error_message();
					$message = preg_replace( '/<\/?a[^>].*>/', '', $message );

					if ( isset( $user->errors['invalid_email'][0] ) ) {

						$message = apply_filters( 'pp_login_form_invalid_email_error', $message );

					} elseif ( isset( $user->errors['invalid_username'][0] ) ) {

						$message = apply_filters( 'pp_login_form_invalid_username_error', $message );

					} elseif ( isset( $user->errors['incorrect_password'][0] ) ) {

						$message = apply_filters( 'pp_login_form_incorrect_password_error', $message );
					}

					throw new \Exception( $message );
				} else {
					do_action( 'pp_after_user_login', $user, $_POST );
					wp_send_json_success(
						array(
							'redirect_url' => $this->get_redirect_url(),
						)
					);
				}
			} catch ( \Exception $e ) {
				$this->form_error = apply_filters( 'login_errors', $e->getMessage() );
				wp_send_json_error( $this->form_error );
			}
		} else {
			wp_send_json_error( __( 'Username or password is missing.', 'powerpack' ) );
		}
	}

	/**
	 * Process social login.
	 *
	 * @since 1.5.0
	 */
	public function process_social_login() {
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'pp_login_nonce' ) ) {
				wp_send_json_error( __( 'Invalid data.', 'powerpack' ) );
		}

		if ( ! isset( $_POST['provider'] ) ) {
			wp_send_json_error( __( 'Provider was not set.', 'powerpack' ) );
		}

		if ( 'facebook' === $_POST['provider'] ) {
			$this->process_facebook_login();
		}
		if ( 'google' === $_POST['provider'] ) {
			$this->process_google_login();
		}
	}

	/**
	 * Process Facebook login.
	 *
	 * @since 1.5.0
	 * @access private
	 */
	private function process_facebook_login() {
		$err_unauth_access = __( 'Unauthorized access.', 'powerpack' );

		if ( ! isset( $_POST['auth_response'] ) ) {
			wp_send_json_error( $err_unauth_access );
		}
		if ( ! isset( $_POST['user_data'] ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$auth_response = $_POST['auth_response'];
		$user_data     = $_POST['user_data'];

		// Email can be empty in case user was registered using phone number on Facebook.
		$email        = isset( $user_data['email'] ) ? sanitize_email( $user_data['email'] ) : '';
		$name         = sanitize_user( $user_data['name'] );
		$first_name   = sanitize_user( $user_data['first_name'] );
		$last_name    = sanitize_user( $user_data['last_name'] );
		$id           = sanitize_text_field( $user_data['id'] );
		$access_token = sanitize_text_field( $auth_response['accessToken'] );
		$user_id      = sanitize_text_field( $auth_response['userID'] );

		$client_id     = \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_fb_app_id' );
		$client_secret = \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_fb_app_secret' );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		// Get Facebook App Access Token.
		$response = wp_remote_get(
			add_query_arg(
				array(
					'client_id'     => $client_id, // Facebook App ID.
					'client_secret' => $client_secret, // Facebook App Secret.
					'grant_type'    => 'client_credentials',
				),
				'https://graph.facebook.com/oauth/access_token'
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		$app_access_token = $response->access_token;

		// Get valid Facebook User ID.
		$user_response = wp_remote_get(
			add_query_arg(
				array(
					'input_token'  => $access_token,
					'access_token' => $app_access_token,
				),
				'https://graph.facebook.com/debug_token'
			)
		);

		if ( is_wp_error( $user_response ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$user_response = json_decode( wp_remote_retrieve_body( $user_response ), true );

		if ( false === $user_response['is_valid'] ) {
			wp_send_json_error( $err_unauth_access );
		}

		if ( $user_id !== $user_response['data']['user_id'] ) {
			wp_send_json_error( $err_unauth_access );
		}

		$valid_user_id = $user_response['data']['user_id'];

		// Get Facebook User Email.
		$response = wp_remote_get(
			add_query_arg(
				array(
					'fields'       => 'email',
					'access_token' => $access_token,
				),
				'https://graph.facebook.com/' . $valid_user_id
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! empty( $email ) && $email !== $response->email ) {
			wp_send_json_error( $err_unauth_access );
		}

		if ( empty( $email ) && empty( $response->email ) ) {
			$user_email = $valid_user_id . '@facebook.com';
		} else {
			$user_email = $response->email;
		}

		$this->do_social_login(
			array(
				'email'      => $user_email,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'provider'   => 'facebook',
			)
		);
	}

	/**
	 * Process Google login.
	 *
	 * @since 1.5.0
	 * @access private
	 */
	private function process_google_login() {
		$err_unauth_access = __( 'Unauthorized access.', 'powerpack' );

		if ( ! isset( $_POST['user_data'] ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$user_data = $_POST['user_data'];

		$email     = sanitize_email( $user_data['email'] );
		$name      = sanitize_user( $user_data['name'] );
		$id_token  = sanitize_text_field( $user_data['hash'] );
		$client_id = \PowerpackElements\Classes\PP_Admin_Settings::get_option( 'pp_google_client_id' );

		require_once POWERPACK_ELEMENTS_PATH . 'modules/login-form/includes/vendor/autoload.php';

		// Let's verify id_token.
		$google_client = new \Google_Client(
			array(
				'client_id' => $client_id,
			)
		);

		$play_load = $google_client->verifyIdToken( $id_token );

		if ( empty( $play_load ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		if ( $email !== $play_load['email'] || $client_id !== $play_load['aud'] ) {
			wp_send_json_error( $err_unauth_access );
		}

		$this->do_social_login(
			array(
				'email'      => $email,
				'first_name' => $name,
				'provider'   => 'google',
			)
		);
	}

	/**
	 * Login into WP via social platforms.
	 *
	 * @since 1.5.0
	 * @access private
	 */
	private function do_social_login( $data, $validate_password = false ) {
		$email    = $data['email'];
		$username = explode( '@', $email );
		$username = $username[0];
		$userdata = get_user_by( 'email', $email );

		if ( ! empty( $userdata ) ) {
			if ( $validate_password && isset( $data['password'] ) ) {
				if ( ! wp_check_password( $data['password'], $userdata->user_pass, $userdata->ID ) ) {
					wp_send_json_error( __( 'Password does not match.', 'powerpack' ) );
				}
			}

			$user_id    = $userdata->ID;
			$user_email = $userdata->user_email;
			$username   = $userdata->user_login;

			wp_set_auth_cookie( $user_id );
			wp_set_current_user( $user_id, $username );

			do_action( 'wp_login', $userdata->user_login, $userdata );

			wp_send_json_success(
				array(
					'redirect_url' => $this->get_redirect_url(),
				)
			);
		} else {
			if ( ! apply_filters( 'pp_login_form_social_login_registration', get_option( 'users_can_register' ) ) ) {
				wp_send_json_error( __( 'New registrations are currently disabled.', 'powerpack' ) );
			}

			if ( username_exists( $username ) ) {
				$username .= '-' . zeroise( wp_rand( 0, 9999 ), 4 );
			}

			$data['username'] = $username;
			$data['password'] = wp_generate_password( apply_filters( 'pp_login_form_password_length', 12 ), true, false );

			$user_id = wp_insert_user(
				array(
					'user_login' => $data['username'],
					'user_pass'  => $data['password'],
					'user_email' => $email,
					'first_name' => isset( $data['first_name'] ) ? $data['first_name'] : '',
					'last_name'  => isset( $data['last_name'] ) ? $data['last_name'] : '',
				)
			);

			if ( is_wp_error( $user_id ) ) {
				wp_send_json_error( $user_id->get_error_message() );
			}

			update_user_meta( $user_id, 'pp_login_form_provider', $data['provider'] );

			$this->do_social_login( $data, true );
		}
	}

	public function process_lost_password() {
		if (
			! isset( $_POST['pp-lf-lost-password-nonce'] ) ||
			! wp_verify_nonce( wp_unslash( $_POST['pp-lf-lost-password-nonce'] ), 'lost_password' ) ) {
				wp_send_json_error( __( 'Invalid data.', 'powerpack' ) );
		}

		$success = $this->retrieve_password();

		if ( ! $success ) {
			wp_send_json_error( $this->form_error );
		}

		wp_send_json_success(
			array(
				'redirect_url' => $this->get_redirect_url(),
			)
		);
	}

	private function retrieve_password() {
		$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : ''; // phpcs:ignore  input var ok, CSRF ok.

		if ( empty( $login ) ) {

			$this->form_error = __( 'Enter a username or email address.', 'powerpack' );

			return false;

		} else {
			// Check on username first, as customers can use emails as usernames.
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $login ) ) {
			$user_data = get_user_by( 'email', $login );
		}

		$errors = new \WP_Error();

		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() ) {
			$this->form_error = $errors->get_error_message();

			return false;
		}

		if ( ! $user_data ) {
			$this->form_error = __( 'Invalid username or email.', 'powerpack' );

			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			$this->form_error = __( 'Invalid username or email.', 'powerpack' );

			return false;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			$this->form_error = __( 'Password reset is not allowed for this user', 'powerpack' );

			return false;

		} elseif ( is_wp_error( $allow ) ) {

			$this->form_error = $errors->get_error_message();

			return false;
		}

		// Get password reset key (function introduced in WordPress 4.4).
		$key = get_password_reset_key( $user_data );

		$page_url = esc_url_raw( $_POST['page_url'] );

		$reset_url = add_query_arg(
			array(
				'reset_pass' => 1,
				'key'        => $key,
				'id'         => $user_data->ID,
			),
			$page_url
		);

		// Send email notification.
		$email_sent = $this->send_activation_email( $user_data, $reset_url );

		if ( ! $email_sent ) {
			$this->form_error = esc_html__( 'An error occurred sending email. Please try again.', 'powerpack' );
		}

		return $email_sent;
	}

	private function send_activation_email( $user, $reset_url ) {
		$email       = $user->data->user_email;
		$blogname    = esc_html( wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
		$admin_email = get_option( 'admin_email' );
		$subject     = sprintf( esc_html__( 'Password Reset Request for %s', 'powerpack' ), $blogname );

		$content = '';
		/* translators: %s: Username */
		$content .= '<p>' . sprintf( esc_html__( 'Hi %s,', 'powerpack' ), esc_html( $user->data->user_login ) ) . '</p>';
		/* translators: %s: Site name */
		$content .= '<p>' . sprintf( esc_html__( 'Someone has requested a new password for the following account on %s:', 'powerpack' ), $blogname ) . '</p>';
		/* translators: %s Username */
		$content .= '<p>' . sprintf( esc_html__( 'Username: %s', 'powerpack' ), esc_html( $user->data->user_login ) ) . '</p>';
		$content .= esc_html__( 'If you didn\'t make this request, just ignore this email. If you\'d like to proceed:', 'powerpack' );
		$content .= '<p>';
		$content .= '<a class="link" href="' . esc_url( $reset_url ) . '">';
		$content .= esc_html__( 'Click here to reset your password', 'powerpack' );
		$content .= '</a>';
		$content .= '</p>';

		// translators: %s: email_from_name
		$headers = sprintf( 'From: %s <%s>' . "\r\n", $blogname, get_option( 'admin_email' ) );
		// translators: %s: email_reply_to
		$headers .= sprintf( 'Reply-To: %s' . "\r\n", $admin_email );
		$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

		// Send email to user.
		$email_sent = wp_mail( $email, $subject, $content, $headers );

		return $email_sent;
	}

	public function process_reset_password() {
		if (
			! isset( $_POST['pp-lf-reset-password-nonce'] ) ||
			! wp_verify_nonce( wp_unslash( $_POST['pp-lf-reset-password-nonce'] ), 'reset_password' ) ) {
				wp_send_json_error( __( 'Invalid data.', 'powerpack' ) );
		}
		$posted_fields = array( 'password_1', 'password_2', 'reset_key', 'reset_login' );
		foreach ( $posted_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				wp_send_json_error( __( 'Invalid data.', 'powerpack' ) );
			}
			if ( in_array( $field, array( 'password_1', 'password_2' ) ) ) { //phpcs:ignore
				// Don't unslash password fields
				$posted_fields[ $field ] = $_POST[ $field ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			} else {
				$posted_fields[ $field ] = wp_unslash( $_POST[ $field ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}
		}
		if ( empty( $posted_fields['password_1'] ) ) {
			$this->form_error = __( 'Please enter your password.', 'powerpack' );
		} elseif ( $posted_fields['password_1'] !== $posted_fields['password_2'] ) {
			$this->form_error = __( 'Passwords do not match.', 'powerpack' );
		}
		$user = $this->check_password_reset_key( $posted_fields['reset_key'], $posted_fields['reset_login'] );
		if ( is_object( $user ) && empty( $this->form_error ) ) {
			$errors = new \WP_Error();
			do_action( 'validate_password_reset', $errors, $user );
			if ( is_wp_error( $errors ) && $errors->get_error_messages() ) {
				foreach ( $errors->get_error_messages() as $error ) {
					$this->form_error .= $error . "\r\n";
				}
			}
			$this->reset_password( $user, $posted_fields['password_1'] );
			do_action( 'pp_login_form_user_reset_password', $user );
			wp_send_json_success();
		}
		if ( ! empty( $this->form_error ) ) {
			wp_send_json_error( $this->form_error );
		}
		wp_send_json_error( __( 'Unknown error', 'powerpack' ) ); //phpcs:ignore 
	}

	public function check_password_reset_key( $key, $login ) {
		// Check for the password reset key.
		// Get user data or an error message in case of invalid or expired key.
		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			$this->form_error = __( 'This key is invalid or has already been used. Please reset your password again if needed.', 'powerpack' );
			return false;
		}

		return $user;
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @param object $user     The user.
	 * @param string $new_pass New password for the user in plaintext.
	 */
	private function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );
		$this->set_reset_password_cookie();

		if ( ! apply_filters( 'pp_login_form_disable_password_change_notification', false ) ) {
			wp_password_change_notification( $user );
		}
	}

	/**
	 * Set or unset the cookie.
	 *
	 * @param string $value Cookie value.
	 */
	private function set_reset_password_cookie( $value = '' ) {
		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$rp_path   = isset( $_POST['page_url'] ) ? current( explode( '?', wp_unslash( $_POST['page_url'] ) ) ) : ''; // WPCS: input var ok, sanitization ok.

		if ( $value ) {
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		} else {
			setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	private function get_redirect_url() {
		if ( ! empty( $_POST['redirect'] ) ) {
			$redirect = wp_unslash( $_POST['redirect'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		} elseif ( $this->get_raw_referer() ) {
			$redirect = $this->get_raw_referer();
		} else {
			$redirect = wp_unslash( $_POST['page_url'] );
		}

		return wp_validate_redirect( $redirect, wp_unslash( $_POST['page_url'] ) );
	}

	/**
	 * Get raw referer.
	 *
	 * @since 1.5.0
	 * @access private
	 */
	private function get_raw_referer() {
		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) { // phpcs:ignore input var ok, CSRF ok.
			return wp_unslash( $_REQUEST['_wp_http_referer'] ); // phpcs:ignore input var ok, CSRF ok, sanitization ok.
		} elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) { // phpcs:ignore input var ok, CSRF ok.
			return wp_unslash( $_SERVER['HTTP_REFERER'] ); // phpcs:ignore input var ok, CSRF ok, sanitization ok.
		}

		return false;
	}

	public function get_error_message() {
		return $this->form_error;
	}
}
