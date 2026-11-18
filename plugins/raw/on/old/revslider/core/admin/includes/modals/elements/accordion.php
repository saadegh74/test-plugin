<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-- Accordion: every question of this FAQ group set at once, only on the group itself, as its last section. Nothing is stored on
     the group, these fields read and write the Selected State of each question and the sr7-acc class of each card
     (SR7.editor.elements.accordion) -->
<sr-separator keepborder>
	<sr-separator-head>
		<sr-separator-title><?php _e('Accordion','revslider'); ?></sr-separator-title>
		<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
	</sr-separator-head>
	<sr-separator-body>
		<sr-wrap wide basic="" class="sr--form--grp sr--mb--15"><sr-onoff id="sr_acc_u" class="sr--mr--10" data-sh=".sr_elements_acc_opts" data-onchange="editor.elements.accordion.set" data-onchangeparams="u"></sr-onoff><span><?php _e('Open on Click','revslider'); ?></span></sr-wrap>
		<sr-wrap class="sr_elements_acc_opts">
			<!-- the questions' Selected State behavior: radio = one at a time, toggle = each by itself, radio1 = always one -->
			<sr-tabs-wrap id="sr_acc_mode" nopopulate class="sr--mb--15">
				<sr-tab left onethird data-v="radio" data-onchange="editor.elements.accordion.set" data-onchangeparams="mode"><?php _e('Single','revslider'); ?></sr-tab>
				<sr-tab none onethird data-v="toggle" data-onchange="editor.elements.accordion.set" data-onchangeparams="mode"><?php _e('Multiple','revslider'); ?></sr-tab>
				<sr-tab right onethird data-v="radio1" data-onchange="editor.elements.accordion.set" data-onchangeparams="mode"><?php _e('Always One','revslider'); ?></sr-tab>
			</sr-tabs-wrap>
			<sr-drop id="sr_acc_start" class="sr--mb--10" wide dropsw="200" data-v="first" data-defval="first" data-onchange="editor.elements.accordion.set" data-onchangeparams="start">
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('At Start','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="first"><?php _e('First Open','revslider'); ?></sr-drops>
				<sr-drops data-v="none"><?php _e('All Closed','revslider'); ?></sr-drops>
				<sr-drops data-v="all"><?php _e('All Open','revslider'); ?></sr-drops>
			</sr-drop>
		</sr-wrap>
	</sr-separator-body>
</sr-separator>
