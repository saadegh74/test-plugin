<?php
/**
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}

/**
 * Get retention.json absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function ai1wmue_retention_path( $params ) {
	return ai1wm_storage_path( $params ) . DIRECTORY_SEPARATOR . AI1WMUE_RETENTION_NAME;
}

/**
 * Check whether export/import is running
 *
 * @return boolean
 */
function ai1wmue_is_running() {
	if ( isset( $_GET['file'] ) || isset( $_POST['file'] ) ) {
		return true;
	}

	return false;
}
