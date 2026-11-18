<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<style>
	/* The dialog's own rules live with the dialog. The row list itself is shared with the Meta Editing dialog and
	   lives in library.css (.sr--dialog--list / --row / --name / --btns / --empty), scoped to both by id */
	/* 🔴 sr-scroll-content is position:absolute, so once the scroller has the list its rows contribute nothing
	   to the width. A scrolled dialog needs a width of its own, the way #sr_addon_warnings_dynamic has one */
	#sr_module_trash{ width:560px; max-width:calc(100vw - 40px); box-sizing:border-box; }
	#sr_module_trash .sr--trash--intro{ display:block; line-height:1.5; }
	#sr_module_trash sr-popup-content{ display:block; }
</style>
<sr-popup id="sr_module_trash">
	<sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-popup-close>
	<sr-popup-header class="sr--text--center">
		<sr-wrap class="sr--text--center"><svg class="sr--icon" width="16" height="20" transform="translate(0, 0)"><use xlink:href="#Delete"></use></svg></sr-wrap>
		<sr-sp h="12"></sr-sp>
		<h2 class="sr--popup--title"><?php echo __('Trash','revslider');?></h2>
		<sr-wrap class="sr--text--center">
			<sr-sp h="8"></sr-sp>
			<span id="sr_trash_intro" class="sr--text sr--trash--intro"></span>
		</sr-wrap>
	</sr-popup-header>
	<sr-popup-content>
		<sr-sp h="10"></sr-sp>
		<sr-wrap id="sr_trash_list" class="sr--dialog--list"></sr-wrap>
	</sr-popup-content>
	<sr-wrap class="sr--text--center">
		<sr-sp h="20"></sr-sp>
		<sr-button data-action="B.popUp.hide" primary class="sr--cta sr--cta--big"><?php echo __('Close','revslider');?></sr-button><!--
		--><sr-sp w="10"></sr-sp><!--
		--><sr-button id="sr_trash_empty" data-action="B.trash.empty" clean class="sr--cta sr--cta--big"><?php echo __('Empty the Trash','revslider');?></sr-button>
	</sr-wrap>
</sr-popup>
