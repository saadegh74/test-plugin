<?php
/**
 * Handles logic for reCAPTCHA.
 */
class PP_ReCaptcha {
	/**
	 * Google reCAPTCHA Secret Key
	 *
	 * @var $secret_key
	 */
	private $secret_key;

	/**
	 * Validate Type
	 *
	 * @var $validate_type
	 */
	private $validate_type;

	/**
	 * Response
	 *
	 * @var $response
	 */
	private $response;

	/**
	 * Is Success
	 *
	 * @var $is_success
	 */
	protected $is_success = false;

	/**
	 * Error
	 *
	 * @var $error
	 */
	protected $error = false;

	/**
	 * __construct
	 *
	 * @param  mixed $secret_key Secret key.
	 * @param  mixed $validate_type validation type.
	 * @param  mixed $response response.
	 */
	public function __construct( $secret_key, $validate_type, $response ) {
		$this->secret_key    = $secret_key;
		$this->validate_type = $validate_type;
		$this->response      = $response;

		// Do recaptcha validation here so we can only load for php 5.3 and above.
		require_once POWERPACK_ELEMENTS_PATH . 'includes/vendor/recaptcha/autoload.php';

		$this->verify_recaptcha();
	}

	/**
	 * Verify Recaptcha
	 */
	private function verify_recaptcha() {
		if ( function_exists( 'curl_exec' ) ) {
			$recaptcha = new \ReCaptcha\ReCaptcha( $this->secret_key, new \ReCaptcha\RequestMethod\CurlPost() );
		} else {
			$recaptcha = new \ReCaptcha\ReCaptcha( $this->secret_key );
		}

		if ( 'invisible_v3' === $this->validate_type ) {
			// @codingStandardsIgnoreStart
			// V3
			$response = $recaptcha->setExpectedHostname( $_SERVER['SERVER_NAME'] )
							->setExpectedAction( 'Form' )
							->setScoreThreshold( 0.5 )
							->verify( $this->response, $_SERVER['REMOTE_ADDR'] );
			// @codingStandardsIgnoreEnd
		} else {
			// V2.
			$response = $recaptcha->verify( $this->response, $_SERVER['REMOTE_ADDR'] );
		}

		if ( ! $response->isSuccess() ) {
			$this->is_success = false;
			$error_codes      = array();
			foreach ( $response->getErrorCodes() as $code ) {
				$error_codes[] = $code;
			}
			$this->error = implode( ' | ', $error_codes );
		} else {
			$this->is_success = true;
			$this->error      = false;
		}
	}

	/**
	 * Check if success
	 */
	public function is_success() {
		return $this->is_success;
	}

	/**
	 * Get error message
	 */
	public function get_error_message() {
		return $this->error;
	}
}
