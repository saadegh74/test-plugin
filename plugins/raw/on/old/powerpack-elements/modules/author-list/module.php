<?php
namespace PowerpackElements\Modules\AuthorList;

use PowerpackElements\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Module_Base {

	public function get_name() {
		return 'pp-author-list';
	}

	public function get_widgets() {
		return [
			'Author_List',
		];
	}
}
