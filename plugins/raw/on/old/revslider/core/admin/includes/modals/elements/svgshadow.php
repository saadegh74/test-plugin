<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

//A view id points here, the panel itself is shadow.php. An SVG is shadowed by its SHAPE, so this one renders
//as a drop-shadow filter and carries no spread.
$sr_shdw_key   = isset($sr_shdw_key) ? $sr_shdw_key : 'sShdw';
$sr_shdw_title = isset($sr_shdw_title) ? $sr_shdw_title : __('Drop Shadow','revslider');
include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/shadow.php');
