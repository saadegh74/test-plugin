<?php
namespace PowerpackElements\Modules\BusinessReviews;

use PowerpackElements\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Module_Base {

	public function get_name() {
		return 'pp-business-reviews';
	}

	public function get_widgets() {
		return [
			'Business_Reviews',
		];
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}
}
