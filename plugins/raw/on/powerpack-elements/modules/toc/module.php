<?php
namespace PowerpackElements\Modules\Toc;

use PowerpackElements\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Module extends Module_Base {

	public function get_name() {
		return 'pp-table-of-contents';
	}

	public function get_widgets() {
		return [
			'Table_Of_Contents',
		];
	}
}
