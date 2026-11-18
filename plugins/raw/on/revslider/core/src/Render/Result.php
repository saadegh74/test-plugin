<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace ThemePunch\SR\Render;

if(!defined('ABSPATH')) exit();

/**
 * One finished render, in page order. The host decides where each part goes:
 * head block into its head, markup into the page, footer script at the end,
 * and the asset manifest into its own enqueue mechanism.
 * @since 7.2.1
 */
class Result {

	/** @var string the head script block (SR7.E environment, breakpoints, JSON bootstrap) */
	public $head_html = '';

	/** @var string the module markup */
	public $body_html = '';

	/** @var string the footer script block (init calls, transition table, stream payloads) */
	public $footer_js = '';

	/** @var array script/style handles, SR7.E name lists, fonts queue, yt-api flag */
	public $assets = [];
}
