<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

//A view id points here, the panel itself is shadow.php. Box shadows are the only ones with a spread, and the field list follows the key.
$sr_shdw_key   = isset($sr_shdw_key) ? $sr_shdw_key : 'bShdw';
$sr_shdw_title = isset($sr_shdw_title) ? $sr_shdw_title : __('Box Shadow','revslider');
include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/shadow.php');
