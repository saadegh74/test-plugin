<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-- On the page the text becomes an input (layer.js SR7.F.fieldIn); a Submit Form action sends what was typed -->
<sr-separator id="layers_field_settings" keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Form Field','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_field" r="field.u" viewchild="layer_basics"></sr-onoff><sr-tooltip key="formfield"></sr-tooltip>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_field">
		<sr-drop wide data-v="email" data-defval="email" r="field.type" viewchild="layer_basics">
			<sr-drop-view>
				<span class="sr--drop--value"></span>
				<span class="sr--form--otitle"><?php _e('Type','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="email"><?php _e('Email','revslider'); ?></sr-drops>
			<sr-drops data-v="text"><?php _e('Text','revslider'); ?></sr-drops>
			<sr-drops data-v="tel"><?php _e('Phone','revslider'); ?></sr-drops>
			<sr-drops data-v="number"><?php _e('Number','revslider'); ?></sr-drops>
			<sr-drops data-v="textarea"><?php _e('Long Text','revslider'); ?></sr-drops>
		</sr-drop>
		<sr-input wide><input name="Field Name" replace r="field.name" viewchild="layer_basics" type="text" autocomplete="off"><span noicon="" class="sr--form--otitle"><?php _e('Sent as','revslider'); ?></span></sr-input>
		<sr-wrap basic class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="field.req" viewchild="layer_basics"></sr-onoff><span><?php _e('Required','revslider'); ?></span></sr-wrap>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
