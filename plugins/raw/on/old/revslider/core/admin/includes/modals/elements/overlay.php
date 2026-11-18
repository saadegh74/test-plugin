<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Content Overlay','revslider'); ?></sr-separator-title>
		<!-- Stores nothing: on is a pattern being set, editor.bgpattern keeps it in step with the model -->
		<sr-onoff id="sr_layer_overlay_onoff" class="sr--mr--0" style="right:0px" data-sh=".sr_elements_overlay_body" data-onchange="editor.bgpattern.power" data-onchangeparams="lover"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_overlay_body">
		<?php
		$sr_bgp_view   = 'layer_extra';
		$sr_bgp_change = 'editor.elements.overlay';
		$sr_bgp_host   = 'sr_layer_overlay_essentials';
		$sr_bgp_ov     = true;
		include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/bgpattern.php');
		?>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>