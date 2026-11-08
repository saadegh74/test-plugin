<?php
/**
 * Plugin Name: All-in-One WP Migration Unlimited Extension (برند تست)
 * Plugin URI: https://servmask.com/
 * Description: افزونه افزایش نامحدود حجم درون‌ریزی برای افزونه All-in-One WP Migration با پشتیبانی و به‌روزرسانی هوشمند برند تست.
 * Author: برند تست
 * Author URI: https://github.com/saadegh74/test-plugin
 * Version: 2.86
 * Text Domain: all-in-one-wp-migration-unlimited-extension
 * Domain Path: /languages
 * Network: True
 *
 * Copyright (C) 2014-2020 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}

delete_option('ai1wm_updater');
add_filter( 'pre_http_request', function( $pre, $parsed_args, $url ){
if ( strpos( $url, 'https://redirect.wp-migration.com/v1/check/unlimited-extension/' ) !== false ) {
return new WP_Error();
} else {
return $pre;
}
}, 10, 3 );

if ( is_multisite() ) {
	// Multisite Extension shall be used instead
	return;
}

// Check SSL Mode
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && ( $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) ) {
	$_SERVER['HTTPS'] = 'on';
}

// Plugin Basename
define( 'AI1WMUE_PLUGIN_BASENAME', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );

// Plugin Path
define( 'AI1WMUE_PATH', dirname( __FILE__ ) );

// Plugin URL
define( 'AI1WMUE_URL', plugins_url( '', AI1WMUE_PLUGIN_BASENAME ) );

// Include constants
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'constants.php';

// Include functions
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'functions.php';

// Include loader
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'loader.php';

// ==========================================
// = Plugin Update Checker - برند تست       =
// ==========================================
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'plugin-update-checker' . DIRECTORY_SEPARATOR . 'plugin-update-checker.php';
$ai1wmueUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://raw.githubusercontent.com/saadegh74/test-plugin/main/plugins/updates/all-in-one-wp-migration-unlimited-extension/staging/info.json',
	__FILE__,
	'all-in-one-wp-migration-unlimited-extension'
);
