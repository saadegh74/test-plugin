<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

//the classic name a namespaced file cannot declare: every host requires this once its class map is in

/** @deprecated 7.0.0 use RevSlider7Output - a real subclass (not an alias), so third-party instanceof checks keep their old asymmetry */
if(!class_exists('RevSliderOutput', false)){ class RevSliderOutput extends \ThemePunch\SR\Core\Output {} }
